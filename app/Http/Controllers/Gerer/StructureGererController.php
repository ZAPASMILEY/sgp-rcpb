<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Guichet;
use App\Traits\GererLayout;
use Illuminate\View\View;

class StructureGererController extends Controller
{
    use GererLayout;

    public function index(): View
    {
        $entite      = Entite::latest()->first();
        $delegations = DelegationTechnique::withCount('caisses')->orderBy('region')->orderBy('ville')->get();
        $directions  = Direction::with('directeur')->orderBy('nom')->get();
        $caisses     = Caisse::with(['delegationTechnique', 'directeur'])->orderBy('nom')->get();
        $agences     = Agence::with('caisse')->orderBy('nom')->get();
        $guichets    = Guichet::with('agence')->orderBy('nom')->get();
        $layout      = $this->layout();

        return view('gerer.structures.index', compact(
            'entite', 'delegations', 'directions', 'caisses', 'agences', 'guichets', 'layout'
        ));
    }
}
