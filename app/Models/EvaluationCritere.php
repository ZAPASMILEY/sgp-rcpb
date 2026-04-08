<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCritere extends Model
{
    use HasFactory;

    protected $table = 'evaluation_criteres';

    protected $fillable = [
        'evaluation_id',
        'type',
        'ordre',
        'titre',
        'description',
        'note_globale',
        'observation',
        'source_template_id',
        'source_fiche_objectif_id',
        'source_fiche_objectif_objectif_id',
    ];

    protected $casts = [
        'note_globale' => 'decimal:2',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function sousCriteres(): HasMany
    {
        return $this->hasMany(EvaluationSousCritere::class, 'evaluation_critere_id')->orderBy('ordre');
    }

    public function ficheObjectif(): BelongsTo
    {
        return $this->belongsTo(FicheObjectif::class, 'source_fiche_objectif_id');
    }

    public function ficheObjectifObjectif(): BelongsTo
    {
        return $this->belongsTo(FicheObjectifObjectif::class, 'source_fiche_objectif_objectif_id');
    }
}
