<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirecteurDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = Auth::user();
        $ctx   = DirecteurEntity::resolveOrFail($user);
        $annee = (int) $request->query('annee', now()->year);

        // ── Évaluations REÇUES par le directeur ──────────────────────────────
        $evalsRecBase = fn () => Evaluation::where(function ($q) use ($ctx) {
                $q->where('evaluable_type', $ctx->modelClass)
                  ->where('evaluable_id', $ctx->getId())
                  ->where('evaluable_role', 'manager');
            })->orWhere(function ($q) use ($user) {
                $q->where('evaluable_type', User::class)
                  ->where('evaluable_id', $user->id);
            })->whereYear('date_debut', $annee);

        $evalsRecStats = [
            'total'     => $evalsRecBase()->count(),
            'soumis'    => $evalsRecBase()->where('statut', 'soumis')->count(),
            'valide'    => $evalsRecBase()->where('statut', 'valide')->count(),
            'refuse'    => $evalsRecBase()->where('statut', 'refuse')->count(),
            'brouillon' => $evalsRecBase()->where('statut', 'brouillon')->count(),
        ];

        // ── Fiches d'objectifs REÇUES par le directeur ───────────────────────
        $fichesRecBase = fn () => FicheObjectif::where(function ($q) use ($ctx) {
                $q->where('assignable_type', $ctx->modelClass)
                  ->where('assignable_id', $ctx->getId());
            })->orWhere(function ($q) use ($user) {
                $q->where('assignable_type', User::class)
                  ->where('assignable_id', $user->id);
            })->whereYear('date', $annee);

        $fichesRecStats = [
            'total'      => $fichesRecBase()->count(),
            'acceptees'  => $fichesRecBase()->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesRecBase()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $fichesRecBase()->where('statut', 'refusee')->count(),
        ];
        $tauxAvancement = round($fichesRecBase()->avg('avancement_percentage') ?? 0, 1);

        // ── Évaluations DONNÉES par le directeur (à l'équipe) ────────────────
        $evalsGivBase = fn () => Evaluation::where('evaluateur_id', $user->id)
            ->whereYear('date_debut', $annee);

        $evalsGivStats = [
            'total'     => $evalsGivBase()->count(),
            'brouillon' => $evalsGivBase()->where('statut', 'brouillon')->count(),
            'soumis'    => $evalsGivBase()->where('statut', 'soumis')->count(),
            'valide'    => $evalsGivBase()->where('statut', 'valide')->count(),
        ];

        // Note moyenne de l'équipe
        $noteMoyenneEquipe = round(
            Evaluation::where('evaluateur_id', $user->id)->where('statut', 'valide')->whereYear('date_debut', $annee)->avg('note_finale') ?? 0,
            2
        );

        // ── Fiches récentes reçues ───────────────────────────────────────────
        $fichesRecentes = FicheObjectif::where(function ($q) use ($ctx) {
                $q->where('assignable_type', $ctx->modelClass)
                  ->where('assignable_id', $ctx->getId());
            })->orWhere(function ($q) use ($user) {
                $q->where('assignable_type', User::class)
                  ->where('assignable_id', $user->id);
            })
            ->whereYear('date', $annee)
            ->latest('date')
            ->take(6)
            ->get();

        // ── Vue équipe (services + chefs) ────────────────────────────────────
        $servicesWithAgents = $ctx->getServicesWithAgents();
        $servicesOverview   = $servicesWithAgents->take(5)->map(function (Service $service) use ($user) {
            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->whereIn('statut', ['soumis', 'valide'])
                ->orderByDesc('date_debut')
                ->first();
            return [
                'service'      => $service,
                'eval'         => $latestEval,
                'agents_count' => $service->agents->count(),
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

        $direction         = $ctx->entity;
        $anneesDisponibles = range(now()->year - 2, now()->year + 1);

        // ── Agents sans évaluation validée pour l'année ouverte ──────────────
        $openAnnee      = Annee::currentOpen();
        $agentsSansEval = 0;
        $allAgentIds    = $ctx->getServicesWithAgents()->flatMap(fn ($s) => $s->agents)->pluck('id')->unique();
        $totalAgents    = $allAgentIds->count();
        $agentsEvalues  = 0;
        if ($openAnnee) {
            $agentsSansEval = Agent::whereIn('id', $allAgentIds)
                ->whereDoesntHave('evaluations', function ($q) use ($openAnnee) {
                    $q->where('statut', 'valide')->where('annee_id', $openAnnee->id);
                })->count();
            $agentsEvalues = $totalAgents - $agentsSansEval;
        }

        return view('directeur.dashboard', compact(
            'user',
            'direction',
            'ctx',
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
            'servicesOverview',
            'openAnnee',
            'agentsSansEval',
            'totalAgents',
            'agentsEvalues',
        ));
    }
}
