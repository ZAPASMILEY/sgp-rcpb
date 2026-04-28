<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Models\Entite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Artisan::command('users:create-from-entite', function () {
    $entite = Entite::first(); // Prend la première entité (faîtière)
    if (!$entite) {
        $this->error('Aucune entité trouvée.');
        return;
    }

    $users = [
        [
            'prenom' => $entite->pca_prenom,
            'nom'    => $entite->pca_nom,
            'email'  => $entite->pca_email,
            'role'   => 'pca',
            'entite_id' => $entite->id,
        ],
        [
            'prenom' => $entite->directrice_generale_prenom,
            'nom'    => $entite->directrice_generale_nom,
            'email'  => $entite->directrice_generale_email,
            'role'   => 'directeur',
        ],
        [
            'prenom' => $entite->dga_prenom,
            'nom'    => $entite->dga_nom,
            'email'  => $entite->dga_email,
            'role'   => 'directeur_adjoint',
        ],
    ];

    foreach ($users as $u) {
        if (!$u['email']) continue;
        $exists = User::where('email', $u['email'])->first();
        if ($exists) {
            // entite_id est maintenant sur agents — mise à jour via l'agent lié
            if ($u['role'] === 'pca' && $exists->agent_id && empty($exists->agent?->entite_id)) {
                $exists->agent->entite_id = $entite->id;
                $exists->agent->save();
                $this->info("entite_id mis à jour sur agent pour le PCA existant: {$u['email']}");
            } else {
                $this->info("Utilisateur déjà existant: {$u['email']}");
            }
            continue;
        }
        $data = [
            'name'     => trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')),
            'email'    => $u['email'],
            'role'     => $u['role'],
            'password' => Hash::make('changeme123'),
        ];
        $user = User::create($data);
        // entite_id du PCA → sur l'agent, pas sur le user
        if (isset($u['entite_id']) && $user->agent_id) {
            $user->agent->entite_id = $u['entite_id'];
            $user->agent->save();
        }
        $this->info("Utilisateur créé: {$user->name} ({$user->role})");
    }
});

