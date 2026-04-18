<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Direction;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * Caisse — Entité organisationnelle de type caisse
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Représente une caisse dans le réseau de l'organisation.
 * Une caisse est rattachée à une DelegationTechnique et peut superviser
 * plusieurs agences.
 *
 * Liens vers les comptes utilisateurs :
 *  • user_id             → compte du directeur de caisse (rôle : Directeur_Caisse)
 *  • secretaire_user_id  → compte de la secrétaire (rôle : personnel)
 *
 * Ces deux champs permettent à DirecteurEntity de résoudre la Caisse à partir
 * du directeur connecté, et d'identifier la secrétaire pour les évaluations.
 *
 * Structure d'organisation :
 *  DelegationTechnique → Caisse → Agences
 *                              → Services (via caisse_id sur la table services)
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * @property int         $id
 * @property int|null    $user_id               Compte du directeur de caisse
 * @property int|null    $secretaire_user_id     Compte de la secrétaire
 * @property int|null    $delegation_technique_id
 * @property int|null    $ville_id
 * @property string      $nom
 * @property string|null $directeur_prenom
 * @property string|null $directeur_nom
 */
class Caisse extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',              // Directeur de caisse (lié au compte User)
        'secretaire_user_id',   // Secrétaire (lié au compte User)
        'delegation_technique_id',
        'ville_id',
        'nom',
        'annee_ouverture',
        'quartier',
        'directeur_prenom',
        'directeur_nom',
        'directeur_sexe',
        'directeur_email',
        'directeur_telephone',
        'directeur_date_debut_mois',
        'secretariat_telephone',
        'secretaire_prenom',
        'secretaire_nom',
        'secretaire_sexe',
        'secretaire_email',
        'secretaire_telephone',
        'secretaire_date_debut_mois',
        'superviseur_direction_id',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    /**
     * Le compte utilisateur du directeur de caisse.
     * Utilisé par DirecteurEntity::fromCaisse() pour résoudre la Caisse.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le compte utilisateur de la secrétaire de la caisse.
     * Utilisé par DirecteurEntity::getSecretaireUserId() pour identifier la secrétaire.
     */
    public function secretaireUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretaire_user_id');
    }

    /** La délégation technique dont dépend cette caisse. */
    public function delegationTechnique(): BelongsTo
    {
        return $this->belongsTo(DelegationTechnique::class);
    }

    /** La ville où est implantée la caisse. */
    public function ville(): BelongsTo
    {
        return $this->belongsTo(Ville::class);
    }

    /**
     * La direction de supervision (faîtière) qui supervise cette caisse.
     * Correspond à superviseur_direction_id.
     */
    public function superviseurDirection(): BelongsTo
    {
        return $this->belongsTo(Direction::class, 'superviseur_direction_id');
    }

    /**
     * Les agences supervisées par cette caisse (via superviseur_caisse_id sur la table agences).
     */
    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class, 'superviseur_caisse_id');
    }

    /**
     * Les services rattachés à cette caisse (via caisse_id sur la table services).
     * Utilisé par DirecteurEntity::getServices() via le champ serviceField = 'caisse_id'.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
