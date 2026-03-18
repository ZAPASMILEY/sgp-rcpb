<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EntiteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $ville = trim((string) $request->query('ville', ''));

        $entitesQuery = Entite::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('ville', 'like', "%{$search}%")
                        ->orWhere('directrice_generale_nom', 'like', "%{$search}%")
                        ->orWhere('directrice_generale_prenom', 'like', "%{$search}%")
                        ->orWhere('directrice_generale_email', 'like', "%{$search}%")
                        ->orWhere('pca_prenom', 'like', "%{$search}%")
                        ->orWhere('pca_nom', 'like', "%{$search}%");
                });
            })
            ->when($ville !== '', function ($query) use ($ville): void {
                $query->where('ville', $ville);
            })
            ->latest();

        return view('admin.entites.index', [
            'entites' => $entitesQuery->paginate(10)->withQueryString(),
            'filters' => [
                'search' => $search,
                'ville' => $ville,
            ],
            'villes' => Entite::query()
                ->select('ville')
                ->whereNotNull('ville')
                ->distinct()
                ->orderBy('ville')
                ->pluck('ville'),
        ]);
    }

    public function show(Entite $entite): View
    {
        return view('admin.entites.show', [
            'entite' => $entite,
        ]);
    }

    public function create(): View
    {
        return view('admin.entites.create');
    }

    public function edit(Entite $entite): View
    {
        return view('admin.entites.edit', [
            'entite' => $entite,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEntite($request);

        $request->validate([
            'pca_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $entite = Entite::query()->create($validated);

        $plainPassword = (string) $request->input('pca_password');
        $pcaUser = User::create([
            'name'          => $validated['pca_prenom'].' '.$validated['pca_nom'],
            'email'         => $validated['pca_email'],
            'password'      => Hash::make($plainPassword),
            'role'          => 'pca',
            'pca_entite_id' => $entite->id,
        ]);

        Mail::to($pcaUser->email)->send(new WelcomeMail(
            recipientName:  $pcaUser->name,
            recipientEmail: $pcaUser->email,
            plainPassword:  $plainPassword,
            role:           'pca',
            loginUrl:       rtrim((string) config('app.url'), '/').'/login',
        ));

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Entite creee avec succes.');
    }

    public function update(Request $request, Entite $entite): RedirectResponse
    {
        $validated = $this->validateEntite($request, $entite);

        $request->validate([
            'pca_password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $pcaUser = User::where('pca_entite_id', $entite->id)->where('role', 'pca')->first();
        if ($pcaUser) {
            $userData = [
                'name'  => $validated['pca_prenom'].' '.$validated['pca_nom'],
                'email' => $validated['pca_email'],
            ];
            if ($request->filled('pca_password')) {
                $userData['password'] = Hash::make((string) $request->input('pca_password'));
            }
            $pcaUser->update($userData);
        }

        $entite->update($validated);

        return redirect()
            ->route('admin.entites.show', $entite)
            ->with('status', 'Entite mise a jour avec succes.');
    }

    public function destroy(Entite $entite): RedirectResponse
    {
        User::where('pca_entite_id', $entite->id)->where('role', 'pca')->delete();
        $entite->delete();

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Entite supprimee avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEntite(Request $request, ?Entite $entite = null): array
    {
        $pcaEmailRule = ['required', 'email', 'max:255'];
        if ($entite === null) {
            $pcaEmailRule[] = Rule::unique('users', 'email');
        } else {
            $pcaUser = User::where('pca_entite_id', $entite->id)->where('role', 'pca')->first();
            $pcaEmailRule[] = Rule::unique('users', 'email')->ignore($pcaUser?->id);
        }

        return $request->validate([
            'nom'                          => ['required', 'string', 'max:255'],
            'ville'                        => ['required', 'string', 'max:255'],
            'directrice_generale_prenom'   => ['required', 'string', 'max:255'],
            'directrice_generale_nom'      => ['required', 'string', 'max:255'],
            'directrice_generale_email'    => ['required', 'email', 'max:255'],
            'pca_prenom'                   => ['required', 'string', 'max:255'],
            'pca_nom'                      => ['required', 'string', 'max:255'],
            'pca_email'                    => $pcaEmailRule,
            'secretariat_telephone'        => ['required', 'string', 'max:30'],
        ]);
    }
}
