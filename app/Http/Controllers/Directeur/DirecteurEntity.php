<?php

namespace App\Http\Controllers\Directeur;

use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurEntity — Contexte d'un directeur connecté
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Le système reconnaît trois types de directeurs, chacun gérant une entité
 * organisationnelle différente :
 *
 *  • Directeur_Direction  → gère une Direction (faîtière)
 *  • Directeur_Caisse     → gère une Caisse
 *  • Directeur_Tehnique   → gère une DelegationTechnique (région)
 *
 * Cette classe résout l'entité associée au compte connecté et expose une
 * interface uniforme utilisée par tous les controllers du namespace Directeur,
 * quel que soit le type de directeur.
 *
 * USAGE TYPE dans un controller :
 *   $ctx = DirecteurEntity::resolveOrFail(Auth::user());
 *   $ctx->entity        // Direction|Caisse|DelegationTechnique
 *   $ctx->modelClass    // FQCN de l'entité (pour les relations polymorphiques)
 *   $ctx->getId()       // id de l'entité
 *   $ctx->getServices() // services rattachés à l'entité
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurEntity
{
    /**
     * @param  Direction|Caisse|DelegationTechnique  $entity       Entité résolue
     * @param  'direction'|'caisse'|'delegation'     $type         Type court
     * @param  string                                $modelClass   FQCN (pour les champs polymorphiques evaluable_type / assignable_type)
     * @param  string                                $serviceField Nom de la FK sur la table `services` qui pointe vers cette entité
     *                                                             ex: 'direction_id', 'caisse_id', 'delegation_technique_id'
     */
    public function __construct(
        public readonly mixed  $entity,
        public readonly string $type,
        public readonly string $modelClass,
        public readonly string $serviceField,
    ) {}

    // ── Factory ────────────────────────────────────────────────────────────

    /**
     * Tente de résoudre l'entité associée au rôle de l'utilisateur.
     * Retourne null si l'utilisateur n'est pas un directeur ou si aucune
     * entité n'est liée à son compte (user_id non renseigné dans la table).
     */
    public static function resolve(?User $user): ?static
    {
        if (! $user) {
            return null;
        }

        return match ($user->role) {
            'Directeur_Direction' => static::fromDirection($user->id),
            'Directeur_Caisse'    => static::fromCaisse($user->id),
            'Directeur_Tehnique'  => static::fromDelegation($user->id),
            default               => null,
        };
    }

    /**
     * Comme resolve() mais déclenche une erreur 403 si aucune entité n'est trouvée.
     * À utiliser dans les controllers pour garantir qu'une entité est bien présente.
     */
    public static function resolveOrFail(?User $user): static
    {
        $ctx = static::resolve($user);
        if (! $ctx) {
            abort(403, 'Aucune entité associée à votre compte. Contactez l\'administrateur.');
        }

        return $ctx;
    }

    /** Résolution pour un Directeur_Direction : cherche la Direction dont user_id = $userId */
    private static function fromDirection(int $userId): ?static
    {
        $e = Direction::where('user_id', $userId)->first();

        return $e ? new static($e, 'direction', Direction::class, 'direction_id') : null;
    }

    /** Résolution pour un Directeur_Caisse : cherche la Caisse dont user_id = $userId */
    private static function fromCaisse(int $userId): ?static
    {
        $e = Caisse::where('user_id', $userId)->first();

        return $e ? new static($e, 'caisse', Caisse::class, 'caisse_id') : null;
    }

    /** Résolution pour un Directeur_Tehnique : cherche la DelegationTechnique dont user_id = $userId */
    private static function fromDelegation(int $userId): ?static
    {
        $e = DelegationTechnique::where('user_id', $userId)->first();

        return $e ? new static($e, 'delegation', DelegationTechnique::class, 'delegation_technique_id') : null;
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    /** Retourne l'id de l'entité (Direction, Caisse ou DelegationTechnique). */
    public function getId(): int
    {
        return $this->entity->id;
    }

    /**
     * Retourne le nom lisible de l'entité.
     * Pour une DelegationTechnique (qui n'a pas de champ `nom`),
     * on concatène région et ville : "Région — Ville".
     */
    public function getNom(): string
    {
        return match ($this->type) {
            'delegation' => trim($this->entity->region.' — '.($this->entity->ville ?? '')),
            default      => (string) $this->entity->nom,
        };
    }

    /** Retourne le libellé du type d'entité pour l'affichage (ex: "Caisse"). */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'direction'  => 'Direction',
            'caisse'     => 'Caisse',
            'delegation' => 'Délégation Technique',
        };
    }

    /** Retourne le libellé du rôle du directeur pour l'affichage dans les évaluations. */
    public function getRoleLabel(): string
    {
        return match ($this->type) {
            'direction'  => 'Directeur de Direction',
            'caisse'     => 'Directeur de Caisse',
            'delegation' => 'Directeur Technique',
        };
    }

    /**
     * Retourne l'id User de la secrétaire liée à l'entité, ou null.
     * Utilisé pour autoriser les actions sur les évaluations / objectifs de la secrétaire.
     */
    public function getSecretaireUserId(): ?int
    {
        $v = $this->entity->secretaire_user_id ?? null;

        return $v ? (int) $v : null;
    }

    /** Retourne le nom complet du directeur (champs directeur_prenom + directeur_nom). */
    public function getDirecteurNomPrenom(): string
    {
        return trim(($this->entity->directeur_prenom ?? '').' '.($this->entity->directeur_nom ?? ''));
    }

    // ── Services ───────────────────────────────────────────────────────────

    /**
     * Retourne tous les services rattachés à l'entité, triés par nom.
     * La requête utilise $serviceField comme colonne de filtre (FK vers l'entité).
     */
    public function getServices(): Collection
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->orderBy('nom')
            ->get();
    }

    /**
     * Comme getServices() mais charge également les agents de chaque service
     * (relation eager-loading) pour éviter les N+1 queries.
     */
    public function getServicesWithAgents(): Collection
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->with('agents')
            ->orderBy('nom')
            ->get();
    }

    /**
     * Retourne un tableau d'IDs de services appartenant à l'entité.
     * Utilisé pour valider que le service_id soumis dans un formulaire
     * appartient bien à ce directeur.
     */
    public function getServiceIds(): array
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->pluck('id')
            ->all();
    }

    /**
     * Vérifie qu'un service appartient à l'entité de ce directeur.
     * Utilisé dans les guards d'autorisation avant toute action sur un service.
     *
     * Ex: if (!$ctx->serviceOwnedBy($service)) abort(403);
     */
    public function serviceOwnedBy(Service $service): bool
    {
        return (int) $service->{$this->serviceField} === $this->entity->id;
    }
}
