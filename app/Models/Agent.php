<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
    public const ROLES = [
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
        'matricule',
        'role',
        'poste',
        'date_debut_fonction',
    ];

    /**
     * Rôles système exclus du personnel évalué.
     * Ces rôles ne sont pas soumis au processus d'évaluation.
     */
    public const NON_PERSONNEL_ROLES = ['PCA', 'Admin', 'RH'];

    /**
     * Scope "personnel" : agents affectés à une structure
     * et dont le rôle système n'est pas PCA / Admin / RH.
     * Utilisé pour les espaces DG, DGA, RH, PCA (le PCA n'est pas compté).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePersonnel($query)
    {
        return $query
            ->where(fn ($q) => $q
                ->whereNotNull('entite_id')
                ->orWhereNotNull('direction_id')
                ->orWhereNotNull('delegation_technique_id')
                ->orWhereNotNull('caisse_id')
                ->orWhereNotNull('agence_id')
                ->orWhereNotNull('guichet_id')
                ->orWhereNotNull('service_id')
                // Le DG et son assistante n'ont aucune structure renseignée
                // mais font partie du personnel évaluable (faitière FCPB)
                ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['DG', 'Assistante_Dg']))
            )
            ->where(fn ($q) => $q
                ->whereDoesntHave('user')
                ->orWhereHas('user', fn ($u) => $u->whereNotIn('role', self::NON_PERSONNEL_ROLES))
            );
    }

    /**
     * Scope "reseau" : agents affectés à une structure,
     * sans les rôles Admin et RH, mais PCA INCLUS.
     * Utilisé uniquement pour les totaux de l'espace Admin
     * (dashboard KPI, statistiques, liste agents).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReseau($query)
    {
        return $query
            ->where(fn ($q) => $q
                ->whereNotNull('entite_id')
                ->orWhereNotNull('direction_id')
                ->orWhereNotNull('delegation_technique_id')
                ->orWhereNotNull('caisse_id')
                ->orWhereNotNull('agence_id')
                ->orWhereNotNull('guichet_id')
                ->orWhereNotNull('service_id')
                ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['DG', 'Assistante_Dg', 'PCA']))
            )
            ->where(fn ($q) => $q
                ->whereDoesntHave('user')
                ->orWhereHas('user', fn ($u) => $u->whereNotIn('role', ['Admin', 'RH']))
            );
    }

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

    // ── Postes faîtière / entité (FK inverses sur la table entites) ────────

    public function assistantedEntite(): HasOne
    {
        return $this->hasOne(Entite::class, 'assistante_agent_id');
    }

    public function pcaedEntite(): HasOne
    {
        return $this->hasOne(Entite::class, 'pca_agent_id');
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

    /**
     * Évaluations de cet agent passées via son compte utilisateur
     * (evaluable_type = User, evaluable_id = user.id).
     * C'est le chemin utilisé par DG, DGA, Directeur, Chef, etc.
     */
    public function evaluationsPersonnel(): HasManyThrough
    {
        return $this->hasManyThrough(
            Evaluation::class,
            User::class,
            'agent_id',     // FK sur users
            'evaluable_id', // FK sur evaluations
            'id',           // clé locale sur agents
            'id',           // clé locale sur users
        )->where('evaluations.evaluable_type', User::class);
    }

    protected function casts(): array
    {
        return [
            'date_debut_fonction' => 'date',
        ];
    }

    /** Retourne vrai si l'agent est déjà rattaché à au moins une structure. */
    public function isAffecte(): bool
    {
        return $this->entite_id !== null
            || $this->direction_id !== null
            || $this->delegation_technique_id !== null
            || $this->caisse_id !== null
            || $this->agence_id !== null
            || $this->guichet_id !== null
            || $this->service_id !== null;
    }
}
