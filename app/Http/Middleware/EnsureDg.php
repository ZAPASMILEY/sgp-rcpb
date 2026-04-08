<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDg
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || strtolower($user->role) !== 'dg') {
            abort(403, 'Accès réservé au Directeur Général.');
        }
        return $next($request);
    }
}