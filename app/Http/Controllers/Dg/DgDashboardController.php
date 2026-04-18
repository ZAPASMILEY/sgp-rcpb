<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DgDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403, 'Accès réservé au Directeur Général.');
        }

        $statut  = trim((string) $request->get('statut', ''));
        $search  = trim((string) $request->get('search', ''));
        $anneeId = (int) $request->get('annee', 0);

        // ── Base : toutes les évaluations du réseau (hors brouillons) ──────────
        $base = fn () => Evaluation::query()->where('statut', '!=', 'brouillon');

        // ── Stats globales ────────────────────────────────────────────────────
        $stats = [
            'total'       => $base()->count(),
            'soumis'      => $base()->where('statut', 'soumis')->count(),
            'valide'      => $base()->where('statut', 'valide')->count(),
            'excellent'   => $base()->where('note_finale', '>=', 8.5)->count(),
            'bien'        => $base()->whereBetween('note_finale', [7, 8.499])->count(),
            'passable'    => $base()->whereBetween('note_finale', [5, 6.999])->count(),
            'insuffisant' => $base()->where('note_finale', '<', 5)->count(),
        ];

        // ── Requête principale avec filtres ───────────────────────────────────
        $query = Evaluation::query()
            ->with(['identification', 'evaluateur'])
            ->where('statut', '!=', 'brouillon')
            ->orderByDesc('updated_at');

        if ($statut) {
            $query->where('statut', $statut);
        }

        if ($anneeId) {
            $query->where('annee_id', $anneeId);
        }

        if ($search !== '') {
            $query->whereHas('identification', fn ($q) =>
                $q->where('nom_prenom', 'like', "%{$search}%")
                  ->orWhere('emploi', 'like', "%{$search}%")
            );
        }

        $evaluations = $query->paginate(20)->withQueryString();

        // ── Liste des années pour le filtre ───────────────────────────────────
        $annees = Annee::orderByDesc('annee')->get();

        // ── Meilleure note / note la plus basse (parmi validées) ──────────────
        $topEval = Evaluation::with('identification')
            ->where('statut', 'valide')
            ->orderByDesc('note_finale')
            ->first();

        $bottomEval = Evaluation::with('identification')
            ->where('statut', 'valide')
            ->orderBy('note_finale')
            ->first();

        $filters = compact('statut', 'search', 'anneeId');

        return view('dg.dashboard', compact(
            'stats',
            'evaluations',
            'annees',
            'topEval',
            'bottomEval',
            'filters',
        ));
    }
}
