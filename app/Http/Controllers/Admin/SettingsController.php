<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomRole;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission; // Remplace l'ancien App\Models\Permission
use Spatie\Permission\Models\Role;       // Remplace l'ancien App\Models\Role

class SettingsController extends Controller
{
    /**
     * Affiche la page des paramètres admin.
     * Contient plusieurs onglets : thème, sécurité, rôles, droits individuels, catalogue.
     */
    public function edit(Request $request): View
    {
        // ── Onglet Catalogue : toutes les permissions disponibles ─────────
        $permissions = Permission::orderBy('name')->get();

        // Grouper les permissions par préfixe pour l'affichage dans les onglets Rôles et Catalogue.
        // Ordre d'affichage : Évaluations → Objectifs → Personnel → Structures → Administration
        $permissionGroups = [
            'evaluations' => [
                'label' => 'Évaluations',
                'icon'  => 'fas fa-clipboard-check',
                'color' => 'indigo',
                'items' => [],
                'labels' => [
                    'evaluations.creer'        => 'Créer une fiche d\'évaluation',
                    'evaluations.soumettre'    => 'Soumettre une évaluation',
                    'evaluations.accepter'     => 'Accepter / refuser une évaluation',
                    'evaluations.voir-propres' => 'Consulter ses propres évaluations',
                    'evaluations.voir-equipe'  => 'Consulter les évaluations de son équipe',
                    'evaluations.voir-reseau'  => 'Consulter toutes les évaluations du réseau (DG)',
                    'evaluations.exporter-pdf' => 'Télécharger une évaluation en PDF',
                ],
            ],
            'objectifs' => [
                'label' => 'Objectifs',
                'icon'  => 'fas fa-bullseye',
                'color' => 'emerald',
                'items' => [],
                'labels' => [
                    'objectifs.assigner'   => 'Assigner une fiche d\'objectifs',
                    'objectifs.accepter'   => 'Accepter / refuser une fiche d\'objectifs',
                    'objectifs.avancement' => 'Mettre à jour l\'avancement',
                    'objectifs.voir-equipe' => 'Consulter les fiches de son équipe',
                ],
            ],
            'agents' => [
                'label' => 'Personnel',
                'icon'  => 'fas fa-users',
                'color' => 'sky',
                'items' => [],
                'labels' => [
                    'agents.voir' => 'Consulter la liste du personnel',
                ],
            ],
            'structures' => [
                'label' => 'Structures',
                'icon'  => 'fas fa-sitemap',
                'color' => 'amber',
                'items' => [],
                'labels' => [
                    'structures.voir' => 'Consulter les structures',
                ],
            ],
            'statistiques' => [
                'label' => 'Rapports',
                'icon'  => 'fas fa-chart-bar',
                'color' => 'teal',
                'items' => [],
                'labels' => [
                    'statistiques.voir' => 'Consulter les statistiques du personnel (notes)',
                    'tableaux.voir'     => 'Consulter et exporter les tableaux Excel personnalisés',
                ],
            ],
            'admin' => [
                'label' => 'Administration',
                'icon'  => 'fas fa-cog',
                'color' => 'rose',
                'items' => [],
                'labels' => [
                    'admin.activites' => 'Consulter les logs d\'activité',
                    'admin.alertes'   => 'Créer et diffuser des alertes',
                ],
            ],
        ];

        // Mapping explicite : certains préfixes de permission sont regroupés
        // dans un groupe dont la clé est différente (ex: 'tableaux' → 'statistiques').
        $prefixToGroup = [
            'tableaux' => 'statistiques',
        ];

        foreach ($permissions as $perm) {
            $prefix = explode('.', $perm->name)[0];
            $group  = $prefixToGroup[$prefix] ?? $prefix;
            if (isset($permissionGroups[$group])) {
                $permissionGroups[$group]['items'][] = $perm;
            }
        }

        // ── Onglet Rôles : affiche les permissions d'un rôle sélectionné ──
        $allRoles        = UserController::allRoles(); // Tableau role_slug => label
        $selectedRoleSlug = $request->query('role');
        $selectedRole     = null;
        $rolePermissions  = collect(); // IDs des permissions déjà accordées au rôle

        if ($selectedRoleSlug && isset($allRoles[$selectedRoleSlug])) {
            // Chercher le rôle Spatie par son "name" (= le slug dans notre convention).
            // Si le rôle n'existe pas encore (seeder non joué), $selectedRole reste null.
            // Recherche le rôle Spatie par son nom (ex: 'dg', 'admin'…).
            // Retourne null si le seeder n'a pas encore été joué.
            $selectedRole = Role::with('permissions')
                ->where('name', $selectedRoleSlug)
                ->first();

            // Récupère les IDs pour pré-cocher les cases dans la vue.
            $rolePermissions = $selectedRole?->permissions->pluck('id') ?? collect();
        }

        // ── Onglet Droits individuels : permissions directes d'un user ────
        $selectedUserId  = $request->query('user_id');
        $selectedUser    = null;
        $userPermissions = collect(); // IDs des permissions directement accordées

        if ($selectedUserId) {
            // Spatie expose getDirectPermissions() pour les permissions directes seulement
            // (exclut celles héritées des rôles).
            $selectedUser    = User::find($selectedUserId);
            $userPermissions = $selectedUser?->getDirectPermissions()->pluck('id') ?? collect();
        }

        return view('admin.settings.edit', [
            'theme'             => $request->user()->theme_preference ?? 'reference',
            'maxLoginAttempts'  => (int) Setting::get('security.max_login_attempts', 3),
            'lockoutTime'       => (int) Setting::get('security.lockout_minutes', 30),
            'allRoles'          => $allRoles,
            'permissions'       => $permissions,
            'permissionGroups'  => $permissionGroups,
            'selectedRoleSlug'  => $selectedRoleSlug,
            'selectedRole'      => $selectedRole,
            'rolePermissions'   => $rolePermissions,
            'allUsers'          => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
            'selectedUser'      => $selectedUser,
            'userPermissions'   => $userPermissions,
            'featuresEnabled'   => [
                'evaluations' => Setting::featureEnabled('evaluations'),
                'objectifs'   => Setting::featureEnabled('objectifs'),
            ],
            'rhUsers'           => User::where('role', 'RH')->orderBy('name')->get(),
            'rhSpatiePerms'     => Role::with('permissions')->where('name', 'RH')->first()?->permissions ?? collect(),
        ]);
    }

