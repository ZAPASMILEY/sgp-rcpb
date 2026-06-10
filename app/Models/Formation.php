<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formation extends Model
{
    // ── Domaines ──────────────────────────────────────────────────────────────
    const DOMAINES = [
        'management'   => 'Management',
        'informatique' => 'Informatique',
        'finance'      => 'Finance & Comptabilité',
        'rh'           => 'Ressources Humaines',
        'juridique'    => 'Juridique & Conformité',
        'operations'   => 'Opérations & Caisse',
        'commercial'   => 'Commercial & Marketing',
        'securite'     => 'Sécurité',
        'autre'        => 'Autre',
    ];

    // ── Types ─────────────────────────────────────────────────────────────────
    const TYPES = [
        'interne' => 'Interne',
        'externe' => 'Externe',
    ];

    // ── Statuts ───────────────────────────────────────────────────────────────
    const STATUTS = [
        'en_attente' => 'En attente',
        'validee'    => 'Validée',
        'refusee'    => 'Refusée',
    ];

    protected $fillable = [
        'agent_id',
        'theme',
        'domaine',
        'type',
        'date_debut',
        'date_fin',
        'duree_heures',
        'attestation_path',
        'statut',
        'motif_refus',
        'formateur_id',
        'created_by',
    ];

    protected $casts = [
        'date_debut'   => 'date',
        'date_fin'     => 'date',
        'duree_heures' => 'integer',
    ];

    // ── Relations ─────────────────────────────────────────────────────────────

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'formateur_id');
    }

    // ── Accesseurs ────────────────────────────────────────────────────────────

    /** Libellé lisible du domaine */
    public function getDomaineLabelAttribute(): string
    {
        return self::DOMAINES[$this->domaine] ?? ucfirst((string) $this->domaine);
    }

    /** Libellé lisible du type */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    /** Période formatée "01 jan. 2025 – 15 jan. 2025" */
    public function getPeriodeFormateeAttribute(): string
    {
        return $this->date_debut->translatedFormat('d M Y')
            . ' – '
            . ($this->date_fin ? $this->date_fin->translatedFormat('d M Y') : 'en cours');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /**
     * Formations dont la période chevauche une évaluation.
     * Seules les formations VALIDÉES par le RH sont incluses.
     */
    public function scopeChevaucheEvaluation($query, string $dateDebut, string $dateFin)
    {
        return $query
            ->where('statut', 'validee')
            ->where('date_debut', '<=', $dateFin)
            ->where('date_fin',   '>=', $dateDebut);
    }

    /** Formations validées uniquement. */
    public function scopeValidees($query)
    {
        return $query->where('statut', 'validee');
    }

    /** Formations en attente de validation RH. */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}
