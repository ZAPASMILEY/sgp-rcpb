<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function show(Request $request, Evaluation $evaluation)
    {
        // Le DG ne peut voir l'évaluation qu'une fois soumise ou validée
        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'a pas encore été soumise.");
        }
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        return view('dg.evaluations.show', compact('evaluation'));
    }

    public function exportPdf(Request $request, Evaluation $evaluation)
    {
        if ($evaluation->evaluable_type !== User::class) {
            abort(403);
        }
        if ((int) $evaluation->evaluable_id !== (int) $request->user()->id) {
            abort(403);
        }
        // Pas encore soumise : le DG ne peut pas y accéder
        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'a pas encore été soumise.");
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $mention    = $this->mentionFromScore((float) $evaluation->note_finale);
        $cibleLabel = $evaluation->identification->nom_prenom ?? 'DG';
        $cibleType  = 'Directeur Général';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation',
            'subjectiveCriteria',
            'objectiveCriteria',
            'mention',
            'cibleLabel',
            'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-dg.pdf');
    }

    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->evaluable_type !== User::class) {
            abort(403);
        }
        if ((int) $evaluation->evaluable_id !== (int) $request->user()->id) {
            abort(403);
        }
        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        $evaluation->save();

        // Notifier le PCA (évaluateur)
        if ($evaluation->evaluateur_id) {
            $dg = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                (int) $evaluation->evaluateur_id,
                "Fiche d'évaluation {$actionLabel}e par le DG",
                "Le DG {$dg?->name} a {$actionLabel} la fiche d'évaluation que vous lui avez soumise.",
                $action === 'accepter' ? 'moyenne' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()->route('dg.evaluations.show', $evaluation)->with('status', $msg);
    }

    public function commentaire(Request $request, Evaluation $evaluation)
    {
        // Seul le DG évalué peut saisir son commentaire
        if ($evaluation->evaluable_type !== User::class) {
            abort(403);
        }
        if ((int) $evaluation->evaluable_id !== (int) $request->user()->id) {
            abort(403);
        }
        // Pas encore soumise : le DG ne peut pas y accéder
        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'a pas encore été soumise.");
        }
        // Verrouillé une fois l'évaluation validée
        if ($evaluation->statut === 'valide') {
            return redirect()->route('dg.evaluations.show', $evaluation)
                ->with('status', "L'évaluation est validée, le commentaire ne peut plus être modifié.");
        }

        $request->validate([
            'commentaires_evalue' => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluation->commentaires_evalue = $request->input('commentaires_evalue');
        $evaluation->save();

        return redirect()->route('dg.evaluations.show', $evaluation)
            ->with('status', 'Votre commentaire a été enregistré.');
    }

    private function mentionFromScore(float $score): string
    {
        if ($score < 5) {
            return 'Insuffisant';
        }
        if ($score < 7) {
            return 'Passable';
        }
        if ($score < 8.5) {
            return 'Bien';
        }
        return 'Excellent';
    }
}
