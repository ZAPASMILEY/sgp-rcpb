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
        $tab    = $request->get('tab', 'agents');
        $statut = trim((string) $request->get('statut', ''));
        $search = trim((string) $request->get('search', ''));
        $annee  = trim((string) $request->get('annee', ''));

        // ── Stats globales ────────────────────────────────────────────────────
        $stats = [
            'agents'      => Agent::count(),
            'evaluations' => Evaluation::count(),
            'eval_valide' => Evaluation::where('statut', 'valide')->count(),
            'objectifs'   => FicheObjectif::count(),
            'obj_accepte' => FicheObjectif::where('statut', 'acceptee')->count(),
        ];

        // ── Données pour les filtres ─────────────────────────────────────────
        $delegations = DelegationTechnique::orderBy('region')->get(['id', 'region', 'ville']);
        $caisses     = Caisse::orderBy('nom')->get(['id', 'nom']);
        $directions  = Direction::orderBy('nom')->get(['id', 'nom']);

        // ── Tab Agents ───────────────────────────────────────────────────────
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

            $dtId      = $request->get('dt_id');
            $caisseId  = $request->get('caisse_id');
            $dirId     = $request->get('dir_id');

            if ($dtId)     $q->where('delegation_technique_id', $dtId);
            if ($caisseId) $q->where('caisse_id', $caisseId);
            if ($dirId)    $q->where('direction_id', $dirId);

            $agents = $q->paginate(20)->withQueryString();
        }

        // ── Tab Évaluations ──────────────────────────────────────────────────
        $evaluations = null;
        $evalStats   = null;
        if ($tab === 'evaluations') {
            $baseE = fn () => Evaluation::where('evaluable_type', Agent::class);

            $evalStats = [
                'total'     => $baseE()->count(),
                'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
                'soumis'    => $baseE()->where('statut', 'soumis')->count(),
                'valide'    => $baseE()->where('statut', 'valide')->count(),
                'refuse'    => $baseE()->where('statut', 'refuse')->count(),
            ];

            $q = Evaluation::with([
                'evaluateur:id,name,role',
                'evaluable' => fn ($m) => $m->select('id', 'nom', 'prenom', 'fonction',
                    'delegation_technique_id', 'caisse_id', 'direction_id'),
            ])
            ->where('evaluable_type', Agent::class)
            ->orderByDesc('date_debut');

            if ($statut) $q->where('statut', $statut);
            if ($annee)  $q->whereYear('date_debut', $annee);
            if ($search) {
                $q->whereHas('evaluable', fn ($s) =>
                    $s->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                );
            }

            $evaluations = $q->paginate(20)->withQueryString();
        }

        // ── Tab Objectifs ────────────────────────────────────────────────────
        $fiches     = null;
        $ficheStats = null;
        if ($tab === 'objectifs') {
            $baseF = fn () => FicheObjectif::where('assignable_type', Agent::class);

            $ficheStats = [
                'total'      => $baseF()->count(),
                'acceptee'   => $baseF()->where('statut', 'acceptee')->count(),
                'en_attente' => $baseF()->whereIn('statut', ['en_attente', 'brouillon'])->count(),
                'refusee'    => $baseF()->where('statut', 'refusee')->count(),
            ];

            $q = FicheObjectif::with([
                'assignable' => fn ($m) => $m->select('id', 'nom', 'prenom', 'fonction',
                    'delegation_technique_id', 'caisse_id'),
            ])
            ->withCount('objectifs')
            ->where('assignable_type', Agent::class)
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
            'tab',
            'filters',
            'delegations',
            'caisses',
            'directions',
            'agents',
            'evaluations',
            'evalStats',
            'fiches',
            'ficheStats',
        ));
    }
}
