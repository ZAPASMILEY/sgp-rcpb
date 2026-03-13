<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'directrice_generale_prenom',
        'directrice_generale_nom',
        'directrice_generale_email',
        'pca_prenom',
        'pca_nom',
        'pca_email',
        'secretariat_telephone',
    ];

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }
}
