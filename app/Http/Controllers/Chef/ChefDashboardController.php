<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChefDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = Auth::user();
        $ctx   = ChefEntity::resolveOrFail($user);
        $agent = $ctx->agent;
        $annee = (int) $request->query('annee', now()->year);

        // ── Évaluations REÇUES par le chef ────────────────────────────────────
        $evalsRecBase = fn () => Evaluation::where(function ($q) use ($user, $agent) {
                $q->where('evaluable_type', User::class)->where('evaluable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('evaluable_type', Agent::class)->where('evaluable_id', $agent->id);
                    });
                }
            })->whereYear('date_debut', $annee);

        $evalsRecStats = [
            'total'     => $evalsRecBase()->count(),
            'soumis'    => $evalsRecBase()->where('statut', 'soumis')->count(),
            'valide'    => $evalsRecBase()->where('statut', 'valide')->count(),
            'refuse'    => $evalsRecBase()->whereIn('statut', ['refuse', 'reclamation'])->count(),
            'brouillon' => $evalsRecBase()->where('statut', 'brouillon')->count(),
        ];

        // ── Fiches d'objectifs REÇUES par le chef ────────────────────────────
        $fichesRecBase = fn () => FicheObjectif::where(function ($q) use ($user, $agent) {
                $q->where('assignable_type', User::class)->where('assignable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)->where('assignable_id', $agent->id);
                    });
                }
            })->whereYear('date', $annee);

        $fichesRecStats = [
            'total'      => $fichesRecBase()->count(),
            'acceptees'  => $fichesRecBase()->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesRecBase()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $fichesRecBase()->where('statut', 'refusee')->count(),
        ];
        $tauxAvancement = round($fichesRecBase()->avg('avancement_percentage') ?? 0, 1);

        // ── Évaluations DONNÉES par le chef à ses agents ─────────────────────
        $evalsGivBase = fn () => Evaluation::where('evaluateur_id', $user->id)
            ->where('evaluable_type', Agent::class)
            ->whereYear('date_debut', $annee);

        $evalsGivStats = [
            'total'     => $evalsGivBase()->count(),
            'brouillon' => $evalsGivBase()->where('statut', 'brouillon')->count(),
            'soumis'    => $evalsGivBase()->where('statut', 'soumis')->count(),
            'valide'    => $evalsGivBase()->where('statut', 'valide')->count(),
        ];

        $noteMoyenneEquipe = round(
            Evaluation::where('evaluateur_id', $user->id)->where('evaluable_type', Agent::class)
                ->where('statut', 'valide')->whereYear('date_debut', $annee)->avg('note_finale') ?? 0,
            2
        );

        // ── Fiches récentes reçues ───────────────────────────────────────────
        $fichesRecentes = FicheObjectif::where(function ($q) use ($user, $agent) {
                $q->where('assignable_type', User::class)->where('assignable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)->where('assignable_id', $agent->id);
                    });
                }
            })
            ->whereYear('date', $annee)
            ->latest('date')
            ->take(6)
            ->get();

        // ── Vue équipe (agents avec dernier statut d'éval) ───────────────────
        $agentsRaw      = $ctx->getAgents();
        $agentsOverview = $agentsRaw->take(5)->map(function (Agent $a) use ($user) {
            $latestEval = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $a->id)
                ->where('evaluateur_id', $user->id)
                ->orderByDesc('date_debut')
                ->first();
            return [
                'agent'       => $a,
                'eval_statut' => $latestEval?->statut,
                'eval_note'   => $latestEval?->note_finale,
            ];
        });

        // ── Données pour ApexCharts ──────────────────────────────────────────
        $evalsDonut = [
            'labels' => ['Validées', 'Soumises', 'Brouillon', 'Refusées'],
            'series' => [$evalsRecStats['valide'], $evalsRecStats['soumis'], $evalsRecStats['brouillon'], $evalsRecStats['refuse']],
            'colors' => ['#10b981', '#f59e0b', '#94a3b8', '#ef4444'],
        ];
        $fichesDonut = [
            'labels' => ['Acceptées', 'En attente', 'Refusées'],
            'series' => [$fichesRecStats['acceptees'], $fichesRecStats['en_attente'], $fichesRecStats['refusees']],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
        ];

        $anneesDisponibles = range(now()->year - 2, now()->year + 1);

        // ── Agents sans évaluation validée pour l'année ouverte ──────────────
        $openAnnee      = Annee::currentOpen();
        $agentsSansEval = 0;
        $totalAgents    = $ctx->getAgents()->count();
        $agentsEvalues  = 0;
        if ($openAnnee) {
            $agentIds = $ctx->getAgents()->pluck('id');
            $agentsSansEval = Agent::whereIn('id', $agentIds)
                ->whereDoesntHave('evaluations', function ($q) use ($openAnnee) {
                    $q->where('statut', 'valide')->where('annee_id', $openAnnee->id);
                })->count();
            $agentsEvalues = $totalAgents - $agentsSansEval;
        }

        return view('chef.dashboard', compact(
            'user',
            'ctx',
            'agent',
            'annee',
            'anneesDisponibles',
            'evalsRecStats',
            'fichesRecStats',
            'tauxAvancement',
            'evalsGivStats',
            'noteMoyenneEquipe',
            'evalsDonut',
            'fichesDonut',
            'fichesRecentes',
            'agentsOverview',
            'openAnnee',
            'agentsSansEval',
            'totalAgents',
            'agentsEvalues',
        ));
    }
}
