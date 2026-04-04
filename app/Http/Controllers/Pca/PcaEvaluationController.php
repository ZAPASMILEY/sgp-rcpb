<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PcaEvaluationController extends Controller
{
    /** @var array<string, array{class: class-string, role: string}> */
    private const TARGET_MAP = [
        'entite'    => ['class' => Entite::class,    'role' => 'entity'],
        'directeur' => ['class' => Direction::class, 'role' => 'manager'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $entiteId = $user->pca_entite_id;

        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $evaluations = Evaluation::query()
            ->with(['evaluable', 'evaluateur'])
            ->where(function ($q) use ($entiteId, $directionIds): void {
                $q->where(function ($sub) use ($entiteId): void {
                    $sub->where('evaluable_type', Entite::class)
                        ->where('evaluable_id', $entiteId);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('evaluable_type', Direction::class)
                        ->whereIn('evaluable_id', $directionIds)
                        ->where('evaluable_role', 'manager');
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->whereHasMorph('evaluable', [Entite::class], fn ($q) => $q->where('nom', 'like', "%{$search}%"))
                        ->orWhereHasMorph('evaluable', [Direction::class], fn ($q) => $q->where('nom', 'like', "%{$search}%")->orWhere('directeur_nom', 'like', "%{$search}%"));
                });
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
        return view('pca.evaluations.create', [
            'assignmentOptions' => $this->buildAssignmentOptions($request->user()->pca_entite_id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entiteId = $request->user()->pca_entite_id;

        $validated = $request->validate([
            'evaluable_type' => ['required', 'string', 'in:entite,directeur'],
            'evaluable_id' => ['required', 'integer', 'min:1'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'note_manuelle' => ['nullable', 'integer', 'min:0', 'max:100'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $targetConfig = self::TARGET_MAP[$validated['evaluable_type']];
        $this->authorizeTarget($validated['evaluable_type'], (int) $validated['evaluable_id'], $entiteId);

        $scores = $this->computeScores(
            $targetConfig['class'],
            (int) $validated['evaluable_id'],
            $validated['date_debut'],
            $validated['date_fin'],
            isset($validated['note_manuelle']) ? (int) $validated['note_manuelle'] : null,
        );

        $evaluation = Evaluation::create([
            'evaluable_type' => $targetConfig['class'],
            'evaluable_id' => (int) $validated['evaluable_id'],
            'evaluable_role' => $targetConfig['role'],
            'annee_id' => Annee::resolveIdForDate($validated['date_debut']),
            'evaluateur_id' => $request->user()->id,
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'note_objectifs' => $scores['note_objectifs'],
            'note_manuelle' => $scores['note_manuelle'],
            'note_finale' => $scores['note_finale'],
            'commentaire' => $validated['commentaire'] ?? null,
            'statut' => 'brouillon',
        ]);

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation creee avec succes.');
    }

    public function show(Request $request, Evaluation $evaluation): View
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        $evaluation->load(['evaluable', 'evaluateur']);

        $objectifs = Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->orderBy('date')
            ->get();

        $mention = $this->mentionFromScore((int) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable, $evaluation->evaluable_role ?? 'entity');
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type, $evaluation->evaluable_role ?? 'entity');

        return view('pca.evaluations.show', compact('evaluation', 'objectifs', 'mention', 'cibleLabel', 'cibleType'));
    }

    public function exportPdf(Request $request, Evaluation $evaluation): Response
    {
        $this->authorizeEvaluation($evaluation, $request->user()->pca_entite_id);

        $evaluation->load(['evaluable', 'evaluateur']);

        $objectifs = Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->orderBy('date')
            ->get();

        $mention = $this->mentionFromScore((int) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable, $evaluation->evaluable_role ?? 'entity');
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type, $evaluation->evaluable_role ?? 'entity');

        $pdf = Pdf::loadView('admin.evaluations.pdf', compact('evaluation', 'objectifs', 'mention', 'cibleLabel', 'cibleType'));

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

        $closedCount = $this->closeAssignableObjectifs($evaluation);

        $evaluation->update(['statut' => 'valide']);

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('status', 'Evaluation validee. '.$closedCount.' objectif(s) cloture(s) automatiquement.');
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
        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $allowed = (
            ($evaluation->evaluable_type === Entite::class && (int) $evaluation->evaluable_id === $entiteId) ||
            ($evaluation->evaluable_type === Direction::class && in_array((int) $evaluation->evaluable_id, $directionIds, true) && $evaluation->evaluable_role === 'manager')
        );

        if (! $allowed) {
            abort(403);
        }
    }

    private function authorizeTarget(string $targetType, int $targetId, int $entiteId): void
    {
        if ($targetType === 'entite' && $targetId !== $entiteId) {
            abort(403);
        }

        if ($targetType === 'directeur') {
            $directionIds = Direction::query()
                ->where('entite_id', $entiteId)
                ->pluck('id')
                ->all();

            if (! in_array($targetId, $directionIds, true)) {
                abort(403);
            }
        }
    }

    /** @return array<string, array<int, array{id:int,label:string}>> */
    private function buildAssignmentOptions(int $entiteId): array
    {
        $entite = Entite::query()->findOrFail($entiteId);
        $directions = Direction::query()->where('entite_id', $entiteId)->orderBy('nom')->get();

        $directeurs = $directions->map(fn (Direction $d) => [
            'id' => $d->id,
            'label' => ($d->directeur_nom ?: 'Directeur non renseigné').' — '.$d->nom,
        ])->values()->all();

        // Ajout DG en haut de la liste si présente dans l'entité
        if ($entite->directrice_generale_nom && $entite->directrice_generale_email) {
            array_unshift($directeurs, [
                'id' => 'dg-'.$entite->id,
                'label' => 'Directrice Générale — '.$entite->directrice_generale_nom,
                'dg' => true,
                'dg_email' => $entite->directrice_generale_email,
            ]);
        }

        return [
            'entite' => [['id' => $entite->id, 'label' => $entite->nom]],
            'directeur' => $directeurs,
        ];
    }

    /** @return array{note_objectifs:int,note_manuelle:int|null,note_finale:int} */
    private function computeScores(string $evaluableClass, int $evaluableId, string $dateDebut, string $dateFin, ?int $noteManuelle): array
    {
        $noteObjectifs = (int) round(
            Objectif::query()
                ->where('assignable_type', $evaluableClass)
                ->where('assignable_id', $evaluableId)
                ->whereBetween('date', [$dateDebut, $dateFin])
                ->avg('avancement_percentage') ?? 0
        );

        $noteFinale = $noteManuelle !== null
            ? (int) round(($noteObjectifs + $noteManuelle) / 2)
            : $noteObjectifs;

        return [
            'note_objectifs' => $noteObjectifs,
            'note_manuelle' => $noteManuelle,
            'note_finale' => $noteFinale,
        ];
    }

    private function mentionFromScore(int $score): string
    {
        if ($score < 50) {
            return 'Insuffisant';
        }

        if ($score < 70) {
            return 'Passable';
        }

        if ($score < 85) {
            return 'Bien';
        }

        return 'Excellent';
    }

    private function evaluableLabel(mixed $evaluable, string $role): string
    {
        if ($evaluable instanceof Direction && $role === 'manager') {
            return $evaluable->directeur_nom ?: 'Directeur non renseigne';
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

    private function closeAssignableObjectifs(Evaluation $evaluation): int
    {
        return Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->where('avancement_percentage', '<', 100)
            ->update(['avancement_percentage' => 100]);
    }
}
