<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Directeur\DirecteurEntity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * EnsureIsDirecteur — Middleware de protection de l'espace directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Vérifie que l'utilisateur connecté possède l'un des trois rôles directeur
 * reconnus dans l'application, ET qu'une structure lui est associée.
 *
 * Si le rôle est correct mais qu'aucune structure n'est trouvée via
 * DirecteurEntity::resolve(), l'utilisateur est redirigé vers la page d'attente
 * (directeur.pending) plutôt que de recevoir une erreur 403.
 *
 * Rôles acceptés :
 *  • Directeur_Direction  → directeur d'une direction de la faîtière
 *  • Directeur_Caisse     → directeur d'une caisse
 *  • Directeur_Technique  → directeur d'une délégation technique
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

    public function handle(Request $request, Closure $next): Response
    {
        // Authentifié ET rôle directeur reconnu ?
        if (! auth()->check() || ! in_array(auth()->user()->role, self::ROLES, true)) {
            return redirect()->route('login');
        }

        // Structure associée ? Sinon → page d'attente (pas de 403)
        if (! DirecteurEntity::resolve(auth()->user())) {
            return redirect()->route('directeur.pending');
        }

        return $next($request);
    }
}
