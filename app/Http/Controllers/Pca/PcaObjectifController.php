<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\FicheObjectif;
use App\Models\LigneFicheObjectif;
use App\Models\Entite;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\FicheObjectifAssigneeMail;
use Illuminate\Support\Facades\Mail;

class PcaObjectifController extends Controller
{
    /**
     * Affiche le contrat d'objectifs avant telechargement.
     */
    public function contrat(Request $request, FicheObjectif $objectif): View
    {
        return view('pca.objectifs.contrat', $this->buildContratData($request, $objectif));
    }

    /**
     * Genere et telecharge le contrat d'objectifs en PDF.
     */
    public function contratDownload(Request $request, FicheObjectif $objectif): Response
    {
        $pdf = Pdf::loadView('pdf.contrat-objectif', $this->buildContratData($request, $objectif));

        return $pdf->download('contrat-objectif-'.$objectif->id.'.pdf');
    }

    private function getDirectionGeneraleDirection(): ?\App\Models\Direction
    {
        $entite = \App\Models\Entite::query()->latest()->first();
        if (!$entite) {
            return null;
        }
        return \App\Models\Direction::query()
            ->where('nom', 'Direction Générale')
            ->where('entite_id', $entite->id)
            ->first();
    }

    private function getDGOfDirectionGenerale(): ?User
    {
        $entite = \App\Models\Entite::query()->latest()->first();
        if (!$entite || !$entite->dg_agent_id) {
            return null;
        }
        return User::query()
            ->where('role', 'DG')
            ->where('agent_id', $entite->dg_agent_id)
            ->first();
    }

