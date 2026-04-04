<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\Direction;
use App\Models\DelegationTechnique;
use App\Models\Entite;
use App\Models\Guichet;
use App\Models\LoginFailure;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $delegations = DelegationTechnique::query()
            ->withCount(['agences', 'caisses'])
            ->with(['directions' => fn ($q) => $q->select('id', 'delegation_technique_id', 'directeur_prenom', 'directeur_nom')->limit(1)])
            ->latest()
            ->take(6)
            ->get();

        $recentServices = Service::query()
            ->with('direction.delegationTechnique')
            ->latest()
            ->take(6)
            ->get();

        $recentAgents = Agent::query()
            ->with(['service.direction.delegationTechnique'])
            ->latest()
            ->take(6)
            ->get();

        $recentDirections = Direction::query()
            ->with(['delegationTechnique', 'services'])
            ->latest()
            ->take(6)
            ->get();

        $faitiereDirectionsCount = Direction::query()->whereNull('delegation_technique_id')->count();
        $delegationDirectionsCount = Direction::query()->whereNotNull('delegation_technique_id')->count();
        $servicesWithoutDirection = Service::query()->whereNull('direction_id')->count();
        $agentsWithoutService = Agent::query()->whereNull('service_id')->count();
        $secretairesCount = User::query()->where('role', 'secretaire')->count();
        $failedLoginAttemptsCount = LoginFailure::query()->count();
        $failedLoginAttemptsToday = LoginFailure::query()
            ->whereDate('attempted_at', today())
            ->count();
        $failedLoginEmailsCount = LoginFailure::query()
            ->whereNotNull('email')
            ->distinct('email')
            ->count('email');

        $recentLoginFailures = LoginFailure::query()
            ->latest('attempted_at')
            ->take(8)
            ->get();

        // Chart data: réseau distribution (donut)
        $reseauChart = [
            'labels' => ['Caisses', 'Agences', 'Guichets'],
            'series' => [
                Caisse::query()->count(),
                Agence::query()->count(),
                Guichet::query()->count(),
            ],
        ];

        // Chart data: delegations with counts (bar)
        $allDelegations = DelegationTechnique::query()
            ->withCount(['caisses', 'agences'])
            ->orderBy('region')
            ->get();
        $delegationsChart = [
            'categories' => $allDelegations->pluck('region')->all(),
            'caisses' => $allDelegations->pluck('caisses_count')->all(),
            'agences' => $allDelegations->pluck('agences_count')->all(),
        ];

        // Chart data: alertes 7 derniers jours (area)
        $alertsChart = ['categories' => [], 'series' => []];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $alertsChart['categories'][] = $day->translatedFormat('D d');
            $alertsChart['series'][] = LoginFailure::query()
                ->whereDate('attempted_at', $day->toDateString())
                ->count();
        }

        return view('admin.dashboard', [
            'caissesCount'       => $reseauChart['series'][0],
            'agencesCount'       => $reseauChart['series'][1],
            'guichetsCount'      => $reseauChart['series'][2],
            'entitesCount'       => Entite::query()->count(),
            'delegationsCount'   => DelegationTechnique::query()->count(),
            'directionsCount'    => Direction::query()->count(),
            'servicesCount'      => Service::query()->count(),
            'agentsCount'        => Agent::query()->count(),
            'secretairesCount'   => $secretairesCount,
            'faitiereDirectionsCount' => $faitiereDirectionsCount,
            'delegationDirectionsCount' => $delegationDirectionsCount,
            'servicesWithoutDirection' => $servicesWithoutDirection,
            'agentsWithoutService' => $agentsWithoutService,
            'failedLoginAttemptsCount' => $failedLoginAttemptsCount,
            'failedLoginAttemptsToday' => $failedLoginAttemptsToday,
            'failedLoginEmailsCount' => $failedLoginEmailsCount,
            'delegations'        => $delegations,
            'recentDirections'   => $recentDirections,
            'recentServices'     => $recentServices,
            'recentAgents'       => $recentAgents,
            'recentLoginFailures' => $recentLoginFailures,
            'reseauChart'        => $reseauChart,
            'delegationsChart'   => $delegationsChart,
            'alertsChart'        => $alertsChart,
        ]);
    }
}
