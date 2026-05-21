<?php

namespace App\Http\Middleware;

use App\Models\Annee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque la création / modification d'évaluations et d'objectifs
 * quand aucune année ou aucun semestre n'est ouvert.
 *
 * Usage dans les routes :
 *   ->middleware('periode.ouverte')
 *
 * Règles :
 *   - S'il n'existe aucune année au statut « ouvert »  → bloqué
 *   - Si l'année est ouverte mais qu'aucun semestre n'est ouvert → bloqué
 */
class PeriodeOuverte
{
    public function handle(Request $request, Closure $next): Response
    {
        $annee = Annee::currentOpen();

        if (! $annee) {
            $message = "Aucune année d'exercice ouverte. L'administrateur doit ouvrir une année avant de pouvoir créer des évaluations.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->back()->with('periode_fermee', $message);
        }

        $hasSemestre = $annee->semestres()->where('statut', 'ouvert')->exists();

        if (! $hasSemestre) {
            $message = "Aucun semestre ouvert pour l'année {$annee->annee}. L'administrateur doit ouvrir un semestre avant de créer des évaluations.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->back()->with('periode_fermee', $message);
        }

        return $next($request);
    }
}
