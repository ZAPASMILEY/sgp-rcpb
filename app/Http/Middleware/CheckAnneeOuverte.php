<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAnneeOuverte
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
   public function handle(Request $request, Closure $next)
{
    // On cherche l'année active (celle liée à la session ou la plus récente)
    // Ici, j'utilise la logique de l'année marquée "active" ou la dernière créée
    $annee = \App\Models\Annee::where('statut', 'ouvert')->latest('annee')->first();

    if (!$annee) {
        if ($request->ajax()) return response()->json(['error' => 'Aucune année d\'exercice n\'est ouverte.'], 403);
        return redirect()->back()->with('error', "Action impossible : l'année d'exercice est clôturée.");
    }

    return $next($request);
}
}
