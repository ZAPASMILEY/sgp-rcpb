<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\CustomRole;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Correspondance fonction (agents.role) → rôle système (users.role).
     * Permet de pré-remplir le select rôle lors de la création d'un compte.
     */
    public const FONCTION_TO_ROLE = [
        'PCA'                     => 'PCA',
        'Directeur Général'       => 'DG',
        'DGA'                     => 'DGA',
        'Assistante DG'           => 'Assistante_Dg',
        'Conseiller DG'           => 'Conseillers_Dg',
        'Secrétaire Assistante'   => 'Secretaire_Assistante',
        'Directeur de Direction'  => 'Directeur_Direction',
        'Secrétaire de Direction' => 'Secretaire_Direction',
        'Directeur Technique'     => 'Directeur_Technique',
        'Secrétaire Technique'    => 'Secretaire_Technique',
        'Directeur de Caisse'     => 'Directeur_Caisse',
        'Secrétaire de Caisse'    => 'Secretaire_Caisse',
        "Chef d'Agence"           => 'Chef_Agence',
        "Secrétaire d'Agence"     => 'Secretaire_Agence',
        'Chef de Guichet'         => 'Chef_Guichet',
        'Chef de Service'         => 'Chef_Service',
        'Agent'                   => 'Agent',
    ];

    /**
     * Rôles système intégrés (immuables).
     * Valeur stockée en BD → libellé affiché.
     */
    public const ROLES = [
        'Admin'                 => 'Administrateur',
        'PCA'                   => 'PCA',
        'DG'                    => 'Directeur Général',
        'DGA'                   => 'DGA',
        'Assistante_Dg'         => 'Assistante DG',
        'Secretaire_Assistante' => 'Secrétaire Assistante DG',
        'Conseillers_Dg'        => 'Conseiller DG',
        'Directeur_Direction'   => 'Directeur de Direction',
        'Directeur_Technique'   => 'Directeur Technique',
        'Directeur_Caisse'      => 'Directeur de Caisse',
        'Secretaire_Direction'  => 'Secrétaire de Direction',
        'Secretaire_Technique'  => 'Secrétaire Technique',
        'Secretaire_Caisse'     => 'Secrétaire de Caisse',
        'Secretaire_Agence'     => "Secrétaire d'Agence",
        'Chef_Agence'           => "Chef d'Agence",
        'Chef_Guichet'          => 'Chef de Guichet',
        'Chef_Service'          => 'Chef de Service',
        'RH'                    => 'Responsable RH',
        'Agent'                 => 'Agent',
    ];

    /**
     * Retourne tous les rôles disponibles : système + rôles personnalisés en base.
     *
     * @return array<string, string>  slug => label
     */
    public static function allRoles(): array
    {
        return array_merge(self::ROLES, CustomRole::allAsMap());
    }

    /**
     * Permissions que l'admin peut attribuer individuellement à n'importe quel utilisateur.
     * Format : permission_name => libellé affiché.
     */
    public const GRANTABLE_PERMISSIONS = [
        'formations.assigner' => 'Gérer les formations (créer, modifier, supprimer)',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->with([
                'agent.entite',
                'agent.direction',
                'agent.delegationTechnique',
                'agent.caisse',
                'agent.agence',
                'agent.guichet',
                'agent.service',
                'agent.directedDirection',
                'agent.directedDelegation',
                'agent.directedCaisse',
                'agent.ledAgence',
                'agent.ledGuichet',
                'agent.ledService',
            ])
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%")
                      ->orWhereHas('agent', fn ($aq) => $aq
                          ->where('nom', 'like', "%{$search}%")
                          ->orWhere('prenom', 'like', "%{$search}%")
                          ->orWhere('role', 'like', "%{$search}%")
                      );
                });
            })
            ->orderBy('name')
            ->get();

        return view('admin.users.index', ['users' => $users, 'search' => $search]);
    }

    public function create(Request $request): View
    {
        $preselectedAgentId = (int) $request->query('agent_id', 0);
        $preselectedAgent   = $preselectedAgentId > 0
            ? Agent::query()->find($preselectedAgentId, ['id', 'nom', 'prenom', 'email', 'role'])
            : null;

        $agents = Agent::query()
            ->doesntHave('user')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'email', 'role']);

        $managers = User::query()
            ->with(['agent.entite', 'agent.direction', 'agent.delegationTechnique', 'agent.caisse', 'agent.agence', 'agent.service'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'agent_id']);

        // Map rôle_subordonné → rôles_managers autorisés, pour filtrage JS dans le formulaire create
        $managerRolesMap = [
            'Agent'                  => ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'],
            'Chef_Service'           => ['Directeur_Caisse', 'Directeur_Technique', 'Directeur_Direction'],
            'Chef_Agence'            => ['Directeur_Caisse', 'Directeur_Technique'],
            'Chef_Guichet'           => ['Chef_Agence', 'Directeur_Caisse'],
            'Secretaire_Caisse'      => ['Directeur_Caisse'],
            'Directeur_Caisse'       => ['Directeur_Technique'],
            'Secretaire_Technique'   => ['Directeur_Technique'],
            'Directeur_Technique'    => ['DGA', 'DG'],
            'Secretaire_Direction'   => ['Directeur_Direction'],
            'Directeur_Direction'    => ['DGA', 'DG'],
            'DGA'                    => ['DG'],
            'Assistante_Dg'          => ['DG'],
            'Secretaire_Assistante'  => ['DG'],
            'Conseillers_Dg'         => ['DG'],
        ];

        return view('admin.users.create', [
            'agents'           => $agents,
            'preselectedAgent' => $preselectedAgent,
            'managers'         => $managers,
            'managerRolesMap'  => $managerRolesMap,
            'roles'            => self::allRoles(),
            'fonctionToRole'   => self::FONCTION_TO_ROLE,
            'entites'          => \App\Models\Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'       => ['required', 'integer', 'exists:agents,id', Rule::unique('users', 'agent_id')],
            'role'           => ['required', 'string', Rule::in(array_keys(self::allRoles()))],
            'manager_id'     => ['nullable', 'integer', 'exists:users,id'],
            'email'          => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'entite_id'  => [
                Rule::requiredIf(in_array($request->input('role'), ['PCA', 'Conseillers_Dg'], true)),
                'nullable', 'integer', 'exists:entites,id',
            ],
        ], [
            'entite_id.required' => 'Ce rôle nécessite de sélectionner une entité faîtière.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);

        User::create([
            'agent_id'              => $agent->id,
            'name'                  => $agent->prenom . ' ' . $agent->nom,
            'email'                 => $validated['email'],
            'password'              => Hash::make($validated['password']),
            'password_plain'        => $validated['password'],
            'role'                  => $validated['role'],
            'manager_id'            => $validated['manager_id'] ?? null,
            'is_active'             => false,
            'must_change_password'  => true,
        ]);

        // entite_id est désormais sur agents (PCA et Conseillers_Dg)
        $rolesAvecEntite = ['PCA', 'Conseillers_Dg'];
        if (in_array($validated['role'], $rolesAvecEntite, true)) {
            $agent->entite_id = $validated['entite_id'] ?? null;
            $agent->save();
        }

        // Synchronise le champ FK de l'entité faîtière selon le rôle
        $this->syncEntiteAgentField($validated['role'], $agent->id);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte utilisateur créé avec succès.');
    }

    /**
     * Rôles éligibles comme supérieur N+1 selon le rôle du subordonné.
     */
    private static function managerRolesFor(string $role): array
    {
        return match ($role) {
            'Agent'                  => ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'],
            'Chef_Service'           => ['Directeur_Caisse', 'Directeur_Technique', 'Directeur_Direction'],
            'Chef_Agence'            => ['Directeur_Caisse', 'Directeur_Technique'],
            'Chef_Guichet'           => ['Chef_Agence', 'Directeur_Caisse'],
            'Secretaire_Caisse',
            'Directeur_Caisse'       => ['Directeur_Technique'],
            'Secretaire_Technique',
            'Directeur_Technique'    => ['DGA', 'DG'],
            'Secretaire_Direction',
            'Directeur_Direction'    => ['DGA', 'DG'],
            'DGA', 'Assistante_Dg',
            'Secretaire_Assistante',
            'Conseillers_Dg'         => ['DG'],
            default                  => [],   // pas de filtre : tous les utilisateurs
        };
    }

    public function edit(User $user): View
    {
        $allowedRoles = self::managerRolesFor($user->role);

        $managers = User::query()
            ->where('id', '!=', $user->id)
            ->when($allowedRoles, fn ($q) => $q->whereIn('role', $allowedRoles))
            ->with(['agent.entite', 'agent.direction', 'agent.delegationTechnique', 'agent.caisse', 'agent.agence', 'agent.service'])
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'agent_id']);

        // Permissions individuelles actuelles de l'utilisateur (hors rôle)
        $userDirectPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        return view('admin.users.edit', [
            'user'                  => $user->load('agent'),
            'managers'              => $managers,
            'roles'                 => self::allRoles(),
            'entites'               => \App\Models\Entite::query()->orderBy('nom')->get(['id', 'nom']),
            'grantablePermissions'  => self::GRANTABLE_PERMISSIONS,
            'userDirectPermissions' => $userDirectPermissions,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role'          => ['required', 'string', Rule::in(array_keys(self::allRoles()))],
            'manager_id'    => ['nullable', 'integer', 'exists:users,id', Rule::notIn([$user->id])],
            'email'         => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => ['nullable', 'confirmed', Password::min(8)],
            'entite_id' => [
                Rule::requiredIf(in_array($request->input('role'), ['PCA', 'Conseillers_Dg'], true)),
                'nullable', 'integer', 'exists:entites,id',
            ],
        ], [
            'entite_id.required' => 'Ce rôle nécessite de sélectionner une entité faîtière.',
        ]);

        $data = [
            'role'       => $validated['role'],
            'manager_id' => $validated['manager_id'] ?? null,
            'email'      => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password']             = Hash::make($validated['password']);
            $data['password_plain']       = $validated['password'];
            $data['must_change_password'] = true;
        }

        $user->update($data);

        // ── Permissions individuelles (grantable) ────────────────────────────
        $requestedPerms = (array) $request->input('extra_permissions', []);
        $permissionsToSync = [];
        foreach (array_keys(self::GRANTABLE_PERMISSIONS) as $perm) {
            if (in_array($perm, $requestedPerms, true)) {
                $permissionsToSync[] = Permission::findOrCreate($perm, 'web');
            }
        }
        $user->syncPermissions($permissionsToSync);

        // entite_id est désormais sur agents (PCA et Conseillers_Dg)
        if ($user->agent_id) {
            $rolesAvecEntite = ['PCA', 'Conseillers_Dg'];
            $agent = Agent::findOrFail($user->agent_id);
            $agent->entite_id = in_array($validated['role'], $rolesAvecEntite, true)
                ? ($validated['entite_id'] ?? null)
                : null;
            $agent->save();

            $this->syncEntiteAgentField($validated['role'], $user->agent_id);

            // ── Affectation automatique via supérieur ───────────────────────
            // Si un supérieur est sélectionné et que l'agent n'est pas encore affecté,
            // on copie l'affectation structurelle du supérieur vers cet agent.
            if (!empty($validated['manager_id'])) {
                $managerAgent = User::find($validated['manager_id'])?->agent;
                if ($managerAgent && ! $agent->fresh()->isAffecte()) {
                    $agent->update([
                        'entite_id'               => $managerAgent->entite_id,
                        'direction_id'            => $managerAgent->direction_id,
                        'delegation_technique_id' => $managerAgent->delegation_technique_id,
                        'caisse_id'               => $managerAgent->caisse_id,
                        'agence_id'               => $managerAgent->agence_id,
                        'guichet_id'              => $managerAgent->guichet_id,
                        'service_id'              => $managerAgent->service_id,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte mis à jour avec succès.');
    }

    /**
     * Met à jour le champ FK correspondant sur l'entité faîtière
     * quand un compte DG / DGA / Assistante_Dg / PCA est créé ou modifié.
     */
    private function syncEntiteAgentField(string $role, int $agentId): void
    {
        $column = match ($role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            'PCA'           => 'pca_agent_id',
            default         => null,
        };

        if ($column === null) {
            return;
        }

        $entite = Entite::query()->latest()->first();
        if ($entite) {
            $entite->update([$column => $agentId]);
            // Synchronise aussi agents.entite_id pour que l'affectation soit visible partout
            Agent::where('id', $agentId)->update(['entite_id' => $entite->id]);
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        // Supprime le compte mais conserve l'agent
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte supprimé. L\'agent est conservé.');
    }

    /**
     * Active ou désactive le compte utilisateur.
     */
    public function toggleActive(User $user): RedirectResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'activé' : 'désactivé';

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Compte de {$user->name} {$label} avec succès.");
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur et retourne le mot de passe généré.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $plain = Str::random(12);
        $user->update([
            'password'             => Hash::make($plain),
            'password_plain'       => $plain,
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Mot de passe réinitialisé : {$plain} — Communiquez-le à l'utilisateur.")
            ->with('generated_password', $plain);
    }

    /**
     * Remet le mot de passe à la valeur par défaut (11111111).
     * Utilisé quand un agent a oublié son mot de passe.
     */
    public function resetToDefault(User $user): RedirectResponse
    {
        $user->update([
            'password'             => Hash::make('11111111'),
            'password_plain'       => '11111111',
            'must_change_password' => true,
        ]);

        return back()->with('status', "Mot de passe de {$user->name} remis à 11111111. L'agent devra le changer à la prochaine connexion.");
    }

    /**
     * Lève la suspension anti-brute force d'un compte.
     */
    public function unblock(User $user): RedirectResponse
    {
        $user->update(['blocked_until' => null]);

        return back()->with('status', "Le compte de {$user->name} a été débloqué.");
    }
}
