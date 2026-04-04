<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\DelegationTechnique;
use App\Models\Guichet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuichetController extends Controller
{
    public function index(): View
    {
        return view('admin.guichets.index', [
            'guichets' => Guichet::query()
                ->with('agence.delegationTechnique')
                ->latest()
                ->paginate(12),
            'stats' => [
                'total' => Guichet::count(),
                'par_delegation' => DelegationTechnique::query()
                    ->orderBy('region')
                    ->get()
                    ->map(function ($d) {
                        $d->guichets_count = Guichet::query()
                            ->whereHas('agence', fn ($q) => $q->where('delegation_technique_id', $d->id))
                            ->count();
                        return $d;
                    }),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.guichets.create', [
            'agences' => Agence::query()
                ->with('delegationTechnique')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('guichets', 'nom')],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_email' => ['required', 'email', 'max:255', Rule::unique('guichets', 'chef_email')],
            'chef_telephone' => ['required', 'string', 'max:30', Rule::unique('guichets', 'chef_telephone')],
            'agence_id' => ['required', 'integer', 'exists:agences,id'],
        ]);

        Guichet::query()->create($validated);

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet cree avec succes.');
    }

    public function edit(Guichet $guichet): View
    {
        return view('admin.guichets.edit', [
            'guichet' => $guichet->load('agence'),
            'agences' => Agence::query()->with('delegationTechnique')->orderBy('nom')->get(),
        ]);
    }

    public function update(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('guichets', 'nom')->ignore($guichet->id)],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_email' => ['required', 'email', 'max:255', Rule::unique('guichets', 'chef_email')->ignore($guichet->id)],
            'chef_telephone' => ['required', 'string', 'max:30', Rule::unique('guichets', 'chef_telephone')->ignore($guichet->id)],
            'agence_id' => ['required', 'integer', 'exists:agences,id'],
        ]);

        $guichet->update($validated);

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet modifie avec succes.');
    }

    public function destroy(Guichet $guichet): RedirectResponse
    {
        $guichet->delete();

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet supprime avec succes.');
    }
}
