<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Trait Auditable — Enregistre automatiquement toute création, modification
 * et suppression d'un modèle dans la table audit_logs.
 *
 * Usage : ajouter `use Auditable;` dans le modèle.
 * Personnalisation : définir `protected array $auditFields = [...]` dans le modèle
 *                   pour ne tracer que certains champs (sinon tous les fillable).
 */
trait Auditable
{
    // Champs jamais tracés, peu importe le modèle
    private static array $auditExcluded = ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'];

    public static function bootAuditable(): void
    {
        static::created(function (self $model): void {
            AuditLog::record(
                static::class,
                (int) $model->getKey(),
                'created',
                null,
                $model->auditSnapshot(),
                $model->auditDescription('créé(e)'),
            );
        });

        static::updated(function (self $model): void {
            // getDirty() est disponible pendant l'événement `updated` (avant sync des originaux)
            $tracked = $model->auditTrackedFields();
            $dirty   = array_intersect_key($model->getDirty(), array_flip($tracked));
            $dirty   = array_diff_key($dirty, array_flip(self::$auditExcluded));

            if (empty($dirty)) {
                return;
            }

            $old = [];
            $new = [];
            foreach ($dirty as $field => $newVal) {
                $old[$field] = $model->getOriginal($field);
                $new[$field] = $newVal;
            }

            AuditLog::record(
                static::class,
                (int) $model->getKey(),
                'updated',
                $old,
                $new,
                $model->auditDescription('modifié(e)').' ('.implode(', ', array_keys($dirty)).')',
            );
        });

        static::deleted(function (self $model): void {
            AuditLog::record(
                static::class,
                (int) $model->getKey(),
                'deleted',
                $model->auditSnapshot(),
                null,
                $model->auditDescription('supprimé(e)'),
            );
        });
    }

    // ── Helpers (surchargeable dans le modèle si besoin) ────────────────────

    /** Champs à tracer lors d'un `updated`. */
    protected function auditTrackedFields(): array
    {
        return property_exists($this, 'auditFields')
            ? $this->auditFields
            : array_diff($this->fillable ?? [], self::$auditExcluded);
    }

    /** Snapshot des champs clés (used pour created/deleted). */
    protected function auditSnapshot(): array
    {
        $fields = array_slice($this->auditTrackedFields(), 0, 8);
        $snap   = [];
        foreach ($fields as $field) {
            $val = $this->getAttribute($field);
            if ($val !== null && $val !== '') {
                $snap[$field] = $val instanceof \Carbon\Carbon
                    ? $val->format('d/m/Y')
                    : $val;
            }
        }
        return $snap ?: ['id' => $this->getKey()];
    }

    /** Description lisible de l'action (surchargeable). */
    protected function auditDescription(string $verb): string
    {
        $label = class_basename(static::class);
        $name  = $this->nom
              ?? $this->titre
              ?? $this->name
              ?? $this->annee
              ?? $this->region
              ?? ('#'.$this->getKey());

        return "{$label} {$verb} : {$name}";
    }
}
