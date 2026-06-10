<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Traits\GererLayout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnelGererController extends Controller
{
    use GererLayout;

    public function index(Request $request): View
    {
        $search  = $request->input('search');
        $roleF   = $request->input('role');
        $delegId = $request->integer('delegation_id') ?: null;
        $caisseId = $request->integer('caisse_id') ?: null;

        $agents = Agent::personnel()
            ->with(['user', 'delegationTechnique', 'caisse.delegationTechnique', 'direction'])
            // Uniquement les agents affectés à une structure
            ->where(fn ($q) => $q
                ->whereNotNull('caisse_id')
                ->orWhereNotNull('direction_id')
                ->orWhereNotNull('delegation_technique_id')
                ->orWhereNotNull('agence_id')
                ->orWhereNotNull('guichet_id')
                ->orWhereNotNull('service_id')
            )
            ->when($search, fn ($q) => $q->where(fn ($s) => $s
                ->where('nom',       'like', "%{$search}%")
                ->orWhere('prenom',  'like', "%{$search}%")
                ->orWhere('matricule','like', "%{$search}%")
            ))
            ->when($roleF, fn ($q) => $q->where('role', $roleF))
            ->when($delegId, fn ($q) => $q->where(fn ($s) => $s
                ->where('delegation_technique_id', $delegId)
                ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $delegId))
            ))
            ->when($caisseId, fn ($q) => $q->where('caisse_id', $caisseId))
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();
        $roles = Agent::personnel()
            ->where(fn ($q) => $q
                ->whereNotNull('caisse_id')
                ->orWhereNotNull('direction_id')
                ->orWhereNotNull('delegation_technique_id')
                ->orWhereNotNull('agence_id')
                ->orWhereNotNull('guichet_id')
                ->orWhereNotNull('service_id')
            )
            ->select('role')->distinct()->orderBy('role')->pluck('role');
        $layout      = $this->layout();

        return view('gerer.personnel.index', compact(
            'agents', 'delegations', 'caisses', 'roles', 'layout'
        ));
    }
}
