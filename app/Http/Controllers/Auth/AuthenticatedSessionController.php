<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginFailure;
use App\Models\Setting;
use App\Models\User;
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
            if (in_array($request->user()->role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet', 'Secretaire_Agence'], true)) {
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

        // ── 1. Vérifier si ce compte est actuellement suspendu ───────────────
        $candidate = User::where('email', $credentials['email'])->first();
        if ($candidate && $candidate->isBlocked()) {
            $restant = now()->diffInMinutes($candidate->blocked_until, false);
            throw ValidationException::withMessages([
                'email' => "Compte temporairement suspendu après plusieurs tentatives échouées. Réessayez dans {$restant} minute(s).",
            ]);
        }

        if (! Auth::attempt($credentials, $remember)) {
            // ── 2. Enregistrer l'échec ───────────────────────────────────────
            LoginFailure::query()->create([
                'email'        => $credentials['email'],
                'ip_address'   => $request->ip(),
                'user_agent'   => substr((string) $request->userAgent(), 0, 65535),
                'attempted_at' => now(),
            ]);

            // ── 3. Compter les échecs récents et bloquer si dépassé ──────────
            $maxAttempts  = (int) Setting::get('security.max_login_attempts', 3);
            $lockMinutes  = (int) Setting::get('security.lockout_minutes', 30);

            $recentFails = LoginFailure::where('email', $credentials['email'])
                ->where('attempted_at', '>=', now()->subMinutes($lockMinutes))
                ->count();

            if ($recentFails >= $maxAttempts) {
                if ($candidate) {
                    $candidate->update(['blocked_until' => now()->addMinutes($lockMinutes)]);
                }
                throw ValidationException::withMessages([
                    'email' => "Compte suspendu pendant {$lockMinutes} minutes après {$maxAttempts} tentatives échouées.",
                ]);
            }

            $remaining = $maxAttempts - $recentFails;
            throw ValidationException::withMessages([
                'email' => __('auth.failed') . " ({$remaining} tentative(s) restante(s) avant suspension)",
            ]);
        }

        // Compte désactivé par l'admin
        if (! $request->user()?->is_active) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages([
                'email' => 'Votre compte a été désactivé. Contactez l\'administrateur.',
            ]);
        }

        // ── 4. Connexion réussie : lever le blocage éventuel ─────────────────
        $request->user()?->update(['blocked_until' => null]);

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
