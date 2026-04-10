<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\FicheObjectifObjectif;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Annee;
use App\Models\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

    public function index(Request $request): View
    {
        $user = $request->user();
        $entiteId = $user->pca_entite_id;

        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $search = trim((string) $request->query('search', ''));

        $fiches = FicheObjectif::query()
            ->where(function ($q) use ($entiteId, $directionIds) {
                $q->where(function ($sub) use ($directionIds) {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                })->orWhere(function ($sub) use ($entiteId) {
                    $sub->where('assignable_type', Entite::class)
                        ->where('assignable_id', $entiteId);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('titre', 'like', "%{$search}%")
                        ->orWhere('annee', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('date')
            ->paginate(10)
            ->withQueryString();

        return view('pca.objectifs.index', [
            'fiches' => $fiches,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(Request $request): View
    {
        return view('pca.objectifs.create', [
            'assignmentOptions' => $this->assignmentOptions($request->user()->pca_entite_id),
            'today' => now()->toDateString(),
        ]);
    }

    public function show(Request $request, $id): View
    {
        $fiche = FicheObjectif::with('objectifs')->findOrFail($id);

        return view('pca.objectifs.show', [
            'fiche' => $fiche,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entiteId = $request->user()->pca_entite_id;
        $date = now()->toDateString();

        $validated = $request->validate([
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs' => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $fiche = FicheObjectif::create([
            'titre' => $validated['titre_fiche'],
            'annee' => date('Y'),
            'assignable_type' => Entite::class,
            'assignable_id' => $entiteId,
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

        return redirect()
            ->route('pca.objectifs.index')
            ->with('status', "Fiche d'objectifs creee avec succes pour le DG.");
    }

    public function adjustProgress(Request $request, FicheObjectifObjectif $objectif): RedirectResponse
    {
        $this->authorizeObjectif($objectif, $request->user()->pca_entite_id);

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

        if ($fiche->statut !== 'en_attente' && $fiche->statut !== null) {
            return redirect()->route('pca.objectifs.index')->with('status', 'Modification impossible : fiche deja validee ou refusee.');
        }

        return view('pca.objectifs.edit', [
            'fiche' => $fiche,
            'assignmentOptions' => $this->assignmentOptions($request->user()->pca_entite_id),
        ]);
    }

    private function authorizeObjectif(FicheObjectifObjectif $objectif, int $entiteId): void
    {
        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $fiche = $objectif->ficheObjectif;

        if (! $fiche) {
            abort(403);
        }

        $allowed = (
            ($fiche->assignable_type === Entite::class && (int) $fiche->assignable_id === $entiteId) ||
            ($fiche->assignable_type === Direction::class && in_array((int) $fiche->assignable_id, $directionIds, true))
        );

        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeFiche(FicheObjectif $fiche, int $entiteId): void
    {
        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $allowed = (
            ($fiche->assignable_type === Entite::class && (int) $fiche->assignable_id === $entiteId) ||
            ($fiche->assignable_type === Direction::class && in_array((int) $fiche->assignable_id, $directionIds, true))
        );

        if (! $allowed) {
            abort(403);
        }
    }

    /**
     * @return array<string, array<int, array{id:int,label:string}>>
     */
    private function assignmentOptions(int $entiteId): array
    {
        $entite = Entite::query()->findOrFail($entiteId);
        $directions = Direction::query()->where('entite_id', $entiteId)->orderBy('nom')->get();

        return [
            'entite' => [['id' => $entite->id, 'label' => $entite->nom]],
            'direction' => $directions->map(fn (Direction $d) => [
                'id' => $d->id,
                'label' => $d->nom.' ('.(($d->directeur_nom) ?: 'Directeur non renseigne').')',
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateObjectif(Request $request, int $entiteId): array
    {
        $validated = $request->validate([
            'assignable_type' => ['required', 'string', 'in:entite,direction'],
            'assignable_id' => ['required', 'integer'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'commentaire' => ['required', 'string', 'max:5000'],
        ]);

        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        if ($validated['assignable_type'] === 'entite' && (int) $validated['assignable_id'] !== $entiteId) {
            throw ValidationException::withMessages(['assignable_id' => 'Cible invalide.']);
        }

        if ($validated['assignable_type'] === 'direction' && ! in_array((int) $validated['assignable_id'], $directionIds, true)) {
            throw ValidationException::withMessages(['assignable_id' => 'Cible invalide.']);
        }

        $validated['assignable_class'] = $validated['assignable_type'] === 'entite' ? Entite::class : Direction::class;

        return $validated;
    }

    private function isLockedByEvaluation(FicheObjectifObjectif $objectif): bool
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
        $this->authorizeFiche($objectif, $request->user()->pca_entite_id);

        $objectif->load('objectifs', 'assignable');

        $assignable = $objectif->assignable;
        $entite = null;
        $salarieNom = '';
        $salarieFonction = '';

        if ($assignable instanceof Direction) {
            $entite = $assignable->entite;
            $salarieNom = trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? ''));
            $salarieFonction = 'Directeur General';
        } elseif ($assignable instanceof Entite) {
            $entite = $assignable;
            $salarieNom = trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? ''));
            $salarieFonction = 'Directeur General';
        }

        $entite ??= Entite::query()->findOrFail($request->user()->pca_entite_id);
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
