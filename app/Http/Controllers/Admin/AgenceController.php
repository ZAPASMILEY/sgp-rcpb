<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgenceController extends Controller
{
    public function index(): View
    {
        return view('admin.agences.index', [
            'agences' => Agence::query()
                ->with([
                    'delegationTechnique',
                    'superviseurCaisse.superviseur.delegationTechnique',
                ])
                ->latest()
                ->paginate(12),
            'stats' => [
                'total' => Agence::count(),
                'par_delegation' => DelegationTechnique::query()
                    ->withCount('agences')
                    ->orderBy('region')
                    ->get(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.agences.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agences', 'nom')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                }),
            ],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'caisse_id'   => [
                'required',
                'integer',
                Rule::exists('caisses', 'id')->where('delegation_technique_id', $request->integer('delegation_technique_id')),
            ],
            'chef_agent_id'       => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ], [
            'nom.unique' => 'Cette agence existe deja pour la delegation technique selectionnee.',
            'caisse_id.required' => 'Veuillez choisir une caisse superviseur.',
            'caisse_id.exists' => 'La caisse choisie n\'appartient pas a la delegation technique selectionnee.',
        ]);

        Agence::query()->create($validated);

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence creee avec succes.');
    }

    public function show(Agence $agence): View
    {
        return view('admin.agences.show', [
            'agence' => $agence->load(['delegationTechnique', 'superviseurCaisse']),
        ]);
    }

    public function edit(Agence $agence): View
    {
        return view('admin.agences.edit', array_merge(
            $this->formData(),
            ['agence' => $agence]
        ));
    }

    private function formData(): array
    {
        return [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'caisses'     => Caisse::query()->with('agences')->orderBy('nom')->get(),
            'chefs'       => Agent::query()->where('fonction', "Chef d'Agence")->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires' => Agent::query()->where('fonction', "Secrétaire d'Agence")->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }

    public function update(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agences', 'nom')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                })->ignore($agence->id),
            ],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'caisse_id'   => ['required', 'integer', 'exists:caisses,id'],
            'chef_agent_id'           => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $agence->update($validated);

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence modifiee avec succes.');
    }

    public function destroy(Agence $agence): RedirectResponse
    {
        $agence->delete();

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence supprimee avec succes.');
    }

    public function agentsIndex(Agence $agence): View
    {
        return view('admin.agences.agents.index', [
            'agence' => $agence->load(['delegationTechnique', 'superviseurCaisse']),
            'agents' => Agent::query()
                ->where('agence_id', $agence->id)
                ->latest()
                ->paginate(12),
        ]);
    }

    public function createAgent(Agence $agence): View
    {
        return view('admin.agences.agents.create', [
            'agence' => $agence->load(['delegationTechnique', 'superviseurCaisse']),
            'agents' => Agent::query()
                ->whereNull('agence_id')
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'fonction']),
        ]);
    }

    public function storeAgent(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);
        $agent->update(['agence_id' => $agence->id]);

        return redirect()
            ->route('admin.agences.agents.index', $agence)
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) à '.$agence->nom.'.');
    }
}
