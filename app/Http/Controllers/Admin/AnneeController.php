<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Semestre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnneeController extends Controller
{
    public function index(): View
    {
        $annees = Annee::query()
            ->withCount(['evaluations', 'objectifs'])
            ->with('semestres')
            ->orderByDesc('annee')
            ->get();

        return view('admin.annees.index', compact('annees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $currentYear = now()->year;

        $request->validate([
            'annee' => [
                'required',
                'integer',
                'digits:4',
                "min:{$currentYear}",
                "max:{$currentYear}",
                Rule::unique('annees', 'annee'),
            ],
        ], [
            'annee.unique' => "L'année {$currentYear} existe déjà.",
            'annee.digits' => "L'année doit comporter 4 chiffres.",
            'annee.min'    => "Seule l'année en cours ({$currentYear}) peut être créée.",
            'annee.max'    => "Seule l'année en cours ({$currentYear}) peut être créée.",
        ]);

        $annee = Annee::query()->create([
            'annee'  => $request->integer('annee'),
            'statut' => 'ouvert',
        ]);

        // Créer automatiquement S1 et S2 (clôturés par défaut)
        $annee->createSemestresIfMissing();

        return redirect()->route('admin.annees.index')->with('status', "L'année {$request->input('annee')} a été créée avec ses deux semestres.");
    }

    public function toggleStatut(Annee $annee): RedirectResponse
    {
        $annee->statut = $annee->statut === 'ouvert' ? 'cloture' : 'ouvert';
        $annee->save();

        $label = $annee->statut === 'ouvert' ? 'ouverte' : 'clôturée';

        return redirect()->route('admin.annees.index')->with('status', "L'année {$annee->annee} est maintenant {$label}.");
    }

    public function toggleSemestre(Annee $annee, int $numero): RedirectResponse
    {
        if (! in_array($numero, [1, 2], true)) {
            return redirect()->route('admin.annees.index')->with('error', 'Numéro de semestre invalide.');
        }

        // S'assurer que les semestres existent (rétrocompatibilité)
        $annee->createSemestresIfMissing();

        $semestre = $annee->semestres()->where('numero', $numero)->firstOrFail();
        $semestre->statut = $semestre->statut === 'ouvert' ? 'cloture' : 'ouvert';
        $semestre->save();

        $label = $semestre->statut === 'ouvert' ? 'ouvert' : 'clôturé';

        return redirect()->route('admin.annees.index')
            ->with('status', "Semestre {$numero} de {$annee->annee} est maintenant {$label}.");
    }

    public function destroy(Annee $annee): RedirectResponse
    {
        if ($annee->evaluations()->exists() || $annee->objectifs()->exists()) {
            return redirect()->route('admin.annees.index')
                ->with('error', "Impossible de supprimer l'année {$annee->annee} : des évaluations ou objectifs y sont rattachés.");
        }

        $label = $annee->annee;
        $annee->delete();

        return redirect()->route('admin.annees.index')->with('status', "L'année {$label} a été supprimée.");
    }
}
