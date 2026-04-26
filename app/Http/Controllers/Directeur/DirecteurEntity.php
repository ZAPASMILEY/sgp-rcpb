<?php

namespace App\Http\Controllers\Directeur;

use App\Models\Agent;
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
 * Tout le personnel est Agent. Un directeur est un Agent dont le compte User
 * est lié à une structure via directeur_agent_id.
 *
 * Résolution :
 *  1. On retrouve l'Agent lié au User connecté (agents.user_id = auth user id)
 *  2. On cherche quelle structure a cet agent comme directeur_agent_id
 *
 * Types de directeurs :
 *  • Directeur_Direction → Direction.directeur_agent_id
 *  • Directeur_Caisse    → Caisse.directeur_agent_id
 *  • Directeur_Tehnique  → DelegationTechnique.directeur_agent_id
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurEntity
{
    public function __construct(
        public readonly mixed  $entity,
        public readonly string $type,
        public readonly string $modelClass,
        public readonly string $serviceField,
        public readonly ?Agent $agent = null,
    ) {}

    // ── Factory ────────────────────────────────────────────────────────────

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

    public static function resolveOrFail(?User $user): static
    {
        $ctx = static::resolve($user);
        if (! $ctx) {
            abort(403, 'Aucune entité associée à votre compte. Contactez l\'administrateur.');
        }

        return $ctx;
    }

    /**
     * Résolution : trouve l'Agent du User, puis la Direction dont il est directeur.
     */
    private static function fromDirection(int $userId): ?static
    {
        $agent = Agent::where('user_id', $userId)->first();
        if (! $agent) {
            return null;
        }

        $e = Direction::where('directeur_agent_id', $agent->id)->first();

        return $e ? new static($e, 'direction', Direction::class, 'direction_id', $agent) : null;
    }

    private static function fromCaisse(int $userId): ?static
    {
        $agent = Agent::where('user_id', $userId)->first();
        if (! $agent) {
            return null;
        }

        $e = Caisse::where('directeur_agent_id', $agent->id)->first();

        return $e ? new static($e, 'caisse', Caisse::class, 'caisse_id', $agent) : null;
    }

    private static function fromDelegation(int $userId): ?static
    {
        $agent = Agent::where('user_id', $userId)->first();
        if (! $agent) {
            return null;
        }

        $e = DelegationTechnique::where('directeur_agent_id', $agent->id)->first();

        return $e ? new static($e, 'delegation', DelegationTechnique::class, 'delegation_technique_id', $agent) : null;
    }

    // ── Accessors ──────────────────────────────────────────────────────────

    public function getId(): int
    {
        return $this->entity->id;
    }

    public function getNom(): string
    {
        return match ($this->type) {
            'delegation' => trim($this->entity->region . ' — ' . ($this->entity->ville ?? '')),
            default      => (string) $this->entity->nom,
        };
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'direction'  => 'Direction',
            'caisse'     => 'Caisse',
            'delegation' => 'Délégation Technique',
        };
    }

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
     * On passe par l'Agent secrétaire pour retrouver son compte User.
     */
    public function getSecretaireUserId(): ?int
    {
        $agentId = $this->entity->secretaire_agent_id ?? null;
        if (! $agentId) {
            return null;
        }

        $agent = Agent::find($agentId);

        return $agent?->user_id;
    }

    /**
     * Retourne le nom complet du directeur depuis son enregistrement Agent.
     */
    public function getDirecteurNomPrenom(): string
    {
        if ($this->agent) {
            return trim(($this->agent->prenom ?? '') . ' ' . ($this->agent->nom ?? ''));
        }

        $agentId = $this->entity->directeur_agent_id ?? null;
        if (! $agentId) {
            return '';
        }

        $agent = Agent::find($agentId);

        return trim(($agent?->prenom ?? '') . ' ' . ($agent?->nom ?? ''));
    }

    // ── Services ───────────────────────────────────────────────────────────

    public function getServices(): Collection
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->orderBy('nom')
            ->get();
    }

    public function getServicesWithAgents(): Collection
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->with('agents')
            ->orderBy('nom')
            ->get();
    }

    public function getServiceIds(): array
    {
        return Service::where($this->serviceField, $this->entity->id)
            ->pluck('id')
            ->all();
    }

    public function serviceOwnedBy(Service $service): bool
    {
        return (int) $service->{$this->serviceField} === $this->entity->id;
    }
}
