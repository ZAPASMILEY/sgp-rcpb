<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationSousCritere extends Model
{
    use HasFactory;

    protected $table = 'evaluation_sous_criteres';

    protected $fillable = [
        'evaluation_critere_id',
        'ordre',
        'libelle',
        'note',
        'observation',
    ];

    protected $casts = [
        'note' => 'decimal:2',
    ];

    public function critere(): BelongsTo
    {
        return $this->belongsTo(EvaluationCritere::class, 'evaluation_critere_id');
    }
}
