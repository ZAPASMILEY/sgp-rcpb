<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
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
                            ->orWhere('directeur_nom', 'like', "%{$search}%")
                            ->orWhere('directeur_email', 'like', "%{$search}%")
                            ->orWhere('directeur_telephone', 'like', "%{$search}%")
                            ->orWhere('secretariat_telephone', 'like', "%{$search}%")
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
        return view('admin.caisses.edit', [
            'caisse' => $caisse->load('delegationTechnique'),
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

    private function validateCaisse(Request $request, ?Caisse $caisse = null): array
    {
        return $request->validate([
            'delegation_technique_id'   => ['required', 'integer', 'exists:delegation_techniques,id'],
            'nom'                       => [
                'required',
                'string',
                'max:255',
                $caisse
                    ? Rule::unique('caisses', 'nom')->ignore($caisse->id)
                    : Rule::unique('caisses', 'nom'),
            ],
            'annee_ouverture'           => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'quartier'                  => ['required', 'string', 'max:255'],
            'directeur_prenom'          => ['required', 'string', 'max:255'],
            'directeur_nom'             => ['required', 'string', 'max:255'],
            'directeur_sexe'            => ['required', 'in:Masculin,Feminin'],
            'directeur_email'           => [
                'required',
                'email',
                'max:255',
                $caisse
                    ? Rule::unique('caisses', 'directeur_email')->ignore($caisse->id)
                    : Rule::unique('caisses', 'directeur_email'),
            ],
            'directeur_telephone'       => [
                'required',
                'string',
                'max:30',
                $caisse
                    ? Rule::unique('caisses', 'directeur_telephone')->ignore($caisse->id)
                    : Rule::unique('caisses', 'directeur_telephone'),
            ],
            'directeur_date_debut_mois' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'secretariat_telephone'     => ['required', 'string', 'max:30'],
            'secretaire_prenom'         => ['required', 'string', 'max:255'],
            'secretaire_nom'            => ['required', 'string', 'max:255'],
            'secretaire_sexe'           => ['required', 'in:Masculin,Feminin'],
            'secretaire_email'          => [
                'required',
                'email',
                'max:255',
                $caisse
                    ? Rule::unique('caisses', 'secretaire_email')->ignore($caisse->id)
                    : Rule::unique('caisses', 'secretaire_email'),
            ],
            'secretaire_telephone'      => [
                'nullable',
                'string',
                'max:30',
                $caisse
                    ? Rule::unique('caisses', 'secretaire_telephone')->ignore($caisse->id)
                    : Rule::unique('caisses', 'secretaire_telephone'),
            ],
            'secretaire_date_debut_mois' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);
    }
}
