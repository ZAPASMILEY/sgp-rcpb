<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RhAnalytiqueController extends Controller
{
    // ── Comparaison inter-période ────────────────────────────────────────────

    public function comparaison(Request $request): View|Response
    {
        $annees = Annee::orderBy('annee')->get();

        $annee1Id = (int) $request->input('annee1', $annees->first()?->id ?? 0);
        $annee2Id = (int) $request->input('annee2', $annees->last()?->id ?? 0);

        $annee1 = Annee::find($annee1Id);
        $annee2 = Annee::find($annee2Id);

        $stats1 = $this->statsForAnnee($annee1Id);
        $stats2 = $this->statsForAnnee($annee2Id);

        $data = compact('annees', 'annee1', 'annee2', 'stats1', 'stats2');

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('rh.comparaison-pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download('comparaison-rh-'.($annee1?->annee ?? 'A1').'-'.($annee2?->annee ?? 'A2').'.pdf');
        }

        return view('rh.comparaison', $data);
    }

    // ── Helper stats par année ───────────────────────────────────────────────

    private function statsForAnnee(int $anneeId): array
    {
        if (! $anneeId) {
            return $this->emptyStats();
        }

        $totalAgents   = Agent::personnel()->count();
        $agentsEvalues = Evaluation::where('annee_id', $anneeId)
            ->where('statut', 'valide')
            ->where('evaluable_type', Agent::class)
            ->distinct('evaluable_id')
            ->count('evaluable_id');

        $base    = fn () => Evaluation::where('annee_id', $anneeId)->where('statut', '!=', 'brouillon');
        $valides = fn () => Evaluation::where('annee_id', $anneeId)->where('statut', 'valide');
        $fiches  = fn () => FicheObjectif::where('annee_id', $anneeId);

        // Notes moyennes par genre pour cette année
        $moyHom = round(
            Evaluation::where('annee_id', $anneeId)->where('statut', 'valide')
                ->where('evaluable_type', Agent::class)
                ->whereHas('evaluable', fn ($q) => $q->where('sexe', 'homme'))
                ->avg('note_finale') ?? 0,
            2
        );
        $moyFem = round(
            Evaluation::where('annee_id', $anneeId)->where('statut', 'valide')
                ->where('evaluable_type', Agent::class)
                ->whereHas('evaluable', fn ($q) => $q->where('sexe', 'femme'))
                ->avg('note_finale') ?? 0,
            2
        );

        return [
            // Évaluations
            'total'            => $base()->count(),
            'validees'         => $valides()->count(),
            'soumises'         => $base()->where('statut', 'soumis')->count(),
            'refusees'         => $base()->where('statut', 'refuse')->count(),
            'brouillons'       => Evaluation::where('annee_id', $anneeId)->where('statut', 'brouillon')->count(),
            'moyenne'          => round($valides()->avg('note_finale') ?? 0, 2),
            'meilleure'        => round($valides()->max('note_finale') ?? 0, 2),
            'pire'             => round($valides()->min('note_finale') ?? 0, 2),
            'excellent'        => $valides()->where('note_finale', '>=', 8.5)->count(),
            'bien'             => $valides()->whereBetween('note_finale', [7, 8.499])->count(),
            'passable'         => $valides()->whereBetween('note_finale', [5, 6.999])->count(),
            'insuffisant'      => $valides()->where('note_finale', '<', 5)->count(),
            // Genre
            'moy_hommes'       => $moyHom,
            'moy_femmes'       => $moyFem,
            // Objectifs
            'fiches'           => $fiches()->count(),
            'fiches_acceptees' => $fiches()->where('statut', 'acceptee')->count(),
            'fiches_attente'   => $fiches()->whereIn('statut', ['en_attente', 'brouillon'])->count(),
            'fiches_refusees'  => $fiches()->where('statut', 'refusee')->count(),
            // Agents
            'agents_evalues'   => $agentsEvalues,
            'total_agents'     => $totalAgents,
            'taux_completion'  => $totalAgents > 0 ? round($agentsEvalues / $totalAgents * 100, 1) : 0,
        ];
    }

    private function emptyStats(): array
    {
        return array_fill_keys([
            'total', 'validees', 'soumises', 'refusees', 'brouillons',
            'moyenne', 'meilleure', 'pire',
            'excellent', 'bien', 'passable', 'insuffisant',
            'moy_hommes', 'moy_femmes',
            'fiches', 'fiches_acceptees', 'fiches_attente', 'fiches_refusees',
            'agents_evalues', 'total_agents', 'taux_completion',
        ], 0);
    }
}
