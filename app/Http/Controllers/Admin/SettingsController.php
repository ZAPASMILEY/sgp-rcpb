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
}
