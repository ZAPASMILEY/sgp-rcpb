<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $permissions    = Permission::orderBy('name')->get();
        $allRoles       = UserController::ROLES;

        // Onglet Rôles — chargement dynamique selon ?role=slug
        $selectedRoleSlug = $request->query('role');
        $selectedRole     = null;
        $rolePermissions  = collect();
        if ($selectedRoleSlug && isset($allRoles[$selectedRoleSlug])) {
            $selectedRole    = Role::with('permissions')->firstOrCreate(
                ['slug' => $selectedRoleSlug],
                ['name' => $allRoles[$selectedRoleSlug], 'description' => '']
            );
            $rolePermissions = $selectedRole->permissions->pluck('id');
        }

        // Onglet Droits individuels — chargement selon ?user_id=id
        $selectedUserId  = $request->query('user_id');
        $selectedUser    = null;
        $userPermissions = collect();
        if ($selectedUserId) {
            $selectedUser    = User::with('permissions')->find($selectedUserId);
            $userPermissions = $selectedUser?->permissions->pluck('id') ?? collect();
        }

        return view('admin.settings.edit', [
            'theme'            => $request->user()->theme_preference ?? 'reference',
            'maxLoginAttempts' => (int) $request->session()->get('admin_security.max_login_attempts', 3),
            'lockoutTime'      => (int) $request->session()->get('admin_security.lockout_time', 30),
            'allRoles'         => $allRoles,
            'permissions'      => $permissions,
            'selectedRoleSlug' => $selectedRoleSlug,
            'selectedRole'     => $selectedRole,
            'rolePermissions'  => $rolePermissions,
            'allUsers'         => User::query()->orderBy('name')->get(['id', 'name', 'email', 'role']),
            'selectedUser'     => $selectedUser,
            'userPermissions'  => $userPermissions,
        ]);
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_preference' => ['required', 'string', 'in:reference,classic'],
        ]);

        $request->user()->forceFill([
            'theme_preference' => $validated['theme_preference'],
        ])->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Theme mis a jour avec succes.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($validated['current_password'], (string) $request->user()->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.',
            ])->withInput();
        }

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Mot de passe mis a jour avec succes.');
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_login_attempts' => ['required', 'integer', 'min:1', 'max:10'],
            'lockout_time' => ['required', 'integer', 'in:15,30,60,1440'],
        ]);

        $request->session()->put('admin_security', [
            'max_login_attempts' => (int) $validated['max_login_attempts'],
            'lockout_time' => (int) $validated['lockout_time'],
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Politique de securite mise a jour avec succes.');
    }

    public function destroyAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delete_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['delete_password'], (string) $user->password)) {
            return back()->withErrors([
                'delete_password' => 'Le mot de passe de confirmation est incorrect.',
            ]);
        }

        if ($user->isAdmin() && User::query()->where('role', 'admin')->count() <= 1) {
            return back()->withErrors([
                'delete_password' => 'Impossible de supprimer le dernier compte administrateur.',
            ]);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Compte supprime avec succes.');
    }

    public function searchUsers(Request $request)
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('role', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'role')
            ->take(10)
            ->get();

        return response()->json($users);
    }

    public function updateUserPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'exists:users,id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Mot de passe de ' . $user->name . ' mis à jour avec succès.');
    }

    public function updateUserRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', 'string', 'in:PCA,DG,DGA,Assistante_Dg,Secretaire_assistante,Secretaire_Direction,Secretaire_Technique,Secretaire_Caisse,Secretaire_Agence,Conseillers_Dg,Directeur_Direction,Directeur_Caisse,Directeur_Tehnique,Chefs de service,chef d\'agence,Agent,admin'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->forceFill(['role' => $validated['role']])->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Rôle de ' . $user->name . ' mis à jour avec succès.');
    }

    // ── Permissions catalogue ─────────────────────────────────────────────────

    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:permissions,name'],
            'slug'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'name.regex'  => 'Le code technique ne doit contenir que des minuscules, chiffres et tirets.',
            'name.unique' => 'Ce code de permission existe déjà.',
        ]);

        Permission::create($validated);

        return redirect()
            ->route('admin.settings.edit', ['tab' => 'catalogue'])
            ->with('status', 'Permission « '.$validated['slug'].' » créée.');
    }

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('admin.settings.edit', ['tab' => 'catalogue'])
            ->with('status', 'Permission supprimée.');
    }

    // ── Permissions par rôle ──────────────────────────────────────────────────

    public function syncRolePermissions(Request $request, string $roleSlug): RedirectResponse
    {
        $allRoles = UserController::ROLES;
        abort_unless(isset($allRoles[$roleSlug]), 404, 'Rôle inconnu.');

        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => $allRoles[$roleSlug], 'description' => '']
        );

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.settings.edit', ['tab' => 'roles', 'role' => $roleSlug])
            ->with('status', 'Permissions du rôle « '.$allRoles[$roleSlug].' » mises à jour.');
    }

    // ── Permissions individuelles utilisateur ─────────────────────────────────

    public function syncUserPermissions(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $user->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.settings.edit', ['tab' => 'droits', 'user_id' => $user->id])
            ->with('status', 'Permissions de '.$user->name.' mises à jour.');
    }
}
