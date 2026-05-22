<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige l'utilisateur vers la page de changement de mot de passe
 * si son compte exige un changement obligatoire (must_change_password = true).
 *
 * Exceptions : la route de changement elle-même et toutes les déconnexions.
 */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            $request->user()->must_change_password &&
            ! $request->routeIs('password.change', 'password.change.update') &&
            ! $request->routeIs('*.logout', 'logout')
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
