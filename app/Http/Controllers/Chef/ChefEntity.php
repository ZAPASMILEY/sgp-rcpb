<?php

namespace App\Http\Controllers\Chef;

use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Guichet;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * ChefEntity — Contexte d'un chef connecté (service, agence ou guichet)
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Analogue à DirecteurEntity, mais pour les rôles Chef_*.
 *
 * Les trois rôles chef partagent le même espace (mêmes routes, mêmes vues,
 * mêmes contrôleurs). Ce contexte object abstrait la différence :
 *
 *  • Chef_Service  → Service.chef_agent_id   = agents.id du User connecté
 *                    Les agents sous sa responsabilité ont agents.service_id = service.id
 *
 *  • Chef_Agence   → Agence.chef_agent_id    = agents.id du User connecté
 *                    Les agents sous sa responsabilité ont agents.agence_id = agence.id
 *
 *  • Chef_Guichet  → Guichet.chef_agent_id   = agents.id du User connecté
 *                    Les agents sous sa responsabilité ont agents.guichet_id = guichet.id
 *
 * Résolution (via resolve()) :
 *  1. On retrouve l'Agent lié au User connecté (User::agent_id → Agent::id)
 *  2. Pour chaque type de chef, on cherche la structure dont chef_agent_id = cet agent
 *  3. On instancie le contexte avec la structure trouvée
 *
 * Propriétés exposées :
 *  • type       — 'service' | 'agence' | 'guichet'
 *  • entity     — l'instance du modèle (Service|Agence|Guichet)
 *  • modelClass — la classe Eloquent complète (ex: App\Models\Service::class)
 *  • agentField — le nom de la FK sur la table agents (ex: 'service_id')
 *  • agent      — l'Agent correspondant au User connecté (le chef lui-même)
 * ──────────────────────────────────────────────────────────────────────────────
 */
class ChefEntity
{
    /**
     * Constructeur : toutes les propriétés sont en lecture seule (readonly).
     * L'instanciation est toujours faite depuis les méthodes statiques.
     */
    public function __construct(
        /** L'entité gérée (Service, Agence ou Guichet) */
        public readonly mixed  $entity,

        /** Type court : 'service' | 'agence' | 'guichet' */
        public readonly string $type,

        /** Classe Eloquent complète de l'entité (pour les requêtes polymorphiques) */
        public readonly string $modelClass,

        /**
         * Nom de la colonne FK sur la table `agents` qui relie un agent à cette structure.
         * Ex: 'service_id' pour Chef_Service, 'agence_id' pour Chef_Agence, etc.
         */
        public readonly string $agentField,

        /** L'Agent qui représente le chef connecté (déduit depuis User::agent_id) */
        public readonly ?Agent $agent = null,
    ) {}

    // ══════════════════════════════════════════════════════════════════════════
    // FACTORY / RÉSOLUTION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Résout le contexte du User connecté selon son rôle.
     *
     * Retourne null si le User n'est pas un chef ou si aucune structure n'est trouvée.
     * Le contrôleur peut alors afficher une erreur ou rediriger.
     */
    public static function resolve(?User $user): ?static
    {
        // Pas de user : pas de contexte
        if (! $user) {
            return null;
        }

        // Délégation selon le rôle stocké dans users.role
        return match ($user->role) {
            'Chef_Service'         => static::fromService($user),
            'Chef_Agence'          => static::fromAgence($user),
            'Chef_Guichet'         => static::fromGuichet($user),
            'Secretaire_Agence'    => static::fromSecretaireAgence($user),
            default                => null, // Rôle non reconnu → pas de contexte chef
        };
    }

    /**
     * Comme resolve(), mais déclenche un 403 si le contexte est introuvable.
     * À utiliser dans les contrôleurs qui requièrent impérativement un contexte.
     */
    public static function resolveOrFail(?User $user): static
    {
        $ctx = static::resolve($user);
        if (! $ctx) {
            abort(403, 'Aucune structure associée à votre compte chef. Contactez l\'administrateur.');
        }

        return $ctx;
    }

