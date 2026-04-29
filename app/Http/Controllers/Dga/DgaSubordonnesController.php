<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Traits\ResolvesEntite;
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
    public function index(): View
    {
        $this->checkDga();

        $entite = $this->getEntiteForDGA();

        // Directeurs techniques : users dont l'agent est référencé comme directeur d'une délégation
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')
            ->pluck('directeur_agent_id');

        $directeursTechniques = User::whereIn('agent_id', $dtAgentIds)
            ->where('role', 'Directeur_Technique')
            ->with('agent.directedDelegation')
            ->get();

        // Secrétaire du DGA
        $secretaire = $this->getDgaSecretaireUser($entite);

        return view('dga.subordonnes.index', compact('directeursTechniques', 'secretaire', 'entite'));
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

        $tab    = $request->get('tab', 'objectifs');
        $search = (string) $request->get('search', '');
        $statut = (string) $request->get('statut', '');

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

        $filters = compact('tab', 'search', 'statut');

        return view('dga.subordonnes.show', compact(
            'user', 'tab', 'fiches', 'fichesStats', 'evaluations', 'evaluationsStats', 'filters'
        ));
    }
}
