<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Guichet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgReseauController extends Controller
{
    private function checkDg(): void
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403);
        }
    }

    public function delegations(Request $request): View
    {
        $this->checkDg();

        $search = trim((string) $request->get('search', ''));

        $query = DelegationTechnique::withCount(['caisses', 'agences', 'directions'])
            ->orderBy('region');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('region', 'like', "%{$search}%")
                ->orWhere('ville', 'like', "%{$search}%")
                ->orWhere('directeur_nom', 'like', "%{$search}%")
                ->orWhere('directeur_prenom', 'like', "%{$search}%")
            );
        }

        $delegations = $query->paginate(15)->withQueryString();

        return view('dg.reseau.delegations', compact('delegations', 'search'));
    }

    public function delegation(DelegationTechnique $delegation): View
    {
        $this->checkDg();

        $delegation->load(['caisses.agences', 'directions.services', 'agents']);

        return view('dg.reseau.delegation-show', compact('delegation'));
    }

    public function caisses(Request $request): View
    {
        $this->checkDg();

        $search     = trim((string) $request->get('search', ''));
        $delegId    = (int) $request->get('delegation', 0);

        $query = Caisse::with('delegationTechnique')
            ->withCount('agences')
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('directeur_nom', 'like', "%{$search}%")
                ->orWhere('directeur_prenom', 'like', "%{$search}%")
                ->orWhere('quartier', 'like', "%{$search}%")
            );
        }

        if ($delegId) {
            $query->where('delegation_technique_id', $delegId);
        }

        $caisses     = $query->paginate(15)->withQueryString();
        $delegations = DelegationTechnique::orderBy('region')->get();

        return view('dg.reseau.caisses', compact('caisses', 'delegations', 'search', 'delegId'));
    }

    public function caisse(Caisse $caisse): View
    {
        $this->checkDg();

        $caisse->load(['delegationTechnique', 'agences.guichets', 'agences.agents']);

        return view('dg.reseau.caisse-show', compact('caisse'));
    }

    public function agences(Request $request): View
    {
        $this->checkDg();

        $search  = trim((string) $request->get('search', ''));
        $caisseId = (int) $request->get('caisse', 0);

        $query = Agence::with(['superviseurCaisse', 'delegationTechnique'])
            ->withCount(['agents', 'guichets'])
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('chef_nom', 'like', "%{$search}%")
            );
        }

        if ($caisseId) {
            $query->where('superviseur_caisse_id', $caisseId);
        }

        $agences  = $query->paginate(15)->withQueryString();
        $caisses  = Caisse::orderBy('nom')->get();

        return view('dg.reseau.agences', compact('agences', 'caisses', 'search', 'caisseId'));
    }

    public function agence(Agence $agence): View
    {
        $this->checkDg();

        $agence->load(['superviseurCaisse', 'delegationTechnique', 'guichets', 'agents']);

        return view('dg.reseau.agence-show', compact('agence'));
    }

    public function guichets(Request $request): View
    {
        $this->checkDg();

        $search   = trim((string) $request->get('search', ''));
        $agenceId = (int) $request->get('agence', 0);

        $query = Guichet::with('agence.superviseurCaisse')
            ->orderBy('nom');

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('chef_nom', 'like', "%{$search}%")
            );
        }

        if ($agenceId) {
            $query->where('agence_id', $agenceId);
        }

        $guichets = $query->paginate(15)->withQueryString();
        $agences  = Agence::orderBy('nom')->get();

        return view('dg.reseau.guichets', compact('guichets', 'agences', 'search', 'agenceId'));
    }
}
