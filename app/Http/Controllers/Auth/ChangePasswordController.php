<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.change-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password.required'   => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'  => 'La confirmation ne correspond pas.',
            'password.min'        => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = $request->user();

        $user->update([
            'password'             => Hash::make($request->password),
            'password_plain'       => null,
            'must_change_password' => false,
        ]);

        return redirect()->to($this->dashboardUrl($user));
    }

    private function dashboardUrl($user): string
    {
        return match (true) {
            $user->isPca()
                => route('pca.dashboard'),
            method_exists($user, 'isDg') && $user->isDg()
                => route('dg.dashboard'),
            $user->role === 'DGA'
                => route('dga.dashboard'),
            in_array($user->role, ['Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'], true)
                => route('subordonne.mon-espace'),
            in_array($user->role, ['Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse'], true)
                => route('directeur.dashboard'),
            in_array($user->role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)
                => route('chef.dashboard'),
            $user->role === 'RH'
                => route('rh.dashboard'),
            $user->isPersonnel()
                => route('personnel.dashboard'),
            default
                => route('admin.dashboard'),
        };
    }
}
