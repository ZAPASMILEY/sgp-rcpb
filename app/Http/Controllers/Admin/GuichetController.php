<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Guichet;
use App\Models\DelegationTechnique;
use App\Models\Poste;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuichetController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    /**
     * Liste des guichets avec statistiques
     */
    public function index()
    {
        $guichets = Guichet::with(['chef', 'agence.delegationTechnique'])
            ->latest()
            ->paginate(10);

        $delegations = DelegationTechnique::all()->map(function($dt) {
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

    public function create(): View
    {
        return view('admin.guichets.create', [
            'chefs'   => Agent::where('role', 'Chef de Guichet')->orderBy('nom')->get(['id', 'nom', 'prenom']),
            'agences' => Agence::with('delegationTechnique')->orderBy('nom')->get(['id', 'nom', 'delegation_technique_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom'               => ['required', 'string', 'max:255'],
            'agence_id'         => ['required', 'integer', 'exists:agences,id'],
            'chef_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
            'telephone_accueil' => ['required', 'string', 'max:30'],
        ], [
            'telephone_accueil.required' => "Le numéro de téléphone d'accueil est obligatoire.",
        ]);

        $guichet = Guichet::create($validated);

        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            $chef->update(['poste' => 'Chef de Guichet de ' . $guichet->nom]);
            $this->accounts->ensureAccount($chef->fresh());
        }

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet « '.$guichet->nom.' » créé.');
    }

    public function edit(Guichet $guichet): View
    {
        return view('admin.guichets.edit', [
            'guichet' => $guichet,
            'chefs'   => Agent::where('role', 'Chef de Guichet')->orderBy('nom')->get(['id', 'nom', 'prenom']),
            'agences' => Agence::with('delegationTechnique')->orderBy('nom')->get(['id', 'nom', 'delegation_technique_id']),
        ]);
    }

    public function update(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'nom'               => ['required', 'string', 'max:255'],
            'agence_id'         => ['required', 'integer', 'exists:agences,id'],
            'chef_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
            'telephone_accueil' => ['required', 'string', 'max:30'],
        ]);

        // Si le chef change, désactiver le compte de l'ancien chef
        if ($guichet->chef_agent_id && $guichet->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $this->accounts->deactivateAccount(Agent::findOrFail($guichet->chef_agent_id));
        }

        $guichet->update($validated);

        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            $chef->update(['poste' => 'Chef de Guichet de ' . $guichet->nom]);
            $this->accounts->ensureAccount($chef->fresh());
        }

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet « '.$guichet->nom.' » modifié.');
    }

    public function destroy(Guichet $guichet): RedirectResponse
    {
        $nom = $guichet->nom;
        $guichet->delete();

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet « '.$nom.' » supprimé.');
    }

    /**
     * Liste des agents d'un guichet spécifique
     */
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

    /**
     * Formulaire d'affectation d'un agent au guichet
     */
    public function createAgent(Guichet $guichet): View
    {
        return view('admin.guichets.agents.create', [
            'guichet' => $guichet->load(['chef', 'agence']),
            'agents'  => Agent::query()
                ->where('agence_id', $guichet->agence_id)
                ->whereNull('guichet_id')
                ->where('id', '!=', $guichet->chef_agent_id)
                // SECURITÉ : On filtre les rôles interdits (Direction/Admin Agence)
                ->whereNotIn('role', ["Chef d'Agence", "Secrétaire d'Agence"])
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role', 'matricule', 'poste']),
            'postes'  => Poste::where('fonction', 'Agent')->orderBy('libelle')->pluck('libelle'),
        ]);
    }

    /**
     * Enregistrement de l'affectation
     */
    public function storeAgent(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
            'poste'    => ['required', 'string', 'max:150'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'poste.required'    => 'La fonction occupée est obligatoire.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);

        // LOGIQUE MÉTIER : Empêcher les rôles administratifs d'agence au guichet
        $fonctionsInterdites = ["Chef d'Agence", "Secrétaire d'Agence"];
        if (in_array($agent->role, $fonctionsInterdites)) {
            return back()->withErrors([
                'agent_id' => "Action impossible : le rôle de « {$agent->role} » est rattaché au siège de l'agence."
            ]);
        }

        // LOGIQUE MÉTIER : Unicité du Chef de Guichet
        if ($agent->role === "Chef de Guichet") {
            $dejaUnChef = Agent::where('guichet_id', $guichet->id)
                               ->where('role', 'Chef de Guichet')
                               ->exists();

            if ($guichet->chef_agent_id || $dejaUnChef) {
                return back()->withErrors(['agent_id' => "Ce guichet possède déjà un Chef de Guichet."]);
            }
        }

        $agent->update(['guichet_id' => $guichet->id, 'poste' => $validated['poste'] ?? null]);
        $this->accounts->ensureAccount($agent->fresh());

        return redirect()
            ->route('admin.guichets.agents.index', $guichet)
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) à '.$guichet->nom.'.');
    }
}