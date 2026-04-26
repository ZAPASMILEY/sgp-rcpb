<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
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
        return view('admin.guichets.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom'           => ['required', 'string', 'max:255', Rule::unique('guichets', 'nom')],
            'agence_id'     => ['required', 'integer', 'exists:agences,id'],
            'chef_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        Guichet::query()->create($validated);

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet cree avec succes.');
    }

    public function edit(Guichet $guichet): View
    {
        return view('admin.guichets.edit', array_merge(
            $this->formData(),
            ['guichet' => $guichet->load(['agence', 'chef'])]
        ));
    }

    private function formData(): array
    {
        return [
            'agences' => Agence::query()->with('delegationTechnique')->orderBy('nom')->get(),
            'chefs'   => Agent::query()->where('fonction', 'Chef de Guichet')->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }

    public function update(Request $request, Guichet $guichet): RedirectResponse
    {
        $validated = $request->validate([
            'nom'           => ['required', 'string', 'max:255', Rule::unique('guichets', 'nom')->ignore($guichet->id)],
            'agence_id'     => ['required', 'integer', 'exists:agences,id'],
            'chef_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
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
