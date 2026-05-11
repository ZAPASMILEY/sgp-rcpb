<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgenceController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    public function index(): View
    {
        return view('admin.agences.index', [
            'agences' => Agence::query()
                ->with(['delegationTechnique', 'caisse', 'chef'])
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
                'required', 'string', 'max:255',
                Rule::unique('agences', 'nom')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                }),
            ],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'caisse_id' => [
                'required', 'integer',
                Rule::exists('caisses', 'id')->where('delegation_technique_id', $request->integer('delegation_technique_id')),
            ],
            'chef_agent_id'       => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'nom.unique'                   => 'Cette agence existe déjà pour la délégation technique sélectionnée.',
            'chef_agent_id.required'       => "Le chef d'agence est obligatoire.",
            'secretaire_agent_id.required' => 'Le secrétaire est obligatoire.',
            'caisse_id.required'           => 'Veuillez choisir une caisse superviseur.',
            'caisse_id.exists'             => "La caisse choisie n'appartient pas à la délégation technique sélectionnée.",
        ]);

        Agence::query()->create($validated);

        $this->accounts->ensureAccount(Agent::findOrFail($validated['chef_agent_id']));
        $this->accounts->ensureAccount(Agent::findOrFail($validated['secretaire_agent_id']));

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence créée avec succès. Les comptes des responsables ont été activés.');
    }

    public function show(Agence $agence): View
    {
        return view('admin.agences.show', [
            'agence' => $agence->load(['delegationTechnique', 'caisse', 'chef']),
        ]);
    }

    public function edit(Agence $agence): View
    {
        return view('admin.agences.edit', array_merge(
            $this->formData($agence),
            ['agence' => $agence]
        ));
    }

    public function update(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required', 'string', 'max:255',
                Rule::unique('agences', 'nom')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                })->ignore($agence->id),
            ],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'caisse_id'           => ['required', 'integer', 'exists:caisses,id'],
            'chef_agent_id'       => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ]);

        // Désactiver les anciens responsables si changement
        if ($agence->chef_agent_id && $agence->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $this->accounts->deactivateAccount(Agent::findOrFail($agence->chef_agent_id));
        }
        if ($agence->secretaire_agent_id && $agence->secretaire_agent_id !== (int) $validated['secretaire_agent_id']) {
            $this->accounts->deactivateAccount(Agent::findOrFail($agence->secretaire_agent_id));
        }

        $agence->update($validated);

        $this->accounts->ensureAccount(Agent::findOrFail($validated['chef_agent_id']));
        $this->accounts->ensureAccount(Agent::findOrFail($validated['secretaire_agent_id']));

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence modifiée avec succès.');
    }

    public function destroy(Agence $agence): RedirectResponse
    {
        $agence->delete();

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence supprimée avec succès.');
    }

    public function agentsIndex(Agence $agence): View
    {
        return view('admin.agences.agents.index', [
            'agence' => $agence->load(['delegationTechnique', 'caisse', 'chef']),
            'agents' => Agent::query()
                ->where('agence_id', $agence->id)
                ->latest()
                ->paginate(12),
        ]);
    }

    public function createAgent(Agence $agence): View
    {
        return view('admin.agences.agents.create', [
            'agence' => $agence->load(['delegationTechnique', 'caisse', 'chef']),
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

    private function formData(?Agence $agence = null): array
    {
        $chefsQuery = Agent::query()
            ->where('fonction', "Chef d'Agence")
            ->where(function (EloquentBuilder $q) use ($agence): void {
                $q->whereNull('agence_id');
                if ($agence?->chef_agent_id) {
                    $q->orWhere('id', $agence->chef_agent_id);
                }
            });

        $secretairesQuery = Agent::query()
            ->where('fonction', "Secrétaire d'Agence")
            ->where(function (EloquentBuilder $q) use ($agence): void {
                $q->whereNull('agence_id');
                if ($agence?->secretaire_agent_id) {
                    $q->orWhere('id', $agence->secretaire_agent_id);
                }
            });

        return [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'caisses'     => Caisse::query()->with('agences')->orderBy('nom')->get(),
            'chefs'       => $chefsQuery->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires' => $secretairesQuery->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }
}
