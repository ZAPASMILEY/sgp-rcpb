<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationIdentification extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'nom_prenom',
        'semestre',
        'date_recrutement',
        'date_evaluation',
        'date_titularisation',
        'matricule',
        'poste',
        'emploi',
        'niveau',
        'date_naissance',
        'direction',
        'direction_service',
        'date_confirmation',
        'categorie',
        'anciennete',
        'sexe',
        'date_affectation',
        'formations',
        'experiences',
    ];

    protected $casts = [
        'date_recrutement' => 'date',
        'date_evaluation' => 'date',
        'date_titularisation' => 'date',
        'date_naissance' => 'date',
        'date_confirmation' => 'date',
        'date_affectation' => 'date',
        'formations' => 'array',
        'experiences' => 'array',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }
}
