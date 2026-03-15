<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\Objectif;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entite = $request->user()->entite()->with('objectifs')->firstOrFail();
        $entiteId = $entite->id;

        $directions = Direction::query()
            ->where('entite_id', $entiteId)
            ->get();

        $directionIds = $directions->pluck('id')->all();

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
            'objectifsEntiteCount',
            'objectifsDirecteursCount',
            'evaluationsEntiteCount',
            'evaluationsDirecteursCount',
            'recentEvaluations',
            'objectifsPendingCount',
        ));
    }
}
