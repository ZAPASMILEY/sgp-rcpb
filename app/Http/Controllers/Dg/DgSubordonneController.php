<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class DgSubordonneController extends Controller
{
    use ResolvesEntite;

    /** Retourne l'entite_id du DG connecte. */
    private function entiteId(): int
    {
        return (int) ($this->getEntiteForDG()?->id ?? 0);
    }

    /** Charge les donnees paginees pour un subordonne (objectifs + evaluations). */
    private function loadDossierData(Request $request, User $subordonne): array
    {
        $tab = $request->get('tab', 'objectifs');
        $search = (string) $request->get('search', '');
        $statut = (string) $request->get('statut', '');

        $baseF = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $subordonne->id);

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $subordonne->id)
            ->orderByDesc('date');

        if ($search && $tab === 'objectifs') {
            $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")
                ->orWhereHas('annee', fn ($a) => $a->where('annee', 'like', "%{$search}%")));
        }

        if ($statut && $tab === 'objectifs') {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }

        $fichesStats = [
            'total' => $baseF()->count(),
            'acceptees' => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees' => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches = $fichesQ->paginate(10)->withQueryString();

        $baseE = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $subordonne->id);

        $evalsQ = Evaluation::query()
            ->with(['evaluable', 'evaluateur', 'identification'])
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $subordonne->id)
            ->orderByDesc('date_debut');

        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total' => $baseE()->count(),
            'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        $ficheBlocksNew = FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $subordonne->id)
            ->whereNotIn('statut', ['refusee'])
            ->exists();
        $ficheAcceptee = FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $subordonne->id)
            ->where('statut', 'acceptee')
            ->exists();

        $evaluationEnCours = $baseE()->whereIn('statut', ['soumis', 'brouillon'])->exists();

        $filters = compact('tab', 'search', 'statut');

        return compact('tab', 'fiches', 'fichesStats', 'evaluations', 'evaluationsStats', 'filters', 'ficheBlocksNew', 'ficheAcceptee', 'evaluationEnCours');
    }

    /** Donnees vides quand le subordonne n'existe pas. */
    private function emptyDossierData(Request $request): array
    {
        $tab = $request->get('tab', 'objectifs');
        $empty = new LengthAwarePaginator([], 0, 10, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return [
            'tab' => $tab,
            'fiches' => $empty,
            'fichesStats' => ['total' => 0, 'acceptees' => 0, 'en_attente' => 0, 'refusees' => 0],
            'evaluations' => $empty,
            'evaluationsStats' => ['total' => 0, 'brouillon' => 0, 'soumis' => 0, 'valide' => 0],
            'filters' => ['tab' => $tab, 'search' => '', 'statut' => ''],
        ];
    }

    public function dga(Request $request): View
    {
        $entite = $this->getEntiteForDG();

        // Lookup via entite.dga_agent_id en priorité, fallback sur le seul DGA actif.
        $dga = null;
        if ($entite && $entite->dga_agent_id) {
            $dga = User::query()->where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
        }
        if (! $dga) {
            $dga = User::query()->where('role', 'DGA')->where('is_active', true)->latest()->first();
            // Resynchronise la clé FK pour les prochains appels
            if ($dga && $entite && $entite->dga_agent_id !== $dga->agent_id) {
                $entite->update(['dga_agent_id' => $dga->agent_id]);
            }
        }

        $data = $dga
            ? $this->loadDossierData($request, $dga)
            : $this->emptyDossierData($request);

        return view('dg.subordonnes.dga', array_merge([
            'subordonne' => $dga,
            'currentSubordonneId' => $dga?->id,
        ], $data));
    }

    public function assistante(Request $request): View
    {
        $entite = $this->getEntiteForDG();

        $assistante = null;
        if ($entite && $entite->assistante_agent_id) {
            $assistante = User::query()->where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
        }
        if (! $assistante) {
            $assistante = User::query()->where('role', 'Assistante_Dg')->where('is_active', true)->latest()->first();
            if ($assistante && $entite && $entite->assistante_agent_id !== $assistante->agent_id) {
                $entite->update(['assistante_agent_id' => $assistante->agent_id]);
            }
        }

        $data = $assistante
            ? $this->loadDossierData($request, $assistante)
            : $this->emptyDossierData($request);

        return view('dg.subordonnes.assistante', array_merge([
            'subordonne' => $assistante,
            'currentSubordonneId' => $assistante?->id,
        ], $data));
    }

    public function conseillers(Request $request): View
    {
        $conseillers = User::query()
            ->where('role', 'Conseillers_Dg')
            ->whereHas('agent', fn ($q) => $q->where('entite_id', $this->entiteId()))
            ->orderBy('name')
            ->get();

        $conseillerUserIds = $conseillers->pluck('id');

        // ── Filtres ──────────────────────────────────────────────────────────
        $tab           = $request->query('tab', 'objectifs');
        $conseillerId  = $request->query('conseiller_id') ? (int) $request->query('conseiller_id') : null;
        $statut        = (string) $request->query('statut', '');
        $annee         = (string) $request->query('annee', '');
        $search        = (string) $request->query('search', '');
        $sort          = $request->query('sort', 'date');
        $sortDir       = $request->query('sort_dir', 'desc');

        // ── Objectifs ────────────────────────────────────────────────────────
        $fichesBaseQ = fn () => FicheObjectif::where('assignable_type', User::class)
            ->whereIn('assignable_id', $conseillerUserIds);

        $fichesStats = [
            'total'      => $fichesBaseQ()->count(),
            'acceptees'  => $fichesBaseQ()->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesBaseQ()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $fichesBaseQ()->where('statut', 'refusee')->count(),
        ];

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->with('assignable')
            ->where('assignable_type', User::class)
            ->whereIn('assignable_id', $conseillerUserIds);

        if ($conseillerId) { $fichesQ->where('assignable_id', $conseillerId); }
        if ($search)       { $fichesQ->where('titre', 'like', "%{$search}%"); }
        if ($annee)        { $fichesQ->whereYear('date', $annee); }
        if ($statut) {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }
        $fichesQ->orderBy(['date' => 'date', 'statut' => 'statut'][$sort] ?? 'date', $sortDir === 'asc' ? 'asc' : 'desc');
        $fiches = $fichesQ->paginate(15)->withQueryString();

        // ── Évaluations ──────────────────────────────────────────────────────
        $evalsBaseQ = fn () => Evaluation::where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $conseillerUserIds);

        $evaluationsStats = [
            'total'     => $evalsBaseQ()->count(),
            'brouillon' => $evalsBaseQ()->where('statut', 'brouillon')->count(),
            'soumis'    => $evalsBaseQ()->where('statut', 'soumis')->count(),
            'valide'    => $evalsBaseQ()->where('statut', 'valide')->count(),
        ];

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification', 'evaluable'])
            ->where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $conseillerUserIds);

        if ($conseillerId) { $evalsQ->where('evaluable_id', $conseillerId); }
        if ($statut)       { $evalsQ->where('statut', $statut); }
        if ($annee)        { $evalsQ->whereYear('date_debut', $annee); }
        $evalsQ->orderBy(['date' => 'date_debut', 'statut' => 'statut'][$sort] ?? 'date_debut', $sortDir === 'asc' ? 'asc' : 'desc');
        $evaluations = $evalsQ->paginate(15)->withQueryString();

        // ── Feature flags ────────────────────────────────────────────────────
        $objectifsEnabled   = \App\Models\Setting::featureEnabled('objectifs')   && \Illuminate\Support\Facades\Auth::user()->can('objectifs.assigner');
        $evaluationsEnabled = \App\Models\Setting::featureEnabled('evaluations') && \Illuminate\Support\Facades\Auth::user()->can('evaluations.creer');

        // ── État fiche pour le conseiller filtré ─────────────────────────────
        $ficheBlocksNewForConseiller = $conseillerId
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $conseillerId)->whereNotIn('statut', ['refusee'])->exists()
            : false;
        $ficheAccepteeForConseiller = $conseillerId
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $conseillerId)->where('statut', 'acceptee')->exists()
            : false;
        $evaluationEnCoursForConseiller = $conseillerId
            ? Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $conseillerId)->whereIn('statut', ['soumis', 'brouillon'])->exists()
            : false;

        $filters = compact('tab', 'conseillerId', 'statut', 'annee', 'search', 'sort', 'sortDir');

        return view('dg.subordonnes.conseillers', compact(
            'conseillers', 'tab', 'filters',
            'fiches', 'fichesStats',
            'evaluations', 'evaluationsStats',
            'objectifsEnabled', 'evaluationsEnabled',
            'ficheBlocksNewForConseiller', 'ficheAccepteeForConseiller', 'evaluationEnCoursForConseiller'
        ));
    }

    public function conseiller(Request $request, User $user): View
    {
        if ((int) ($user->agent?->entite_id ?? 0) !== $this->entiteId() || $user->role !== 'Conseillers_Dg') {
            abort(403);
        }

        $data = $this->loadDossierData($request, $user);

        return view('dg.subordonnes.conseiller', array_merge([
            'subordonne' => $user,
            'currentSubordonneId' => $user->id,
        ], $data));
    }
}
