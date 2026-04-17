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
        $searchObjectif = $request->query('search_objectif', '');
        $searchEvaluation = $request->query('search_evaluation', '');

        $fiches = FicheObjectif::query()
            ->when($searchObjectif, function ($query, $searchObjectif) {
                $query->where('titre', 'like', "%{$searchObjectif}%")
                      ->orWhere('annee', 'like', "%{$searchObjectif}%");
            })
            ->get();

        $evaluations = Evaluation::query()
            ->where('statut', '!=', 'brouillon') // masquées jusqu'à soumission par le PCA
            ->when($searchEvaluation, function ($query, $searchEvaluation) {
                $query->where('date_debut', 'like', "%{$searchEvaluation}%")
                      ->orWhere('date_fin', 'like', "%{$searchEvaluation}%");
            })
            ->get();

        return view('dg.mon-espace', [
            'fiches' => $fiches,
            'evaluations' => $evaluations,
            'searchObjectif' => $searchObjectif,
            'searchEvaluation' => $searchEvaluation,
        ]);
    }
}
