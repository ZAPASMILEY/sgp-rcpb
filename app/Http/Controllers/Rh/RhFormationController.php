<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Formation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RhFormationController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Formation::with(['agent', 'createdBy']);

        // Filtres
        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('theme', 'like', "%{$search}%")
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

        $agents      = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role']);
        $annees      = range(now()->year + 1, now()->year - 4);
        $domaines    = Formation::DOMAINES;
        $routePrefix = 'rh';

        return view('rh.formations.index', compact('formations', 'agents', 'annees', 'domaines', 'routePrefix'));
    }

    // ── Créer ─────────────────────────────────────────────────────────────────

    public function create(Request $request): View
    {
        $agents              = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role']);
        $domaines            = Formation::DOMAINES;
        $preselectedAgentId  = (int) $request->get('agent_id', 0);
        $themesExistants     = Formation::distinct()->orderBy('theme')->pluck('theme');
        $routePrefix         = 'rh';

        return view('rh.formations.create', compact('agents', 'domaines', 'preselectedAgentId', 'themesExistants', 'routePrefix'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'theme'        => ['required', 'string', 'max:255'],
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
            ->route('rh.formations.index')
            ->with('status', 'Formation « ' . $formation->theme . ' » enregistrée.');
    }

    // ── Modifier ──────────────────────────────────────────────────────────────

    public function edit(Formation $formation): View
    {
        $agents      = Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role']);
        $domaines    = Formation::DOMAINES;
        $routePrefix = 'rh';

        return view('rh.formations.edit', compact('formation', 'agents', 'domaines', 'routePrefix'));
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'     => ['required', 'integer', 'exists:agents,id'],
            'theme'        => ['required', 'string', 'max:255'],
            'domaine'      => ['required', 'string', 'in:' . implode(',', array_keys(Formation::DOMAINES))],
            'date_debut'   => ['required', 'date'],
            'date_fin'     => ['required', 'date', 'after_or_equal:date_debut'],
            'duree_heures' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $formation->update($validated);

        return redirect()
            ->route('rh.formations.index')
            ->with('status', 'Formation « ' . $formation->theme . ' » mise à jour.');
    }

    // ── Supprimer ─────────────────────────────────────────────────────────────

    public function destroy(Formation $formation): RedirectResponse
    {
        $theme = $formation->theme;
        $formation->delete();

        return redirect()
            ->route('rh.formations.index')
            ->with('status', 'Formation « ' . $theme . ' » supprimée.');
    }

    // ── API JSON — formations d'un agent (utilisé par les formulaires d'évaluation) ──

    public function pourAgent(Agent $agent): JsonResponse
    {
        $formations = Formation::where('agent_id', $agent->id)
            ->orderBy('date_debut', 'desc')
            ->get()
            ->map(fn ($f) => [
                'periode' => $f->date_debut->translatedFormat('M Y')
                    . ' – '
                    . ($f->date_fin ? $f->date_fin->translatedFormat('M Y') : 'en cours'),
                'libelle' => $f->theme,
                'domaine' => $f->domaine_label,
            ]);

        return response()->json($formations);
    }

    // ── PDF ───────────────────────────────────────────────────────────────────

    public function pdf(Formation $formation): \Illuminate\Http\Response
    {
        $formation->load('agent.service', 'createdBy');

        $pdf = Pdf::loadView('formations.pdf', compact('formation'))
            ->setPaper('a4', 'portrait');

        $filename = 'formation_' . $formation->id . '_' . str_replace(' ', '_', $formation->theme) . '.pdf';

        return $pdf->download($filename);
    }
}
