<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Caisse;
use App\Models\Guichet;
use App\Models\DelegationTechnique;
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
    public function index(Request $request)
    {
        $caisseId = $request->integer('caisse_id') ?: null;

        $guichets = Guichet::with(['chef', 'agence.delegationTechnique', 'agence.caisse'])
            ->when($caisseId, fn ($q) => $q->whereHas('agence', fn ($q2) => $q2->where('caisse_id', $caisseId)))
            ->latest()
            ->get();

        $delegations = DelegationTechnique::all()->map(function ($dt) {
            $dt->guichets_count = Guichet::whereHas('agence', fn ($q) => $q->where('delegation_technique_id', $dt->id))->count();
            return $dt;
        });

        $stats = [
            'total'          => Guichet::count(),
            'par_delegation' => $delegations,
        ];

        return view('admin.guichets.index', [
            'guichets' => $guichets,
            'caisses'  => Caisse::orderBy('nom')->get(['id', 'nom']),
            'caisseId' => $caisseId,
            'stats'    => $stats,
        ]);
    }

    public function create(): View
    {
        return view('admin.guichets.create', [
            'chefs'   => Agent::query()
                ->whereIn('role', ['Chef de Guichet', 'Agent'])
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role', 'agence_id']),
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

        // Valider que le chef appartient bien à l'agence choisie (si agent simple)
        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            if ($chef->role === 'Agent' && $chef->agence_id && $chef->agence_id !== (int) $validated['agence_id']) {
                return back()->withErrors(['chef_agent_id' => "Cet agent n'appartient pas à l'agence sélectionnée."])->withInput();
            }
        }

        $guichet = Guichet::create($validated);

        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            $chef->update([
                'role'      => 'Chef de Guichet',
                'poste'     => 'Chef de Guichet de ' . $guichet->nom,
                'agence_id' => $guichet->agence_id,
                'guichet_id'=> $guichet->id,
            ]);
            $this->accounts->ensureAccount($chef->fresh());
            if ($chefUser = $chef->fresh()->user) {
                Alerte::notifier(
                    $chefUser->id,
                    'Vous avez été nommé(e) Chef de Guichet',
                    'Vous êtes désormais Chef de Guichet de « ' . $guichet->nom . ' ».',
                    'haute',
                    route('admin.agents.show', $chef)
                );
            }
        }

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet « '.$guichet->nom.' » créé.');
    }

    public function edit(Guichet $guichet): View
    {
        return view('admin.guichets.edit', [
            'guichet' => $guichet,
            'chefs'   => Agent::query()
                ->whereIn('role', ['Chef de Guichet', 'Agent'])
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role', 'agence_id']),
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

        // Valider que le chef appartient bien à l'agence choisie (si agent simple)
        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            if ($chef->role === 'Agent' && $chef->agence_id && $chef->agence_id !== (int) $validated['agence_id']) {
                return back()->withErrors(['chef_agent_id' => "Cet agent n'appartient pas à l'agence sélectionnée."])->withInput();
            }
        }

        // Si le chef change, réinitialiser l'ancien chef
        if ($guichet->chef_agent_id && $guichet->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $ancienChef = Agent::findOrFail($guichet->chef_agent_id);
            $ancienChef->update(['role' => 'Agent', 'poste' => null, 'guichet_id' => null]);
            $this->accounts->deactivateAccount($ancienChef);
        }

        $guichet->update($validated);

        if ($validated['chef_agent_id']) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            $chef->update([
                'role'       => 'Chef de Guichet',
                'poste'      => 'Chef de Guichet de ' . $guichet->nom,
                'agence_id'  => $guichet->agence_id,
                'guichet_id' => $guichet->id,
            ]);
            $this->accounts->ensureAccount($chef->fresh());
            if ($chefUser = $chef->fresh()->user) {
                Alerte::notifier(
                    $chefUser->id,
                    'Vous avez été nommé(e) Chef de Guichet',
                    'Vous êtes désormais Chef de Guichet de « ' . $guichet->nom . ' ».',
                    'haute',
                    route('admin.agents.show', $chef)
                );
            }
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
                ->get(),
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
                // Exclure le chef de guichet actuel uniquement s'il existe
                ->when($guichet->chef_agent_id, fn ($q) => $q->where('id', '!=', $guichet->chef_agent_id))
                // SÉCURITÉ : On filtre les rôles interdits (Direction/Admin Agence)
                ->whereNotIn('role', ["Chef d'Agence", "Secrétaire d'Agence"])
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role', 'matricule']),
        ]);
    }

    /**
     * Détacher un agent du guichet (sans le supprimer)
     */
    public function detachAgent(Guichet $guichet, Agent $agent): RedirectResponse
    {
        // Vérifier que l'agent appartient bien à ce guichet
        if ($agent->guichet_id !== $guichet->id) {
            return redirect()
                ->route('admin.guichets.agents.index', $guichet)
                ->with('error', "Cet agent n'appartient pas à ce guichet.");
        }

        $wasChef = $agent->id === $guichet->chef_agent_id;

        // Retirer du guichet, garder l'agence parente
        $agent->update([
            'guichet_id' => null,
            'poste'      => null,
            'role'       => $wasChef ? 'Agent' : $agent->role,
        ]);

        // Si c'était le chef, retirer aussi la référence sur le guichet
        if ($wasChef) {
            $guichet->update(['chef_agent_id' => null]);
            $this->accounts->deactivateAccount($agent->fresh());
        }

        return redirect()
            ->route('admin.guichets.agents.index', $guichet)
            ->with('status', $agent->prenom . ' ' . $agent->nom . ' retiré(e) du guichet.');
    }

    /**
     * Enregistrement de l'affectation
     */
    public function storeAgent(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);

        // LOGIQUE MÉTIER : Empêcher les rôles administratifs d'agence au guichet
        $fonctionsInterdites = ["Chef d'Agence", "Secrétaire d'Agence"];
        if (in_array($agent->role, $fonctionsInterdites)) {
            return back()->withErrors([
                'agent_id' => "Action impossible : le rôle de « {$agent->role} » est rattaché au siège de l'agence."
            ]);
        }

        // SÉCURITÉ : L'agent doit appartenir à l'agence du guichet
        if ($agent->agence_id !== $guichet->agence_id) {
            return back()->withErrors([
                'agent_id' => "Cet agent n'appartient pas à l'agence de ce guichet."
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

        // Le poste est automatiquement défini selon le guichet
        $posteAuto = 'Agent de ' . $guichet->nom;

        $agent->update([
            'guichet_id' => $guichet->id,
            'agence_id'  => $guichet->agence_id,
            'poste'      => $posteAuto,
        ]);
        $this->accounts->ensureAccount($agent->fresh());

        if ($agentUser = $agent->fresh()->user) {
            Alerte::notifier(
                $agentUser->id,
                'Vous avez été affecté(e) à un guichet',
                'Vous êtes désormais agent de « ' . $guichet->nom . ' ».',
                'moyenne',
                route('admin.agents.show', $agent)
            );
        }

        return redirect()
            ->route('admin.guichets.agents.index', $guichet)
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) à '.$guichet->nom.'.');
    }
}