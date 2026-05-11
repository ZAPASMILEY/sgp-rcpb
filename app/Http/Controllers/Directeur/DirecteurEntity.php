<?php

namespace App\Http\Controllers\Directeur;

use App\Models\Agent;
use App\Models\Agence;
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
 *  • Directeur_Direction → Direction.directeur_agent_id       (gère services)
 *  • Directeur_Caisse    → Caisse.directeur_agent_id          (gère services + agences + guichets)
 *  • Directeur_Technique → DelegationTechnique.directeur_agent_id (gère services)
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
            'Directeur_Technique' => static::fromDelegation($user->id),
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

    private static function fromDirection(int $userId): ?static
    {
        $user  = User::find($userId);
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent) {
            return null;
        }

        $e = Direction::where('directeur_agent_id', $agent->id)->first();

        return $e ? new static($e, 'direction', Direction::class, 'direction_id', $agent) : null;
    }

    private static function fromCaisse(int $userId): ?static
    {
        $user  = User::find($userId);
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent) {
            return null;
        }

        $e = Caisse::where('directeur_agent_id', $agent->id)->first();

        return $e ? new static($e, 'caisse', Caisse::class, 'caisse_id', $agent) : null;
    }

    private static function fromDelegation(int $userId): ?static
    {
        $user  = User::find($userId);
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;
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

    public function getSecretaireUserId(): ?int
    {
        $agentId = $this->entity->secretaire_agent_id ?? null;
        if (! $agentId) {
            return null;
        }

        return User::where('agent_id', $agentId)->value('id');
    }

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

    // ── Agences (Directeur_Caisse uniquement) ──────────────────────────────

    /** Indique si ce contexte gère des agences. */
    public function hasAgences(): bool
    {
        return $this->type === 'caisse';
    }

    public function getAgences(): Collection
    {
        if (! $this->hasAgences()) {
            return new Collection();
        }

        return Agence::where('caisse_id', $this->entity->id)
            ->orderBy('nom')
            ->get();
    }

    public function getAgencesWithGuichets(): Collection
    {
        if (! $this->hasAgences()) {
            return new Collection();
        }

        return Agence::where('caisse_id', $this->entity->id)
            ->with(['chef', 'guichets'])
            ->withCount('agents')
            ->orderBy('nom')
            ->get();
    }

    public function agenceOwnedBy(Agence $agence): bool
    {
        return $this->hasAgences() && (int) $agence->caisse_id === $this->entity->id;
    }

    // ── Caisses (Directeur_Technique uniquement) ────────────────────────

    public function hasCaisses(): bool
    {
        return $this->type === 'delegation';
    }

    public function getCaisses(): Collection
    {
        if (! $this->hasCaisses()) {
            return new Collection();
        }

        return \App\Models\Caisse::where('delegation_technique_id', $this->entity->id)
            ->orderBy('nom')
            ->get();
    }

    public function getCaissesWithDirecteur(): Collection
    {
        if (! $this->hasCaisses()) {
            return new Collection();
        }

        return \App\Models\Caisse::where('delegation_technique_id', $this->entity->id)
            ->with(['directeurAgent'])
            ->withCount('agents')
            ->orderBy('nom')
            ->get();
    }

    public function caisseOwnedBy(\App\Models\Caisse $caisse): bool
    {
        return $this->hasCaisses() && (int) $caisse->delegation_technique_id === $this->entity->id;
    }
}
