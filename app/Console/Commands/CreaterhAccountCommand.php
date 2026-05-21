<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateRhAccountCommand extends Command
{
    /**
     * Le nom et la signature de la commande de la console.
     * @var string
     */
    protected $signature = 'make:rh {email} {name=Responsable_RH}';

    /**
     * La description de la commande.
     * @var string
     */
    protected $description = 'Crée un compte utilisateur avec le rôle RH et le mot de passe par défaut 11111111';

    /**
     * Exécuter la commande de la console.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->error("Erreur : Un utilisateur avec l'adresse email [{$email}] existe déjà !");
            return Command::FAILURE;
        }

        // Création du compte RH
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make('11111111'),
            'role'     => 'drh', // Adapte ici ('rh' ou 'drh') selon la valeur exacte attendue par ton middleware EnsureDg / rôles
        ]);

        $this->info("Succès ! Le compte RH pour {$name} ({$email}) a été créé avec succès.");
        $this->info("Mot de passe par défaut : 11111111");

        return Command::SUCCESS;
    }
}