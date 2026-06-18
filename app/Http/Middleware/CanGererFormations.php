<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorise l'accès à la gestion des formations si l'utilisateur possède
 * la permission 'formations.assigner' OU 'formations.valider'.
 */
class CanGererFormations
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->can('formations.assigner') && ! $user->can('formations.valider'))) {
            abort(403);
        }

        return $next($request);
    }
}