    /**
     * Résout le contexte pour un Chef_Service.
     *
     * Cherche le Service dont chef_agent_id correspond à l'agent du User.
     * Un chef de service peut appartenir à une Direction, une Caisse ou une
     * DelegationTechnique — mais ici on ne filtre pas par parent, on cherche
     * simplement la structure qui désigne cet agent comme chef.
     */
    private static function fromService(User $user): ?static
    {
        // Récupère l'agent lié au compte User (via la FK user.agent_id)
        $agent = $user->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent) {
            return null;
        }

        // Cherche le Service dont cet agent est le chef désigné
        $service = Service::where('chef_agent_id', $agent->id)->first();

        return $service
            ? new static($service, 'service', Service::class, 'service_id', $agent)
            : null;
    }

    /**
     * Résout le contexte pour un Chef_Agence.
     *
     * Cherche l'Agence dont chef_agent_id correspond à l'agent du User.
     */
    private static function fromAgence(User $user): ?static
    {
        $agent = $user->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent) {
            return null;
        }

        $agence = Agence::where('chef_agent_id', $agent->id)->first();

        return $agence
            ? new static($agence, 'agence', Agence::class, 'agence_id', $agent)
            : null;
    }

    /**
     * Résout le contexte pour un Chef_Guichet.
     *
     * Cherche le Guichet dont chef_agent_id correspond à l'agent du User.
     */
    private static function fromGuichet(User $user): ?static
    {
        $agent = $user->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent) {
            return null;
        }

        $guichet = Guichet::where('chef_agent_id', $agent->id)->first();

        return $guichet
            ? new static($guichet, 'guichet', Guichet::class, 'guichet_id', $agent)
            : null;
    }

    /**
     * Résout le contexte pour une Secretaire_Agence.
     *
     * La secrétaire est rattachée à l'agence via agents.agence_id.
     * Elle obtient le même périmètre de vue que le chef de cette agence
     * (tous les agents dont agents.agence_id = agence.id).
     */
    private static function fromSecretaireAgence(User $user): ?static
    {
        $agent = $user->agent_id ? Agent::find($user->agent_id) : null;
        if (! $agent || ! $agent->agence_id) {
            return null;
        }

        $agence = Agence::find($agent->agence_id);

        return $agence
            ? new static($agence, 'agence', Agence::class, 'agence_id', $agent)
            : null;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ACCESSEURS — IDENTITÉ
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Retourne l'ID de la structure gérée.
     */
    public function getId(): int
    {
        return (int) $this->entity->id;
    }

    /**
     * Retourne le nom de la structure gérée.
     *
     * Pour Service et Agence : champ 'nom'.
     * Pour Guichet : champ 'nom' également (défini dans la migration).
     */
    public function getNom(): string
    {
        return (string) ($this->entity->nom ?? '');
    }

    /**
     * Retourne le libellé humain du type de structure.
     * Utilisé dans les en-têtes et les titres de page.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'service' => 'Service',
            'agence'  => 'Agence',
            'guichet' => 'Guichet',
            default   => ucfirst($this->type),
        };
    }

    /**
     * Retourne le libellé du rôle du chef pour les en-têtes de formulaires.
     */
    public function getRoleLabel(): string
    {
        return match ($this->type) {
            'service' => 'Chef de Service',
            'agence'  => "Chef d'Agence",
            'guichet' => 'Chef de Guichet',
            default   => 'Chef',
        };
    }

    /**
     * Retourne le nom complet du chef (prénom + nom de l'Agent).
     * Utile pour les formulaires d'identification d'évaluation.
     */
    public function getChefNomPrenom(): string
    {
        if ($this->agent) {
            return trim(($this->agent->prenom ?? '') . ' ' . ($this->agent->nom ?? ''));
        }

        return '';
    }

    /**
     * Retourne le nom de la structure parente (le niveau hiérarchique supérieur).
     *
     * Service → peut appartenir à :
     *   a) Direction (direction_id → Direction::nom)
     *   b) Caisse    (caisse_id   → Caisse::nom)
     *   c) DelegationTechnique (delegation_technique_id → DelegationTechnique::region)
     *
     * Agence → appartient à une Caisse (caisse_id → Caisse::nom)
     *
     * Guichet → appartient à une Agence (agence_id → Agence::nom)
     *
     * Retourne une chaîne vide si le parent n'est pas trouvé.
     */
    public function getParentNom(): string
    {
        return match ($this->type) {
            'service' => $this->getServiceParentNom(),
            'agence'  => $this->getAgenceParentNom(),
            'guichet' => $this->getGuichetParentNom(),
            default   => '',
        };
    }

    /**
     * Nom du parent d'un Service.
     * Un service peut être rattaché à une Direction, une Caisse ou une DelegationTechnique.
     * On teste les trois FK dans l'ordre de priorité.
     */
    private function getServiceParentNom(): string
    {
        /** @var Service $service */
        $service = $this->entity;

        // Cas 1 : rattaché à une Direction (via direction_id)
        if (! blank($service->direction_id)) {
            $direction = Direction::find($service->direction_id);
            return $direction ? (string) $direction->nom : '';
        }

        // Cas 2 : rattaché à une Caisse (via caisse_id)
        if (! blank($service->caisse_id)) {
            $caisse = Caisse::find($service->caisse_id);
            return $caisse ? (string) $caisse->nom : '';
        }

        // Cas 3 : rattaché à une DelegationTechnique (via delegation_technique_id)
        if (! blank($service->delegation_technique_id)) {
            $dt = DelegationTechnique::find($service->delegation_technique_id);
            // La DelegationTechnique utilise `region` (+ optionnellement `ville`) comme identifiant
            return $dt ? trim(($dt->region ?? '') . ' — ' . ($dt->ville ?? '')) : '';
        }

        // Aucun parent trouvé
        return '';
    }

    /**
     * Nom du parent d'une Agence (toujours une Caisse).
     */
    private function getAgenceParentNom(): string
    {
        /** @var Agence $agence */
        $agence = $this->entity;

        if (blank($agence->caisse_id)) {
            return '';
        }

        $caisse = Caisse::find($agence->caisse_id);

        return $caisse ? (string) $caisse->nom : '';
    }

    /**
     * Nom du parent d'un Guichet (toujours une Agence).
     */
    private function getGuichetParentNom(): string
    {
        /** @var Guichet $guichet */
        $guichet = $this->entity;

        if (blank($guichet->agence_id)) {
            return '';
        }

        $agence = Agence::find($guichet->agence_id);

        return $agence ? (string) $agence->nom : '';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // GESTION DES AGENTS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Retourne tous les agents rattachés à la structure gérée par ce chef.
     *
     * La FK utilisée dépend du type de chef :
     *   Chef_Service  → agents.service_id = entity.id
     *   Chef_Agence   → agents.agence_id  = entity.id
     *   Chef_Guichet  → agents.guichet_id = entity.id
     *
     * On exclut le chef lui-même (l'utilisateur connecté) pour éviter
     * qu'il apparaisse dans sa propre liste de subordonnés.
     */
    public function getAgents(): Collection
    {
        return Agent::where($this->agentField, $this->entity->id)
            ->when(
                $this->agent,
                // Exclure l'agent-chef de la liste des subordonnés
                fn ($q) => $q->where('id', '!=', $this->agent->id)
            )
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
    }

    /**
     * Retourne uniquement les IDs des agents subordonnés (sans le chef).
     * Utilisé pour les vérifications d'appartenance et les requêtes "in".
     */
    public function getAgentIds(): array
    {
        return Agent::where($this->agentField, $this->entity->id)
            ->when(
                $this->agent,
                fn ($q) => $q->where('id', '!=', $this->agent->id)
            )
            ->pluck('id')
            ->all();
    }

    /**
     * Vérifie qu'un Agent donné appartient bien à la structure gérée par ce chef.
     *
     * Contrôle la FK `agentField` sur le modèle Agent.
     * Interdit aussi l'évaluation du chef par lui-même (l'agent ne doit pas
     * être l'agent-chef lui-même).
     *
     * Retourne true seulement si :
     *   1. La FK agentField de l'agent pointe vers cette structure
     *   2. L'agent n'est pas le chef lui-même
     */
    public function agentOwnedBy(Agent $agent): bool
    {
        // Vérifie que l'agent appartient bien à cette structure
        $inStructure = (int) $agent->{$this->agentField} === $this->entity->id;

        // Le chef ne peut pas s'évaluer lui-même
        $notSelf = $this->agent ? $agent->id !== $this->agent->id : true;

        return $inStructure && $notSelf;
    }
}
