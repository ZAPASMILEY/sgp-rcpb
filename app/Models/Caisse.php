<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Caisse — Entité organisationnelle rattachée à une DelegationTechnique.
 *
 * Le directeur et la secrétaire sont des Agents existants.
 * Hiérarchie : DelegationTechnique → Caisse → Agences / Services
 */
class Caisse extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegation_technique_id',
        'ville_id',
        'nom',
        'annee_ouverture',
        'quartier',
        'secretariat_telephone',
        'superviseur_direction_id',
        // Responsables : FK vers agents
        'directeur_agent_id',
        'secretaire_agent_id',
    ];

    // ── Responsables ───────────────────────────────────────────────────────

    public function directeur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'directeur_agent_id');
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

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function superviseurDirection(): BelongsTo
    {
        return $this->belongsTo(Direction::class, 'superviseur_direction_id');
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'superviseur_caisse_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }
}
