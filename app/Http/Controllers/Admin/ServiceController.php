<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $directionId = (string) $request->query('direction_id', '');

        $servicesQuery = Service::query()
            ->with(['direction.entite'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('chef_prenom', 'like', "%{$search}%")
                        ->orWhere('chef_nom', 'like', "%{$search}%")
                        ->orWhere('chef_email', 'like', "%{$search}%")
                        ->orWhere('chef_telephone', 'like', "%{$search}%")
                        ->orWhereHas('direction', function ($directionQuery) use ($search): void {
                            $directionQuery
                                ->where('nom', 'like', "%{$search}%")
                                ->orWhereHas('entite', function ($entiteQuery) use ($search): void {
                                    $entiteQuery->where('nom', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($directionId !== '', function ($query) use ($directionId): void {
                $query->where('direction_id', $directionId);
            })
            ->latest();

        return view('admin.services.index', [
            'services' => $servicesQuery->paginate(10)->withQueryString(),
            'filters' => [
                'search' => $search,
                'direction_id' => $directionId,
            ],
            'directions' => Direction::query()->with('entite')->orderBy('nom')->get(['id', 'nom', 'entite_id']),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'directions' => Direction::query()->with('entite')->orderBy('nom')->get(['id', 'nom', 'entite_id']),
        ]);
    }

    public function show(Service $service): View
    {
        return view('admin.services.show', [
            'service' => $service->load('direction.entite'),
        ]);
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'directions' => Direction::query()->with('entite')->orderBy('nom')->get(['id', 'nom', 'entite_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Service::query()->create($this->validateService($request));

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service cree avec succes.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validateService($request));

        return redirect()
            ->route('admin.services.show', $service)
            ->with('status', 'Service mis a jour avec succes.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service supprime avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateService(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'direction_id' => ['required', 'integer', 'exists:directions,id'],
            'chef_prenom' => ['required', 'string', 'max:255'],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_email' => ['required', 'email', 'max:255'],
            'chef_telephone' => ['required', 'string', 'max:30'],
        ]);
    }
}
