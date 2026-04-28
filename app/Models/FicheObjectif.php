<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Ajouté pour la relation période

class FicheObjectif extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'annee_id', // On utilise l'ID pour la relation avec la table Années
        'assignable_id',
        'assignable_type',
        'date',
        'date_echeance',
        'avancement_percentage',
        'statut',
    ];

    // Relation avec les lignes d'objectifs (les détails de la fiche)
    public function objectifs(): HasMany
    {
        return $this->hasMany(LigneFicheObjectif::class);
    }

    // Relation polymorphique (Agent, Service ou Agence)
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    // Relation avec l'année (Bloc 3 - Gestion des périodes)
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Annee::class, 'annee_id');
    }

    // Accesseur : $fiche->annee retourne la valeur entière de l'année (ex: 2025)
    public function getAnneeAttribute(): ?int
    {
        return $this->periode?->annee;
    }
}