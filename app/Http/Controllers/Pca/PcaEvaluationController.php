<?php

namespace App\Http\Controllers\Pca;

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
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PcaEvaluationController extends Controller
{
    public function __construct(private readonly EvaluationService $evaluationService) {}

    /** @var array<string, array{class: class-string, role: string}> */
    private const TARGET_MAP = [
        'user'   => ['class' => User::class, 'role' => 'DG'],
    ];

    public function index(Request $request): View
    {
        $this->authorize('evaluations.voir-equipe');
        $user = $request->user();
        $entiteId = $user->agent?->entite_id;

        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $dgUser = $this->getDGOfDirectionGenerale();
        $baseQuery = Evaluation::query()
            ->with(['evaluable', 'evaluateur'])
            ->where('evaluable_type', User::class)
            ->when($dgUser, fn ($query) => $query->where('evaluable_id', $dgUser->id), fn ($query) => $query->whereRaw('1 = 0'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHasMorph('evaluable', [User::class], function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($statut !== '', fn ($query) => $query->where('statut', $statut));

        $evaluations = (clone $baseQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'    => (clone $baseQuery)->count(),
            'brouillon'=> (clone $baseQuery)->where('statut', 'brouillon')->count(),
            'soumis'   => (clone $baseQuery)->where('statut', 'soumis')->count(),
            'valide'   => (clone $baseQuery)->where('statut', 'valide')->count(),
            'refuse'   => (clone $baseQuery)->where('statut', 'refuse')->count(),
        ];

        return view('pca.evaluations.index', [
            'evaluations' => $evaluations,
            'filters' => ['search' => $search, 'statut' => $statut],
            'stats' => $stats,
        ]);
    }


    public function create(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $entiteFaitiere = $this->getDirectionGeneraleEntite();
        $directionGenerale = $this->getDirectionGeneraleDirection();
        $dg = $this->getDGOfDirectionGenerale();

        if (!$entiteFaitiere) {
            abort(500, "L'entité faîtière est introuvable. Veuillez vérifier la base de données.");
        }

        $assignmentOptions = [
            'user' => $dg ? [['id' => $dg->id, 'label' => $dg->name ?? 'Directeur Général']] : [],
        ];

        $oldFormations    = old('identification.formations');
        $prefilledAgentId = $dg?->agent_id;

        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();

        return view('pca.evaluations.create', [
            'dg'               => $dg,
            'entiteFaitiere'   => $entiteFaitiere,
            'directionGenerale' => $directionGenerale,
            'assignmentOptions' => $assignmentOptions,
            'targetProfiles'   => $this->buildTargetProfiles($entiteFaitiere->id),
            'objectiveOptions' => $this->buildObjectiveOptions($entiteFaitiere->id),
            'subjectiveTemplates' => $this->evaluationService->buildSubjectiveTemplates(),
            'oldFormations'    => $oldFormations,
            'prefilledAgentId' => $prefilledAgentId,
            'openAnnee'        => $openAnnee,
            'openSemestres'    => $openSemestres,
            'openSemestre'     => $openSemestre,
        ]);
    }

    private function getDirectionGeneraleEntite(): ?Entite
    {
        return Entite::query()->latest()->first();
    }

    private function getDGOfDirectionGenerale(): ?User
    {
        $entite = $this->getDirectionGeneraleEntite();
        if (!$entite || !$entite->dg_agent_id) {
            return null;
        }
        // Le DG est l'agent référencé dans entites.dg_agent_id
        // dont le compte utilisateur est lié via users.agent_id
        return User::query()
            ->where('role', 'DG')
            ->where('agent_id', $entite->dg_agent_id)
            ->first();
    }

    private function getDirectionGeneraleDirection(): ?Direction
    {
        $entite = $this->getDirectionGeneraleEntite();
        if (!$entite) {
            return null;
        }
        return Direction::query()
            ->where('nom', 'Direction Générale')
            ->where('entite_id', $entite->id)
            ->first();
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $user = $request->user();
        $entiteId = $user->agent?->entite_id;
        $entiteDirectionGenerale = $this->getDirectionGeneraleEntite();
        $dg = $this->getDGOfDirectionGenerale();
        $entiteId = $request->user()->agent?->entite_id;

        $validated = $request->validate([
            'evaluable_type' => ['required', 'string', 'in:user'],
            'identification.nom_prenom' => ['nullable', 'string', 'max:255'],
            'identification.date_recrutement' => ['nullable', 'string', 'max:20'],
            'identification.date_evaluation' => ['nullable', 'string', 'max:20'],
            'identification.date_titularisation' => ['nullable', 'string', 'max:20'],
            'identification.matricule' => ['nullable', 'string', 'max:255'],
            'identification.grade' => ['required', 'string', 'max:255'],
            'identification.poste' => ['nullable', 'string', 'max:255'],
            'identification.emploi' => ['nullable', 'string', 'max:255'],
            'identification.niveau' => ['nullable', 'string', 'max:255'],
            'identification.date_naissance' => ['nullable', 'string', 'max:20'],
            'identification.direction' => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.date_confirmation' => ['nullable', 'string', 'max:20'],
            'identification.categorie' => ['nullable', 'string', 'max:255'],
            'identification.anciennete' => ['nullable', 'string', 'max:255'],
            'identification.sexe' => ['nullable', 'string', 'max:1'],
            'identification.date_affectation' => ['nullable', 'string', 'max:20'],
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

        // Inject semestre + matricule from DG user
        $validated['identification']['semestre'] = (string) $semestre->numero;
        $validated['identification']['matricule'] = $dg?->agent?->matricule ?? ($validated['identification']['matricule'] ?? null);

        $validated = $this->evaluationService->normalizePayloadDates($validated, [
            'identification.date_recrutement',
            'identification.date_evaluation',
            'identification.date_titularisation',
            'identification.date_naissance',
            'identification.date_confirmation',
            'identification.date_affectation',
            'date_signature_evalue',
            'date_signature_directeur',
            'date_signature_evaluateur',
        ]);

        $targetConfig = self::TARGET_MAP[$validated['evaluable_type']];
        $targetId = $dg?->id;
        $this->authorizeTarget($validated['evaluable_type'], $targetId, $entiteId);

        $normalizedSubjective = $this->evaluationService->normalizeCriteria(
            (array) $request->input('subjective_criteres', []),
            'subjectif',
            1,
            5,
            false
        );
        $normalizedObjective = $this->evaluationService->normalizeCriteria(
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

        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);

        $evaluation = DB::transaction(function () use ($request, $validated, $targetConfig, $targetId, $normalizedSubjective, $normalizedObjective, $scores, $anneeId, $semestreId, $dateDebut, $dateFin) {
            $evaluation = Evaluation::create([
                'evaluable_type' => $targetConfig['class'],
                'evaluable_id' => $targetId,
                'evaluable_role' => $targetConfig['role'],
                'annee_id' => $anneeId,
                'semestre_id' => $semestreId,
                'evaluateur_id' => $request->user()->id,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
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
            $this->evaluationService->persistCriteria($evaluation, array_merge($normalizedSubjective, $normalizedObjective));

            return $evaluation;
        });

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation creee avec succes.');
    }
    /**
     * Retourne le DG rattaché à l'entité Direction Générale
     */


    public function show(Request $request, Evaluation $evaluation): View
    {
        $this->authorize('evaluations.voir-equipe');
        $this->authorizeEvaluation($evaluation, $request->user()->agent?->entite_id);

        $evaluation->load([
            'evaluable',
            'evaluateur',
            'identification',
            'criteres.sousCriteres',
        ]);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $mention = $this->evaluationService->mention((float) $evaluation->note_finale);
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
        $this->authorize('evaluations.exporter-pdf');
        $this->authorizeEvaluation($evaluation, $request->user()->agent?->entite_id);

        $evaluation->load([
            'evaluable',
            'evaluateur',
            'identification',
            'criteres.sousCriteres',
        ]);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria = $evaluation->criteres->where('type', 'objectif')->values();
        $mention = $this->evaluationService->mention((float) $evaluation->note_finale);
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
        $this->authorize('evaluations.soumettre');
        $this->authorizeEvaluation($evaluation, $request->user()->agent?->entite_id);

        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('pca.evaluations.show', $evaluation)
                ->with('status', 'Cette evaluation a deja ete soumise ou validee.');
        }

        $evaluation->update(['statut' => 'soumis']);

        // Notifier l'évalué (DG)
        if ($evaluation->evaluable_type === User::class && $evaluation->evaluable_id) {
            Alerte::notifier(
                (int) $evaluation->evaluable_id,
                'Nouvelle fiche d\'évaluation reçue',
                'Une fiche d\'évaluation vous a été soumise par le PCA. Connectez-vous pour la consulter.',
                'haute'
            );
        }

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation soumise avec succes.');
    }

    public function approve(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->authorizeEvaluation($evaluation, $request->user()->agent?->entite_id);

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
        $this->authorize('evaluations.creer');
        $this->authorizeEvaluation($evaluation, $request->user()->agent?->entite_id);

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
        $allowed = false;
        // Autoriser le PCA de l'entité
        // Autoriser le DG à voir ses propres évaluations
        if ($evaluation->evaluable_type === User::class) {
            $dgUser = $this->getDGOfDirectionGenerale();
            if ($dgUser && (int) $evaluation->evaluable_id === $dgUser->id) {
                // Si l'utilisateur connecté est le DG, il peut voir
                if (Auth::check() && Auth::user()->id === $dgUser->id) {
                    $allowed = true;
                }
                // Ou si c'est le PCA de l'entité
                if ((int) $entiteId === ($evaluation->evaluateur->agent?->entite_id ?? null)) {
                    $allowed = true;
                }
            }
        }
        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeTarget(string $targetType, int $targetId, int $entiteId): void
    {
        if ($targetType === 'user') {
            // Only allow the DG user of the Faîtière (main entity)
            $dgUser = $this->getDGOfDirectionGenerale();
            if (!$dgUser || $targetId !== $dgUser->id) {
                abort(403);
            }
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function buildTargetProfiles(int $entiteId): array
    {
        $profiles = [];
        $entite     = Entite::query()->findOrFail($entiteId);
        $direction  = $this->getDirectionGeneraleDirection();
        $dgUser     = $this->getDGOfDirectionGenerale();
        if ($dgUser) {
            $profiles['user:'.$dgUser->id] = [
                'nom_prenom' => $dgUser->name,
                'poste' => 'Directeur Général',
                'emploi' => 'Directeur Général',
                'direction' => $entite->nom,
                'direction_service' => $direction?->nom ?? 'Direction Générale',
                'categorie' => 'Direction générale',
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
        }
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
        $options = ['user' => []];
        $today = now()->toDateString();

        // Fiches pour l'entité
        $fichesEntite = FicheObjectif::query()
            ->with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', Entite::class)
            ->where('assignable_id', $entiteId)
            ->orderBy('titre')
            ->get();

        foreach ($fichesEntite as $fiche) {
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

        // Fiches pour le DG (user)
        $dgUser = $this->getDGOfDirectionGenerale();
        if ($dgUser) {
            $fichesDG = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
                ->where('assignable_type', User::class)
                ->where('assignable_id', $dgUser->id)
                ->orderBy('titre')
                ->get();

            foreach ($fichesDG as $fiche) {
                $options['user'][] = [
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
        }

        return $options;
    }

    private function evaluableLabel(mixed $evaluable, string $role): string
    {
        $role = strtolower($role);

        if ($evaluable instanceof User && $role === 'dg') {
            return $evaluable->name;
        }

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
        $role = strtolower($role);

        if ($evaluableType === User::class && $role === 'dg') {
            return 'Directeur General';
        }

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
