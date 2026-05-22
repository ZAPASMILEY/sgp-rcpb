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
        // RÈGLE MÉTIER : Si on veut CLÔTURER l'année (passage de ouvert à cloture)
        if ($annee->statut === 'ouvert') {
            $hasOpenSemestres = $annee->semestres()->where('statut', 'ouvert')->exists();
            
            if ($hasOpenSemestres) {
                return redirect()->route('admin.annees.index')
                    ->with('error', "Impossible de clôturer l'année {$annee->annee} tant que tous ses semestres ne sont pas fermés.");
            }
        }

        // Changement de statut standard
        $annee->statut = $annee->statut === 'ouvert' ? 'cloture' : 'ouvert';
        $annee->save();

        $label = $annee->statut === 'ouvert' ? 'ouverte' : 'clôturée';

        return redirect()->route('admin.annees.index')
            ->with('status', "L'année {$annee->annee} est maintenant {$label}.");
    }
  public function toggleSemestre(Annee $annee, int $numero): RedirectResponse
    {
        // 1. Validation de sécurité sur le numéro du semestre
        if (! in_array($numero, [1, 2], true)) {
            return redirect()->route('admin.annees.index')->with('error', 'Numéro de semestre invalide.');
        }

        // 2. Sécurité d'initialisation (Garanti que S1 et S2 existent en base)
        $annee->createSemestresIfMissing();

        // 3. Récupération des deux instances de semestres pour les contrôles croisés
        $s1 = $annee->semestres()->where('numero', 1)->firstOrFail();
        $s2 = $annee->semestres()->where('numero', 2)->firstOrFail();

        // Définition du semestre cible sur lequel l'utilisateur a cliqué
        $semestreCible = ($numero === 1) ? $s1 : $s2;

        // 4. APPLICATION DES RÈGLES MÉTIERS SI ON VEUT OUVRIR LE SEMESTRE (passage de 'cloture' à 'ouvert')
        if ($semestreCible->statut === 'cloture') {
            
            // --- CAS DU SEMESTRE 1 ---
            if ($numero === 1) {
                // Règle A : Interdit d'ouvrir S1 si S2 est actif en ce moment
                if ($s2->statut === 'ouvert') {
                    return redirect()->route('admin.annees.index')
                        ->with('error', "Impossible d'ouvrir le Semestre 1 tant que le Semestre 2 est actif.");
                }
                
                // Règle B : Interdit d'ouvrir S1 si l'année a déjà des données (donc S2 a été entamé)
                $anneeAContenu = $annee->evaluations()->exists() || $annee->objectifs()->exists();
                
                if ($anneeAContenu) {
                    return redirect()->route('admin.annees.index')
                        ->with('error', "Le Semestre 1 est définitivement verrouillé car le Semestre 2 de cette année a déjà été entamé.");
                }
            }

            // --- CAS DU SEMESTRE 2 ---
            if ($numero === 2) {
                // Règle C : Interdit d'ouvrir S2 tant que S1 est encore ouvert
                if ($s1->statut === 'ouvert') {
                    return redirect()->route('admin.annees.index')
                        ->with('error', "Veuillez d'abord clôturer le Semestre 1 avant d'ouvrir le Semestre 2.");
                }
            }
        }

        // 5. TOGGLE DE STATUT STANDARD (Si aucune règle n'a bloqué l'exécution)
        $semestreCible->statut = $semestreCible->statut === 'ouvert' ? 'cloture' : 'ouvert';
        $semestreCible->save();

        $label = $semestreCible->statut === 'ouvert' ? 'ouvert' : 'clôturé';

        return redirect()->route('admin.annees.index')
            ->with('status', "Le Semestre {$numero} de l'année {$annee->annee} est maintenant {$label}.");
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
