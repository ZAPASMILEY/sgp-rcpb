<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ChefEquipeController — Page "Mon équipe"
 *
 * Affiche les agents subordonnés du chef avec leur dernier statut d'évaluation
 * et les actions disponibles (évaluer, assigner des objectifs).
 *
 * Distinct de ChefMonEspaceController qui gère le dossier personnel
 * (évaluations reçues + objectifs reçus).
 */
class ChefEquipeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = Auth::user();
        $ctx   = ChefEntity::resolveOrFail($user);
        $agent = $ctx->agent;

        $sexe    = trim((string) $request->query('sexe', ''));
        $fonction = trim((string) $request->query('fonction', ''));

        // Agents subordonnés de la structure gérée par ce chef
        $agentsRaw = $ctx->getAgents();

        if ($sexe !== '') {
            $agentsRaw = $agentsRaw->filter(fn (Agent $a) => $a->sexe === $sexe);
        }
        if ($fonction !== '') {
            $agentsRaw = $agentsRaw->filter(fn (Agent $a) => $a->role === $fonction);
        }

        // Enrichissement : dernière évaluation créée PAR ce chef pour chaque agent
        $agentsOverview = $agentsRaw->map(function (Agent $a) use ($user) {
            $latestEval = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $a->id)
                ->where('evaluateur_id', $user->id)
                ->orderByDesc('date_debut')
                ->first();

            return [
                'agent'       => $a,
                'latest_eval' => $latestEval,
                'eval_statut' => $latestEval?->statut,
                'eval_note'   => $latestEval?->note_finale,
            ];
        });

        // Statistiques équipe
        $agentsEvalues = $agentsOverview->filter(fn ($r) => $r['latest_eval'] !== null)->count();
        $evaluationsCreees = Evaluation::where('evaluateur_id', $user->id)
            ->where('evaluable_type', Agent::class)
            ->count();

        $stats = [
            'total_agents'       => $agentsRaw->count(),
            'agents_evalues'     => $agentsEvalues,
            'evaluations_creees' => $evaluationsCreees,
        ];

        $fonctions = Agent::ROLES;

        return view('chef.equipe', compact(
            'user',
            'ctx',
            'agent',
            'agentsOverview',
            'stats',
            'sexe',
            'fonction',
            'fonctions',
        ));
    }
}
