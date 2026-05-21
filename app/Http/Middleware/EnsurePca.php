<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePca
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isPca()) {
            if ($request->user()?->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('login');
        }

        // Le PCA est lié à l'entité via entites.pca_agent_id (pas agents.entite_id)
        $agent = $request->user()->agent;
        if (! $agent || ! $agent->pcaedEntite()->exists()) {
            return redirect()->route('pca.pending');
        }

        return $next($request);
    }
}
