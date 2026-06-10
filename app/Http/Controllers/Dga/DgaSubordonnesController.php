<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Setting;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaSubordonnesController extends Controller
{
    use ResolvesEntite;

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /**
     * Liste des subordonnés du DGA :
     *  - Directeurs Techniques (directeur_agent_id de chaque DelegationTechnique)
     *  - Secrétaire du DGA (via entites.dga_secretaire_agent_id)
     */
    public function index(Request $request): View
    {
        $this->checkDga();

        $entite = $this->getEntiteForDGA();

        // ── DTs disponibles ──────────────────────────────────────────────────
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')
            ->pluck('directeur_agent_id');

        $directeursTechniques = User::whereIn('agent_id', $dtAgentIds)
            ->where('role', 'Directeur_Technique')
            ->with('agent.directedDelegation')
            ->orderBy('name')
            ->get();

        $secretaire  = $this->getDgaSecretaireUser($entite);
        $dtUserIds   = $directeursTechniques->pluck('id');

        // ── Filtres ──────────────────────────────────────────────────────────
        $tab     = $request->query('tab', 'objectifs');
        $dtId    = $request->query('dt_id') ? (int) $request->query('dt_id') : null;
        $statut  = (string) $request->query('statut', '');
        $annee   = (string) $request->query('annee', '');
        $search  = (string) $request->query('search', '');
        $sort    = $request->query('sort', 'date');
        $sortDir = $request->query('sort_dir', 'desc');

        // ── Objectifs ────────────────────────────────────────────────────────
        $fichesBaseQ = fn () => FicheObjectif::where('assignable_type', User::class)
            ->whereIn('assignable_id', $dtUserIds);

        $fichesStats = [
            'total'      => $fichesBaseQ()->count(),
            'acceptees'  => $fichesBaseQ()->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesBaseQ()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $fichesBaseQ()->where('statut', 'refusee')->count(),
        ];

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->with(['assignable'])
            ->where('assignable_type', User::class)
            ->whereIn('assignable_id', $dtUserIds);

        if ($dtId)   { $fichesQ->where('assignable_id', $dtId); }
        if ($search) { $fichesQ->where('titre', 'like', "%{$search}%"); }
        if ($annee)  { $fichesQ->whereYear('date', $annee); }
        if ($statut) {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }

        $allowedFicheSort = ['date' => 'date', 'statut' => 'statut'];
        $fichesQ->orderBy($allowedFicheSort[$sort] ?? 'date', $sortDir === 'asc' ? 'asc' : 'desc');

        $fiches = $fichesQ->paginate(15)->withQueryString();

        // ── Évaluations ──────────────────────────────────────────────────────
        $evalsBaseQ = fn () => Evaluation::where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $dtUserIds);

        $evaluationsStats = [
            'total'     => $evalsBaseQ()->count(),
            'brouillon' => $evalsBaseQ()->where('statut', 'brouillon')->count(),
            'soumis'    => $evalsBaseQ()->where('statut', 'soumis')->count(),
            'valide'    => $evalsBaseQ()->where('statut', 'valide')->count(),
        ];

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification', 'evaluable'])
            ->where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $dtUserIds);

        if ($dtId)   { $evalsQ->where('evaluable_id', $dtId); }
        if ($statut) { $evalsQ->where('statut', $statut); }
        if ($annee)  { $evalsQ->whereYear('date_debut', $annee); }

        $allowedEvalSort = ['date' => 'date_debut', 'statut' => 'statut'];
        $evalsQ->orderBy($allowedEvalSort[$sort] ?? 'date_debut', $sortDir === 'asc' ? 'asc' : 'desc');

        $evaluations = $evalsQ->paginate(15)->withQueryString();

        // ── Feature flags ────────────────────────────────────────────────────
        $objectifsEnabled   = Setting::featureEnabled('objectifs')   && Auth::user()->can('objectifs.assigner');
        $evaluationsEnabled = Setting::featureEnabled('evaluations') && Auth::user()->can('evaluations.creer');

        // ── État fiche pour le DT filtré (si un DT est sélectionné) ─────────
        $ficheBlocksNewForDt = $dtId
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $dtId)->whereNotIn('statut', ['refusee'])->exists()
            : false;
        $ficheAccepteeForDt = $dtId
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $dtId)->where('statut', 'acceptee')->exists()
            : false;
        $evaluationEnCoursForDt = $dtId
            ? Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $dtId)->whereIn('statut', ['soumis', 'brouillon'])->exists()
            : false;

        $filters = compact('tab', 'dtId', 'statut', 'annee', 'search', 'sort', 'sortDir');

        return view('dga.subordonnes.index', compact(
            'directeursTechniques', 'secretaire', 'entite',
            'tab', 'filters',
            'fiches', 'fichesStats',
            'evaluations', 'evaluationsStats',
            'objectifsEnabled', 'evaluationsEnabled',
            'ficheBlocksNewForDt', 'ficheAccepteeForDt', 'evaluationEnCoursForDt'
        ));
    }

    /**
     * Redirige vers le dossier de la secrétaire du DGA, ou affiche un écran
     * "non configurée" si aucune secrétaire n'est définie.
     */
    public function secretaire(): View|RedirectResponse
    {
        $this->checkDga();

        $entite    = $this->getEntiteForDGA();
        $secretaire = $this->getDgaSecretaireUser($entite);

        if (! $secretaire) {
            return view('dga.secretaire.index', compact('entite'));
        }

        return redirect()->route('dga.subordonnes.show', $secretaire);
    }

    /**
     * Dossier d'un subordonné : objectifs + évaluations.
     */
    public function show(Request $request, User $user): View
    {
        $this->checkDga();

        $entite = $this->getEntiteForDGA();

        // Vérifier que cet utilisateur est bien un subordonné du DGA
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $isDirecteurTechnique = $user->role === 'Directeur_Technique' && $dtAgentIds->contains($user->agent_id);
        $isSecretaire = $entite && $user->agent_id == $entite->dga_secretaire_agent_id;

        if (! $isDirecteurTechnique && ! $isSecretaire) {
            abort(403, 'Cet utilisateur n\'est pas un subordonné du DGA.');
        }

        $tab    = $request->query('tab', 'objectifs');
        $search = (string) $request->query('search', '');
        $statut = (string) $request->query('statut', '');

        // Fiches d'objectifs
        $baseF = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id);

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->orderByDesc('date');

        if ($search && $tab === 'objectifs') {
            $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%"));
        }
        if ($statut && $tab === 'objectifs') {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];
        $fiches = $fichesQ->paginate(10)->withQueryString();

        // Évaluations
        $baseE = fn () => Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $user->id);

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification'])
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->orderByDesc('date_debut');

        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'     => $baseE()->count(),
            'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
            'soumis'    => $baseE()->where('statut', 'soumis')->count(),
            'valide'    => $baseE()->where('statut', 'valide')->count(),
        ];
        $evaluations = $evalsQ->paginate(10)->withQueryString();

        $ficheBlocksNew = FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->whereNotIn('statut', ['refusee'])
            ->exists();
        $ficheAcceptee = FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->where('statut', 'acceptee')
            ->exists();

        $evaluationEnCours = $baseE()->whereIn('statut', ['soumis', 'brouillon'])->exists();

        $filters = compact('tab', 'search', 'statut');

        return view('dga.subordonnes.show', compact(
            'user', 'entite', 'tab', 'fiches', 'fichesStats', 'evaluations', 'evaluationsStats', 'filters',
            'ficheBlocksNew', 'ficheAcceptee', 'evaluationEnCours'
        ));
    }
}
