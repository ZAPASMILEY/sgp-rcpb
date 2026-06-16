<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormationCrud;
use App\Models\Alerte;
use App\Models\Formation;
use App\Models\User;
use App\Traits\GererLayout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des formations accessible à tout utilisateur disposant de
 * la permission 'formations.assigner', quel que soit son rôle.
 *
 * Le layout est déterminé dynamiquement par le trait GererLayout.
 * Toute la logique CRUD est mutualisée dans HasFormationCrud.
 *
 * validationIndex() et valider() sont accessibles via la permission
 * 'formations.valider' — permet de déléguer la validation RH à un autre rôle.
 */
class FormationGererController extends Controller
{
    use GererLayout, HasFormationCrud;

    protected function routePrefix(): string
    {
        return 'gerer';
    }

    protected function formationLayout(): ?string
    {
        return $this->layout();
    }

    // ── Validation déléguée ───────────────────────────────────────────────────

    public function validationIndex(): View
    {
        $monAgentId = auth()->user()->agent_id;

        $enAttente = Formation::with('agent')
            ->where('statut', 'en_attente')
            ->orderBy('created_at')
            ->get();

        return view('gerer.formations.validation', [
            'enAttente'  => $enAttente,
            'monAgentId' => $monAgentId,
            'layout'     => $this->layout(),
        ]);
    }

    public function valider(Request $request, Formation $formation): RedirectResponse
    {
        // SÉCURITÉ : interdire de valider/refuser sa propre formation
        if ($formation->agent_id && $formation->agent_id === auth()->user()->agent_id) {
            return redirect()->route('gerer.formations.validation')
                ->with('error', 'Vous ne pouvez pas statuer sur votre propre formation.');
        }

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
                'PCA'                                                             => 'pca.formations.index',
                'DG'                                                              => 'dg.formations.index',
                'DGA'                                                             => 'dga.formations.index',
                'Directeur_Technique', 'Directeur_Direction', 'Directeur_Caisse' => 'directeur.formations.index',
                'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                    => 'chef.formations.index',
                'Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'       => 'subordonne.formations.index',
                default                                                           => 'personnel.formations.index',
            };

            if ($decision === 'validee') {
                Alerte::notifier(
                    $agentUser->id,
                    'Formation validée',
                    'Votre formation « ' . $formation->theme . ' » a été validée.',
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

        return redirect()->route('gerer.formations.validation')->with('status', $msg);
    }
}
