<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\Guichet;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgReseauController extends Controller
{
    private function checkDg(): void
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403);
        }
    }

    /**
     * Construit la requête d'évaluations pour une liste d'agent IDs avec filtres.
     */
    private function evalQuery(array $agentIds, Request $request): Builder
    {
        $search       = trim((string) $request->get('search', ''));
        $appreciation = trim((string) $request->get('appreciation', ''));
        $statut       = trim((string) $request->get('statut', ''));
        $sort         = $request->get('sort', 'note_desc');

        $query = Evaluation::query()
            ->with(['identification', 'evaluateur'])
            ->where('evaluable_type', Agent::class)
            ->whereIn('evaluable_id', $agentIds)
            ->where('statut', '!=', 'brouillon')
            ->whereHas('identification');

        if ($statut) {
            $query->where('statut', $statut);
        }

        match ($appreciation) {
            'excellent'   => $query->where('note_finale', '>=', 8.5),
            'bien'        => $query->whereBetween('note_finale', [7, 8.4999]),
            'passable'    => $query->whereBetween('note_finale', [5, 6.9999]),
            'insuffisant' => $query->where('note_finale', '<', 5),
            default       => null,
        };

        if ($search !== '') {
            $query->whereHas('identification', fn ($q) => $q->where(fn ($s) => $s
                ->where('nom_prenom', 'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%")
                ->orWhere('emploi', 'like', "%{$search}%")
            ));
        }

        match ($sort) {
            'note_asc'  => $query->orderBy('note_finale', 'asc'),
            'date_desc' => $query->orderByDesc('date_fin'),
            'date_asc'  => $query->orderBy('date_fin'),
            default     => $query->orderByDesc('note_finale'),
        };

        return $query;
    }

    /** Stats rapides sur un Builder (avant pagination) */
    private function evalStats(Builder $base): array
    {
        return [
            'total'       => (clone $base)->count(),
            'excellent'   => (clone $base)->where('note_finale', '>=', 8.5)->count(),
            'bien'        => (clone $base)->whereBetween('note_finale', [7, 8.4999])->count(),
            'passable'    => (clone $base)->whereBetween('note_finale', [5, 6.9999])->count(),
            'insuffisant' => (clone $base)->where('note_finale', '<', 5)->count(),
            'moyenne'     => round((float) (clone $base)->avg('note_finale'), 2),
        ];
    }

    // ── DÉLÉGATIONS ──────────────────────────────────────────────────────────

    public function delegations(Request $request): View
    {
        $this->checkDg();

        $search = trim((string) $request->get('search', ''));

        $query = DelegationTechnique::withCount(['caisses', 'agences', 'directions'])
            ->orderBy('region');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('region', 'like', "%{$search}%")
                ->orWhere('ville', 'like', "%{$search}%")
                ->orWhere('directeur_nom', 'like', "%{$search}%")
                ->orWhere('directeur_prenom', 'like', "%{$search}%")
            );
        }

        $delegations = $query->paginate(15)->withQueryString();

        return view('dg.reseau.delegations', compact('delegations', 'search'));
    }

    public function delegation(DelegationTechnique $delegation, Request $request): View
    {
        $this->checkDg();

        $delegation->load(['caisses.agences', 'directions.services']);

        $agentIds = Agent::where('delegation_technique_id', $delegation->id)->pluck('id')->all();

        $baseQuery  = $this->evalQuery($agentIds, $request);
        $stats      = $this->evalStats(clone $baseQuery);
        $evaluations = $baseQuery->paginate(20)->withQueryString();

        $filters = [
            'search'       => trim((string) $request->get('search', '')),
            'appreciation' => trim((string) $request->get('appreciation', '')),
            'statut'       => trim((string) $request->get('statut', '')),
            'sort'         => $request->get('sort', 'note_desc'),
        ];

        return view('dg.reseau.delegation-show', compact('delegation', 'evaluations', 'stats', 'filters'));
    }

    // ── CAISSES ───────────────────────────────────────────────────────────────

    public function caisses(Request $request): View
    {
        $this->checkDg();

        $search  = trim((string) $request->get('search', ''));
        $delegId = (int) $request->get('delegation', 0);

        $query = Caisse::with('delegationTechnique')
            ->withCount('agences')
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('directeur_nom', 'like', "%{$search}%")
                ->orWhere('directeur_prenom', 'like', "%{$search}%")
                ->orWhere('quartier', 'like', "%{$search}%")
            );
        }

        if ($delegId) {
            $query->where('delegation_technique_id', $delegId);
        }

        $caisses     = $query->paginate(15)->withQueryString();
        $delegations = DelegationTechnique::orderBy('region')->get();

        return view('dg.reseau.caisses', compact('caisses', 'delegations', 'search', 'delegId'));
    }

    public function caisse(Caisse $caisse, Request $request): View
    {
        $this->checkDg();

        $caisse->load(['delegationTechnique', 'agences']);

        $agenceIds = $caisse->agences()->pluck('id')->all();
        $agentIds  = Agent::whereIn('agence_id', $agenceIds)->pluck('id')->all();

        $baseQuery   = $this->evalQuery($agentIds, $request);
        $stats       = $this->evalStats(clone $baseQuery);
        $evaluations = $baseQuery->paginate(20)->withQueryString();

        $filters = [
            'search'       => trim((string) $request->get('search', '')),
            'appreciation' => trim((string) $request->get('appreciation', '')),
            'statut'       => trim((string) $request->get('statut', '')),
            'sort'         => $request->get('sort', 'note_desc'),
        ];

        return view('dg.reseau.caisse-show', compact('caisse', 'evaluations', 'stats', 'filters'));
    }

    // ── AGENCES ───────────────────────────────────────────────────────────────

    public function agences(Request $request): View
    {
        $this->checkDg();

        $search   = trim((string) $request->get('search', ''));
        $caisseId = (int) $request->get('caisse', 0);

        $query = Agence::with(['superviseurCaisse', 'delegationTechnique'])
            ->withCount(['agents', 'guichets'])
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('chef_nom', 'like', "%{$search}%")
            );
        }

        if ($caisseId) {
            $query->where('superviseur_caisse_id', $caisseId);
        }

        $agences = $query->paginate(15)->withQueryString();
        $caisses = Caisse::orderBy('nom')->get();

        return view('dg.reseau.agences', compact('agences', 'caisses', 'search', 'caisseId'));
    }

    public function agence(Agence $agence, Request $request): View
    {
        $this->checkDg();

        $agence->load(['superviseurCaisse', 'delegationTechnique', 'guichets']);

        $agentIds = Agent::where('agence_id', $agence->id)->pluck('id')->all();

        $baseQuery   = $this->evalQuery($agentIds, $request);
        $stats       = $this->evalStats(clone $baseQuery);
        $evaluations = $baseQuery->paginate(20)->withQueryString();

        $filters = [
            'search'       => trim((string) $request->get('search', '')),
            'appreciation' => trim((string) $request->get('appreciation', '')),
            'statut'       => trim((string) $request->get('statut', '')),
            'sort'         => $request->get('sort', 'note_desc'),
        ];

        return view('dg.reseau.agence-show', compact('agence', 'evaluations', 'stats', 'filters'));
    }

    // ── GUICHETS ──────────────────────────────────────────────────────────────

    public function guichets(Request $request): View
    {
        $this->checkDg();

        $search   = trim((string) $request->get('search', ''));
        $agenceId = (int) $request->get('agence', 0);

        $query = Guichet::with('agence.superviseurCaisse')
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('chef_nom', 'like', "%{$search}%")
            );
        }

        if ($agenceId) {
            $query->where('agence_id', $agenceId);
        }

        $guichets = $query->paginate(15)->withQueryString();
        $agences  = Agence::orderBy('nom')->get();

        return view('dg.reseau.guichets', compact('guichets', 'agences', 'search', 'agenceId'));
    }

    public function guichet(Guichet $guichet, Request $request): View
    {
        $this->checkDg();

        $guichet->load('agence.superviseurCaisse');

        // Les agents appartiennent à l'agence du guichet
        $agentIds = Agent::where('agence_id', $guichet->agence_id)->pluck('id')->all();

        $baseQuery   = $this->evalQuery($agentIds, $request);
        $stats       = $this->evalStats(clone $baseQuery);
        $evaluations = $baseQuery->paginate(20)->withQueryString();

        $filters = [
            'search'       => trim((string) $request->get('search', '')),
            'appreciation' => trim((string) $request->get('appreciation', '')),
            'statut'       => trim((string) $request->get('statut', '')),
            'sort'         => $request->get('sort', 'note_desc'),
        ];

        return view('dg.reseau.guichet-show', compact('guichet', 'evaluations', 'stats', 'filters'));
    }

    // ── EXPORTS PDF ───────────────────────────────────────────────────────────

    private function makePdf(array $agentIds, Request $request, string $entiteLabel, string $entiteNom): Response
    {
        $filters = [
            'search'       => trim((string) $request->get('search', '')),
            'appreciation' => trim((string) $request->get('appreciation', '')),
            'statut'       => trim((string) $request->get('statut', '')),
            'sort'         => $request->get('sort', 'note_desc'),
        ];

        $evaluations = $this->evalQuery($agentIds, $request)->get();

        $total = $evaluations->count();
        $stats = [
            'total'       => $total,
            'excellent'   => $evaluations->where('note_finale', '>=', 8.5)->count(),
            'bien'        => $evaluations->filter(fn ($e) => $e->note_finale >= 7 && $e->note_finale < 8.5)->count(),
            'passable'    => $evaluations->filter(fn ($e) => $e->note_finale >= 5 && $e->note_finale < 7)->count(),
            'insuffisant' => $evaluations->where('note_finale', '<', 5)->count(),
            'moyenne'     => $total > 0 ? round($evaluations->avg('note_finale'), 2) : 0,
        ];

        $pdf = Pdf::loadView('dg.reseau.personnel-pdf', compact(
            'evaluations', 'stats', 'filters', 'entiteLabel', 'entiteNom'
        ))->setPaper('a4', 'landscape');

        $slug = \Str::slug($entiteNom);

        return $pdf->download("personnel-{$slug}-".now()->format('Y-m-d').'.pdf');
    }

    public function delegationPdf(DelegationTechnique $delegation, Request $request): Response
    {
        $this->checkDg();
        $agentIds = Agent::where('delegation_technique_id', $delegation->id)->pluck('id')->all();
        return $this->makePdf($agentIds, $request, 'Délégation Technique', $delegation->region);
    }

    public function caissePdf(Caisse $caisse, Request $request): Response
    {
        $this->checkDg();
        $agenceIds = $caisse->agences()->pluck('id')->all();
        $agentIds  = Agent::whereIn('agence_id', $agenceIds)->pluck('id')->all();
        return $this->makePdf($agentIds, $request, 'Caisse Populaire', $caisse->nom);
    }

    public function agencePdf(Agence $agence, Request $request): Response
    {
        $this->checkDg();
        $agentIds = Agent::where('agence_id', $agence->id)->pluck('id')->all();
        return $this->makePdf($agentIds, $request, 'Agence', $agence->nom);
    }

    public function guichetPdf(Guichet $guichet, Request $request): Response
    {
        $this->checkDg();
        $guichet->load('agence');
        $agentIds = Agent::where('agence_id', $guichet->agence_id)->pluck('id')->all();
        return $this->makePdf($agentIds, $request, 'Guichet', $guichet->nom);
    }
}
