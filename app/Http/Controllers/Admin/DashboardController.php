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

        return view('admin.dashboard', [
            'caissesCount'       => Caisse::query()->count(),
            'agencesCount'       => Agence::query()->count(),
            'guichetsCount'      => Guichet::query()->count(),
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
        ]);
    }
}
