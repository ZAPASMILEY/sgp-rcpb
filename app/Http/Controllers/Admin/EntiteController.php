<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        Entite::query()->create($this->validateEntite($request));

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Entite creee avec succes.');
    }

    public function update(Request $request, Entite $entite): RedirectResponse
    {
        $entite->update($this->validateEntite($request));

        return redirect()
            ->route('admin.entites.show', $entite)
            ->with('status', 'Entite mise a jour avec succes.');
    }

    public function destroy(Entite $entite): RedirectResponse
    {
        $entite->delete();

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Entite supprimee avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEntite(Request $request): array
    {
        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:255'],
            'directrice_generale_prenom' => ['required', 'string', 'max:255'],
            'directrice_generale_nom' => ['required', 'string', 'max:255'],
            'directrice_generale_email' => ['required', 'email', 'max:255'],
            'pca_prenom' => ['required', 'string', 'max:255'],
            'pca_nom' => ['required', 'string', 'max:255'],
            'pca_email' => ['required', 'email', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
        ]);
    }
}
