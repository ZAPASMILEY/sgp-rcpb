<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $ctx   = ChefEntity::resolveOrFail($user);
        $agent = $ctx->agent;

        $sexe        = trim((string) $request->query('sexe', ''));
        $fonction    = trim((string) $request->query('fonction', ''));
        $search      = trim((string) $request->query('search', ''));
        $statutEval  = trim((string) $request->query('statut_eval', ''));
        $statutFiche = trim((string) $request->query('statut_fiche', ''));
        $noteRange   = trim((string) $request->query('note', ''));

        // Agents subordonnés de la structure gérée par ce chef
        $agentsRaw = $ctx->getAgents();

        // ── Agents avec notifs non lues (pour le chef actuel) ───────────────
        $agentsWithUnread = $this->resolveAgentsWithUnreadNotifs($user->id, $agentsRaw);

        // ── Enrichissement complet (avant filtres affichage) ────────────────
        $agentsOverviewAll = $agentsRaw->map(function (Agent $a) use ($user, $agentsWithUnread) {
            $latestEval = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $a->id)
                ->where('evaluateur_id', $user->id)
                ->orderByDesc('date_debut')
                ->first();

            $attentionRequise = $latestEval !== null
                && $latestEval->statut === 'reclamation'
                && ! in_array($latestEval->statut_reclamation, ['maintenu', 'rouvert'], true);

            $ficheAcceptee  = FicheObjectif::where('assignable_type', Agent::class)
                ->where('assignable_id', $a->id)
                ->where('statut', 'acceptee')
                ->exists();
            $ficheBlocksNew = FicheObjectif::where('assignable_type', Agent::class)
                ->where('assignable_id', $a->id)
                ->whereNotIn('statut', ['refusee'])
                ->exists();
            $evalEnCours = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $a->id)
                ->where('evaluateur_id', $user->id)
                ->whereIn('statut', ['soumis', 'brouillon'])
                ->exists();
            $statutFicheVal = FicheObjectif::where('assignable_type', Agent::class)
                ->where('assignable_id', $a->id)
                ->orderByDesc('date')
                ->value('statut');

            return [
                'agent'             => $a,
                'latest_eval'       => $latestEval,
                'eval_statut'       => $latestEval?->statut,
                'eval_note'         => $latestEval?->note_finale,
                'attention'         => $attentionRequise,
                'attention_reason'  => $attentionRequise ? 'Réclamation active' : null,
                'has_unread_notif'   => isset($agentsWithUnread[$a->id]),
                'ficheBlocksNew'     => $ficheBlocksNew,
                'ficheAcceptee'      => $ficheAcceptee,
                'evaluationEnCours'  => $evalEnCours,
                'statut_fiche_val'   => $statutFicheVal,
            ];
        });

        // ── Filtres sur la collection enrichie ──────────────────────────────
        $agentsOverview = $agentsOverviewAll->filter(function (array $row) use (
            $sexe, $fonction, $search, $statutEval, $statutFiche, $noteRange
        ) {
            $ag = $row['agent'];

            if ($sexe !== '' && $ag->sexe !== $sexe) return false;
            if ($fonction !== '' && $ag->role !== $fonction) return false;

            if ($search !== '') {
                $fullName = strtolower(trim($ag->prenom . ' ' . $ag->nom));
                if (! str_contains($fullName, strtolower($search))) return false;
            }

            if ($statutEval !== '') {
                if ($statutEval === 'non_evalue' && $row['eval_statut'] !== null) return false;
                if ($statutEval !== 'non_evalue' && $row['eval_statut'] !== $statutEval) return false;
            }

            if ($statutFiche !== '') {
                if ($statutFiche === 'aucune' && $row['statut_fiche_val'] !== null) return false;
                if ($statutFiche === 'en_attente') {
                    if (! in_array($row['statut_fiche_val'], ['en_attente', 'brouillon', 'conteste'], true)) return false;
                } elseif ($statutFiche !== 'aucune' && $row['statut_fiche_val'] !== $statutFiche) return false;
            }

            if ($noteRange !== '') {
                $n = $row['eval_note'] !== null ? (float) $row['eval_note'] : null;
                if ($noteRange === 'non_note' && $n !== null) return false;
                if ($noteRange === 'excellent'    && ($n === null || $n < 8.5)) return false;
                if ($noteRange === 'bien'         && ($n === null || $n < 7 || $n >= 8.5)) return false;
                if ($noteRange === 'moyen'        && ($n === null || $n < 5 || $n >= 7)) return false;
                if ($noteRange === 'insuffisant'  && ($n === null || $n >= 5)) return false;
            }

            return true;
        });

        // Statistiques équipe (basées sur l'ensemble avant filtres)
        $agentsEvalues     = $agentsOverviewAll->filter(fn ($r) => $r['latest_eval'] !== null)->count();
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

        $hasFilters = $sexe || $fonction || $search || $statutEval || $statutFiche || $noteRange;

        return view('chef.equipe', compact(
            'user', 'ctx', 'agent',
            'agentsOverview',
            'stats',
            'sexe', 'fonction', 'search', 'statutEval', 'statutFiche', 'noteRange',
            'fonctions',
            'evaluationsEnabled', 'objectifsEnabled',
            'hasFilters',
        ));
    }

    /**
     * Retourne un tableau [agentId => true] pour les agents dont une alerte
     * non lue (destinataire = $userId) référence une évaluation de cet agent.
     */
    private function resolveAgentsWithUnreadNotifs(int $userId, Collection $agents): array
    {
        // Évaluations du chef pour ses agents : evalId → agentId
        $agentIds = $agents->pluck('id')->toArray();
        if (empty($agentIds)) {
            return [];
        }

        $evalMap = Evaluation::where('evaluateur_id', $userId)
            ->where('evaluable_type', Agent::class)
            ->whereIn('evaluable_id', $agentIds)
            ->pluck('evaluable_id', 'id'); // [evalId => agentId]

        if ($evalMap->isEmpty()) {
            return [];
        }

        // Alertes non lues du chef
        $unreadAlertes = Alerte::whereHas('destinataires', fn ($q) =>
            $q->where('user_id', $userId)->where('lu', false)
        )->whereNotNull('lien')->get(['id', 'lien']);

        $result = [];
        foreach ($unreadAlertes as $alerte) {
            foreach ($evalMap as $evalId => $agentId) {
                if (str_contains((string) $alerte->lien, "/{$evalId}")) {
                    $result[$agentId] = true;
                    break;
                }
            }
        }

        return $result;
    }

    public function showAgent(Request $request, Agent $agent): View
    {
        $this->authorize('evaluations.voir-equipe');

        /** @var \App\Models\User $user */
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
        $evaluations = $evalsQ->get();

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
        $fiches = $fichesQ->get();

        $filters = compact('tab', 'statut', 'search');

        $evaluationsEnabled = Setting::featureEnabled('evaluations') && $user->can('evaluations.creer');
        $objectifsEnabled   = Setting::featureEnabled('objectifs')   && $user->can('objectifs.assigner');

        // Fiche d'objectifs pour l'année en cours
        $ficheBloquante    = null;
        $ficheAnneeEnCours = null;
        $ficheAvancee      = null;
        $evaluationReclamationActive = null;
        $evaluationEnCours = null;
        try {
            $anneeEnCours = Annee::resolveOpenYearId(now());
            $ficheAnneeEnCours = $baseF()
                ->where('annee_id', $anneeEnCours)
                ->latest()
                ->first();
            if ($ficheAnneeEnCours && ! in_array($ficheAnneeEnCours->statut, ['acceptee'], true)) {
                $ficheBloquante = $ficheAnneeEnCours;
            }
            if ($ficheAnneeEnCours
                && $ficheAnneeEnCours->statut === 'acceptee'
                && ($ficheAnneeEnCours->avancement_percentage ?? 0) > 0) {
                $ficheAvancee = $ficheAnneeEnCours;
            }
            // Réclamation active (non traitée ou non refusée)
            $evaluationReclamationActive = $baseE()
                ->where('annee_id', $anneeEnCours)
                ->where('statut', 'reclamation')
                ->where(fn ($q) => $q->whereNull('statut_reclamation')
                    ->orWhere('statut_reclamation', '!=', 'maintenu'))
                ->first();
            // Évaluation déjà en cours (soumise ou brouillon) → bloquer la création
            $evaluationEnCours = $baseE()
                ->where('annee_id', $anneeEnCours)
                ->whereIn('statut', ['soumis', 'brouillon'])
                ->first();
        } catch (\RuntimeException) {
            // Pas d'année ouverte — pas de blocage
        }

        return view('chef.agent.show', compact(
            'agent', 'ctx', 'tab',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'stats', 'filters',
            'evaluationsEnabled', 'objectifsEnabled',
            'ficheBloquante', 'ficheAnneeEnCours', 'ficheAvancee',
            'evaluationReclamationActive', 'evaluationEnCours'
        ));
    }
}
