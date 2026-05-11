<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginFailure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            if ($request->user()->isPca()) {
                return redirect()->route('pca.dashboard');
            }
            if (method_exists($request->user(), 'isDg') && $request->user()->isDg()) {
                return redirect()->route('dg.dashboard');
            }
            if ($request->user()->role === 'DGA') {
                return redirect()->route('dga.dashboard');
            }
            if (in_array($request->user()->role, ['Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'], true)) {
                return redirect()->route('subordonne.mon-espace');
            }
            if (in_array($request->user()->role, ['Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse'], true)) {
                return redirect()->route('directeur.dashboard');
            }
            if (in_array($request->user()->role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)) {
                return redirect()->route('chef.dashboard');
            }
            if ($request->user()->role === 'RH') {
                return redirect()->route('rh.dashboard');
            }
            if ($request->user()->isPersonnel()) {
                return redirect()->route('personnel.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            LoginFailure::query()->create([
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 65535),
                'attempted_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Compte désactivé par l'admin
        if (! $request->user()?->is_active) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->user()?->isPca()) {
            return redirect()->intended(route('pca.dashboard'));
        }
        if (method_exists($request->user(), 'isDg') && $request->user()->isDg()) {
            return redirect()->intended(route('dg.dashboard'));
        }
        if ($request->user()?->role === 'DGA') {
            return redirect()->intended(route('dga.dashboard'));
        }
        if (in_array($request->user()?->role, ['Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'], true)) {
            return redirect()->intended(route('subordonne.mon-espace'));
        }
        if (in_array($request->user()?->role, ['Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse'], true)) {
            return redirect()->intended(route('directeur.dashboard'));
        }
        // Chefs : Chef_Service, Chef_Agence, Chef_Guichet ont leur propre espace
        if (in_array($request->user()?->role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)) {
            return redirect()->intended(route('chef.dashboard'));
        }
        if ($request->user()?->role === 'RH') {
            return redirect()->intended(route('rh.dashboard'));
        }
        if ($request->user()?->isPersonnel()) {
            return redirect()->intended(route('personnel.dashboard'));
        }
        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
