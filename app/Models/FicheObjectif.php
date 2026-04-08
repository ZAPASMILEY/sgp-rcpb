<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FicheObjectif extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'annee',
        'assignable_id',
        'assignable_type',
        'date',
        'date_echeance',
        'avancement_percentage',
        'statut',
    ];

    public function objectifs(): HasMany
    {
        return $this->hasMany(FicheObjectifObjectif::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
