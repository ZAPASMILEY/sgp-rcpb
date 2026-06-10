<?php

namespace App\Http\Controllers\Support;

use App\Models\Evaluation;

/**
 * Configuration pour les méthodes côté ÉVALUÉ (receveur de l'évaluation).
 *
 * Couvre : sharedStatut · sharedReclamer · sharedCommentaire · sharedReceivedExportPdf
 *          sharedReceivedShow (si les champs show sont fournis)
 *
 * Rôles couverts : DG (reçoit du PCA) · DGA/Assistante/Conseillers (reçoivent du DG)
 *                  Directeur (reçoit du DGA/DG) · Chef (reçoit du Directeur)
 *                  Personnel (reçoit du Chef)
 */
final class RoleEvaluationReceivedConfig
{
    /**
     * @param \Closure $checkOwnership  fn(Evaluation): void — abort(403) si non autorisé
     * @param \Closure $notifyStatut    fn(Evaluation, string $labelStatut): void
     * @param \Closure $notifyReclamer  fn(Evaluation): void
     * @param string   $pdfView            Vue Blade pour le PDF
     * @param string   $pdfFilenamePrefix  Préfixe du nom de fichier PDF
     *
     * Champs optionnels utilisés par sharedReceivedShow :
     * @param string|null   $layout           Nom du layout Blade
     * @param \Closure|null $getCibleType     fn(Evaluation): string — type affiché (ex: 'Directeur Général')
     * @param \Closure|null $getBackRoute     fn(Evaluation): string — URL de retour
     * @param string|null   $breadcrumb       Texte du fil d'ariane
     * @param string|null   $statutRoute      Route nommée PATCH statut
     * @param string|null   $reclamerRoute    Route nommée PATCH reclamer
     * @param string|null   $commentaireRoute Route nommée PATCH commentaire
     * @param string|null   $showPdfRoute     Route nommée pour le bouton PDF dans la vue show
     */
    public function __construct(
        public readonly \Closure $checkOwnership,
        public readonly \Closure $notifyStatut,
        public readonly \Closure $notifyReclamer,
        public readonly string   $pdfView,
        public readonly string   $pdfFilenamePrefix,
        // — champs optionnels pour sharedReceivedShow —
        public readonly ?string   $layout           = null,
        public readonly ?\Closure $getCibleType     = null,
        public readonly ?\Closure $getBackRoute     = null,
        public readonly ?string   $breadcrumb       = null,
        public readonly ?string   $statutRoute      = null,
        public readonly ?string   $reclamerRoute    = null,
        public readonly ?string   $commentaireRoute = null,
        public readonly ?string   $showPdfRoute     = null,
    ) {}
}
