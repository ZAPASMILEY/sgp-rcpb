<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\SubjectiveCriteriaTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PcaEvaluationController extends Controller
{
    /** @var array<string, array{class: class-string, role: string}> */
    private const TARGET_MAP = [
        'entite' => ['class' => Entite::class, 'role' => 'entity'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $entiteId = $user->pca_entite_id;

        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $evaluations = Evaluation::query()
            ->with(['evaluable', 'evaluateur'])
            ->where('evaluable_type', Entite::class)
            ->where('evaluable_id', $entiteId)
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHasMorph('evaluable', [Entite::class], fn ($q) => $q->where('nom', 'like', "%{$search}%"));
            })
            ->when($statut !== '', fn ($query) => $query->where('statut', $statut))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pca.evaluations.index', [
            'evaluations' => $evaluations,
            'filters' => ['search' => $search, 'statut' => $statut],
        ]);
    }

    public function create(Request $request): View
    {
        $entiteId = $request->user()->pca_entite_id;

        return view('pca.evaluations.create', [
            'assignmentOptions' => $this->buildAssignmentOptions($entiteId),
            'targetProfiles' => $this->buildTargetProfiles($entiteId),
            'objectiveOptions' => $this->buildObjectiveOptions($entiteId),
            'subjectiveTemplates' => $this->buildSubjectiveTemplates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entiteId = $request->user()->pca_entite_id;

        $validated = $request->validate([
            'evaluable_type' => ['required', 'string', 'in:entite'],
            'evaluable_id' => ['required'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'identification.nom_prenom' => ['nullable', 'string', 'max:255'],
            'identification.semestre' => ['nullable', 'string', 'max:20'],
            'identification.date_recrutement' => ['nullable', 'date'],
            'identification.date_evaluation' => ['nullable', 'date'],
            'identification.date_titularisation' => ['nullable', 'date'],
            'identification.matricule' => ['nullable', 'string', 'max:255'],
            'identification.poste' => ['nullable', 'string', 'max:255'],
            'identification.emploi' => ['nullable', 'string', 'max:255'],
            'identification.niveau' => ['nullable', 'string', 'max:255'],
            'identification.date_naissance' => ['nullable', 'date'],
            'identification.direction' => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.date_confirmation' => ['nullable', 'date'],
            'identification.categorie' => ['nullable', 'string', 'max:255'],
            'identification.anciennete' => ['nullable', 'string', 'max:255'],
            'identification.sexe' => ['nullable', 'string', 'max:1'],
            'identification.date_affectation' => ['nullable', 'date'],
            'identification.formations' => ['nullable', 'array'],
            'identification.formations.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine' => ['nullable', 'string', 'max:255'],
            'identification.experiences' => ['nullable', 'array'],
            'identification.experiences.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste' => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
            'subjective_criteres' => ['required', 'array', 'min:1'],
            'objective_criteres' => ['required', 'array', 'min:1'],
            'points_a_ameliorer' => ['nullable', 'string'],
            'strategies_amelioration' => ['nullable', 'string'],
            'commentaires_evalue' => ['nullable', 'string'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom' => ['nullable', 'string', 'max:255'],
            'signature_directeur_nom' => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom' => ['nullable', 'string', 'max:255'],
            'date_signature_evalue' => ['nullable', 'date'],
            'date_signature_directeur' => ['nullable', 'date'],
            'date_signature_evaluateur' => ['nullable', 'date'],
        ]);

        $targetConfig = self::TARGET_MAP[$validated['evaluable_type']];
        $targetId = (int) $validated['evaluable_id'];
        $this->authorizeTarget($validated['evaluable_type'], $targetId, $entiteId);

        $normalizedSubjective = $this->normalizeCriteria(
            (array) $request->input('subjective_criteres', []),
            'subjectif',
            1,
            5
        );
        $normalizedObjective = $this->normalizeCriteria(
            (array) $request->input('objective_criteres', []),
            'objectif',
            1,
            5
        );

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => "Les criteres subjectifs et objectifs doivent contenir au moins une ligne notee.",
            ]);
        }

        $scores = $this->computeScores($normalizedSubjective, $normalizedObjective);

        $evaluation = DB::transaction(function () use ($request, $validated, $targetConfig, $targetId, $normalizedSubjective, $normalizedObjective, $scores) {
            $evaluation = Evaluation::create([
                'evaluable_type' => $targetConfig['class'],
                'evaluable_id' => $targetId,
                'evaluable_role' => $targetConfig['role'],
                'annee_id' => Annee::resolveIdForDate($validated['date_debut']),
                'evaluateur_id' => $request->user()->id,
                'date_debut' => $validated['date_debut'],
                'date_fin' => $validated['date_fin'],
                'moyenne_subjectifs' => $scores['moyenne_subjectifs'],
                'note_criteres_subjectifs' => $scores['note_criteres_subjectifs'],
                'moyenne_objectifs' => $scores['moyenne_objectifs'],
                'note_criteres_objectifs' => $scores['note_criteres_objectifs'],
                'note_objectifs' => (int) round(($scores['moyenne_objectifs'] / 5) * 100),
                'note_manuelle' => null,
                'note_finale' => $scores['note_finale'],
                'commentaire' => $validated['commentaire'] ?? null,
                'points_a_ameliorer' => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration' => $validated['strategies_amelioration'] ?? null,
                'commentaires_evalue' => $validated['commentaires_evalue'] ?? null,
                'signature_evalue_nom' => $validated['signature_evalue_nom'] ?? ($validated['identification']['nom_prenom'] ?? null),
                'signature_directeur_nom' => null,
                'signature_evaluateur_nom' => $validated['signature_evaluateur_nom'] ?? ($request->user()->name ?? null),
                'date_signature_evalue' => $validated['date_signature_evalue'] ?? null,
                'date_signature_directeur' => null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
                'statut' => 'brouillon',
            ]);

            $identification = $validated['identification'] ?? [];
            $identification['formations'] = collect($identification['formations'] ?? [])
                ->map(fn ($row) => [
                    'periode' => trim((string) ($row['periode'] ?? '')),
                    'libelle' => trim((string) ($row['libelle'] ?? '')),
                    'domaine' => trim((string) ($row['domaine'] ?? '')),
                ])
                ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
                ->values()
                ->all();
            $identification['experiences'] = collect($identification['experiences'] ?? [])
                ->map(fn ($row) => [
                    'periode' => trim((string) ($row['periode'] ?? '')),
                    'poste' => trim((string) ($row['poste'] ?? '')),
                    'observations' => trim((string) ($row['observations'] ?? '')),
                ])
                ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
                ->values()
                ->all();

            $evaluation->identification()->create($identification);

            foreach (array_merge($normalizedSubjective, $normalizedObjective) as $criterion) {
                $critere = $evaluation->criteres()->create([
                    'type' => $criterion['type'],
                    'ordre' => $criterion['ordre'],
                    'titre' => $criterion['titre'],
                    'description' => $criterion['description'],
                    'note_globale' => $criterion['note_globale'],
                    'observation' => $criterion['observation'],
                    'source_template_id' => $criterion['source_template_id'],
                    'source_fiche_objectif_id' => $criterion['source_fiche_objectif_id'],
                    'source_fiche_objectif_objectif_id' => $criterion['source_fiche_objectif_objectif_id'],
                ]);

                foreach ($criterion['subcriteria'] as $subcriterion) {
                    $critere->sousCriteres()->create([
                        'ordre' => $subcriterion['ordre'],
                        'libelle' => $subcriterion['libelle'],
                        'note' => $subcriterion['note'],
                        'observation' => $subcriterion['observation'],
                    ]);
                }
            }

            return $evaluation;
        });

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation creee avec succes.');
    }

    public function show(Request $request, Evaluation $evaluation): View
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        $evaluation->load([
            'evaluable',
            'evaluateur',
            'identification',
            'criteres.sousCriteres',
        ]);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $mention = $this->mentionFromScore((float) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable, $evaluation->evaluable_role ?? 'entity');
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type, $evaluation->evaluable_role ?? 'entity');

        return view('pca.evaluations.show', compact(
            'evaluation',
            'subjectiveCriteria',
            'objectiveCriteria',
            'mention',
            'cibleLabel',
            'cibleType'
        ));
    }

    public function exportPdf(Request $request, Evaluation $evaluation): Response
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        $evaluation->load([
            'evaluable',
            'evaluateur',
            'identification',
            'criteres.sousCriteres',
        ]);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $mention = $this->mentionFromScore((float) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable, $evaluation->evaluable_role ?? 'entity');
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type, $evaluation->evaluable_role ?? 'entity');

        $pdf = Pdf::loadView('pca.evaluations.pdf', compact(
            'evaluation',
            'subjectiveCriteria',
            'objectiveCriteria',
            'mention',
            'cibleLabel',
            'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'.pdf');
    }

    public function submit(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('pca.evaluations.show', $evaluation)
                ->with('status', 'Cette evaluation a deja ete soumise ou validee.');
        }

        $evaluation->update(['statut' => 'soumis']);

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation soumise avec succes.');
    }

    public function approve(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        if ($evaluation->statut !== 'soumis') {
            return redirect()->route('pca.evaluations.show', $evaluation)
                ->with('status', 'Seule une evaluation soumise peut etre validee.');
        }

        $evaluation->update(['statut' => 'valide']);

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation validee avec succes.');
    }

    public function destroy(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        if ($evaluation->statut === 'valide') {
            return redirect()->route('pca.evaluations.index')
                ->with('status', 'Une evaluation validee ne peut pas etre supprimee.');
        }

        $evaluation->delete();

        return redirect()->route('pca.evaluations.index')
            ->with('status', 'Evaluation supprimee.');
    }

    private function authorizeEvaluation(Evaluation $evaluation, int $entiteId): void
    {
        $allowed = $evaluation->evaluable_type === Entite::class
            && (int) $evaluation->evaluable_id === $entiteId;

        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeTarget(string $targetType, int $targetId, int $entiteId): void
    {
        if ($targetType === 'entite') {
            if ($targetId !== $entiteId) {
                abort(403);
            }

            return;
        }

        abort(403);
    }

    /** @return array<string, array<int, array{id:int,label:string}>> */
    private function buildAssignmentOptions(int $entiteId): array
    {
        $entite = Entite::query()->findOrFail($entiteId);
        $dgNom = trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? ''));

        return [
            'entite' => [[
                'id' => $entite->id,
                'label' => ($dgNom !== '' ? $dgNom : 'DG non renseigne').' - '.$entite->nom,
            ]],
            'directeur' => [],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function buildTargetProfiles(int $entiteId): array
    {
        $profiles = [];
        $entite = Entite::query()->findOrFail($entiteId);
        $profiles['entite:'.$entite->id] = [
            'nom_prenom' => trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? '')),
            'poste' => 'Directeur General',
            'emploi' => 'Directeur General',
            'direction' => $entite->nom,
            'direction_service' => $entite->nom,
            'categorie' => 'Direction generale',
            'sexe' => null,
            'niveau' => null,
            'anciennete' => null,
            'matricule' => null,
            'semestre' => null,
            'date_recrutement' => null,
            'date_evaluation' => null,
            'date_titularisation' => null,
            'date_naissance' => null,
            'date_confirmation' => null,
            'date_affectation' => null,
            'formations' => [],
            'experiences' => [],
        ];

        return $profiles;
    }

    /**
     * @return array{entite_ids: array<int, int>, direction_ids: array<int, int>}
     */
    private function availableEvaluationTargets(int $entiteId): array
    {
        $today = now()->toDateString();
        $targets = FicheObjectif::query()
            ->select(['assignable_type', 'assignable_id'])
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', Entite::class)
            ->where('assignable_id', $entiteId)
            ->get();

        $entiteIds = [];

        foreach ($targets as $target) {
            $assignableId = (int) $target->assignable_id;

            if ($target->assignable_type === Entite::class && $assignableId === $entiteId) {
                $entiteIds[] = $assignableId;
            }
        }

        return [
            'entite_ids' => array_values(array_unique(array_merge([$entiteId], $entiteIds))),
            'direction_ids' => [],
        ];
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function buildObjectiveOptions(int $entiteId): array
    {
        $options = ['entite' => [], 'directeur' => []];
        $today = now()->toDateString();

        $fiches = FicheObjectif::query()
            ->with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', Entite::class)
            ->where('assignable_id', $entiteId)
            ->orderBy('titre')
            ->get();

        foreach ($fiches as $fiche) {
            $options['entite'][] = [
                'id' => $fiche->id,
                'target_id' => $fiche->assignable_id,
                'titre' => $fiche->titre,
                'date_echeance' => $fiche->date_echeance,
                'objectifs' => $fiche->objectifs->map(fn ($item) => [
                    'source_fiche_objectif_objectif_id' => $item->id,
                    'titre' => $item->description,
                ])->values()->all(),
            ];
        }

        return $options;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn (SubjectiveCriteriaTemplate $template) => [
                'id' => $template->id,
                'ordre' => $template->ordre,
                'titre' => $template->titre,
                'description' => $template->description,
                'subcriteria' => $template->subcriteria->map(fn ($subcriterion) => [
                    'libelle' => $subcriterion->libelle,
                    'ordre' => $subcriterion->ordre,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<int, mixed> $criteria
     * @return array<int, array<string, mixed>>
     */
    private function normalizeCriteria(array $criteria, string $type, int $minNote, int $maxNote): array
    {
        $normalized = [];

        foreach (array_values($criteria) as $criterionIndex => $criterion) {
            if (! is_array($criterion)) {
                continue;
            }

            $title = trim((string) ($criterion['titre'] ?? ''));
            $subcriteria = [];

            foreach (array_values((array) ($criterion['subcriteria'] ?? [])) as $subIndex => $subcriterion) {
                if (! is_array($subcriterion)) {
                    continue;
                }

                $label = trim((string) ($subcriterion['libelle'] ?? ''));
                if ($label === '') {
                    continue;
                }

                $note = (float) ($subcriterion['note'] ?? 0);
                $note = max($minNote, min($maxNote, $note));

                $subcriteria[] = [
                    'ordre' => $subIndex + 1,
                    'libelle' => $label,
                    'note' => $note,
                    'observation' => filled($subcriterion['observation'] ?? null) ? trim((string) $subcriterion['observation']) : null,
                ];
            }

            if ($title === '' || $subcriteria === []) {
                continue;
            }

            $noteGlobale = round(collect($subcriteria)->avg('note') ?? 0, 2);

            $normalized[] = [
                'type' => $type,
                'ordre' => $criterionIndex + 1,
                'titre' => $title,
                'description' => filled($criterion['description'] ?? null) ? trim((string) $criterion['description']) : null,
                'note_globale' => $noteGlobale,
                'observation' => filled($criterion['observation'] ?? null) ? trim((string) $criterion['observation']) : null,
                'source_template_id' => isset($criterion['source_template_id']) ? (int) $criterion['source_template_id'] : null,
                'source_fiche_objectif_id' => isset($criterion['source_fiche_objectif_id']) ? (int) $criterion['source_fiche_objectif_id'] : null,
                'source_fiche_objectif_objectif_id' => isset($criterion['source_fiche_objectif_objectif_id']) ? (int) $criterion['source_fiche_objectif_objectif_id'] : null,
                'subcriteria' => $subcriteria,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $subjectiveCriteria
     * @param array<int, array<string, mixed>> $objectiveCriteria
     * @return array<string, float|int>
     */
    private function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyenneSubjectifs = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moyenneObjectifs = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $noteCriteresSubjectifs = round($moyenneSubjectifs * 0.25, 2);
        $noteCriteresObjectifs = round($moyenneObjectifs * 0.75, 2);
        $noteFinale = round(($noteCriteresObjectifs + $noteCriteresSubjectifs) * 2, 2);

        return [
            'moyenne_subjectifs' => $moyenneSubjectifs,
            'moyenne_objectifs' => $moyenneObjectifs,
            'note_criteres_subjectifs' => $noteCriteresSubjectifs,
            'note_criteres_objectifs' => $noteCriteresObjectifs,
            'note_finale' => $noteFinale,
        ];
    }

    private function mentionFromScore(float $score): string
    {
        if ($score < 5) {
            return 'Insuffisant';
        }

        if ($score < 7) {
            return 'Passable';
        }

        if ($score < 8.5) {
            return 'Bien';
        }

        return 'Excellent';
    }

    private function evaluableLabel(mixed $evaluable, string $role): string
    {
        if ($evaluable instanceof Direction && $role === 'manager') {
            return trim(($evaluable->directeur_prenom ?? '').' '.($evaluable->directeur_nom ?? '')) ?: 'Directeur non renseigne';
        }

        if ($evaluable instanceof Direction) {
            return $evaluable->nom;
        }

        if ($evaluable instanceof Entite) {
            return $evaluable->nom;
        }

        return '-';
    }

    private function evaluableTypeLabel(string $evaluableType, string $role): string
    {
        if ($evaluableType === Direction::class && $role === 'manager') {
            return 'Directeur';
        }

        return match ($evaluableType) {
            Entite::class => 'Entite',
            Direction::class => 'Direction',
            default => $evaluableType,
        };
    }
}
