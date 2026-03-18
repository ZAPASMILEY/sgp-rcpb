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

class StatistiqueController extends Controller
{
    public function __invoke(): View
    {
        $entitesCount = Entite::query()->count();
        $directionsCount = Direction::query()->count();
        $servicesCount = Service::query()->count();
        $agentsCount = Agent::query()->count();
        $objectifsCount = Objectif::query()->count();
        $evaluationsCount = Evaluation::query()->count();

        $evaluationsByStatut = [
            'Brouillon' => Evaluation::query()->where('statut', 'brouillon')->count(),
            'Soumis' => Evaluation::query()->where('statut', 'soumis')->count(),
            'Valide' => Evaluation::query()->where('statut', 'valide')->count(),
        ];

        $objectifsTermines = Objectif::query()->where('avancement_percentage', '>=', 100)->count();
        $objectifsEnCours = max(0, $objectifsCount - $objectifsTermines);

        $avancementMoyen = (int) round((float) Objectif::query()->avg('avancement_percentage'));

        return view('admin.statistiques.index', compact(
            'entitesCount',
            'directionsCount',
            'servicesCount',
            'agentsCount',
            'objectifsCount',
            'evaluationsCount',
            'evaluationsByStatut',
            'objectifsTermines',
            'objectifsEnCours',
            'avancementMoyen',
        ));
    }
}
