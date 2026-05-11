<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Services\ObjectifService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurObjectifController — Objectifs reçus par le directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Gère les fiches d'objectifs que le DG / la PCA assigne au directeur lui-même
 * (assignable = entité du directeur : Direction, Caisse ou DelegationTechnique).
 *
 * Le directeur peut :
 *  • Consulter le détail d'une fiche d'objectifs (show)
 *  • Accepter ou refuser une fiche en attente (statut)
 *
 * Il ne peut PAS créer de fiches pour lui-même ; c'est le DG ou la PCA qui
 * les crée et les lui assigne.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurObjectifController extends Controller
{
    public function __construct(private readonly ObjectifService $objectifService) {}

    /**
     * Résout et retourne le contexte du directeur connecté.
     * Déclenche un 403 si l'utilisateur n'est pas lié à une entité.
     */
    private function getContext(): DirecteurEntity
    {
        return DirecteurEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que la fiche appartient au directeur connecté.
     *
     * Accepte deux cas :
     *  a) Assignée à l'entité du directeur (DG / PCA → Direction / Caisse / DelegationTechnique)
     *  b) Assignée directement au User (DGA → Directeur_Technique ou secrétaire)
     */
    private function ficheAppartientAuDirecteur(FicheObjectif $fiche, DirecteurEntity $ctx): bool
    {
        // Cas a : entité
        if ($fiche->assignable_type === $ctx->modelClass && (int) $fiche->assignable_id === $ctx->getId()) {
            return true;
        }
        // Cas b : utilisateur (DGA assigne au User directement)
        if ($fiche->assignable_type === \App\Models\User::class && (int) $fiche->assignable_id === Auth::id()) {
            return true;
        }
        return false;
    }

    /**
     * Affiche le détail d'une fiche d'objectifs reçue par le directeur.
     *
     * Vérifie que la fiche est bien assignée à l'entité du directeur connecté
     * (contrôle du type polymorphique et de l'id) avant d'afficher.
     */
    public function show(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $ctx = $this->getContext();

        // Sécurité : la fiche doit appartenir au directeur connecté.
        if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
            abort(403);
        }

        // Charge les objectifs de la fiche en une seule requête (évite N+1).
        $fiche->load('objectifs');

        // Classe CSS et libellé du badge de statut pour la vue Blade.
        $statusClass = match ($fiche->statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($fiche->statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) $fiche->statut),
        };

        // $direction est l'entité (Direction, Caisse ou DelegationTechnique)
        // passée pour compatibilité avec les vues Blade existantes.
        $direction = $ctx->entity;

        return view('directeur.objectifs.show', compact(
            'fiche',
            'direction',
            'statusClass',
            'statusLabel',
        ));
    }

    /**
     * Met à jour le pourcentage d'avancement d'une fiche d'objectifs reçue.
     * L'avancement doit être un multiple de 5 (0, 5, 10, … 100).
     */
    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $ctx = $this->getContext();

        if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
            abort(403);
        }

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $val = (int) $request->avancement_percentage;
        if ($val % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        $this->objectifService->updateAvancement($fiche, $val);

        return redirect()
            ->route('directeur.objectifs.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    /**
     * Exporte la fiche d'objectifs reçue en PDF.
     */
    public function exportPdf(FicheObjectif $fiche): \Illuminate\Http\Response
    {
        $this->authorize('objectifs.voir-equipe');
        $ctx = $this->getContext();

        if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
            abort(403);
        }

        $fiche->load(['objectifs', 'annee']);

        $assigneNom    = $ctx->getDirecteurNomPrenom();
        $assigneRole   = $ctx->getRoleLabel();
        $assigneurNom  = '-';
        $assigneurRole = 'Supérieur hiérarchique';

        $pdf = Pdf::loadView('pdf.fiche-objectifs', compact(
            'fiche', 'assigneNom', 'assigneRole', 'assigneurNom', 'assigneurRole'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('fiche-objectifs-directeur-' . $fiche->id . '.pdf');
    }

    /**
     * Traite l'action d'acceptation ou de refus d'une fiche d'objectifs.
     *
     * Règles métier :
     *  • La fiche doit être en statut 'en_attente' (sinon déjà traitée).
     *  • L'action doit être 'accepter' ou 'refuser'.
     *  • Seul le directeur propriétaire de la fiche peut agir.
     */
    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.accepter');
        $ctx = $this->getContext();

        // Vérification que la fiche appartient bien à ce directeur.
        if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
            abort(403);
        }

        // Une fiche déjà acceptée ou refusée ne peut plus être modifiée.
        if ($fiche->statut !== 'en_attente') {
            return back()->with('error', 'Cette fiche ne peut plus être modifiée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action        = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        $msg = $action === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';

        return redirect()->route('directeur.objectifs.show', $fiche)->with('status', $msg);
    }
}
