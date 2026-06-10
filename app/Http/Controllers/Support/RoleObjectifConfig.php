<?php

namespace App\Http\Controllers\Support;

/**
 * Objet de configuration role-spécifique pour FicheObjectifController.
 *
 * Toutes les données qui varient par rôle (layout, noms de routes, labels,
 * callbacks d'autorisation et de notification) sont regroupées ici.
 * Le contrôleur utilise ce seul objet dans ses méthodes unifiées au lieu
 * de méthodes privées dupliquées par rôle.
 */
final class RoleObjectifConfig
{
    /**
     * @param string        $layout                Nom du layout Blade (ex : 'layouts.dg')
     * @param string        $storeRoute            Route nommée pour le POST store (création)
     * @param string        $showRoute             Route nommée vers la page show
     * @param string        $editRoute             Route nommée vers la page edit
     * @param string        $updateRoute           Route nommée pour le PUT update
     * @param string|null   $pdfRoute              Route nommée pour le téléchargement PDF (null si absent)
     * @param string|\Closure $createBackRoute     URL (ou fn(Request):string) de retour sur le formulaire de création
     * @param int           $maxObjectifLength     Longueur max d'un objectif (5000 pour DG/DGA/PCA, 500 pour Chef)
     * @param string|null   $subordonneField       Nom du champ subordonné ('subordonne_id' | 'agent_id' | null=cible fixe)
     * @param string        $assignableType        Classe du modèle assignable (User::class ou Agent::class)
     * @param \Closure      $getSubordonnes        fn(): Collection<{id,nom,role_label?}> — liste pour le sélecteur
     * @param \Closure      $resolveAssignable     fn(Request, array $allowedIds): User|Agent|null
     * @param \Closure      $resolveAfterStore     fn(FicheObjectif, mixed $assignable, bool $isBrouillon): RedirectResponse
     * @param \Closure      $checkOwnership        fn(FicheObjectif): void — abort(403) si non autorisé
     * @param \Closure      $resolveCibleLabel     fn(FicheObjectif): string — libellé de la cible (vue edit/show)
     * @param \Closure      $resolveBackUrl        fn(FicheObjectif): string — URL de retour (vue show)
     * @param \Closure      $canDelete             fn(FicheObjectif): bool — autorise-t-on la suppression ?
     * @param \Closure      $resolveDeleteRedirect fn(FicheObjectif): string — URL après suppression
     * @param \Closure      $notifyOnSend          fn(FicheObjectif): void — notifie lors d'un premier envoi
     * @param \Closure      $notifyOnResend        fn(FicheObjectif): void — notifie lors d'un renvoi (contesté/refusée)
     * @param \Closure      $buildPdfResponse      fn(FicheObjectif, User): Response — génère le téléchargement PDF côté assignateur
     */
    public function __construct(
        // ── Identité du rôle ──────────────────────────────────────────────────
        public readonly string   $layout,

        // ── Routes ────────────────────────────────────────────────────────────
        public readonly string   $storeRoute,
        public readonly string   $showRoute,
        public readonly string   $editRoute,
        public readonly string   $updateRoute,
        public readonly ?string  $pdfRoute,

        // ── Création ─────────────────────────────────────────────────────────
        public readonly string|\Closure $createBackRoute,
        public readonly int      $maxObjectifLength,
        public readonly ?string  $subordonneField,
        public readonly string   $assignableType,
        public readonly \Closure $getSubordonnes,
        public readonly \Closure $resolveAssignable,
        public readonly \Closure $resolveAfterStore,

        // ── Édition / mise à jour ─────────────────────────────────────────────
        public readonly \Closure $checkOwnership,
        public readonly \Closure $resolveCibleLabel,
        public readonly \Closure $resolveBackUrl,
        public readonly \Closure $canDelete,
        public readonly \Closure $resolveDeleteRedirect,

        // ── Notifications ─────────────────────────────────────────────────────
        public readonly \Closure $notifyOnSend,
        public readonly \Closure $notifyOnResend,

        // ── PDF (côté assignateur) ─────────────────────────────────────────────
        public readonly \Closure $buildPdfResponse,
    ) {}
}
