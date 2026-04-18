<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaEvaluationController extends Controller
{
    private function authorize(Evaluation $evaluation): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'DGA') {
            abort(403);
        }
        if ($evaluation->evaluable_type !== User::class || (int) $evaluation->evaluable_id !== $user->id) {
            abort(403);
        }
    }

    public function show(Evaluation $evaluation): View
    {
        $this->authorize($evaluation);
        $user = Auth::user();

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));

        $identification = $evaluation->identification;
        $anneeEval      = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestreEval   = trim((string) ($identification?->semestre ?? ''));
        if ($semestreEval === '') {
            $semestreEval = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }
        $periodeLabel       = $anneeEval.' - Semestre '.$semestreEval;
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        $statusClass = match ($evaluation->statut) {
            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse' => 'border-rose-200 bg-rose-50 text-rose-700',
            default  => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide' => 'Acceptee',
            'soumis' => 'Soumise',
            'refuse' => 'Refusee',
            default  => 'Brouillon',
        };

        return view('dga.evaluations.show', compact(
            'evaluation',
            'user',
            'mention',
            'periodeLabel',
            'objectiveCriteria',
            'subjectiveCriteria',
            'statusClass',
            'statusLabel',
        ));
    }

    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize($evaluation);

        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $evaluation->statut = $request->input('action') === 'accepter' ? 'valide' : 'refuse';
        $evaluation->save();

        $msg = $request->input('action') === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()->route('dga.evaluations.show', $evaluation)->with('status', $msg);
    }

    public function commentaire(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize($evaluation);

        if ($evaluation->statut === 'valide') {
            return redirect()->route('dga.evaluations.show', $evaluation)
                ->with('status', "L'évaluation est validée, le commentaire ne peut plus être modifié.");
        }

        $request->validate([
            'commentaires_evalue' => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluation->commentaires_evalue = $request->input('commentaires_evalue');
        $evaluation->save();

        return redirect()->route('dga.evaluations.show', $evaluation)
            ->with('status', 'Votre commentaire a été enregistré.');
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $this->authorize($evaluation);
        $user = Auth::user();

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note       = (float) $evaluation->note_finale;
        $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel = $evaluation->identification->nom_prenom ?? $user->name;
        $cibleType  = 'Directeur Général Adjoint';

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-dga.pdf');
    }
}
