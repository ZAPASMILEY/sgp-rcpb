<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->user()?->isPca()) {
                return redirect()->route('pca.dashboard');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
