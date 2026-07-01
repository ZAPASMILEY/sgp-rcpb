<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Ouvre la page des paramètres.
     */
public function settings(Request $request)
    {
        $user = $request->user();

        // 1. Déterminer le bon layout d'affichage en analysant l'URL ou la session
        $firstSegment = $request->segment(1); 
        
        // Si on est sur l'URL brute sans préfixe, on regarde d'où vient l'utilisateur (via le referer)
        if ($firstSegment === 'mon-profil') {
            $referer = $request->headers->get('referer');
            if ($referer) {
                $path = parse_url($referer, PHP_URL_PATH);
                $segments = explode('/', trim($path, '/'));
                $firstSegment = $segments[0] ?? null;
            }
        }

        // 2. Attribution du layout correspondant au dossier existant
        if ($firstSegment && file_exists(resource_path("views/layouts/{$firstSegment}.blade.php"))) {
            $layout = 'layouts.' . $firstSegment;
        } else {
            // Repli automatique sécurisé par rapport aux rôles de l'utilisateur
            if ($user->hasRole('DG')) {
                $layout = 'layouts.dg';
                $firstSegment = 'dg';
            } elseif ($user->hasRole('RH')) {
                $layout = 'layouts.rh';
                $firstSegment = 'rh';
            } else {
                $layout = 'layouts.personnel';
                $firstSegment = 'personnel';
            }
        }

        return view('shared.profile.settings', compact('user', 'layout', 'firstSegment'));
    }

    /**
     * Traitement du changement de mot de passe sécurisé.
     */
 public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()],
        ], [
            'current_password.current_password' => 'Votre mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation ne correspond pas.',
            'password.min' => 'Le mot de passe doit faire au moins 8 caractères.',
            // Ces deux lignes corrigent les messages bruts comme validation.password.letters :
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
        ]);

        $user = $request->user();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Votre mot de passe a été modifié avec succès !');
    }
}