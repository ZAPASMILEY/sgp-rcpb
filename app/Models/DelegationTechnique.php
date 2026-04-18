<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DelegationTechnique — Entité organisationnelle régionale
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Représente une délégation technique (bureau régional) dans le réseau.
 * Une délégation supervise plusieurs caisses et peut avoir des directions
 * qui lui sont rattachées.
 *
 * PARTICULARITÉ : Contrairement à Direction et Caisse, DelegationTechnique
 * n'a PAS de champ `nom`. Le nom est composé dynamiquement : "$region — $ville".
 * Cette logique est centralisée dans DirecteurEntity::getNom().
 *
 * Liens vers les comptes utilisateurs :
 *  • user_id             → compte du directeur technique (rôle : Directeur_Tehnique)
 *  • secretaire_user_id  → compte de la secrétaire
 *
 * Relations de services :
 *  • services()       — services rattachés via les directions (HasManyThrough Direction)
 *  • directServices() — services rattachés directement à la délégation
 *                       (via delegation_technique_id sur la table services)
 *
 * C'est directServices() qui est utilisé par DirecteurEntity via le serviceField
 * 'delegation_technique_id' pour lister les services du directeur technique.
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * @property int         $id
 * @property int|null    $user_id              Compte du directeur technique
 * @property int|null    $secretaire_user_id   Compte de la secrétaire
 * @property string      $region               Nom de la région (sert de "nom")
 * @property string|null $ville                Ville principale (complément du nom)
 */
class DelegationTechnique extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',              // Directeur technique (lié au compte User)
        'secretaire_user_id',   // Secrétaire (lié au compte User)
        'region',
        'ville',
        'secretariat_telephone',
        'directeur_prenom',
        'directeur_nom',
        'directeur_sexe',
        'directeur_email',
        'directeur_telephone',
        'directeur_date_debut_mois',
        'directeur_photo_path',
        'secretaire_prenom',
        'secretaire_nom',
        'secretaire_sexe',
        'secretaire_email',
        'secretaire_telephone',
        'secretaire_date_debut_mois',
    ];

    // ── Relations ──────────────────────────────────────────────────────────

    /**
     * Le compte utilisateur du directeur technique.
     * Utilisé par DirecteurEntity::fromDelegation() pour résoudre la délégation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Le compte utilisateur de la secrétaire de la délégation.
     * Utilisé par DirecteurEntity::getSecretaireUserId().
     */
    public function secretaireUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretaire_user_id');
    }

    /** Les directions rattachées à cette délégation. */
    public function directions(): HasMany
    {
        return $this->hasMany(Direction::class);
    }

    /** Les agences directement rattachées à cette délégation. */
    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }

    /**
     * Services passant par les directions de la délégation (relation intermédiaire).
     * NB : Ces services ont direction_id renseigné, pas delegation_technique_id.
     * Utilisé pour des agrégats globaux uniquement.
     */
    public function services(): HasManyThrough
    {
        return $this->hasManyThrough(Service::class, Direction::class, 'delegation_technique_id', 'direction_id');
    }

    /**
     * Services directement rattachés à la délégation (via delegation_technique_id sur services).
     * C'est cette relation que DirecteurEntity utilise (serviceField = 'delegation_technique_id').
     */
    public function directServices(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** Les caisses rattachées à cette délégation. */
    public function caisses(): HasMany
    {
        return $this->hasMany(Caisse::class);
    }

    /** Les agents directement rattachés à la délégation. */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    /** Les villes de la région couverte par cette délégation. */
    public function villes(): HasMany
    {
        return $this->hasMany(Ville::class);
    }
}
