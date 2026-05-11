<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Guichet;
use App\Models\DelegationTechnique;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuichetController extends Controller
{
        public function index()
        {
            $guichets = Guichet::with(['chef', 'agence.delegationTechnique'])
                ->latest()
                ->paginate(10);

            // On récupère les délégations et on compte manuellement pour éviter l'erreur de méthode
            $delegations = DelegationTechnique::all()->map(function($dt) {
                // On compte les guichets qui appartiennent aux agences de cette DT
                $dt->guichets_count = Guichet::whereHas('agence', function($q) use ($dt) {
                    $q->where('delegation_technique_id', $dt->id);
                })->count();
                return $dt;
            });

            $stats = [
                'total' => Guichet::count(),
                'par_delegation' => $delegations
            ];

            return view('admin.guichets.index', compact('guichets', 'stats'));
        }

    public function agentsIndex(Guichet $guichet): View
    {
        return view('admin.guichets.agents.index', [
            'guichet' => $guichet->load(['chef', 'agence.delegationTechnique']),
            'agents'  => Agent::query()
                ->where('guichet_id', $guichet->id)
                ->latest()
                ->paginate(12),
        ]);
    }

    public function createAgent(Guichet $guichet): View
    {
        return view('admin.guichets.agents.create', [
            'guichet' => $guichet->load(['chef', 'agence']),
            'agents'  => Agent::query()
                ->where('agence_id', $guichet->agence_id)
                ->whereNull('guichet_id')
                ->where('id', '!=', $guichet->chef_agent_id)
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'fonction']),
        ]);
    }

    public function storeAgent(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);
        $agent->update(['guichet_id' => $guichet->id]);

        return redirect()
            ->route('admin.guichets.agents.index', $guichet)
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) à '.$guichet->nom.'.');
    }
}