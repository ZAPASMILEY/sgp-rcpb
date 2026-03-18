<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agent;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
        $this->validateCredentials($request);

        $validated['photo_path'] = $this->storeSelectedPhoto($request);

        $user = User::create([
            'name'     => $validated['prenom'].' '.$validated['nom'],
            'email'    => $validated['email'],
            'password' => Hash::make((string) $request->input('password')),
            'role'     => 'agent',
        ]);

        $validated['user_id'] = $user->id;
        Agent::query()->create($validated);

        $plainPassword = (string) $request->input('password');
        Mail::to($user->email)->send(new WelcomeMail(
            recipientName:  $user->name,
            recipientEmail: $user->email,
            plainPassword:  $plainPassword,
            role:           'agent',
            loginUrl:       rtrim((string) config('app.url'), '/').'/login',
        ));

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent cree avec succes.');
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $this->validateAgent($request, $agent);
        $this->validatePasswordUpdate($request);

        $photo = $this->storeSelectedPhoto($request);

        if ($photo !== null) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = null;
        }

        if ($agent->user) {
            $userData = [
                'name'  => $validated['prenom'].' '.$validated['nom'],
                'email' => $validated['email'],
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make((string) $request->input('password'));
            }
            $agent->user->update($userData);
        }

        $agent->update($validated);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Agent mis a jour avec succes.');
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $this->deletePhoto($agent->photo_path);
        $agent->user?->delete();
        $agent->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent supprime avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAgent(Request $request, ?Agent $agent = null): array
    {
        $emailRule = ['required', 'email', 'max:255'];
        if ($agent === null) {
            $emailRule[] = Rule::unique('users', 'email');
        } else {
            $emailRule[] = Rule::unique('users', 'email')->ignore($agent->user_id);
        }

        return $request->validate([
            'service_id'       => ['required', 'integer', 'exists:services,id'],
            'nom'              => ['required', 'string', 'max:255'],
            'prenom'           => ['required', 'string', 'max:255'],
            'fonction'         => ['required', 'string', 'max:255'],
            'numero_telephone' => ['required', 'string', 'max:30'],
            'email'            => $emailRule,
            'photo_import'     => ['nullable', 'image', 'max:3072'],
            'photo_camera'     => ['nullable', 'image', 'max:3072'],
            'remove_photo'     => ['nullable', 'boolean'],
        ]);
    }

    private function validateCredentials(Request $request): void
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
    }

    private function validatePasswordUpdate(Request $request): void
    {
        $request->validate([
            'password' => ['nullable', 'confirmed', Password::min(8)],
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