<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Agent; // <- La ligne manquante est ici !
use App\Models\Annee;
use App\Models\Evaluation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DgAnalytiqueController extends Controller
{
    // ── Comparaison inter-période ────────────────────────────────────────────

   // ── Comparaison inter-période ────────────────────────────────────────────

    public function comparaison(Request $request): View|Response|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeDg();

        $annees = Annee::orderBy('annee')->get();

        $annee1Id = (int) $request->input('annee1', $annees->first()?->id ?? 0);
        $annee2Id = (int) $request->input('annee2', $annees->last()?->id ?? 0);

        // 🛡️ Sécurité : Empêcher la comparaison de deux années identiques
        if ($annee1Id === $annee2Id && $annees->count() > 1) {
            return redirect()->back()->with('error', 'Veuillez sélectionner deux années différentes pour la comparaison.');
        }

        $annee1 = Annee::find($annee1Id);
        $annee2 = Annee::find($annee2Id);

        $stats1 = $this->statsForAnnee($annee1Id);
        $stats2 = $this->statsForAnnee($annee2Id);

        $data = compact('annees', 'annee1', 'annee2', 'stats1', 'stats2');

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('dg.comparaison-pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download('comparaison-dg-'.($annee1?->annee ?? 'A1').'-'.($annee2?->annee ?? 'A2').'.pdf');
        }

        return view('dg.comparaison', $data);
    }
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function statsForAnnee(int $anneeId): array
    {
        if (! $anneeId) {
            return $this->emptyStats();
        }

        $totalAgents = Agent::personnel()->count();

        // Couvre tous les chemins d'évaluation : User, Agent, Direction, Caisse, DelegationTechnique, Agence, Service, Guichet
        $agentsEvalues = Agent::personnel()
            ->where(fn ($q) => $q
                ->whereHas('evaluationsPersonnel', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide'))
                ->orWhereHas('evaluations',        fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide'))
                ->orWhereHas('directedDirection',  fn ($d) => $d->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
                ->orWhereHas('directedCaisse',     fn ($c) => $c->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
                ->orWhereHas('directedDelegation', fn ($d) => $d->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
                ->orWhereHas('ledAgence',          fn ($a) => $a->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
                ->orWhereHas('ledService',         fn ($s) => $s->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
                ->orWhereHas('ledGuichet',         fn ($g) => $g->whereHas('evaluations', fn ($e) => $e->where('annee_id', $anneeId)->where('statut', 'valide')))
            )
            ->count();

        $base    = fn () => Evaluation::where('annee_id', $anneeId)->where('statut', '!=', 'brouillon');
        $valides = fn () => Evaluation::where('annee_id', $anneeId)->where('statut', 'valide');

        return [
            'total'           => $base()->count(),
            'validees'        => $valides()->count(),
            'soumises'        => $base()->where('statut', 'soumis')->count(),
            'refusees'        => $base()->whereIn('statut', ['refuse', 'reclamation'])->count(),
            'moyenne'         => round($valides()->avg('note_finale') ?? 0, 2),
            'meilleure'       => round($valides()->max('note_finale') ?? 0, 2),
            'pire'            => round($valides()->min('note_finale') ?? 0, 2),
            'excellent'       => $valides()->where('note_finale', '>=', 8.5)->count(),
            'bien'            => $valides()->whereBetween('note_finale', [7, 8.499])->count(),
            'passable'        => $valides()->whereBetween('note_finale', [5, 6.999])->count(),
            'insuffisant'     => $valides()->where('note_finale', '<', 5)->count(),
            'taux_completion' => $totalAgents > 0 ? round($agentsEvalues / $totalAgents * 100, 1) : 0,
            'agents_evalues'  => $agentsEvalues,
            'total_agents'    => $totalAgents,
        ];
    }

    private function emptyStats(): array
    {
        return array_fill_keys([
            'total', 'validees', 'soumises', 'refusees',
            'moyenne', 'meilleure', 'pire',
            'excellent', 'bien', 'passable', 'insuffisant',
            'taux_completion', 'agents_evalues', 'total_agents',
        ], 0);
    }

    private function authorizeDg(): void
    {
        $user = Auth::user();
        if (! $user || strtolower((string) $user->role) !== 'dg') {
            abort(403, 'Accès réservé au Directeur Général.');
        }
    }
}