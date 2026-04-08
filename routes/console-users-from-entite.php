<?php
// Script à placer dans routes/console.php ou à exécuter via tinker
use Illuminate\Support\Facades\Artisan;
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
            $this->info("Utilisateur déjà existant: {$u['email']}");
            continue;
        }
        $user = User::create([
            'name' => trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? '')),
            'email' => $u['email'],
            'role' => $u['role'],
            'password' => Hash::make('changeme123'), // Mot de passe temporaire
        ]);
        $this->info("Utilisateur créé: {$user->name} ({$user->role})");
    }
});
