<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * PersonnelDashboardController — Tableau de bord de l'agent
 *
 * Vue d'ensemble avec :
 *   - Hero banner (identité + KPIs résumés)
 *   - Graphiques de répartition (évaluations + objectifs)
 *   - Évaluations récentes (5 dernières)
 *   - Fiches d'objectifs récentes (5 dernières)
 *
 * Pour la liste complète et filtrée, voir PersonnelMonEspaceController → personnel.mon-espace.
 */
class PersonnelDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = $request->user();
        $annee = (int) $request->input('annee', now()->year);

        $agent = $user->agent_id
            ? Agent::with(['service.direction.entite', 'agence'])->find($user->agent_id)
            : null;

        // ── Closure de base : évaluations reçues (filtrées par année) ────────
        $baseE = function () use ($user, $agent, $annee) {
            return Evaluation::where(function ($q) use ($user, $agent) {
                $q->where('evaluable_type', \App\Models\User::class)
                  ->where('evaluable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('evaluable_type', Agent::class)
                           ->where('evaluable_id', $agent->id);
                    });
                }
            })->whereYear('date_debut', $annee);
        };

        $evaluationsStats = [
            'total'     => $baseE()->count(),
            'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
            'soumis'    => $baseE()->where('statut', 'soumis')->count(),
            'valide'    => $baseE()->where('statut', 'valide')->count(),
        ];

        // 5 évaluations les plus récentes (pas de pagination sur le dashboard)
        $evaluationsRecentes = $baseE()
            ->with(['evaluateur', 'identification'])
            ->orderByDesc('date_debut')
            ->take(5)
            ->get();

        // ── Closure de base : fiches d'objectifs reçues (filtrées par année) ─
        $baseF = function () use ($user, $agent, $annee) {
            return FicheObjectif::where(function ($q) use ($user, $agent) {
                $q->where('assignable_type', \App\Models\User::class)
                  ->where('assignable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)
                           ->where('assignable_id', $agent->id);
                    });
                }
            })->whereYear('date', $annee);
        };

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $tauxAvancement = round($baseF()->avg('avancement_percentage') ?? 0, 1);

        // 5 fiches les plus récentes
        $fichesRecentes = $baseF()
            ->withCount('objectifs')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // Données pour les graphiques ApexCharts
        $evalsDonut = [
            'labels' => ['Validées', 'Soumises', 'Brouillon'],
            'series' => [$evaluationsStats['valide'], $evaluationsStats['soumis'], $evaluationsStats['brouillon']],
            'colors' => ['#10b981', '#f59e0b', '#94a3b8'],
        ];
        $fichesDonut = [
            'labels' => ['Acceptées', 'En attente', 'Refusées'],
            'series' => [$fichesStats['acceptees'], $fichesStats['en_attente'], $fichesStats['refusees']],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
        ];

        $anneesDisponibles = range(now()->year - 2, now()->year + 1);

        return view('personnel.dashboard', compact(
            'user',
            'agent',
            'annee',
            'anneesDisponibles',
            'evaluationsStats',
            'evaluationsRecentes',
            'fichesStats',
            'fichesRecentes',
            'tauxAvancement',
            'evalsDonut',
            'fichesDonut',
        ));
    }
}
