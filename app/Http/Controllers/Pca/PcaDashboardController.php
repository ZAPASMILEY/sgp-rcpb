<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entite = $request->user()->entite()->with('objectifs')->firstOrFail();
        $entiteId = $entite->id;

        // Comptage optimisé des fiches d'objectifs DG par statut
        $fichesObjectifsDG = \App\Models\FicheObjectif::query()
            ->where('assignable_type', \App\Models\Entite::class)
            ->where('assignable_id', $entiteId)
            ->get();

        $fichesStatsDG = [
            'acceptées' => $fichesObjectifsDG->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesObjectifsDG->where('statut', 'en_attente')->count(),
            'refusées' => $fichesObjectifsDG->where('statut', 'refusee')->count(),
        ];

        $directions = Direction::query()
            ->where('entite_id', $entiteId)
            ->get();

        $directionsRattacheesCount = $directions->count();
        $directionIds = $directions->pluck('id')->all();

        $servicesRattachesCount = Service::query()
            ->whereIn('direction_id', $directionIds)
            ->count();

        $serviceIds = Service::query()
            ->whereIn('direction_id', $directionIds)
            ->pluck('id')
            ->all();

        $agentsRattachesCount = Agent::query()
            ->whereIn('service_id', $serviceIds)
            ->count();

        $personnelRattache = collect([
            [
                'fonction' => 'Directrice generale',
                'nom' => trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? '')),
                'icone' => 'fas fa-user-tie',
            ],
            [
                'fonction' => 'Assistante DG',
                'nom' => trim(($entite->assistante_dg_prenom ?? '').' '.($entite->assistante_dg_nom ?? '')),
                'icone' => 'fas fa-user',
            ],
            [
                'fonction' => 'DGA',
                'nom' => trim(($entite->dga_prenom ?? '').' '.($entite->dga_nom ?? '')),
                'icone' => 'fas fa-user-shield',
            ],
        ])->filter(fn (array $personne): bool => $personne['nom'] !== '')->values();

        $personnelRattacheCount = $personnelRattache->count();

        $objectifsEntiteCount = Objectif::query()
            ->where('assignable_type', \App\Models\Entite::class)
            ->where('assignable_id', $entiteId)
            ->count();

        $objectifsDirecteursCount = Objectif::query()
            ->where('assignable_type', Direction::class)
            ->whereIn('assignable_id', $directionIds)
            ->count();

        $evaluationsEntiteCount = Evaluation::query()
            ->where('evaluable_type', \App\Models\Entite::class)
            ->where('evaluable_id', $entiteId)
            ->count();

        $evaluationsDirecteursCount = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->whereIn('evaluable_id', $directionIds)
            ->where('evaluable_role', 'manager')
            ->count();

        $recentEvaluations = Evaluation::query()
            ->with('evaluable')
            ->where(function ($q) use ($entiteId, $directionIds): void {
                $q->where(function ($sub) use ($entiteId): void {
                    $sub->where('evaluable_type', \App\Models\Entite::class)
                        ->where('evaluable_id', $entiteId);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->latest()
            ->take(5)
            ->get();

        $objectifsPendingCount = Objectif::query()
            ->where(function ($q) use ($entiteId, $directionIds): void {
                $q->where(function ($sub) use ($entiteId): void {
                    $sub->where('assignable_type', \App\Models\Entite::class)
                        ->where('assignable_id', $entiteId);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->where('avancement_percentage', '<', 100)
            ->count();

        return view('pca.dashboard', compact(
            'entite',
            'directions',
            'directionsRattacheesCount',
            'servicesRattachesCount',
            'agentsRattachesCount',
            'personnelRattache',
            'personnelRattacheCount',
            'objectifsEntiteCount',
            'objectifsDirecteursCount',
            'evaluationsEntiteCount',
            'evaluationsDirecteursCount',
            'recentEvaluations',
            'objectifsPendingCount',
            'fichesStatsDG',
        ));
    }
}
