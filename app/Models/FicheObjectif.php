<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FicheObjectif extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'titre',
        'annee_id',
        'assignable_id',
        'assignable_type',
        'date',
        'date_echeance',
        'date_validation',
        'avancement_percentage',
        'statut',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    /**
     * Relation avec les lignes d'objectifs (les détails de la fiche)
     */
    public function objectifs(): HasMany
    {
        return $this->hasMany(LigneFicheObjectif::class);
    }

    /**
     * Recalcule l'avancement de la fiche comme moyenne des lignes.
     */
    public function recalculateAvancement(): void
    {
        $avg = (int) round($this->objectifs()->avg('avancement_percentage') ?? 0);
        $this->update(['avancement_percentage' => $avg]);
    }

    /**
     * Relation polymorphique (Agent, Service ou Agence)
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * CORRECTION : Ajout de la relation 'annee' demandée par le contrôleur
     */
    public function annee(): BelongsTo
    {
        return $this->belongsTo(Annee::class, 'annee_id');
    }

    /**
     * Relation avec l'année (Alias existant dans ton code)
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Annee::class, 'annee_id');
    }

    // ── Accesseurs ────────────────────────────────────────────────────────────

    /**
     * Retourne la valeur entière de l'année (ex: 2026)
     */
    public function getAnneeValueAttribute(): ?int
    {
        return $this->periode?->annee;
    }
}