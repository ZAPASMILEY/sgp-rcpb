<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ObjectifController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $objectifsQuery = Objectif::query()
            ->with('assignable')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('commentaire', 'like', "%{$search}%")
                        ->orWhere('date_echeance', 'like', "%{$search}%")
                        ->orWhereHasMorph('assignable', [Entite::class], function ($entiteQuery) use ($search): void {
                            $entiteQuery->where('nom', 'like', "%{$search}%");
                        })
                        ->orWhereHasMorph('assignable', [Direction::class], function ($directionQuery) use ($search): void {
                            $directionQuery->where('nom', 'like', "%{$search}%");
                        })
                        ->orWhereHasMorph('assignable', [Service::class], function ($serviceQuery) use ($search): void {
                            $serviceQuery->where('nom', 'like', "%{$search}%");
                        })
                        ->orWhereHasMorph('assignable', [Agent::class], function ($agentQuery) use ($search): void {
                            $agentQuery
                                ->where('nom', 'like', "%{$search}%")
                                ->orWhere('prenom', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('date')
            ->latest();

        $objectifs = $objectifsQuery->paginate(10)->withQueryString();

        $objectifs->getCollection()->transform(function (Objectif $objectif): Objectif {
            $objectif->setAttribute('is_evaluation_locked', $this->isLockedByEvaluation($objectif));

            return $objectif;
        });

        return view('admin.objectifs.index', [
            'objectifs' => $objectifs,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.objectifs.create', [
            'assignmentOptions' => $this->assignmentOptions(),
            'today' => now()->toDateString(),
        ]);
    }

    public function show(Objectif $objectif): View
    {
        return view('admin.objectifs.show', [
            'objectif' => $objectif->load('assignable'),
        ]);
    }

    public function edit(Objectif $objectif): View
    {
        return view('admin.objectifs.edit', [
            'objectif' => $objectif->load('assignable'),
            'assignmentOptions' => $this->assignmentOptions(),
            'selectedAssignableType' => $this->assignableKeyFromClass($objectif->assignable_type),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateObjectif($request);

        Objectif::query()->create([
            'assignable_type' => $validated['assignable_class'],
            'assignable_id' => $validated['assignable_id'],
            'date' => now()->toDateString(),
            'date_echeance' => $validated['date_echeance'],
            'commentaire' => $validated['commentaire'],
            'avancement_percentage' => 0,
        ]);

        return redirect()
            ->route('admin.objectifs.index')
            ->with('status', 'Objectif cree avec succes.');
    }

    public function update(Request $request, Objectif $objectif): RedirectResponse
    {
        $validated = $this->validateObjectif($request);

        $objectif->update([
            'assignable_type' => $validated['assignable_class'],
            'assignable_id' => $validated['assignable_id'],
            'date_echeance' => $validated['date_echeance'],
            'commentaire' => $validated['commentaire'],
        ]);

        return redirect()
            ->route('admin.objectifs.show', $objectif)
            ->with('status', 'Objectif mis a jour avec succes.');
    }

    public function destroy(Objectif $objectif): RedirectResponse
    {
        $objectif->delete();

        return redirect()
            ->route('admin.objectifs.index')
            ->with('status', 'Objectif supprime avec succes.');
    }

    public function adjustProgress(Request $request, Objectif $objectif): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => ['required', 'string', 'in:up,down'],
        ]);

        if ($this->isExpired($objectif)) {
            return redirect()
                ->route('admin.objectifs.index')
                ->with('status', 'L\'echeance est depassee. L\'avancement de cet objectif ne peut plus etre modifie.');
        }

        if ($this->isLockedByEvaluation($objectif)) {
            return redirect()
                ->route('admin.objectifs.index')
                ->with('status', 'Avancement verrouille: la cible a deja ete evaluee pour la periode contenant cette echeance.');
        }

        $step = 10;
        $current = (int) $objectif->avancement_percentage;
        $next = $validated['direction'] === 'up'
            ? min(100, $current + $step)
            : max(0, $current - $step);

        $objectif->update([
            'avancement_percentage' => $next,
        ]);

        return redirect()
            ->route('admin.objectifs.index')
            ->with('status', 'Avancement de l\'objectif mis a jour a '.$next.'%.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateObjectif(Request $request): array
    {
        $validated = $request->validate([
            'assignable_type' => ['required', 'string', 'in:entite,direction,service,agent'],
            'assignable_id' => ['required', 'integer'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'commentaire' => ['required', 'string', 'max:5000'],
        ]);

        $assignableClass = $this->assignableClassFromKey($validated['assignable_type']);

        if (! $assignableClass::query()->whereKey($validated['assignable_id'])->exists()) {
            throw ValidationException::withMessages([
                'assignable_id' => 'La cible selectionnee est invalide.',
            ]);
        }

        $validated['assignable_class'] = $assignableClass;

        return $validated;
    }

    /**
     * @return array<string, array<int, array{id:int,label:string}>>
     */
    private function assignmentOptions(): array
    {
        return [
            'entite' => Entite::query()
                ->orderBy('nom')
                ->get(['id', 'nom'])
                ->map(fn (Entite $entite): array => [
                    'id' => $entite->id,
                    'label' => $entite->nom,
                ])
                ->values()
                ->all(),
            'direction' => Direction::query()
                ->with('entite')
                ->orderBy('nom')
                ->get(['id', 'nom', 'entite_id'])
                ->map(fn (Direction $direction): array => [
                    'id' => $direction->id,
                    'label' => $direction->nom.($direction->entite ? ' - '.$direction->entite->nom : ''),
                ])
                ->values()
                ->all(),
            'service' => Service::query()
                ->with('direction')
                ->orderBy('nom')
                ->get(['id', 'nom', 'direction_id'])
                ->map(fn (Service $service): array => [
                    'id' => $service->id,
                    'label' => $service->nom.($service->direction ? ' - '.$service->direction->nom : ''),
                ])
                ->values()
                ->all(),
            'agent' => Agent::query()
                ->with('service')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get(['id', 'nom', 'prenom', 'service_id'])
                ->map(fn (Agent $agent): array => [
                    'id' => $agent->id,
                    'label' => $agent->prenom.' '.$agent->nom.($agent->service ? ' - '.$agent->service->nom : ''),
                ])
                ->values()
                ->all(),
        ];
    }

    private function assignableClassFromKey(string $key): string
    {
        return match ($key) {
            'entite' => Entite::class,
            'direction' => Direction::class,
            'service' => Service::class,
            'agent' => Agent::class,
        };
    }

    private function assignableKeyFromClass(string $class): string
    {
        return match ($class) {
            Entite::class => 'entite',
            Direction::class => 'direction',
            Service::class => 'service',
            Agent::class => 'agent',
            default => 'entite',
        };
    }

    private function isExpired(Objectif $objectif): bool
    {
        return Carbon::parse($objectif->date_echeance)->isBefore(today());
    }

    private function isLockedByEvaluation(Objectif $objectif): bool
    {
        return Evaluation::query()
            ->where('evaluable_type', $objectif->assignable_type)
            ->where('evaluable_id', $objectif->assignable_id)
            ->whereDate('date_debut', '<=', $objectif->date_echeance)
            ->whereDate('date_fin', '>=', $objectif->date_echeance)
            ->exists();
    }
}