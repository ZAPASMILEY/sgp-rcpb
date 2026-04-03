<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DelegationTechnique extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'region',
        'ville',
        'secretariat_telephone',
        'directeur_prenom',
        'directeur_nom',
        'directeur_sexe',
        'directeur_email',
        'directeur_telephone',
        'directeur_date_debut_mois',
        'directeur_photo_path',
        'secretaire_prenom',
        'secretaire_nom',
        'secretaire_sexe',
        'secretaire_email',
        'secretaire_telephone',
        'secretaire_date_debut_mois',
    ];

    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }

    public function services(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, Direction::class, 'delegation_technique_id', 'direction_id');
    }

    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class);
    }
}
