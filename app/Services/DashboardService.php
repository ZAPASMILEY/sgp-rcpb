<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;

/**
 * DashboardService — calculs partagés entre tous les tableaux de bord.
 *
 * Élimine la duplication des closures de base répétées identiquement dans
 * PersonnelDashboard, ChefDashboard, DirecteurDashboard, PcaDashboard et DgaDashboard.
 */
class DashboardService
{
    // =========================================================================
    // Statistiques évaluations
    // =========================================================================

    /**
     * Compte les évaluations par statut à partir d'une closure de base query.
     * La closure doit retourner un nouveau Builder à chaque appel.
     *
     * @return array{total:int, brouillon:int, soumis:int, valide:int, refuse:int}
     */
    public function evalStats(\Closure $base): array
    {
        return [
            'total'     => $base()->count(),
            'brouillon' => $base()->where('statut', 'brouillon')->count(),
            'soumis'    => $base()->where('statut', 'soumis')->count(),
            'valide'    => $base()->where('statut', 'valide')->count(),
            'refuse'    => $base()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];
    }

    /**
     * Stats des évaluations DONNÉES par un évaluateur (brouillon→valide).
     *
     * @return array{total:int, brouillon:int, soumis:int, valide:int}
     */
    public function evalsGivStats(int $evaluateurId, int $annee, ?string $evaluableType = null): array
    {
        $base = fn () => Evaluation::where('evaluateur_id', $evaluateurId)
            ->whereYear('date_debut', $annee)
            ->when($evaluableType, fn ($q) => $q->where('evaluable_type', $evaluableType));

        return [
            'total'     => $base()->count(),
            'brouillon' => $base()->where('statut', 'brouillon')->count(),
            'soumis'    => $base()->where('statut', 'soumis')->count(),
            'valide'    => $base()->where('statut', 'valide')->count(),
        ];
    }

    /**
     * Note moyenne des évaluations validées données par un évaluateur.
     */
    public function noteMoyenneEquipe(int $evaluateurId, int $annee, ?string $evaluableType = null): float
    {
        return round(
            Evaluation::where('evaluateur_id', $evaluateurId)
                ->where('statut', 'valide')
                ->whereYear('date_debut', $annee)
                ->when($evaluableType, fn ($q) => $q->where('evaluable_type', $evaluableType))
                ->avg('note_finale') ?? 0,
            2
        );
    }

    // =========================================================================
    // Statistiques fiches d'objectifs
    // =========================================================================

    /**
     * Compte les fiches d'objectifs par statut.
     *
     * @return array{total:int, acceptees:int, en_attente:int, refusees:int}
     */
    public function ficheStats(\Closure $base): array
    {
        return [
            'total'      => $base()->count(),
            'acceptees'  => $base()->where('statut', 'acceptee')->count(),
            'en_attente' => $base()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $base()->where('statut', 'refusee')->count(),
        ];
    }

    /**
     * Taux d'avancement moyen des fiches (0–100).
     */
    public function tauxAvancement(\Closure $base): float
    {
        return round($base()->avg('avancement_percentage') ?? 0, 1);
    }

    // =========================================================================
    // Couverture évaluations (agents sans éval)
    // =========================================================================

    /**
     * Couverture des agents (evaluable_type = Agent::class).
     * Utilisé par Chef et Directeur.
     *
     * @return array{totalAgents:int, agentsSansEval:int, agentsEvalues:int}
     */
    public function agentsCoverage(\Illuminate\Support\Collection $agentIds, Annee $openAnnee): array
    {
        $total    = $agentIds->count();
        $sansEval = Agent::whereIn('id', $agentIds)
            ->whereDoesntHave('evaluations', fn ($q) =>
                $q->where('statut', 'valide')->where('annee_id', $openAnnee->id)
            )->count();

        return [
            'totalAgents'    => $total,
            'agentsSansEval' => $sansEval,
            'agentsEvalues'  => $total - $sansEval,
        ];
    }

    /**
     * Couverture des utilisateurs (evaluable_type = User::class).
     * Utilisé par DGA (qui évalue des Users, pas des Agents).
     *
     * @return array{totalAgents:int, agentsSansEval:int, agentsEvalues:int}
     */
    public function userCoverage(array $userIds, int $evaluateurId, int $anneeId): array
    {
        $total   = count($userIds);
        $evalues = Evaluation::where('evaluateur_id', $evaluateurId)
            ->where('evaluable_type', User::class)
            ->whereIn('evaluable_id', $userIds)
            ->where('statut', 'valide')
            ->where('annee_id', $anneeId)
            ->distinct('evaluable_id')
            ->count('evaluable_id');

        return [
            'totalAgents'    => $total,
            'agentsSansEval' => $total - $evalues,
            'agentsEvalues'  => $evalues,
        ];
    }

    // =========================================================================
    // Données ApexCharts
    // =========================================================================

    /**
     * Données du donut évaluations.
     * $inclureRefus = false pour Personnel (3 tranches), true pour les autres (4 tranches).
     */
    public function evalsDonut(array $stats, bool $inclureRefus = true): array
    {
        if ($inclureRefus) {
            return [
                'labels' => ['Validées', 'Soumises', 'Brouillon', 'Refusées'],
                'series' => [$stats['valide'], $stats['soumis'], $stats['brouillon'], $stats['refuse'] ?? 0],
                'colors' => ['#10b981', '#f59e0b', '#94a3b8', '#ef4444'],
            ];
        }

        return [
            'labels' => ['Validées', 'Soumises', 'Brouillon'],
            'series' => [$stats['valide'], $stats['soumis'], $stats['brouillon']],
            'colors' => ['#10b981', '#f59e0b', '#94a3b8'],
        ];
    }

    /**
     * Données du donut fiches d'objectifs.
     */
    public function fichesDonut(array $stats): array
    {
        return [
            'labels' => ['Acceptées', 'En attente', 'Refusées'],
            'series' => [$stats['acceptees'], $stats['en_attente'], $stats['refusees']],
            'colors' => ['#10b981', '#f59e0b', '#ef4444'],
        ];
    }

    // =========================================================================
    // Utilitaires
    // =========================================================================

    /**
     * Années disponibles pour le filtre (année-2 → année+1).
     */
    public function anneesDisponibles(): array
    {
        return range(now()->year - 2, now()->year + 1);
    }
}
