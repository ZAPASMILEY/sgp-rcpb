<?php

namespace App\Http\Middleware;

use App\Models\Annee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloque l'assignation d'objectifs quand aucune année n'est ouverte.
 * (Contrairement à PeriodeOuverte, ne vérifie pas l'état des semestres.)
 *
 * Usage dans les routes :
 *   ->middleware('annee.ouverte')
 */
class AnneeOuverte
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Annee::currentOpen()) {
            $message = "Aucune année d'exercice ouverte. L'administrateur doit ouvrir une année avant de pouvoir assigner des objectifs.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->back()->with('periode_fermee', $message);
        }

        return $next($request);
    }
}
