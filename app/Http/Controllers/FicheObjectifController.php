<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
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
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * FicheObjectifController — Contrôleur unique pour la gestion des objectifs
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Ce contrôleur remplace tous les anciens contrôleurs role-spécifiques :
 *   - PcaObjectifController    (PCA assigne au DG)
 *   - DgObjectifController     (DG assigne à ses subordonnés + reçoit du PCA)
 *   - DgaSubObjectifController (DGA assigne à ses subordonnés)
 *   - Dga/ObjectifController   (DGA / Assistante / Conseillers reçoivent du DG)
 *   - DirecteurObjectifController (Directeur reçoit du DGA/DG/PCA)
 *   - ChefObjectifController   (Chef assigne à ses agents)
 *   - PersonnelFicheController (Personnel reçoit de son chef)
 *
 * Chaque méthode publique route vers la logique appropriée selon Auth::user()->role.
 * Les routes gardent leurs chemins et middlewares existants ; seule la classe
 * contrôleur change.
 * ══════════════════════════════════════════════════════════════════════════════
 */
class FicheObjectifController extends Controller
{
    use ResolvesEntite;

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

        $fiches = $listQuery->orderByDesc('date')->paginate(10)->withQueryString();

        return view('pca.objectifs.index', [
            'fiches'  => $fiches,
            'dgUser'  => $dgUser,
            'filters' => ['search' => $search, 'statut' => $statut],
            'stats'   => $stats,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CREATE — formulaire de création
    // ══════════════════════════════════════════════════════════════════════════

    public function create(Request $request): View
    {
        $this->authorize('objectifs.assigner');
        $role = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaCreateView($request),
            'DG'                           => $this->dgCreateView($request),
            'DGA'                          => $this->dgaCreateView($request),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefCreateView($request),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STORE — persiste la nouvelle fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $role = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaStore($request),
            'DG'                           => $this->dgStore($request),
            'DGA'                          => $this->dgaStore($request),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefStore($request),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SHOW — consulter une fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function show(Request $request, $fiche): View
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($fiche);
        $role  = Auth::user()->role;
        $route = (string) $request->route()->getName();

        return match (true) {
            $role === 'PCA'                                                     => $this->pcaShow($fiche),
            $role === 'DG' && !str_contains($route, 'sub-objectifs')           => $this->dgShow($fiche),
            $role === 'DGA' && str_contains($route, 'sub-objectifs')           => $this->dgaSubShow($fiche),
            in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)  => $this->dgaReceiveShow($fiche),
            $role === 'Directeur_Technique'                                     => $this->directeurShow($fiche),
            in_array($role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true) => $this->chefShow($fiche),
            default                                                             => $this->personnelShow($fiche),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EDIT — formulaire de modification (brouillon / contesté / refusée)
    // ══════════════════════════════════════════════════════════════════════════

    public function edit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche->load('objectifs');
        $role = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaEdit($fiche),
            'DG'                           => $this->dgEdit($fiche),
            'DGA'                          => $this->dgaEdit($fiche),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefEdit($fiche),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // UPDATE — sauvegarde les modifications
    // ══════════════════════════════════════════════════════════════════════════

    public function update(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche->load('objectifs');
        $role = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaUpdate($request, $fiche),
            'DG'                           => $this->dgUpdate($request, $fiche),
            'DGA'                          => $this->dgaUpdate($request, $fiche),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefUpdate($request, $fiche),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SOUMETTRE — brouillon → en_attente
    // ══════════════════════════════════════════════════════════════════════════

    public function soumettre(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $role = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaSoumettre($fiche),
            'DG'                           => $this->dgSoumettre($fiche),
            'DGA'                          => $this->dgaSoumettre($fiche),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefSoumettre($fiche),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // DESTROY — supprimer une fiche
    // ══════════════════════════════════════════════════════════════════════════

    public function destroy(Request $request, $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $fiche = FicheObjectif::findOrFail($fiche);
        $role  = Auth::user()->role;

        return match ($role) {
            'PCA'                          => $this->pcaDestroy($request, $fiche),
            'DG'                           => $this->dgDestroy($fiche),
            'DGA'                          => $this->dgaDestroy($fiche),
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'  => $this->chefDestroy($fiche),
            default                        => abort(403),
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STATUT — accepter / refuser une fiche reçue
    // ══════════════════════════════════════════════════════════════════════════

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $role = Auth::user()->role;

        // Acceptation par l'assignateur (DG accepte fiche reçue du PCA)
        if ($role === 'DG') {
            $this->authorize('objectifs.accepter');
            $request->validate(['statut' => ['required', 'in:acceptee,refusee']]);
            $fiche->statut = $request->statut;
            if ($request->statut === 'acceptee') {
                $fiche->date_validation = now()->toDateString();
            }
            $fiche->save();
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Statut mis à jour.');
        }

        // DGA, Assistante_Dg, Conseillers_Dg — reçoivent du DG
        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $this->authorizeSubordonneFiche($fiche);
            if (! in_array($fiche->statut ?? 'en_attente', ['en_attente', null], true)) {
                return back()->with('error', 'Cette fiche a déjà été traitée.');
            }
            $request->validate(['action' => ['required', 'in:accepter,refuser']]);
            $action = $request->input('action');
            $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
            if ($action === 'accepter') {
                $fiche->date_validation = now()->toDateString();
            }
            $fiche->save();

            $evalue    = Auth::user();
            $entite    = $this->getEntite();
            $dgUser    = $this->getDGUser($entite);
            $roleLabel = self::SUBORDONNE_ROLE_LABELS[$role] ?? $role;
            if ($dgUser) {
                $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
                Alerte::notifier(
                    $dgUser->id,
                    "Fiche d'objectifs {$actionLabel}e",
                    "{$roleLabel} {$evalue?->name} a {$actionLabel} la fiche « {$fiche->titre} ».",
                    $action === 'accepter' ? 'moyenne' : 'haute',
                    route('dg.objectifs.show', $fiche)
                );
            }

            $routePrefix = $this->espaceRoutePrefix();
            return redirect()->route("{$routePrefix}.objectifs.show", $fiche)
                ->with('status', $action === 'accepter' ? 'Fiche acceptée.' : 'Fiche refusée.');
        }

        // Directeur_Technique — reçoit du DGA/DG/PCA
        if ($role === 'Directeur_Technique') {
            $this->authorize('objectifs.accepter');
            $ctx = DirecteurEntity::resolveOrFail(Auth::user());
            if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
                abort(403);
            }
            if ($fiche->statut !== 'en_attente') {
                return back()->with('error', 'Cette fiche ne peut plus être modifiée.');
            }
            $request->validate(['action' => ['required', 'in:accepter,refuser']]);
            $action = $request->input('action');
            $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
            if ($action === 'accepter') {
                $fiche->date_validation = now()->toDateString();
            }
            $fiche->save();

            $evalue      = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            $roleLabel   = $ctx->getRoleLabel();
            $message     = "{$roleLabel} {$evalue?->name} a {$actionLabel} la fiche d'objectifs « {$fiche->titre} » que vous lui avez assignée.";
            $priorite    = $action === 'accepter' ? 'moyenne' : 'haute';

            if ($fiche->assignable_type === User::class) {
                foreach (User::where('role', 'DGA')->get() as $dga) {
                    Alerte::notifier($dga->id, "Fiche d'objectifs {$actionLabel}e", $message, $priorite,
                        route('dga.sub-objectifs.show', $fiche));
                }
            } else {
                foreach (User::where('role', 'DG')->get() as $dg) {
                    Alerte::notifier($dg->id, "Fiche d'objectifs {$actionLabel}e", $message, $priorite,
                        route('dg.objectifs.show', $fiche));
                }
            }

            $msg = $action === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';
            return redirect()->route('directeur.objectifs.show', $fiche)->with('status', $msg);
        }

        // Personnel (Agent, secrétaires, etc.) — reçoit de son chef/directeur
        $this->checkPersonnelOwnership($fiche);
        if (($fiche->statut ?? 'en_attente') !== 'en_attente') {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }
        $request->validate(['action' => ['required', 'in:accepter,refuser']]);
        $action = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        return redirect()->route('personnel.fiches.show', $fiche)
            ->with('status', $action === 'accepter' ? "Fiche d'objectifs acceptée." : "Fiche d'objectifs refusée.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AVANCEMENT — mettre à jour le pourcentage global
    // ══════════════════════════════════════════════════════════════════════════

    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $role = Auth::user()->role;

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $pct = (int) $request->avancement_percentage;

        if ($pct % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        if ($role === 'DG') {
            $this->objectifService->assertUserOwns($fiche, Auth::id());
            $this->objectifService->updateAvancement($fiche, $pct);
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
        }

        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $this->authorizeSubordonneFiche($fiche);
            $fiche->avancement_percentage = $pct;
            $fiche->save();
            $routePrefix = $this->espaceRoutePrefix();
            return redirect()->route("{$routePrefix}.objectifs.show", $fiche)->with('status', 'Avancement mis à jour.');
        }

        if ($role === 'Directeur_Technique') {
            $ctx = DirecteurEntity::resolveOrFail(Auth::user());
            if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
                abort(403);
            }
            $this->objectifService->updateAvancement($fiche, $pct);
            return redirect()->route('directeur.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
        }

        // Personnel
        $this->checkPersonnelOwnership($fiche);
        if ($fiche->statut !== 'acceptee') {
            return back()->with('error', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }
        $fiche->avancement_percentage = $pct;
        $fiche->save();
        return redirect()->route('personnel.fiches.show', $fiche)->with('status', 'Avancement mis à jour.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AVANCEMENT LIGNE — mettre à jour le pourcentage d'un objectif individuel
    // ══════════════════════════════════════════════════════════════════════════

    public function avancementLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $role  = Auth::user()->role;
        $fiche = FicheObjectif::findOrFail($ficheId);

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $val = (int) $request->avancement_percentage;
        if ($val % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        if ($role === 'DG') {
            if ($fiche->assignable_type !== User::class || (int) $fiche->assignable_id !== Auth::id()) {
                abort(403);
            }
            if ($fiche->statut !== 'acceptee') {
                return redirect()->route('dg.objectifs.show', $fiche)
                    ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['avancement_percentage' => $val]);
            $fiche->recalculateAvancement();
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
        }

        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $this->authorizeSubordonneFiche($fiche);
            if ($fiche->statut !== 'acceptee') {
                $routePrefix = $this->espaceRoutePrefix();
                return redirect()->route("{$routePrefix}.objectifs.show", $fiche)
                    ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['avancement_percentage' => $val]);
            $fiche->recalculateAvancement();
            $routePrefix = $this->espaceRoutePrefix();
            return redirect()->route("{$routePrefix}.objectifs.show", $fiche)->with('status', 'Avancement mis à jour.');
        }

        if ($role === 'Directeur_Technique') {
            $ctx = DirecteurEntity::resolveOrFail(Auth::user());
            if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
                abort(403);
            }
            if ($fiche->statut !== 'acceptee') {
                return redirect()->route('directeur.objectifs.show', $fiche)
                    ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['avancement_percentage' => $val]);
            $fiche->recalculateAvancement();
            return redirect()->route('directeur.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
        }

        // Personnel (identifiants via model binding ou integer)
        $ligne = $ligneId instanceof LigneFicheObjectif
            ? $ligneId
            : LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $this->checkPersonnelOwnership($fiche);
        if ($fiche->statut !== 'acceptee') {
            return back()->with('error', "L'avancement par objectif n'est disponible que sur une fiche acceptée.");
        }
        if ($ligne->fiche_objectif_id !== $fiche->id) {
            abort(403);
        }
        $ligne->update(['avancement_percentage' => $val]);
        $fiche->recalculateAvancement();
        return redirect()->route('personnel.fiches.show', $fiche)->with('status', 'Avancement mis à jour.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CONTESTER LIGNE — contester un objectif individuel
    // ══════════════════════════════════════════════════════════════════════════

    public function contesterLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $this->authorize('objectifs.contester');
        $role  = Auth::user()->role;
        $fiche = FicheObjectif::findOrFail($ficheId);

        if ($role === 'DG') {
            if ($fiche->assignable_type !== User::class || (int) $fiche->assignable_id !== Auth::id()) {
                abort(403);
            }
            if ($fiche->statut === 'acceptee') {
                return redirect()->route('dg.objectifs.show', $fiche)
                    ->with('status', 'Impossible de contester une fiche déjà acceptée.');
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['statut' => 'contesté']);
            $fiche->update(['statut' => 'contesté']);
            foreach (User::where('role', 'PCA')->get() as $pca) {
                Alerte::notifier($pca->id, 'Objectif contesté',
                    "Le DG a contesté un objectif dans la fiche « {$fiche->titre} ». Connectez-vous pour réviser.",
                    'haute',
                    route('pca.objectifs.show', $fiche));
            }
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Objectif contesté. Le PCA a été notifié.');
        }

        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $this->authorizeSubordonneFiche($fiche);
            if ($fiche->statut === 'acceptee') {
                $routePrefix = $this->espaceRoutePrefix();
                return redirect()->route("{$routePrefix}.objectifs.show", $fiche)
                    ->with('status', 'Impossible de contester une fiche déjà acceptée.');
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['statut' => 'contesté']);
            $fiche->update(['statut' => 'contesté']);
            $evalue   = Auth::user();
            $dgUsers  = User::where('role', 'DG')->get();
            foreach ($dgUsers as $dg) {
                Alerte::notifier($dg->id, 'Objectif contesté',
                    "{$evalue->name} a contesté un objectif dans la fiche « {$fiche->titre} ».",
                    'haute',
                    route('dg.objectifs.show', $fiche));
            }
            $routePrefix = $this->espaceRoutePrefix();
            return redirect()->route("{$routePrefix}.objectifs.show", $fiche)
                ->with('status', 'Objectif contesté. Le DG a été notifié.');
        }

        if ($role === 'Directeur_Technique') {
            $ctx = DirecteurEntity::resolveOrFail(Auth::user());
            if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
                abort(403);
            }
            if ($fiche->statut === 'acceptee') {
                return redirect()->route('directeur.objectifs.show', $fiche)
                    ->with('status', 'Impossible de contester une fiche déjà acceptée.');
            }
            $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
            $ligne->update(['statut' => 'contesté']);
            $fiche->update(['statut' => 'contesté']);
            $evalue    = Auth::user();
            $roleLabel = $ctx->getRoleLabel();
            $contMsg   = "{$roleLabel} {$evalue->name} a contesté un objectif dans la fiche « {$fiche->titre} ».";
            if ($fiche->assignable_type === User::class) {
                foreach (User::where('role', 'DGA')->get() as $dga) {
                    Alerte::notifier($dga->id, 'Objectif contesté', $contMsg, 'haute',
                        route('dga.sub-objectifs.show', $fiche));
                }
            } else {
                foreach (User::where('role', 'DG')->get() as $dg) {
                    Alerte::notifier($dg->id, 'Objectif contesté', $contMsg, 'haute',
                        route('dg.objectifs.show', $fiche));
                }
            }
            return redirect()->route('directeur.objectifs.show', $fiche)
                ->with('status', 'Objectif contesté. Votre supérieur hiérarchique a été notifié.');
        }

        // Personnel
        $ligne = $ligneId instanceof LigneFicheObjectif
            ? $ligneId
            : LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $this->checkPersonnelOwnership($fiche);
        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Impossible de contester un objectif sur une fiche déjà acceptée.');
        }
        if ($ligne->fiche_objectif_id !== $fiche->id) {
            abort(403);
        }
        $ligne->update(['statut' => 'contesté']);
        $fiche->update(['statut' => 'contesté']);

        $chefUser = null;
        if ($fiche->assignable_type === Agent::class) {
            $agent    = Agent::find($fiche->assignable_id);
            $service  = $agent?->service_id ? Service::find($agent->service_id) : null;
            $chefUser = $service?->chef_agent_id ? User::where('agent_id', $service->chef_agent_id)->first() : null;
        } elseif ($fiche->assignable_type === User::class) {
            $assignedUser = User::find($fiche->assignable_id);
            $agent        = $assignedUser?->agent_id ? Agent::find($assignedUser->agent_id) : null;
            $service      = $agent?->service_id ? Service::find($agent->service_id) : null;
            $chefUser     = $service?->chef_agent_id ? User::where('agent_id', $service->chef_agent_id)->first() : null;
        }
        if ($chefUser) {
            Alerte::notifier($chefUser->id, 'Objectif contesté',
                Auth::user()->name . " a contesté un objectif dans la fiche « {$fiche->titre} ».",
                'haute',
                route('chef.objectifs.show', $fiche));
        }
        return redirect()->route('personnel.fiches.show', $fiche)
            ->with('status', 'Objectif contesté. Votre supérieur hiérarchique a été notifié.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORT PDF — télécharger la fiche en PDF
    // ══════════════════════════════════════════════════════════════════════════

    public function exportPdf(Request $request, $fiche)
    {
        $fiche = FicheObjectif::with(['objectifs', 'assignable', 'annee'])->findOrFail($fiche);
        $role  = Auth::user()->role;
        $user  = Auth::user();

        if ($role === 'DG') {
            $this->authorize('objectifs.voir-equipe');
            $entite          = $this->getEntiteForDG();
            $nom             = strtolower(trim((string) ($entite?->nom ?? '')));
            $institutionSigle = ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
            $pdf = Pdf::loadView('pdf.contrat-objectif', [
                'contrat'                    => $fiche,
                'partieCollaborateur'        => (object) ['name' => $fiche->assignable?->name ?? '-', 'role' => self::SUBORDONNE_ROLE_LABELS[$fiche->assignable?->role ?? ''] ?? ($fiche->assignable?->role ?? '-')],
                'partieFaitiere'             => $entite,
                'partieFaitiereNomComplet'   => $user->name,
                'partieFaitiereRole'         => 'Directeur Général',
                'objectifs'                  => $fiche->objectifs,
                'dateDebut'                  => $fiche->date,
                'dateFin'                    => $fiche->date_echeance,
                'institution_sigle'          => $institutionSigle,
            ]);
            return $pdf->download('contrat-objectifs-' . $fiche->id . '.pdf');
        }

        // DGA exportant une fiche assignée à un subordonné (DT, secrétaire…)
        if ($role === 'DGA' && $request->routeIs('dga.sub-objectifs.pdf')) {
            $this->authorize('objectifs.voir-equipe');
            $entite           = $this->getEntite();
            $institutionSigle = $this->resolveInstitutionSigle($entite);
            $assignable       = $fiche->assignable;
            $assignableRole   = $assignable instanceof \App\Models\User
                ? (self::SUBORDONNE_ROLE_LABELS[$assignable->role ?? ''] ?? ($assignable->role ?? '-'))
                : '-';
            $pdf = Pdf::loadView('pdf.contrat-objectif', [
                'contrat'                    => $fiche,
                'partieCollaborateur'        => (object) ['name' => $assignable?->name ?? '-', 'role' => $assignableRole],
                'partieFaitiere'             => $entite,
                'partieFaitiereNomComplet'   => $user->name,
                'partieFaitiereRole'         => 'Directeur Général Adjoint',
                'objectifs'                  => $fiche->objectifs,
                'dateDebut'                  => $fiche->date,
                'dateFin'                    => $fiche->date_echeance,
                'institution_sigle'          => $institutionSigle,
            ]);
            return $pdf->download('contrat-objectifs-' . $fiche->id . '.pdf');
        }

        if (in_array($role, ['DGA', 'Assistante_Dg', 'Conseillers_Dg'], true)) {
            $this->authorizeSubordonneFiche($fiche);
            $entite          = $this->getEntite();
            $dgUser          = $this->getDGUser($entite);
            $institutionSigle = $this->resolveInstitutionSigle($entite);
            $pdf = Pdf::loadView('pdf.contrat-objectif', [
                'contrat'                    => $fiche,
                'partieCollaborateur'        => (object) ['name' => $user->name, 'role' => self::SUBORDONNE_ROLE_LABELS[$role] ?? $role],
                'partieFaitiere'             => $entite,
                'partieFaitiereNomComplet'   => $dgUser?->name ?? '',
                'partieFaitiereRole'         => 'Directeur Général',
                'objectifs'                  => $fiche->objectifs,
                'dateDebut'                  => $fiche->date,
                'dateFin'                    => $fiche->date_echeance,
                'institution_sigle'          => $institutionSigle,
            ]);
            return $pdf->download('contrat-objectifs-' . $fiche->id . '.pdf');
        }

        if ($role === 'Directeur_Technique') {
            $this->authorize('objectifs.voir-equipe');
            $ctx           = DirecteurEntity::resolveOrFail(Auth::user());
            if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
                abort(403);
            }
            $assigneNom    = $ctx->getDirecteurNomPrenom();
            $assigneRole   = $ctx->getRoleLabel();
            $pdf = Pdf::loadView('pdf.fiche-objectifs', [
                'fiche'         => $fiche,
                'assigneNom'    => $assigneNom,
                'assigneRole'   => $assigneRole,
                'assigneurNom'  => '-',
                'assigneurRole' => 'Supérieur hiérarchique',
            ])->setPaper('a4', 'portrait');
            return $pdf->download('fiche-objectifs-directeur-' . $fiche->id . '.pdf');
        }

        // Personnel
        $this->checkPersonnelOwnership($fiche);
        $roleLabels = [
            'DGA' => 'Directeur Général Adjoint', 'Directeur_Technique' => 'Directeur Technique',
            'Chef_Agence' => "Chef d'Agence", 'Chef_Guichet' => 'Chef de Guichet',
            'Assistante_Dg' => 'Assistante DG', 'Conseillers_Dg' => 'Conseiller DG',
            'Secretaire_Assistante' => 'Secrétaire',
        ];
        $pdf = Pdf::loadView('pdf.fiche-objectifs', [
            'fiche'         => $fiche,
            'assigneNom'    => $user->name ?? '-',
            'assigneRole'   => $roleLabels[$role ?? ''] ?? ($role ?? 'Personnel'),
            'assigneurNom'  => '-',
            'assigneurRole' => 'Supérieur hiérarchique',
        ])->setPaper('a4', 'portrait');
        return $pdf->download('fiche-objectifs-' . $fiche->id . '.pdf');
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
    // ADJUST PROGRESS — uniquement PCA (LigneFicheObjectif, pas +5%)
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

        return redirect()->route('chef.mon-espace')->with('status', $msg);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS — PCA
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

    private function pcaCreateView(Request $request): View
    {
        $dgUser = $this->getDGOfDirectionGenerale();
        return view('objectifs.create', [
            'layout'       => 'layouts.pca',
            'storeRoute'   => 'pca.objectifs.store',
            'backRoute'    => route('pca.objectifs.index'),
            'cibleLabel'   => $dgUser?->name ?? 'Directeur Général',
            'hiddenField'  => null,
            'oldObjectifs' => is_array(old('objectifs')) ? old('objectifs') : [''],
        ]);
    }

    private function pcaStore(Request $request): RedirectResponse
    {
        $dgUser = $this->getDGOfDirectionGenerale();
        $action = $request->input('action', 'soumettre');

        if (! $dgUser) {
            return redirect()->route('pca.objectifs.index')
                ->with('status', "Aucun compte DG n'est associé à la Direction Générale.");
        }

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $dgUser->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour le DG pour l\'année en cours.');
        }

        $statut = $action === 'brouillon' ? 'brouillon' : 'en_attente';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $dgUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => now()->endOfYear()->toDateString(),
            'avancement_percentage' => 0,
            'statut'                => $statut,
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if ($statut === 'brouillon') {
            return redirect()->route('pca.objectifs.show', $fiche)
                ->with('status', "Brouillon enregistré. Vous pouvez le modifier avant de l'envoyer au DG.");
        }

        $entite = Entite::find($request->user()->agent?->entite_id);
        if ($entite && $entite->directrice_generale_email) {
            $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
            Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
        }
        Alerte::notifier($dgUser->id, 'Nouvelle fiche d\'objectifs reçue',
            "Une fiche d'objectifs « {$fiche->titre} » vous a été assignée par le PCA. Consultez votre espace.", 'haute',
            route('dg.objectifs.show', $fiche));

        return redirect()->route('pca.objectifs.index')->with('status', "Fiche d'objectifs envoyée au DG avec succès.");
    }

    private function pcaShow(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $this->pcaAuthorizeFiche($fiche, (int) request()->user()->agent?->entite_id);
        return view('objectifs.show', [
            'layout'     => 'layouts.pca',
            'fiche'      => $fiche,
            'backRoute'  => route('pca.objectifs.index'),
            'pdfRoute'   => 'pca.objectifs.contrat.download',
            'editRoute'  => 'pca.objectifs.edit',
            'isAssignee' => false,
        ]);
    }

    private function pcaEdit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->pcaAuthorizeFiche($fiche, (int) request()->user()->agent?->entite_id);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }
        return view('objectifs.edit', [
            'layout'      => 'layouts.pca',
            'fiche'       => $fiche,
            'updateRoute' => 'pca.objectifs.update',
            'cancelUrl'   => route('pca.objectifs.show', $fiche),
            'cibleLabel'  => $this->getDGOfDirectionGenerale()?->name ?? 'Directeur Général',
        ]);
    }

    private function pcaUpdate(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->pcaAuthorizeFiche($fiche, (int) $request->user()->agent?->entite_id);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $dgUser = $this->getDGOfDirectionGenerale();
            $entite = Entite::find($request->user()->agent?->entite_id);
            if ($entite && $entite->directrice_generale_email) {
                $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
                Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
            }
            if ($dgUser) {
                Alerte::notifier($dgUser->id, 'Fiche d\'objectifs révisée',
                    "La fiche d'objectifs « {$fiche->titre} » a été révisée par le PCA. Consultez votre espace.", 'haute',
                    route('dg.objectifs.show', $fiche));
            }
            return redirect()->route('pca.objectifs.show', $fiche)
                ->with('status', $wasRefusee ? 'Fiche corrigée et renvoyée au DG.' : 'Fiche révisée et renvoyée au DG.');
        }

        if (! $wasContested && ! $wasRefusee && $action === 'envoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $dgUser = $this->getDGOfDirectionGenerale();
            $entite = Entite::find($request->user()->agent?->entite_id);
            if ($entite && $entite->directrice_generale_email) {
                $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
                Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
            }
            if ($dgUser) {
                Alerte::notifier($dgUser->id, 'Nouvelle fiche d\'objectifs reçue',
                    "Une fiche d'objectifs « {$fiche->titre} » vous a été assignée par le PCA.", 'haute',
                    route('dg.objectifs.show', $fiche));
            }
            return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Fiche transmise au DG.');
        }

        return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Brouillon mis à jour.');
    }

    private function pcaSoumettre(FicheObjectif $fiche): RedirectResponse
    {
        $entiteId = Auth::user()->agent?->entite_id;
        $this->pcaAuthorizeFiche($fiche, (int) $entiteId);

        if ($fiche->statut !== 'brouillon') {
            return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Cette fiche n\'est pas en brouillon.');
        }

        $dgUser = $this->getDGOfDirectionGenerale();
        $fiche->update(['statut' => 'en_attente']);

        $entite = Entite::find($entiteId);
        if ($entite && $entite->directrice_generale_email) {
            $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
            Mail::to($entite->directrice_generale_email)->send(new FicheObjectifAssigneeMail($fiche, $dgName));
        }
        if ($dgUser) {
            Alerte::notifier($dgUser->id, 'Nouvelle fiche d\'objectifs reçue',
                "Une fiche d'objectifs « {$fiche->titre} » vous a été assignée par le PCA.", 'haute',
                route('dg.objectifs.show', $fiche));
        }

        return redirect()->route('pca.objectifs.show', $fiche)->with('status', 'Fiche soumise au DG avec succès.');
    }

    private function pcaDestroy(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->pcaAuthorizeFiche($fiche, (int) $request->user()->agent?->entite_id);
        if (! in_array($fiche->statut, ['en_attente', 'brouillon', 'contesté', null], true)) {
            return redirect()->route('pca.objectifs.index')->with('status', 'Suppression impossible : fiche déjà validée ou refusée.');
        }
        $fiche->objectifs()->delete();
        $fiche->delete();
        return redirect()->route('pca.objectifs.index')->with('status', 'Fiche supprimée.');
    }

    private function buildPcaContratData(Request $request, FicheObjectif $objectif): array
    {
        $this->pcaAuthorizeFiche($objectif, $request->user()->agent?->entite_id);
        $objectif->load('objectifs', 'assignable');
        $assignable     = $objectif->assignable;
        $entite         = Entite::find($request->user()->agent?->entite_id) ?? Entite::query()->latest()->first();
        $salarieNom     = $assignable instanceof User ? ($assignable->name ?? '') : '';
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
        $fiche  = $objectif->ficheObjectif;
        $dgUser = $this->getDGOfDirectionGenerale();
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
    // HELPERS PRIVÉS — DG
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

    private function dgCreateView(Request $request): View
    {
        $subordonnes        = $this->getDgSubordonnes()->values();
        $requestedId        = (int) $request->integer('subordonne_id');
        $selectedSubordonne = $subordonnes->firstWhere('id', $requestedId);
        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }
        $hiddenField = $selectedSubordonne ? ['name' => 'subordonne_id', 'value' => $selectedSubordonne['id']] : null;
        return view('objectifs.create', [
            'layout'          => 'layouts.dg',
            'storeRoute'      => 'dg.objectifs.store',
            'backRoute'       => route('dg.mon-espace'),
            'cibleLabel'      => $selectedSubordonne ? $selectedSubordonne['nom'] : 'Choisir un subordonné',
            'hiddenField'     => $hiddenField,
            'subordonnes'     => $hiddenField ? null : $subordonnes,
            'subordonneField' => 'subordonne_id',
            'oldObjectifs'    => is_array(old('objectifs')) ? old('objectifs') : [''],
        ]);
    }

    private function dgStore(Request $request): RedirectResponse
    {
        $subordonnes     = $this->getDgSubordonnes()->values();
        $allowedIds      = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (blank($request->input('subordonne_id')) && count($allowedIds) === 1) {
            $request->merge(['subordonne_id' => $allowedIds[0]]);
        }

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer', Rule::in($allowedIds)],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, (int) $validated['subordonne_id'])) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour cette personne pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';
        $subordonne  = User::findOrFail($validated['subordonne_id']);

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $validated['subordonne_id'],
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                $this->ficheShowUrlForUser($subordonne, $fiche));
        }

