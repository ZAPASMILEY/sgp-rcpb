<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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
        $validated = $this->validateDirection($request);

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $validated['directeur_nom'],
            'email'    => $validated['directeur_email'],
            'password' => Hash::make((string) $request->input('password')),
            'role'     => 'directeur',
        ]);

        $validated['user_id'] = $user->id;
        Direction::query()->create($validated);

        $plainPassword = (string) $request->input('password');
        Mail::to($user->email)->send(new WelcomeMail(
            recipientName:  $user->name,
            recipientEmail: $user->email,
            plainPassword:  $plainPassword,
            role:           'directeur',
            loginUrl:       rtrim((string) config('app.url'), '/').'/login',
        ));

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Direction creee avec succes.');
    }

    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $validated = $this->validateDirection($request, $direction);

        $request->validate([
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        if ($direction->user) {
            $userData = [
                'name'  => $validated['directeur_nom'],
                'email' => $validated['directeur_email'],
            ];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make((string) $request->input('password'));
            }
            $direction->user->update($userData);
        }

        $direction->update($validated);

        return redirect()
            ->route('admin.directions.show', $direction)
            ->with('status', 'Direction mise a jour avec succes.');
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->user?->delete();
        $direction->delete();

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Direction supprimee avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDirection(Request $request, ?Direction $direction = null): array
    {
        $emailRule = ['required', 'email', 'max:255'];
        if ($direction === null) {
            $emailRule[] = Rule::unique('users', 'email');
        } else {
            $emailRule[] = Rule::unique('users', 'email')->ignore($direction->user_id);
        }

        return $request->validate([
            'nom'                    => ['required', 'string', 'max:255'],
            'entite_id'              => ['required', 'integer', 'exists:entites,id'],
            'directeur_nom'          => ['required', 'string', 'max:255'],
            'directeur_email'        => $emailRule,
            'secretariat_telephone'  => ['required', 'string', 'max:30'],
        ]);
    }
}
