<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Détermine le layout Blade à utiliser selon le rôle de l'utilisateur connecté.
 * Utilisé par tous les contrôleurs de l'espace /gerer.
 */
trait GererLayout
{
    protected function layout(): string
    {
        $role = Auth::user()?->role ?? '';

        return match (true) {
            $role === 'Admin'                                                                           => 'layouts.app',
            $role === 'DG'                                                                             => 'layouts.dg',
            $role === 'DGA'                                                                            => 'layouts.dga',
            $role === 'PCA'                                                                            => 'layouts.pca',
            $role === 'RH'                                                                             => 'layouts.rh',
            str_starts_with($role, 'Directeur_')                                                      => 'layouts.directeur',
            str_starts_with($role, 'Chef_')                                                           => 'layouts.chef',
            in_array($role, ['Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante',
                             'Secretaire_Direction', 'Secretaire_Technique',
                             'Secretaire_Caisse', 'Secretaire_Agence'], true)                         => 'layouts.personnel',
            $role === 'Agent'                                                                          => 'layouts.personnel',
            default                                                                                    => 'layouts.personnel',
        };
    }
}
