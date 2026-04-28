<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * EnsureIsDirecteur — Middleware de protection de l'espace directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Vérifie que l'utilisateur connecté possède l'un des trois rôles directeur
 * reconnus dans l'application.
 *
 * Les trois types de directeurs partagent le même espace (mêmes routes, mêmes
 * vues, mêmes controllers). La distinction de l'entité gérée est réalisée en
 * amont par DirecteurEntity::resolve() qui s'appuie sur le rôle et le user_id.
 *
 * Rôles acceptés :
 *  • Directeur_Direction  → directeur d'une direction de la faîtière
 *  • Directeur_Caisse     → directeur d'une caisse
 *  • Directeur_Technique  → directeur d'une délégation technique
 *
 * Si l'utilisateur n'est pas connecté ou n'a pas le bon rôle, il est redirigé
 * vers la page de connexion.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class EnsureIsDirecteur
{
    /** Liste des rôles autorisés à accéder à l'espace directeur. */
    private const ROLES = [
        'Directeur_Direction',
        'Directeur_Caisse',
        'Directeur_Technique',
    ];

    /**
     * Traite la requête entrante.
     *
     * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
     * ou si son rôle ne figure pas dans la liste ROLES.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, self::ROLES, true)) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
