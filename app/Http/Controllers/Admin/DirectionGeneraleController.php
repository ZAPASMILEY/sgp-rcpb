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
                ->get();

            $conseillers = User::where('role', 'Conseillers_Dg')
                ->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))
                ->get();
        }

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

            $agentsDisponibles = Agent::where('direction_id', $direction->id)
                ->whereNull('service_id')
                ->orderBy('nom')->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'role']);
        }

        return view('admin.direction-generale.index', [
            'entite'            => $entite,
            'direction'         => $direction,
            'membres'           => $membres,
            'secretaires'       => $secretaires,
            'conseillers'       => $conseillers,
            'services'          => $services,
            'agentsDisponibles' => $agentsDisponibles,
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction, 404, 'Direction Générale introuvable.');

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
        ], ['nom.required' => 'Le nom du service est obligatoire.']);

        Service::create([
            'nom'          => $validated['nom'],
            'direction_id' => $direction->id,
        ]);

        return back()->with('status', 'Service « ' . $validated['nom'] . ' » créé avec succès.');
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

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], ['agent_id.required' => 'Sélectionnez un agent.']);

        $agent = Agent::findOrFail($validated['agent_id']);

        if ((int) $agent->direction_id !== $direction->id) {
            return back()->with('error', 'Cet agent ne fait pas partie de la Direction Générale.');
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
            'prenom'              => ['required', 'string', 'max:255'],
            'nom'                 => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'sexe'                => ['required', 'in:Homme,Femme,Autres'],
            'date_prise_fonction' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        // Mise à jour du compte utilisateur
        $user->update([
            'name'                => $validated['prenom'].' '.$validated['nom'],
            'email'               => $validated['email'],
            'sexe'                => $validated['sexe'],
            'date_prise_fonction' => $validated['date_prise_fonction'],
        ]);

        // Mise à jour des champs miroirs sur l'entite selon le rôle
        $entiteFields = match ($user->role) {
            'DG' => [
                'directrice_generale_prenom'              => $validated['prenom'],
                'directrice_generale_nom'                 => $validated['nom'],
                'directrice_generale_email'               => $validated['email'],
                'directrice_generale_sexe'                => $validated['sexe'],
                'directrice_generale_date_prise_fonction' => $validated['date_prise_fonction'],
            ],
            'DGA' => [
                'dga_prenom'              => $validated['prenom'],
                'dga_nom'                 => $validated['nom'],
                'dga_email'               => $validated['email'],
                'dga_sexe'                => $validated['sexe'],
                'dga_date_prise_fonction' => $validated['date_prise_fonction'],
            ],
            'Assistante_Dg' => [
                'assistante_dg_prenom'              => $validated['prenom'],
                'assistante_dg_nom'                 => $validated['nom'],
                'assistante_dg_email'               => $validated['email'],
                'assistante_dg_sexe'                => $validated['sexe'],
                'assistante_dg_date_prise_fonction'  => $validated['date_prise_fonction'],
            ],
            default => [],
        };

        if ($entiteFields) {
            $entite->update($entiteFields);
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
            ->whereRaw('LOWER(nom) LIKE ?', ['%direction g%n%rale%'])
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
