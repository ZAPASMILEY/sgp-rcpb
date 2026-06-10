<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Poste;
use App\Models\Service;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    /**
     * Liste des services d'une caisse
     */
    public function caisseServices($caisseId): View
    {
        $services = Service::where('caisse_id', $caisseId)->with('direction')->latest()->get();
        $caisse = \App\Models\Caisse::findOrFail($caisseId);
        return view('admin.services.caisse', compact('services', 'caisse'));
    }

    /**
     * Liste des services de la faitière uniquement
     */
    public function faitiereServices(): View
    {
        $entite = \App\Models\Entite::latest()->first();

        $directionIds = $entite
            ? Direction::where('entite_id', $entite->id)->pluck('id')
            : collect();

        $services = Service::whereIn('direction_id', $directionIds)
            ->whereNull('delegation_technique_id')
            ->whereNull('caisse_id')
            ->with(['direction', 'chef'])
            ->latest()
            ->get();

        return view('admin.services.faitiere', compact('services', 'entite'));
    }
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $directionId = (string) $request->query('direction_id', '');
        $source = (string) $request->query('source', '');
        $delegationId = (string) $request->query('delegation_id', '');
        $caisseId = (string) $request->query('caisse_id', '');

        $servicesQuery = Service::query()
            ->with(['direction.entite', 'chef'])
            ->when($source === 'faitiere', function ($query): void {
                $query->whereNotNull('direction_id')
                      ->whereNull('delegation_technique_id')
                      ->whereNull('caisse_id');
            })
            ->when($delegationId !== '', function ($query) use ($delegationId): void {
                $query->where('delegation_technique_id', $delegationId);
            })
            ->when($caisseId !== '', function ($query) use ($caisseId): void {
                $query->where('caisse_id', $caisseId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhereHas('chef', function ($chefQuery) use ($search): void {
                            $chefQuery->where('nom', 'like', "%{$search}%")
                                      ->orWhere('prenom', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('direction', function ($directionQuery) use ($search): void {
                            $directionQuery
                                ->where('nom', 'like', "%{$search}%")
                                ->orWhereHas('entite', function ($entiteQuery) use ($search): void {
                                    $entiteQuery->where('nom', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($directionId !== '', function ($query) use ($directionId): void {
                $query->where('direction_id', $directionId);
            })
            ->latest();

        return view('admin.services.index', [
            'services' => $servicesQuery->get(),
            'filters' => [
                'search' => $search,
                'direction_id' => $directionId,
                'source' => $source,
                'delegation_id' => $delegationId,
                'caisse_id' => $caisseId,
            ],
            'directions' => Direction::query()->where('nom', '!=', 'Direction Générale')->with('entite')
                ->orderBy('nom')->get(['id', 'nom', 'entite_id']),
            'stats' => [
                'total' => Service::count(),
                'par_delegation' => DelegationTechnique::query()
                    ->withCount(['directServices as services_count'])
                    ->orderBy('region')
                    ->get(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', $this->formData());
    }

    public function show(Service $service): View
    {
        return view('admin.services.show', [
            'service' => $service->load(['direction.entite', 'chef', 'agents']),
            'postes'  => Poste::where('fonction', 'Agent')->orderBy('libelle')->pluck('libelle'),
        ]);
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', array_merge(
            $this->formData(),
            ['service' => $service->load('chef')]
        ));
    }

    private function formData(): array
    {
        return [
            'faitiereDirections' => Direction::query()->where('nom', '!=', 'Direction Générale')->orderBy('nom')->get(['id', 'nom']),
            'delegations'        => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'caisses'            => Caisse::query()->with('delegationTechnique')->orderBy('nom')->get(['id', 'nom', 'delegation_technique_id']),
            'directions'         => Direction::query()->where('nom', '!=', 'Direction Générale')->with('entite')->orderBy('nom')->get(['id', 'nom', 'entite_id']),
            'chefs'              => Agent::query()->where('role', 'Chef de Service')->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateService($request);
        $service = Service::query()->create($validated);

        if (!empty($validated['chef_agent_id'])) {
            Agent::findOrFail($validated['chef_agent_id'])->update(['poste' => 'Chef du Service ' . $service->nom]);
        }

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service cree avec succes.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $this->validateService($request, $service);
        $service->update($validated);

        if (!empty($validated['chef_agent_id'])) {
            Agent::findOrFail($validated['chef_agent_id'])->update(['poste' => 'Chef du Service ' . $service->nom]);
        }

        return redirect()
            ->route('admin.services.show', $service)
            ->with('status', 'Service mis a jour avec succes.');
    }
    public function attachAgent(Request $request, Service $service): RedirectResponse
    {
        $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'poste'    => 'required|string|max:150',
        ], [
            'poste.required' => 'La fonction occupée est obligatoire.',
        ]);

        $agent = Agent::findOrFail($request->agent_id);

        if ($agent->service_id !== null) {
            return redirect()->back()->with('error', "Cet agent est déjà affecté à un autre service.");
        }

        $agent->update([
            'service_id'              => $service->id,
            'direction_id'            => $service->direction_id,
            'caisse_id'               => $service->caisse_id,
            'delegation_technique_id' => $service->delegation_technique_id,
            'poste'                   => $request->input('poste') ?: null,
        ]);
        $this->accounts->ensureAccount($agent->fresh());

        return redirect()->back()->with('status', "L'agent {$agent->nom} a été affecté avec succès.");
    }
public function affecterCaisse(Request $request)
{
    $request->validate([
        'service_id' => 'required|exists:services,id',
        'caisse_id'  => 'required|exists:caisses,id',
    ]);

    // On récupère le service sélectionné
    $service = Service::findOrFail($request->service_id);
    
    // On lui attribue directement l'ID de la caisse
    $service->caisse_id = $request->caisse_id;
    $service->save();

    // On redirige vers la page des services de cette caisse avec un message de succès
    return redirect()->route('admin.caisses.affecter-service', $request->caisse_id)
                     ->with('success', 'Le service a bien été rattaché à la caisse.');
}

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service supprime avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateService(Request $request, ?Service $service = null): array
    {
        // Infer parent_type if not submitted
        if (! $request->has('parent_type')) {
            if ($request->filled('caisse_id')) {
                $request->merge(['parent_type' => 'caisse']);
            } elseif ($request->filled('delegation_technique_id')) {
                $request->merge(['parent_type' => 'delegation']);
            } else {
                $request->merge(['parent_type' => 'faitiere']);
            }
        }

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                $service
                    ? Rule::unique('services', 'nom')->ignore($service->id)
                    : Rule::unique('services', 'nom'),
            ],
            'parent_type'             => ['required', 'in:faitiere,delegation,caisse'],
            'direction_id'            => ['required_if:parent_type,faitiere', 'nullable', 'integer', 'exists:directions,id'],
            'delegation_technique_id' => ['required_if:parent_type,delegation', 'nullable', 'integer', 'exists:delegation_techniques,id'],
            'caisse_id'               => ['required_if:parent_type,caisse', 'nullable', 'integer', 'exists:caisses,id'],
            'chef_agent_id'           => [$service ? 'nullable' : 'required', 'integer', 'exists:agents,id'],
        ], [
            'chef_agent_id.required' => 'Le chef de service est obligatoire pour créer un Service.',
        ]);

        $parentType = $validated['parent_type'];
        unset($validated['parent_type']);

        if ($parentType === 'faitiere') {
            $validated['delegation_technique_id'] = null;
            $validated['caisse_id'] = null;
        } elseif ($parentType === 'delegation') {
            $validated['direction_id'] = null;
            $validated['caisse_id'] = null;
        } else {
            $validated['direction_id'] = null;
            $validated['delegation_technique_id'] = null;
        }

        return $validated;
                
    }
}
