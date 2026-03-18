<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\Objectif;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaStatistiqueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entite = $request->user()->entite()->firstOrFail();
        $directionIds = Direction::query()
            ->where('entite_id', $entite->id)
            ->pluck('id')
            ->all();

        $objectifsEntiteCount = Objectif::query()
            ->where('assignable_type', \App\Models\Entite::class)
            ->where('assignable_id', $entite->id)
            ->count();

        $objectifsDirecteursCount = Objectif::query()
            ->where('assignable_type', Direction::class)
            ->whereIn('assignable_id', $directionIds)
            ->count();

        $evaluationsEntiteCount = Evaluation::query()
            ->where('evaluable_type', \App\Models\Entite::class)
            ->where('evaluable_id', $entite->id)
            ->count();

        $evaluationsDirecteursCount = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->whereIn('evaluable_id', $directionIds)
            ->where('evaluable_role', 'manager')
            ->count();

        $objectifsTotal = $objectifsEntiteCount + $objectifsDirecteursCount;

        $objectifsTermines = Objectif::query()
            ->where(function ($query) use ($entite, $directionIds): void {
                $query->where(function ($sub) use ($entite): void {
                    $sub->where('assignable_type', \App\Models\Entite::class)
                        ->where('assignable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->where('avancement_percentage', '>=', 100)
            ->count();

        $avancementMoyen = (int) round((float) Objectif::query()
            ->where(function ($query) use ($entite, $directionIds): void {
                $query->where(function ($sub) use ($entite): void {
                    $sub->where('assignable_type', \App\Models\Entite::class)
                        ->where('assignable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->avg('avancement_percentage'));

        $evaluationsByStatut = [
            'Brouillon' => Evaluation::query()
                ->where(function ($query) use ($entite, $directionIds): void {
                    $query->where(function ($sub) use ($entite): void {
                        $sub->where('evaluable_type', \App\Models\Entite::class)
                            ->where('evaluable_id', $entite->id);
                    })->orWhere(function ($sub) use ($directionIds): void {
                        $sub->where('evaluable_type', Direction::class)
                            ->whereIn('evaluable_id', $directionIds)
                            ->where('evaluable_role', 'manager');
                    });
                })
                ->where('statut', 'brouillon')
                ->count(),
            'Soumis' => Evaluation::query()
                ->where(function ($query) use ($entite, $directionIds): void {
                    $query->where(function ($sub) use ($entite): void {
                        $sub->where('evaluable_type', \App\Models\Entite::class)
                            ->where('evaluable_id', $entite->id);
                    })->orWhere(function ($sub) use ($directionIds): void {
                        $sub->where('evaluable_type', Direction::class)
                            ->whereIn('evaluable_id', $directionIds)
                            ->where('evaluable_role', 'manager');
                    });
                })
                ->where('statut', 'soumis')
                ->count(),
            'Valide' => Evaluation::query()
                ->where(function ($query) use ($entite, $directionIds): void {
                    $query->where(function ($sub) use ($entite): void {
                        $sub->where('evaluable_type', \App\Models\Entite::class)
                            ->where('evaluable_id', $entite->id);
                    })->orWhere(function ($sub) use ($directionIds): void {
                        $sub->where('evaluable_type', Direction::class)
                            ->whereIn('evaluable_id', $directionIds)
                            ->where('evaluable_role', 'manager');
                    });
                })
                ->where('statut', 'valide')
                ->count(),
        ];

        // Le DG est rattache a l'entite; on prend donc la meilleure note des evaluations de l'entite.
        $meilleureNoteDirecteurGeneral = (int) (Evaluation::query()
            ->where('evaluable_type', \App\Models\Entite::class)
            ->where('evaluable_id', $entite->id)
            ->max('note_finale') ?? 0);

        $evaluationsSoumises = Evaluation::query()
            ->where(function ($query) use ($entite, $directionIds): void {
                $query->where(function ($sub) use ($entite): void {
                    $sub->where('evaluable_type', \App\Models\Entite::class)
                        ->where('evaluable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->where('statut', 'soumis')
            ->count();

        $evaluationsAcceptees = Evaluation::query()
            ->where(function ($query) use ($entite, $directionIds): void {
                $query->where(function ($sub) use ($entite): void {
                    $sub->where('evaluable_type', \App\Models\Entite::class)
                        ->where('evaluable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->where('statut', 'valide')
            ->count();

        $evaluationsRejetees = Evaluation::query()
            ->where(function ($query) use ($entite, $directionIds): void {
                $query->where(function ($sub) use ($entite): void {
                    $sub->where('evaluable_type', \App\Models\Entite::class)
                        ->where('evaluable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->whereIn('statut', ['rejete', 'rejetee'])
            ->count();

        return view('pca.statistiques.index', compact(
            'entite',
            'objectifsEntiteCount',
            'objectifsDirecteursCount',
            'evaluationsEntiteCount',
            'evaluationsDirecteursCount',
            'objectifsTotal',
            'objectifsTermines',
            'avancementMoyen',
            'evaluationsByStatut',
            'meilleureNoteDirecteurGeneral',
            'evaluationsSoumises',
            'evaluationsAcceptees',
            'evaluationsRejetees',
        ));
    }
}
