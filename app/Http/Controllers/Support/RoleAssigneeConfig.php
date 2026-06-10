<?php

namespace App\Http\Controllers\Support;

/**
 * Objet de configuration pour le rôle assigné (celui qui REÇOIT la fiche d'objectifs).
 *
 * Utilisé par les méthodes unifiées :
 *   sharedStatut · sharedAvancement · sharedAvancementLigne · sharedContesterLigne
 *   sharedAssigneeShow · exportPdf (assignee)
 *
 * Rôles couverts : DG (reçoit du PCA) · DGA/Assistante_Dg/Conseillers_Dg (reçoivent du DG)
 *                  Directeur_Technique (reçoit du DGA/DG) · Personnel (reçoit du Chef)
 */
final class RoleAssigneeConfig
{
    /**
     * @param string   $layout              Nom du layout Blade (ex : 'layouts.dg')
     * @param string   $showRoute           Route nommée vers la page show
     * @param string   $backRoute           URL complète de retour (ex : route('dg.mon-espace'))
     * @param string   $statusRoute         Route nommée pour le PATCH statut
     * @param string   $avancementRoute     Route nommée pour le PATCH avancement par ligne
     * @param string   $contesterRoute      Route nommée pour le PATCH contester ligne
     * @param string   $pdfRoute            Route nommée pour le téléchargement PDF
     * @param \Closure $checkOwnership      fn(FicheObjectif): void — abort(403) si non autorisé
     * @param \Closure $notifyOnStatut      fn(FicheObjectif, string $action): void — notifie l'assignateur
     * @param \Closure $notifyOnContest     fn(FicheObjectif): void — notifie l'assignateur lors d'une contestation
     * @param \Closure $buildPdfResponse    fn(FicheObjectif, User): \Symfony\Component\HttpFoundation\Response
     */
    public function __construct(
        public readonly string   $layout,
        public readonly string   $showRoute,
        public readonly string   $backRoute,
        public readonly string   $statusRoute,
        public readonly string   $avancementRoute,
        public readonly string   $contesterRoute,
        public readonly string   $pdfRoute,
        public readonly \Closure $checkOwnership,
        public readonly \Closure $notifyOnStatut,
        public readonly \Closure $notifyOnContest,
        public readonly \Closure $buildPdfResponse,
    ) {}
}
