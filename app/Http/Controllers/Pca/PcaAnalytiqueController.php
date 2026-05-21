<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PcaAnalytiqueController extends Controller
{
    // ── Résolution du contexte PCA → DG ────────────────────────────────────

    private function dgContext(Request $request): array
    {
        $entite = Entite::where('pca_agent_id', $request->user()->agent_id)->firstOrFail();

        $dgUser   = $entite->dg_agent_id
            ? User::where('agent_id', $entite->dg_agent_id)->first()
            : null;
        $dgUserId = $dgUser?->id ?? 0;

        return compact('entite', 'dgUser', 'dgUserId');
    }

    // ── Comparaison inter-période ────────────────────────────────────────────

    public function comparaison(Request $request): View|Response
    {
        ['entite' => $entite, 'dgUser' => $dgUser, 'dgUserId' => $dgUserId] = $this->dgContext($request);

        $annees = Annee::orderBy('annee')->get();

        $annee1Id = (int) $request->input('annee1', $annees->first()?->id ?? 0);
        $annee2Id = (int) $request->input('annee2', $annees->last()?->id ?? 0);

        $annee1 = Annee::find($annee1Id);
        $annee2 = Annee::find($annee2Id);

        $stats1 = $this->statsForAnnee($dgUserId, $annee1Id);
        $stats2 = $this->statsForAnnee($dgUserId, $annee2Id);

        $data = compact('entite', 'dgUser', 'annees', 'annee1', 'annee2', 'stats1', 'stats2');

        if ($request->query('format') === 'pdf') {
            $pdf = Pdf::loadView('pca.comparaison-pdf', $data)->setPaper('a4', 'landscape');
            return $pdf->download('comparaison-pca-'.($annee1?->annee ?? 'A1').'-'.($annee2?->annee ?? 'A2').'.pdf');
        }

        return view('pca.comparaison', $data);
    }

    // ── Helper stats par année ───────────────────────────────────────────────

    private function statsForAnnee(int $dgUserId, int $anneeId): array
    {
        if (! $anneeId || ! $dgUserId) {
            return $this->emptyStats();
        }

        $fiches      = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $dgUserId)
            ->where('annee_id', $anneeId);
        $evals       = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $dgUserId)
            ->where('annee_id', $anneeId);
        $evalsValides = fn () => $evals()->where('statut', 'valide');

        return [
            'fiches'           => $fiches()->count(),
            'fiches_acceptees' => $fiches()->where('statut', 'acceptee')->count(),
            'fiches_attente'   => $fiches()->where('statut', 'en_attente')->count(),
            'fiches_refusees'  => $fiches()->where('statut', 'refusee')->count(),
            'avancement'       => (int) round($fiches()->avg('avancement_percentage') ?? 0),
            'evals'            => $evals()->count(),
            'evals_validees'   => $evalsValides()->count(),
            'evals_soumises'   => $evals()->where('statut', 'soumis')->count(),
            'evals_brouillon'  => $evals()->where('statut', 'brouillon')->count(),
            'evals_refusees'   => $evals()->where('statut', 'refuse')->count(),
            'moyenne'          => round($evalsValides()->avg('note_finale') ?? 0, 2),
            'meilleure'        => round($evalsValides()->max('note_finale') ?? 0, 2),
        ];
    }

    private function emptyStats(): array
    {
        return array_fill_keys([
            'fiches', 'fiches_acceptees', 'fiches_attente', 'fiches_refusees', 'avancement',
            'evals', 'evals_validees', 'evals_soumises', 'evals_brouillon', 'evals_refusees',
            'moyenne', 'meilleure',
        ], 0);
    }
}
