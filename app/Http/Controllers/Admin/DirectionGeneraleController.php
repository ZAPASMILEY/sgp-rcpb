<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Poste;
use App\Models\Service;
use App\Models\User;
use App\Services\AgentAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class DirectionGeneraleController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    private function getDirection(): ?Direction
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return null;
        }
        return Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->with([
                'services' => fn ($q) => $q->with(['chef', 'agents']),
            ])
            ->first();
    }

    public function index(): View
    {
        $entite    = Entite::latest()->first();
        $direction = $entite
            ? Direction::where('entite_id', $entite->id)
                ->where('nom', 'Direction Générale')
                ->with(['services' => fn ($q) => $q->with(['chef', 'agents'])])
                ->first()
            : null;

        $membres     = collect();
        $secretaires = collect();
        $conseillers = collect();
        $services    = collect();
        $agentsDisponibles = collect();

        if ($entite) {
            // DG et Assistante uniquement
            $agentIds = array_values(array_filter([
                $entite->dg_agent_id,
                $entite->assistante_agent_id,
            ]));
            $membres = $agentIds
                ? User::whereIn('agent_id', $agentIds)->get()
                : collect();

            $secretaires = User::where('role', 'Secretaire_Assistante')
                ->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))
                ->when($entite->dga_secretaire_agent_id, fn ($q) =>
                    $q->where('agent_id', '!=', $entite->dga_secretaire_agent_id)
                )
                ->get();

            $conseillers = User::where('role', 'Conseillers_Dg')
                ->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))
                ->with('agent')
                ->get();
        }

        $chefsDisponibles = collect();

        if ($direction) {
            $services = $direction->services->map(function (Service $s): array {
                $chef     = $s->chef;
                $chefUser = $chef ? User::where('agent_id', $chef->id)->first() : null;
                return [
                    'service'  => $s,
                    'chef'     => $chef,
                    'chefUser' => $chefUser,
                    'nbAgents' => $s->agents->count(),
                ];
            });

            // Exclure le DGA et sa secrétaire — ils appartiennent à la Direction DGA
            $exclus = array_values(array_filter([
                $entite?->dga_agent_id,
                $entite?->dga_secretaire_agent_id,
            ]));

            $agentsDisponibles = Agent::where('direction_id', $direction->id)
                ->whereNull('service_id')
                ->when($exclus, fn ($q) => $q->whereNotIn('id', $exclus))
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role']);

            // Chefs de service disponibles : rôle Chef de Service, sans service affecté
            $dejaChefs = Service::whereNotNull('chef_agent_id')->pluck('chef_agent_id');
            $chefsDisponibles = Agent::where('role', 'Chef de Service')
                ->whereNull('service_id')
                ->whereNotIn('id', $dejaChefs)
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'matricule']);
        }

        return view('admin.direction-generale.index', [
            'entite'            => $entite,
            'direction'         => $direction,
            'membres'           => $membres,
            'secretaires'       => $secretaires,
            'conseillers'       => $conseillers,
            'services'          => $services,
            'agentsDisponibles' => $agentsDisponibles,
            'chefsDisponibles'  => $chefsDisponibles,
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction, 404, 'Direction Générale introuvable.');

        $validated = $request->validate([
            'nom'           => ['required', 'string', 'max:150'],
            'chef_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ], ['nom.required' => 'Le nom du service est obligatoire.']);

        $service = Service::create([
            'nom'           => $validated['nom'],
            'direction_id'  => $direction->id,
            'chef_agent_id' => $validated['chef_agent_id'] ?? null,
        ]);

        if (! empty($validated['chef_agent_id'])) {
            $chef = Agent::findOrFail($validated['chef_agent_id']);
            $chef->update([
                'direction_id' => $direction->id,
                'service_id'   => $service->id,
                'poste'        => 'Chef du Service ' . $service->nom,
            ]);
            $this->accounts->ensureAccount($chef->fresh());
        }

        return back()->with('status', 'Service « ' . $validated['nom'] . ' » créé avec succès.');
    }

    public function updateChefService(Request $request, Service $service): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction || $service->direction_id !== $direction->id, 403);

        $validated = $request->validate([
            'chef_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], ['chef_agent_id.required' => 'Veuillez sélectionner un chef de service.']);

        // Libérer l'ancien chef si changement
        if ($service->chef_agent_id && $service->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $ancien = Agent::find($service->chef_agent_id);
            if ($ancien) {
                $this->accounts->deactivateAccount($ancien);
                $ancien->update(['service_id' => null, 'direction_id' => null]);
            }
        }

        $service->update(['chef_agent_id' => $validated['chef_agent_id']]);

        $chef = Agent::findOrFail($validated['chef_agent_id']);
        $chef->update([
            'direction_id' => $direction->id,
            'service_id'   => $service->id,
            'poste'        => 'Chef du Service ' . $service->nom,
        ]);
        $this->accounts->ensureAccount($chef->fresh());

        return back()->with('status', $chef->prenom . ' ' . $chef->nom . ' affecté(e) comme chef du service « ' . $service->nom . ' ».');
    }

    public function destroyService(Service $service): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction || $service->direction_id !== $direction->id, 403);

        Agent::where('service_id', $service->id)->update(['service_id' => null]);

        $nom = $service->nom;
        $service->delete();

        return back()->with('status', 'Service « ' . $nom . ' » supprimé.');
    }

    public function storeAgent(Request $request, Service $service): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction || $service->direction_id !== $direction->id, 403);

        if (! $service->chef_agent_id) {
            return back()->with('error', 'Impossible d\'ajouter un agent : le service « ' . $service->nom . ' » n\'a pas encore de chef.');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], ['agent_id.required' => 'Sélectionnez un agent.']);

        $agent = Agent::findOrFail($validated['agent_id']);

        if ((int) $agent->direction_id !== $direction->id) {
            return back()->with('error', 'Cet agent ne fait pas partie de la Direction Générale.');
        }

        // Empêcher l'ajout du DGA ou de sa secrétaire dans un service de la DG
        $entite = Entite::latest()->first();
        $exclus = array_filter([$entite?->dga_agent_id, $entite?->dga_secretaire_agent_id]);
        if (in_array($agent->id, $exclus, true)) {
            return back()->with('error', 'Cet agent appartient à la Direction Générale Adjointe et ne peut pas être affecté à un service de la Direction Générale.');
        }

        $agent->update(['service_id' => $service->id]);
        $this->accounts->ensureAccount($agent->fresh());

        return back()->with('status', $agent->prenom . ' ' . $agent->nom . ' affecté(e) au service « ' . $service->nom . ' ».');
    }

    public function removeAgent(Service $service, Agent $agent): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction || $service->direction_id !== $direction->id, 403);
        abort_if((int) $agent->service_id !== $service->id, 403);

        $agent->update(['service_id' => null]);

        return back()->with('status', $agent->prenom . ' ' . $agent->nom . ' retiré(e) du service « ' . $service->nom . ' ».');
    }

    public function create(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $dejaConfiguree = Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->exists();

        if ($dejaConfiguree) {
            return redirect()
                ->route('admin.direction-generale.index')
                ->with('error', 'La Direction Générale est déjà configurée. Utilisez le bouton Modifier pour apporter des changements.');
        }

        return view('admin.direction-generale.create', [
            'entite'         => $entite,
            'dejaConfiguree' => $dejaConfiguree,
            'dg_agents'      => \App\Models\Agent::query()->where('role', 'Directeur Général')->orderBy('nom')->get(),
            'assistantes'    => \App\Models\Agent::query()->where('role', 'Assistante DG')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $dejaConfiguree = Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->exists();

        if ($dejaConfiguree) {
            return redirect()
                ->route('admin.direction-generale.index')
                ->with('error', 'La Direction Générale est déjà configurée.');
        }

        $validated = $request->validate([
            'dg_agent_id'         => ['nullable', 'integer', 'exists:agents,id'],
            'assistante_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $entite->update($validated);

        Direction::create([
            'entite_id'          => $entite->id,
            'nom'                => 'Direction Générale',
            'directeur_agent_id' => $validated['dg_agent_id'] ?? null,
        ]);

        if (!empty($validated['dg_agent_id'])) {
            \App\Models\Agent::findOrFail($validated['dg_agent_id'])->update(['poste' => 'Directeur Général']);
        }
        if (!empty($validated['assistante_agent_id'])) {
            \App\Models\Agent::findOrFail($validated['assistante_agent_id'])->update(['poste' => 'Assistante du Directeur Général']);
        }

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Direction Generale configuree avec succes.');
    }

    /**
     * Affiche le formulaire de modification d'un membre de la Direction Générale
     * (DG, DGA ou Assistante_Dg).
     */
    public function editMembre(User $user): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.direction-generale.index');
        }

        // Sécurité : seuls les membres de l'entite courante peuvent être édités
        $roleColumn = match ($user->role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            default         => null,
        };
        if (! $roleColumn || (int) $entite->{$roleColumn} !== (int) $user->agent_id) {
            abort(403);
        }

        $user->load('agent');

        return view('admin.direction-generale.edit-membre', compact('user', 'entite'));
    }

    /**
     * Met à jour le compte d'un membre principal (DG, DGA ou Assistante_Dg).
     *
     * Met également à jour les champs correspondants sur l'entite (directrice_generale_nom, etc.)
     * pour que l'affichage admin reste cohérent avec les comptes utilisateurs.
     */
    public function updateMembre(Request $request, User $user): RedirectResponse
    {
        $entite = Entite::latest()->first();
        $roleColumn = match ($user->role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            default         => null,
        };
        if (! $entite || ! $roleColumn || (int) $entite->{$roleColumn} !== (int) $user->agent_id) {
            abort(403);
        }

        $validated = $request->validate([
            'prenom'              => ['required', 'string', 'max:100'],
            'nom'                 => ['required', 'string', 'max:100'],
            'email'               => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'sexe'                => ['required', 'in:homme,femme'],
            'date_prise_fonction' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        // Mise à jour du compte utilisateur (name + email uniquement)
        $user->update([
            'name'  => $validated['prenom'] . ' ' . $validated['nom'],
            'email' => $validated['email'],
        ]);

        // Mise à jour de la fiche agent (données personnelles)
        if ($user->agent_id) {
            Agent::where('id', $user->agent_id)->update([
                'prenom'              => $validated['prenom'],
                'nom'                 => $validated['nom'],
                'email'               => $validated['email'],
                'sexe'                => $validated['sexe'],
                'date_debut_fonction' => $validated['date_prise_fonction'] . '-01',
            ]);
        }

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Membre mis à jour avec succès.');
    }

    public function createSecretaire(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $agents = \App\Models\Agent::query()
            ->where('role', 'Secrétaire Assistante')
            ->with('user')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-generale.create-secretaire', compact('entite', 'agents'));
    }

    public function storeSecretaire(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
        ]);

        $agent = \App\Models\Agent::findOrFail($validated['agent_id']);
        $agent->entite_id = $entite->id;
        $agent->save();
        $this->accounts->ensureAccount($agent->fresh());

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) comme Secrétaire Assistante.');
    }

    public function destroySecretaire(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Secrétaire supprimé.');
    }

    public function createConseiller(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $agents = \App\Models\Agent::query()
            ->where('role', 'Conseiller DG')
            ->whereNull('entite_id')
            ->with('user')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        $postes = Poste::where('fonction', 'Conseiller DG')->orderBy('libelle')->pluck('libelle');

        return view('admin.direction-generale.create-conseiller', compact('entite', 'agents', 'postes'));
    }

    public function storeConseiller(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
            'poste'    => ['required', 'string', 'max:150'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
            'poste.required'    => 'La fonction occupée est obligatoire.',
        ]);

        $agent = \App\Models\Agent::findOrFail($validated['agent_id']);
        $agent->entite_id = $entite->id;
        $agent->poste     = $validated['poste'];

        // Rattacher à la Direction Générale pour les statistiques
        $dirGen = \App\Models\Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->first();
        if ($dirGen) {
            $agent->direction_id = $dirGen->id;
        }

        $agent->save();
        $this->accounts->ensureAccount($agent->fresh());

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) comme Conseiller DG.');
    }

    public function destroyConseiller(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Conseiller supprimé.');
    }
}
