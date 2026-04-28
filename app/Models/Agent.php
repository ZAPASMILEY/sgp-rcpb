<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Agent extends Model
{
    use HasFactory;

    /**
     * Liste officielle des fonctions — valeurs stockées en base.
     * Utilisée pour pré-remplir le select du formulaire agent
     * ET pour filtrer les selects des formulaires de structures.
     *
     * Clé   = valeur stockée en BD (agents.fonction)
     * Valeur = libellé affiché dans l'interface
     */
    public const FONCTIONS = [
        // Direction Générale (faîtière)
        'PCA'                     => 'PCA',
        'Directeur Général'       => 'Directeur Général',
        'DGA'                     => 'DGA',
        'Assistante DG'           => 'Assistante DG',
        'Conseiller DG'           => 'Conseiller DG',
        'Secrétaire Assistante'   => 'Secrétaire Assistante DG',
        // Directions de la faîtière
        'Directeur de Direction'  => 'Directeur de Direction',
        'Secrétaire de Direction' => 'Secrétaire de Direction',
        // Délégation Technique
        'Directeur Technique'     => 'Directeur Technique',
        'Secrétaire Technique'    => 'Secrétaire Technique',
        // Caisse
        'Directeur de Caisse'     => 'Directeur de Caisse',
        'Secrétaire de Caisse'    => 'Secrétaire de Caisse',
        // Agence
        "Chef d'Agence"           => "Chef d'Agence",
        "Secrétaire d'Agence"     => "Secrétaire d'Agence",
        // Guichet
        'Chef de Guichet'         => 'Chef de Guichet',
        // Service
        'Chef de Service'         => 'Chef de Service',
        // Base
        'Agent'                   => 'Agent',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'entite_id',
        'direction_id',
        'delegation_technique_id',
        'caisse_id',
        'agence_id',
        'guichet_id',
        'service_id',
        'nom',
        'prenom',
        'sexe',
        'email',
        'numero_telephone',
        'photo_path',
        'fonction',
        'date_debut_fonction',
    ];

    // ── Compte de connexion ───────────────────────────────────────────────────

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    // ── Rattachements hiérarchiques ───────────────────────────────────────────

    public function entite(): BelongsTo
    {
        return $this->belongsTo(Entite::class);
    }

    public function direction(): BelongsTo
    {
        return $this->belongsTo(Direction::class);
    }

    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    public function caisse(): BelongsTo
    {
        return $this->belongsTo(Caisse::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function guichet(): BelongsTo
    {
        return $this->belongsTo(Guichet::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
    // ── Postes de responsabilité (FK inverses) ─────────────────────────────
    // L'agent EST le responsable d'une structure (référencé depuis la table structure)

    public function directedDirection(): HasOne
    {
        return $this->hasOne(Direction::class, 'directeur_agent_id');
    }

    public function secretariedDirection(): HasOne
    {
        return $this->hasOne(Direction::class, 'secretaire_agent_id');
    }

    public function directedDelegation(): HasOne
    {
        return $this->hasOne(DelegationTechnique::class, 'directeur_agent_id');
    }

    public function secretariedDelegation(): HasOne
    {
        return $this->hasOne(DelegationTechnique::class, 'secretaire_agent_id');
    }

    public function directedCaisse(): HasOne
    {
        return $this->hasOne(Caisse::class, 'directeur_agent_id');
    }

    public function secretariedCaisse(): HasOne
    {
        return $this->hasOne(Caisse::class, 'secretaire_agent_id');
    }

    public function ledAgence(): HasOne
    {
        return $this->hasOne(Agence::class, 'chef_agent_id');
    }

    public function secretariedAgence(): HasOne
    {
        return $this->hasOne(Agence::class, 'secretaire_agent_id');
    }

    public function ledGuichet(): HasOne
    {
        return $this->hasOne(Guichet::class, 'chef_agent_id');
    }

    public function ledService(): HasOne
    {
        return $this->hasOne(Service::class, 'chef_agent_id');
    }

    // ── Objectifs / Évaluations ────────────────────────────────────────────

    public function objectifs(): MorphMany
    {
        return $this->morphMany(Objectif::class, 'assignable');
    }

    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluation::class, 'evaluable');
    }

    protected function casts(): array
    {
        return [
            'date_debut_fonction' => 'date',
        ];
    }
}
