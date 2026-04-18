<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
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
    /**
     * Résout et retourne le contexte du directeur connecté.
     * Déclenche un 403 si l'utilisateur n'est pas lié à une entité.
     */
    private function getContext(): DirecteurEntity
    {
        return DirecteurEntity::resolveOrFail(Auth::user());
    }

    /**
     * Affiche le détail d'une fiche d'objectifs reçue par le directeur.
     *
     * Vérifie que la fiche est bien assignée à l'entité du directeur connecté
     * (contrôle du type polymorphique et de l'id) avant d'afficher.
     */
    public function show(FicheObjectif $fiche): View
    {
        $ctx = $this->getContext();

        // Sécurité : la fiche doit être assignée à l'entité exacte du directeur.
        if (
            $fiche->assignable_type !== $ctx->modelClass ||
            (int) $fiche->assignable_id !== $ctx->getId()
        ) {
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
     * Traite l'action d'acceptation ou de refus d'une fiche d'objectifs.
     *
     * Règles métier :
     *  • La fiche doit être en statut 'en_attente' (sinon déjà traitée).
     *  • L'action doit être 'accepter' ou 'refuser'.
     *  • Seul le directeur propriétaire de la fiche peut agir.
     */
    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $ctx = $this->getContext();

        // Vérification que la fiche appartient bien à ce directeur.
        if (
            $fiche->assignable_type !== $ctx->modelClass ||
            (int) $fiche->assignable_id !== $ctx->getId()
        ) {
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
