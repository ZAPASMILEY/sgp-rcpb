<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgObjectifController extends Controller
{
    public function create(): View
    {
        $user = Auth::user();

        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403, 'Acces reserve au Directeur General.');
        }

        $entite = $user->entite;
        $subordonnes = collect([
            'dga' => ($entite && ! empty($entite->dga_user_id))
                ? ['id' => $entite->dga_user_id, 'nom' => trim(($entite->dga_prenom ?? '').' '.($entite->dga_nom ?? ''))]
                : null,
            'secretaire' => ($entite && ! empty($entite->secretaire_user_id))
                ? ['id' => $entite->secretaire_user_id, 'nom' => trim(($entite->secretaire_prenom ?? '').' '.($entite->secretaire_nom ?? ''))]
                : null,
        ])->filter();

        return view('dg.objectifs.create', [
            'subordonnes' => $subordonnes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403, 'Acces reserve au Directeur General.');
        }

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer'],
            'objectifs' => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $fiche = FicheObjectif::create([
            'titre' => $validated['titre_fiche'],
            'annee' => now()->year,
            'assignable_type' => User::class,
            'assignable_id' => $validated['subordonne_id'],
            'date' => now()->toDateString(),
            'date_echeance' => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut' => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $objectifDesc) {
            $fiche->objectifs()->create([
                'description' => $objectifDesc,
            ]);
        }

        return redirect()
            ->route('dg.dashboard')
            ->with('status', "Fiche d'objectifs assignee avec succes.");
    }

    public function show($fiche): View
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($fiche);

        return view('dg.objectifs.show', compact('fiche'));
    }

    public function statut(Request $request, $fiche): RedirectResponse
    {
        $fiche = FicheObjectif::findOrFail($fiche);

        $request->validate([
            'statut' => ['required', 'in:acceptee,refusee'],
        ]);

        $fiche->statut = $request->statut;
        $fiche->save();

        return redirect()
            ->route('dg.objectifs.show', $fiche)
            ->with('status', 'Statut mis a jour.');
    }

    public function avancement(Request $request, $fiche): RedirectResponse
    {
        $fiche = FicheObjectif::findOrFail($fiche);

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (((int) $request->avancement_percentage) % 5 !== 0) {
            return redirect()
                ->route('dg.objectifs.show', $fiche)
                ->with('status', "L'avancement doit etre un multiple de 5.");
        }

        $fiche->avancement_percentage = $request->avancement_percentage;
        $fiche->save();

        return redirect()
            ->route('dg.objectifs.show', $fiche)
            ->with('status', 'Avancement mis a jour.');
    }
}
