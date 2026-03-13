<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EvaluationController extends Controller
{
    /** @var array<string, class-string> */
    private const TYPE_MAP = [
        'entite' => Entite::class,
        'direction' => Direction::class,
        'service' => Service::class,
        'agent' => Agent::class,
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $query = Evaluation::query()
            ->with(['evaluable', 'evaluateur'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->whereHasMorph('evaluable', [Entite::class], fn ($entiteQuery) => $entiteQuery->where('nom', 'like', "%{$search}%"))
                        ->orWhereHasMorph('evaluable', [Direction::class], fn ($directionQuery) => $directionQuery->where('nom', 'like', "%{$search}%"))
                        ->orWhereHasMorph('evaluable', [Service::class], fn ($serviceQuery) => $serviceQuery->where('nom', 'like', "%{$search}%"))
                        ->orWhereHasMorph('evaluable', [Agent::class], fn ($agentQuery) => $agentQuery->where('nom', 'like', "%{$search}%")->orWhere('prenom', 'like', "%{$search}%"));
                });
            })
            ->when($statut !== '', fn ($query) => $query->where('statut', $statut))
            ->latest();

        return view('admin.evaluations.index', [
            'evaluations' => $query->paginate(10)->withQueryString(),
            'filters' => ['search' => $search, 'statut' => $statut],
        ]);
    }

    public function create(): View
    {
        return view('admin.evaluations.create', [
            'assignmentOptions' => $this->buildAssignmentOptions(),
        ]);
    }

    public function edit(Evaluation $evaluation): View|RedirectResponse
    {
        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('status', 'Seule une evaluation en brouillon peut etre modifiee.');
        }

        return view('admin.evaluations.edit', [
            'evaluation' => $evaluation,
            'assignmentOptions' => $this->buildAssignmentOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'evaluable_type' => ['required', 'string', 'in:entite,direction,service,agent'],
            'evaluable_id' => ['required', 'integer', 'min:1'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'note_manuelle' => ['nullable', 'integer', 'min:0', 'max:100'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluableClass = self::TYPE_MAP[$validated['evaluable_type']];
        $evaluableId = (int) $validated['evaluable_id'];

        $scores = $this->computeScores(
            $evaluableClass,
            $evaluableId,
            $validated['date_debut'],
            $validated['date_fin'],
            isset($validated['note_manuelle']) ? (int) $validated['note_manuelle'] : null,
        );

        $evaluation = Evaluation::create([
            'evaluable_type' => $evaluableClass,
            'evaluable_id' => $evaluableId,
            'evaluateur_id' => $request->user()->id,
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'note_objectifs' => $scores['note_objectifs'],
            'note_manuelle' => $scores['note_manuelle'],
            'note_finale' => $scores['note_finale'],
            'commentaire' => $validated['commentaire'] ?? null,
            'statut' => 'brouillon',
        ]);

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('status', 'Evaluation creee avec succes.');
    }

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('status', 'Seule une evaluation en brouillon peut etre modifiee.');
        }

        $validated = $request->validate([
            'evaluable_type' => ['required', 'string', 'in:entite,direction,service,agent'],
            'evaluable_id' => ['required', 'integer', 'min:1'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'note_manuelle' => ['nullable', 'integer', 'min:0', 'max:100'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ]);

        $evaluableClass = self::TYPE_MAP[$validated['evaluable_type']];
        $evaluableId = (int) $validated['evaluable_id'];

        $scores = $this->computeScores(
            $evaluableClass,
            $evaluableId,
            $validated['date_debut'],
            $validated['date_fin'],
            isset($validated['note_manuelle']) ? (int) $validated['note_manuelle'] : null,
        );

        $evaluation->update([
            'evaluable_type' => $evaluableClass,
            'evaluable_id' => $evaluableId,
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'note_objectifs' => $scores['note_objectifs'],
            'note_manuelle' => $scores['note_manuelle'],
            'note_finale' => $scores['note_finale'],
            'commentaire' => $validated['commentaire'] ?? null,
        ]);

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('status', 'Evaluation mise a jour avec succes.');
    }

    public function show(Evaluation $evaluation): View
    {
        $evaluation->load(['evaluable', 'evaluateur']);

        $objectifs = Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->orderBy('date')
            ->get();

        $mention = $this->mentionFromScore((int) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable);
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type);

        return view('admin.evaluations.show', compact('evaluation', 'objectifs', 'mention', 'cibleLabel', 'cibleType'));
    }

    public function exportPdf(Evaluation $evaluation): Response
    {
        $evaluation->load(['evaluable', 'evaluateur']);

        $objectifs = Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->orderBy('date')
            ->get();

        $mention = $this->mentionFromScore((int) $evaluation->note_finale);
        $cibleLabel = $this->evaluableLabel($evaluation->evaluable);
        $cibleType = $this->evaluableTypeLabel($evaluation->evaluable_type);

        $pdf = Pdf::loadView('admin.evaluations.pdf', compact('evaluation', 'objectifs', 'mention', 'cibleLabel', 'cibleType'));

        return $pdf->download('evaluation-'.$evaluation->id.'.pdf');
    }

    public function submit(Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('status', 'Cette evaluation a deja ete soumise ou validee.');
        }

        $evaluation->update(['statut' => 'soumis']);

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('status', 'Evaluation soumise avec succes.');
    }

    public function approve(Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->statut !== 'soumis') {
            return redirect()->route('admin.evaluations.show', $evaluation)
                ->with('status', 'Seule une evaluation soumise peut etre validee.');
        }

        $closedObjectifsCount = $this->closeAssignableObjectifs($evaluation);

        $evaluation->update(['statut' => 'valide']);

        return redirect()->route('admin.evaluations.show', $evaluation)
            ->with('status', 'Evaluation validee avec succes. '.$closedObjectifsCount.' objectif(s) cloture(s) automatiquement.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        if ($evaluation->statut === 'valide') {
            return redirect()->route('admin.evaluations.index')
                ->with('status', 'Une evaluation validee ne peut pas etre supprimee.');
        }

        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')
            ->with('status', 'Evaluation supprimee.');
    }

    /**
     * @return array{note_objectifs:int,note_manuelle:int|null,note_finale:int}
     */
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

    private function evaluableLabel(mixed $evaluable): string
    {
        if ($evaluable instanceof Agent) {
            return trim($evaluable->prenom.' '.$evaluable->nom);
        }

        if ($evaluable instanceof Direction) {
            return $evaluable->nom.($evaluable->directeur_nom ? ' ('.$evaluable->directeur_nom.')' : '');
        }

        if ($evaluable instanceof Service) {
            $chef = trim(($evaluable->chef_prenom ?? '').' '.($evaluable->chef_nom ?? ''));

            return $evaluable->nom.($chef !== '' ? ' ('.$chef.')' : '');
        }

        if ($evaluable instanceof Entite) {
            return $evaluable->nom;
        }

        return '-';
    }

    private function evaluableTypeLabel(string $evaluableType): string
    {
        return match ($evaluableType) {
            Agent::class => 'Agent',
            Direction::class => 'Direction',
            Service::class => 'Service',
            Entite::class => 'Entite',
            default => $evaluableType,
        };
    }

    /**
     * @return array<string, array<int, array{id: int, label: string}>>
     */
    private function buildAssignmentOptions(): array
    {
        return [
            'entite' => Entite::query()->orderBy('nom')->get()
                ->map(fn ($entite) => ['id' => $entite->id, 'label' => $entite->nom])
                ->values()
                ->all(),
            'direction' => Direction::query()->orderBy('nom')->get()
                ->map(fn ($direction) => ['id' => $direction->id, 'label' => $direction->nom.($direction->directeur_nom ? ' ('.$direction->directeur_nom.')' : '')])
                ->values()
                ->all(),
            'service' => Service::query()->orderBy('nom')->get()
                ->map(function ($service) {
                    $chef = trim(($service->chef_prenom ?? '').' '.($service->chef_nom ?? ''));

                    return ['id' => $service->id, 'label' => $service->nom.($chef !== '' ? ' ('.$chef.')' : '')];
                })
                ->values()
                ->all(),
            'agent' => Agent::query()->with('service')->orderBy('nom')->get()
                ->map(fn ($agent) => ['id' => $agent->id, 'label' => trim($agent->prenom.' '.$agent->nom).($agent->service ? ' - '.$agent->service->nom : '')])
                ->values()
                ->all(),
        ];
    }

    private function closeAssignableObjectifs(Evaluation $evaluation): int
    {
        return Objectif::query()
            ->where('assignable_type', $evaluation->evaluable_type)
            ->where('assignable_id', $evaluation->evaluable_id)
            ->whereBetween('date', [$evaluation->date_debut->toDateString(), $evaluation->date_fin->toDateString()])
            ->where('avancement_percentage', '<', 100)
            ->update([
                'avancement_percentage' => 100,
            ]);
    }
}
