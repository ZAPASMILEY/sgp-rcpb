<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Evaluation;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct(private readonly EvaluationService $evaluationService) {}
    public function show(Request $request, Evaluation $evaluation)
    {
        $this->authorize('evaluations.voir-propres');
        // Le DG ne peut voir l'évaluation qu'une fois soumise ou validée
        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'a pas encore été soumise.");
        }
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        return view('dg.evaluations.show', compact('evaluation'));
    }

    public function exportPdf(Request $request, Evaluation $evaluation)
    {
        $this->authorize('evaluations.exporter-pdf');
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
        $mention    = $this->evaluationService->mention((float) $evaluation->note_finale);
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
        $this->authorize('evaluations.accepter');
        if ($evaluation->evaluable_type !== User::class) {
            abort(403);
        }
        if ((int) $evaluation->evaluable_id !== (int) $request->user()->id) {
            abort(403);
        }
        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
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

    public function reclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.voir-propres');
        if ($evaluation->evaluable_type !== User::class) {
            abort(403);
        }
        if ((int) $evaluation->evaluable_id !== (int) $request->user()->id) {
            abort(403);
        }
        if ($evaluation->statut !== 'refuse') {
            return back()->with('error', "La réclamation n'est possible que sur une évaluation refusée.");
        }

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->reclamation = $request->input('reclamation');
        $evaluation->save();

        return redirect()->route('dg.evaluations.show', $evaluation)
            ->with('status', 'Votre réclamation a été enregistrée.');
    }

    public function commentaire(Request $request, Evaluation $evaluation)
    {
        $this->authorize('evaluations.voir-propres');
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

}
