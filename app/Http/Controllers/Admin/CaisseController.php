<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
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
                ->with('superviseur.delegationTechnique')
                ->when($search !== '', function (EloquentBuilder $query) use ($search): void {
                    $query->where(function (EloquentBuilder $subQuery) use ($search): void {
                        $subQuery
                            ->where('nom', 'like', "%{$search}%")
                            ->orWhere('directeur_nom', 'like', "%{$search}%")
                            ->orWhere('directeur_email', 'like', "%{$search}%")
                            ->orWhere('directeur_telephone', 'like', "%{$search}%")
                            ->orWhere('secretariat_telephone', 'like', "%{$search}%")
                            ->orWhereHas('superviseur', function (EloquentBuilder $directionQuery) use ($search): void {
                                $directionQuery
                                    ->where('directeur_prenom', 'like', "%{$search}%")
                                    ->orWhere('directeur_nom', 'like', "%{$search}%")
                                    ->orWhereHas('delegationTechnique', function (EloquentBuilder $delegationQuery) use ($search): void {
                                        $delegationQuery
                                            ->where('region', 'like', "%{$search}%")
                                            ->orWhere('ville', 'like', "%{$search}%");
                                    });
                            });
                    });
                })
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.caisses.create', [
            'delegations' => DelegationTechnique::query()
                ->orderBy('region')
                ->orderBy('ville')
                ->get(),
            'directions' => Direction::query()
                ->with('delegationTechnique')
                ->orderBy('directeur_nom')
                ->orderBy('directeur_prenom')
                ->get(),
        ]);
    }

    public function show(Caisse $caisse): View
    {
        return view('admin.caisses.show', [
            'caisse' => $caisse->load('superviseur.delegationTechnique'),
        ]);
    }

    public function directionsIndex(Caisse $caisse): View
    {
        return view('admin.caisses.directions', [
            'caisse' => $caisse->load('superviseur.delegationTechnique'),
            'caisseDirections' => collect(),
        ]);
    }

    public function servicesIndex(Caisse $caisse): View
    {
        return view('admin.caisses.services', [
            'caisse' => $caisse->load('superviseur.delegationTechnique'),
            'services' => collect(),
        ]);
    }

    public function edit(Caisse $caisse): View
    {
        return view('admin.caisses.edit', [
            'caisse' => $caisse->load('superviseur.delegationTechnique'),
            'delegations' => DelegationTechnique::query()
                ->orderBy('region')
                ->orderBy('ville')
                ->get(),
            'directions' => Direction::query()
                ->with('delegationTechnique')
                ->orderBy('directeur_nom')
                ->orderBy('directeur_prenom')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCaisse($request);

        $payload = $validated;
        unset($payload['delegation_technique_id']);

        Caisse::query()->create($payload);

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse creee avec succes.');
    }

    public function update(Request $request, Caisse $caisse): RedirectResponse
    {
        $validated = $this->validateCaisse($request, $caisse);

        $payload = $validated;
        unset($payload['delegation_technique_id']);

        $caisse->update($payload);

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

    private function validateCaisse(Request $request, ?Caisse $caisse = null): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'directeur_nom' => ['required', 'string', 'max:255'],
            'directeur_email' => [
                'required',
                'email',
                'max:255',
                $caisse
                    ? Rule::unique('caisses', 'directeur_email')->ignore($caisse->id)
                    : Rule::unique('caisses', 'directeur_email'),
            ],
            'directeur_telephone' => ['required', 'string', 'max:30'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
            'superviseur_direction_id' => [
                'required',
                'integer',
                Rule::exists('directions', 'id')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                }),
            ],
        ]);
    }
}
