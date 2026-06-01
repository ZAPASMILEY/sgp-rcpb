<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Statuts qui permettent à l'évaluateur de modifier/re-soumettre l'évaluation.
     * 'brouillon'  → évaluation en cours de rédaction (jamais soumise)
     * 'a_reviser'  → évaluation réouverte après refus de l'évalué (motif_refus conservé)
     */
    public const EDITABLE_STATUTS = ['brouillon', 'a_reviser'];

    /** Libellés affichés dans les vues pour chaque statut. */
    public const STATUT_LABELS = [
        'brouillon'  => 'Brouillon',
        'a_reviser'  => 'À réviser',
        'soumis'     => 'Soumis',
        'valide'     => 'Validé',
        'refuse'     => 'Refusé',
        'reclamation'=> 'Réclamation',
    ];

    /** @var list<string> */
    protected $fillable = [
        'evaluable_type',
        'evaluable_id',
        'evaluable_role',
        'annee_id',
        'semestre_id',
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
        'motif_refus',
        'reclamation',
        'statut_reclamation',
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
    
    public function agent(): MorphTo
    {
        // On redirige la relation 'agent' sur le comportement 'evaluable'
        return $this->evaluable();
    }

    public function evaluateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    public function annee(): BelongsTo
    {
        return $this->belongsTo(Annee::class);
    }

    public function semestre(): BelongsTo
    {
        return $this->belongsTo(Semestre::class);
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
