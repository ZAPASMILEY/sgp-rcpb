<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guichet extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'agence_id',
        'chef_agent_id', // FK vers la table agents
    ];

    /**
     * Relation vers l'Agent qui est Chef de Guichet (FK Inverse)
     */
    public function chef(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'chef_agent_id');
    }

    /**
     * Relation vers l'Agence parente
     */
    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    /**
     * Liste des agents affectés à ce guichet (FK Directe)
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}