<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PcaObjectifController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $entiteId = $user->pca_entite_id;

        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $search = trim((string) $request->query('search', ''));

        $objectifs = Objectif::query()
            ->with('assignable')
            ->where(function ($q) use ($entiteId, $directionIds): void {
                $q->where(function ($sub) use ($entiteId): void {
                    $sub->where('assignable_type', Entite::class)
                        ->where('assignable_id', $entiteId);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('commentaire', 'like', "%{$search}%");
            })
            ->latest('date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $objectifs->getCollection()->transform(function (Objectif $objectif): Objectif {
            $objectif->setAttribute('is_evaluation_locked', $this->isLockedByEvaluation($objectif));

            return $objectif;
        });

        return view('pca.objectifs.index', [
            'objectifs' => $objectifs,
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

    public function show(Request $request, Objectif $objectif): View
    {
        $this->authorizeObjectif($objectif, $request->user()->pca_entite_id);

        return view('pca.objectifs.show', [
            'objectif' => $objectif->load('assignable'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entiteId = $request->user()->pca_entite_id;
        $validated = $this->validateObjectif($request, $entiteId);
        $date = now()->toDateString();

        Objectif::query()->create([
            'assignable_type' => $validated['assignable_class'],
            'assignable_id' => $validated['assignable_id'],
            'annee_id' => Annee::resolveIdForDate($date),
            'date' => $date,
            'date_echeance' => $validated['date_echeance'],
            'commentaire' => $validated['commentaire'],
            'avancement_percentage' => 0,
        ]);

        return redirect()
            ->route('pca.objectifs.index')
            ->with('status', 'Objectif cree avec succes.');
    }

    public function adjustProgress(Request $request, Objectif $objectif): RedirectResponse
    {
        $this->authorizeObjectif($objectif, $request->user()->pca_entite_id);

        $validated = $request->validate([
            'direction' => ['required', 'string', 'in:up,down'],
        ]);

        if (Carbon::parse($objectif->date_echeance)->isBefore(today())) {
            return redirect()
                ->route('pca.objectifs.index')
                ->with('status', 'L\'echeance est depassee. L\'avancement de cet objectif ne peut plus etre modifie.');
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

    public function destroy(Request $request, Objectif $objectif): RedirectResponse
    {
        $this->authorizeObjectif($objectif, $request->user()->pca_entite_id);

        $objectif->delete();

        return redirect()
            ->route('pca.objectifs.index')
            ->with('status', 'Objectif supprime.');
    }

    private function authorizeObjectif(Objectif $objectif, int $entiteId): void
    {
        $directionIds = Direction::query()
            ->where('entite_id', $entiteId)
            ->pluck('id')
            ->all();

        $allowed = (
            ($objectif->assignable_type === Entite::class && (int) $objectif->assignable_id === $entiteId) ||
            ($objectif->assignable_type === Direction::class && in_array((int) $objectif->assignable_id, $directionIds, true))
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
