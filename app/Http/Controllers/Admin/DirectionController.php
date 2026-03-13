<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Entite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $entiteId = (string) $request->query('entite_id', '');

        $directionsQuery = Direction::query()
            ->with('entite')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('directeur_nom', 'like', "%{$search}%")
                        ->orWhere('directeur_email', 'like', "%{$search}%")
                        ->orWhere('secretariat_telephone', 'like', "%{$search}%")
                        ->orWhereHas('entite', function ($entiteQuery) use ($search): void {
                            $entiteQuery->where('nom', 'like', "%{$search}%");
                        });
                });
            })
            ->when($entiteId !== '', function ($query) use ($entiteId): void {
                $query->where('entite_id', $entiteId);
            })
            ->latest();

        return view('admin.directions.index', [
            'directions' => $directionsQuery->paginate(10)->withQueryString(),
            'filters' => [
                'search' => $search,
                'entite_id' => $entiteId,
            ],
            'entites' => Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function create(): View
    {
        return view('admin.directions.create', [
            'entites' => Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function show(Direction $direction): View
    {
        return view('admin.directions.show', [
            'direction' => $direction->load('entite'),
        ]);
    }

    public function edit(Direction $direction): View
    {
        return view('admin.directions.edit', [
            'direction' => $direction,
            'entites' => Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Direction::query()->create($this->validateDirection($request));

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Direction creee avec succes.');
    }

    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $direction->update($this->validateDirection($request));

        return redirect()
            ->route('admin.directions.show', $direction)
            ->with('status', 'Direction mise a jour avec succes.');
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->delete();

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Direction supprimee avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDirection(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'entite_id' => ['required', 'integer', 'exists:entites,id'],
            'directeur_nom' => ['required', 'string', 'max:255'],
            'directeur_email' => ['required', 'email', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
        ]);
    }
}
