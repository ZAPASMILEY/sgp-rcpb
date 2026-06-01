<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Setting;
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
        $this->authorize('evaluations.voir-equipe');

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

        $evaluationsEnabled = Setting::featureEnabled('evaluations') && $user->can('evaluations.creer');
        $objectifsEnabled   = Setting::featureEnabled('objectifs')   && $user->can('objectifs.assigner');

        return view('chef.equipe', compact(
            'user',
            'ctx',
            'agent',
            'agentsOverview',
            'stats',
            'sexe',
            'fonction',
            'fonctions',
            'evaluationsEnabled',
            'objectifsEnabled',
        ));
    }

    public function showAgent(Request $request, Agent $agent): View
    {
        $this->authorize('evaluations.voir-equipe');

        $user = Auth::user();
        $ctx  = ChefEntity::resolveOrFail($user);

        if (! $ctx->agentOwnedBy($agent)) {
            abort(403, "Cet agent n'est pas sous votre responsabilité.");
        }

        $tab    = in_array($request->query('tab'), ['evaluations', 'objectifs']) ? $request->query('tab') : 'evaluations';
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        // ── Closures de base ────────────────────────────────────────────────
        $baseE = fn () => Evaluation::where('evaluable_type', Agent::class)
            ->where('evaluable_id', $agent->id)
            ->where('evaluateur_id', $user->id);

        $baseF = fn () => FicheObjectif::where('assignable_type', Agent::class)
            ->where('assignable_id', $agent->id);

        // ── Statistiques (globales, non filtrées) ────────────────────────────
        $stats = [
            'evaluations' => $baseE()->count(),
            'fiches'      => $baseF()->count(),
            'evalides'    => $baseE()->where('statut', 'valide')->count(),
            'facceptees'  => $baseF()->where('statut', 'acceptee')->count(),
        ];

        $evaluationsStats = [
            'total'     => $baseE()->count(),
            'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
            'soumis'    => $baseE()->where('statut', 'soumis')->count(),
            'valide'    => $baseE()->where('statut', 'valide')->count(),
        ];

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        // ── Évaluations paginées + filtrées ─────────────────────────────────
        $evalsQ = $baseE()->with('identification')->orderByDesc('date_debut');
        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }
        $evaluations = $evalsQ->paginate(10)->withQueryString();

        // ── Fiches paginées + filtrées ───────────────────────────────────────
        $fichesQ = $baseF()->withCount('objectifs')->with('annee')->orderByDesc('date');
        if ($search && $tab === 'objectifs') {
            $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")
                ->orWhereHas('annee', fn ($a) => $a->where('annee', 'like', "%{$search}%")));
        }
        if ($statut && $tab === 'objectifs') {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }
        $fiches = $fichesQ->paginate(10)->withQueryString();

        $filters = compact('tab', 'statut', 'search');

        $evaluationsEnabled = Setting::featureEnabled('evaluations') && $user->can('evaluations.creer');
        $objectifsEnabled   = Setting::featureEnabled('objectifs')   && $user->can('objectifs.assigner');

        return view('chef.agent.show', compact(
            'agent', 'ctx', 'tab',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'stats', 'filters',
            'evaluationsEnabled', 'objectifsEnabled'
        ));
    }
}
