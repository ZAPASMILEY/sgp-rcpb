<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
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

    public function index(Request $request): View
    {
        $caisseId = $request->integer('caisse_id') ?: null;

        return view('admin.agences.index', [
            'agences' => Agence::query()
                ->with(['delegationTechnique', 'caisse', 'chef', 'secretaire'])
                ->when($caisseId, fn ($q) => $q->where('caisse_id', $caisseId))
                ->latest()
                ->get(),
            'caisses'  => Caisse::orderBy('nom')->get(['id', 'nom']),
            'caisseId' => $caisseId,
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
            'nom'                 => ['required', 'string', 'max:255', Rule::unique('agences', 'nom')],
            'caisse_id'           => ['required', 'integer', 'exists:caisses,id'],
            'chef_agent_id'       => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['required', 'integer', 'exists:agents,id'],
            'telephone_accueil'   => ['required', 'string', 'max:30'],
        ], [
            'nom.unique'                   => 'Une agence avec ce nom existe déjà.',
            'chef_agent_id.required'       => "Le chef d'agence est obligatoire.",
            'secretaire_agent_id.required' => 'Le secrétaire est obligatoire.',
            'caisse_id.required'           => 'Veuillez choisir une caisse.',
            'telephone_accueil.required'   => "Le numéro de téléphone d'accueil est obligatoire.",
        ]);

        // Dériver la délégation depuis la caisse choisie
        $caisse = Caisse::findOrFail($validated['caisse_id']);
        $validated['delegation_technique_id'] = $caisse->delegation_technique_id;

        $agence = Agence::query()->create($validated);

        $chef = Agent::findOrFail($validated['chef_agent_id']);
        $chef->update([
            'role'      => "Chef d'Agence",
            'poste'     => "Chef d'Agence de " . $agence->nom,
            'agence_id' => $agence->id,
        ]);

        $secretaire = Agent::findOrFail($validated['secretaire_agent_id']);
        $secretaire->update([
            'role'      => "Secrétaire d'Agence",
            'poste'     => "Secrétaire d'Agence de " . $agence->nom,
            'agence_id' => $agence->id,
        ]);

        $this->accounts->ensureAccount($chef->fresh());
        $this->accounts->ensureAccount($secretaire->fresh());

        if ($chefUser = $chef->fresh()->user) {
            Alerte::notifier(
                $chefUser->id,
                "Vous avez été nommé(e) Chef d'Agence",
                "Vous êtes désormais Chef d'Agence de « " . $agence->nom . ' ».',
                'haute',
                route('admin.agents.show', $chef)
            );
        }
        if ($secUser = $secretaire->fresh()->user) {
            Alerte::notifier(
                $secUser->id,
                "Vous avez été nommé(e) Secrétaire d'Agence",
                "Vous êtes désormais Secrétaire d'Agence de « " . $agence->nom . ' ».',
                'haute',
                route('admin.agents.show', $secretaire)
            );
        }

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
            'telephone_accueil'   => ['required', 'string', 'max:30'],
        ]);

        // Réinitialiser les anciens responsables si changement
        if ($agence->chef_agent_id && $agence->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $ancienChef = Agent::findOrFail($agence->chef_agent_id);
            $ancienChef->update(['role' => 'Agent', 'poste' => null, 'agence_id' => null]);
            $this->accounts->deactivateAccount($ancienChef);
        }
        if ($agence->secretaire_agent_id && $agence->secretaire_agent_id !== (int) $validated['secretaire_agent_id']) {
            $ancienSec = Agent::findOrFail($agence->secretaire_agent_id);
            $ancienSec->update(['role' => 'Agent', 'poste' => null, 'agence_id' => null]);
            $this->accounts->deactivateAccount($ancienSec);
        }

        $agence->update($validated);

        $chef = Agent::findOrFail($validated['chef_agent_id']);
        $chef->update([
            'role'      => "Chef d'Agence",
            'poste'     => "Chef d'Agence de " . $agence->nom,
            'agence_id' => $agence->id,
        ]);

        $secretaire = Agent::findOrFail($validated['secretaire_agent_id']);
        $secretaire->update([
            'role'      => "Secrétaire d'Agence",
            'poste'     => "Secrétaire d'Agence de " . $agence->nom,
            'agence_id' => $agence->id,
        ]);

        $this->accounts->ensureAccount($chef->fresh());
        $this->accounts->ensureAccount($secretaire->fresh());

        if ($chefUser = $chef->fresh()->user) {
            Alerte::notifier(
                $chefUser->id,
                "Vous avez été nommé(e) Chef d'Agence",
                "Vous êtes désormais Chef d'Agence de « " . $agence->nom . ' ».',
                'haute',
                route('admin.agents.show', $chef)
            );
        }
        if ($secUser = $secretaire->fresh()->user) {
            Alerte::notifier(
                $secUser->id,
                "Vous avez été nommé(e) Secrétaire d'Agence",
                "Vous êtes désormais Secrétaire d'Agence de « " . $agence->nom . ' ».',
                'haute',
                route('admin.agents.show', $secretaire)
            );
        }

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
                ->get(),
        ]);
    }

    public function createAgent(Agence $agence): View
    {
        return view('admin.agences.agents.create', [
            'agence'  => $agence->load(['delegationTechnique', 'caisse', 'chef']),
            'agents'  => Agent::query()
                ->where('role', 'Agent')
                ->whereNull('agence_id')
                ->whereNull('service_id')
                ->whereNull('direction_id')
                ->whereNull('caisse_id')
                ->whereNull('delegation_technique_id')
                ->whereNull('guichet_id')
                ->whereNull('entite_id')
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'matricule', 'poste']),
            'postes'  => \App\Models\Poste::where('fonction', 'Agent')->orderBy('libelle')->pluck('libelle'),
        ]);
    }

    public function detachAgent(Agence $agence, Agent $agent): RedirectResponse
    {
        if ($agent->agence_id !== $agence->id) {
            return redirect()
                ->route('admin.agences.agents.index', $agence)
                ->with('error', "Cet agent n'appartient pas à cette agence.");
        }

        $wasChef = $agent->id === $agence->chef_agent_id;
        $wasSec  = $agent->id === $agence->secretaire_agent_id;

        $agent->update([
            'agence_id'  => null,
            'guichet_id' => null,
            'poste'      => null,
            'role'       => 'Agent',
        ]);

        if ($wasChef) {
            $agence->update(['chef_agent_id' => null]);
        }
        if ($wasSec) {
            $agence->update(['secretaire_agent_id' => null]);
        }

        $this->accounts->deactivateAccount($agent->fresh());

        return redirect()
            ->route('admin.agences.agents.index', $agence)
            ->with('status', $agent->prenom . ' ' . $agent->nom . ' retiré(e) de l\'agence.');
    }

    public function storeAgent(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
            'poste'    => ['required', 'string', 'max:150'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
            'poste.required'    => 'La fonction occupée est obligatoire.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);
        $agent->update(['agence_id' => $agence->id, 'poste' => $validated['poste'] ?? null]);
        $this->accounts->ensureAccount($agent->fresh());

        if ($agentUser = $agent->fresh()->user) {
            Alerte::notifier(
                $agentUser->id,
                'Vous avez été affecté(e) à une agence',
                'Vous êtes désormais agent de « ' . $agence->nom . ' ».',
                'moyenne',
                route('admin.agents.show', $agent)
            );
        }

        return redirect()
            ->route('admin.agences.agents.index', $agence)
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) à '.$agence->nom.'.');
    }

    private function formData(?Agence $agence = null): array
    {
        $chefsQuery = Agent::query()
            ->where('role', "Chef d'Agence")
            ->where(function (EloquentBuilder $q) use ($agence): void {
                $q->whereNull('agence_id');
                if ($agence?->chef_agent_id) {
                    $q->orWhere('id', $agence->chef_agent_id);
                }
            });

        $secretairesQuery = Agent::query()
            ->where('role', "Secrétaire d'Agence")
            ->where(function (EloquentBuilder $q) use ($agence): void {
                $q->whereNull('agence_id');
                if ($agence?->secretaire_agent_id) {
                    $q->orWhere('id', $agence->secretaire_agent_id);
                }
            });

        return [
            'delegations'    => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'caisses'        => Caisse::query()->with(['directeur', 'delegationTechnique'])->orderBy('nom')->get(),
            'chefs'          => $chefsQuery->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires'    => $secretairesQuery->orderBy('nom')->orderBy('prenom')->get(),
            'totalChefs'     => Agent::where('role', "Chef d'Agence")->count(),
            'totalSecretaires' => Agent::where('role', "Secrétaire d'Agence")->count(),
        ];
    }
}
