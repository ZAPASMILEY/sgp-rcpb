<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Entite extends Model
{
    use HasFactory;

    /**
     * La faîtière est unique : une seule ligne peut exister dans cette table.
     * La colonne `singleton` (valeur 1, contrainte UNIQUE) l'impose en base ;
     * ce boot() l'impose au niveau applicatif pour des messages d'erreur clairs.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (): void {
            if (static::query()->exists()) {
                throw new \RuntimeException(
                    'La table entites ne peut contenir qu\'une seule ligne (faîtière unique).'
                );
            }
        });
    }

    protected $fillable = [
        'nom',
        'ville',
        'region',
        'secretariat_telephone',
        // Responsables : FK vers agents
        'dg_agent_id',
        'dga_agent_id',
        'pca_agent_id',
        'assistante_agent_id',
    ];

    // ── Responsables ───────────────────────────────────────────────────────

    public function dg(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'dg_agent_id');
    }

    public function dga(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'dga_agent_id');
    }

    public function pca(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'pca_agent_id');
    }

    public function assistante(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'assistante_agent_id');
    }

    // ── Structures rattachées ──────────────────────────────────────────────

    /** Directions internes de la faîtière. */
    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    /** Délégations techniques rattachées à cette faîtière. */
    public function delegationTechniques(): HasMany
    {
        return $this->hasMany(DelegationTechnique::class);
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }
}
