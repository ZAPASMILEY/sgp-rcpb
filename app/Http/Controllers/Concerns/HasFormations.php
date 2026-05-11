<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use App\Models\Formation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Trait partagé par tous les rôles qui ont une page "Mes formations".
 *
 * Utilisation :
 *   - Implémenter getAgentIds(Request $request): array
 *     Retourne les IDs des agents dont les formations sont visibles.
 *   - Implémenter getLayoutName(): string
 *     Retourne le nom du layout Blade à étendre.
 *   - Implémenter getPdfRoutePrefix(): string
 *     Retourne le préfixe de route pour le PDF (ex: 'personnel').
 */
trait HasFormations
{
    /**
     * IDs des agents dont on affiche les formations.
     * Doit être implémenté par chaque contrôleur.
     */
    abstract protected function getAgentIds(Request $request): array;

    /** Layout Blade à étendre (ex: 'layouts.personnel') */
    abstract protected function getLayoutName(): string;

    /** Préfixe de route PDF (ex: 'personnel' → route 'personnel.formations.pdf') */
    abstract protected function getPdfRoutePrefix(): string;

    // ── Page Mes formations ───────────────────────────────────────────────────

    public function mesFormations(Request $request): View
    {
        $agentIds = $this->getAgentIds($request);

        $query = Formation::with('agent')
            ->whereIn('agent_id', $agentIds ?: [0]);

        // Filtres
        if ($domaine = $request->get('domaine')) {
            $query->where('domaine', $domaine);
        }

        if ($annee = $request->get('annee')) {
            $query->whereYear('date_debut', $annee);
        }

        if ($search = trim((string) $request->get('search'))) {
            $query->where('titre', 'like', "%{$search}%");
        }

        $formations = $query->orderByDesc('date_debut')->paginate(15)->withQueryString();

        // Stats
        $stats = [
            'total'     => Formation::whereIn('agent_id', $agentIds ?: [0])->count(),
            'ce_mois'   => Formation::whereIn('agent_id', $agentIds ?: [0])
                ->whereMonth('date_debut', now()->month)
                ->whereYear('date_debut', now()->year)
                ->count(),
            'heures'    => Formation::whereIn('agent_id', $agentIds ?: [0])->sum('duree_heures'),
        ];

        $domaines        = Formation::DOMAINES;
        $annees          = range(now()->year + 1, now()->year - 4);
        $layout          = $this->getLayoutName();
        $pdfRoutePrefix  = $this->getPdfRoutePrefix();

        return view('formations.mes-formations', compact(
            'formations', 'stats', 'domaines', 'annees', 'layout', 'pdfRoutePrefix'
        ));
    }

    // ── PDF d'une formation ───────────────────────────────────────────────────

    public function formationPdf(Request $request, Formation $formation): \Illuminate\Http\Response
    {
        // Sécurité : vérifier que la formation appartient à un agent autorisé
        $agentIds = $this->getAgentIds($request);
        abort_unless(in_array($formation->agent_id, $agentIds, false), 403);

        $formation->load('agent', 'createdBy');

        $pdf = Pdf::loadView('formations.pdf', compact('formation'))
            ->setPaper('a4', 'portrait');

        $filename = 'formation_' . $formation->id . '_' . str_replace(' ', '_', $formation->titre) . '.pdf';

        return $pdf->download($filename);
    }
}
