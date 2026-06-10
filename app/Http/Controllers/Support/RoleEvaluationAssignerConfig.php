<?php

namespace App\Http\Controllers\Support;

use App\Models\Evaluation;

/**
 * Configuration pour les méthodes côté ÉVALUATEUR (créateur de l'évaluation).
 *
 * Couvre : sharedAssignerShow · sharedEdit · sharedUpdate · sharedSubmit
 *          sharedDestroy · sharedAssignerExportPdf
 *
 * Rôles couverts : PCA · DG-sub · DG-directions · DGA-sub
 *                  Directeur · DirecteurSecrétaire · Chef · Assistante
 */
final class RoleEvaluationAssignerConfig
{
    /**
     * @param string   $layout               Nom du layout Blade
     * @param \Closure $getHeroSubtitle      fn(Evaluation): string — sous-titre de la page edit
     * @param string   $updateRoute          Route nommée pour le PUT update (form action dans edit)
     * @param string   $showRoute            Route nommée show (backUrl dans edit, redirect après update)
     * @param string   $editRoute            Route nommée pour le lien "Modifier" dans la vue show
     * @param string   $submitRoute          Route nommée pour le bouton "Soumettre" dans la vue show
     * @param string   $destroyRoute         Route nommée pour le bouton "Supprimer" dans la vue show
     * @param string   $pdfRoute             Route nommée pour le téléchargement PDF
     * @param string   $breadcrumb           Texte du fil d'ariane dans la vue show
     * @param \Closure $getEvalueLabel       fn(Evaluation): string — label de l'évalué
     * @param \Closure $getEvaluateurLabel   fn(Evaluation): string — label de l'évaluateur
     * @param \Closure $getCibleNom          fn(Evaluation): string — nom affiché de la cible
     * @param \Closure $getCibleType         fn(Evaluation): string — type affiché de la cible
     * @param \Closure $getObjectiveOptions  fn(Evaluation): array — fiches objectifs disponibles
     * @param \Closure $getBackRoute         fn(Evaluation): string — URL de retour dans show
     * @param \Closure $checkOwnership       fn(Evaluation): void — abort(403) si non autorisé
     * @param \Closure $notifyOnSubmit       fn(Evaluation): void — notifie l'évalué lors de la soumission
     * @param \Closure $resolveRedirectAfterSubmit   fn(Evaluation): string — URL après soumission
     * @param \Closure $resolveRedirectAfterDestroy  fn(Evaluation): string — URL après suppression
     * @param string   $pdfView              Vue Blade utilisée pour générer le PDF
     * @param string   $pdfFilenamePrefix    Préfixe du nom de fichier PDF (ex: 'pca', 'dg-sub')
     */
    public function __construct(
        public readonly string   $layout,
        public readonly \Closure $getHeroSubtitle,
        public readonly string   $updateRoute,
        public readonly string   $showRoute,
        public readonly string   $editRoute,
        public readonly string   $submitRoute,
        public readonly string   $destroyRoute,
        public readonly string   $pdfRoute,
        public readonly string   $breadcrumb,
        public readonly \Closure $getEvalueLabel,
        public readonly \Closure $getEvaluateurLabel,
        public readonly \Closure $getCibleNom,
        public readonly \Closure $getCibleType,
        public readonly \Closure $getObjectiveOptions,
        public readonly \Closure $getBackRoute,
        public readonly \Closure $checkOwnership,
        public readonly \Closure $notifyOnSubmit,
        public readonly \Closure $resolveRedirectAfterSubmit,
        public readonly \Closure $resolveRedirectAfterDestroy,
        public readonly string   $pdfView,
        public readonly string   $pdfFilenamePrefix,
    ) {}
}
