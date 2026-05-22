<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Chef\ChefEntity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * EnsureIsChef — Middleware de protection de l'espace chef
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Vérifie que l'utilisateur connecté possède l'un des trois rôles chef
 * reconnus dans l'application RCPB, ET qu'une structure lui est associée.
 *
 * Si le rôle est correct mais qu'aucune structure n'est trouvée via
 * ChefEntity::resolve(), l'utilisateur est redirigé vers la page d'attente
 * (chef.pending) plutôt que de recevoir une erreur 403.
 *
 * Rôles acceptés :
 *  • Chef_Service  → chef d'un service (service.chef_agent_id)
 *  • Chef_Agence   → chef d'une agence (agence.chef_agent_id)
 *  • Chef_Guichet  → chef d'un guichet (guichet.chef_agent_id)
 * ──────────────────────────────────────────────────────────────────────────────
 */
class EnsureIsChef
{
    /**
     * Liste exhaustive des rôles autorisés à accéder à l'espace chef.
     */
    private const ROLES = [
        'Chef_Service',
        'Chef_Agence',
        'Chef_Guichet',
        'Secretaire_Agence',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Authentifié ET rôle chef reconnu ?
        if (! auth()->check() || ! in_array(auth()->user()->role, self::ROLES, true)) {
            return redirect()->route('login');
        }

        // Structure associée ? Sinon → page d'attente (pas de 403)
        if (! ChefEntity::resolve(auth()->user())) {
            return redirect()->route('chef.pending');
        }

        return $next($request);
    }
}
