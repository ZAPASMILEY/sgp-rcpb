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
            ->paginate(12)
            ->withQueryString();

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
            ->paginate(12)
            ->withQueryString();

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
            ->paginate(12)
            ->withQueryString();

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
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.agents_index', [
            'agents' => $agents,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
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

        return view('admin.delegations_techniques.show', [
            'delegation' => $delegationTechnique,
            'caisses'    => $caisses,
            'agents'     => $agents,
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
            'directeur_agent_id'   => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'  => ['nullable', 'integer', 'exists:agents,id'],
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

        DelegationTechnique::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Delegation technique creee avec succes.');
    }

    public function storeCaisse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'ville_id'                => ['nullable', 'exists:villes,id'],
            'nom'                     => ['required', 'string', 'max:255'],
            'annee_ouverture'         => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'quartier'                => ['nullable', 'string', 'max:255'],
            'secretariat_telephone'   => ['nullable', 'string', 'max:30'],
            'directeur_agent_id'      => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        Caisse::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.show', $validated['delegation_technique_id'])
            ->with('status', 'Caisse creee avec succes.');
    }

    public function storeDelegationAgent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'service_id'              => ['nullable', 'exists:services,id'],
            'prenom'                  => ['required', 'string', 'max:255'],
            'nom'                     => ['required', 'string', 'max:255'],
            'sexe'                    => ['required', 'in:Masculin,Feminin'],
            'fonction'                => ['required', 'string', 'max:255'],
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
            'chef_agent_id'           => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        Service::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Service cree avec succes.');
    }

    public function editDelegation(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->load(['villes', 'directeur', 'secretaire']);

        return view('admin.delegations_techniques.edit', [
            'delegationTechnique' => $delegationTechnique,
            'directeurs'          => Agent::query()->where('fonction', 'Directeur Technique')->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires'         => Agent::query()->where('fonction', 'Secrétaire Technique')->orderBy('nom')->orderBy('prenom')->get(),
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
        return view('admin.directions.create', [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'directeurs'  => Agent::query()->where('fonction', 'Directeur de Direction')->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom']),
            'secretaires' => Agent::query()->where('fonction', 'Secrétaire de Direction')->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom']),
        ]);
    }

    public function show(Direction $direction): View
    {
        $direction->load(['entite', 'directeur', 'secretaire']);

        // Récupérer les évaluations validées et les dernières
        $evaluations = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->where('evaluable_id', $direction->id)
            ->where('statut', 'valide')
            ->latest()
            ->get();

        // Récupérer les objectifs assignés à cette direction
        $objectifs = Objectif::query()
            ->where('assignable_type', Direction::class)
            ->where('assignable_id', $direction->id)
            ->latest()
            ->get();

        return view('admin.directions.show', [
            'direction'   => $direction,
            'evaluations' => $evaluations,
            'objectifs'   => $objectifs,
        ]);
    }

    public function edit(Direction $direction): View
    {
        return view('admin.directions.edit', [
            'direction' => $direction,
            'agents'    => Agent::query()->orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'fonction']),
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

        $validated = $request->validate([
            'nom'                 => ['required', 'string', 'max:255', Rule::unique('directions')->where('entite_id', $entite->id)],
            'directeur_agent_id'  => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $validated['entite_id'] = $entite->id;
        Direction::query()->create($validated);

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