        if ($isBrouillon) {
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', "Brouillon enregistré pour {$subordonne->name}.");
        }

        $redirect = match ($subordonne->role) {
            'DGA'           => route('dg.dga') . '?tab=objectifs',
            'Assistante_Dg' => route('dg.assistante') . '?tab=objectifs',
            default         => route('dg.conseillers.show', $subordonne) . '?tab=objectifs',
        };
        return redirect($redirect)->with('status', "Fiche d'objectifs assignée avec succès à {$subordonne->name}.");
    }

    private function dgShow(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        // Le DG peut être assignataire (reçoit la fiche de PCA)
        // ou assignateur (a créé la fiche pour DGA).
        $isDgReceiver = $fiche->assignable_type === \App\Models\User::class
            && (int) $fiche->assignable_id === auth()->id();
        return view('objectifs.show', [
            'layout'          => 'layouts.dg',
            'fiche'           => $fiche,
            'backRoute'       => route('dg.mon-espace'),
            'pdfRoute'        => 'dg.objectifs.pdf',
            'statusRoute'     => $isDgReceiver ? 'dg.objectifs.statut' : null,
            'avancementRoute' => $isDgReceiver ? 'dg.objectifs.lignes.avancement' : null,
            'contesterRoute'  => $isDgReceiver ? 'dg.objectifs.lignes.contester' : null,
            'editRoute'       => $isDgReceiver ? null : 'dg.objectifs.edit',
            'isAssignee'      => $isDgReceiver,
        ]);
    }

    private function dgEdit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->authorizeDgFicheAssignee($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }
        $subordonne = User::find($fiche->assignable_id);
        return view('objectifs.edit', [
            'layout'      => 'layouts.dg',
            'fiche'       => $fiche,
            'updateRoute' => 'dg.objectifs.update',
            'cancelUrl'   => route('dg.objectifs.show', $fiche),
            'cibleLabel'  => $subordonne?->name ?? '—',
        ]);
    }

    private function dgUpdate(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeDgFicheAssignee($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $subordonne = User::find($fiche->assignable_id);
            if ($subordonne) {
                Alerte::notifier($subordonne->id, 'Fiche d\'objectifs révisée',
                    "Le Directeur Général a révisé la fiche « {$fiche->titre} » suite à vos contestations.", 'haute',
                    $this->ficheShowUrlForUser($subordonne, $fiche));
            }
            return redirect()->route('dg.objectifs.show', $fiche)
                ->with('status', $wasRefusee ? 'Fiche corrigée et renvoyée.' : 'Fiche révisée et renvoyée.');
        }

        if (! $wasContested && ! $wasRefusee && $action === 'envoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $subordonne = User::find($fiche->assignable_id);
            if ($subordonne) {
                Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                    "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                    $this->ficheShowUrlForUser($subordonne, $fiche));
            }
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Fiche envoyée.');
        }

        return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Brouillon mis à jour.');
    }

    private function dgSoumettre(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeDgFicheAssignee($fiche);
        if ($fiche->statut !== 'brouillon') {
            return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Cette fiche n\'est pas en brouillon.');
        }
        $fiche->update(['statut' => 'en_attente']);
        $subordonne = User::find($fiche->assignable_id);
        if ($subordonne) {
            Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                $this->ficheShowUrlForUser($subordonne, $fiche));
        }
        return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Fiche soumise avec succès.');
    }

    private function dgDestroy(FicheObjectif $fiche): RedirectResponse
    {
        $fiche->delete();
        return redirect()->route('dg.mon-espace')->with('status', "Fiche d'objectifs supprimée.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS — DGA
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

    private function dgaCreateView(Request $request): View
    {
        $subordonnes        = $this->getDgaSubordonnes()->values();
        $requestedId        = (int) $request->integer('subordonne_id');
        $selectedSubordonne = $subordonnes->firstWhere('id', $requestedId);
        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }
        $hiddenField = $selectedSubordonne ? ['name' => 'subordonne_id', 'value' => $selectedSubordonne['id']] : null;
        return view('objectifs.create', [
            'layout'          => 'layouts.dga',
            'storeRoute'      => 'dga.sub-objectifs.store',
            'backRoute'       => route('dga.mon-espace'),
            'cibleLabel'      => $selectedSubordonne ? $selectedSubordonne['nom'] : 'Choisir un subordonné',
            'hiddenField'     => $hiddenField,
            'subordonnes'     => $hiddenField ? null : $subordonnes,
            'subordonneField' => 'subordonne_id',
            'oldObjectifs'    => is_array(old('objectifs')) ? old('objectifs') : [''],
        ]);
    }

    private function dgaStore(Request $request): RedirectResponse
    {
        $subordonnes = $this->getDgaSubordonnes()->values();
        $allowedIds  = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer', Rule::in($allowedIds)],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $subordonne = User::findOrFail($validated['subordonne_id']);

        try {
            $anneeId = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $subordonne->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour cette personne pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $subordonne->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                "Le DGA vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                $this->ficheShowUrlForUser($subordonne, $fiche));
        }

        if ($isBrouillon) {
            return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', "Brouillon enregistré pour {$subordonne->name}.");
        }

        return redirect()->route('dga.subordonnes.show', $subordonne->id)->with('status', "Fiche d'objectifs assignée avec succès.");
    }

    private function dgaSubShow(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $fiche->load(['objectifs', 'assignable']);
        return view('objectifs.show', [
            'layout'      => 'layouts.dga',
            'fiche'       => $fiche,
            'backRoute'   => route('dga.subordonnes.show', $fiche->assignable_id),
            'editRoute'   => 'dga.sub-objectifs.edit',
            'pdfRoute'    => 'dga.sub-objectifs.pdf',
            'isAssignee'  => false,
        ]);
    }

    private function dgaReceiveShow(FicheObjectif $fiche): View
    {
        $this->authorizeSubordonneFiche($fiche);
        $routePrefix = $this->espaceRoutePrefix(); // 'dga' ou 'subordonne'
        $layout      = 'layouts.' . $this->espaceViewPrefix();
        return view('objectifs.show', [
            'layout'          => $layout,
            'fiche'           => $fiche,
            'backRoute'       => route("{$routePrefix}.mon-espace"),
            'statusRoute'     => "{$routePrefix}.objectifs.statut",
            'avancementRoute' => "{$routePrefix}.objectifs.lignes.avancement",
            'contesterRoute'  => "{$routePrefix}.objectifs.lignes.contester",
            'pdfRoute'        => "{$routePrefix}.objectifs.pdf",
            'isAssignee'      => true,
        ]);
    }

    private function dgaEdit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->authorizeDgaFicheAssignee($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }
        $subordonne = User::find($fiche->assignable_id);
        return view('objectifs.edit', [
            'layout'      => 'layouts.dga',
            'fiche'       => $fiche,
            'updateRoute' => 'dga.sub-objectifs.update',
            'cancelUrl'   => route('dga.sub-objectifs.show', $fiche),
            'cibleLabel'  => $subordonne?->name ?? '—',
        ]);
    }

    private function dgaUpdate(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeDgaFicheAssignee($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $subordonne = User::find($fiche->assignable_id);
            if ($subordonne) {
                Alerte::notifier($subordonne->id, 'Fiche d\'objectifs révisée',
                    "Le DGA a révisé la fiche « {$fiche->titre} » suite à vos contestations.", 'haute',
                    $this->ficheShowUrlForUser($subordonne, $fiche));
            }
            return redirect()->route('dga.sub-objectifs.show', $fiche)
                ->with('status', $wasRefusee ? 'Fiche corrigée et renvoyée.' : 'Fiche révisée et renvoyée.');
        }

        if (! $wasContested && ! $wasRefusee && $action === 'envoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $subordonne = User::find($fiche->assignable_id);
            if ($subordonne) {
                Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                    "Le DGA vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                    $this->ficheShowUrlForUser($subordonne, $fiche));
            }
            return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Fiche envoyée.');
        }

        return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Brouillon mis à jour.');
    }

    private function dgaSoumettre(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeDgaFicheAssignee($fiche);
        if ($fiche->statut !== 'brouillon') {
            return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Cette fiche n\'est pas en brouillon.');
        }
        $fiche->update(['statut' => 'en_attente']);
        $subordonne = User::find($fiche->assignable_id);
        if ($subordonne) {
            Alerte::notifier($subordonne->id, 'Nouvelle fiche d\'objectifs reçue',
                "Le DGA vous a assigné une fiche d'objectifs « {$fiche->titre} ».", 'haute',
                $this->ficheShowUrlForUser($subordonne, $fiche));
        }
        return redirect()->route('dga.sub-objectifs.show', $fiche)->with('status', 'Fiche soumise avec succès.');
    }

    private function dgaDestroy(FicheObjectif $fiche): RedirectResponse
    {
        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Impossible de supprimer une fiche acceptée.');
        }
        $subordonneId = $fiche->assignable_type === User::class
            ? $fiche->assignable_id
            : User::where('agent_id', $fiche->assignable->directeur_agent_id ?? null)->value('id');
        $fiche->delete();
        return redirect()->route('dga.subordonnes.show', $subordonneId)->with('status', "Fiche d'objectifs supprimée.");
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS — Directeur
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

    private function directeurShow(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $ctx = DirecteurEntity::resolveOrFail(Auth::user());
        if (! $this->ficheAppartientAuDirecteur($fiche, $ctx)) {
            abort(403);
        }
        return view('objectifs.show', [
            'layout'          => 'layouts.directeur',
            'fiche'           => $fiche,
            'backRoute'       => route('directeur.mon-espace'),
            'statusRoute'     => 'directeur.objectifs.statut',
            'avancementRoute' => 'directeur.objectifs.lignes.avancement',
            'contesterRoute'  => 'directeur.objectifs.lignes.contester',
            'pdfRoute'        => 'directeur.objectifs.pdf',
            'isAssignee'      => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS — Chef
    // ══════════════════════════════════════════════════════════════════════════

    private function chefCreateView(Request $request): View
    {
        $ctx           = ChefEntity::resolveOrFail(Auth::user());
        $agents        = $ctx->getAgents();
        $preselectedId = (int) $request->get('agent_id', 0);
        $selectedAgent = $agents->firstWhere('id', $preselectedId);
        if (! $selectedAgent && $agents->count() === 1) {
            $selectedAgent = $agents->first();
        }
        $hiddenField   = $selectedAgent ? ['name' => 'agent_id', 'value' => $selectedAgent->id] : null;
        $agentOptions  = $agents->map(fn ($a) => [
            'id'         => $a->id,
            'nom'        => trim($a->prenom . ' ' . $a->nom),
            'role_label' => '',
        ])->values();
        return view('objectifs.create', [
            'layout'          => 'layouts.chef',
            'storeRoute'      => 'chef.objectifs.store',
            'backRoute'       => route('chef.mon-espace'),
            'cibleLabel'      => $selectedAgent ? trim($selectedAgent->prenom . ' ' . $selectedAgent->nom) : 'Choisir un agent',
            'hiddenField'     => $hiddenField,
            'subordonnes'     => $hiddenField ? null : $agentOptions,
            'subordonneField' => 'agent_id',
            'oldObjectifs'    => is_array(old('objectifs')) ? old('objectifs') : [''],
        ]);
    }

    private function chefStore(Request $request): RedirectResponse
    {
        $ctx      = ChefEntity::resolveOrFail(Auth::user());
        $agentIds = $ctx->getAgentIds();
        $user     = Auth::user();

        $validated = $request->validate([
            'agent_id'     => ['required', 'integer', 'in:' . implode(',', $agentIds ?: [0])],
            'titre_fiche'  => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date'],
            'objectifs'    => ['required', 'array', 'min:1'],
            'objectifs.*'  => ['required', 'string', 'max:500'],
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);
        if (! $ctx->agentOwnedBy($agent)) {
            abort(403, 'Cet agent n\'est pas sous votre responsabilité.');
        }

        $objectifsData = array_values(array_filter(
            array_map('trim', $validated['objectifs']),
            fn ($v) => $v !== ''
        ));

        if (empty($objectifsData)) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        try {
            $anneeId = Annee::resolveOpenYearId($validated['date_echeance']);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable) {
            $anneeId = null;
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        if (FicheObjectif::existsPourAnnee($anneeId, Agent::class, $agent->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour cet agent pour l\'année en cours.');
        }

        $fiche = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $agent, $anneeId, $objectifsData, $isBrouillon) {
            $fiche = FicheObjectif::create([
                'assignable_type'       => Agent::class,
                'assignable_id'         => $agent->id,
                'titre'                 => $validated['titre_fiche'],
                'annee_id'              => $anneeId,
                'date'                  => now()->toDateString(),
                'date_echeance'         => $validated['date_echeance'],
                'avancement_percentage' => 0,
                'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
            ]);
            foreach ($objectifsData as $desc) {
                LigneFicheObjectif::create(['fiche_objectif_id' => $fiche->id, 'description' => $desc]);
            }
            return $fiche;
        });

        if (! $isBrouillon) {
            $agentUser = User::where('agent_id', $agent->id)->first();
            if ($agentUser) {
                Alerte::notifier($agentUser->id, 'Nouvelle fiche d\'objectifs reçue',
                    "Votre chef {$user->name} vous a assigné une fiche d'objectifs : « {$fiche->titre} ».", 'moyenne',
                    route('personnel.fiches.show', $fiche));
            }
        }

        return redirect()->route('chef.objectifs.show', $fiche)
            ->with('status', $isBrouillon ? 'Brouillon enregistré.' : 'Fiche d\'objectifs créée et transmise à l\'agent.');
    }

    private function chefAuthorizeFiche(FicheObjectif $fiche): ChefEntity
    {
        $ctx = ChefEntity::resolveOrFail(Auth::user());
        if ($fiche->assignable_type === Agent::class) {
            $agent = Agent::find($fiche->assignable_id);
            if (! $agent || ! $ctx->agentOwnedBy($agent)) {
                abort(403, 'Cet agent n\'est pas sous votre responsabilité.');
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
                abort(403, 'Ce chef de guichet n\'est pas sous votre responsabilité.');
            }
            return $ctx;
        }
        abort(403, 'Type de fiche non reconnu.');
    }

    private function chefShow(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $this->chefAuthorizeFiche($fiche);
        $fiche->load(['objectifs', 'assignable']);
        return view('objectifs.show', [
            'layout'     => 'layouts.chef',
            'fiche'      => $fiche,
            'backRoute'  => route('chef.mon-espace'),
            'editRoute'  => 'chef.objectifs.edit',
            'isAssignee' => false,
        ]);
    }

    private function chefEdit(FicheObjectif $fiche): View|RedirectResponse
    {
        $this->chefAuthorizeFiche($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }
        $assignable = $fiche->assignable;
        $cibleLabel = $assignable instanceof Agent
            ? trim($assignable->prenom . ' ' . $assignable->nom)
            : ($assignable?->name ?? '—');
        return view('objectifs.edit', [
            'layout'      => 'layouts.chef',
            'fiche'       => $fiche,
            'updateRoute' => 'chef.objectifs.update',
            'cancelUrl'   => route('chef.objectifs.show', $fiche),
            'cibleLabel'  => $cibleLabel,
        ]);
    }

    private function chefUpdate(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $ctx = $this->chefAuthorizeFiche($fiche);
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:500'],
        ]);

        $objectifsData = array_values(array_filter(
            array_map('trim', $validated['objectifs']),
            fn ($v) => $v !== ''
        ));

        if (empty($objectifsData)) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifsData as $desc) {
            LigneFicheObjectif::create(['fiche_objectif_id' => $fiche->id, 'description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $agentUser = $fiche->assignable_type === User::class
                ? User::find($fiche->assignable_id)
                : User::where('agent_id', $fiche->assignable_id)->first();
            if ($agentUser) {
                Alerte::notifier($agentUser->id, 'Fiche d\'objectifs révisée',
                    "Votre chef a révisé la fiche d'objectifs « {$fiche->titre} » suite à vos contestations.", 'moyenne',
                    route('personnel.fiches.show', $fiche));
            }
            return redirect()->route('chef.objectifs.show', $fiche)
                ->with('status', $wasRefusee ? 'Fiche corrigée et renvoyée à l\'agent.' : 'Fiche révisée et renvoyée à l\'agent.');
        }

        if (! $wasContested && ! $wasRefusee && $action === 'envoyer') {
            $fiche->update(['statut' => 'en_attente']);
            $user      = Auth::user();
            $agentUser = $fiche->assignable_type === User::class
                ? User::find($fiche->assignable_id)
                : User::where('agent_id', $fiche->assignable_id)->first();
            if ($agentUser) {
                Alerte::notifier($agentUser->id, 'Nouvelle fiche d\'objectifs reçue',
                    "Votre chef {$user->name} vous a assigné une fiche d'objectifs : « {$fiche->titre} ».", 'moyenne',
                    route('personnel.fiches.show', $fiche));
            }
            return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Fiche envoyée à l\'agent.');
        }

        return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Brouillon mis à jour.');
    }

    private function chefSoumettre(FicheObjectif $fiche): RedirectResponse
    {
        $this->chefAuthorizeFiche($fiche);
        if ($fiche->statut !== 'brouillon') {
            return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Cette fiche n\'est pas en brouillon.');
        }
        $fiche->update(['statut' => 'en_attente']);
        $user      = Auth::user();
        $agentUser = $fiche->assignable_type === User::class
            ? User::find($fiche->assignable_id)
            : User::where('agent_id', $fiche->assignable_id)->first();
        if ($agentUser) {
            Alerte::notifier($agentUser->id, 'Nouvelle fiche d\'objectifs reçue',
                "Votre chef {$user->name} vous a assigné une fiche d'objectifs : « {$fiche->titre} ».", 'moyenne',
                route('personnel.fiches.show', $fiche));
        }
        return redirect()->route('chef.objectifs.show', $fiche)->with('status', 'Fiche soumise à l\'agent.');
    }

    private function chefDestroy(FicheObjectif $fiche): RedirectResponse
    {
        $this->chefAuthorizeFiche($fiche);
        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Une fiche d\'objectifs acceptée ne peut pas être supprimée.');
        }
        $fiche->delete();
        return redirect()->route('chef.mon-espace', ['tab' => 'agents'])->with('status', 'Fiche d\'objectifs supprimée.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PRIVÉS — Personnel
    // ══════════════════════════════════════════════════════════════════════════

    private function checkPersonnelOwnership(FicheObjectif $fiche): void
    {
        $user  = Auth::user();
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;
        $isForUser  = $fiche->assignable_type === User::class && (int) $fiche->assignable_id === $user->id;
        $isForAgent = $agent && $fiche->assignable_type === Agent::class && (int) $fiche->assignable_id === $agent->id;
        if (! $isForUser && ! $isForAgent) {
            abort(403, "Cette fiche d'objectifs ne vous est pas adressée.");
        }
    }

    private function personnelShow(FicheObjectif $fiche): View
    {
        $this->checkPersonnelOwnership($fiche);
        $fiche->load(['objectifs', 'annee']);
        return view('objectifs.show', [
            'layout'          => 'layouts.personnel',
            'fiche'           => $fiche,
            'backRoute'       => route('personnel.mon-espace'),
            'statusRoute'     => 'personnel.fiches.statut',
            'avancementRoute' => 'personnel.fiches.lignes.avancement',
            'contesterRoute'  => 'personnel.fiches.lignes.contester',
            'pdfRoute'        => 'personnel.fiches.pdf',
            'isAssignee'      => true,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS PARTAGÉS
    // ══════════════════════════════════════════════════════════════════════════

    private const SUBORDONNE_ROLE_LABELS = [
        'DGA'            => 'Directeur Général Adjoint',
        'Assistante_Dg'  => 'Assistante DG',
        'Conseillers_Dg' => 'Conseiller DG',
    ];

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

    private function resolveInstitutionSigle(?Entite $entite): string
    {
        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        return ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
    }

    /**
     * Retourne l'URL de consultation d'une fiche pour un destinataire donné
     * (fiche reçue par le destinataire depuis son supérieur hiérarchique).
     */
    private function ficheShowUrlForUser(User $user, FicheObjectif $fiche): string
    {
        return match($user->role) {
            'PCA'                                      => route('pca.objectifs.show', $fiche),
            'DG'                                       => route('dg.objectifs.show', $fiche),
            'DGA', 'Assistante_Dg', 'Conseillers_Dg'  => route('dga.objectifs.show', $fiche),
            'Directeur_Technique'                      => route('directeur.objectifs.show', $fiche),
            default                                    => route('personnel.fiches.show', $fiche),
        };
    }
}
