<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Evaluation extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'evaluable_type',
        'evaluable_id',
        'evaluable_role',
        'annee_id',
        'evaluateur_id',
        'date_debut',
        'date_fin',
        'moyenne_subjectifs',
        'note_criteres_subjectifs',
        'moyenne_objectifs',
        'note_criteres_objectifs',
        'note_objectifs',
        'note_manuelle',
        'note_finale',
        'commentaire',
        'points_a_ameliorer',
        'strategies_amelioration',
        'commentaires_evalue',
        'signature_evalue_nom',
        'signature_directeur_nom',
        'signature_evaluateur_nom',
        'date_signature_evalue',
        'date_signature_directeur',
        'date_signature_evaluateur',
        'statut',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'moyenne_subjectifs' => 'decimal:2',
        'note_criteres_subjectifs' => 'decimal:2',
        'moyenne_objectifs' => 'decimal:2',
        'note_criteres_objectifs' => 'decimal:2',
        'note_objectifs' => 'integer',
        'note_manuelle'  => 'integer',
        'note_finale'    => 'decimal:2',
        'date_debut'     => 'date',
        'date_fin'       => 'date',
        'date_signature_evalue' => 'date',
        'date_signature_directeur' => 'date',
        'date_signature_evaluateur' => 'date',
    ];

    public function evaluable(): MorphTo
    {
        return $this->morphTo();
    }

    public function evaluateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    public function annee(): BelongsTo
    {
        return $this->belongsTo(Annee::class);
    }

    public function identification(): HasOne
    {
        return $this->hasOne(EvaluationIdentification::class);
    }

    public function criteres(): HasMany
    {
        return $this->hasMany(EvaluationCritere::class)->orderBy('ordre');
    }
}
