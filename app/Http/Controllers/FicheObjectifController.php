<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Http\Controllers\Support\RoleAssigneeConfig;
use App\Http\Controllers\Support\RoleObjectifConfig;
use App\Mail\FicheObjectifAssigneeMail;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Guichet;
use App\Models\LigneFicheObjectif;
use App\Models\Service;
use App\Models\User;
use App\Services\ObjectifService;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * FicheObjectifController — Contrôleur unique pour la gestion des objectifs
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Chaque méthode publique CRUD délègue à des implémentations partagées via
 * un objet RoleObjectifConfig (pattern Strategy) au lieu de méthodes privées
 * dupliquées par rôle.
 *
 * Rôles couverts : PCA · DG · DGA · Directeur · Chef · Personnel
 * ══════════════════════════════════════════════════════════════════════════════
 */
class FicheObjectifController extends Controller
{
    use ResolvesEntite;

    private const SUBORDONNE_ROLE_LABELS = [
        'DGA'            => 'Directeur Général Adjoint',
        'Assistante_Dg'  => 'Assistante DG',
        'Conseillers_Dg' => 'Conseiller DG',
    ];

    public function __construct(private readonly ObjectifService $objectifService) {}

    // ══════════════════════════════════════════════════════════════════════════
    // INDEX — liste des fiches (uniquement PCA)
    // ══════════════════════════════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $this->authorize('objectifs.voir-equipe');
        $dgUser = $this->getDGOfDirectionGenerale();
        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $baseQuery = FicheObjectif::query()
            ->with(['assignable', 'annee'])
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->when($dgUser, fn ($q) => $q->where('assignable_id', $dgUser->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($search !== '', fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('titre', 'like', "%{$search}%")
                    ->orWhereHas('annee', fn ($a) => $a->where('annee', 'like', "%{$search}%"));
            }));

        $stats = [
            'total'      => (clone $baseQuery)->count(),
            'brouillon'  => (clone $baseQuery)->where('statut', 'brouillon')->count(),
            'acceptees'  => (clone $baseQuery)->where('statut', 'acceptee')->count(),
            'en_attente' => (clone $baseQuery)->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => (clone $baseQuery)->where('statut', 'refusee')->count(),
            'contestees' => (clone $baseQuery)->where('statut', 'contesté')->count(),
        ];

        $listQuery = clone $baseQuery;
        match ($statut) {
            'brouillon'   => $listQuery->where('statut', 'brouillon'),
            'acceptee'    => $listQuery->where('statut', 'acceptee'),
            'refusee'     => $listQuery->where('statut', 'refusee'),
            'en_attente'  => $listQuery->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut')),
            'contestee'   => $listQuery->where('statut', 'contesté'),
            default       => null,
        };

        $fiches = $listQuery->orderByDesc('date')->get();

