<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\Guichet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaReseauController extends Controller
{
    /** Rôles de la faîtière — exclus des stats de notes réseau. */
    private const FAITIERE_ROLES = ['DG', 'DGA', 'PCA', 'Assistante_Dg', 'Conseillers_Dg'];

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /**
     * Calcule la note moyenne des évaluations validées pour une liste d'agent IDs,
     * en excluant les agents dont l'utilisateur associé est de la faîtière.
     *
     * @param  int[]  $agentIds
     * @return array{moyenne: float|null, total: int}
     */
    private function noteStats(array $agentIds): array
    {
        if (empty($agentIds)) {
            return ['moyenne' => null, 'total' => 0];
        }

        // Exclure les agents liés à des users faîtière
        $faitiereAgentIds = User::whereIn('role', self::FAITIERE_ROLES)
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->all();

        $filteredIds = array_diff($agentIds, $faitiereAgentIds);

        if (empty($filteredIds)) {
            return ['moyenne' => null, 'total' => 0];
        }

        $evals = Evaluation::query()
            ->where('evaluable_type', Agent::class)
            ->whereIn('evaluable_id', $filteredIds)
            ->where('statut', 'valide')
            ->whereNotNull('note_finale');

        $total   = $evals->count();
        $moyenne = $total > 0 ? round((float) $evals->avg('note_finale'), 2) : null;

        return compact('moyenne', 'total');
    }

    /** Retourne le badge HTML d'une note (ou —). */
    private static function noteBadge(?float $moyenne): string
    {
        if ($moyenne === null) {
            return '<span class="text-xs font-bold text-slate-300">—</span>';
        }
        $color = $moyenne >= 8.5 ? 'bg-emerald-100 text-emerald-700'
               : ($moyenne >= 7  ? 'bg-blue-100 text-blue-700'
               : ($moyenne >= 5  ? 'bg-amber-100 text-amber-700'
               :                   'bg-rose-100 text-rose-700'));
        return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold '.$color.'">'.number_format($moyenne, 2).'</span>';
    }

    // ── DÉLÉGATIONS ───────────────────────────────────────────────────────────

    public function delegations(Request $request): View
    {
        $this->checkDga();

        $search = trim((string) $request->get('search', ''));

        $query = DelegationTechnique::withCount(['agences', 'caisses'])
            ->with('directeur')
            ->orderBy('region');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('region', 'like', "%{$search}%")
                ->orWhere('ville', 'like', "%{$search}%")
            );
        }

        $delegations = $query->paginate(15)->withQueryString();

        // Calcul des notes par délégation
        $delegationNotes = [];
        foreach ($delegations as $d) {
            $agentIds = Agent::where('delegation_technique_id', $d->id)->pluck('id')->all();
            $delegationNotes[$d->id] = $this->noteStats($agentIds);
        }

        return view('dga.reseau.delegations', compact('delegations', 'search', 'delegationNotes'));
    }

    public function delegation(DelegationTechnique $delegation, Request $request): View
    {
        $this->checkDga();

        $delegation->load(['directeur', 'secretaire', 'caisses', 'agences']);

        $agentIds  = Agent::where('delegation_technique_id', $delegation->id)->pluck('id')->all();
        $noteStats = $this->noteStats($agentIds);

        return view('dga.reseau.delegation-show', compact('delegation', 'agentIds', 'noteStats'));
    }

    // ── CAISSES ───────────────────────────────────────────────────────────────

    public function caisses(Request $request): View
    {
        $this->checkDga();

        $search  = trim((string) $request->get('search', ''));
        $delegId = (int) $request->get('delegation', 0);

        $query = Caisse::with(['delegationTechnique'])
            ->withCount('agences')
            ->orderBy('nom');

        if ($search !== '') {
            $query->where('nom', 'like', "%{$search}%");
        }
        if ($delegId) {
            $query->where('delegation_technique_id', $delegId);
        }

        $caisses     = $query->paginate(15)->withQueryString();
        $delegations = DelegationTechnique::orderBy('region')->get();

        $caisseNotes = [];
        foreach ($caisses as $c) {
            $agenceIds = $c->agences()->pluck('id')->all();
            $agentIds  = Agent::whereIn('agence_id', $agenceIds)->pluck('id')->all();
            $caisseNotes[$c->id] = $this->noteStats($agentIds);
        }

        return view('dga.reseau.caisses', compact('caisses', 'delegations', 'search', 'delegId', 'caisseNotes'));
    }

    public function caisse(Caisse $caisse): View
    {
        $this->checkDga();

        $caisse->load(['delegationTechnique', 'agences']);

        $agenceIds = $caisse->agences()->pluck('id')->all();
        $agentIds  = Agent::whereIn('agence_id', $agenceIds)->pluck('id')->all();
        $noteStats = $this->noteStats($agentIds);

        return view('dga.reseau.caisse-show', compact('caisse', 'noteStats'));
    }

    // ── AGENCES ───────────────────────────────────────────────────────────────

    public function agences(Request $request): View
    {
        $this->checkDga();

        $search   = trim((string) $request->get('search', ''));
        $caisseId = (int) $request->get('caisse', 0);

        $query = Agence::with(['caisse', 'delegationTechnique'])
            ->withCount(['agents', 'guichets'])
            ->orderBy('nom');

        if ($search !== '') {
            $query->where('nom', 'like', "%{$search}%");
        }
        if ($caisseId) {
            $query->where('caisse_id', $caisseId);
        }

        $agences = $query->paginate(15)->withQueryString();
        $caisses = Caisse::orderBy('nom')->get();

        $agenceNotes = [];
        foreach ($agences as $a) {
            $agentIds = Agent::where('agence_id', $a->id)->pluck('id')->all();
            $agenceNotes[$a->id] = $this->noteStats($agentIds);
        }

        return view('dga.reseau.agences', compact('agences', 'caisses', 'search', 'caisseId', 'agenceNotes'));
    }

    public function agence(Agence $agence): View
    {
        $this->checkDga();

        $agence->load(['caisse', 'delegationTechnique', 'guichets']);

        $agentIds  = Agent::where('agence_id', $agence->id)->pluck('id')->all();
        $noteStats = $this->noteStats($agentIds);

        return view('dga.reseau.agence-show', compact('agence', 'noteStats'));
    }

    // ── GUICHETS ──────────────────────────────────────────────────────────────

    public function guichets(Request $request): View
    {
        $this->checkDga();

        $search   = trim((string) $request->get('search', ''));
        $agenceId = (int) $request->get('agence', 0);

        $query = Guichet::with('agence.caisse')->orderBy('nom');

        if ($search !== '') {
            $query->where('nom', 'like', "%{$search}%");
        }
        if ($agenceId) {
            $query->where('agence_id', $agenceId);
        }

        $guichets = $query->paginate(15)->withQueryString();
        $agences  = Agence::orderBy('nom')->get();

        $guichetNotes = [];
        foreach ($guichets as $g) {
            $agentIds = Agent::where('agence_id', $g->agence_id)->pluck('id')->all();
            $guichetNotes[$g->id] = $this->noteStats($agentIds);
        }

        return view('dga.reseau.guichets', compact('guichets', 'agences', 'search', 'agenceId', 'guichetNotes'));
    }

    public function guichet(Guichet $guichet): View
    {
        $this->checkDga();

        $guichet->load('agence.caisse');

        $agentIds  = Agent::where('agence_id', $guichet->agence_id)->pluck('id')->all();
        $noteStats = $this->noteStats($agentIds);

        return view('dga.reseau.guichet-show', compact('guichet', 'noteStats'));
    }
}
