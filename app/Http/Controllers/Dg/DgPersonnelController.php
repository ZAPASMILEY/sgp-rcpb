<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\EvaluationIdentification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgPersonnelController extends Controller
{
    private function checkDg(): void
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403);
        }
    }

    public function __invoke(Request $request): View
    {
        $this->checkDg();

        $search      = trim((string) $request->get('search', ''));
        $anneeId     = (int) $request->get('annee', 0);
        $semestre    = trim((string) $request->get('semestre', ''));
        $appreciation = trim((string) $request->get('appreciation', ''));
        $statut      = trim((string) $request->get('statut', ''));
        $emploi      = trim((string) $request->get('emploi', ''));
        $structure   = trim((string) $request->get('structure', ''));
        $sort        = $request->get('sort', 'note_desc');

        // ── Base query ────────────────────────────────────────────────────────
        $query = Evaluation::query()
            ->with(['identification', 'evaluateur', 'annee'])
            ->where('statut', '!=', 'brouillon')
            ->whereHas('identification');

        // ── Filtres ───────────────────────────────────────────────────────────
        if ($anneeId) {
            $query->where('annee_id', $anneeId);
        }

        if ($statut) {
            $query->where('statut', $statut);
        }

        // Filtre appréciation sur note_finale
        match ($appreciation) {
            'excellent'   => $query->where('note_finale', '>=', 8.5),
            'bien'        => $query->whereBetween('note_finale', [7, 8.4999]),
            'passable'    => $query->whereBetween('note_finale', [5, 6.9999]),
            'insuffisant' => $query->where('note_finale', '<', 5),
            default       => null,
        };

        // Filtres sur identification (relation)
        if ($search !== '' || $emploi !== '' || $structure !== '' || $semestre !== '') {
            $query->whereHas('identification', function ($q) use ($search, $emploi, $structure, $semestre) {
                if ($search !== '') {
                    $q->where(fn ($s) => $s
                        ->where('nom_prenom', 'like', "%{$search}%")
                        ->orWhere('emploi', 'like', "%{$search}%")
                        ->orWhere('matricule', 'like', "%{$search}%")
                    );
                }
                if ($emploi !== '') {
                    $q->where('emploi', 'like', "%{$emploi}%");
                }
                if ($structure !== '') {
                    $q->where(fn ($s) => $s
                        ->where('direction', 'like', "%{$structure}%")
                        ->orWhere('direction_service', 'like', "%{$structure}%")
                    );
                }
                if ($semestre !== '') {
                    $q->where('semestre', $semestre);
                }
            });
        }

        // ── Tri ───────────────────────────────────────────────────────────────
        match ($sort) {
            'note_asc'    => $query->orderBy('note_finale', 'asc'),
            'note_desc'   => $query->orderByDesc('note_finale'),
            'nom_asc'     => $query->orderBy(
                                EvaluationIdentification::select('nom_prenom')
                                    ->whereColumn('evaluation_id', 'evaluations.id')
                                    ->limit(1),
                                'asc'
                            ),
            'date_desc'   => $query->orderByDesc('date_fin'),
            'date_asc'    => $query->orderBy('date_fin'),
            default       => $query->orderByDesc('note_finale'),
        };

        $evaluations = $query->paginate(25)->withQueryString();

        // ── Stats rapides ─────────────────────────────────────────────────────
        $baseStats = Evaluation::query()
            ->where('statut', '!=', 'brouillon')
            ->whereHas('identification');

        $stats = [
            'total'       => $baseStats->clone()->count(),
            'excellent'   => $baseStats->clone()->where('note_finale', '>=', 8.5)->count(),
            'bien'        => $baseStats->clone()->whereBetween('note_finale', [7, 8.4999])->count(),
            'passable'    => $baseStats->clone()->whereBetween('note_finale', [5, 6.9999])->count(),
            'insuffisant' => $baseStats->clone()->where('note_finale', '<', 5)->count(),
            'moyenne'     => round((float) $baseStats->clone()->avg('note_finale'), 2),
        ];

        // ── Listes pour filtres ───────────────────────────────────────────────
        $annees  = Annee::orderByDesc('annee')->get();

        // Emplois distincts dans les identifications
        $emplois = EvaluationIdentification::distinct()
            ->whereNotNull('emploi')
            ->orderBy('emploi')
            ->pluck('emploi');

        // Structures distinctes (direction)
        $structures = EvaluationIdentification::distinct()
            ->whereNotNull('direction')
            ->orderBy('direction')
            ->pluck('direction');

        $filters = compact('search', 'anneeId', 'semestre', 'appreciation', 'statut', 'emploi', 'structure', 'sort');

        return view('dg.personnel.index', compact(
            'evaluations',
            'stats',
            'annees',
            'emplois',
            'structures',
            'filters',
        ));
    }
}
