<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\DelegationTechnique;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $performanceGlobale = round((float) (Objectif::query()->avg('avancement_percentage') ?? 0), 1);

        $totalObjectifs  = Objectif::query()->count();
        $objectifsAtteints = Objectif::query()->where('avancement_percentage', 100)->count();

        $totalEvals     = Evaluation::query()->count();
        $evalsTerminees = Evaluation::query()->where('statut', 'valide')->count();
        $tauxCompletion = $totalEvals > 0 ? round(($evalsTerminees / $totalEvals) * 100, 1) : 0;

        $evaluationsEnRetard = Evaluation::query()
            ->where('date_fin', '<', Carbon::now())
            ->where('statut', '!=', 'valide')
            ->count();

        $directionDelegationMap = Direction::query()
            ->whereNotNull('delegation_technique_id')
            ->pluck('delegation_technique_id', 'id');

        $serviceDirectionMap = Service::query()
            ->whereNotNull('direction_id')
            ->pluck('direction_id', 'id');

        $agentServiceMap = Agent::query()
            ->whereNotNull('service_id')
            ->pluck('service_id', 'id');

        $delegationScores = [];

        Objectif::query()
            ->whereIn('assignable_type', [Agent::class, Service::class, Direction::class])
            ->get(['assignable_type', 'assignable_id', 'avancement_percentage'])
            ->each(function (Objectif $objectif) use (&$delegationScores, $directionDelegationMap, $serviceDirectionMap, $agentServiceMap): void {
                $delegationId = null;

                if ($objectif->assignable_type === Direction::class) {
                    $delegationId = $directionDelegationMap->get($objectif->assignable_id);
                }

                if ($objectif->assignable_type === Service::class) {
                    $directionId = $serviceDirectionMap->get($objectif->assignable_id);
                    $delegationId = $directionId ? $directionDelegationMap->get($directionId) : null;
                }

                if ($objectif->assignable_type === Agent::class) {
                    $serviceId = $agentServiceMap->get($objectif->assignable_id);
                    $directionId = $serviceId ? $serviceDirectionMap->get($serviceId) : null;
                    $delegationId = $directionId ? $directionDelegationMap->get($directionId) : null;
                }

                if (! $delegationId) {
                    return;
                }

                $delegationScores[$delegationId][] = (int) $objectif->avancement_percentage;
            });

        $delegationPerf = collect($delegationScores)
            ->map(fn (array $scores) => round(array_sum($scores) / count($scores), 1));

        $delegations = DelegationTechnique::query()
            ->withCount(['directions', 'services'])
            ->with(['directions' => fn ($q) => $q->select('id', 'delegation_technique_id', 'directeur_prenom', 'directeur_nom')->limit(1)])
            ->get()
            ->each(function ($delegation) use ($delegationPerf) {
                $delegation->performance = $delegationPerf[$delegation->id] ?? null;
            });

        $recentServices = Service::query()
            ->with('direction.delegationTechnique')
            ->latest()
            ->take(6)
            ->get();

        $secretaires = Direction::query()
            ->whereNotNull('secretaire_nom')
            ->with('delegationTechnique')
            ->latest()
            ->take(6)
            ->get();

        $monthlyLabels = [];
        $monthlyValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $month->locale('fr')->isoFormat('MMM');
            $monthlyValues[] = Evaluation::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return view('admin.dashboard', [
            'entitesCount'       => Entite::query()->count(),
            'directionsCount'    => Direction::query()->count(),
            'servicesCount'      => Service::query()->count(),
            'agentsCount'        => Agent::query()->count(),
            'objectifsCount'     => $totalObjectifs,
            'objectifsAtteints'  => $objectifsAtteints,
            'evaluationsCount'   => $totalEvals,
            'evaluationsEnRetard' => $evaluationsEnRetard,
            'performanceGlobale' => $performanceGlobale,
            'tauxCompletion'     => $tauxCompletion,
            'delegations'        => $delegations,
            'recentServices'     => $recentServices,
            'secretaires'        => $secretaires,
            'monthlyLabels'      => json_encode(array_values($monthlyLabels)),
            'monthlyValues'      => json_encode(array_values($monthlyValues)),
        ]);
    }
}