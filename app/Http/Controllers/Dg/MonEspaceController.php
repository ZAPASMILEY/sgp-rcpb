<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonEspaceController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();


        // Afficher toutes les fiches créées par le PCA (toutes les fiches existantes)
        $fiches = FicheObjectif::all();

        // Évaluations assignées au DG (User)
        $evaluations = Evaluation::where('evaluable_type', 'App\\Models\\User')
            ->where('evaluable_id', $user->id)
            ->get();

        return view('dg.mon-espace', [
            'fiches' => $fiches,
            'evaluations' => $evaluations,
        ]);
    }
}
