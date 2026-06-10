<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormationCrud;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur formations RH.
 * Toute la logique CRUD est mutualisée dans HasFormationCrud.
 *
 * valider() gère le workflow d'approbation / refus des formations
 * soumises par les agents eux-mêmes (statut en_attente → validee / refusee).
 */
class RhFormationController extends Controller
{
    use HasFormationCrud;

    protected function routePrefix(): string
    {
        return 'rh';
    }

    // ── Validation RH des formations soumises par les agents ──────────────────

    public function valider(Request $request, Formation $formation): RedirectResponse
    {
        $request->validate([
            'decision'    => ['required', 'in:validee,refusee'],
            'motif_refus' => ['required_if:decision,refusee', 'nullable', 'string', 'max:1000'],
        ], [
            'motif_refus.required_if' => 'Le motif du refus est obligatoire.',
        ]);

        $decision = $request->input('decision');
        $formation->statut      = $decision;
        $formation->motif_refus = $decision === 'refusee' ? $request->input('motif_refus') : null;
        $formation->save();

        // Notifier l'agent
        $agentUser = $formation->agent
            ? User::where('agent_id', $formation->agent->id)->first()
            : null;

        if ($agentUser) {
            $routeName = match ($agentUser->role) {
                'PCA'                                                                => 'pca.formations.index',
                'DG'                                                                 => 'dg.formations.index',
                'DGA'                                                                => 'dga.formations.index',
                'Directeur_Technique', 'Directeur_Direction', 'Directeur_Caisse'    => 'directeur.formations.index',
                'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                       => 'chef.formations.index',
                'Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'          => 'subordonne.formations.index',
                default                                                              => 'personnel.formations.index',
            };

            if ($decision === 'validee') {
                Alerte::notifier(
                    $agentUser->id,
                    'Formation validée',
                    'Votre formation « ' . $formation->theme . ' » a été validée par le RH.',
                    'moyenne',
                    route($routeName)
                );
            } else {
                Alerte::notifier(
                    $agentUser->id,
                    'Formation refusée',
                    'Votre formation « ' . $formation->theme . ' » a été refusée. Motif : ' . $formation->motif_refus,
                    'haute',
                    route($routeName)
                );
            }
        }

        $msg = $decision === 'validee'
            ? 'Formation validée et ajoutée au dossier de l\'agent.'
            : 'Formation refusée. L\'agent a été notifié.';

        return redirect()->route('rh.formations.index')->with('status', $msg);
    }

    // ── API JSON — formations validées d'un agent (formulaires d'évaluation) ──

    public function pourAgent(Agent $agent): JsonResponse
    {
        $formations = Formation::where('agent_id', $agent->id)
            ->where('statut', 'validee')
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(fn ($f) => [
                'periode' => $f->date_debut->translatedFormat('M Y')
                    . ' – '
                    . ($f->date_fin ? $f->date_fin->translatedFormat('M Y') : 'en cours'),
                'libelle' => $f->theme,
                'domaine' => $f->domaine_label,
            ]);

        return response()->json($formations);
    }
}
