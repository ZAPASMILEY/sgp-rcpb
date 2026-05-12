<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Formation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des formations accessible à tout utilisateur
 * disposant de la permission 'formations.assigner', quelle que soit son rôle.
 *
 * Les routes sont protégées par le middleware can:formations.assigner.
 */
class FormationGererController extends Controller
{
    /**
     * Détermine le layout Blade à utiliser selon le rôle de l'utilisateur connecté.
     */
    private function layout(): string
    {
        $role = Auth::user()?->role ?? '';

        return match (true) {
            $role === 'Admin'                                    => 'layouts.app',
            $role === 'DG'                                       => 'layouts.dg',
            $role === 'DGA'                                      => 'layouts.dga',
            $role === 'PCA'                                      => 'layouts.pca',
            $role === 'RH'                                       => 'layouts.rh',
            str_starts_with($role, 'Directeur_')                => 'layouts.directeur',
            str_starts_with($role, 'Chef_')                     => 'layouts.chef',
            str_starts_with($role, 'Secretaire_')               => 'layouts.personnel',
            in_array($role, ['Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'], true)
                                                                 => 'layouts.personnel',
            default                                              => 'layouts.app',
        };
    }

    // ── Liste ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Formation::with(['agent', 'createdBy']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhereHas('agent', fn ($a) =>
                      $a->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                  );
            });
        }

        if ($domaine = $request->get('domaine')) {
            $query->where('domaine', $domaine);
        }

        if ($annee = $request->get('annee')) {
            $query->whereYear('date_debut', $annee);
        }

        if ($agentId = $request->get('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        $formations = $query->orderByDesc('date_debut')->paginate(15)->withQueryString();
        $agents     = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'fonction']);
        $annees     = range(now()->year + 1, now()->year - 4);
        $domaines   = Formation::DOMAINES;
        $layout     = $this->layout();

        // Routes pour les actions CRUD dans la vue (on réutilise les routes gerer.*)
        $routePrefix = 'gerer';

        return view('rh.formations.index', compact('formations', 'agents', 'annees', 'domaines', 'layout', 'routePrefix'));
    }

    // ── Créer ──────────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $agents              = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'fonction']);
        $domaines            = Formation::DOMAINES;
        $preselectedAgentId  = (int) $request->get('agent_id', 0);
        $titresExistants     = Formation::distinct()->orderBy('titre')->pluck('titre');
        $layout              = $this->layout();
        $routePrefix         = 'gerer';

        return view('rh.formations.create', compact('agents', 'domaines', 'preselectedAgentId', 'titresExistants', 'layout', 'routePrefix'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'titre'        => ['required', 'string', 'max:255'],
            'domaine'      => ['required', 'string', 'in:' . implode(',', array_keys(Formation::DOMAINES))],
            'date_debut'   => ['required', 'date'],
            'date_fin'     => ['required', 'date', 'after_or_equal:date_debut'],
            'duree_heures' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $formation = Formation::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('gerer.formations.index')
            ->with('status', 'Formation « ' . $formation->titre . ' » enregistrée.');
    }

    // ── Modifier ───────────────────────────────────────────────────────────────

    public function edit(Formation $formation): View
    {
        $agents      = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'fonction']);
        $domaines    = Formation::DOMAINES;
        $layout      = $this->layout();
        $routePrefix = 'gerer';

        return view('rh.formations.edit', compact('formation', 'agents', 'domaines', 'layout', 'routePrefix'));
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'titre'        => ['required', 'string', 'max:255'],
            'domaine'      => ['required', 'string', 'in:' . implode(',', array_keys(Formation::DOMAINES))],
            'date_debut'   => ['required', 'date'],
            'date_fin'     => ['required', 'date', 'after_or_equal:date_debut'],
            'duree_heures' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $formation->update($validated);

        return redirect()
            ->route('gerer.formations.index')
            ->with('status', 'Formation mise à jour.');
    }

    // ── Supprimer ──────────────────────────────────────────────────────────────

    public function destroy(Formation $formation): RedirectResponse
    {
        $titre = $formation->titre;
        $formation->delete();

        return redirect()
            ->route('gerer.formations.index')
            ->with('status', 'Formation « ' . $titre . ' » supprimée.');
    }

    // ── PDF ────────────────────────────────────────────────────────────────────

    public function pdf(Formation $formation): \Illuminate\Http\Response
    {
        $formation->load('agent.service', 'createdBy');

        $pdf = Pdf::loadView('formations.pdf', compact('formation'))
            ->setPaper('a4', 'portrait');

        $filename = 'formation_' . $formation->id . '_' . str_replace(' ', '_', $formation->titre) . '.pdf';

        return $pdf->download($filename);
    }
}
