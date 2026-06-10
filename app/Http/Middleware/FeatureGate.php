<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'accès aux routes de création/assignation quand une
 * fonctionnalité a été désactivée par l'administrateur.
 *
 * Usage dans les routes :
 *   ->middleware('feature:evaluations')
 *   ->middleware('feature:objectifs')
 */
class FeatureGate
{
    private const FALLBACKS = [
        'evaluations' => 'La création d\'évaluations est actuellement désactivée par l\'administrateur.',
        'objectifs'   => 'L\'assignation d\'objectifs est actuellement désactivée par l\'administrateur.',
    ];

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! Setting::featureEnabled($feature)) {
            $custom   = Setting::featureMessage($feature);
            $message  = $custom ?: (self::FALLBACKS[$feature] ?? "La fonctionnalité « {$feature} » est désactivée.");

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->back()->with('feature_disabled', $message);
        }

        return $next($request);
    }
}
