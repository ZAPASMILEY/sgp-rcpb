<?php

namespace App\Models;

use App\Traits\Auditable;
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
    use HasFactory, Auditable;

    protected $fillable = [
        'delegation_technique_id',
        'ville_id',
        'nom',
        'annee_ouverture',
        'quartier',
        'secretariat_telephone',
        'direction_id',
        // Responsables : FK vers agents
        'directeur_agent_id',
        'secretaire_agent_id',
    ];

    // ── Responsables ───────────────────────────────────────────────────────

    public function directeur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'directeur_agent_id');
    }

    public function directeurAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'directeur_agent_id');
    }

    public function secretaire(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'secretaire_agent_id');
    }
    /**
 * Calcule l'effectif réel et total de la caisse
 */
public function getEffectifReelAttribute(): int
{
    $total = 0;

    // 1. On compte le directeur s'il est assigné
    if ($this->directeur_agent_id) {
        $total++;
    }

    // 2. On compte le secrétaire s'il est assigné
    if ($this->secretaire_agent_id) {
        $total++;
    }

    // 3. On ajoute tous les agents qui travaillent dans les services de cette caisse
    // (Cette relation 'services' existe déjà d'après ton controller et charge ses agents)
    foreach ($this->services as $service) {
        // Compte le chef de service s'il existe
        if ($service->chef_agent_id) {
            $total++;
        }
        
        // Compte les autres agents du service s'il y a une relation agents sur le service
        if ($service->agents) {
            $total += $service->agents->count();
        }
    }

    return $total;
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

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'caisse_id');
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'caisse_id');
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
