<?php

namespace App\Http\Controllers\Subordonne;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubordonneEvaluationController extends Controller
{
    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function show(Evaluation $evaluation): View
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }

        // L'utilisateur ne peut voir que ses propres evaluations
        if ($evaluation->evaluable_type !== User::class || (int) $evaluation->evaluable_id !== $user->id) {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $note     = (float) $evaluation->note_finale;
        $mention  = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));

        $identification  = $evaluation->identification;
        $anneeEval       = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestreEval    = trim((string) ($identification?->semestre ?? ''));
        if ($semestreEval === '') {
            $semestreEval = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }
        $periodeLabel    = $anneeEval.' - Semestre '.$semestreEval;
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        return view('subordonne.evaluations.show', compact(
            'evaluation',
            'user',
            'mention',
            'periodeLabel',
            'objectiveCriteria',
            'subjectiveCriteria',
        ));
    }
}
