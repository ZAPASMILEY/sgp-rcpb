<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DgDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'dg') {
            abort(403, 'Accès réservé au Directeur Général.');
        }
        $entiteId = $user->pca_entite_id;

        // Fiches et évaluations assignées au DG (lui-même)
        $fiches = FicheObjectif::where('assignable_type', get_class($user))
            ->where('assignable_id', $user->id)
            ->get();
        $evaluations = Evaluation::where('evaluable_type', get_class($user))
            ->where('evaluable_id', $user->id)
            ->get();


        // Fiches et évaluations assignées aux subordonnés (DGA, assistante DG)
        $dgaId = $user->entite->dga_user_id ?? null;
        $assistanteId = null;
        if (!empty($user->entite->assistante_dg_email)) {
            $assistante = \App\Models\User::where('email', $user->entite->assistante_dg_email)->first();
            $assistanteId = $assistante?->id;
        }
        $subordonnesIds = collect([$dgaId, $assistanteId])->filter();
        $fichesSubordonnes = FicheObjectif::whereIn('assignable_id', $subordonnesIds)
            ->get();
        $evaluationsSubordonnes = Evaluation::whereIn('evaluable_id', $subordonnesIds)
            ->get();

        return view('dg.dashboard', [
            'fiches' => $fiches,
            'evaluations' => $evaluations,
            'fichesSubordonnes' => $fichesSubordonnes,
            'evaluationsSubordonnes' => $evaluationsSubordonnes,
        ]);
    }
}
