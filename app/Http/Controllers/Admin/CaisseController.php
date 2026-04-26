<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Guichet;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaisseController extends Controller
{
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
            $this->formData(),
            ['caisse' => $caisse->load(['delegationTechnique', 'directeur', 'secretaire'])]
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCaisse($request);

        Caisse::query()->create($validated);

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse creee avec succes.');
    }

    public function update(Request $request, Caisse $caisse): RedirectResponse
    {
        $validated = $this->validateCaisse($request, $caisse);

        $caisse->update($validated);

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse mise a jour avec succes.');
    }

    public function destroy(Request $request, Caisse $caisse): RedirectResponse
    {
        $caisse->delete();

        return redirect()
            ->back()
            ->with('status', 'Caisse supprimee avec succes.');
    }

    private function formData(): array
    {
        return [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
            'directions'  => Direction::query()->with('directeur')->orderBy('nom')->get(),
            'directeurs'  => Agent::query()->where('fonction', 'Directeur de Caisse')->orderBy('nom')->orderBy('prenom')->get(),
            'secretaires' => Agent::query()->where('fonction', 'Secrétaire de Caisse')->orderBy('nom')->orderBy('prenom')->get(),
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
            'directeur_agent_id'      => ['nullable', 'integer', 'exists:agents,id'],
            'secretaire_agent_id'     => ['nullable', 'integer', 'exists:agents,id'],
        ]);
    }
}
