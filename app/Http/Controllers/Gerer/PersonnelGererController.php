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

        $agents = Agent::query()
            // Exclure uniquement les rôles système non-personnel (Admin, RH)
            ->where(fn ($q) => $q
                ->whereDoesntHave('user')
                ->orWhereHas('user', fn ($u) => $u->whereNotIn('role', Agent::NON_PERSONNEL_ROLES))
            )
            ->with([
                'user',
                'delegationTechnique',
                'caisse',
                'direction',
                'agence.caisse',
                'guichet.agence',
                'service',
            ])
            ->when($search, fn ($q) => $q->where(fn ($s) => $s
                ->where('nom',        'like', "%{$search}%")
                ->orWhere('prenom',   'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%")
            ))
            ->when($roleF, fn ($q) => $q->where('role', $roleF))
            ->when($delegId, fn ($q) => $q->where(fn ($s) => $s
                ->where('delegation_technique_id', $delegId)
                ->orWhereHas('caisse',  fn ($c) => $c->where('delegation_technique_id', $delegId))
                ->orWhereHas('agence',  fn ($a) => $a->where('delegation_technique_id', $delegId))
                ->orWhereHas('guichet', fn ($g) => $g->whereHas('agence', fn ($a) => $a->where('delegation_technique_id', $delegId)))
            ))
            ->when($caisseId, fn ($q) => $q->where(fn ($s) => $s
                ->where('caisse_id', $caisseId)
                ->orWhereHas('agence',  fn ($a) => $a->where('caisse_id', $caisseId))
                ->orWhereHas('guichet', fn ($g) => $g->whereHas('agence', fn ($a) => $a->where('caisse_id', $caisseId)))
            ))
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();
        $roles = Agent::query()
            ->where(fn ($q) => $q
                ->whereDoesntHave('user')
                ->orWhereHas('user', fn ($u) => $u->whereNotIn('role', Agent::NON_PERSONNEL_ROLES))
            )
            ->select('role')->distinct()->orderBy('role')->pluck('role');
        $layout      = $this->layout();

        return view('gerer.personnel.index', compact(
            'agents', 'delegations', 'caisses', 'roles', 'layout'
        ));
    }
}
