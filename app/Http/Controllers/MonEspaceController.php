<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur unifié "Mon espace" — remplace les 5 contrôleurs par rôle.
 *
 * Rôles couverts : DG, DGA, Assistante_Dg, Conseillers_Dg,
 *                  Directeur_Direction, Directeur_Caisse, Directeur_Technique,
 *                  Chef_Service, Chef_Agence, Chef_Guichet, et tout le Personnel.
 *
 * Retourne toujours la vue unifiée 'mon-espace' avec des variables de
 * présentation qui adaptent la mise en page au rôle courant.
 */
class MonEspaceController extends Controller
{
    use ResolvesEntite;

    public function __invoke(Request $request): View
    {
        $role = Auth::user()?->role;

        return match (true) {
            $role === 'DG'
                => $this->dgInvoke($request),

            in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)
                => $this->dgaSubordonneInvoke($request),

            in_array($role, ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'], true)
                => $this->directeurInvoke($request),

            in_array($role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)
                => $this->chefInvoke($request),

            default
                => $this->personnelInvoke($request),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DG — reçoit évals du PCA et objectifs du PCA
    // ══════════════════════════════════════════════════════════════════════════

    private function dgInvoke(Request $request): View
    {
        $user   = Auth::user();
        $tab    = $request->input('tab', 'evaluations');
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        $baseE = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon');

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification'])
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon')
            ->orderByDesc('date_debut');

        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        $baseF = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id);

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->orderByDesc('date');

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

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        // ── Présentation ──────────────────────────────────────────────────────
        $layout           = 'layouts.dg';
        $monEspaceUrl     = url()->current();
        $evalShowRoute    = 'dg.evaluations.show';
        $ficheShowRoute   = 'dg.objectifs.show';
        $headerSubtitle   = 'Mon Espace / Directeur Général';
        $headerDetail     = 'Directeur Général';
        $avatarClasses    = 'bg-emerald-100 text-emerald-700';
        $useHeroHeader    = false;
        $tabPanelClass    = 'admin-panel px-6 py-6 lg:px-8';
        $themeEval        = 'emerald';
        $themeFiche       = 'emerald';
        $showBrouillonFilter = false;
        $hasEvalActions   = false;
        $hasFicheActions  = false;

        $outerStats = [
            ['label' => 'Évaluations', 'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star',         'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Acceptées',   'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-check',        'tone' => 'border-teal-100 bg-teal-50/80 text-teal-900',         'iw' => 'bg-white text-teal-600'],
            ['label' => 'Objectifs',   'value' => $fichesStats['total'],       'icon' => 'fas fa-bullseye',     'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-500'],
            ['label' => 'Acceptés',    'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check', 'tone' => 'border-slate-100 bg-white text-slate-900',             'iw' => 'bg-slate-100 text-slate-600'],
        ];
        $evalInnerCards = [
            ['label' => 'Total',    'value' => $evaluationsStats['total'],  'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'], 'icon' => 'fas fa-paper-plane',   'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Acceptées','value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',  'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'], 'icon' => 'fas fa-circle-xmark',  'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];
        $ficheInnerCards = [
            ['label' => 'Total',      'value' => $fichesStats['total'],      'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'En attente', 'value' => $fichesStats['en_attente'], 'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Acceptés',   'value' => $fichesStats['acceptees'],  'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusés',    'value' => $fichesStats['refusees'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];

        return view('mon-espace', compact(
            'user', 'tab',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'filters',
            'layout', 'monEspaceUrl', 'evalShowRoute', 'ficheShowRoute',
            'headerSubtitle', 'headerDetail', 'avatarClasses',
            'useHeroHeader', 'tabPanelClass',
            'themeEval', 'themeFiche',
            'showBrouillonFilter', 'hasEvalActions', 'hasFicheActions',
            'outerStats', 'evalInnerCards', 'ficheInnerCards',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DGA / Assistante_Dg / Conseillers_Dg — reçoivent évals et objectifs du DG
    // ══════════════════════════════════════════════════════════════════════════

    private function dgaSubordonneInvoke(Request $request): View
    {
        $user   = Auth::user();
        $isDga  = $user->role === 'DGA';
        $tab    = $request->input('tab', 'evaluations');
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        $baseE = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon');

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification'])
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon')
            ->orderByDesc('date_debut');

        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        $baseF = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id);

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->orderByDesc('date');

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

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        // ── Présentation ──────────────────────────────────────────────────────
        $espacePrefix = $this->espaceViewPrefix();  // 'dga' ou 'subordonne'
        $routePrefix  = $this->espaceRoutePrefix(); // 'dga' ou 'subordonne'

        $roleLabel = match ($user->role) {
            'DGA'            => 'Directeur Général Adjoint',
            'Assistante_Dg'  => 'Assistante du DG',
            'Conseillers_Dg' => 'Conseiller du DG',
            default          => $user->role,
        };

        $layout           = 'layouts.'.$espacePrefix;
        $monEspaceUrl     = url()->current();
        $evalShowRoute    = $routePrefix.'.evaluations.show';
        $ficheShowRoute   = $routePrefix.'.objectifs.show';
        $headerSubtitle   = 'Mon Espace / '.$roleLabel;
        $headerDetail     = $roleLabel;
        $avatarClasses    = $isDga ? 'bg-violet-100 text-violet-700' : 'bg-teal-100 text-teal-700';
        $useHeroHeader    = ! $isDga;
        $tabPanelClass    = $isDga
            ? 'admin-panel px-6 py-6 lg:px-8'
            : 'rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm';
        $themeEval        = $isDga ? 'violet' : 'cyan';
        $themeFiche       = $isDga ? 'violet' : 'indigo';
        $showBrouillonFilter = false;
        $hasEvalActions   = false;
        $hasFicheActions  = false;

        // Outer stats (DGA : admin-panel grid ; subordonne : mini-KPIs dans le hero)
        $toneEval = $isDga ? 'border-violet-100 bg-violet-50/80 text-violet-900' : '';
        $iwEval   = $isDga ? 'bg-white text-violet-600' : '';
        $outerStats = [
            ['label' => 'Évaluations', 'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star',         'tone' => $toneEval,                                             'iw' => $iwEval],
            ['label' => 'Validées',    'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-check',        'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Objectifs',   'value' => $fichesStats['total'],       'icon' => 'fas fa-clipboard-list','tone' => $toneEval,                                             'iw' => $iwEval],
            ['label' => 'Acceptés',    'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check', 'tone' => 'border-slate-100 bg-white text-slate-900',             'iw' => 'bg-slate-100 text-slate-600'],
        ];
        $evalInnerCards = [
            ['label' => 'Total',    'value' => $evaluationsStats['total'],  'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'], 'icon' => 'fas fa-paper-plane',   'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Validées', 'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',  'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'], 'icon' => 'fas fa-circle-xmark',  'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];
        $ficheInnerCards = [
            ['label' => 'Total',      'value' => $fichesStats['total'],      'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Acceptées',  'value' => $fichesStats['acceptees'],  'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'En attente', 'value' => $fichesStats['en_attente'], 'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Refusées',   'value' => $fichesStats['refusees'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];

        return view('mon-espace', compact(
            'user', 'tab', 'roleLabel',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'filters',
            'layout', 'monEspaceUrl', 'evalShowRoute', 'ficheShowRoute',
            'headerSubtitle', 'headerDetail', 'avatarClasses',
            'useHeroHeader', 'tabPanelClass',
            'themeEval', 'themeFiche',
            'showBrouillonFilter', 'hasEvalActions', 'hasFicheActions',
            'outerStats', 'evalInnerCards', 'ficheInnerCards',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Directeur — reçoit évals + objectifs via son entité (Direction/Caisse/DT)
    // ══════════════════════════════════════════════════════════════════════════

    private function directeurInvoke(Request $request): View
    {
        $user = Auth::user();
        $ctx  = DirecteurEntity::resolveOrFail($user);
        $tab    = $request->query('tab', 'evaluations');
        $statut = trim((string) $request->query('statut', ''));
        $search = trim((string) $request->query('search', ''));

        // ── Évaluations reçues ────────────────────────────────────────────────
        $baseE = fn () => Evaluation::where(function ($q) use ($ctx, $user) {
            $q->where(function ($q2) use ($ctx) {
                $q2->where('evaluable_type', $ctx->modelClass)
                   ->where('evaluable_id', $ctx->getId())
                   ->where('evaluable_role', 'manager');
            })->orWhere(function ($q2) use ($user) {
                $q2->where('evaluable_type', User::class)
                   ->where('evaluable_id', $user->id);
            });
        })->where('statut', '!=', 'brouillon');

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $evalsQ = $baseE()->with(['evaluateur', 'identification'])->orderByDesc('date_debut');
        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }
        $evaluations = $evalsQ->paginate(10)->withQueryString();

        // ── Fiches d'objectifs reçues ─────────────────────────────────────────
        $baseF = fn () => FicheObjectif::where(function ($q) use ($ctx) {
            $q->where('assignable_type', $ctx->modelClass)
              ->where('assignable_id', $ctx->getId());
        })->orWhere(function ($q) use ($user) {
            $q->where('assignable_type', User::class)
              ->where('assignable_id', $user->id);
        });

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

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
        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        // ── Vue d'ensemble des services / caisses ─────────────────────────────
        $servicesWithAgents = $ctx->getServicesWithAgents();
        $servicesOverview = $servicesWithAgents->map(function (Service $service) {
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

        $notesChefs  = $servicesOverview->pluck('eval')->filter()->pluck('note_finale')->map(fn ($n) => (float) $n);
        $noteMoyenne = $notesChefs->isNotEmpty() ? round($notesChefs->avg(), 2) : null;

        $caissesOverview = collect();
        if ($ctx->hasCaisses()) {
            $caissesOverview = $ctx->getCaissesWithDirecteur()->map(function ($caisse) {
                $directeurUser = $caisse->directeur_agent_id
                    ? User::where('agent_id', $caisse->directeur_agent_id)->first()
                    : null;
                $latestEval = Evaluation::where('evaluable_type', Caisse::class)
                    ->where('evaluable_id', $caisse->id)
                    ->where('evaluable_role', 'manager')
                    ->whereIn('statut', ['soumis', 'valide'])
                    ->orderByDesc('date_debut')
                    ->first();
                return [
                    'caisse'        => $caisse,
                    'directeurUser' => $directeurUser,
                    'eval'          => $latestEval,
                    'agents_count'  => $caisse->agents_count,
                ];
            });
        }

        $evaluationsCreees = Evaluation::where('evaluateur_id', $user->id)
            ->whereIn('evaluable_type', [Service::class, Agence::class, Caisse::class])
            ->where('evaluable_role', 'manager')
            ->with(['evaluable', 'identification'])
            ->orderByDesc('created_at')
            ->get();

        // ── Présentation ──────────────────────────────────────────────────────
        $layout           = 'layouts.directeur';
        $monEspaceUrl     = url()->current();
        $evalShowRoute    = 'directeur.evaluations.show';
        $ficheShowRoute   = 'directeur.objectifs.show';
        $evalStatutRoute  = 'directeur.evaluations.statut';
        $ficheStatutRoute = 'directeur.objectifs.statut';
        $headerSubtitle   = 'Mon Espace / '.$ctx->getRoleLabel();
        $headerDetail     = null; // computed from $ctx in view
        $avatarClasses    = 'bg-blue-100 text-blue-700';
        $useHeroHeader    = false;
        $tabPanelClass    = 'admin-panel px-6 py-6 lg:px-8';
        $themeEval        = 'blue';
        $themeFiche       = 'blue';
        $showBrouillonFilter = false;
        $hasEvalActions   = true;
        $hasFicheActions  = true;

        $outerStats = [
            ['label' => 'Évaluations reçues', 'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star-half-stroke', 'tone' => 'border border-blue-100 bg-blue-50 text-blue-900',      'iw' => 'bg-blue-600 text-white'],
            ['label' => 'Acceptées',          'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',     'tone' => 'border border-emerald-100 bg-emerald-50 text-emerald-900','iw' => 'bg-emerald-600 text-white'],
            ['label' => 'Objectifs reçus',    'value' => $fichesStats['total'],       'icon' => 'fas fa-bullseye',         'tone' => 'border border-slate-200 bg-slate-50 text-slate-900',   'iw' => 'bg-slate-700 text-white'],
            ['label' => 'Objectifs acceptés', 'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check',     'tone' => 'border border-teal-100 bg-teal-50 text-teal-900',      'iw' => 'bg-teal-600 text-white'],
        ];
        $evalInnerCards = [
            ['label' => 'Total',    'value' => $evaluationsStats['total'],  'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'], 'icon' => 'fas fa-paper-plane',   'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Acceptées','value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',  'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'], 'icon' => 'fas fa-circle-xmark',  'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];
        $ficheInnerCards = [
            ['label' => 'Total',      'value' => $fichesStats['total'],      'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'En attente', 'value' => $fichesStats['en_attente'], 'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Acceptés',   'value' => $fichesStats['acceptees'],  'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusés',    'value' => $fichesStats['refusees'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];

        return view('mon-espace', compact(
            'user', 'tab', 'ctx',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'filters',
            'servicesOverview', 'caissesOverview',
            'evaluationsCreees', 'noteMoyenne',
            'layout', 'monEspaceUrl', 'evalShowRoute', 'ficheShowRoute',
            'evalStatutRoute', 'ficheStatutRoute',
            'headerSubtitle', 'headerDetail', 'avatarClasses',
            'useHeroHeader', 'tabPanelClass',
            'themeEval', 'themeFiche',
            'showBrouillonFilter', 'hasEvalActions', 'hasFicheActions',
            'outerStats', 'evalInnerCards', 'ficheInnerCards',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Chef — reçoit évals via sa structure + voit l'aperçu de ses agents
    // ══════════════════════════════════════════════════════════════════════════

    private function chefInvoke(Request $request): View
    {
        $user    = Auth::user();
        $ctx     = ChefEntity::resolveOrFail($user);
        $agent   = $ctx->agent;
        $agentId = $agent?->id ?? $user->agent_id;

        $tab    = in_array($request->input('tab'), ['evaluations', 'objectifs']) ? $request->input('tab') : 'evaluations';
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        $baseE = fn () => Evaluation::where(function ($q) use ($ctx, $user, $agent) {
            // Évaluations reçues en tant que manager de la structure (Guichet/Service/Agence)
            $q->where(function ($q2) use ($ctx) {
                $q2->where('evaluable_type', $ctx->modelClass)
                   ->where('evaluable_id', $ctx->getId())
                   ->where('evaluable_role', 'manager');
            });
            // Évaluations adressées directement au compte User du chef
            $q->orWhere(function ($q2) use ($user) {
                $q2->where('evaluable_type', User::class)
                   ->where('evaluable_id', $user->id);
            });
            // Évaluations adressées via le compte Agent du chef
            if ($agent) {
                $q->orWhere(function ($q2) use ($agent) {
                    $q2->where('evaluable_type', Agent::class)
                       ->where('evaluable_id', $agent->id);
                });
            }
        })->where('statut', '!=', 'brouillon');

        $baseF = fn () => FicheObjectif::where(function ($q) use ($ctx, $user, $agent) {
            // Fiche adressée à la structure gérée (Guichet/Agence/Service)
            $q->where(function ($q2) use ($ctx) {
                $q2->where('assignable_type', $ctx->modelClass)
                   ->where('assignable_id', $ctx->getId());
            });
            // Fiche adressée directement au compte User du chef
            $q->orWhere(function ($q2) use ($user) {
                $q2->where('assignable_type', User::class)
                   ->where('assignable_id', $user->id);
            });
            // Fiche adressée via l'Agent lié au chef (chemin admin ou gestion)
            if ($agent) {
                $q->orWhere(function ($q2) use ($agent) {
                    $q2->where('assignable_type', Agent::class)
                       ->where('assignable_id', $agent->id);
                });
            }
        });

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $evalsQ = $baseE()->with(['evaluateur', 'identification'])->orderByDesc('date_debut');
        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }
        $evaluations = $evalsQ->paginate(10)->withQueryString();

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
        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        $agentsStructure = $ctx->getAgents();
        $agentsOverview  = $agentsStructure->filter(fn ($ag) => $ag->id !== $agentId)
            ->map(function (Agent $subordonne) {
                $latestEval = Evaluation::where('evaluable_type', Agent::class)
                    ->where('evaluable_id', $subordonne->id)
                    ->where('statut', '!=', 'brouillon')
                    ->orderByDesc('date_fin')
                    ->first();
                return [
                    'agent'       => $subordonne,
                    'latest_eval' => $latestEval,
                    'eval_statut' => $latestEval ? $latestEval->statut : 'non_evalue',
                ];
            });

        // ── Présentation ──────────────────────────────────────────────────────
        $layout           = 'layouts.chef';
        $monEspaceUrl     = url()->current();
        $evalShowRoute    = 'chef.evaluations.show';
        $ficheShowRoute   = 'chef.mes-fiches.show';
        $headerSubtitle   = 'Mon Espace / '.$ctx->getRoleLabel();
        $headerDetail     = null; // computed from $ctx in view
        $avatarClasses    = 'bg-blue-100 text-blue-700';
        $useHeroHeader    = false;
        $tabPanelClass    = 'rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm';
        $themeEval        = 'blue';
        $themeFiche       = 'teal';
        $showBrouillonFilter = false;
        $hasEvalActions   = false;
        $hasFicheActions  = false;
        $outerStats       = null; // pas de grille KPI hors des tabs

        $evalInnerCards = [
            ['label' => 'Total',    'value' => $evaluationsStats['total'],  'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'], 'icon' => 'fas fa-paper-plane',   'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Validées', 'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',  'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'], 'icon' => 'fas fa-circle-xmark',  'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];
        $ficheInnerCards = [
            ['label' => 'Total',      'value' => $fichesStats['total'],      'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Acceptées',  'value' => $fichesStats['acceptees'],  'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'En attente', 'value' => $fichesStats['en_attente'], 'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Refusées',   'value' => $fichesStats['refusees'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];

        return view('mon-espace', compact(
            'user', 'tab', 'ctx', 'agent',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'filters', 'agentsOverview',
            'layout', 'monEspaceUrl', 'evalShowRoute', 'ficheShowRoute',
            'headerSubtitle', 'headerDetail', 'avatarClasses',
            'useHeroHeader', 'tabPanelClass',
            'themeEval', 'themeFiche',
            'showBrouillonFilter', 'hasEvalActions', 'hasFicheActions',
            'outerStats', 'evalInnerCards', 'ficheInnerCards',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Personnel — reçoit évals + objectifs via User ou Agent
    // ══════════════════════════════════════════════════════════════════════════

    private function personnelInvoke(Request $request): View
    {
        $user  = $request->user();
        $tab   = in_array($request->input('tab'), ['evaluations', 'objectifs'])
            ? $request->input('tab')
            : 'evaluations';
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        $agent = $user->agent_id
            ? Agent::with(['service.direction.entite', 'agence'])->find($user->agent_id)
            : null;

        $baseE = function () use ($user, $agent) {
            return Evaluation::where(function ($q) use ($user, $agent) {
                $q->where('evaluable_type', User::class)
                  ->where('evaluable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('evaluable_type', Agent::class)
                           ->where('evaluable_id', $agent->id);
                    });
                }
            })->where('statut', '!=', 'brouillon');
        };

        $evalsQ = $baseE()->with(['evaluateur', 'identification'])->orderByDesc('date_debut');
        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        $baseF = function () use ($user, $agent) {
            return FicheObjectif::where(function ($q) use ($user, $agent) {
                $q->where('assignable_type', User::class)
                  ->where('assignable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)
                           ->where('assignable_id', $agent->id);
                    });
                }
            });
        };

        $fichesQ = $baseF()->withCount('objectifs')->orderByDesc('date');
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

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        // ── Présentation ──────────────────────────────────────────────────────
        $layout           = 'layouts.personnel';
        $monEspaceUrl     = url()->current();
        $evalShowRoute    = 'personnel.evaluations.show';
        $ficheShowRoute   = 'personnel.fiches.show';
        $headerSubtitle   = 'Mon dossier · Personnel';
        $headerDetail     = null; // computed from $agent in view
        $avatarClasses    = 'bg-emerald-100 text-emerald-700';
        $useHeroHeader    = false;
        $tabPanelClass    = 'rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm';
        $themeEval        = 'cyan';
        $themeFiche       = 'emerald';
        $showBrouillonFilter = false;
        $hasEvalActions   = false;
        $hasFicheActions  = false;
        $outerStats       = null;

        $evalInnerCards = [
            ['label' => 'Total',    'value' => $evaluationsStats['total'],  'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'], 'icon' => 'fas fa-paper-plane',   'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Validées', 'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',  'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'], 'icon' => 'fas fa-circle-xmark',  'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];
        $ficheInnerCards = [
            ['label' => 'Total',      'value' => $fichesStats['total'],      'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iw' => 'bg-slate-100 text-slate-600'],
            ['label' => 'Acceptées',  'value' => $fichesStats['acceptees'],  'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw' => 'bg-white text-emerald-600'],
            ['label' => 'En attente', 'value' => $fichesStats['en_attente'], 'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iw' => 'bg-white text-amber-600'],
            ['label' => 'Refusées',   'value' => $fichesStats['refusees'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iw' => 'bg-white text-rose-500'],
        ];

        return view('mon-espace', compact(
            'user', 'agent', 'tab',
            'evaluations', 'evaluationsStats',
            'fiches', 'fichesStats',
            'filters',
            'layout', 'monEspaceUrl', 'evalShowRoute', 'ficheShowRoute',
            'headerSubtitle', 'headerDetail', 'avatarClasses',
            'useHeroHeader', 'tabPanelClass',
            'themeEval', 'themeFiche',
            'showBrouillonFilter', 'hasEvalActions', 'hasFicheActions',
            'outerStats', 'evalInnerCards', 'ficheInnerCards',
        ));
    }
}
