<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Evaluation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * PersonnelEvaluationController — Évaluations reçues par le personnel
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Utilisé par les agents simples, les secrétaires, et tout rôle sous la
 * protection du middleware 'personnel'. Gère les évaluations reçues seulement.
 *
 * Une évaluation peut être reçue de deux façons :
 *   1. evaluable_type = Agent::class  → évaluée par un chef direct
 *   2. evaluable_type = User::class   → évaluée par un directeur ou DGA
 *      directement sur le compte User (cas fréquent pour les secrétaires)
 *
 * Actions disponibles :
 *   - show      : consulter le détail de l'évaluation (seulement si soumise)
 *   - statut    : accepter ou refuser une évaluation soumise
 *   - exportPdf : télécharger l'évaluation en PDF
 * ──────────────────────────────────────────────────────────────────────────────
 */
class PersonnelEvaluationController extends Controller
{
    // ── Autorisation ──────────────────────────────────────────────────────────

    /**
     * Vérifie que l'évaluation est bien adressée à l'utilisateur connecté.
     *
     * Accepte deux cas :
     *   a) evaluable_type = User::class  → directeur/DGA évalue directement
     *   b) evaluable_type = Agent::class → chef évalue via l'agent lié
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function authorizeEval(Evaluation $evaluation): void
    {
        $user  = Auth::user();
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;

        // Cas a : évaluation adressée directement au compte User
        $isForUser = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === $user->id;

        // Cas b : évaluation adressée via l'Agent lié au compte
        $isForAgent = $agent
            && $evaluation->evaluable_type === Agent::class
            && (int) $evaluation->evaluable_id === $agent->id;

        if (! $isForUser && ! $isForAgent) {
            abort(403, "Cette évaluation ne vous est pas adressée.");
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Affiche le détail d'une évaluation reçue.
     *
     * Une évaluation en brouillon n'est pas encore visible par l'évalué.
     * Charge les critères (objectifs + subjectifs) pour affichage complet.
     */
    public function show(Evaluation $evaluation): View
    {
        $this->authorizeEval($evaluation);

        // Le brouillon n'est pas encore accessible à l'évalué
        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'est pas encore disponible.");
        }

        // Charge toutes les relations nécessaires à l'affichage
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));

        // Séparation critères objectifs / subjectifs pour les tableaux de notation
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        // Données d'identification de la fiche d'évaluation
        $ident       = $evaluation->identification;
        $anneeEval   = $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
        $semestreEval= trim((string) ($ident?->semestre ?? ''));
        if ($semestreEval === '') {
            $semestreEval = $evaluation->date_debut->month <= 6 ? '1' : '2';
        }

        // Badge de statut
        $statusClass = match ($evaluation->statut) {
            'valide'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis'    => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default     => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide'    => 'Acceptée',
            'soumis'    => 'Soumise',
            'refuse'    => 'Refusée',
            'brouillon' => 'Brouillon',
            default     => ucfirst((string) $evaluation->statut),
        };

        // L'évalué peut accepter/refuser uniquement si l'évaluation est soumise
        $canValidate = $evaluation->statut === 'soumis';

        return view('personnel.evaluations.show', compact(
            'evaluation',
            'note',
            'mention',
            'objectiveCriteria',
            'subjectiveCriteria',
            'ident',
            'anneeEval',
            'semestreEval',
            'statusClass',
            'statusLabel',
            'canValidate',
        ));
    }

    /**
     * Accepte ou refuse une évaluation soumise.
     *
     * Seule une évaluation au statut 'soumis' peut être traitée.
     * Notifie l'évaluateur (chef ou directeur) du résultat.
     */
    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEval($evaluation);

        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', "Cette action n'est possible que sur une évaluation soumise.");
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action             = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        $evaluation->save();

        // Notification à l'évaluateur du résultat
        if ($evaluation->evaluateur_id) {
            $evalue      = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                (int) $evaluation->evaluateur_id,
                "Évaluation {$actionLabel}e",
                "{$evalue?->name} a {$actionLabel} la fiche d'évaluation que vous lui avez soumise.",
                $action === 'accepter' ? 'basse' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()
            ->route('personnel.evaluations.show', $evaluation)
            ->with('status', $msg);
    }

    /**
     * Exporte l'évaluation en PDF.
     *
     * Utilise le template PDF partagé (pdf.evaluation).
     * Non disponible si l'évaluation est encore en brouillon.
     */
    public function exportPdf(Evaluation $evaluation): \Illuminate\Http\Response
    {
        $this->authorizeEval($evaluation);

        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'est pas encore disponible.");
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres', 'evaluable']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel         = $evaluation->identification?->nom_prenom ?? Auth::user()?->name ?? '-';
        $cibleType          = 'Agent';

        // Cherche un template PDF existant (shared entre rôles)
        $pdfView = view()->exists('pdf.evaluation')
            ? 'pdf.evaluation'
            : 'dg.evaluations.pdf';

        $pdf = Pdf::loadView($pdfView, compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ))->setPaper('a4', 'portrait');

        $filename = 'evaluation-' . $evaluation->id . '.pdf';

        return $pdf->download($filename);
    }
}
