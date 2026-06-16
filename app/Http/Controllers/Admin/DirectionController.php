<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DirectionController extends Controller
{
    public function directeursIndex(Request $request): View
    {
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();

        $directeurs = Direction::query()
            ->with(['directeur', 'entite'])
            ->latest()
            ->get();

        return view('admin.delegations_techniques.directeurs_index', [
            'directeurs' => $directeurs,
            'delegations' => $delegations,
            'activeDelegationId' => 0,
            'selectedDelegation' => null,
            'delegationServices' => new Collection(),
        ]);
    }

    public function servicesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $search = trim((string) $request->query('search', ''));
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $services = Service::query()
            ->with('direction.entite')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->where('delegation_technique_id', $delegationId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhereHas('chef', function ($chefQuery) use ($search): void {
                            $chefQuery
                                ->where('nom', 'like', "%{$search}%")
                                ->orWhere('prenom', 'like', "%{$search}%");
                        })
                        ->orWhereHas('direction', function ($directionQuery) use ($search): void {
                            $directionQuery->where('nom', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('admin.delegations_techniques.services_index', [
            'services' => $services,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
            'search' => $search,
        ]);
    }

    public function secretairesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $secretaires = Direction::query()
            ->with(['entite', 'secretaire'])
            ->whereNotNull('secretaire_agent_id')
            ->latest()
            ->get();

        return view('admin.delegations_techniques.secretaires_index', [
            'secretaires' => $secretaires,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
    }

    public function agentsIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $agents = Agent::query()
            ->with(['service.direction.entite', 'delegationTechnique'])
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->where('delegation_technique_id', $delegationId);
            })
            ->latest()
            ->get();

        return view('admin.delegations_techniques.agents_index', [
            'agents' => $agents,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
    }

   public function delegationsIndex(): View
    {
        $delegations = DelegationTechnique::query()
            ->withCount(['caisses', 'agents', 'villes'])
            ->orderBy('region')
            ->get();

        $stats = [
            'delegations' => $delegations->count(),
            'caisses'     => $delegations->sum('caisses_count'),
            'agents'      => $delegations->sum('agents_count'),
            'services'    => \App\Models\Service::whereNotNull('delegation_technique_id')->count(),
        ];

        return view('admin.delegations_techniques.index', array_merge(compact('delegations', 'stats'), [
            'directeurs'              => Agent::where('role', 'Directeur Technique')->whereDoesntHave('directedDelegation')->orderBy('nom')->get(),
            'secretaires'             => Agent::where('role', 'Secrétaire Technique')->whereDoesntHave('secretariedDelegation')->orderBy('nom')->get(),
            'directeurs_caisse'       => Agent::where('role', 'Directeur de Caisse')->whereDoesntHave('directedCaisse')->orderBy('nom')->get(),
            'secretaires_caisse'      => Agent::where('role', 'Secrétaire de Caisse')->whereDoesntHave('secretariedCaisse')->orderBy('nom')->get(),
            'totalDirecteurs'         => Agent::where('role', 'Directeur Technique')->count(),
            'totalSecretaires'        => Agent::where('role', 'Secrétaire Technique')->count(),
            'totalDirecteursCaisse'   => Agent::where('role', 'Directeur de Caisse')->count(),
            'totalSecretairesCaisse'  => Agent::where('role', 'Secrétaire de Caisse')->count(),
        ]));
    }

    public function index(): View
    {
        $entite = Entite::with(['pca', 'dg', 'dga'])->latest()->first();

        $directions = Direction::query()
            ->where('nom', '!=', 'Direction Générale')
            ->with(['directeur', 'secretaire'])
            ->withCount(['services', 'agents'])
            ->when($entite, fn ($q) => $q->where('entite_id', $entite->id))
            ->orderBy('nom')
            ->get();

        $stats = [
            'directions' => $directions->count(),
            'agents'     => $directions->sum('agents_count'),
            'services'   => $directions->sum('services_count'),
        ];

        return view('admin.directions.index', compact('entite', 'directions', 'stats'));
    }

    public function showDelegation(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->loadCount(['caisses', 'agents']);
        $delegationTechnique->load('villes');

        $caisses = $delegationTechnique->caisses()->with('ville')->orderBy('nom')->get();
        $agents  = $delegationTechnique->agents()->orderBy('nom')->get();

        $directeurs  = Agent::where('role', 'Directeur de Caisse')->whereDoesntHave('directedCaisse')->orderBy('nom')->orderBy('prenom')->get();
        $secretaires = Agent::where('role', 'Secrétaire de Caisse')->whereDoesntHave('secretariedCaisse')->orderBy('nom')->orderBy('prenom')->get();

        return view('admin.delegations_techniques.show', [
            'delegation'       => $delegationTechnique,
            'caisses'          => $caisses,
            'agents'           => $agents,
            'directeurs'       => $directeurs,
            'secretaires'      => $secretaires,
            'totalDirecteurs'  => Agent::where('role', 'Directeur de Caisse')->count(),
            'totalSecretaires' => Agent::where('role', 'Secrétaire de Caisse')->count(),
        ]);
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        if (DelegationTechnique::query()->count() >= 3) {
            return redirect()
                ->route('admin.delegations-techniques.index')
                ->with('status', 'Maximum 3 delegations techniques configurees.');
        }

        $validated = $request->validate([
            'region'               => ['required', 'string', 'max:255'],
            'ville'                => ['required', 'string', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
            'directeur_agent_id'   => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'  => ['required', 'integer', 'exists:agents,id'],
        ], [
            'directeur_agent_id.required'  => 'Le directeur est obligatoire pour créer une Délégation Technique.',
            'secretaire_agent_id.required' => 'Le secrétaire est obligatoire pour créer une Délégation Technique.',
        ]);

        $alreadyExists = DelegationTechnique::query()
            ->where('region', $validated['region'])
            ->where('ville', $validated['ville'])
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->route('admin.delegations-techniques.index')
                ->with('status', 'Cette delegation existe deja.');
        }

        $entite = Entite::query()->latest()->first();
        if ($entite) {
            $validated['entite_id'] = $entite->id;
        }

        $dt = DelegationTechnique::query()->create($validated);

        Agent::findOrFail($validated['directeur_agent_id'])->update(['poste' => 'Directeur Technique de ' . $dt->ville]);
        Agent::findOrFail($validated['secretaire_agent_id'])->update(['poste' => 'Secrétaire Technique de ' . $dt->ville]);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Delegation technique creee avec succes.');
    }

    public function createCaisse(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->load(['directeur', 'villes']);

        return view('admin.delegations_techniques.create_caisse', [
            'delegation'       => $delegationTechnique,
            'directeurs'       => Agent::where('role', 'Directeur de Caisse')->whereDoesntHave('directedCaisse')->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires'      => Agent::where('role', 'Secrétaire de Caisse')->whereDoesntHave('secretariedCaisse')->orderBy('nom')->orderBy('prenom')->get(),
            'totalDirecteurs'  => Agent::where('role', 'Directeur de Caisse')->count(),
            'totalSecretaires' => Agent::where('role', 'Secrétaire de Caisse')->count(),
        ]);
    }

    public function storeCaisse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'ville_id'                => ['required', 'exists:villes,id'],
            'nom'                     => ['required', 'string', 'max:255', 'unique:caisses,nom'],
            'annee_ouverture'         => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'quartier'                => ['nullable', 'string', 'max:255'],
            'secretariat_telephone'   => ['required', 'string', 'max:30'],
            'directeur_agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
        ], [
            'directeur_agent_id.required' => 'Le directeur est obligatoire pour créer une Caisse.',
        ]);

        $caisse = Caisse::query()->create($validated);

        Agent::findOrFail($validated['directeur_agent_id'])->update([
            'caisse_id'               => $caisse->id,
            'delegation_technique_id' => $validated['delegation_technique_id'],
            'poste'                   => 'Directeur de ' . $caisse->nom,
        ]);

        if (!empty($validated['secretaire_agent_id'])) {
            Agent::findOrFail($validated['secretaire_agent_id'])->update([
                'caisse_id'               => $caisse->id,
                'delegation_technique_id' => $validated['delegation_technique_id'],
                'poste'                   => 'Secrétaire de ' . $caisse->nom,
            ]);
        }

        return redirect()
            ->route('admin.delegations-techniques.show', $validated['delegation_technique_id'])
            ->with('status', 'Caisse créée avec succès.');
    }

    public function storeDelegationAgent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'service_id'              => ['nullable', 'exists:services,id'],
            'prenom'                  => ['required', 'string', 'max:255'],
            'nom'                     => ['required', 'string', 'max:255'],
            'sexe'                    => ['required', 'in:Masculin,Feminin'],
            'role'                    => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'max:255'],
            'numero_telephone'        => ['nullable', 'string', 'max:30'],
            'date_debut_fonction'     => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        Agent::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.show', $validated['delegation_technique_id'])
            ->with('status', 'Agent ajoute avec succes.');
    }

    public function storeDelegationService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'nom'                     => ['required', 'string', 'max:255'],
            'chef_agent_id'           => ['required', 'integer', 'exists:agents,id'],
        ], [
            'chef_agent_id.required' => 'Le chef de service est obligatoire pour créer un Service.',
        ]);

        $service = Service::query()->create($validated);

        Agent::findOrFail($validated['chef_agent_id'])->update(['poste' => 'Chef du Service ' . $service->nom]);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Service cree avec succes.');
    }

    public function editVilles(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->load('villes');

        return view('admin.delegations_techniques.edit_villes', [
            'delegation' => $delegationTechnique,
        ]);
    }

    public function updateVilles(Request $request, DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $villesData = $request->input('villes', []);

        foreach ($villesData as $villeItem) {
            if (empty($villeItem['nom'])) continue;

            $existsElsewhere = Ville::where('nom', $villeItem['nom'])
                ->where('delegation_technique_id', '!=', $delegationTechnique->id)
                ->when(!empty($villeItem['id']), fn ($q) => $q->where('id', '!=', $villeItem['id']))
                ->exists();

            if ($existsElsewhere) {
                return redirect()->back()->withInput()->withErrors([
                    'villes' => "La ville \"{$villeItem['nom']}\" est déjà couverte par une autre délégation.",
                ]);
            }
        }

        $existingIds = [];
        foreach ($villesData as $villeItem) {
            if (empty($villeItem['nom'])) continue;

            if (!empty($villeItem['id'])) {
                $ville = Ville::find($villeItem['id']);
                if ($ville && $ville->delegation_technique_id === $delegationTechnique->id) {
                    $ville->update(['nom' => $villeItem['nom']]);
                    $existingIds[] = $ville->id;
                }
            } else {
                $new = $delegationTechnique->villes()->create(['nom' => $villeItem['nom']]);
                $existingIds[] = $new->id;
            }
        }
        $delegationTechnique->villes()->whereNotIn('id', $existingIds)->delete();

        return redirect()
            ->route('admin.delegations-techniques.villes.edit', $delegationTechnique)
            ->with('status', 'Villes mises à jour avec succès.');
    }

    public function editDelegation(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->load(['villes', 'directeur', 'secretaire']);

        $prisDirecteurIds  = DelegationTechnique::where('id', '!=', $delegationTechnique->id)
            ->whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $prisSecretaireIds = DelegationTechnique::where('id', '!=', $delegationTechnique->id)
            ->whereNotNull('secretaire_agent_id')->pluck('secretaire_agent_id');

        $totalDirecteurs  = Agent::where('role', 'Directeur Technique')->count();
        $totalSecretaires = Agent::where('role', 'Secrétaire Technique')->count();

        return view('admin.delegations_techniques.edit', [
            'delegationTechnique' => $delegationTechnique,
            'totalDirecteurs'     => $totalDirecteurs,
            'totalSecretaires'    => $totalSecretaires,
            'directeurs'  => Agent::query()
                ->where(function ($q) use ($prisDirecteurIds, $delegationTechnique) {
                    $q->where('role', 'Directeur Technique')->whereNotIn('id', $prisDirecteurIds);
                    if ($delegationTechnique->directeur_agent_id) {
                        $q->orWhere('id', $delegationTechnique->directeur_agent_id);
                    }
                })
                ->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires' => Agent::query()
                ->where(function ($q) use ($prisSecretaireIds, $delegationTechnique) {
                    $q->where('role', 'Secrétaire Technique')->whereNotIn('id', $prisSecretaireIds);
                    if ($delegationTechnique->secretaire_agent_id) {
                        $q->orWhere('id', $delegationTechnique->secretaire_agent_id);
                    }
                })
                ->orderBy('nom')->orderBy('prenom')->get(),
        ]);
    }

    public function updateDelegation(Request $request, DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $validated = $this->validateDelegation($request, $delegationTechnique);

        // Extract villes before updating delegation
        $villesData = $validated['villes'] ?? [];
        unset($validated['villes']);

        // Check uniqueness of ville names across other delegations
        foreach ($villesData as $villeItem) {
            $existsElsewhere = Ville::where('nom', $villeItem['nom'])
                ->where('delegation_technique_id', '!=', $delegationTechnique->id)
                ->when(!empty($villeItem['id']), fn ($q) => $q->where('id', '!=', $villeItem['id']))
                ->exists();

            if ($existsElsewhere) {
                return redirect()->back()->withInput()->withErrors([
                    'villes' => "La ville \"{$villeItem['nom']}\" est déjà couverte par une autre délégation.",
                ]);
            }
        }

        $delegationTechnique->update($validated);

        // Sync villes
        $existingIds = [];
        foreach ($villesData as $villeItem) {
            if (!empty($villeItem['id'])) {
                $ville = Ville::find($villeItem['id']);
                if ($ville && $ville->delegation_technique_id === $delegationTechnique->id) {
                    $ville->update(['nom' => $villeItem['nom']]);
                    $existingIds[] = $ville->id;
                }
            } else {
                $new = $delegationTechnique->villes()->create(['nom' => $villeItem['nom']]);
                $existingIds[] = $new->id;
            }
        }
        $delegationTechnique->villes()->whereNotIn('id', $existingIds)->delete();

        return redirect()
            ->route('admin.delegations-techniques.show', $delegationTechnique)
            ->with('status', 'Delegation technique mise a jour avec succes.');
    }

    public function destroyDelegation(DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $delegationTechnique->delete();

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Delegation technique supprimee avec succes.');
    }

    public function create(): View
    {
        // IDs déjà affectés comme directeur ou secrétaire dans une direction existante
        $prisDirecteurIds  = Direction::whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $prisSecretaireIds = Direction::whereNotNull('secretaire_agent_id')->pluck('secretaire_agent_id');

        $totalDirecteurs  = Agent::where('role', 'Directeur de Direction')->count();
        $totalSecretaires = Agent::where('role', 'Secrétaire de Direction')->count();

        return view('admin.directions.create', [
            'delegations'     => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'directeurs'      => Agent::query()->where('role', 'Directeur de Direction')->whereNotIn('id', $prisDirecteurIds)->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role']),
            'secretaires'     => Agent::query()->where('role', 'Secrétaire de Direction')->whereNotIn('id', $prisSecretaireIds)->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role']),
            'totalDirecteurs'  => $totalDirecteurs,
            'totalSecretaires' => $totalSecretaires,
        ]);
    }

    public function show(Direction $direction, Request $request): View
    {
        $direction->load(['entite', 'directeur', 'secretaire', 'services']);

        // ── Services de la direction (pour le filtre) ─────────────────────
        $services    = $direction->services()->orderBy('nom')->get(['id', 'nom']);
        $serviceId   = $request->query('service_id'); // null = tous les services

        // ── Agents travaillant dans cette direction ────────────────────────
        // Un agent peut être rattaché directement à la direction (direction_id)
        // ou à l'un de ses services (service_id → service.direction_id).
        $serviceIds = $services->pluck('id');

        // Exclure le directeur et la secrétaire : ils sont déjà affichés dans les cards en haut
        $exclus = array_values(array_filter([
            $direction->directeur_agent_id,
            $direction->secretaire_agent_id,
        ]));

        $agents = Agent::query()
            ->where(function ($q) use ($direction, $serviceIds) {
                $q->where('direction_id', $direction->id)
                  ->orWhereIn('service_id', $serviceIds);
            })
            ->when($exclus, fn ($q) => $q->whereNotIn('id', $exclus))
            ->whereNotNull('service_id')
            // Filtre additionnel si un service est sélectionné
            ->when($serviceId, fn ($q) => $q->where('service_id', $serviceId))
            ->with('service')           // évite N+1 pour afficher le service de chaque agent
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15, ['id', 'nom', 'prenom', 'poste', 'email', 'service_id', 'direction_id'])
            ->withQueryString();

        return view('admin.directions.show', [
            'direction'       => $direction,
            'services'        => $services,
            'agents'          => $agents,
            'selectedService' => $serviceId,
        ]);
    }

    public function edit(Direction $direction): View
    {
        // IDs déjà pris dans UNE AUTRE direction (on exclut la direction en cours d'édition)
        $prisDirecteurIds  = Direction::where('id', '!=', $direction->id)
            ->whereNotNull('directeur_agent_id')
            ->pluck('directeur_agent_id');

        $prisSecretaireIds = Direction::where('id', '!=', $direction->id)
            ->whereNotNull('secretaire_agent_id')
            ->pluck('secretaire_agent_id');

        // Directeurs disponibles : uniquement "Directeur de Direction" + pas pris dans une autre direction.
        // Le directeur actuellement en poste est toujours inclus pour qu'il reste sélectionnable.
        $directeurs = Agent::query()
            ->where(function ($q) use ($prisDirecteurIds, $direction) {
                $q->where('role', 'Directeur de Direction')
                  ->whereNotIn('id', $prisDirecteurIds);
                if ($direction->directeur_agent_id) {
                    $q->orWhere('id', $direction->directeur_agent_id);
                }
            })
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'role']);

        // Secrétaires disponibles : uniquement "Secrétaire de Direction" + pas prises ailleurs.
        $secretaires = Agent::query()
            ->where(function ($q) use ($prisSecretaireIds, $direction) {
                $q->where('role', 'Secrétaire de Direction')
                  ->whereNotIn('id', $prisSecretaireIds);
                if ($direction->secretaire_agent_id) {
                    $q->orWhere('id', $direction->secretaire_agent_id);
                }
            })
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'role']);

        return view('admin.directions.edit', [
            'direction'  => $direction,
            'directeurs' => $directeurs,
            'secretaires' => $secretaires,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entite = Entite::query()->latest()->first();
        if (! $entite) {
            return redirect()
                ->route('admin.entites.index')
                ->with('status', 'Configurez d abord la Faitiere avant de creer une Direction.');
        }

        // La Direction Générale Adjointe a son propre formulaire dédié
        if (strtolower(trim($request->input('nom', ''))) === 'direction générale adjointe') {
            return redirect()
                ->route('admin.direction-dga.configurer')
                ->with('status', 'Utilisez ce formulaire dédié pour configurer la Direction Générale Adjointe.');
        }

        $validated = $request->validate([
            'nom'                 => ['required', 'string', 'max:255', Rule::unique('directions')->where('entite_id', $entite->id)],
            'directeur_agent_id'  => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ], [
            'directeur_agent_id.required' => 'Le directeur est obligatoire pour créer une Direction.',
        ]);

        $validated['entite_id'] = $entite->id;
        $direction = Direction::query()->create($validated);

        Agent::findOrFail($validated['directeur_agent_id'])->update(['poste' => 'Directeur de ' . $direction->nom]);
        if (!empty($validated['secretaire_agent_id'])) {
            Agent::findOrFail($validated['secretaire_agent_id'])->update(['poste' => 'Secrétaire de ' . $direction->nom]);
        }

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Direction creee avec succes.');
    }

    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $validated = $request->validate([
            'nom'                 => ['required', 'string', 'max:255'],
            'directeur_agent_id'  => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $direction->update($validated);

        if (!empty($validated['directeur_agent_id'])) {
            Agent::findOrFail($validated['directeur_agent_id'])->update(['poste' => 'Directeur de ' . $direction->nom]);
        }
        if (!empty($validated['secretaire_agent_id'])) {
            Agent::findOrFail($validated['secretaire_agent_id'])->update(['poste' => 'Secrétaire de ' . $direction->nom]);
        }

        return redirect()
            ->route('admin.directions.show', $direction)
            ->with('status', 'Direction mise a jour avec succes.');
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->delete();

        return redirect()
            ->back()
            ->with('status', 'Direction supprimee avec succes.');
    }

    /**
     * @return array{selectedDelegation: ?DelegationTechnique, delegationServices: Collection<int, Service>}
     */
    private function delegationContext(int $delegationId): array
    {
        if ($delegationId <= 0) {
            return [
                'selectedDelegation' => null,
                'delegationServices' => new Collection(),
            ];
        }

        $selectedDelegation = DelegationTechnique::query()->find($delegationId);

        if (! $selectedDelegation) {
            return [
                'selectedDelegation' => null,
                'delegationServices' => new Collection(),
            ];
        }

        return [
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $selectedDelegation->directServices()
                ->orderBy('nom')
                ->get(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validateDelegation(Request $request, ?DelegationTechnique $delegationTechnique = null): array
    {
        return $request->validate([
            'region'               => ['required', 'string', 'max:255'],
            'ville'                => ['required', 'string', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
            'directeur_agent_id'   => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'  => ['nullable', 'integer', 'exists:agents,id'],
            'villes'               => ['nullable', 'array'],
            'villes.*.id'          => ['nullable', 'integer'],
            'villes.*.nom'         => ['required_with:villes', 'string', 'max:255'],
        ]);
    }
}
