<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsDirecteur
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || auth()->user()->role !== 'Directeur_Direction') {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
