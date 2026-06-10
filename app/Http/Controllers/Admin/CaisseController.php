<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Services\AgentAccountService;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaisseController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    public function index(Request $request): View
{
    $search = trim((string) $request->query('search', ''));

    return view('admin.caisses.index', [
        'caisses' => Caisse::query()
            // On charge la délégation, le directeur, le secrétaire ET les services avec leurs agents
            ->with(['delegationTechnique', 'directeur', 'secretaire', 'services.agents']) 
            ->when($search !== '', function (EloquentBuilder $query) use ($search): void {
                // ... (Garde ton code de recherche identique ici) ...
            })
            ->latest()
            ->get(),
        'delegations' => DelegationTechnique::query()->orderBy('region')->get(),
        'search' => $search,
        'stats' => [
            'total' => Caisse::count(),
            'par_delegation' => DelegationTechnique::query()
                ->withCount('caisses')
                ->orderBy('region')
                ->get(),
        ],
    ]);
}

    public function create(): View
    {
        return view('admin.caisses.create', $this->formData());
    }

    public function show(Caisse $caisse): View
    {
        $caisse->load(['services.chef', 'services.agents', 'agences.chef', 'delegationTechnique']);

        return view('admin.caisses.show', compact('caisse'));
    }
    public function directionsIndex(Caisse $caisse): View
    {
        return view('admin.caisses.directions', [
            'caisse' => $caisse->load('delegationTechnique'),
            'caisseDirections' => collect(),
        ]);
    }
 public function affecterService(Caisse $caisse)
{
    // On sélectionne uniquement les agents :
    // 1. Dont la fonction est strictement 'Chef de Service'
    // 2. Qui ne sont chefs d'AUCUN service (relation 'ledService' inexistante)
    $chefs = \App\Models\Agent::where('role', 'Chef de Service')
        ->whereDoesntHave('ledService')
        ->orderBy('nom', 'asc')
        ->orderBy('prenom', 'asc')
        ->get();

    return view('admin.caisses.services', compact('caisse', 'chefs'));
}

    public function affecterAgence(Caisse $caisse): View
    {
        $caisse->load('delegationTechnique');

        $chefs = Agent::where('role', "Chef d'Agence")
            ->whereDoesntHave('ledAgence')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        $secretaires = Agent::where('role', "Secrétaire d'Agence")
            ->whereDoesntHave('secretariedAgence')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.caisses.agences', compact('caisse', 'chefs', 'secretaires'));
    }

    public function servicesIndex(Caisse $caisse): View
    {
        return view('admin.caisses.services', [
            'caisse' => $caisse->load('delegationTechnique'),
            'services' => collect(),
        ]);
    }

    public function edit(Caisse $caisse): View
    {
        return view('admin.caisses.edit', array_merge(
            $this->formData($caisse),
            ['caisse' => $caisse->load(['delegationTechnique', 'directeur', 'secretaire'])]
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCaisse($request);

        $caisse = Caisse::query()->create($validated);

        Agent::findOrFail($validated['directeur_agent_id'])->update(['poste' => 'Directeur de ' . $caisse->nom]);
        Agent::findOrFail($validated['secretaire_agent_id'])->update(['poste' => 'Secrétaire de ' . $caisse->nom]);

        // Créer automatiquement les comptes des responsables
        $this->accounts->ensureAccount(Agent::findOrFail($validated['directeur_agent_id']));
        $this->accounts->ensureAccount(Agent::findOrFail($validated['secretaire_agent_id']));

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse créée avec succès. Les comptes des responsables ont été activés.');
    }

    public function update(Request $request, Caisse $caisse): RedirectResponse
    {
        $validated = $this->validateCaisse($request, $caisse);

        // Si les responsables changent, désactiver les anciens
        if ($caisse->directeur_agent_id && $caisse->directeur_agent_id !== (int) $validated['directeur_agent_id']) {
            $this->accounts->deactivateAccount(Agent::findOrFail($caisse->directeur_agent_id));
        }
        if ($caisse->secretaire_agent_id && $caisse->secretaire_agent_id !== (int) $validated['secretaire_agent_id']) {
            $this->accounts->deactivateAccount(Agent::findOrFail($caisse->secretaire_agent_id));
        }

        $caisse->update($validated);

        Agent::findOrFail($validated['directeur_agent_id'])->update(['poste' => 'Directeur de ' . $caisse->nom]);
        Agent::findOrFail($validated['secretaire_agent_id'])->update(['poste' => 'Secrétaire de ' . $caisse->nom]);

        // Activer les nouveaux responsables
        $this->accounts->ensureAccount(Agent::findOrFail($validated['directeur_agent_id']));
        $this->accounts->ensureAccount(Agent::findOrFail($validated['secretaire_agent_id']));

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse mise à jour avec succès.');
    }

    public function destroy(Request $request, Caisse $caisse): RedirectResponse
    {
        $caisse->delete();

        return redirect()
            ->back()
            ->with('status', 'Caisse supprimée avec succès.');
    }

    private function formData(?Caisse $caisse = null): array
    {
        // Agents libres (sans caisse) + le responsable actuel (pour edit)
        $directeurQuery = Agent::query()
            ->where('role', 'Directeur de Caisse')
            ->where(function (EloquentBuilder $q) use ($caisse): void {
                $q->whereNull('caisse_id');
                if ($caisse?->directeur_agent_id) {
                    $q->orWhere('id', $caisse->directeur_agent_id);
                }
            });

        $secretaireQuery = Agent::query()
            ->where('role', 'Secrétaire de Caisse')
            ->where(function (EloquentBuilder $q) use ($caisse): void {
                $q->whereNull('caisse_id');
                if ($caisse?->secretaire_agent_id) {
                    $q->orWhere('id', $caisse->secretaire_agent_id);
                }
            });

        return [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'directions'  => Direction::query()->with('directeur')->orderBy('nom')->get(),
            'directeurs'  => $directeurQuery->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires' => $secretaireQuery->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }

    private function validateCaisse(Request $request, ?Caisse $caisse = null): array
    {
        return $request->validate([
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'nom'                     => [
                'required',
                'string',
                'max:255',
                $caisse
                    ? Rule::unique('caisses', 'nom')->ignore($caisse->id)
                    : Rule::unique('caisses', 'nom'),
            ],
            'annee_ouverture'         => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'quartier'                => ['nullable', 'string', 'max:255'],
            'secretariat_telephone'   => ['required', 'string', 'max:30'],
            'directeur_agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['required', 'integer', 'exists:agents,id'],
        ], [
            'directeur_agent_id.required'  => 'Le directeur est obligatoire pour créer une Caisse.',
            'secretaire_agent_id.required' => 'Le secrétaire est obligatoire pour créer une Caisse.',
        ]);
    }
}