    // ── Thème & Sécurité ──────────────────────────────────────────────────────

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_preference' => ['required', 'string', 'in:reference,classic'],
        ]);

        $request->user()->forceFill(['theme_preference' => $validated['theme_preference']])->save();

        return redirect()->route('admin.settings.edit')->with('status', 'Thème mis à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], (string) $request->user()->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])->withInput();
        }

        $request->user()->forceFill([
            'password'       => Hash::make($validated['password']),
            'password_plain' => null,
        ])->save();

        return redirect()->route('admin.settings.edit')->with('status', 'Mot de passe mis à jour.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'lockout_time'       => ['required', 'integer', 'in:15,30,60,1440'],
        ]);

        Setting::set('security.max_login_attempts', $validated['max_login_attempts']);
        Setting::set('security.lockout_minutes', $validated['lockout_time']);

        return redirect()->route('admin.settings.edit')->with('status', 'Politique de sécurité mise à jour.');
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate(['delete_password' => ['required', 'string']]);
        $user = $request->user();

        if (! Hash::check($validated['delete_password'], (string) $user->password)) {
            return back()->withErrors(['delete_password' => 'Le mot de passe de confirmation est incorrect.']);
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->withErrors(['delete_password' => 'Impossible de supprimer le dernier compte administrateur.']);
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Compte supprimé.');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        return response()->json(
            User::where(fn ($q) => $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('role', 'like', "%{$query}%"))
                ->select('id', 'name', 'email', 'role')
                ->take(10)
                ->get()
        );
    }

    public function updateUserPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'exists:users,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->forceFill([
            'password'       => Hash::make($validated['password']),
            'password_plain' => $validated['password'],
        ])->save();

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Mot de passe de '.$user->name.' mis à jour.');
    }

    public function updateUserRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', 'string', 'in:'.implode(',', array_keys(UserController::allRoles()))],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->forceFill(['role' => $validated['role']])->save();

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Rôle de '.$user->name.' mis à jour.');
    }

    // ── Permissions par rôle ──────────────────────────────────────────────────

    /**
     * Met à jour les permissions d'un rôle entier (ex: le rôle 'dg').
     * Le formulaire soumet les IDs des permissions cochées.
     * On les convertit en noms avant d'appeler syncPermissions() de Spatie.
     */
    public function syncRolePermissions(Request $request, string $roleSlug): RedirectResponse
    {
        $allRoles = UserController::allRoles();
        abort_unless(array_key_exists($roleSlug, $allRoles), 404, 'Rôle inconnu.');

        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Récupérer ou créer le rôle Spatie correspondant au slug.
        $role = Role::firstOrCreate([
            'name'       => $roleSlug,   // 'dg', 'admin', 'pca'…
            'guard_name' => 'web',
        ]);

        // Convertir les IDs reçus en noms de permissions (Spatie travaille avec les noms).
        $permissionNames = Permission::whereIn('id', $validated['permissions'] ?? [])
            ->pluck('name');

        // syncPermissions() remplace toutes les permissions du rôle.
        $role->syncPermissions($permissionNames);

        // Vider le cache pour que les changements soient pris en compte immédiatement.
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.settings.edit', ['tab' => 'roles', 'role' => $roleSlug])
            ->with('status', 'Permissions du rôle « '.$allRoles[$roleSlug].' » mises à jour.');
    }

    // ── Permissions individuelles utilisateur ─────────────────────────────────

    /**
     * Met à jour les permissions directes d'un utilisateur spécifique.
     * Ces permissions s'ajoutent à celles héritées de son rôle Spatie.
     * Le formulaire soumet les IDs des permissions cochées.
     */
    // ── Gestion des rôles personnalisés ──────────────────────────────────────

    /**
     * Crée un nouveau rôle personnalisé.
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug'  => [
                'required', 'string', 'max:100',
                'regex:/^[A-Za-z0-9_]+$/',
                'unique:custom_roles,slug',
                function ($_attribute, $value, $fail): void {
                    if (isset(UserController::ROLES[$value])) {
                        $fail('Ce slug est déjà utilisé par un rôle système.');
                    }
                },
            ],
            'label' => ['required', 'string', 'max:150'],
        ], [
            'slug.regex'  => 'Le slug ne doit contenir que des lettres, chiffres et underscores.',
            'slug.unique' => 'Ce slug est déjà utilisé par un rôle personnalisé.',
        ]);

        CustomRole::create($validated);

        return redirect()->route('admin.settings.edit', ['tab' => 'roles'])
            ->with('status', 'Rôle « '.$validated['label'].' » créé avec succès.');
    }

    /**
     * Supprime un rôle personnalisé (impossible pour les rôles système).
     */
    public function destroyRole(CustomRole $customRole): RedirectResponse
    {
        $label = $customRole->label;
        $customRole->delete();

        return redirect()->route('admin.settings.edit', ['tab' => 'roles'])
            ->with('status', 'Rôle « '.$label.' » supprimé.');
    }

    // ── Feature toggles ───────────────────────────────────────────────────────

    /**
     * Active ou désactive une fonctionnalité globale (evaluations / objectifs).
     */
    public function toggleFeature(string $feature): RedirectResponse
    {
        abort_unless(in_array($feature, ['evaluations', 'objectifs'], true), 404);

        $current = Setting::featureEnabled($feature);
        Setting::set($feature . '_enabled', $current ? '0' : '1');

        $label  = $feature === 'evaluations' ? 'Évaluations' : 'Assignation d\'objectifs';
        $state  = $current ? 'désactivée' : 'activée';

        return redirect()->route('admin.settings.edit', ['tab' => 'fonctionnalites'])
            ->with('status', "{$label} {$state} avec succès.");
    }

    public function syncUserPermissions(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Convertir les IDs en noms de permissions.
        $permissionNames = Permission::whereIn('id', $validated['permissions'] ?? [])
            ->pluck('name');

        // syncPermissions() sur un utilisateur remplace ses permissions directes
        // (pas celles héritées de ses rôles Spatie).
        $user->syncPermissions($permissionNames);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.settings.edit', ['tab' => 'droits', 'user_id' => $user->id])
            ->with('status', 'Permissions de '.$user->name.' mises à jour.');
    }

    // ── Compte RH ─────────────────────────────────────────────────────────────

    /**
     * Crée un compte utilisateur avec le rôle RH depuis la page des paramètres.
     * Le RH est un compte système (comme Admin) : pas de lien obligatoire avec un agent.
     */
    public function storeRhAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:191'],
            'email'    => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'  => 'Le nom complet est obligatoire.',
            'email.unique'   => 'Cette adresse email est déjà utilisée.',
            'password.min'   => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        User::create([
            'agent_id'             => null,
            'name'                 => trim($validated['name']),
            'email'                => $validated['email'],
            'password'             => Hash::make($validated['password']),
            'password_plain'       => $validated['password'],
            'role'                 => 'RH',
            'must_change_password' => true,
        ]);

        return redirect()->route('admin.settings.edit', ['tab' => 'comptes'])
            ->with('status', 'Compte RH « '.$validated['name'].' » créé avec succès.');
    }
}
