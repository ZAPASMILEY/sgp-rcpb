<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'entite_id',
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

    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class, 'superviseur_direction_id');
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }
}
