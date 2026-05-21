<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formation extends Model
{
    // ── Liste fixe des domaines ───────────────────────────────────────────────
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

    protected $fillable = [
        'agent_id',
        'titre',
        'domaine',
        'date_debut',
        'date_fin',
        'duree_heures',
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

    // ── Accesseurs ────────────────────────────────────────────────────────────

    /** Libellé lisible du domaine */
    public function getDomaineLabelAttribute(): string
    {
        return self::DOMAINES[$this->domaine] ?? ucfirst((string) $this->domaine);
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
     * Utilisé pour auto-remplir la section formations dans une évaluation.
     */
    public function scopeChevaucheEvaluation($query, string $dateDebut, string $dateFin)
    {
        return $query
            ->where('date_debut', '<=', $dateFin)
            ->where('date_fin',   '>=', $dateDebut);
    }
}
