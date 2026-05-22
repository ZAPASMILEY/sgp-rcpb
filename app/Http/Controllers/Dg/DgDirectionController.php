<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;

class DgDirectionController extends Controller
{
    public function __construct(private readonly EvaluationService $evaluationService) {}

    // ── Helpers ────────────────────────────────────────────────────────────

    private function getEntiteId(): int
    {
        $dg = Auth::user();
        $entite = Entite::query()->where('dg_agent_id', $dg->agent_id)->first()
            ?? Entite::query()->latest()->first();
        return (int) ($entite?->id ?? 0);
    }

    private function getDirections(): \Illuminate\Support\Collection
    {
        // On exclut la direction dont le DG connecté est lui-même le directeur
        // (ex: "Direction Générale") — le DG ne peut pas s'auto-évaluer ici.
        $dgAgentId = Auth::user()->agent_id;

        return Direction::where('entite_id', $this->getEntiteId())
            ->when($dgAgentId, fn ($q) => $q->where(function ($q) use ($dgAgentId) {
                // Garder les directions sans directeur assigné OU dont le directeur
                // n'est pas le DG connecté.
                $q->whereNull('directeur_agent_id')
                  ->orWhere('directeur_agent_id', '!=', $dgAgentId);
            }))
            ->with(['directeur', 'services'])
            ->orderBy('nom')
            ->get();
    }

    private function authorizeDirection(Direction $direction): void
    {
        if ((int) $direction->entite_id !== $this->getEntiteId()) {
            abort(403);
        }
    }

    private function authorizeEvaluation(Evaluation $evaluation): Direction
    {
        if (
            $evaluation->evaluable_type !== Direction::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }

        $direction = Direction::find($evaluation->evaluable_id);
        if (! $direction || (int) $direction->entite_id !== $this->getEntiteId()) {
            abort(403);
        }

        return $direction;
    }

    private function authorizeObjectif(FicheObjectif $fiche): Direction
    {
        if ($fiche->assignable_type !== User::class) {
            abort(403);
        }

        $user = User::find($fiche->assignable_id);
        if (! $user || ! $user->agent_id) {
            abort(403);
        }

        // La fiche doit viser le directeur d'une direction rattachée à cette entité.
        $direction = Direction::where('entite_id', $this->getEntiteId())
            ->where('directeur_agent_id', $user->agent_id)
            ->first();

        if (! $direction) {
            abort(403);
        }

        return $direction;
    }

    // ── Index ──────────────────────────────────────────────────────────────

    public function index(): View
    {
        $this->authorize('evaluations.voir-reseau');
        $directions = $this->getDirections();

        return view('dg.directions.index', compact('directions'));
    }

    // ── Show direction ─────────────────────────────────────────────────────

    public function show(Request $request, Direction $direction): View
    {
        $this->authorize('evaluations.voir-reseau');
        $this->authorizeDirection($direction);

        $tab = $request->get('tab', 'evaluations');

        $evaluations = Evaluation::where('evaluable_type', Direction::class)
            ->where('evaluable_id', $direction->id)
            ->where(fn ($q) => $q->where('evaluable_role', 'manager')->orWhere('evaluable_role', 'Manager'))
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $direction->load('directeur');

        $directeurUser = $direction->directeur_agent_id
            ? User::where('agent_id', $direction->directeur_agent_id)->first()
            : null;

        $fiches = $directeurUser
            ? FicheObjectif::where('assignable_type', User::class)
                ->where('assignable_id', $directeurUser->id)
                ->withCount('objectifs')
                ->orderByDesc('date')
                ->get()
            : collect();

        return view('dg.directions.show', compact('direction', 'tab', 'evaluations', 'fiches'));
    }

    // ── Objectifs ──────────────────────────────────────────────────────────

    public function createObjectif(Request $request, Direction $direction): View
    {
        $this->authorize('objectifs.assigner');
        $this->authorizeDirection($direction);

        $direction->load('directeur');

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('dg.directions.objectifs.create', compact('direction', 'oldObjectifs'));
    }

    public function storeObjectif(Request $request): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $directionIds = Direction::where('entite_id', $this->getEntiteId())->pluck('id')->all();

