<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Annee;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\Service;
use App\Models\User;
use App\Services\EvaluationService;
use App\Traits\ResolvesEntite;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaNotesReseauController extends Controller
{
    use ResolvesEntite;

    public function __construct(private readonly EvaluationService $evaluationService) {}

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /**
     * IDs des utilisateurs dans le périmètre de supervision DGA :
     *  – Subordonnés directs (directeurs DT + secrétaire DGA)
     *  – Agents des services de la Direction DGA
     *  – Agents directement rattachés aux délégations techniques
     *  – Agents des services des délégations techniques
     *
     * Hors périmètre : caisses, agences, guichets.
     */
    private function perimetre(): Collection
    {
        $entite    = $this->getEntiteForDGA();
        $agentId   = Auth::user()?->agent_id;
        $direction = $agentId
            ? Direction::where('directeur_agent_id', $agentId)->first()
            : null;

        // 1. Agents directement rattachés à une délégation technique
        $agentIdsDt = Agent::whereNotNull('delegation_technique_id')
            ->whereNull('caisse_id')
            ->whereNull('agence_id')
            ->whereNull('guichet_id')
            ->pluck('id');

        // 2. Agents des services des délégations techniques
        $serviceIdsDt   = Service::whereNotNull('delegation_technique_id')->pluck('id');
        $agentIdsServDt = Agent::whereIn('service_id', $serviceIdsDt)->pluck('id');

        // 3. Agents des services de la Direction DGA
        $agentIdsServicesDga = collect();
        if ($direction) {
            $serviceIds = Service::where('direction_id', $direction->id)->pluck('id');
            $agentIdsServicesDga = Agent::whereIn('service_id', $serviceIds)->pluck('id');
        }

        // 4. Subordonnés directs (directeurs DT + secrétaire DGA)
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $secAgentId = $entite?->dga_secretaire_agent_id ? collect([$entite->dga_secretaire_agent_id]) : collect();

        $allAgentIds = $agentIdsDt
            ->merge($agentIdsServDt)
            ->merge($agentIdsServicesDga)
            ->merge($dtAgentIds)
            ->merge($secAgentId)
            ->unique();

        return User::whereIn('agent_id', $allAgentIds)->pluck('id');
    }

    private static function noteColor(?float $n): string
    {
        if ($n === null) {
            return 'bg-slate-100 text-slate-400';
        }

        return $n >= 8.5 ? 'bg-emerald-100 text-emerald-700'
             : ($n >= 7  ? 'bg-blue-100 text-blue-700'
             : ($n >= 5  ? 'bg-amber-100 text-amber-700'
             :              'bg-red-100 text-red-600'));
    }

    public function index(Request $request): View
    {
        $this->checkDga();

        $search   = trim((string) $request->get('search', ''));
        $statut   = trim((string) $request->get('statut', ''));
        $anneeId  = (int) $request->get('annee_id', 0);
        $delegId  = (int) $request->get('delegation_id', 0);
        $caisseId = (int) $request->get('caisse_id', 0);
        $sexe     = trim((string) $request->get('sexe', ''));
        $fonction = trim((string) $request->get('fonction', ''));

        $userIds = $this->perimetre();

        // ── Stats globales ───────────────────────────────────────────────────
        $baseStats = fn () => Evaluation::where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $userIds);

        $stats = [
            'total'     => $baseStats()->count(),
            'brouillon' => $baseStats()->where('statut', 'brouillon')->count(),
            'soumis'    => $baseStats()->where('statut', 'soumis')->count(),
            'valide'    => $baseStats()->where('statut', 'valide')->count(),
            'refuse'    => $baseStats()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $noteMoyenne = $baseStats()
            ->where('statut', 'valide')
            ->whereNotNull('note_finale')
            ->avg('note_finale');
        $noteMoyenne = $noteMoyenne ? round((float) $noteMoyenne, 2) : null;

        // ── Liste paginée ────────────────────────────────────────────────────
        $query = Evaluation::with(['evaluateur', 'identification', 'evaluable'])
            ->where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $userIds)
            ->orderByDesc('updated_at');

        if ($statut !== '') {
            $query->where('statut', $statut);
        }
        if ($anneeId) {
            $query->where('annee_id', $anneeId);
        }
        if ($search !== '') {
            $query->whereHas('identification', fn ($q) =>
                $q->where('nom_prenom', 'like', "%{$search}%")
                  ->orWhere('emploi', 'like', "%{$search}%")
                  ->orWhere('direction', 'like', "%{$search}%")
            );
        }
        if ($delegId) {
            $caisseIds  = Caisse::where('delegation_technique_id', $delegId)->pluck('id');
            $agenceIds  = Agence::whereIn('caisse_id', $caisseIds)->pluck('id');
            $serviceIds = Service::where('delegation_technique_id', $delegId)->pluck('id');

            $agentIdsInDt = Agent::where(function ($q) use ($delegId, $caisseIds, $agenceIds, $serviceIds): void {
                $q->where('delegation_technique_id', $delegId)
                  ->orWhereIn('caisse_id', $caisseIds)
                  ->orWhereIn('agence_id', $agenceIds)
                  ->orWhereIn('service_id', $serviceIds);
            })->pluck('id');

            $query->whereIn('evaluable_id', User::whereIn('agent_id', $agentIdsInDt)->pluck('id'));
        }
        if ($caisseId && ! $delegId) {
            $agenceIds  = Agence::where('caisse_id', $caisseId)->pluck('id');
            $serviceIds = Service::where('caisse_id', $caisseId)->pluck('id');

            $agentIdsInCaisse = Agent::where(function ($q) use ($caisseId, $agenceIds, $serviceIds): void {
                $q->where('caisse_id', $caisseId)
                  ->orWhereIn('agence_id', $agenceIds)
                  ->orWhereIn('service_id', $serviceIds);
            })->pluck('id');

            $query->whereIn('evaluable_id', User::whereIn('agent_id', $agentIdsInCaisse)->pluck('id'));
        }
        if ($sexe !== '') {
            $query->whereHas('evaluable', fn ($q) =>
                $q->whereHas('agent', fn ($qa) => $qa->where('sexe', $sexe))
            );
        }
        if ($fonction !== '') {
            $query->whereHas('identification', fn ($q) =>
                $q->where('emploi', $fonction)
            );
        }

        $evaluations = $query->get();

        $filterDelegations = DelegationTechnique::orderBy('region')->get();
        $filterCaisses     = $delegId
            ? Caisse::where('delegation_technique_id', $delegId)->orderBy('nom')->get()
            : Caisse::orderBy('nom')->get();
        $annees = Annee::orderByDesc('annee')->get();

        // Notes agrégées visibles uniquement pour une année clôturée
        $anneeSelectionnee = $anneeId ? Annee::find($anneeId) : null;
        $notesVisibles     = $anneeSelectionnee
            ? $anneeSelectionnee->statut === 'cloture'
            : ! Annee::hasOpenYear();

        $filters   = compact('search', 'statut', 'anneeId', 'delegId', 'caisseId', 'sexe', 'fonction');
        $fonctions = \App\Models\Agent::ROLES;
        $noteColor = fn (?float $n) => self::noteColor($n);

        return view('dga.notes-reseau.index', compact(
            'evaluations', 'stats', 'noteMoyenne',
            'filterDelegations', 'filterCaisses', 'annees',
            'filters', 'noteColor', 'notesVisibles', 'anneeSelectionnee',
            'fonctions'
        ));
    }
}

