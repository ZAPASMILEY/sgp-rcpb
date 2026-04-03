<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caisse extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'delegation_technique_id',
        'ville_id',
        'nom',
        'annee_ouverture',
        'quartier',
        'directeur_prenom',
        'directeur_nom',
        'directeur_sexe',
        'directeur_email',
        'directeur_telephone',
        'directeur_date_debut_mois',
        'secretariat_telephone',
        'secretaire_prenom',
        'secretaire_nom',
        'secretaire_sexe',
        'secretaire_email',
        'secretaire_telephone',
        'secretaire_date_debut_mois',
    ];

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'superviseur_caisse_id');
    }
}
