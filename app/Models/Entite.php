<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Entite extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nom',
        'ville',
        'region',
        'directrice_generale_prenom',
        'directrice_generale_nom',
        'directrice_generale_email',
        'dga_prenom',
        'dga_nom',
        'dga_email',
        'assistante_dg_prenom',
        'assistante_dg_nom',
        'assistante_dg_email',
        'pca_prenom',
        'pca_nom',
        'pca_email',
        'secretariat_telephone',
    ];

    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }
}
