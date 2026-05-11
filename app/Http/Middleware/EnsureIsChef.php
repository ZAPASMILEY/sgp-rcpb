<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * EnsureIsChef — Middleware de protection de l'espace chef
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Vérifie que l'utilisateur connecté possède l'un des trois rôles chef
 * reconnus dans l'application RCPB.
 *
 * Les trois types de chefs partagent le même espace (mêmes routes, mêmes
 * vues, mêmes contrôleurs). La distinction de l'entité gérée est réalisée
 * par ChefEntity::resolve() qui s'appuie sur le rôle et l'agent_id du User.
 *
 * Rôles acceptés :
 *  • Chef_Service  → chef d'un service (service.chef_agent_id)
 *  • Chef_Agence   → chef d'une agence (agence.chef_agent_id)
 *  • Chef_Guichet  → chef d'un guichet (guichet.chef_agent_id)
 *
 * Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé
 * vers la page de connexion.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class EnsureIsChef
{
    /**
     * Liste exhaustive des rôles autorisés à accéder à l'espace chef.
     * Modifiez ici si de nouveaux types de chefs sont ajoutés.
     */
    private const ROLES = [
        'Chef_Service',
        'Chef_Agence',
        'Chef_Guichet',
    ];

    /**
     * Traite la requête entrante.
     *
     * Si l'utilisateur est authentifié et son rôle figure dans la liste,
     * la requête est transmise normalement au contrôleur suivant.
     * Sinon, redirection vers la page de connexion.
     *
     * @param Request  $request  La requête HTTP entrante
     * @param Closure  $next     Le prochain handler dans la pile de middleware
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Double vérification : authentifié ET rôle autorisé
        if (! auth()->check() || ! in_array(auth()->user()->role, self::ROLES, true)) {
            // On utilise la route nommée 'login' pour la portabilité
            return redirect()->route('login');
        }

        return $next($request);
    }
}
