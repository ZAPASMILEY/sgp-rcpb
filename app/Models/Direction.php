<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Direction extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nom',
        'entite_id',
        'delegation_technique_id',
        'directeur_prenom',
        'directeur_nom',
        'directeur_email',
        'directeur_numero',
        'directeur_region',
        'secretaire_user_id',
        'secretaire_prenom',
        'secretaire_nom',
        'secretaire_email',
        'secretaire_telephone',
        'secretariat_telephone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function secretaireUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretaire_user_id');
    }

    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class);
    }

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
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
