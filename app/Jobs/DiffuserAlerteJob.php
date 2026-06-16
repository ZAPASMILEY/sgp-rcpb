<?php

namespace App\Jobs;

use App\Mail\AlerteMail;
use App\Mail\AlerteVipMail;
use App\Models\Alerte;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Diffuse une alerte personnalisée en arrière-plan :
 *   – notifications in-app pour les rôles ciblés
 *   – emails (optionnel) aux mêmes destinataires
 */
class DiffuserAlerteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int   $alerteId      ID de l'alerte à diffuser
     * @param array $rolesCibles   Rôles destinataires. Vide ou ['tous'] = tous les utilisateurs actifs.
     * @param bool  $diffuserEmail Envoyer également par email
     */
    public function __construct(
        private readonly int $alerteId,
        private readonly array $rolesCibles,
        private readonly bool $diffuserEmail,
    ) {}

    public function handle(): void
    {
        $alerte = Alerte::find($this->alerteId);
        if (! $alerte) {
            return;
        }

        // ── Résolution des destinataires ──────────────────────────────────────
        $envoyerATous = empty($this->rolesCibles) || in_array('tous', $this->rolesCibles, true);

        $destinataires = User::query()
            ->where('is_active', true)
            ->when(! $envoyerATous, fn ($q) => $q->whereIn('role', $this->rolesCibles))
            ->get();

        // ── Notifications in-app ──────────────────────────────────────────────
        $alerte->destinataires()->syncWithoutDetaching(
            $destinataires->pluck('id')
                ->mapWithKeys(fn ($id) => [$id => ['lu' => false]])
                ->all()
        );

        if (! $this->diffuserEmail) {
            return;
        }

        // ── Emails ────────────────────────────────────────────────────────────
        $alerte->loadMissing('createur');

        // Dirigeants (PCA, DG, DGA) → template VIP
        $entite      = Entite::first();
        $vipEmails   = [];
        $vipRecipients = [];

        if ($entite) {
            foreach ([
                ['email' => $entite->pca_email,                  'nom' => trim($entite->pca_prenom . ' ' . $entite->pca_nom),                                           'role' => "Président(e) du Conseil d'Administration"],
                ['email' => $entite->directrice_generale_email,  'nom' => trim($entite->directrice_generale_prenom . ' ' . $entite->directrice_generale_nom),           'role' => 'Directeur(trice) Général(e)'],
                ['email' => $entite->dga_email,                  'nom' => trim($entite->dga_prenom . ' ' . $entite->dga_nom),                                           'role' => 'Directeur(trice) Général(e) Adjoint(e)'],
            ] as $vip) {
                if (! empty($vip['email'])) {
                    $vipEmails[] = strtolower($vip['email']);
                    $vipRecipients[] = $vip;
                }
            }
        }

        foreach ($vipRecipients as $vip) {
            // N'envoyer que si ce VIP est dans les destinataires ciblés
            $estCible = $destinataires->contains(fn ($u) => strtolower($u->email) === strtolower($vip['email']));
            if ($estCible) {
                try {
                    Mail::to($vip['email'])->send(new AlerteVipMail($alerte, $vip['nom'], $vip['role']));
                } catch (\Throwable $e) {
                    \Log::error("AlerteVipMail échec [{$vip['email']}] : " . $e->getMessage());
                }
            }
        }

        // Autres utilisateurs → template standard
        foreach ($destinataires as $user) {
            if (! in_array(strtolower($user->email ?? ''), $vipEmails, true)) {
                try {
                    Mail::to($user->email)->send(new AlerteMail($alerte, $user->name));
                } catch (\Throwable $e) {
                    \Log::error("AlerteMail échec [{$user->email}] : " . $e->getMessage());
                }
            }
        }
    }
}
