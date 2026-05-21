<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crée une entrée d'audit.
     */
    public static function record(
        string $auditableType,
        int    $auditableId,
        string $action,
        ?array $oldValues,
        ?array $newValues,
        string $description,
    ): void {
        $user = Auth::user();

        static::create([
            'user_id'        => $user?->id,
            'user_name'      => $user?->name,
            'auditable_type' => $auditableType,
            'auditable_id'   => $auditableId,
            'action'         => $action,
            'old_values'     => $oldValues  ?: null,
            'new_values'     => $newValues  ?: null,
            'description'    => $description,
            'ip_address'     => Request::ip(),
            'created_at'     => now(),
        ]);
    }

    // ── Helpers affichage ───────────────────────────────────────────────────

    public function auditableLabel(): string
    {
        return match (true) {
            str_ends_with($this->auditable_type, 'Evaluation') => 'Évaluation',
            str_ends_with($this->auditable_type, 'User')       => 'Compte',
            str_ends_with($this->auditable_type, 'Agent')      => 'Agent',
            str_ends_with($this->auditable_type, 'Formation')  => 'Formation',
            default => class_basename($this->auditable_type),
        };
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created'       => 'bg-emerald-100 text-emerald-700',
            'deleted'       => 'bg-red-100 text-red-700',
            'statut_change' => 'bg-violet-100 text-violet-700',
            default         => 'bg-slate-100 text-slate-600',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created'       => 'Créé',
            'updated'       => 'Modifié',
            'deleted'       => 'Supprimé',
            'statut_change' => 'Statut changé',
            default         => $this->action,
        };
    }
}
