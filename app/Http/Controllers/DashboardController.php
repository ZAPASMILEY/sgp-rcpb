<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Guichet;
use App\Models\LoginFailure;
use App\Models\Service;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /** Rôles de la faîtière — exclus des stats de notes réseau (DGA). */
    private const FAITIERE_ROLES = ['DG', 'DGA', 'PCA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function __construct(private readonly DashboardService $ds) {}

    // ══════════════════════════════════════════════════════════════════════════
    // PERSONNEL
    // ══════════════════════════════════════════════════════════════════════════

    public function personnel(Request $request): View
    {
        $user  = $request->user();
        $annee = (int) $request->input('annee', now()->year);

        $agent = $user->agent_id
            ? Agent::with(['service.direction.entite', 'agence'])->find($user->agent_id)
            : null;

        $baseE = fn () => Evaluation::where(function ($q) use ($user, $agent) {
            $q->where('evaluable_type', User::class)->where('evaluable_id', $user->id);
            if ($agent) {
                $q->orWhere(fn ($q2) => $q2->where('evaluable_type', Agent::class)->where('evaluable_id', $agent->id));
            }
        })->whereYear('date_debut', $annee);

        $evalsRecStats = $this->ds->evalStats($baseE);

        $evaluationsRecentes = $baseE()
            ->with(['evaluateur', 'identification'])
            ->orderByDesc('date_debut')
            ->take(5)
            ->get();

        $baseF = fn () => FicheObjectif::where(function ($q) use ($user, $agent) {
            $q->where('assignable_type', User::class)->where('assignable_id', $user->id);
            if ($agent) {
                $q->orWhere(fn ($q2) => $q2->where('assignable_type', Agent::class)->where('assignable_id', $agent->id));
            }
        })->whereYear('date', $annee);

        $fichesRecStats = $this->ds->ficheStats($baseF);
        $tauxAvancement = $this->ds->tauxAvancement($baseF);

        $fichesRecentes = $baseF()->withCount('objectifs')->orderByDesc('date')->take(5)->get();

        $evalsDonut        = $this->ds->evalsDonut($evalsRecStats, false);
        $fichesDonut       = $this->ds->fichesDonut($fichesRecStats);
        $anneesDisponibles = $this->ds->anneesDisponibles();
        $role              = 'personnel';
        $layout            = 'layouts.personnel';

        return view('dashboard.index', compact(
            'user', 'agent', 'annee', 'anneesDisponibles',
            'evalsRecStats', 'evaluationsRecentes',
            'fichesRecStats', 'fichesRecentes', 'tauxAvancement',
            'evalsDonut', 'fichesDonut', 'role', 'layout',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CHEF
    // ══════════════════════════════════════════════════════════════════════════

    public function chef(Request $request): View
    {
        $user  = Auth::user();
        $ctx   = ChefEntity::resolveOrFail($user);
        $agent = $ctx->agent;
        $annee = (int) $request->query('annee', now()->year);

        $evalsRecBase = fn () => Evaluation::where(function ($q) use ($ctx, $user, $agent) {
            // Évaluations de la structure gérée (Guichet, Service, Agence)
            $q->where(function ($q2) use ($ctx) {
                $q2->where('evaluable_type', $ctx->modelClass)
                   ->where('evaluable_id', $ctx->getId())
                   ->where('evaluable_role', 'manager');
            });
            // Évaluations directement sur le compte User
            $q->orWhere(fn ($q2) => $q2->where('evaluable_type', User::class)->where('evaluable_id', $user->id));
            // Évaluations sur le compte Agent
            if ($agent) {
                $q->orWhere(fn ($q2) => $q2->where('evaluable_type', Agent::class)->where('evaluable_id', $agent->id));
            }
        })->whereYear('date_debut', $annee);

        $evalsRecStats = $this->ds->evalStats($evalsRecBase);

        $fichesRecBase = fn () => FicheObjectif::where(function ($q) use ($ctx, $user, $agent) {
            // Fiches adressées à la structure gérée (Guichet, Service, Agence)
            $q->where(function ($q2) use ($ctx) {
                $q2->where('assignable_type', $ctx->modelClass)
                   ->where('assignable_id', $ctx->getId());
            });
            // Fiches adressées directement au compte User
            $q->orWhere(fn ($q2) => $q2->where('assignable_type', User::class)->where('assignable_id', $user->id));
            // Fiches adressées via le compte Agent
            if ($agent) {
                $q->orWhere(fn ($q2) => $q2->where('assignable_type', Agent::class)->where('assignable_id', $agent->id));
            }
        })->whereYear('date', $annee);

        $fichesRecStats    = $this->ds->ficheStats($fichesRecBase);
        $tauxAvancement    = $this->ds->tauxAvancement($fichesRecBase);
        $evalsGivStats     = $this->ds->evalsGivStats($user->id, $annee, Agent::class);
        $noteMoyenneEquipe = $this->ds->noteMoyenneEquipe($user->id, $annee, Agent::class);
        $fichesRecentes    = $fichesRecBase()->latest('date')->take(6)->get();

        $agentsOverview = $ctx->getAgents()->take(5)->map(function (Agent $a) use ($user) {
            $latestEval = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $a->id)
                ->where('evaluateur_id', $user->id)
                ->orderByDesc('date_debut')
                ->first();
            return ['agent' => $a, 'eval_statut' => $latestEval?->statut, 'eval_note' => $latestEval?->note_finale];
        });

        $evalsDonut        = $this->ds->evalsDonut($evalsRecStats);
        $fichesDonut       = $this->ds->fichesDonut($fichesRecStats);
        $anneesDisponibles = $this->ds->anneesDisponibles();

        $openAnnee = Annee::currentOpen();
        $agentIds  = $ctx->getAgents()->pluck('id');
        $coverage  = $openAnnee
            ? $this->ds->agentsCoverage($agentIds, $openAnnee)
            : ['totalAgents' => $agentIds->count(), 'agentsSansEval' => 0, 'agentsEvalues' => 0];

        $role   = 'chef';
        $layout = 'layouts.chef';

        return view('dashboard.index', compact(
            'user', 'ctx', 'agent', 'annee', 'anneesDisponibles',
            'evalsRecStats', 'fichesRecStats', 'tauxAvancement',
            'evalsGivStats', 'noteMoyenneEquipe',
            'evalsDonut', 'fichesDonut', 'fichesRecentes',
            'agentsOverview', 'openAnnee', 'role', 'layout',
        ) + $coverage);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DIRECTEUR
    // ══════════════════════════════════════════════════════════════════════════

    public function directeur(Request $request): View
    {
        $user  = Auth::user();
        $ctx   = DirecteurEntity::resolveOrFail($user);
        $annee = (int) $request->query('annee', now()->year);

        $evalsRecBase = fn () => Evaluation::where(function ($q) use ($ctx) {
            $q->where('evaluable_type', $ctx->modelClass)
              ->where('evaluable_id', $ctx->getId())
              ->where('evaluable_role', 'manager');
        })->orWhere(function ($q) use ($user) {
            $q->where('evaluable_type', User::class)->where('evaluable_id', $user->id);
        })->whereYear('date_debut', $annee);

        $evalsRecStats = $this->ds->evalStats($evalsRecBase);

        $fichesRecBase = fn () => FicheObjectif::where(function ($q) use ($ctx) {
            $q->where('assignable_type', $ctx->modelClass)->where('assignable_id', $ctx->getId());
        })->orWhere(function ($q) use ($user) {
            $q->where('assignable_type', User::class)->where('assignable_id', $user->id);
        })->whereYear('date', $annee);

        $fichesRecStats    = $this->ds->ficheStats($fichesRecBase);
        $tauxAvancement    = $this->ds->tauxAvancement($fichesRecBase);
        $evalsGivStats     = $this->ds->evalsGivStats($user->id, $annee);
        $noteMoyenneEquipe = $this->ds->noteMoyenneEquipe($user->id, $annee);
        $fichesRecentes    = $fichesRecBase()->latest('date')->take(6)->get();

        $servicesWithAgents = $ctx->getServicesWithAgents();
        $servicesOverview   = $servicesWithAgents->take(5)->map(function (Service $service) {
            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->whereIn('statut', ['soumis', 'valide'])
                ->orderByDesc('date_debut')
                ->first();
            return ['service' => $service, 'eval' => $latestEval, 'agents_count' => $service->agents->count()];
        });

        $evalsDonut        = $this->ds->evalsDonut($evalsRecStats);
        $fichesDonut       = $this->ds->fichesDonut($fichesRecStats);
        $direction         = $ctx->entity;
        $anneesDisponibles = $this->ds->anneesDisponibles();

        $openAnnee = Annee::currentOpen();
        $agentIds  = $servicesWithAgents->flatMap(fn ($s) => $s->agents)->pluck('id')->unique();
        $coverage  = $openAnnee
            ? $this->ds->agentsCoverage($agentIds, $openAnnee)
            : ['totalAgents' => $agentIds->count(), 'agentsSansEval' => 0, 'agentsEvalues' => 0];

        $role   = 'directeur';
        $layout = 'layouts.directeur';

        return view('dashboard.index', compact(
            'user', 'direction', 'ctx', 'annee', 'anneesDisponibles',
            'evalsRecStats', 'fichesRecStats', 'tauxAvancement',
            'evalsGivStats', 'noteMoyenneEquipe',
            'evalsDonut', 'fichesDonut', 'fichesRecentes',
            'servicesOverview', 'openAnnee', 'role', 'layout',
        ) + $coverage);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DGA
    // ══════════════════════════════════════════════════════════════════════════

    public function dga(Request $request): View
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'DGA') {
            abort(403, 'Accès réservé au DGA.');
        }

        $annee   = (int) $request->query('annee', now()->year);
        $statut  = trim((string) $request->input('statut', ''));
        $search  = trim((string) $request->input('search', ''));
        $anneeId = (int) $request->input('annee_id', 0);

        $reseauStats = [
            'delegations' => DelegationTechnique::count(),
            'caisses'     => Caisse::count(),
            'agences'     => Agence::count(),
            'guichets'    => Guichet::count(),
        ];

        $faitiereAgentIds = User::whereIn('role', self::FAITIERE_ROLES)
            ->whereNotNull('agent_id')->pluck('agent_id')->all();

        $noteReseau = Evaluation::where('evaluable_type', Agent::class)
            ->whereNotIn('evaluable_id', $faitiereAgentIds)
            ->where('statut', 'valide')->whereNotNull('note_finale')
            ->avg('note_finale');

        $baseSubEvals = fn () => Evaluation::where('evaluateur_id', $user->id);
        $subStats     = $this->ds->evalStats($baseSubEvals);

        $queryRecent = Evaluation::with(['identification', 'evaluateur'])
            ->where('evaluateur_id', $user->id)->orderByDesc('updated_at');

        if ($statut)        $queryRecent->where('statut', $statut);
        if ($anneeId)       $queryRecent->where('annee_id', $anneeId);
        if ($search !== '') $queryRecent->whereHas('identification', fn ($q) =>
            $q->where('nom_prenom', 'like', "%{$search}%")->orWhere('emploi', 'like', "%{$search}%")
        );

        $evaluations = $queryRecent->paginate(15)->withQueryString();
        $annees      = Annee::orderByDesc('annee')->get();

        $topEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)->where('statut', 'valide')
            ->orderByDesc('note_finale')->first();

        $bottomEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)->where('statut', 'valide')
            ->orderBy('note_finale')->first();

        $evalsRecBase = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)->whereYear('date_debut', $annee);

        $evalsRecStats = $this->ds->evalStats($evalsRecBase);

        $fichesRecBase = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id)->whereYear('date', $annee);

        $fichesRecStats    = $this->ds->ficheStats($fichesRecBase);
        $tauxAvancement    = $this->ds->tauxAvancement($fichesRecBase);
        $evalsDonut        = $this->ds->evalsDonut($evalsRecStats);
        $fichesDonut       = $this->ds->fichesDonut($fichesRecStats);
        $anneesDisponibles = $this->ds->anneesDisponibles();

        $openAnnee     = Annee::currentOpen();
        $dgaSubUserIds = User::where('role', 'Directeur_Technique')->pluck('id')->all();

        $entite = Entite::query()->where('dga_agent_id', $user->agent_id)->first()
            ?? Entite::query()->latest()->first();
        if ($entite?->dga_secretaire_agent_id) {
            $secretaireUser = User::where('agent_id', $entite->dga_secretaire_agent_id)->first();
            if ($secretaireUser) {
                $dgaSubUserIds[] = $secretaireUser->id;
            }
        }
        $dgaSubUserIds = array_unique($dgaSubUserIds);

        $coverage = $openAnnee
            ? $this->ds->userCoverage($dgaSubUserIds, $user->id, $openAnnee->id)
            : ['totalAgents' => count($dgaSubUserIds), 'agentsSansEval' => 0, 'agentsEvalues' => 0];

        $filters = compact('statut', 'search', 'anneeId');
        $role    = 'dga';
        $layout  = 'layouts.dga';

        return view('dashboard.index', compact(
            'annee', 'anneesDisponibles', 'reseauStats', 'noteReseau',
            'subStats', 'evalsRecStats', 'fichesRecStats', 'tauxAvancement',
            'evalsDonut', 'fichesDonut', 'evaluations', 'annees',
            'topEval', 'bottomEval', 'filters', 'openAnnee', 'role', 'layout',
        ) + $coverage);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PCA
    // ══════════════════════════════════════════════════════════════════════════

    public function pca(Request $request): View
    {
        $entite = Entite::with(['dg', 'dga', 'dgaSecretaire', 'pca', 'assistante'])
            ->where('pca_agent_id', $request->user()->agent_id)
            ->firstOrFail();

        $annee    = (int) $request->query('annee', now()->year);
        $dgUser   = $entite->dg_agent_id ? User::where('agent_id', $entite->dg_agent_id)->first() : null;
        $dgUserId = $dgUser?->id ?? 0;
        $dgNom      = $entite->dg ? trim($entite->dg->prenom . ' ' . $entite->dg->nom) : '';
        $dgInitiale = strtoupper(substr($dgNom ?: 'D', 0, 1));

        $baseF = fn () => FicheObjectif::query()
            ->where('assignable_type', User::class)->where('assignable_id', $dgUserId)
            ->whereYear('date', $annee);

        $fichesStats     = $this->ds->ficheStats($baseF);
        $tauxAvancement  = $this->ds->tauxAvancement($baseF);
        $totalFiches     = $fichesStats['total'];
        $fichesAcceptees = $fichesStats['acceptees'];
        $fichesEnAttente = $fichesStats['en_attente'];
        $fichesRefusees  = $fichesStats['refusees'];

        $baseE = fn () => Evaluation::query()
            ->where('evaluable_type', User::class)->where('evaluable_id', $dgUserId)
            ->whereYear('date_debut', $annee);

        $evalsStats     = $this->ds->evalStats($baseE);
        $evalsTotal     = $evalsStats['total'];
        $evalsValidees  = $evalsStats['valide'];
        $evalsSoumises  = $evalsStats['soumis'];
        $evalsRefusees  = $evalsStats['refuse'];
        $evalsBrouillon = $evalsStats['brouillon'];
        $noteMoyenne    = round($baseE()->where('statut', 'valide')->avg('note_finale') ?? 0, 2);

        $fichesDGRecentes = $dgUserId ? $baseF()->latest('date')->take(6)->get() : collect();

        $personnelCabinet = collect([
            ['role' => 'Directeur(trice) Général(e)', 'agent' => $entite->dg,           'icon' => 'fas fa-user-tie',    'color' => 'bg-emerald-100 text-emerald-700'],
            ['role' => 'DGA',                          'agent' => $entite->dga,          'icon' => 'fas fa-user-shield', 'color' => 'bg-sky-100 text-sky-700'],
            ['role' => 'Assistante DG',                'agent' => $entite->assistante,   'icon' => 'fas fa-user',        'color' => 'bg-violet-100 text-violet-700'],
            ['role' => 'Sec. DGA',                    'agent' => $entite->dgaSecretaire, 'icon' => 'fas fa-user-pen',   'color' => 'bg-amber-100 text-amber-700'],
        ])->filter(fn ($p) => $p['agent'] !== null)->values();

        $evalsDonut        = $this->ds->evalsDonut($evalsStats);
        $fichesDonut       = $this->ds->fichesDonut($fichesStats);
        $anneesDisponibles = $this->ds->anneesDisponibles();
        $role              = 'pca';
        $layout            = 'layouts.pca';

        return view('dashboard.index', compact(
            'entite', 'annee', 'anneesDisponibles', 'dgUser', 'dgNom', 'dgInitiale', 'personnelCabinet',
            'totalFiches', 'fichesAcceptees', 'fichesEnAttente', 'fichesRefusees', 'tauxAvancement',
            'evalsTotal', 'evalsValidees', 'evalsSoumises', 'evalsRefusees', 'evalsBrouillon', 'noteMoyenne',
            'evalsDonut', 'fichesDonut', 'fichesDGRecentes', 'role', 'layout',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DG  (tableau de bord réseau — outil de reporting)
    // ══════════════════════════════════════════════════════════════════════════

    public function dg(Request $request): View
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403, 'Accès réservé au Directeur Général.');
        }

        $statut  = trim((string) $request->input('statut', ''));
        $search  = trim((string) $request->input('search', ''));
        $anneeId = (int) $request->input('annee', 0);

        $base = fn () => Evaluation::query()->where('statut', '!=', 'brouillon');

        $stats = [
            'total'       => $base()->count(),
            'soumis'      => $base()->where('statut', 'soumis')->count(),
            'valide'      => $base()->where('statut', 'valide')->count(),
            'excellent'   => Evaluation::where('statut', 'valide')->where('note_finale', '>=', 8.5)->count(),
            'bien'        => Evaluation::where('statut', 'valide')->whereBetween('note_finale', [7, 8.499])->count(),
            'passable'    => Evaluation::where('statut', 'valide')->whereBetween('note_finale', [5, 6.999])->count(),
            'insuffisant' => Evaluation::where('statut', 'valide')->where('note_finale', '<', 5)->count(),
        ];

        $query = Evaluation::with(['identification', 'evaluateur'])
            ->where('statut', '!=', 'brouillon')
            ->orderByDesc('updated_at');

        if ($statut)        $query->where('statut', $statut);
        if ($anneeId)       $query->where('annee_id', $anneeId);
        if ($search !== '') $query->whereHas('identification', fn ($q) =>
            $q->where('nom_prenom', 'like', "%{$search}%")->orWhere('emploi', 'like', "%{$search}%")
        );

        $evaluations = $query->paginate(20)->withQueryString();
        $annees      = Annee::orderByDesc('annee')->get();

        $topEval = Evaluation::with('identification')
            ->where('statut', 'valide')->orderByDesc('note_finale')->first();

        $bottomEval = Evaluation::with('identification')
            ->where('statut', 'valide')->orderBy('note_finale')->first();

        $openAnnee      = Annee::currentOpen();
        $agentsSansEval = 0;
        if ($openAnnee) {
            $totalPersonnel = Agent::personnel()->count();
            $agentsEvalues  = Agent::personnel()
                ->where(fn ($q) => $q
                    ->whereHas('evaluationsPersonnel', fn ($e) => $e->where('statut', 'valide')->where('annee_id', $openAnnee->id))
                    ->orWhereHas('evaluations',        fn ($e) => $e->where('statut', 'valide')->where('annee_id', $openAnnee->id))
                    ->orWhereHas('directedDirection',  fn ($d) => $d->whereHas('evaluations', fn ($e) => $e->where('statut', 'valide')->where('annee_id', $openAnnee->id)))
                    ->orWhereHas('directedCaisse',     fn ($c) => $c->whereHas('evaluations', fn ($e) => $e->where('statut', 'valide')->where('annee_id', $openAnnee->id)))
                    ->orWhereHas('directedDelegation', fn ($d) => $d->whereHas('evaluations', fn ($e) => $e->where('statut', 'valide')->where('annee_id', $openAnnee->id)))
                )
                ->count();
            $agentsSansEval = $totalPersonnel - $agentsEvalues;
        }

        $filters = compact('statut', 'search', 'anneeId');

        return view('dg.dashboard', compact(
            'stats', 'evaluations', 'annees', 'topEval', 'bottomEval',
            'filters', 'openAnnee', 'agentsSansEval',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // RH  (tableau de bord RH — outil de reporting)
    // ══════════════════════════════════════════════════════════════════════════

    public function rh(Request $request): View
    {
        $tab       = $request->input('tab', 'evaluations');
        $search    = $request->filled('search') ? trim((string) $request->input('search')) : null;
        $statut    = $request->filled('statut') ? trim((string) $request->input('statut')) : null;
        $annee     = $request->filled('annee') ? trim((string) $request->input('annee')) : null;
        $sexe      = $request->filled('sexe') ? trim((string) $request->input('sexe')) : '';
        $fonction  = $request->filled('fonction') ? trim((string) $request->input('fonction')) : '';
        $dt_id     = $request->filled('dt_id') ? $request->input('dt_id') : null;
        $caisse_id = $request->filled('caisse_id') ? $request->input('caisse_id') : null;
        $dir_id    = $request->filled('dir_id') ? $request->input('dir_id') : null;

        $statutDb = null;
        if ($statut) {
            $statutDb = match (mb_strtolower($statut, 'UTF-8')) {
                'acceptée', 'acceptee', 'valide'                             => 'acceptee',
                'en attente', 'en_attente', 'soumis', 'brouillon'           => 'en_attente',
                'rejetée', 'refusee', 'refusée', 'refuse'                   => 'refusee',
                default => $statut,
            };
        }

        // Calcul anticipé de l'année ouverte pour scoper les stats par défaut
        $openAnnee    = Annee::currentOpen();
        $openSemestre = null;
        $totalAgents  = Agent::count();
        $agentsEvalues = $agentsSansEval = 0;

        $evalQuery = Evaluation::query();
        // Par défaut (aucun filtre d'année saisi) : limiter à l'année ouverte
        if (! $annee && $openAnnee) {
            $evalQuery->where('annee_id', $openAnnee->id);
        }
        if ($statut) {
            $evalQuery->where($statut === 'refusee'
                ? fn ($q) => $q->whereIn('statut', ['refuse', 'reclamation'])
                : fn ($q) => $q->where('statut', $statut === 'acceptee' ? 'valide' : $statut)
            );
        }
        if ($annee)     $evalQuery->whereYear('date_debut', $annee);
        if ($search)    $evalQuery->where(fn ($s) => $s
            ->whereHas('identification', fn ($i) => $i->where('nom_prenom', 'like', "%{$search}%")->orWhere('emploi', 'like', "%{$search}%"))
            ->orWhereHas('evaluateur', fn ($e) => $e->where('name', 'like', "%{$search}%"))
        );
        if ($sexe !== '') $evalQuery->where(fn ($s) => $s
            ->where(fn ($s2) => $s2->where('evaluable_type', Agent::class)->whereHas('evaluable', fn ($qa) => $qa->where('sexe', $sexe)))
            ->orWhere(fn ($s2) => $s2->where('evaluable_type', User::class)->whereHas('evaluable', fn ($qu) => $qu->whereHas('agent', fn ($qa) => $qa->where('sexe', $sexe))))
        );
        if ($fonction !== '') $evalQuery->whereHas('identification', fn ($i) => $i->where('emploi', $fonction));
        if ($dt_id)     $evalQuery->whereHas('identification.agent', fn ($q) => $q->where('delegation_technique_id', $dt_id));
        if ($caisse_id) $evalQuery->whereHas('identification.agent', fn ($q) => $q->where('caisse_id', $caisse_id));
        if ($dir_id)    $evalQuery->whereHas('identification.agent', fn ($q) => $q->where('direction_id', $dir_id));

        $stats = [
            'agents'      => Agent::personnel()->count(),
            'total'       => (clone $evalQuery)->count(),
            'soumis'      => (clone $evalQuery)->where('statut', 'soumis')->count(),
            'valide'      => (clone $evalQuery)->where('statut', 'valide')->count(),
            'refuse'      => (clone $evalQuery)->whereIn('statut', ['refuse', 'reclamation'])->count(),
            'brouillon'   => (clone $evalQuery)->where('statut', 'brouillon')->count(),
            'excellent'   => (clone $evalQuery)->where('statut', 'valide')->where('note_finale', '>=', 8.5)->count(),
            'bien'        => (clone $evalQuery)->where('statut', 'valide')->where('note_finale', '>=', 7)->where('note_finale', '<', 8.5)->count(),
            'passable'    => (clone $evalQuery)->where('statut', 'valide')->where('note_finale', '>=', 5)->where('note_finale', '<', 7)->count(),
            'insuffisant' => (clone $evalQuery)->where('statut', 'valide')->where('note_finale', '>', 0)->where('note_finale', '<', 5)->count(),
        ];

        $evaluations = null;
        if ($tab === 'evaluations') {
            $evaluations = $evalQuery->with(['evaluateur:id,name,role', 'evaluable', 'identification:id,evaluation_id,nom_prenom,emploi'])
                ->orderByDesc('date_debut')->paginate(25)->withQueryString();
        }

        $fiches = $ficheStats = null;
        if ($tab === 'objectifs') {
            $ficheQuery = FicheObjectif::with(['assignable'])->withCount('objectifs');
            if ($statutDb)   $ficheQuery->where('statut', $statutDb);
            if ($annee)      $ficheQuery->whereYear('date', $annee);
            if ($search)     $ficheQuery->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")->orWhereHas('assignable', fn ($a) => $a->where('nom', 'like', "%{$search}%")->orWhere('prenom', 'like', "%{$search}%")));
            if ($dt_id)      $ficheQuery->whereHas('assignable', fn ($q) => $q->where('delegation_technique_id', $dt_id));
            if ($caisse_id)  $ficheQuery->whereHas('assignable', fn ($q) => $q->where('caisse_id', $caisse_id));
            if ($dir_id)     $ficheQuery->whereHas('assignable', fn ($q) => $q->where('direction_id', $dir_id));

            $ficheStats = [
                'total'      => (clone $ficheQuery)->count(),
                'acceptee'   => (clone $ficheQuery)->where('statut', 'acceptee')->count(),
                'en_attente' => (clone $ficheQuery)->whereIn('statut', ['en_attente', 'brouillon'])->count(),
                'refusee'    => (clone $ficheQuery)->where('statut', 'refusee')->count(),
            ];
            $fiches = $ficheQuery->orderByDesc('date')->paginate(20)->withQueryString();
        }

        $delegations = DelegationTechnique::orderBy('region')->get(['id', 'region', 'ville']);
        $caisses     = Caisse::orderBy('nom')->get(['id', 'nom']);
        $directions  = Direction::orderBy('nom')->get(['id', 'nom']);
        $fonctions   = Agent::ROLES;

        if ($openAnnee) {
            $openSemestre = $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first();
            // Agents avec au moins une évaluation VALIDÉE dans l'année+semestre ouverts
            // (même périmètre que $stats['valide'] → les deux chiffres sont comparables)
            $agentsEvalues = Agent::personnel()
                ->where(function ($q) use ($openAnnee, $openSemestre) {
                    $ef = function ($e) use ($openAnnee, $openSemestre) {
                        $e->where('annee_id', $openAnnee->id)
                          ->where('statut', 'valide');
                        if ($openSemestre) $e->where('semestre_id', $openSemestre->id);
                    };
                    $q->whereHas('evaluationsPersonnel', $ef)
                      ->orWhereHas('evaluations', $ef)
                      ->orWhereHas('directedDirection',  fn ($d) => $d->whereHas('evaluations', $ef))
                      ->orWhereHas('directedCaisse',     fn ($c) => $c->whereHas('evaluations', $ef))
                      ->orWhereHas('directedDelegation', fn ($d) => $d->whereHas('evaluations', $ef))
                      ->orWhereHas('ledAgence',          fn ($a) => $a->whereHas('evaluations', $ef))
                      ->orWhereHas('ledService',         fn ($s) => $s->whereHas('evaluations', $ef))
                      ->orWhereHas('ledGuichet',         fn ($g) => $g->whereHas('evaluations', $ef));
                })
                ->count();
            $agentsSansEval = $totalAgents - $agentsEvalues;
        }

        $filters = compact('tab', 'statut', 'search', 'annee', 'sexe', 'fonction', 'dt_id', 'caisse_id', 'dir_id');

        return view('rh.dashboard', compact(
            'stats', 'tab', 'filters', 'delegations', 'caisses', 'directions',
            'evaluations', 'fiches', 'ficheStats', 'openAnnee',
            'agentsSansEval', 'totalAgents', 'agentsEvalues', 'fonctions', 'openSemestre',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADMIN
    // ══════════════════════════════════════════════════════════════════════════

    public function admin(): View
    {
        $delegations = DelegationTechnique::with('directeur')->withCount(['agences', 'caisses'])->latest()->take(6)->get();
        $recentServices  = Service::with('direction.entite')->latest()->take(6)->get();
        $recentAgents    = Agent::with(['service', 'direction', 'delegationTechnique', 'caisse', 'agence', 'guichet'])->latest()->take(6)->get();
        $recentDirections = Direction::with(['entite', 'directeur', 'services'])->latest()->take(6)->get();

        $reseauChart = [
            'labels' => ['Caisses', 'Agences', 'Guichets'],
            'series' => [Caisse::count(), Agence::count(), Guichet::count()],
        ];

        $allDelegations   = DelegationTechnique::withCount(['caisses', 'agences'])->orderBy('region')->get();
        $delegationsChart = [
            'categories' => $allDelegations->pluck('region')->all(),
            'caisses'    => $allDelegations->pluck('caisses_count')->all(),
            'agences'    => $allDelegations->pluck('agences_count')->all(),
        ];

        $alertsChart = ['categories' => [], 'series' => []];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $alertsChart['categories'][] = $day->translatedFormat('D d');
            $alertsChart['series'][]     = LoginFailure::whereDate('attempted_at', $day->toDateString())->count();
        }

        $secretairesCount = User::whereIn('role', [
            'Secretaire_Direction', 'Secretaire_Technique', 'Secretaire_Caisse',
            'Secretaire_Agence', 'Secretaire_Assistante',
        ])->count();

        return view('admin.dashboard', [
            'caissesCount'             => $reseauChart['series'][0],
            'agencesCount'             => $reseauChart['series'][1],
            'guichetsCount'            => $reseauChart['series'][2],
            'entitesCount'             => Entite::count(),
            'delegationsCount'         => DelegationTechnique::count(),
            'directionsCount'          => Direction::count(),
            'servicesCount'            => Service::count(),
            'agentsCount'              => Agent::count(),
            'secretairesCount'         => $secretairesCount,
            'faitiereDirectionsCount'  => Direction::count(),
            'caissesParDelegation'     => Caisse::whereNotNull('delegation_technique_id')->count(),
            'servicesWithoutDirection' => Service::whereNull('direction_id')->count(),
            'agentsWithoutService'     => Agent::whereNull('service_id')->count(),
            'failedLoginAttemptsCount' => LoginFailure::count(),
            'failedLoginAttemptsToday' => LoginFailure::whereDate('attempted_at', today())->count(),
            'failedLoginEmailsCount'   => LoginFailure::whereNotNull('email')->distinct('email')->count('email'),
            'delegations'              => $delegations,
            'recentDirections'         => $recentDirections,
            'recentServices'           => $recentServices,
            'recentAgents'             => $recentAgents,
            'recentLoginFailures'      => LoginFailure::latest('attempted_at')->take(3)->get(),
            'reseauChart'              => $reseauChart,
            'delegationsChart'         => $delegationsChart,
            'alertsChart'              => $alertsChart,
        ]);
    }
}
