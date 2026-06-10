<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RhReclamationController extends Controller
{
    public function index(Request $request): View
    {
        $search  = trim((string) $request->query('search', ''));
        $statut  = trim((string) $request->query('statut', ''));

        $query = Evaluation::query()
            ->where(function ($q) {
                $q->whereIn('statut', ['refuse', 'reclamation'])
                  ->orWhereNotNull('statut_reclamation');
            })
            ->with(['evaluable', 'evaluateur', 'identification']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('identification', fn ($i) => $i->where('nom_prenom', 'like', "%{$search}%"))
                  ->orWhereHas('evaluateur', fn ($e) => $e->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('evaluable', fn ($a) =>
                      $a->where('nom', 'like', "%{$search}%")->orWhere('prenom', 'like', "%{$search}%")
                  );
            });
        }

        if ($statut !== '') {
            if ($statut === 'sans_reclamation') {
                $query->whereNull('statut_reclamation');
            } else {
                $query->where('statut_reclamation', $statut);
            }
        }

        $evaluations = $query->latest()->get();
        $enAttente   = Evaluation::where(function ($q) {
            $q->whereIn('statut', ['refuse', 'reclamation'])->orWhereNotNull('statut_reclamation');
        })->where('statut_reclamation', 'en_attente')->count();

        return view('rh.reclamations.index', compact('evaluations', 'enAttente', 'search', 'statut'));
    }

    public function show(Evaluation $evaluation): View
    {
        $evaluation->load(['evaluable', 'evaluateur', 'identification']);
        return view('rh.reclamations.show', compact('evaluation'));
    }

    public function repondre(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $request->validate([
            'reponse' => ['required', 'in:maintenu,rouvert'],
        ]);

        $evaluation->statut_reclamation = $request->input('reponse');

        if ($request->input('reponse') === 'rouvert') {
            $evaluation->statut = 'a_reviser';
            // motif_refus conservé pour que l'évaluateur voie la raison du refus
        }

        $evaluation->save();

        if ($request->input('reponse') === 'rouvert') {
            // Notifier l'évaluateur que la fiche doit être révisée
            if ($evaluation->evaluateur_id) {
                $evaluateur = User::find($evaluation->evaluateur_id);
                $lien = match($evaluateur?->role) {
                    'PCA'                                                          => route('pca.evaluations.show', $evaluation),
                    'DG'                                                           => route('dg.sub-evaluations.show', $evaluation),
                    'DGA'                                                          => route('dga.sub-evaluations.show', $evaluation),
                    'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                 => route('chef.evaluations.show', $evaluation),
                    'Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse' => route('directeur.evaluations.show', $evaluation),
                    'Assistante_Dg'                                               => route('assistante.secretaire.evaluations.show', $evaluation),
                    default                                                        => null,
                };
                Alerte::notifier($evaluation->evaluateur_id, 'Une réclamation a été acceptée : votre évaluation doit être révisée.', '', 'haute', $lien);
            }
        } else {
            // Notifier l'évalué que son refus est maintenu
            if ($evaluation->evaluable_id) {
                $evaluable = User::find($evaluation->evaluable_id);
                $lien = match($evaluable?->role) {
                    'DG'                                                              => route('dg.evaluations.show', $evaluation),
                    'DGA'                                                             => route('dga.evaluations.show', $evaluation),
                    'Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse' => route('directeur.evaluations.show', $evaluation),
                    'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                    => route('chef.evaluations.show', $evaluation),
                    'Secretaire_Assistante', 'Secretaire_Agence'                     => route('personnel.evaluations.show', $evaluation),
                    default                                                           => null,
                };
                Alerte::notifier($evaluation->evaluable_id, 'Votre réclamation a été examinée : le refus est maintenu.', '', 'haute', $lien);
            }
        }

        $msg = $request->input('reponse') === 'maintenu'
            ? 'Le refus a été maintenu.'
            : 'L\'évaluation a été rouverte pour correction.';

        return redirect()->route('rh.reclamations.index')->with('status', $msg);
    }
}
