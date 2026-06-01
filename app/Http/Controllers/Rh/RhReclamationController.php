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
    public function index(): View
    {
        $evaluations = Evaluation::query()
            ->where(function ($q) {
                $q->whereIn('statut', ['refuse', 'reclamation'])
                  ->orWhereNotNull('statut_reclamation');
            })
            ->with(['evaluable', 'evaluateur', 'identification'])
            ->latest()
            ->get();

        $enAttente = $evaluations->where('statut_reclamation', 'en_attente')->count();

        return view('rh.reclamations.index', compact('evaluations', 'enAttente'));
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
                    'Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse' => route('chef.evaluations.show', $evaluation),
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
