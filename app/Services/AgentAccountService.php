<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Hash;

/**
 * Gère la création / activation / désactivation automatique des comptes
 * utilisateur liés aux agents responsables de structures.
 *
 * Règle métier :
 *   – Un agent AFFECTÉ comme responsable d'une structure → compte actif.
 *   – Un agent NON AFFECTÉ → pas de compte (ou compte désactivé).
 */
class AgentAccountService
{
    /**
     * Mapping fonction agent → rôle système.
     */
    private const FONCTION_ROLE = [
        // Faîtière – Siège
        'PCA'                                       => 'PCA',
        'Directeur Général'                         => 'DG',
        'DGA'                                       => 'DGA',
        'Assistante DG'                             => 'Assistante_Dg',
        'Conseiller DG'                             => 'Conseillers_Dg',
        'Secrétaire Assistante'                     => 'Secretaire_Assistante',
        'Secrétaire de Direction'                   => 'Secretaire_Assistante',
        'Directeur de Direction'                    => 'Directeur_Technique',
        'Directeur Technique'                       => 'Directeur_Technique',
        'Directeur RH'                              => 'Directeur_Technique',
        'Directeur Finances'                        => 'Directeur_Technique',
        'Directeur SI'                              => 'Directeur_Technique',
        'Directeur des Engagements'                 => 'Directeur_Technique',
        'Directeur Marketing'                       => 'Directeur_Technique',
        'Directeur Audit Interne'                   => 'Directeur_Technique',
        'Chef de Service'                           => 'Chef_Service',
        // Terrain – Délégations
        'Secrétaire Technique'                      => 'Secretaire_Technique',
        // Terrain – Caisses
        'Directeur de Caisse'                       => 'Directeur_Caisse',
        'Secrétaire de Caisse'                      => 'Secretaire_Caisse',
        // Terrain – Agences
        "Chef d'Agence"                             => 'Chef_Agence',
        "Secrétaire d'Agence"                       => 'Secretaire_Agence',
        // Terrain – Guichets
        'Chef de Guichet'                           => 'Chef_Guichet',
        // Base
        'Agent'                                     => 'Agent',
    ];

    /**
     * S'assure qu'un compte actif existe pour l'agent.
     * Crée le compte s'il n'existe pas, le réactive s'il était désactivé.
     */
    public function ensureAccount(Agent $agent): User
    {
        $role = self::FONCTION_ROLE[$agent->role] ?? 'Agent';

        // Chercher en contournant tout scope éventuel
        $user = User::withoutGlobalScopes()->where('agent_id', $agent->id)->first();

        if ($user) {
            // Réactiver si désactivé, mettre à jour le rôle si nécessaire
            $user->update([
                'is_active' => true,
                'role'      => $role,
            ]);
            return $user;
        }

        try {
            return User::create([
                'agent_id'             => $agent->id,
                'name'                 => trim($agent->prenom . ' ' . $agent->nom),
                'email'                => $agent->email,
                'password'             => Hash::make('11111111'),
                'password_plain'       => '11111111',
                'role'                 => $role,
                'is_active'            => true,
                'must_change_password' => true,
            ]);
        } catch (UniqueConstraintViolationException) {
            // Compte créé simultanément (race condition) — on le récupère et on le réactive
            $user = User::withoutGlobalScopes()->where('agent_id', $agent->id)->firstOrFail();
            $user->update(['is_active' => true, 'role' => $role]);
            return $user;
        }
    }

    /**
     * Désactive le compte d'un agent qui n'est plus responsable d'une structure.
     * Ne supprime pas le compte (historique conservé).
     */
    public function deactivateAccount(Agent $agent): void
    {
        User::where('agent_id', $agent->id)->update(['is_active' => false]);
    }

    /**
     * Retourne le rôle système correspondant à une fonction agent.
     */
    public static function roleForFonction(string $fonction): string
    {
        return self::FONCTION_ROLE[$fonction] ?? 'Agent';
    }
}
