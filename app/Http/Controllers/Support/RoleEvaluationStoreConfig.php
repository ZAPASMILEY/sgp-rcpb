<?php

namespace App\Http\Controllers\Support;

/**
 * Configuration pour la création (store) d'une évaluation côté évaluateur.
 *
 * Utilisé par sharedStore — unifie tous les *Store des rôles :
 * PCA · DG-sub · DG-directions · DGA-sub · Directeur · DirecteurSecrétaire
 * Chef · Assistante
 */
final class RoleEvaluationStoreConfig
{
    /**
     * @param \Closure        $resolveEvaluable    fn(Request): array{0: string, 1: Model|null}
     *                                              Retourne [evaluableType, evaluable|null].
     *                                              Doit aussi gérer les vérifications de périmètre (abort 403).
     *                                              Retourner null comme Model déclenchera un message d'erreur.
     * @param string|\Closure $evaluableRole       Rôle fixe (string) ou fn(Model): string calculé sur l'évalué
     * @param \Closure        $redirectAfterStore  fn(Evaluation): string — URL après création réussie
     * @param \Closure|null   $notifyOnCreate      fn(Evaluation): void — notification optionnelle à la création
     * @param string|null     $missingEvaluableMessage  Message d'erreur si l'évalué est introuvable
     */
    public function __construct(
        public readonly \Closure        $resolveEvaluable,
        public readonly string|\Closure $evaluableRole,
        public readonly \Closure        $redirectAfterStore,
        public readonly ?\Closure       $notifyOnCreate          = null,
        public readonly ?string         $missingEvaluableMessage = null,
        public readonly ?string         $successMessage          = null,
    ) {}
}
