<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Poste;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class PosteController extends Controller
{
    /** Fonctions qui ont des postes spécifiques configurables. */
    private const FONCTIONS_AVEC_POSTES = [
        'Agent'         => 'Agent simple',
        'Conseiller DG' => 'Conseiller DG',
    ];

    public function index(): View
    {
        $postes = Poste::orderBy('fonction')->orderBy('libelle')->get()->groupBy('fonction');

        return view('admin.postes.index', [
            'postes'             => $postes,
            'fonctionsDisponibles' => self::FONCTIONS_AVEC_POSTES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fonction' => ['required', 'string', Rule::in(array_keys(self::FONCTIONS_AVEC_POSTES))],
            'libelle'  => [
                'required', 'string', 'max:150',
                Rule::unique('postes')->where(fn ($q) => $q->where('fonction', $request->input('fonction'))),
            ],
        ], [
            'libelle.unique' => 'Ce poste existe déjà pour cette fonction.',
        ]);

        Poste::create($validated);

        return redirect()->route('admin.postes.index')
            ->with('status', 'Poste « '.$validated['libelle'].' » ajouté.');
    }

    public function destroy(Poste $poste): RedirectResponse
    {
        $libelle = $poste->libelle;
        $poste->delete();

        return redirect()->route('admin.postes.index')
            ->with('status', 'Poste « '.$libelle.' » supprimé.');
    }

    /** Retourne les postes d'une fonction (pour l'auto-complétion JS). */
    public function byFonction(string $fonction)
    {
        return response()->json(
            Poste::where('fonction', $fonction)->orderBy('libelle')->pluck('libelle')
        );
    }
}