    public function index(Request $request): View
    {
        $dgUser = $this->getDGOfDirectionGenerale();
        $search = trim((string) $request->query('search', ''));

        $baseQuery = FicheObjectif::query()
            ->with('assignable')
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->when($dgUser, fn ($query) => $query->where('assignable_id', $dgUser->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('titre', 'like', "%{$search}%")
                        ->orWhere('annee', 'like', "%{$search}%");
                });
            });

        $fiches = (clone $baseQuery)
            ->orderByDesc('date')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'acceptees' => (clone $baseQuery)->where('statut', 'acceptee')->count(),
            'en_attente' => (clone $baseQuery)->where(function ($query) {
                $query->where('statut', 'en_attente')
                    ->orWhereNull('statut');
            })->count(),
            'refusees' => (clone $baseQuery)->where('statut', 'refusee')->count(),
        ];

        return view('pca.objectifs.index', [
            'fiches' => $fiches,
            'filters' => ['search' => $search],
            'stats' => $stats,
        ]);
    }

    public function create(Request $request): View
    {
        $dgUser = $this->getDGOfDirectionGenerale();
        return view('pca.objectifs.create', [
            'dgUser' => $dgUser,
            'today' => now()->toDateString(),
        ]);
    }

    public function show(Request $request, $id): View
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($id);
        $this->authorizeFiche($fiche, (int) $request->user()->agent?->entite_id);

        return view('pca.objectifs.show', [
            'fiche' => $fiche,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entiteId = $request->user()->agent?->entite_id;
        $date = now()->toDateString();
        $dgUser = $this->getDGOfDirectionGenerale();

        if (! $dgUser) {
            return redirect()
                ->route('pca.objectifs.index')
                ->with('status', "Aucun compte DG n'est associe a la Direction Générale.");
        }

        $validated = $request->validate([
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs' => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $fiche = FicheObjectif::create([
            'titre' => $validated['titre_fiche'],
            'annee_id' => Annee::resolveIdForDate(now()),
            'assignable_type' => User::class,
            'assignable_id' => $dgUser->id,
            'date' => $date,
            'date_echeance' => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut' => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $objectifDesc) {
            $fiche->objectifs()->create([
                'description' => $objectifDesc,
            ]);
        }

        // Envoi du mail au DG après création de la fiche
        $entite = Entite::find($entiteId);
        if ($entite && $entite->directrice_generale_email) {
            $dgName = trim(($entite->directrice_generale_prenom ?? '') . ' ' . ($entite->directrice_generale_nom ?? ''));
            Mail::to($entite->directrice_generale_email)
                ->send(new FicheObjectifAssigneeMail($fiche, $dgName));
        }

        // Notification in-app au DG
        if ($dgUser) {
            Alerte::notifier(
                $dgUser->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Une fiche d'objectifs « {$fiche->titre} » vous a été assignée par le PCA. Consultez votre espace pour l'examiner.",
                'haute'
            );
        }

        return redirect()
            ->route('pca.objectifs.index')
            ->with('status', "Fiche d'objectifs creee avec succes pour le DG.");
    }

    public function adjustProgress(Request $request, LigneFicheObjectif $objectif): RedirectResponse
    {
        $this->authorizeObjectif($objectif, $request->user()->agent?->entite_id);

        $validated = $request->validate([
            'direction' => ['required', 'string', 'in:up,down'],
        ]);

        if (Carbon::parse($objectif->date_echeance)->isBefore(today())) {
            return redirect()
                ->route('pca.objectifs.index')
                ->with('status', "L'echeance est depassee. L'avancement de cet objectif ne peut plus etre modifie.");
        }

        if ($this->isLockedByEvaluation($objectif)) {
            return redirect()
                ->route('pca.objectifs.index')
                ->with('status', 'Avancement verrouille: la cible a deja ete evaluee pour la periode contenant cette echeance.');
        }

        $step = 10;
        $current = (int) $objectif->avancement_percentage;
        $next = $validated['direction'] === 'up'
            ? min(100, $current + $step)
            : max(0, $current - $step);

        $objectif->update(['avancement_percentage' => $next]);

        return redirect()
            ->route('pca.objectifs.index')
            ->with('status', 'Avancement mis a jour a '.$next.'%.');
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        $fiche = FicheObjectif::findOrFail($id);
        $this->authorizeFiche($fiche, (int) $request->user()->agent?->entite_id);

        if ($fiche->statut !== 'en_attente' && $fiche->statut !== null) {
            return redirect()->route('pca.objectifs.index')->with('status', 'Suppression impossible : fiche deja validee ou refusee.');
        }

        $fiche->objectifs()->delete();
        $fiche->delete();

        return redirect()->route('pca.objectifs.index')->with('status', 'Fiche supprimee.');
    }

    public function edit(Request $request, $id): View|RedirectResponse
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($id);
        $this->authorizeFiche($fiche, (int) $request->user()->agent?->entite_id);

        if ($fiche->statut !== 'en_attente' && $fiche->statut !== null) {
            return redirect()->route('pca.objectifs.index')->with('status', 'Modification impossible : fiche deja validee ou refusee.');
        }

        return redirect()
            ->route('pca.objectifs.show', $fiche)
            ->with('status', 'La modification directe de cette fiche n\'est pas disponible.');
    }

    private function authorizeObjectif(LigneFicheObjectif $objectif, int $entiteId): void
    {
        $fiche = $objectif->ficheObjectif;
        $dgUser = $this->getDGOfDirectionGenerale();

        if (! $fiche) {
            abort(403);
        }

        $allowed = $dgUser
            && $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === (int) $dgUser->id;

        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeFiche(FicheObjectif $fiche, int $entiteId): void
    {
        $dgUser = $this->getDGOfDirectionGenerale();

        $allowed = $dgUser
            && $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === (int) $dgUser->id;

        if (! $allowed) {
            abort(403);
        }
    }

    private function isLockedByEvaluation(LigneFicheObjectif $objectif): bool
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

    /**
     * @return array<string, mixed>
     */
    private function buildContratData(Request $request, FicheObjectif $objectif): array
    {
        $this->authorizeFiche($objectif, $request->user()->agent?->entite_id);

        $objectif->load('objectifs', 'assignable');

        $assignable = $objectif->assignable;
        $entite = null;
        $salarieNom = '';
        $salarieFonction = '';

        if ($assignable instanceof User) {
            $entite = Entite::query()->find($request->user()->agent?->entite_id);
            $salarieNom = $assignable->name ?? '';
            $salarieFonction = 'Directeur General';
        }

        $entite ??= Entite::query()->findOrFail($request->user()->agent?->entite_id);
        $institutionSigle = $this->resolveInstitutionSigle($entite);

        return [
            'contrat' => $objectif,
            'partieCollaborateur' => (object) [
                'name' => $salarieNom !== '' ? $salarieNom : ($assignable->nom ?? 'Collaborateur'),
                'role' => $salarieFonction,
            ],
            'partieFaitiere' => $entite,
            'partieFaitiereNomComplet' => trim(($entite->pca_prenom ?? '').' '.($entite->pca_nom ?? '')),
            'objectifs' => $objectif->objectifs,
            'dateDebut' => $objectif->date,
            'dateFin' => $objectif->date_echeance,
            'salarie_nom' => $salarieNom,
            'salarie_fonction' => $salarieFonction,
            'institution_representant' => trim(($entite->pca_prenom ?? '').' '.($entite->pca_nom ?? '')),
            'institution_fonction' => "President du Conseil d'Administration",
            'institution_sigle' => $institutionSigle,
            'date_debut' => $objectif->date,
            'date_fin' => $objectif->date_echeance,
        ];
    }

    private function resolveInstitutionSigle(Entite $entite): string
    {
        $nom = strtolower(trim((string) $entite->nom));

        if ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) {
            return 'FCPB';
        }

        return 'RCPB';
    }


}
