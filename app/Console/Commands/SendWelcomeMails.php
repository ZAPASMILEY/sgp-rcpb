<?php

namespace App\Console\Commands;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendWelcomeMails extends Command
{
    protected $signature = 'mail:welcome
                            {--dry-run : Simuler sans envoyer ni modifier}
                            {--user= : Envoyer uniquement à un user ID précis}
                            {--limit= : Limiter le nombre d\'envois}';

    protected $description = 'Envoie un mail de bienvenue à tous les utilisateurs avec réinitialisation du mot de passe';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user');
        $limit  = $this->option('limit');

        $query = User::whereNotNull('email')
            ->where('is_active', true)
            ->with('agent');

        if ($userId) {
            $query->where('id', $userId);
        }

        if ($limit) {
            $query->limit((int) $limit);
        }

        $users = $query->get();

        $this->info("📧 {$users->count()} utilisateur(s) à traiter" . ($dryRun ? ' [DRY-RUN]' : ''));

        if (! $dryRun && ! $this->confirm('Confirmer l\'envoi et la réinitialisation des mots de passe ?')) {
            $this->warn('Annulé.');
            return self::FAILURE;
        }

        $sent    = 0;
        $failed  = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Nom complet depuis l'agent lié ou le name du compte
            $name = $user->agent
                ? trim(($user->agent->prenom ?? '') . ' ' . ($user->agent->nom ?? ''))
                : $user->name;

            if (empty($name)) {
                $name = $user->email;
            }

            if ($dryRun) {
                $this->newLine();
                $this->line("  [DRY-RUN] → {$user->email} ({$name})");
                $bar->advance();
                $skipped++;
                continue;
            }

            // Générer un nouveau mot de passe
            $plainPassword = Str::random(10);

            try {
                // Mettre à jour en base
                $user->update([
                    'password'             => Hash::make($plainPassword),
                    'must_change_password' => true,
                ]);

                // Envoyer le mail
                Mail::to($user->email)->send(new WelcomeMail(
                    recipientName:  $name,
                    recipientEmail: $user->email,
                    plainPassword:  $plainPassword,
                    role:           $user->role ?? 'Agent',
                    loginUrl:       url('/login'),
                ));

                $sent++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("  ✗ {$user->email} → " . $e->getMessage());
                $failed++;
            }

            $bar->advance();

            // Petite pause pour ne pas surcharger Gmail (limite : ~500/jour)
            usleep(200_000); // 200ms entre chaque envoi
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Envoyés', 'Échoués', 'Ignorés'],
            [[$sent, $failed, $skipped]]
        );

        if ($dryRun) {
            $this->info('DRY-RUN terminé — aucun mail envoyé, aucun mot de passe modifié.');
        } else {
            $this->info("✅ Terminé. {$sent} mail(s) envoyé(s). Les utilisateurs devront changer leur mot de passe à la première connexion.");
        }

        return self::SUCCESS;
    }
}
