<?php

namespace App\Services;

use App\Models\Activite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Enregistre une action dans le journal d'activité.
     *
     * @param string      $action      Titre court (ex: "Connexion", "Évaluation créée")
     * @param string|null $description Détail optionnel
     * @param int|null    $userId      Par défaut : utilisateur connecté
     */
    public static function log(string $action, ?string $description = null, ?int $userId = null): void
    {
        try {
            Activite::create([
                'user_id'     => $userId ?? Auth::id(),
                'action'      => $action,
                'description' => $description,
                'ip_address'  => Request::ip(),
                'user_agent'  => substr((string) Request::userAgent(), 0, 500),
            ]);
        } catch (\Throwable) {
            // Ne jamais faire planter l'app à cause du logging
        }
    }
}
