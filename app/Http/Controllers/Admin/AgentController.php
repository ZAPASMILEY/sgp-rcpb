<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Poste;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

   public function index(Request $request): View
{
    // 1. Récupération des filtres depuis la requête
    $role = $request->input('role'); 
    $search = $request->input('search');
    $affectation = $request->input('affectation');

    // 2. Construction de la requête pour récupérer les agents (La variable manquante !)
    $query = Agent::query()->orderBy('nom', 'asc')->orderBy('prenom', 'asc');

    // Filtrage par rôle si sélectionné
    if ($role) {
        $query->where('role', $role);
    }

    // Filtrage par recherche (Nom / Prénom)
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('prenom', 'like', "%{$search}%");
        });
    }

    // Exécution de la requête pour alimenter la variable $agents
    $agents = $query->get(); 

    // 3. Calcul des statistiques pour les badges et filtres
    $countsByRole = Agent::query()
        ->selectRaw('role, count(*) as total')
        ->groupBy('role')
        ->pluck('total', 'role');

    // Liste des colonnes de clés étrangères pour les affectations directes
    // Dans AgentController@index :
$directFks = ['entite_id', 'direction_id', 'delegation_technique_id', 'caisse_id', 'agence_id', 'guichet_id', 'service_id'];
    // Liste des relations inverses (si tu as défini des relations dans ton modèle Agent)
    $inverseRelations = ['pcaEntite', 'assistanteEntite', 'directeurDirection', 'secretaireDirection', 'directeurDelegation'];

    $totalAffectes = Agent::query()->where(function ($q) use ($directFks, $inverseRelations): void {
        foreach ($directFks as $col) {
            $q->orWhereNotNull($col);
        }
        foreach ($inverseRelations as $rel) {
            if (method_exists(Agent::class, $rel)) {
                $q->orWhereHas($rel);
            }
        }
    })->count();

    // 4. Envoi complet à la vue (Tout est défini !)
    return view('admin.agents.index', [
        'agents'        => $agents,
        'roleActive'    => $role, 
        'affectation'   => $affectation,
        'search'        => $search,
        'roles'         => Agent::ROLES,
        'countsByRole'  => $countsByRole,
        'totalAgents'   => Agent::count(),
        'totalAffectes' => $totalAffectes,
    ]);
}
    public function create(): View
    {
        return view('admin.agents.create', $this->formData());
    }

    public function show(Agent $agent): View
{
    return view('admin.agents.show', [
        'agent' => $agent->load([
            'entite', 
            'direction', 
            'service.direction.entite', 
            'service.delegationTechnique', 
            'service.caisse', 
            'delegationTechnique', 
            'caisse', 
            'agence', 
            'guichet', 
            'user'
        ]),
    ]);
}

    public function edit(Agent $agent): View
    {
        return view('admin.agents.edit', array_merge(
            $this->formData(),
            ['agent' => $agent]
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAgent($request);
        $validated['photo_path'] = $this->storeSelectedPhoto($request);

        DB::transaction(function () use ($validated): void {
            $agent = Agent::query()->create($validated);
            $this->accounts->ensureAccount($agent);
        });

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent créé avec succès. Compte de connexion généré (mot de passe : 11111111).');
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $this->validateAgent($request, $agent);

        $photo = $this->storeSelectedPhoto($request);
        if ($photo !== null) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = null;
        }

        DB::transaction(function () use ($agent, $validated): void {
            $agent->update($validated);
            $this->accounts->ensureAccount($agent->fresh());
        });

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Agent mis à jour avec succès.');
    }

    public function destroy(Request $request, Agent $agent): RedirectResponse
    {
        $this->deletePhoto($agent->photo_path);
        $agent->delete();

        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo !== '' && str_starts_with($redirectTo, (string) url('/'))) {
            return redirect()
                ->to($redirectTo)
                ->with('status', 'Agent supprime avec succes.');
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent supprime avec succes.');
    }

    /**
     * Crée (ou réactive) le compte de connexion d'un agent existant.
     * Mot de passe par défaut : 11111111.
     */
    public function activateAccount(Agent $agent): RedirectResponse
    {
        $this->accounts->ensureAccount($agent);

        return redirect()
            ->back()
            ->with('status', "Compte de {$agent->prenom} {$agent->nom} activé (mot de passe : 11111111).");
    }

    /**
     * Crée les comptes manquants pour tous les agents qui n'en ont pas.
     * Utilise une requête DB directe pour éviter les faux positifs liés aux scopes Eloquent.
     */
    public function syncAllAccounts(): RedirectResponse
    {
        // Requête directe sans scope pour trouver les agents sans aucun user (actif ou non)
        $agentsIds = DB::table('agents')
            ->whereNotIn('id', DB::table('users')->whereNotNull('agent_id')->pluck('agent_id'))
            ->pluck('id');

        $agents = Agent::whereIn('id', $agentsIds)->get();
        $count  = 0;

        foreach ($agents as $agent) {
            $this->accounts->ensureAccount($agent);
            $count++;
        }

        $message = $count > 0
            ? "{$count} compte(s) créé(s) avec succès. Mot de passe par défaut : 11111111."
            : 'Tous les agents ont déjà un compte.';

        return redirect()
            ->route('admin.agents.index')
            ->with('status', $message);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'postesByFonction' => Poste::byFonction(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAgent(Request $request, ?Agent $agent = null): array
    {
        $emailRule = ['required', 'email', 'max:191'];
        if ($agent === null) {
            $emailRule[] = Rule::unique('agents', 'email');
        } else {
            $emailRule[] = Rule::unique('agents', 'email')->ignore($agent->id);
        }

        $matriculeRule = ['required', 'string', 'max:50'];
        if ($agent === null) {
            $matriculeRule[] = Rule::unique('agents', 'matricule');
        } else {
            $matriculeRule[] = Rule::unique('agents', 'matricule')->ignore($agent->id);
        }

        return $request->validate([
            // Données personnelles
            'nom'              => ['required', 'string', 'max:100'],
            'prenom'           => ['required', 'string', 'max:100'],
            'sexe'             => ['required', 'in:homme,femme'],
            'email'            => $emailRule,
            'numero_telephone' => [
                'required',
                'string',
                'max:30',
                $agent
                    ? Rule::unique('agents', 'numero_telephone')->ignore($agent->id)
                    : Rule::unique('agents', 'numero_telephone'),
            ],
            'photo_import'     => ['nullable', 'image', 'max:3072'],
            'photo_camera'     => ['nullable', 'image', 'max:3072'],
            'remove_photo'     => ['nullable', 'boolean'],

            // Données professionnelles
            'matricule'           => $matriculeRule,
            'role'                => ['required', 'string', Rule::in(array_keys(Agent::ROLES))],
            'poste'               => [
                Rule::requiredIf(in_array($request->input('role'), ['Agent', 'Conseiller DG'], true)),
                'nullable', 'string', 'max:150',
            ],
            'date_debut_fonction' => ['required', 'date'],
        ], [
            'nom.required'              => 'Le nom est obligatoire.',
            'prenom.required'           => 'Le prénom est obligatoire.',
            'email.required'            => "L'email est obligatoire.",
            'email.email'               => "L'email n'est pas valide.",
            'email.unique'              => 'Cet email est déjà utilisé par un autre agent.',
            'numero_telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé par un autre agent.',
            'sexe.required'             => 'Le sexe est obligatoire.',
            'sexe.in'                   => 'Le sexe doit être "homme" ou "femme".',
            'matricule.required'        => 'Le matricule est obligatoire.',
            'matricule.unique'          => 'Ce matricule est déjà utilisé par un autre agent.',
            'role.required'              => 'Le rôle est obligatoire.',
            'role.in'                    => 'Le rôle sélectionné est invalide.',
            'poste.required'             => 'La fonction est obligatoire pour ce rôle.',
            'date_debut_fonction.required' => 'La date de prise de fonction est obligatoire.',
            'date_debut_fonction.date'     => 'La date de prise de fonction est invalide.',
            'photo_import.image'        => 'Le fichier doit être une image.',
            'photo_import.max'          => 'La photo ne doit pas dépasser 3 Mo.',
            'photo_camera.image'        => 'Le fichier doit être une image.',
            'photo_camera.max'          => 'La photo ne doit pas dépasser 3 Mo.',
        ]);
    }

    private function storeSelectedPhoto(Request $request): ?string
    {
        $photo = $this->selectedPhoto($request);

        if (! $photo instanceof UploadedFile) {
            return null;
        }

        return $photo->store('agents', 'public');
    }

    private function selectedPhoto(Request $request): ?UploadedFile
    {
        $photoCamera = $request->file('photo_camera');
        if ($photoCamera instanceof UploadedFile) {
            return $photoCamera;
        }

        $photoImport = $request->file('photo_import');
        if ($photoImport instanceof UploadedFile) {
            return $photoImport;
        }

        return null;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

}