        $validated = $request->validate([
            'direction_id'  => ['required', 'integer', 'in:'.implode(',', $directionIds ?: [0])],
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $direction = Direction::findOrFail($validated['direction_id']);

        // Résolution du directeur — on assigne à la PERSONNE, pas à la structure.
        $directeurUser = $direction->directeur_agent_id
            ? User::where('agent_id', $direction->directeur_agent_id)->first()
            : null;

        if (! $directeurUser) {
            return back()->withInput()
                ->with('error', "Aucun directeur avec un compte utilisateur n'est assigné à la direction « {$direction->nom} ».");
        }

        try {
            $anneeIdFiche = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeIdFiche, User::class, $directeurUser->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce directeur pour l\'année en cours.');
        }

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeIdFiche,
            'assignable_type'       => User::class,
            'assignable_id'         => $directeurUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        Alerte::notifier(
            $directeurUser->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
            'haute'
        );

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs assignée au directeur de « {$direction->nom} ».");
    }

    public function showObjectif(FicheObjectif $fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $direction = $this->authorizeObjectif($fiche);
        $fiche->load('objectifs');

        $statusClass = match ($fiche->statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($fiche->statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) ($fiche->statut ?? 'En attente')),
        };

        return view('dg.directions.objectifs.show', compact('fiche', 'direction', 'statusClass', 'statusLabel'));
    }

    public function destroyObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $direction = $this->authorizeObjectif($fiche);
        $fiche->delete();

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs'])
            ->with('status', 'Fiche d\'objectifs supprimée.');
    }

    // ── Évaluations ────────────────────────────────────────────────────────

    public function createEvaluation(Request $request, Direction $direction): View
    {
        $this->authorize('evaluations.creer');
        $this->authorizeDirection($direction);

        $direction->load('directeur');

        $today = now()->toDateString();
        $directeurUser = $direction->directeur_agent_id
            ? User::where('agent_id', $direction->directeur_agent_id)->first()
            : null;

        $fiches = $directeurUser
            ? FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
                ->where('assignable_type', User::class)
                ->where('assignable_id', $directeurUser->id)
                ->orderBy('titre')
                ->get()
            : collect();

        $objectiveOptions = $fiches->map(fn ($f) => [
            'id'            => $f->id,
            'titre'         => $f->titre,
            'date_echeance' => $f->date_echeance instanceof Carbon
                ? $f->date_echeance->toDateString()
                : (string) $f->date_echeance,
            'objectifs'     => $f->objectifs->map(fn ($item) => [
                'source_fiche_objectif_objectif_id' => $item->id,
                'titre'                             => $item->description,
            ])->values()->all(),
        ])->values()->all();

        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        $oldFormations  = old('identification.formations');
        $oldExperiences = old('identification.experiences');

        $entiteNom        = \App\Models\Entite::find($this->getEntiteId())?->nom ?? '';
        $prefilledAgentId = $direction->directeur_agent_id;

        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();

        return view('dg.directions.evaluations.create', compact(
            'direction',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
            'entiteNom',
            'prefilledAgentId',
            'openAnnee',
            'openSemestres',
            'openSemestre',
        ));
    }

    public function storeEvaluation(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $user         = Auth::user();
        $directionIds = Direction::where('entite_id', $this->getEntiteId())->pluck('id')->all();

        $validated = $request->validate([
            'direction_id'                     => ['required', 'integer', 'in:'.implode(',', $directionIds ?: [0])],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.grade'             => ['required', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'        => ['nullable', 'array'],
            'identification.formations.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine' => ['nullable', 'string', 'max:255'],
            'identification.experiences'       => ['nullable', 'array'],
            'identification.experiences.*.periode'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'        => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
            'subjective_criteres'              => ['required', 'array', 'min:1'],
            'objective_criteres'               => ['required', 'array', 'min:1'],
            'points_a_ameliorer'               => ['nullable', 'string'],
            'strategies_amelioration'          => ['nullable', 'string'],
            'commentaire'                      => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom'             => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom'         => ['nullable', 'string', 'max:255'],
            'date_signature_evalue'            => ['nullable', 'date'],
            'date_signature_evaluateur'        => ['nullable', 'date'],
        ]);

        $direction = Direction::findOrFail($validated['direction_id']);

        $openAnnee = Annee::currentOpen();
        if (! $openAnnee) {
            return back()->withInput()->with('error', "Aucune année d'exercice ouverte.");
        }
        $semestre = $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first();
        if (! $semestre) {
            return back()->withInput()->with('error', "Aucun semestre ouvert pour {$openAnnee->annee}.");
        }
        $dateDebut  = $semestre->dateDebut()->toDateString();
        $dateFin    = $semestre->dateFin()->toDateString();
        $anneeId    = $openAnnee->id;
        $semestreId = $semestre->id;

        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        $normalizedSubjective = $this->evaluationService->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->evaluationService->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.']);
        }

        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);

        $evaluation = DB::transaction(function () use (
            $user, $direction, $dateDebut, $dateFin, $anneeId, $semestreId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => Direction::class,
                'evaluable_id'              => $direction->id,
                'evaluable_role'            => 'manager',
                'annee_id'                  => $anneeId,
                'semestre_id'               => $semestreId,
                'evaluateur_id'             => $user->id,
                'date_debut'                => $dateDebut,
                'date_fin'                  => $dateFin,
                'moyenne_subjectifs'        => $scores['moyenne_subjectifs'],
                'note_criteres_subjectifs'  => $scores['note_criteres_subjectifs'],
                'moyenne_objectifs'         => $scores['moyenne_objectifs'],
                'note_criteres_objectifs'   => $scores['note_criteres_objectifs'],
                'note_finale'               => $scores['note_finale'],
                'commentaire'               => $validated['commentaire'] ?? null,
                'points_a_ameliorer'        => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration'   => $validated['strategies_amelioration'] ?? null,
                'signature_evalue_nom'      => $validated['signature_evalue_nom'] ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom'  => $validated['signature_evaluateur_nom'] ?? $user->name,
                'date_signature_evalue'     => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
                'statut'                    => 'brouillon',
            ]);

            $evaluation->identification()->create($identification);
            $this->evaluationService->persistCriteria($evaluation, array_merge($normalizedSubjective, $normalizedObjective));

            return $evaluation;
        });

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', "Évaluation créée pour la direction « {$direction->nom} ».");
    }

    public function showEvaluation(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.voir-reseau');
        $direction = $this->authorizeEvaluation($evaluation);
        $direction->load('directeur');
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $ident   = $evaluation->identification;

        $statusClass = match ($evaluation->statut) {
            'valide'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis'    => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse'    => 'border-rose-200 bg-rose-50 text-rose-700',
            'brouillon' => 'border-slate-200 bg-slate-100 text-slate-700',
            default     => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide'    => 'Acceptée',
            'soumis'    => 'Soumise',
            'refuse'    => 'Refusée',
            'brouillon' => 'Brouillon',
            default     => ucfirst((string) $evaluation->statut),
        };

        return view('dg.directions.evaluations.show', compact(
            'evaluation',
            'direction',
            'objectiveCriteria',
            'subjectiveCriteria',
            'note',
            'mention',
            'ident',
            'statusClass',
            'statusLabel',
        ));
    }

    public function submitEvaluation(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $direction = $this->authorizeEvaluation($evaluation);

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        // Notifier le directeur
        if ($direction->directeur_agent_id) {
            $directeurUser = User::where('agent_id', $direction->directeur_agent_id)->first();
            if ($directeurUser) {
                Alerte::notifier(
                    $directeurUser->id,
                    'Nouvelle fiche d\'évaluation reçue',
                    'Le Directeur Général vous a soumis une fiche d\'évaluation. Connectez-vous pour la consulter.',
                    'haute'
                );
            }
        }

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', 'Évaluation soumise au directeur.');
    }

    public function destroyEvaluation(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $direction = $this->authorizeEvaluation($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()
            ->route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations'])
            ->with('status', 'Évaluation supprimée.');
    }

    public function exportEvaluationPdf(Evaluation $evaluation)
    {
        $this->authorize('evaluations.exporter-pdf');
        $direction = $this->authorizeEvaluation($evaluation);
        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note       = (float) $evaluation->note_finale;
        $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $direction->loadMissing('directeur');
        $directeurNom = $direction->directeur ? trim($direction->directeur->prenom.' '.$direction->directeur->nom) : '';
        $cibleLabel = $evaluation->identification?->nom_prenom ?? ($directeurNom ?: $direction->nom);
        $cibleType  = 'Directeur — '.$direction->nom;

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-direction-'.$evaluation->id.'.pdf');
    }

}
