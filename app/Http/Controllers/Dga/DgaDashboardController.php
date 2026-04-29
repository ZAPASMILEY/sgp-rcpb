<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\Guichet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DgaDashboardController extends Controller
{
    /** Rôles de la faîtière — exclus des stats de notes réseau. */
    private const FAITIERE_ROLES = ['DG', 'DGA', 'PCA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function __invoke(Request $request)
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'DGA') {
            abort(403, 'Accès réservé au DGA.');
        }

        $statut  = trim((string) $request->get('statut', ''));
        $search  = trim((string) $request->get('search', ''));
        $anneeId = (int) $request->get('annee', 0);

        // ── Réseau : comptages ───────────────────────────────────────────────
        $reseauStats = [
            'delegations' => DelegationTechnique::count(),
            'caisses'     => Caisse::count(),
            'agences'     => Agence::count(),
            'guichets'    => Guichet::count(),
        ];

        // ── Note moyenne réseau (hors faîtière) ──────────────────────────────
        $faitiereAgentIds = User::whereIn('role', self::FAITIERE_ROLES)
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->all();

        $noteReseau = Evaluation::where('evaluable_type', Agent::class)
            ->whereNotIn('evaluable_id', $faitiereAgentIds)
            ->where('statut', 'valide')
            ->whereNotNull('note_finale')
            ->avg('note_finale');

        // ── Évaluations données par le DGA (à ses subordonnés) ───────────────
        $baseSubEvals = fn () => Evaluation::where('evaluateur_id', $user->id);

        $subStats = [
            'total'       => $baseSubEvals()->count(),
            'brouillon'   => $baseSubEvals()->where('statut', 'brouillon')->count(),
            'soumis'      => $baseSubEvals()->where('statut', 'soumis')->count(),
            'valide'      => $baseSubEvals()->where('statut', 'valide')->count(),
        ];

        // ── Évaluations récentes données par le DGA ───────────────────────────
        $queryRecent = Evaluation::with(['identification', 'evaluateur'])
            ->where('evaluateur_id', $user->id)
            ->orderByDesc('updated_at');

        if ($statut) {
            $queryRecent->where('statut', $statut);
        }
        if ($anneeId) {
            $queryRecent->where('annee_id', $anneeId);
        }
        if ($search !== '') {
            $queryRecent->whereHas('identification', fn ($q) =>
                $q->where('nom_prenom', 'like', "%{$search}%")
                  ->orWhere('emploi', 'like', "%{$search}%")
            );
        }

        $evaluations = $queryRecent->paginate(15)->withQueryString();

        $annees = Annee::orderByDesc('annee')->get();

        // ── Meilleure / plus basse note des subordonnés du DGA ───────────────
        $topEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)
            ->where('statut', 'valide')
            ->orderByDesc('note_finale')
            ->first();

        $bottomEval = Evaluation::with('identification')
            ->where('evaluateur_id', $user->id)
            ->where('statut', 'valide')
            ->orderBy('note_finale')
            ->first();

        $filters = compact('statut', 'search', 'anneeId');

        return view('dga.dashboard', compact(
            'reseauStats',
            'noteReseau',
            'subStats',
            'evaluations',
            'annees',
            'topEval',
            'bottomEval',
            'filters',
        ));
    }
}
