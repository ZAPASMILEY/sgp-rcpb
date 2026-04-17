<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSubordonne
{
    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, self::ALLOWED_ROLES, true)) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
