<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use Illuminate\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaisseController extends Controller
{
    public function index(): View
    {
        return view('admin.caisses.index', [
            'caisses' => Caisse::query()
                ->with('superviseur.delegationTechnique')
                ->latest()
                ->paginate(12),
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'directeur_nom' => ['required', 'string', 'max:255'],
            'directeur_email' => ['required', 'email', 'max:255', Rule::unique('caisses', 'directeur_email')],
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

        $payload = $validated;
        unset($payload['delegation_technique_id']);

        Caisse::query()->create($payload);

        return redirect()
            ->route('admin.caisses.index')
            ->with('status', 'Caisse creee avec succes.');
    }
}
