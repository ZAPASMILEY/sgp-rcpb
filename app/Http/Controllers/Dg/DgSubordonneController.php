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
                ->orWhere('annee', 'like', "%{$search}%"));
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

        $filters = compact('tab', 'search', 'statut');

        return compact('tab', 'fiches', 'fichesStats', 'evaluations', 'evaluationsStats', 'filters');
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
        $dga = ($entite && $entite->dga_agent_id)
            ? User::query()->where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first()
            : null;

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
        $assistante = ($entite && $entite->assistante_agent_id)
            ? User::query()->where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first()
            : null;

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
            ->get();

        return view('dg.subordonnes.conseillers', compact('conseillers'));
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
