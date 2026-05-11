<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
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
                ->with('delegationTechnique')
                ->when($search !== '', function (EloquentBuilder $query) use ($search): void {
                    $query->where(function (EloquentBuilder $subQuery) use ($search): void {
                        $subQuery
                            ->where('nom', 'like', "%{$search}%")
                            ->orWhere('secretariat_telephone', 'like', "%{$search}%")
                            ->orWhereHas('directeur', function (EloquentBuilder $dq) use ($search): void {
                                $dq->where('nom', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('delegationTechnique', function (EloquentBuilder $delegationQuery) use ($search): void {
                                $delegationQuery
                                    ->where('region', 'like', "%{$search}%")
                                    ->orWhere('ville', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->paginate(12)
                ->withQueryString(),
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
        return view('admin.caisses.show', [
            'caisse' => $caisse->load('delegationTechnique'),
        ]);
    }

    public function directionsIndex(Caisse $caisse): View
    {
        return view('admin.caisses.directions', [
            'caisse' => $caisse->load('delegationTechnique'),
            'caisseDirections' => collect(),
        ]);
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
            ->where('fonction', 'Directeur de Caisse')
            ->where(function (EloquentBuilder $q) use ($caisse): void {
                $q->whereNull('caisse_id');
                if ($caisse?->directeur_agent_id) {
                    $q->orWhere('id', $caisse->directeur_agent_id);
                }
            });

        $secretaireQuery = Agent::query()
            ->where('fonction', 'Secrétaire de Caisse')
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
            'secretariat_telephone'   => ['nullable', 'string', 'max:30'],
            'directeur_agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['required', 'integer', 'exists:agents,id'],
        ], [
            'directeur_agent_id.required'  => 'Le directeur est obligatoire pour créer une Caisse.',
            'secretaire_agent_id.required' => 'Le secrétaire est obligatoire pour créer une Caisse.',
        ]);
    }
}
