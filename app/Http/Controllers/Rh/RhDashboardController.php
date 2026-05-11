<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\DelegationTechnique;
use App\Models\Caisse;
use App\Models\Direction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RhDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tab    = $request->input('tab', 'evaluations');
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));
        $annee  = trim((string) $request->input('annee', ''));

        // ── KPI globaux (toutes évaluations — agents ET cadres) ───────────────
        $stats = [
            'agents'      => Agent::count(),
            'total'       => Evaluation::count(),
            'soumis'      => Evaluation::where('statut', 'soumis')->count(),
            'valide'      => Evaluation::where('statut', 'valide')->count(),
            'refuse'      => Evaluation::where('statut', 'refuse')->count(),
            'brouillon'   => Evaluation::where('statut', 'brouillon')->count(),
            'excellent'   => Evaluation::where('statut', '!=', 'brouillon')->where('note_finale', '>=', 8.5)->count(),
            'bien'        => Evaluation::where('statut', '!=', 'brouillon')->where('note_finale', '>=', 7)->where('note_finale', '<', 8.5)->count(),
            'passable'    => Evaluation::where('statut', '!=', 'brouillon')->where('note_finale', '>=', 5)->where('note_finale', '<', 7)->count(),
            'insuffisant' => Evaluation::where('statut', '!=', 'brouillon')->where('note_finale', '>', 0)->where('note_finale', '<', 5)->count(),
            'objectifs'   => FicheObjectif::count(),
            'obj_accepte' => FicheObjectif::where('statut', 'acceptee')->count(),
        ];

        // ── Top / Bottom performers ───────────────────────────────────────────
        $topEval = Evaluation::whereIn('statut', ['soumis', 'valide'])
            ->where('note_finale', '>', 0)
            ->with('identification')
            ->orderByDesc('note_finale')
            ->first();

        $bottomEval = Evaluation::whereIn('statut', ['soumis', 'valide'])
            ->where('note_finale', '>', 0)
            ->with('identification')
            ->orderBy('note_finale')
            ->first();

        // ── Filtres pour les listes ───────────────────────────────────────────
        $delegations = DelegationTechnique::orderBy('region')->get(['id', 'region', 'ville']);
        $caisses     = Caisse::orderBy('nom')->get(['id', 'nom']);
        $directions  = Direction::orderBy('nom')->get(['id', 'nom']);

        // ── Tab Évaluations : TOUTES les évaluations (agents + cadres) ────────
        $evaluations = null;
        if ($tab === 'evaluations') {
            $q = Evaluation::with([
                'evaluateur:id,name,role',
                'evaluable',
                'identification:id,evaluation_id,nom_prenom,emploi',
            ])->orderByDesc('date_debut');

            if ($statut) $q->where('statut', $statut);
            if ($annee)  $q->whereYear('date_debut', $annee);
            if ($search) {
                $q->where(function ($s) use ($search) {
                    $s->whereHas('identification', fn ($i) =>
                            $i->where('nom_prenom', 'like', "%{$search}%")
                              ->orWhere('emploi', 'like', "%{$search}%")
                      )
                      ->orWhereHas('evaluateur', fn ($e) =>
                            $e->where('name', 'like', "%{$search}%")
                      );
                });
            }

            $evaluations = $q->paginate(25)->withQueryString();
        }

        // ── Tab Agents ────────────────────────────────────────────────────────
        $agents = null;
        if ($tab === 'agents') {
            $q = Agent::with([
                'delegationTechnique:id,region,ville',
                'caisse:id,nom',
                'agence:id,nom',
                'direction:id,nom',
                'service:id,nom',
                'user:id,agent_id,role',
            ])->orderBy('nom')->orderBy('prenom');

            if ($search) {
                $q->where(fn ($s) =>
                    $s->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('fonction', 'like', "%{$search}%")
                );
            }
            if ($request->input('dt_id'))     $q->where('delegation_technique_id', $request->input('dt_id'));
            if ($request->input('caisse_id')) $q->where('caisse_id', $request->input('caisse_id'));
            if ($request->input('dir_id'))    $q->where('direction_id', $request->input('dir_id'));

            $agents = $q->paginate(20)->withQueryString();
        }

        // ── Tab Objectifs ─────────────────────────────────────────────────────
        $fiches     = null;
        $ficheStats = null;
        if ($tab === 'objectifs') {
            $ficheStats = [
                'total'      => FicheObjectif::count(),
                'acceptee'   => FicheObjectif::where('statut', 'acceptee')->count(),
                'en_attente' => FicheObjectif::whereIn('statut', ['en_attente', 'brouillon'])->count(),
                'refusee'    => FicheObjectif::where('statut', 'refusee')->count(),
            ];

            $q = FicheObjectif::with(['assignable'])
            ->withCount('objectifs')
            ->orderByDesc('date');

            if ($statut) $q->where('statut', $statut);
            if ($annee)  $q->whereYear('date', $annee);
            if ($search) {
                $q->where('titre', 'like', "%{$search}%")
                  ->orWhereHas('assignable', fn ($s) =>
                      $s->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                  );
            }

            $fiches = $q->paginate(20)->withQueryString();
        }

        $filters = compact('tab', 'statut', 'search', 'annee');

        return view('rh.dashboard', compact(
            'stats',
            'topEval',
            'bottomEval',
            'tab',
            'filters',
            'delegations',
            'caisses',
            'directions',
            'agents',
            'evaluations',
            'fiches',
            'ficheStats',
        ));
    }
}
