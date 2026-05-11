<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Contracts\View\View;

class RhEvaluationController extends Controller
{
    /**
     * Affiche le détail d'une évaluation en lecture seule pour le RH.
     * Le RH peut voir toutes les évaluations (agents et cadres).
     */
    public function show(Evaluation $evaluation): View
    {
        $evaluation->load([
            'evaluateur',
            'evaluable',
            'identification',
            'criteres.sousCriteres',
        ]);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif');
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif');
        $ident              = $evaluation->identification ?? null;

        return view('rh.evaluations.show', compact(
            'evaluation',
            'objectiveCriteria',
            'subjectiveCriteria',
            'ident',
        ));
    }
}