        $ficheBlocksNew = $dgUser
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $dgUser->id)->whereNotIn('statut', ['refusee'])->exists()
            : false;

        return view('pca.objectifs.index', [
            'fiches'         => $fiches,
            'dgUser'         => $dgUser,
            'filters'        => ['search' => $search, 'statut' => $statut],
            'stats'          => $stats,
            'ficheBlocksNew' => $ficheBlocksNew,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CREATE — formulaire de création
    // ══════════════════════════════════════════════════════════════════════════

    public function create(Request $request): View
    {
        $this->authorize('objectifs.assigner');
        return $this->sharedCreateView($request, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STORE — persiste la nouvelle fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        return $this->sharedStore($request, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SHOW — consulter une fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function show(Request $request, $fiche): View
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($fiche);
        $role  = Auth::user()->role;
        $route = (string) $request->route()->getName();

        // DG : double rôle — reçoit du PCA OU a assigné à ses subordonnés
        if ($role === 'DG') {
            if ($fiche->assignable_type === User::class && (int) $fiche->assignable_id === Auth::id()) {
                return $this->sharedAssigneeShow($fiche, $this->resolveAssigneeConfig($fiche));
            }
            return $this->sharedAssignerShow($fiche, $this->resolveAssignerConfig());
        }

        // DGA : double rôle — reçoit du DG OU a assigné au DT (route sub-objectifs)
        if ($role === 'DGA') {
            return str_contains($route, 'sub-objectifs')
                ? $this->sharedAssignerShow($fiche, $this->resolveAssignerConfig())
                : $this->sharedAssigneeShow($fiche, $this->resolveAssigneeConfig($fiche));
        }

        // Assignateurs purs : PCA, Chef
        if (in_array($role, ['PCA', 'Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)) {
            return $this->sharedAssignerShow($fiche, $this->resolveAssignerConfig());
        }

        // Assignés purs : DT, Personnel, etc.
        return $this->sharedAssigneeShow($fiche, $this->resolveAssigneeConfig($fiche));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EDIT — formulaire de modification (brouillon / contesté / refusée)
    // ══════════════════════════════════════════════════════════════════════════

    public function edit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche->load('objectifs');
        return $this->sharedEdit($fiche, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPDATE — sauvegarde les modifications
    // ══════════════════════════════════════════════════════════════════════════

    public function update(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche->load('objectifs');
        return $this->sharedUpdate($request, $fiche, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SOUMETTRE — brouillon → en_attente
    // ══════════════════════════════════════════════════════════════════════════

    public function soumettre(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        return $this->sharedSoumettre($fiche, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DESTROY — supprimer une fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function destroy(Request $request, $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche = FicheObjectif::findOrFail($fiche);
        return $this->sharedDestroy($fiche, $this->resolveAssignerConfig());
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATUT — accepter / refuser une fiche reçue
    // ══════════════════════════════════════════════════════════════════════════

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        return $this->sharedStatut($request, $fiche, $this->resolveAssigneeConfig($fiche));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AVANCEMENT — mettre à jour le pourcentage global
    // ══════════════════════════════════════════════════════════════════════════

    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        return $this->sharedAvancement($request, $fiche, $this->resolveAssigneeConfig($fiche));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AVANCEMENT LIGNE — mettre à jour le pourcentage d'un objectif individuel
    // ══════════════════════════════════════════════════════════════════════════

    public function avancementLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $fiche = FicheObjectif::findOrFail($ficheId);
        return $this->sharedAvancementLigne($request, $fiche, $ligneId, $this->resolveAssigneeConfig($fiche));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONTESTER LIGNE — contester un objectif individuel
    // ══════════════════════════════════════════════════════════════════════════

    public function contesterLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $this->authorize('objectifs.contester');
        $fiche = FicheObjectif::findOrFail($ficheId);
        return $this->sharedContesterLigne($request, $fiche, $ligneId, $this->resolveAssigneeConfig($fiche));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORT PDF — télécharger la fiche en PDF
    // ══════════════════════════════════════════════════════════════════════════

    public function exportPdf(Request $request, $fiche)
    {
        $fiche = FicheObjectif::with(['objectifs', 'assignable', 'annee'])->findOrFail($fiche);
        $role  = Auth::user()->role;
        $user  = Auth::user();

        // Assigner-side PDF : DG (toujours), DGA sur sa route d'assigner
        if ($role === 'DG' || ($role === 'DGA' && $request->routeIs('dga.sub-objectifs.pdf'))) {
            $this->authorize('objectifs.voir-equipe');
            return ($this->resolveAssignerConfig()->buildPdfResponse)($fiche, $user);
        }

        // Assignee-side PDF : tous les autres (DGA/Assistante/Conseillers assignés, DT, Personnel)
        return ($this->resolveAssigneeConfig($fiche)->buildPdfResponse)($fiche, $user);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONTRAT — uniquement PCA
    // ══════════════════════════════════════════════════════════════════════════

    public function contrat(Request $request, FicheObjectif $objectif): View
    {
        return view('pca.objectifs.contrat', $this->buildPcaContratData($request, $objectif));
    }

    public function contratDownload(Request $request, FicheObjectif $objectif): Response
    {
        $pdf = Pdf::loadView('pdf.contrat-objectif', $this->buildPcaContratData($request, $objectif));
        return $pdf->download('contrat-objectif-' . $objectif->id . '.pdf');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ADJUST PROGRESS — uniquement PCA
    // ══════════════════════════════════════════════════════════════════════════

    public function adjustProgress(Request $request, LigneFicheObjectif $objectif): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $this->authorizePcaObjectifLigne($objectif, (int) $request->user()->agent?->entite_id);

        $validated = $request->validate(['direction' => ['required', 'string', 'in:up,down']]);

        if (Carbon::parse($objectif->ficheObjectif?->date_echeance)->isBefore(today())) {
            return redirect()->route('pca.objectifs.index')
                ->with('status', "L'échéance est dépassée. L'avancement ne peut plus être modifié.");
        }

        if ($this->pcaIsLockedByEvaluation($objectif)) {
            return redirect()->route('pca.objectifs.index')
                ->with('status', 'Avancement verrouillé : la cible a déjà été évaluée pour la période contenant cette échéance.');
        }

        $step    = 10;
        $current = (int) $objectif->avancement_percentage;
        $next    = $validated['direction'] === 'up' ? min(100, $current + $step) : max(0, $current - $step);

        $objectif->update(['avancement_percentage' => $next]);

        return redirect()->route('pca.objectifs.index')->with('status', "Avancement mis à jour à {$next}%.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CHEF → GUICHET — Chef_Agence assigne à un Chef de Guichet
    // ══════════════════════════════════════════════════════════════════════════

    public function createGuichetObjectif(Guichet $guichet): View
    {
        $this->authorize('objectifs.assigner');
        $ctx = ChefEntity::resolveOrFail(Auth::user());

        if ($ctx->type !== 'agence') {
            abort(403, "Seul un Chef d'Agence peut assigner des objectifs à un guichet.");
        }
        if ((int) $guichet->agence_id !== $ctx->getId()) {
            abort(403, "Ce guichet n'appartient pas à votre agence.");
        }

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('objectifs.create', [
            'layout'       => 'layouts.chef',
            'storeRoute'   => 'chef.subordonnes.guichet.objectifs.store',
            'hiddenField'  => ['name' => 'guichet_id', 'value' => $guichet->id],
            'cibleLabel'   => 'Guichet — ' . $guichet->nom,
            'backRoute'    => route('chef.mon-espace'),
            'oldObjectifs' => $oldObjectifs,
        ]);
    }

    public function storeGuichetObjectif(Request $request): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $ctx = ChefEntity::resolveOrFail(Auth::user());

        if ($ctx->type !== 'agence') {
            abort(403, "Seul un Chef d'Agence peut assigner des objectifs à un guichet.");
        }

        $guichet = Guichet::findOrFail((int) $request->input('guichet_id'));
        if ((int) $guichet->agence_id !== $ctx->getId()) {
            abort(403, "Ce guichet n'appartient pas à votre agence.");
        }

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $chefUser = $guichet->chef_agent_id
            ? User::where('agent_id', $guichet->chef_agent_id)->first()
            : null;

        if (! $chefUser) {
            return back()->withInput()
                ->with('error', "Aucun chef avec un compte utilisateur n'est assigné au guichet « {$guichet->nom} ».");
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $chefUser->id)) {
            return back()->withInput()
                ->with('error', "Une fiche d'objectifs existe déjà pour ce chef de guichet pour l'année en cours.");
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $chefUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
            'created_by'            => Auth::id(),
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $chefUser->id,
                "Nouvelle fiche d'objectifs reçue",
                "Le Chef d'Agence {$ctx->getChefNomPrenom()} vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute',
                route('chef.mes-fiches.show', $fiche)
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour le guichet « {$guichet->nom} »."
            : "Fiche d'objectifs assignée au chef du guichet « {$guichet->nom} ».";

        return redirect()->route('chef.guichets')->with('status', $msg);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONFIG FACTORY — résolution du contexte role-spécifique
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Retourne la configuration du rôle courant (côté assignateur).
     * Utilisé par create, store, edit, update, soumettre, destroy, exportPdf (assigner).
     */
    private function resolveAssignerConfig(): RoleObjectifConfig
    {
        return match (Auth::user()->role) {
            'PCA'                                          => $this->buildPcaConfig(),
            'DG'                                           => $this->buildDgConfig(),
            'DGA'                                          => $this->buildDgaConfig(),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->buildChefConfig(),
            default                                        => abort(403),
        };
    }

    private function buildPcaConfig(): RoleObjectifConfig
    {
        return new RoleObjectifConfig(
            layout:                'layouts.pca',
            storeRoute:            'pca.objectifs.store',
            showRoute:             'pca.objectifs.show',
            editRoute:             'pca.objectifs.edit',
            updateRoute:           'pca.objectifs.update',
            pdfRoute:              'pca.objectifs.contrat.download',
            createBackRoute:       route('pca.objectifs.index'),
            maxObjectifLength:     5000,
            subordonneField:       null,   // cible fixe : DG unique
            assignableType:        User::class,
            getSubordonnes:        fn () => collect([]),
            resolveAssignable:     fn (Request $req, array $ids): ?User => $this->getDGOfDirectionGenerale(),
            resolveAfterStore:     function (FicheObjectif $fiche, mixed $assignable, bool $isBrouillon): RedirectResponse {
                if ($isBrouillon) {
                    return redirect()->route('pca.objectifs.show', $fiche)
                        ->with('status', "Brouillon enregistré. Vous pouvez le modifier avant de l'envoyer au DG.");
                }
                return redirect()->route('pca.objectifs.index')
                    ->with('status', "Fiche d'objectifs envoyée au DG avec succès.");
            },
            checkOwnership:        function (FicheObjectif $fiche): void {
                $this->pcaAuthorizeFiche($fiche, (int) Auth::user()->agent?->entite_id);
            },
            resolveCibleLabel:     fn (FicheObjectif $f) => $this->getDGOfDirectionGenerale()?->name ?? 'Directeur Général',
            resolveBackUrl:        fn (FicheObjectif $f) => route('pca.objectifs.index'),
            canDelete:             fn (FicheObjectif $f) => in_array($f->statut, ['en_attente', 'brouillon', 'contesté', null], true),
            resolveDeleteRedirect: fn (FicheObjectif $f) => route('pca.objectifs.index'),
            notifyOnSend:          function (FicheObjectif $fiche): void {
                $dgUser = $this->getDGOfDirectionGenerale();
                $entite = Entite::find(Auth::user()->agent?->entite_id);
                if ($entite && $entite->directrice_generale_email) {
                    $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
                    Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
                }
                if ($dgUser) {
                    Alerte::notifier($dgUser->id, "Nouvelle fiche d'objectifs reçue",
                        "Une fiche d'objectifs « {$fiche->titre} » vous a été assignée par le PCA.", 'haute',
                        route('dg.objectifs.show', $fiche));
                }
            },
            notifyOnResend:        function (FicheObjectif $fiche): void {
                $dgUser = $this->getDGOfDirectionGenerale();
                $entite = Entite::find(Auth::user()->agent?->entite_id);
                if ($entite && $entite->directrice_generale_email) {
                    $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
                    Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
                }
                if ($dgUser) {
                    Alerte::notifier($dgUser->id, "Fiche d'objectifs révisée",
                        "La fiche « {$fiche->titre} » a été révisée par le PCA.", 'haute',
                        route('dg.objectifs.show', $fiche));
                }
            },
            buildPdfResponse:      function (FicheObjectif $fiche, User $user): \Symfony\Component\HttpFoundation\Response {
                return $this->buildContratObjectifPdf(
                    $fiche,
                    $fiche->assignable?->name ?? '-',
                    $this->resolveAssignableRoleLabel($fiche->assignable),
                    $user->name, 'Directeur Général',
                    $this->getEntiteForDG(),
                );
            },
        );
    }

    private function buildDgConfig(): RoleObjectifConfig
    {
        return new RoleObjectifConfig(
            layout:                'layouts.dg',
            storeRoute:            'dg.objectifs.store',
            showRoute:             'dg.objectifs.show',
            editRoute:             'dg.objectifs.edit',
            updateRoute:           'dg.objectifs.update',
            pdfRoute:              'dg.objectifs.pdf',
            createBackRoute:       function (Request $req): string {
                $id = (int) $req->integer('subordonne_id');
                if ($id > 0) {
                    $user = User::find($id);
                    if ($user) {
                        return match ($user->role ?? '') {
                            'DGA'           => route('dg.dga') . '?tab=objectifs',
                            'Assistante_Dg' => route('dg.assistante') . '?tab=objectifs',
                            default         => route('dg.conseillers.show', $user) . '?tab=objectifs',
                        };
                    }
                }
                return route('dg.mon-espace');
            },
            maxObjectifLength:     5000,
            subordonneField:       'subordonne_id',
            assignableType:        User::class,
            getSubordonnes:        fn () => $this->getDgSubordonnes()->values(),
            resolveAssignable:     fn (Request $req, array $ids): ?User => User::findOrFail($req->input('subordonne_id')),
            resolveAfterStore:     function (FicheObjectif $fiche, mixed $assignable, bool $isBrouillon): RedirectResponse {
                if ($isBrouillon) {
                    return redirect()->route('dg.objectifs.show', $fiche)
                        ->with('status', "Brouillon enregistré pour {$assignable->name}.");
                }
                $redirect = match ($assignable->role) {
                    'DGA'           => route('dg.dga') . '?tab=objectifs',
                    'Assistante_Dg' => route('dg.assistante') . '?tab=objectifs',
                    default         => route('dg.conseillers.show', $assignable) . '?tab=objectifs',
                };
                return redirect($redirect)
                    ->with('status', "Fiche d'objectifs assignée avec succès à {$assignable->name}.");
            },
            checkOwnership:        fn (FicheObjectif $f) => $this->authorizeDgFicheAssignee($f),
            resolveCibleLabel:     fn (FicheObjectif $f) => User::find($f->assignable_id)?->name ?? '—',
            resolveBackUrl:        function (FicheObjectif $f): string {
                $assignable = $f->assignable;
                if (! $assignable) {
                    return route('dg.mon-espace');
                }
                return match ($assignable->role ?? '') {
                    'DGA'           => route('dg.dga') . '?tab=objectifs',
                    'Assistante_Dg' => route('dg.assistante') . '?tab=objectifs',
                    default         => route('dg.conseillers.show', $assignable) . '?tab=objectifs',
                };
            },
            canDelete:             fn (FicheObjectif $f) => true,
            resolveDeleteRedirect: function (FicheObjectif $f): string {
                $assignable = $f->assignable;
                if (! $assignable) {
                    return route('dg.mon-espace');
                }
                return match ($assignable->role ?? '') {
                    'DGA'           => route('dg.dga') . '?tab=objectifs',
                    'Assistante_Dg' => route('dg.assistante') . '?tab=objectifs',
                    default         => route('dg.conseillers.show', $assignable) . '?tab=objectifs',
                };
            },
            notifyOnSend:          function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Nouvelle fiche d'objectifs reçue",
                    "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} »."
                );
            },
            notifyOnResend:        function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Fiche d'objectifs révisée",
                    "Le Directeur Général a révisé la fiche « {$fiche->titre} » suite à vos contestations."
                );
            },
            buildPdfResponse:      fn (FicheObjectif $fiche, User $user): \Symfony\Component\HttpFoundation\Response =>
                $this->buildContratObjectifPdf(
                    $fiche,
                    $fiche->assignable?->name ?? '-',
                    $this->resolveAssignableRoleLabel($fiche->assignable),
                    $user->name, 'Directeur Général',
                    $this->getEntite(),
                ),
        );
    }

    private function buildDgaConfig(): RoleObjectifConfig
    {
        return new RoleObjectifConfig(
            layout:                'layouts.dga',
            storeRoute:            'dga.sub-objectifs.store',
            showRoute:             'dga.sub-objectifs.show',
            editRoute:             'dga.sub-objectifs.edit',
            updateRoute:           'dga.sub-objectifs.update',
            pdfRoute:              'dga.sub-objectifs.pdf',
            createBackRoute:       function (Request $req): string {
                $id = (int) $req->integer('subordonne_id');
                if ($id > 0) {
                    return route('dga.subordonnes.show', $id);
                }
                return route('dga.mon-espace');
            },
            maxObjectifLength:     5000,
            subordonneField:       'subordonne_id',
            assignableType:        User::class,
            getSubordonnes:        fn () => $this->getDgaSubordonnes()->values(),
            resolveAssignable:     fn (Request $req, array $ids): ?User => User::findOrFail($req->input('subordonne_id')),
            resolveAfterStore:     function (FicheObjectif $fiche, mixed $assignable, bool $isBrouillon): RedirectResponse {
                if ($isBrouillon) {
                    return redirect()->route('dga.sub-objectifs.show', $fiche)
                        ->with('status', "Brouillon enregistré pour {$assignable->name}.");
                }
                return redirect()->route('dga.subordonnes.show', $assignable->id)
                    ->with('status', "Fiche d'objectifs assignée avec succès.");
            },
            checkOwnership:        fn (FicheObjectif $f) => $this->authorizeDgaFicheAssignee($f),
            resolveCibleLabel:     fn (FicheObjectif $f) => User::find($f->assignable_id)?->name ?? '—',
            resolveBackUrl:        fn (FicheObjectif $f) => route('dga.subordonnes.show', $f->assignable_id),
            canDelete:             fn (FicheObjectif $f) => $f->statut !== 'acceptee',
            resolveDeleteRedirect: function (FicheObjectif $f): string {
                $subordonneId = $f->assignable_type === User::class
                    ? $f->assignable_id
                    : User::where('agent_id', $f->assignable?->directeur_agent_id ?? null)->value('id');
                return route('dga.subordonnes.show', $subordonneId);
            },
            notifyOnSend:          function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Nouvelle fiche d'objectifs reçue",
                    "Le DGA vous a assigné une fiche d'objectifs « {$fiche->titre} »."
                );
            },
            notifyOnResend:        function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Fiche d'objectifs révisée",
                    "Le DGA a révisé la fiche « {$fiche->titre} » suite à vos contestations."
                );
            },
            buildPdfResponse:      fn (FicheObjectif $fiche, User $user): \Symfony\Component\HttpFoundation\Response =>
                $this->buildContratObjectifPdf(
                    $fiche,
                    $fiche->assignable?->name ?? '-',
                    $this->resolveAssignableRoleLabel($fiche->assignable),
                    $user->name, 'Directeur Général Adjoint',
                    $this->getEntite(),
                ),
        );
    }

    private function buildChefConfig(): RoleObjectifConfig
    {
        return new RoleObjectifConfig(
            layout:                'layouts.chef',
            storeRoute:            'chef.objectifs.store',
            showRoute:             'chef.objectifs.show',
            editRoute:             'chef.objectifs.edit',
            updateRoute:           'chef.objectifs.update',
            pdfRoute:              null,
            createBackRoute:       function (Request $req): string {
                $agentId = (int) $req->integer('agent_id');
                if ($agentId > 0) {
                    return route('chef.agent.show', $agentId);
                }
                return route('chef.equipe');
            },
            maxObjectifLength:     500,
            subordonneField:       'agent_id',
            assignableType:        Agent::class,
            getSubordonnes:        function (): \Illuminate\Support\Collection {
                $ctx = ChefEntity::resolveOrFail(Auth::user());
                return $ctx->getAgents()->map(fn ($a) => [
                    'id'         => $a->id,
                    'nom'        => trim($a->prenom . ' ' . $a->nom),
                    'role_label' => '',
                ])->values();
            },
            resolveAssignable:     function (Request $req, array $ids): ?Agent {
                $ctx   = ChefEntity::resolveOrFail(Auth::user());
                $agent = Agent::findOrFail($req->input('agent_id'));
                if (! $ctx->agentOwnedBy($agent)) {
                    abort(403, "Cet agent n'est pas sous votre responsabilité.");
                }
                return $agent;
            },
            resolveAfterStore:     fn (FicheObjectif $fiche, mixed $assignable, bool $isBrouillon): RedirectResponse
                => redirect()->route('chef.objectifs.show', $fiche)
                    ->with('status', $isBrouillon
                        ? 'Brouillon enregistré.'
                        : "Fiche d'objectifs créée et transmise à l'agent."),
            checkOwnership:        fn (FicheObjectif $f) => $this->chefAuthorizeFiche($f),
            resolveCibleLabel:     function (FicheObjectif $f): string {
                $a = $f->assignable;
                return $a instanceof Agent
                    ? trim($a->prenom . ' ' . $a->nom)
                    : ($a?->name ?? '—');
            },
            resolveBackUrl:        function (FicheObjectif $f): string {
                $assignable = $f->assignable;
                return $assignable instanceof \App\Models\Agent
                    ? route('chef.agent.show', $assignable->id)
                    : route('chef.equipe');
            },
            canDelete:             fn (FicheObjectif $f) => $f->statut !== 'acceptee',
            resolveDeleteRedirect: function (FicheObjectif $f): string {
                $assignable = $f->assignable;
                return $assignable instanceof \App\Models\Agent
                    ? route('chef.agent.show', $assignable->id)
                    : route('chef.equipe');
            },
            notifyOnSend:          function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Nouvelle fiche d'objectifs reçue",
                    "Votre chef " . Auth::user()->name . " vous a assigné une fiche d'objectifs : « {$fiche->titre} ».",
                    'moyenne'
                );
            },
            notifyOnResend:        function (FicheObjectif $fiche): void {
                $this->notifyFicheAssignee($fiche,
                    "Fiche d'objectifs révisée",
                    "Votre chef a révisé la fiche d'objectifs « {$fiche->titre} » suite à vos contestations.",
                    'moyenne'
                );
            },
            buildPdfResponse:      fn (FicheObjectif $fiche, User $user): \Symfony\Component\HttpFoundation\Response =>
                $this->buildFicheObjectifPdf(
                    $fiche,
                    $fiche->assignable instanceof Agent
                        ? trim($fiche->assignable->prenom . ' ' . $fiche->assignable->nom)
                        : ($fiche->assignable?->name ?? '-'),
                    'Agent',
                    $user->name, 'Chef de service',
                ),
        );
    }

    /**
     * Retourne la configuration du rôle courant (côté assigné — celui qui reçoit la fiche).
     * Utilisé par statut, avancement, avancementLigne, contesterLigne, exportPdf (assignee).
     */
    private function resolveAssigneeConfig(FicheObjectif $fiche): RoleAssigneeConfig
    {
        $role = Auth::user()->role;

        // ── DG reçoit du PCA ──────────────────────────────────────────────────
        if ($role === 'DG') {
            return new RoleAssigneeConfig(
                layout:          'layouts.dg',
                showRoute:       'dg.objectifs.show',
                backRoute:       route('dg.mon-espace'),
                statusRoute:     'dg.objectifs.statut',
                avancementRoute: 'dg.objectifs.lignes.avancement',
                contesterRoute:  'dg.objectifs.lignes.contester',
                pdfRoute:        'dg.objectifs.pdf',
                checkOwnership:  function (FicheObjectif $f): void {
                    $this->authorize('objectifs.accepter');
                    if ($f->assignable_type !== User::class || (int) $f->assignable_id !== Auth::id()) {
                        abort(403);
                    }
                },
                notifyOnStatut:  function (FicheObjectif $f, string $action): void {},
                notifyOnContest: function (FicheObjectif $f): void {
                    foreach (User::where('role', 'PCA')->get() as $pca) {
                        Alerte::notifier($pca->id, 'Objectif contesté',
                            "Le DG a contesté un objectif dans la fiche « {$f->titre} ».", 'haute',
                            route('pca.objectifs.show', $f));
                    }
                },
                buildPdfResponse: function (FicheObjectif $f, User $user): \Symfony\Component\HttpFoundation\Response {
                    $entite = $this->getEntite();
                    return $this->buildContratObjectifPdf(
                        $f,
                        $user->name, 'Directeur Général',
                        $this->getDGUser($entite)?->name ?? '', "Président du Conseil d'Administration",
                        $entite,
                    );
                },
            );
        }

        // ── DGA / Assistante_Dg / Conseillers_Dg reçoivent du DG ─────────────
        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $prefix = $this->espaceRoutePrefix();
            return new RoleAssigneeConfig(
                layout:          'layouts.' . $this->espaceViewPrefix(),
                showRoute:       "{$prefix}.objectifs.show",
                backRoute:       route("{$prefix}.mon-espace"),
                statusRoute:     "{$prefix}.objectifs.statut",
                avancementRoute: "{$prefix}.objectifs.lignes.avancement",
                contesterRoute:  "{$prefix}.objectifs.lignes.contester",
                pdfRoute:        "{$prefix}.objectifs.pdf",
                checkOwnership:  fn (FicheObjectif $f) => $this->authorizeSubordonneFiche($f),
                notifyOnStatut:  function (FicheObjectif $f, string $action): void {
                    $evalue    = Auth::user();
                    $entite    = $this->getEntite();
                    $dgUser    = $this->getDGUser($entite);
                    $roleLabel = self::SUBORDONNE_ROLE_LABELS[$evalue->role] ?? $evalue->role;
                    if ($dgUser) {
                        $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
                        Alerte::notifier($dgUser->id, "Fiche d'objectifs {$actionLabel}e",
                            "{$roleLabel} {$evalue->name} a {$actionLabel} la fiche « {$f->titre} ».",
                            $action === 'accepter' ? 'moyenne' : 'haute',
                            route('dg.objectifs.show', $f));
                    }
                },
                notifyOnContest: function (FicheObjectif $f): void {
                    $evalue = Auth::user();
                    foreach (User::where('role', 'DG')->get() as $dg) {
                        Alerte::notifier($dg->id, 'Objectif contesté',
                            "{$evalue->name} a contesté un objectif dans la fiche « {$f->titre} ».",
                            'haute', route('dg.objectifs.show', $f));
                    }
                },
                buildPdfResponse: function (FicheObjectif $f, User $user): \Symfony\Component\HttpFoundation\Response {
                    $entite = $this->getEntite();
                    return $this->buildContratObjectifPdf(
                        $f,
                        $user->name, self::SUBORDONNE_ROLE_LABELS[$user->role] ?? $user->role,
                        $this->getDGUser($entite)?->name ?? '', 'Directeur Général',
                        $entite,
                    );
                },
            );
        }

        // ── Directeurs (HQ, Caisse, Technique) reçoivent du DGA/DG ──────────────
        if (in_array($role, ['Directeur_Technique', 'Directeur_Direction', 'Directeur_Caisse'], true)) {
            $ctx = DirecteurEntity::resolveOrFail(Auth::user());
            return new RoleAssigneeConfig(
                layout:          'layouts.directeur',
                showRoute:       'directeur.objectifs.show',
                backRoute:       route('directeur.mon-espace'),
                statusRoute:     'directeur.objectifs.statut',
                avancementRoute: 'directeur.objectifs.lignes.avancement',
                contesterRoute:  'directeur.objectifs.lignes.contester',
                pdfRoute:        'directeur.objectifs.pdf',
                checkOwnership:  function (FicheObjectif $f) use ($ctx): void {
                    $this->authorize('objectifs.accepter');
                    if (! $this->ficheAppartientAuDirecteur($f, $ctx)) {
                        abort(403);
                    }
                },
                notifyOnStatut:  function (FicheObjectif $f, string $action) use ($ctx): void {
                    $evalue      = Auth::user();
                    $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
                    $roleLabel   = $ctx->getRoleLabel();
                    $message     = "{$roleLabel} {$evalue->name} a {$actionLabel} la fiche d'objectifs « {$f->titre} » que vous lui avez assignée.";
                    $priorite    = $action === 'accepter' ? 'moyenne' : 'haute';
                    // DC → notifier le DT de sa délégation
                    if ($ctx->type === 'caisse') {
                        $dtUser = $this->resolveDtUserForCaisse($ctx->entity);
                        if ($dtUser) {
                            Alerte::notifier($dtUser->id, "Fiche d'objectifs {$actionLabel}e", $message, $priorite,
                                route('directeur.subordonnes.caisse.objectifs.show', $f));
                        }
                    } elseif ($f->assignable_type === User::class) {
                        foreach (User::where('role', 'DGA')->get() as $dga) {
                            Alerte::notifier($dga->id, "Fiche d'objectifs {$actionLabel}e", $message, $priorite,
                                route('dga.sub-objectifs.show', $f));
                        }
                    } else {
                        foreach (User::where('role', 'DG')->get() as $dg) {
                            Alerte::notifier($dg->id, "Fiche d'objectifs {$actionLabel}e", $message, $priorite,
                                route('dg.objectifs.show', $f));
                        }
                    }
                },
                notifyOnContest: function (FicheObjectif $f) use ($ctx): void {
                    $evalue    = Auth::user();
                    $roleLabel = $ctx->getRoleLabel();
                    $msg       = "{$roleLabel} {$evalue->name} a contesté un objectif dans la fiche « {$f->titre} ».";
                    // DC → notifier le DT de sa délégation
                    if ($ctx->type === 'caisse') {
                        $dtUser = $this->resolveDtUserForCaisse($ctx->entity);
                        if ($dtUser) {
                            Alerte::notifier($dtUser->id, 'Objectif contesté', $msg, 'haute',
                                route('directeur.subordonnes.caisse.objectifs.show', $f));
                        }
                    } elseif ($f->assignable_type === User::class) {
                        foreach (User::where('role', 'DGA')->get() as $dga) {
                            Alerte::notifier($dga->id, 'Objectif contesté', $msg, 'haute',
                                route('dga.sub-objectifs.show', $f));
                        }
                    } else {
                        foreach (User::where('role', 'DG')->get() as $dg) {
                            Alerte::notifier($dg->id, 'Objectif contesté', $msg, 'haute',
                                route('dg.objectifs.show', $f));
                        }
                    }
                },
                buildPdfResponse: fn (FicheObjectif $f, User $user): \Symfony\Component\HttpFoundation\Response =>
                    $this->buildFicheObjectifPdf($f, $ctx->getDirecteurNomPrenom(), $ctx->getRoleLabel()),
            );
        }

        // ── Personnel (Agent, secrétaires, etc.) reçoit du Chef ───────────────
        $roleLabels = [
            'DGA' => 'Directeur Général Adjoint', 'Directeur_Technique' => 'Directeur Technique',
            'Chef_Agence' => "Chef d'Agence", 'Chef_Guichet' => 'Chef de Guichet',
            'Assistante_Dg' => 'Assistante DG', 'Conseillers_Dg' => 'Conseiller DG',
            'Secretaire_Assistante' => 'Secrétaire',
        ];
        $userRole = Auth::user()->role;
        return new RoleAssigneeConfig(
            layout:          'layouts.personnel',
            showRoute:       'personnel.fiches.show',
            backRoute:       route('personnel.mon-espace'),
            statusRoute:     'personnel.fiches.statut',
            avancementRoute: 'personnel.fiches.lignes.avancement',
            contesterRoute:  'personnel.fiches.lignes.contester',
            pdfRoute:        'personnel.fiches.pdf',
            checkOwnership:  fn (FicheObjectif $f) => $this->checkPersonnelOwnership($f),
            notifyOnStatut:  function (FicheObjectif $f, string $action): void {
                $chefUser = $this->resolveChefUserForFiche($f);
                if (! $chefUser) return;
                $label = $action === 'accepter' ? 'accepté' : 'refusé';
                Alerte::notifier(
                    $chefUser->id,
                    "Fiche d'objectifs {$label}e",
                    Auth::user()->name . " a {$label} la fiche d'objectifs « {$f->titre} ».",
                    $action === 'accepter' ? 'moyenne' : 'haute',
                    route('chef.objectifs.show', $f)
                );
            },
            notifyOnContest: function (FicheObjectif $f): void {
                $chefUser = $this->resolveChefUserForFiche($f);
                if ($chefUser) {
                    Alerte::notifier(
                        $chefUser->id,
                        'Objectif contesté',
                        Auth::user()->name . " a contesté un objectif dans la fiche « {$f->titre} ».",
                        'haute',
                        route('chef.objectifs.show', $f)
                    );
                    return;
                }
                // Fallback : secrétaire DGA → notifier le DGA
                if ($f->assignable_type === User::class) {
                    $secretary = User::find($f->assignable_id);
                    $entite = $secretary?->agent?->entite_id
                        ? Entite::find($secretary->agent->entite_id)
                        : null;
                    if ($entite?->dga_agent_id) {
                        $dgaUser = User::where('role', 'DGA')
                            ->where('agent_id', $entite->dga_agent_id)
                            ->first();
                        if ($dgaUser) {
                            Alerte::notifier(
                                $dgaUser->id,
                                'Objectif contesté',
                                Auth::user()->name . " a contesté un objectif dans la fiche « {$f->titre} ».",
                                'haute',
                                route('dga.sub-objectifs.show', $f)
                            );
                        }
                    }
                }
            },
            buildPdfResponse: fn (FicheObjectif $f, User $user): \Symfony\Component\HttpFoundation\Response =>
                $this->buildFicheObjectifPdf(
                    $f,
                    $user->name ?? '-',
                    $roleLabels[$userRole ?? ''] ?? ($userRole ?? 'Personnel'),
                ),
        );
    }

    // ══════════════════════════════════════════════════════════════════════════
    // IMPLÉMENTATIONS UNIFIÉES — partagées entre tous les rôles assignateurs
    // ══════════════════════════════════════════════════════════════════════════

    /** Formulaire de création — même logique pour tous les rôles assignateurs. */
    private function sharedCreateView(Request $request, RoleObjectifConfig $cfg): View
    {
        $subordonnes = ($cfg->getSubordonnes)();

        if ($cfg->subordonneField !== null) {
            $requestedId = (int) $request->integer($cfg->subordonneField);
            $selected    = $subordonnes->firstWhere('id', $requestedId);
            if (! $selected && $subordonnes->count() === 1) {
                $selected = $subordonnes->first();
            }
            $hiddenField         = $selected ? ['name' => $cfg->subordonneField, 'value' => $selected['id']] : null;
            $cibleLabel          = $selected ? $selected['nom'] : 'Choisir un subordonné';
            $subordonnesForView  = $hiddenField ? null : $subordonnes;
        } else {
            // Cible fixe (ex. PCA → DG unique) : pas de sélecteur
            $dgUser              = ($cfg->resolveAssignable)(request(), []);
            $hiddenField         = null;
            $cibleLabel          = $dgUser?->name ?? 'Directeur Général';
            $subordonnesForView  = null;
        }

        $backRoute = $cfg->createBackRoute instanceof \Closure
            ? ($cfg->createBackRoute)($request)
            : $cfg->createBackRoute;

        return view('objectifs.create', [
            'layout'          => $cfg->layout,
            'storeRoute'      => $cfg->storeRoute,
            'backRoute'       => $backRoute,
            'cibleLabel'      => $cibleLabel,
            'hiddenField'     => $hiddenField,
            'subordonnes'     => $subordonnesForView,
            'subordonneField' => $cfg->subordonneField,
            'oldObjectifs'    => is_array(old('objectifs')) ? old('objectifs') : [''],
        ]);
    }

    /** Persistance d'une nouvelle fiche — même logique pour tous les rôles assignateurs. */
    private function sharedStore(Request $request, RoleObjectifConfig $cfg): RedirectResponse
    {
        $subordonnes = ($cfg->getSubordonnes)();
        $allowedIds  = $cfg->subordonneField !== null
            ? $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        // Auto-sélection si un seul subordonné possible
        if ($cfg->subordonneField !== null
            && blank($request->input($cfg->subordonneField))
            && count($allowedIds) === 1
        ) {
            $request->merge([$cfg->subordonneField => $allowedIds[0]]);
        }

        // Règles de validation
        $rules = [
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:' . $cfg->maxObjectifLength],
        ];
        if ($cfg->subordonneField !== null) {
            $rules[$cfg->subordonneField] = ['required', 'integer', Rule::in($allowedIds)];
        }
        $validated = $request->validate($rules);

        // Nettoyage des objectifs
        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (empty($objectifs)) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        // Résolution de la cible
        $assignable = ($cfg->resolveAssignable)($request, $allowedIds);
        if ($assignable === null) {
            return back()->withInput()->with('error', "Aucune cible valide n'a été trouvée pour créer la fiche.");
        }

        // Année ouverte
        try {
            $anneeId = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Doublon
        if (FicheObjectif::existsPourAnnee($anneeId, $cfg->assignableType, $assignable->id)) {
            return back()->withInput()->with('error', "Une fiche d'objectifs existe déjà pour cette personne pour l'année en cours.");
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        // Création atomique
        $fiche = DB::transaction(function () use ($validated, $cfg, $assignable, $anneeId, $isBrouillon, $objectifs): FicheObjectif {
            $fiche = FicheObjectif::create([
                'titre'                 => $validated['titre_fiche'],
                'annee'                 => now()->year,
                'annee_id'              => $anneeId,
                'assignable_type'       => $cfg->assignableType,
                'assignable_id'         => $assignable->id,
                'date'                  => now()->toDateString(),
                'date_echeance'         => $validated['date_echeance'],
                'avancement_percentage' => 0,
                'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
                'created_by'            => Auth::id(),
            ]);
            foreach ($objectifs as $desc) {
                $fiche->objectifs()->create(['description' => $desc]);
            }
            return $fiche;
        });

        if (! $isBrouillon) {
            ($cfg->notifyOnSend)($fiche);
        }

        return ($cfg->resolveAfterStore)($fiche, $assignable, $isBrouillon);
    }

    // ── Côté assigné (celui qui reçoit la fiche) ──────────────────────────────

    /** Accepter / refuser une fiche reçue — même logique pour tous les rôles assignés. */
    private function sharedStatut(Request $request, FicheObjectif $fiche, RoleAssigneeConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if (! in_array($fiche->statut ?? 'en_attente', ['en_attente', null], true)) {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }
        $request->validate([
            'action'      => ['required', 'in:accepter,refuser'],
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ], [
            'motif_refus.required_if' => 'Veuillez indiquer le motif du refus.',
        ]);
        $action        = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        if ($action === 'accepter') {
            $fiche->date_validation = now()->toDateString();
        } else {
            $fiche->motif_refus = $request->input('motif_refus');
        }
        $fiche->save();
        ($cfg->notifyOnStatut)($fiche, $action);
        return redirect()->route($cfg->showRoute, $fiche)
            ->with('status', $action === 'accepter' ? 'Fiche acceptée.' : 'Fiche refusée.');
    }

    /** Mettre à jour l'avancement global — même logique pour tous les rôles assignés. */
    private function sharedAvancement(Request $request, FicheObjectif $fiche, RoleAssigneeConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $pct = (int) $request->avancement_percentage;
        if ($pct % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }
        if ($fiche->statut !== 'acceptee') {
            return back()->with('error', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }
        $fiche->avancement_percentage = $pct;
        $fiche->save();
        return redirect()->route($cfg->showRoute, $fiche)->with('status', 'Avancement mis à jour.');
    }

    /** Mettre à jour l'avancement d'une ligne — même logique pour tous les rôles assignés. */
    private function sharedAvancementLigne(Request $request, FicheObjectif $fiche, int|string $ligneId, RoleAssigneeConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $val = (int) $request->avancement_percentage;
        if ($val % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }
        if ($fiche->statut !== 'acceptee') {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }
        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $fiche->id)->findOrFail($ligneId);
        $ligne->update(['avancement_percentage' => $val]);
        $fiche->recalculateAvancement();
        return redirect()->route($cfg->showRoute, $fiche)->with('status', 'Avancement mis à jour.');
    }

    /** Contester une ligne — même logique pour tous les rôles assignés. */
    private function sharedContesterLigne(Request $request, FicheObjectif $fiche, int|string $ligneId, RoleAssigneeConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if ($fiche->statut === 'acceptee') {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', 'Impossible de contester une fiche déjà acceptée.');
        }
        if ($fiche->statut === 'refusee') {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', 'Vous avez déjà refusé cette fiche. Il faut choisir entre refuser la fiche ou contester des objectifs, pas les deux.');
        }
        $request->validate([
            'motif' => ['required', 'string', 'max:1000'],
        ], [
            'motif.required' => 'Veuillez indiquer le motif de la contestation.',
        ]);
        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $fiche->id)->findOrFail($ligneId);
        $ligne->update(['statut' => 'contesté', 'motif' => $request->input('motif')]);
        $fiche->update(['statut' => 'contesté']);
        ($cfg->notifyOnContest)($fiche);
        return redirect()->route($cfg->showRoute, $fiche)
            ->with('status', 'Objectif contesté. Votre supérieur hiérarchique a été notifié.');
    }

    /** Vue show pour le rôle assignateur (celui qui a créé la fiche). */
    private function sharedAssignerShow(FicheObjectif $fiche, RoleObjectifConfig $cfg): View
    {
        $this->authorize('objectifs.voir-equipe');
        ($cfg->checkOwnership)($fiche);
        return view('objectifs.show', [
            'layout'     => $cfg->layout,
            'fiche'      => $fiche,
            'backRoute'  => ($cfg->resolveBackUrl)($fiche),
            'pdfRoute'   => $cfg->pdfRoute,
            'editRoute'  => $cfg->editRoute,
            'isAssignee' => false,
        ]);
    }

    /** Vue show pour le rôle assigné (celui qui reçoit la fiche). */
    private function sharedAssigneeShow(FicheObjectif $fiche, RoleAssigneeConfig $cfg): View
    {
        ($cfg->checkOwnership)($fiche);
        return view('objectifs.show', [
            'layout'          => $cfg->layout,
            'fiche'           => $fiche,
            'backRoute'       => $cfg->backRoute,
            'statusRoute'     => $cfg->statusRoute,
            'avancementRoute' => $cfg->avancementRoute,
            'contesterRoute'  => $cfg->contesterRoute,
            'pdfRoute'        => $cfg->pdfRoute,
            'isAssignee'      => true,
        ]);
    }

    /** Formulaire d'édition — même logique pour tous les rôles assignateurs. */
    private function sharedEdit(FicheObjectif $fiche, RoleObjectifConfig $cfg): View|RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }
        return view('objectifs.edit', [
            'layout'      => $cfg->layout,
            'fiche'       => $fiche,
            'updateRoute' => $cfg->updateRoute,
            'cancelUrl'   => ($cfg->resolveBackUrl)($fiche),
            'cibleLabel'  => ($cfg->resolveCibleLabel)($fiche),
            'assigneeUser'=> $fiche->assignable instanceof User ? $fiche->assignable : null,
        ]);
    }

    /** Sauvegarde des modifications — même logique pour tous les rôles assignateurs. */
    private function sharedUpdate(Request $request, FicheObjectif $fiche, RoleObjectifConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:' . $cfg->maxObjectifLength],
        ]);

        $objectifs = array_values(array_filter(
            array_map('trim', $validated['objectifs']),
            fn ($v) => $v !== ''
        ));
        if (empty($objectifs)) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);
            ($cfg->notifyOnResend)($fiche);
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', $wasRefusee ? 'Fiche corrigée et renvoyée.' : 'Fiche révisée et renvoyée.');
        }

        if (! $wasContested && ! $wasRefusee && $action === 'envoyer') {
            $fiche->update(['statut' => 'en_attente']);
            ($cfg->notifyOnSend)($fiche);
            return redirect()->route($cfg->showRoute, $fiche)->with('status', 'Fiche envoyée.');
        }

        return redirect()->route($cfg->showRoute, $fiche)->with('status', 'Brouillon mis à jour.');
    }

    /** Passage brouillon → en_attente — même logique pour tous les rôles assignateurs. */
    private function sharedSoumettre(FicheObjectif $fiche, RoleObjectifConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if ($fiche->statut !== 'brouillon') {
            return redirect()->route($cfg->showRoute, $fiche)
                ->with('status', "Cette fiche n'est pas en brouillon.");
        }
        $fiche->update(['statut' => 'en_attente']);
        ($cfg->notifyOnSend)($fiche);
        return redirect()->route($cfg->showRoute, $fiche)->with('status', 'Fiche soumise avec succès.');
    }

    /** Suppression — même logique pour tous les rôles assignateurs. */
    private function sharedDestroy(FicheObjectif $fiche, RoleObjectifConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($fiche);
        if (! ($cfg->canDelete)($fiche)) {
            return back()->with('error', "Cette fiche ne peut pas être supprimée.");
        }
        $redirectUrl = ($cfg->resolveDeleteRedirect)($fiche);
        $fiche->objectifs()->delete();
        $fiche->delete();
        return redirect($redirectUrl)->with('status', "Fiche d'objectifs supprimée.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — PCA
    // ══════════════════════════════════════════════════════════════════════════

    private function getDGOfDirectionGenerale(): ?User
    {
        $entite = Entite::query()->latest()->first();
        if (! $entite || ! $entite->dg_agent_id) {
            return null;
        }
        return User::where('role', 'DG')->where('agent_id', $entite->dg_agent_id)->first();
    }

    private function pcaAuthorizeFiche(FicheObjectif $fiche, ?int $entiteId): void
    {
        $dgUser  = $this->getDGOfDirectionGenerale();
        $allowed = $dgUser
            && $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === (int) $dgUser->id;
        if (! $allowed) {
            abort(403);
        }
    }

    private function buildPcaContratData(Request $request, FicheObjectif $objectif): array
    {
        $this->pcaAuthorizeFiche($objectif, $request->user()->agent?->entite_id);
        $objectif->load('objectifs', 'assignable');
        $assignable      = $objectif->assignable;
        $entite          = Entite::find($request->user()->agent?->entite_id) ?? Entite::query()->latest()->first();
        $salarieNom      = $assignable instanceof User ? ($assignable->name ?? '') : '';
        $salarieFonction = 'Directeur Général';
        return [
            'contrat'                      => $objectif,
            'partieCollaborateur'          => (object) ['name' => $salarieNom ?: ($assignable?->name ?? 'Collaborateur'), 'role' => $salarieFonction],
            'partieFaitiere'               => $entite,
            'partieFaitiereNomComplet'     => trim(($entite?->pca_prenom ?? '') . ' ' . ($entite?->pca_nom ?? '')),
            'objectifs'                    => $objectif->objectifs,
            'dateDebut'                    => $objectif->date,
            'dateFin'                      => $objectif->date_echeance,
            'salarie_nom'                  => $salarieNom,
            'salarie_fonction'             => $salarieFonction,
            'institution_representant'     => trim(($entite?->pca_prenom ?? '') . ' ' . ($entite?->pca_nom ?? '')),
            'institution_fonction'         => "Président du Conseil d'Administration",
            'institution_sigle'            => $this->resolveInstitutionSigle($entite),
            'date_debut'                   => $objectif->date,
            'date_fin'                     => $objectif->date_echeance,
        ];
    }

    private function authorizePcaObjectifLigne(LigneFicheObjectif $objectif, int $entiteId): void
    {
        $fiche   = $objectif->ficheObjectif;
        $dgUser  = $this->getDGOfDirectionGenerale();
        $allowed = $fiche && $dgUser
            && $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === (int) $dgUser->id;
        if (! $allowed) {
            abort(403);
        }
    }

    private function pcaIsLockedByEvaluation(LigneFicheObjectif $objectif): bool
    {
        $fiche = $objectif->ficheObjectif;
        if (! $fiche) {
            return false;
        }
        return Evaluation::query()
            ->where('evaluable_type', $fiche->assignable_type)
            ->where('evaluable_id', $fiche->assignable_id)
            ->whereDate('date_debut', '<=', $fiche->date_echeance)
            ->whereDate('date_fin', '>=', $fiche->date_echeance)
            ->exists();
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — DG
    // ══════════════════════════════════════════════════════════════════════════

    private function getDgSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDG();
        $subordonnes = collect();
        if (! $entite) {
            return $subordonnes;
        }
        if ($entite->dga_agent_id) {
            $dga = User::where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
            if ($dga) {
                $subordonnes->push(['id' => $dga->id, 'nom' => $dga->name, 'role_label' => 'DGA']);
            }
        }
        if ($entite->assistante_agent_id) {
            $assistante = User::where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
            if ($assistante) {
                $subordonnes->push(['id' => $assistante->id, 'nom' => $assistante->name, 'role_label' => 'Assistante']);
            }
        }
        $conseillers = User::where('role', 'Conseillers_Dg')->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))->get();
        foreach ($conseillers as $c) {
            $subordonnes->push(['id' => $c->id, 'nom' => $c->name, 'role_label' => 'Conseiller']);
        }
        return $subordonnes;
    }

    private function authorizeDgFicheAssignee(FicheObjectif $fiche): void
    {
        $allowedIds = $this->getDgSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($fiche->assignable_type !== User::class || ! in_array((int) $fiche->assignable_id, $allowedIds, true)) {
            abort(403, 'Cette fiche ne vous appartient pas.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — DGA
    // ══════════════════════════════════════════════════════════════════════════

    private function getDgaSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDGA();
        $subordonnes = collect();
        if (! $entite) {
            return $subordonnes;
        }
        $dts = User::where('role', 'Directeur_Technique')->with('agent.directedDelegation')->get();
        foreach ($dts as $dt) {
            $subordonnes->push([
                'id'         => $dt->id,
                'nom'        => $dt->name,
                'role_label' => 'Directeur Technique' . ($dt->agent?->directedDelegation ? ' — ' . $dt->agent->directedDelegation->region : ''),
            ]);
        }
        $secretaire = $this->getDgaSecretaireUser($entite);
        if ($secretaire) {
            $subordonnes->push(['id' => $secretaire->id, 'nom' => $secretaire->name, 'role_label' => 'Secrétaire DGA']);
        }
        return $subordonnes;
    }

    private function authorizeDgaFicheAssignee(FicheObjectif $fiche): void
    {
        $allowedIds = $this->getDgaSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($fiche->assignable_type !== User::class || ! in_array((int) $fiche->assignable_id, $allowedIds, true)) {
            abort(403, 'Cette fiche ne vous appartient pas.');
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — Directeur
    // ══════════════════════════════════════════════════════════════════════════

    private function ficheAppartientAuDirecteur(FicheObjectif $fiche, DirecteurEntity $ctx): bool
    {
        if ($fiche->assignable_type === $ctx->modelClass && (int) $fiche->assignable_id === $ctx->getId()) {
            return true;
        }
        if ($fiche->assignable_type === User::class && (int) $fiche->assignable_id === Auth::id()) {
            return true;
        }
        return false;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — Chef
    // ══════════════════════════════════════════════════════════════════════════

    private function chefAuthorizeFiche(FicheObjectif $fiche): ChefEntity
    {
        $ctx = ChefEntity::resolveOrFail(Auth::user());
        if ($fiche->assignable_type === Agent::class) {
            $agent = Agent::find($fiche->assignable_id);
            if (! $agent || ! $ctx->agentOwnedBy($agent)) {
                abort(403, "Cet agent n'est pas sous votre responsabilité.");
            }
            return $ctx;
        }
        if ($fiche->assignable_type === User::class) {
            if ($ctx->type !== 'agence') {
                abort(403, 'Cette fiche ne vous appartient pas.');
            }
            $chefUser = User::find($fiche->assignable_id);
            if (! $chefUser || ! $chefUser->agent_id) {
                abort(403, 'Utilisateur cible introuvable.');
            }
            $guichet = Guichet::where('chef_agent_id', $chefUser->agent_id)->where('agence_id', $ctx->getId())->first();
            if (! $guichet) {
                abort(403, "Ce chef de guichet n'est pas sous votre responsabilité.");
            }
            return $ctx;
        }
        abort(403, 'Type de fiche non reconnu.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION — Personnel
    // ══════════════════════════════════════════════════════════════════════════

    private function checkPersonnelOwnership(FicheObjectif $fiche): void
    {
        $user       = Auth::user();
        $agent      = $user?->agent_id ? Agent::find($user->agent_id) : null;
        $isForUser  = $fiche->assignable_type === User::class && (int) $fiche->assignable_id === $user->id;
        $isForAgent = $agent && $fiche->assignable_type === Agent::class && (int) $fiche->assignable_id === $agent->id;
        if (! $isForUser && ! $isForAgent) {
            abort(403, "Cette fiche d'objectifs ne vous est pas adressée.");
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PARTAGÉS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Autorise DGA / Assistante_Dg / Conseillers_Dg sur leur propre fiche reçue.
     */
    private function authorizeSubordonneFiche(FicheObjectif $fiche): void
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, array_keys(self::SUBORDONNE_ROLE_LABELS), true)) {
            abort(403);
        }
        if ($fiche->assignable_type !== User::class || (int) $fiche->assignable_id !== $user->id) {
            abort(403);
        }
    }

    // ── Helpers PDF ───────────────────────────────────────────────────────────

    /**
     * Génère et télécharge un PDF sur le template pdf.contrat-objectif.
     * Utilisé par PCA, DG, DGA (côté assignateur) et DG, DGA (côté assigné).
     */
    private function buildContratObjectifPdf(
        FicheObjectif $fiche,
        string $collaborateurNom,
        string $collaborateurRole,
        string $signatoryNom,
        string $signatoryRole,
        ?Entite $entite,
    ): \Symfony\Component\HttpFoundation\Response {
        return Pdf::loadView('pdf.contrat-objectif', [
            'contrat'                  => $fiche,
            'partieCollaborateur'      => (object) ['name' => $collaborateurNom, 'role' => $collaborateurRole],
            'partieFaitiere'           => $entite,
            'partieFaitiereNomComplet' => $signatoryNom,
            'partieFaitiereRole'       => $signatoryRole,
            'objectifs'                => $fiche->objectifs,
            'dateDebut'                => $fiche->date,
            'dateFin'                  => $fiche->date_echeance,
            'institution_sigle'        => $this->resolveInstitutionSigle($entite),
        ])->download('contrat-objectifs-' . $fiche->id . '.pdf');
    }

    /**
     * Génère et télécharge un PDF sur le template pdf.fiche-objectifs.
     * Utilisé par Chef (assignateur) et DT, Personnel (assignés).
     */
    private function buildFicheObjectifPdf(
        FicheObjectif $fiche,
        string $assigneNom,
        string $assigneRole,
        string $assigneurNom = '-',
        string $assigneurRole = 'Supérieur hiérarchique',
    ): \Symfony\Component\HttpFoundation\Response {
        return Pdf::loadView('pdf.fiche-objectifs', [
            'fiche'         => $fiche,
            'assigneNom'    => $assigneNom,
            'assigneRole'   => $assigneRole,
            'assigneurNom'  => $assigneurNom,
            'assigneurRole' => $assigneurRole,
        ])->setPaper('a4', 'portrait')->download('fiche-objectifs-' . $fiche->id . '.pdf');
    }

    /**
     * Résout le label de rôle d'un assignable pour l'affichage PDF.
     */
    private function resolveAssignableRoleLabel(mixed $assignable): string
    {
        if (! ($assignable instanceof User)) {
            return '-';
        }
        return self::SUBORDONNE_ROLE_LABELS[$assignable->role ?? ''] ?? ($assignable->role ?? '-');
    }

    /**
     * Notifie l'utilisateur destinataire d'une fiche (via Alerte).
     * Résout automatiquement le user depuis assignable_type/assignable_id.
     */
    private function notifyFicheAssignee(
        FicheObjectif $fiche,
        string $subject,
        string $message,
        string $priorite = 'haute',
    ): void {
        $recipient = $fiche->assignable_type === User::class
            ? User::find($fiche->assignable_id)
            : User::where('agent_id', $fiche->assignable_id)->first();
        if ($recipient) {
            Alerte::notifier($recipient->id, $subject, $message, $priorite, $this->ficheShowUrlForUser($recipient, $fiche));
        }
    }

    /**
     * Résout le User chef hiérarchique direct d'un agent porteur d'une fiche.
     * Cascade : Chef de Service → Chef de Guichet → Chef d'Agence.
     */
    private function resolveChefUserForFiche(FicheObjectif $fiche): ?User
    {
        if ($fiche->assignable_type === Agent::class) {
            $agent = Agent::find($fiche->assignable_id);
        } elseif ($fiche->assignable_type === User::class) {
            $u     = User::find($fiche->assignable_id);
            $agent = $u?->agent_id ? Agent::find($u->agent_id) : null;
        } else {
            $agent = null;
        }

        if (! $agent) {
            return null;
        }

        // 1. Chef de Service
        if ($agent->service_id) {
            $service = Service::find($agent->service_id);
            $chef    = $service?->chef_agent_id ? User::where('agent_id', $service->chef_agent_id)->first() : null;
            if ($chef) return $chef;
        }

        // 2. Chef de Guichet
        if ($agent->guichet_id) {
            $guichet = \App\Models\Guichet::find($agent->guichet_id);
            $chef    = $guichet?->chef_agent_id ? User::where('agent_id', $guichet->chef_agent_id)->first() : null;
            if ($chef) return $chef;
        }

        // 3. Chef d'Agence
        if ($agent->agence_id) {
            $agence = \App\Models\Agence::find($agent->agence_id);
            $chef   = $agence?->chef_agent_id ? User::where('agent_id', $agence->chef_agent_id)->first() : null;
            if ($chef) return $chef;
        }

        return null;
    }

    /**
     * Trouve le User DT responsable d'une Caisse donnée, via sa DelegationTechnique.
     * Utilisé pour notifier le DT quand le DC refuse ou conteste une fiche.
     */
    private function resolveDtUserForCaisse(\App\Models\Caisse $caisse): ?User
    {
        if (! $caisse->delegation_technique_id) {
            return null;
        }
        $delegation = \App\Models\DelegationTechnique::find($caisse->delegation_technique_id);
        if (! $delegation?->directeur_agent_id) {
            return null;
        }
        return User::where('agent_id', $delegation->directeur_agent_id)
            ->where('role', 'Directeur_Technique')
            ->first();
    }

    private function resolveInstitutionSigle(?Entite $entite): string
    {
        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        return ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
    }

    /**
     * Retourne l'URL de consultation d'une fiche pour un destinataire donné.
     */
    private function ficheShowUrlForUser(User $user, FicheObjectif $fiche): string
    {
        return match ($user->role) {
            'PCA'                                     => route('pca.objectifs.show', $fiche),
            'DG'                                      => route('dg.objectifs.show', $fiche),
            'DGA'                                   => route('dga.objectifs.show', $fiche),
            'Assistante_Dg', 'Conseillers_Dg'     => route('subordonne.objectifs.show', $fiche),
            'Directeur_Technique', 'Directeur_Direction', 'Directeur_Caisse' => route('directeur.objectifs.show', $fiche),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet' => route('chef.mes-fiches.show', $fiche),
            default                                   => route('personnel.fiches.show', $fiche),
        };
    }
}
