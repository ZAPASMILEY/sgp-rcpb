<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function updateUserRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', 'string', 'in:admin,pca,agent,directeur,directeur_adjoint,assistant,chef,secretaire,rh'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->role = $validated['role'];
        $user->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Rôle de ' . $user->name . ' mis à jour avec succès.');
    }
    public function edit(Request $request): View
    {
        return view('admin.settings.edit', [
            'theme' => $request->user()->theme_preference ?? 'reference',
            'maxLoginAttempts' => (int) $request->session()->get('admin_security.max_login_attempts', 3),
            'lockoutTime' => (int) $request->session()->get('admin_security.lockout_time', 30),
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
}
