<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'entitesCount' => Entite::query()->count(),
            'directionsCount' => Direction::query()->count(),
            'servicesCount' => Service::query()->count(),
            'agentsCount' => Agent::query()->count(),
            'objectifsCount' => Objectif::query()->count(),
            'evaluationsCount' => Evaluation::query()->count(),
        ]);
    }
}