<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Traits\GererLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteGererController extends Controller
{
    use GererLayout;

    public function index(Request $request): View
    {
        $alertes = Alerte::with('createur')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $layout = $this->layout();

        return view('gerer.alertes.index', compact('alertes', 'layout'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre'    => ['required', 'string', 'max:255'],
            'message'  => ['required', 'string'],
            'type'     => ['required', 'in:info,avertissement,critique,securite'],
            'priorite' => ['required', 'in:faible,normale,haute,critique'],
        ]);

        Alerte::create([
            'titre'      => $validated['titre'],
            'message'    => $validated['message'],
            'type'       => $validated['type'],
            'priorite'   => $validated['priorite'],
            'statut'     => 'active',
            'created_by' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('status', 'Alerte créée avec succès.');
    }

    public function destroy(Alerte $alerte): RedirectResponse
    {
        $alerte->delete();
        return back()->with('status', 'Alerte supprimée.');
    }

    public function updateStatut(Request $request, Alerte $alerte): RedirectResponse
    {
        $request->validate(['statut' => ['required', 'in:active,resolue,ignoree']]);
        $alerte->update(['statut' => $request->statut]);
        return back()->with('status', 'Statut mis à jour.');
    }
}
