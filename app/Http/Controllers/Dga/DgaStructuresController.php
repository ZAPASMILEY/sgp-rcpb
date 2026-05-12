<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\Service;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaStructuresController extends Controller
{
    use ResolvesEntite;

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    private function directionDga(): ?Direction
    {
        $agentId = Auth::user()?->agent_id;
        if (! $agentId) {
            return null;
        }

        return Direction::where('directeur_agent_id', $agentId)
            ->with(['services.chef'])
            ->first();
    }

    /**
     * Note moyenne validée + total pour un ensemble d'agent_ids.
     *
     * @param  Collection<int>  $agentIds
     */
    private function noteStats(Collection $agentIds): array
    {
        if ($agentIds->isEmpty()) {
            return ['moyenne' => null, 'total' => 0];
        }

        $userIds = User::whereIn('agent_id', $agentIds)->pluck('id');

        if ($userIds->isEmpty()) {
            return ['moyenne' => null, 'total' => 0];
        }

        $q = Evaluation::where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $userIds)
            ->where('statut', 'valide')
            ->whereNotNull('note_finale');

        $total   = $q->count();
        $moyenne = $total > 0 ? round((float) $q->avg('note_finale'), 2) : null;

        return compact('moyenne', 'total');
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

        // Toutes les DTs (pour les onglets)
        $delegations = DelegationTechnique::with(['directeur'])->orderBy('region')->get();

        // Onglet actif : 'services-dga' ou l'id d'une DT
        $tab = $request->get('tab', $delegations->first()?->id ?? 'services-dga');

        $direction    = $this->directionDga();
        $servicesDga  = [];
        $dtData       = null;

        // ── Onglet Services DGA ──────────────────────────────────────────────
        if ($tab === 'services-dga') {
            if ($direction) {
                $services = Service::where('direction_id', $direction->id)
                    ->with('chef')
                    ->get();

                foreach ($services as $svc) {
                    $agentIds = Agent::where('service_id', $svc->id)->pluck('id');
                    $servicesDga[] = [
                        'service'   => $svc,
                        'nbAgents'  => $agentIds->count(),
                        'note'      => $this->noteStats($agentIds),
                    ];
                }
            }
        }

        // ── Onglet DT (onglet = id de la délégation) ────────────────────────
        else {
            $dtId = (int) $tab;
            $dt   = DelegationTechnique::with(['directeur'])->find($dtId);

            if ($dt) {
                // Agents directement rattachés à la DT (hors caisses/agences/guichets)
                $dtAgentIds = Agent::where('delegation_technique_id', $dt->id)
                    ->whereNull('caisse_id')
                    ->whereNull('agence_id')
                    ->whereNull('guichet_id')
                    ->pluck('id');

                // Agents des services de la DT
                $servicesDtIds    = Service::where('delegation_technique_id', $dt->id)->pluck('id');
                $agentsServicesDt = Agent::whereIn('service_id', $servicesDtIds)->pluck('id');
                $dtAllAgents      = $dtAgentIds->merge($agentsServicesDt)->unique();

                // Services de la DT
                $servicesDt = Service::where('delegation_technique_id', $dt->id)
                    ->with('chef')
                    ->get()
                    ->map(function (Service $svc): array {
                        $agentIds = Agent::where('service_id', $svc->id)->pluck('id');

                        return [
                            'service'  => $svc,
                            'nbAgents' => $agentIds->count(),
                            'note'     => $this->noteStats($agentIds),
                        ];
                    });

                $dtData = [
                    'dt'       => $dt,
                    'note'     => $this->noteStats($dtAllAgents),
                    'services' => $servicesDt,
                ];
            }
        }

        $noteColor = fn (?float $n) => self::noteColor($n);

        return view('dga.structures.index', compact(
            'tab', 'delegations', 'direction',
            'servicesDga', 'dtData', 'noteColor'
        ));
    }
}
