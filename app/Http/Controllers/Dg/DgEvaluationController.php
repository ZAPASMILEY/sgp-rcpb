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
        $this->authorize('evaluations.accepter');
        $user     = $request->user();
        $entite   = Entite::query()->where('dg_agent_id', $user->agent_id)->first()
            ?? Entite::query()->latest()->first();
        $entiteId = (int) ($entite?->id ?? 0);
        $allowed =
            ($evaluation->evaluable_type === \App\Models\Entite::class && (int) $evaluation->evaluable_id === $entiteId)
            || ($evaluation->evaluable_type === get_class($user) && (int) $evaluation->evaluable_id === $user->id);
        if (! $allowed) {
            abort(403);
        }
        $request->validate([
            'action'      => ['required', 'in:accepter,refuser'],
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);
        $action = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        if ($action === 'refuser') {
            $evaluation->motif_refus        = $request->input('motif_refus');
            $evaluation->statut_reclamation = 'en_attente';
        }
        $evaluation->save();
        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';
        return redirect()->route('dg.evaluations.show', $evaluation)->with('status', $msg);
    }

    public function show(Request $request, Evaluation $evaluation): View
    {
        $this->authorize('evaluations.voir-reseau');
        $user     = $request->user();
        $entite   = Entite::query()->where('dg_agent_id', $user->agent_id)->first()
            ?? Entite::query()->latest()->first();
        $entiteId = (int) ($entite?->id ?? 0);

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
