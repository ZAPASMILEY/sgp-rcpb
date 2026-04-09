<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DgEvaluationController extends Controller
{
    public function statut(Request $request, Evaluation $evaluation)
    {
        $user = $request->user();
        $entiteId = (int) ($user->pca_entite_id ?? 0);
        $allowed =
            ($evaluation->evaluable_type === \App\Models\Entite::class && (int) $evaluation->evaluable_id === $entiteId)
            || ($evaluation->evaluable_type === get_class($user) && (int) $evaluation->evaluable_id === $user->id);
        if (! $allowed) {
            abort(403);
        }
        $request->validate([
            'statut' => 'required|in:acceptee,refusee',
        ]);
        $evaluation->statut = $request->statut;
        $evaluation->save();
        return redirect()->route('dg.evaluations.show', $evaluation)->with('status', 'Statut mis à jour.');
    }

    public function show(Request $request, Evaluation $evaluation): View
    {
        $entiteId = (int) ($request->user()->pca_entite_id ?? 0);

        $allowed =
            ($evaluation->evaluable_type === Entite::class && (int) $evaluation->evaluable_id === $entiteId)
            || ($evaluation->evaluable_type === get_class($request->user()) && (int) $evaluation->evaluable_id === $request->user()->id);

        if (! $allowed) {
            abort(403);
        }

        $evaluation->load([
            'evaluable',
            'evaluateur',
            'identification',
            'criteres.sousCriteres',
        ]);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $ident = $evaluation->identification;
        $cibleLabel = $evaluation->evaluable?->nom ?? 'Entite';

        return view('dg.evaluations.show', compact(
            'evaluation',
            'ident',
            'subjectiveCriteria',
            'objectiveCriteria',
            'cibleLabel'
        ));
    }
}
