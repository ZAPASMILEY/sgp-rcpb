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
        // Le DG voit tout ce qui vient du PCA : toutes les fiches et toutes les évaluations
        $fiches = FicheObjectif::all();
        $evaluations = Evaluation::all();

        return view('dg.mon-espace', [
            'fiches' => $fiches,
            'evaluations' => $evaluations,
        ]);
    }
}
