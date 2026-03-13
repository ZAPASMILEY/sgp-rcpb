<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $agentsQuery = Agent::query()
            ->with('service')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                        ->orWhere('fonction', 'like', "%{$search}%")
                        ->orWhere('numero_telephone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('service', function ($serviceQuery) use ($search): void {
                            $serviceQuery->where('nom', 'like', "%{$search}%");
                        });
                });
            })
            ->latest();

        return view('admin.agents.index', [
            'agents' => $agentsQuery->paginate(10)->withQueryString(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.agents.create', [
            'services' => Service::query()->with('direction.entite')->orderBy('nom')->get(['id', 'nom', 'direction_id']),
        ]);
    }

    public function show(Agent $agent): View
    {
        return view('admin.agents.show', [
            'agent' => $agent->load('service.direction.entite'),
        ]);
    }

    public function edit(Agent $agent): View
    {
        return view('admin.agents.edit', [
            'agent' => $agent,
            'services' => Service::query()->with('direction.entite')->orderBy('nom')->get(['id', 'nom', 'direction_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAgent($request);
        $validated['photo_path'] = $this->storeSelectedPhoto($request);

        Agent::query()->create($validated);

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent cree avec succes.');
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $this->validateAgent($request);
        $photo = $this->storeSelectedPhoto($request);

        if ($photo !== null) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = null;
        }

        $agent->update($validated);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Agent mis a jour avec succes.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $this->deletePhoto($agent->photo_path);
        $agent->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent supprime avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAgent(Request $request): array
    {
        return $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'fonction' => ['required', 'string', 'max:255'],
            'numero_telephone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'photo_import' => ['nullable', 'image', 'max:3072'],
            'photo_camera' => ['nullable', 'image', 'max:3072'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);
    }

    private function storeSelectedPhoto(Request $request): ?string
    {
        $photo = $this->selectedPhoto($request);

        if (! $photo instanceof UploadedFile) {
            return null;
        }

        return $photo->store('agents', 'public');
    }

    private function selectedPhoto(Request $request): ?UploadedFile
    {
        $photoCamera = $request->file('photo_camera');
        if ($photoCamera instanceof UploadedFile) {
            return $photoCamera;
        }

        $photoImport = $request->file('photo_import');
        if ($photoImport instanceof UploadedFile) {
            return $photoImport;
        }

        return null;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}