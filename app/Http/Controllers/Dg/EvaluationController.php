<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function show(Request $request, Evaluation $evaluation)
    {
        // Le DG peut ouvrir toute évaluation créée par le PCA
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        return view('dg.evaluations.show', compact('evaluation'));
    }

    public function statut(Request $request, Evaluation $evaluation)
    {
        // Le DG peut accepter/refuser toute évaluation affichée dans son espace
        $request->validate([
            'statut' => ['required', 'in:acceptee,refusee'],
            'commentaires_evalue' => ['nullable', 'string', 'max:2000'],
        ]);
        $evaluation->statut = $request->input('statut');
        // Si le DG accepte, on enregistre le commentaire fourni et on le rend non modifiable
        if ($request->input('statut') === 'acceptee') {
            $evaluation->commentaires_evalue = $request->input('commentaires_evalue');
        }
        $evaluation->save();
        return redirect()->route('dg.evaluations.show', $evaluation)
            ->with('status', 'Statut mis à jour.');
    }

    public function exportPdf(Request $request, Evaluation $evaluation)
    {
        // Sécurité : vérifier que l'utilisateur connecté est bien le DG concerné
        if ($evaluation->evaluable_type !== 'App\\Models\\User' && $evaluation->evaluable_type !== \App\Models\User::class) {
            abort(403);
        }
        if ($evaluation->evaluable_id !== $request->user()->id) {
            abort(403);
        }
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        // Attribution de la mention sur 10
        $mention = $this->mentionFromScore((float) $evaluation->note_finale);
        $cibleLabel = $evaluation->identification->nom_prenom ?? 'DG';
        $cibleType = 'Directeur Général';
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

    public function commentaire(Request $request, Evaluation $evaluation)
    {
        // Sécurité : vérifier que l'utilisateur connecté est bien le DG concerné
        if ($evaluation->evaluable_type !== 'App\\Models\\User' && $evaluation->evaluable_type !== \App\Models\User::class) {
            abort(403);
        }
        if ($evaluation->evaluable_id !== $request->user()->id) {
            abort(403);
        }
        $request->validate([
            'commentaires_evalue' => ['nullable', 'string', 'max:2000'],
        ]);
        $evaluation->commentaires_evalue = $request->input('commentaires_evalue');
        $evaluation->save();
        return redirect()->route('dg.evaluations.show', $evaluation)
            ->with('status', 'Commentaire enregistré.');
    }

    /**
     * Attribution d'une mention sur 10
     */
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
