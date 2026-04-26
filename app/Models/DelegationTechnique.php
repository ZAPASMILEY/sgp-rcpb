<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * DelegationTechnique — Bureau régional rattaché à une faîtière (Entite).
 *
 * Une délégation est identifiée par son couple (region, ville).
 * Elle supervise des caisses, des agences, et des services directs.
 *
 * Le directeur et la secrétaire sont des Agents existants,
 * sélectionnés lors de la création/édition (pas de champs dénormalisés).
 *
 * Hiérarchie : Entite → DelegationTechnique → Caisse → Agence
 */
class DelegationTechnique extends Model
{
    use HasFactory;

    protected $fillable = [
        'entite_id',
        'region',
        'ville',
        'secretariat_telephone',
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

    /**
     * La faîtière (Direction Générale) dont dépend cette délégation.
     * C'est le DG de la faîtière qui évalue le directeur technique (via DGA).
     */
    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class);
    }

    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }

    /**
     * Services directement rattachés à la délégation
     * (via delegation_technique_id sur la table services).
     */
    public function directServices(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}
