<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
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
            'nom' => ['required', 'string', 'max:255'],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_email' => ['required', 'email', 'max:255', Rule::unique('guichets', 'chef_email')],
            'chef_telephone' => ['required', 'string', 'max:30'],
            'agence_id' => ['required', 'integer', 'exists:agences,id'],
        ]);

        Guichet::query()->create($validated);

        return redirect()
            ->route('admin.guichets.index')
            ->with('status', 'Guichet cree avec succes.');
    }
}
