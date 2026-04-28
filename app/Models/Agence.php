<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agence — Rattachée à une DelegationTechnique et supervisée par une Caisse.
 *
 * Le chef et la secrétaire sont des Agents existants.
 */
class Agence extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'delegation_technique_id',
        'caisse_id',
        // Responsables : FK vers agents
        'chef_agent_id',
        'secretaire_agent_id',
    ];

    // ── Responsables ───────────────────────────────────────────────────────

    public function chef(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'chef_agent_id');
    }

    public function secretaire(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'secretaire_agent_id');
    }

    // ── Hiérarchie ─────────────────────────────────────────────────────────

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class, 'caisse_id');
    }

    public function guichets(): HasMany
    {
        return $this->hasMany(Guichet::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
