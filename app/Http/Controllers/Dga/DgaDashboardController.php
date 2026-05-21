<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Guichet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DgaDashboardController extends Controller
{
    /** Rôles de la faîtière — exclus des stats de notes réseau. */
    private const FAITIERE_ROLES = ['DG', 'DGA', 'PCA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'DGA') {
            abort(403, 'Accès réservé au DGA.');
        }

        $annee   = (int) $request->query('annee', now()->year);
        $statut  = trim((string) $request->get('statut', ''));
        $search  = trim((string) $request->get('search', ''));
        $anneeId = (int) $request->get('annee_id', 0);

        // ── Réseau : comptages ───────────────────────────────────────────────
        $reseauStats = [
            'delegations' => DelegationTechnique::count(),
            'caisses'     => Caisse::count(),
            'agences'     => Agence::count(),
            'guichets'    => Guichet::count(),
        ];

        // ── Note moyenne réseau (hors faîtière) ──────────────────────────────
        $faitiereAgentIds = User::whereIn('role', self::FAITIERE_ROLES)
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->all();

        $noteReseau = Evaluation::where('evaluable_type', Agent::class)
            ->whereNotIn('evaluable_id', $faitiereAgentIds)
            ->where('statut', 'valide')
            ->whereNotNull('note_finale')
            ->avg('note_finale');

        // ── Évaluations données par le DGA (à ses subordonnés) ───────────────
        $baseSubEvals = fn () => Evaluation::where('evaluateur_id', $user->id);

        $subStats = [
            'total'       => $baseSubEvals()->count(),
            'brouillon'   => $baseSubEvals()->where('statut', 'brouillon')->count(),
            'soumis'      => $baseSubEvals()->where('statut', 'soumis')->count(),
            'valide'      => $baseSubEvals()->where('statut', 'valide')->count(),
        ];

        // ── Évaluations récentes données par le DGA ───────────────────────────
        $queryRecent = Evaluation::with(['identification', 'evaluateur'])
            ->where('evaluateur_id', $user->id)
            ->orderByDesc('updated_at');

        if ($statut) {
            $queryRecent->where('statut', $statut);
        }
        if ($anneeId) {
            $queryRecent->where('annee_id', $anneeId);
        }
        if ($search !== '') {
            $queryRecent->whereHas('identification', fn ($q) =>
                $q->where('nom_prenom', 'like', "%{$search}%")
                  ->orWhere('emploi', 'like', "%{$search}%")
            );
        }

        $evaluations = $queryRecent->paginate(15)->withQueryString();

        $annees = Annee::orderByDesc('annee')->get();

        // ── Meilleure / plus basse note des subordonnés du DGA ───────────────
        $topEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)
            ->where('statut', 'valide')
            ->orderByDesc('note_finale')
            ->first();

        $bottomEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)
            ->where('statut', 'valide')
            ->orderBy('note_finale')
            ->first();

        // ── Évaluations REÇUES par le DGA (de la part du DG) ────────────────
        $evalsRecBase = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->whereYear('date_debut', $annee);

        $evalsRecStats = [
            'total'     => $evalsRecBase()->count(),
            'soumis'    => $evalsRecBase()->where('statut', 'soumis')->count(),
            'valide'    => $evalsRecBase()->where('statut', 'valide')->count(),
            'refuse'    => $evalsRecBase()->where('statut', 'refuse')->count(),
            'brouillon' => $evalsRecBase()->where('statut', 'brouillon')->count(),
        ];

        // ── Fiches d'objectifs REÇUES par le DGA ─────────────────────────────
        $fichesRecBase = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->whereYear('date', $annee);

        $fichesRecStats = [
            'total'      => $fichesRecBase()->count(),
            'acceptees'  => $fichesRecBase()->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesRecBase()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $fichesRecBase()->where('statut', 'refusee')->count(),
        ];
        $tauxAvancement = round($fichesRecBase()->avg('avancement_percentage') ?? 0, 1);

        // ── Données pour ApexCharts ───────────────────────────────────────────
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
        $totalAgents    = Agent::personnel()->count();
        $agentsEvalues  = 0;
        if ($openAnnee) {
            $agentsSansEval = Agent::personnel()
                ->whereDoesntHave('evaluations', function ($q) use ($openAnnee) {
                    $q->where('statut', 'valide')->where('annee_id', $openAnnee->id);
                })->count();
            $agentsEvalues = $totalAgents - $agentsSansEval;
        }

        $filters = compact('statut', 'search', 'anneeId');

        return view('dga.dashboard', compact(
            'annee',
            'anneesDisponibles',
            'reseauStats',
            'noteReseau',
            'subStats',
            'evalsRecStats',
            'fichesRecStats',
            'tauxAvancement',
            'evalsDonut',
            'fichesDonut',
            'evaluations',
            'annees',
            'topEval',
            'bottomEval',
            'filters',
            'openAnnee',
            'agentsSansEval',
            'totalAgents',
            'agentsEvalues',
        ));
    }
}
