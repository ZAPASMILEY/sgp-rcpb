<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
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
        $availableYears = Annee::query()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->all();
        $selectedYear = $availableYears[0] ?? (int) now()->year;
        $requestedYear = (int) $request->query('annee', $selectedYear);

        if (in_array($requestedYear, $availableYears, true)) {
            $selectedYear = $requestedYear;
        }

        $selectedAnneeId = (int) (Annee::query()->where('annee', $selectedYear)->value('id') ?? 0);
        $directionIds = Direction::query()
            ->where('entite_id', $entite->id)
            ->pluck('id')
            ->all();

        $objectifsScopes = fn ($query) => $query
            ->where(function ($builder) use ($entite, $directionIds): void {
                $builder->where(function ($sub) use ($entite): void {
                    $sub->where('assignable_type', \App\Models\Entite::class)
                        ->where('assignable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->when($selectedAnneeId > 0, fn ($builder) => $builder->where('annee_id', $selectedAnneeId), fn ($builder) => $builder->whereYear('date', $selectedYear));

        $evaluationsScopes = fn ($query) => $query
            ->where(function ($builder) use ($entite, $directionIds): void {
                $builder->where(function ($sub) use ($entite): void {
                    $sub->where('evaluable_type', \App\Models\Entite::class)
                        ->where('evaluable_id', $entite->id);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->when($selectedAnneeId > 0, fn ($builder) => $builder->where('annee_id', $selectedAnneeId), fn ($builder) => $builder->whereYear('date_debut', $selectedYear));

        $objectifsEntiteCount = Objectif::query()
            ->where('assignable_type', \App\Models\Entite::class)
            ->where('assignable_id', $entite->id)
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date', $selectedYear))
            ->count();

        $objectifsDirecteursCount = Objectif::query()
            ->where('assignable_type', Direction::class)
            ->whereIn('assignable_id', $directionIds)
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date', $selectedYear))
            ->count();

        $evaluationsEntiteCount = Evaluation::query()
            ->where('evaluable_type', \App\Models\Entite::class)
            ->where('evaluable_id', $entite->id)
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date_debut', $selectedYear))
            ->count();

        $evaluationsDirecteursCount = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->whereIn('evaluable_id', $directionIds)
            ->where('evaluable_role', 'manager')
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date_debut', $selectedYear))
            ->count();

        $objectifsTotal = $objectifsEntiteCount + $objectifsDirecteursCount;

        $objectifsTermines = Objectif::query()
            ->tap($objectifsScopes)
            ->where('avancement_percentage', '>=', 100)
            ->count();

        $avancementMoyen = (int) round((float) Objectif::query()
            ->tap($objectifsScopes)
            ->avg('avancement_percentage'));

        $evaluationsByStatut = [
            'Brouillon' => Evaluation::query()
                ->tap($evaluationsScopes)
                ->where('statut', 'brouillon')
                ->count(),
            'Soumis' => Evaluation::query()
                ->tap($evaluationsScopes)
                ->where('statut', 'soumis')
                ->count(),
            'Valide' => Evaluation::query()
                ->tap($evaluationsScopes)
                ->where('statut', 'valide')
                ->count(),
        ];

        // Le DG est rattache a l'entite; on prend donc la meilleure note des evaluations de l'entite.
        $meilleureNoteDirecteurGeneral = (int) (Evaluation::query()
            ->where('evaluable_type', \App\Models\Entite::class)
            ->where('evaluable_id', $entite->id)
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date_debut', $selectedYear))
            ->max('note_finale') ?? 0);

        $evaluationsSoumises = Evaluation::query()
            ->tap($evaluationsScopes)
            ->where('statut', 'soumis')
            ->count();

        $evaluationsAcceptees = Evaluation::query()
            ->tap($evaluationsScopes)
            ->where('statut', 'valide')
            ->count();

        $evaluationsRejetees = Evaluation::query()
            ->tap($evaluationsScopes)
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
            'availableYears',
            'selectedYear',
        ));
    }
}
