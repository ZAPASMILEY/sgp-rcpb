<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * PersonnelMonEspaceController — Dossier personnel de l'agent
 *
 * Page détaillée avec :
 *   - Onglet "Mes évaluations" : liste paginée avec filtres
 *   - Onglet "Mes objectifs"  : liste paginée avec filtres
 *
 * Distinct de PersonnelDashboardController qui est la vue d'ensemble (overview).
 */
class PersonnelMonEspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = $request->user();
        $tab   = in_array($request->input('tab'), ['evaluations', 'objectifs'])
            ? $request->input('tab')
            : 'evaluations';
        $statut = trim((string) $request->input('statut', ''));
        $search = trim((string) $request->input('search', ''));

        $agent = $user->agent_id
            ? Agent::with(['service.direction.entite', 'agence'])->find($user->agent_id)
            : null;

        // ── Closure de base : évaluations reçues (via User OU Agent) ─────────
        $baseE = function () use ($user, $agent) {
            return Evaluation::where(function ($q) use ($user, $agent) {
                $q->where('evaluable_type', \App\Models\User::class)
                  ->where('evaluable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('evaluable_type', Agent::class)
                           ->where('evaluable_id', $agent->id);
                    });
                }
            });
        };

        $evalsQ = $baseE()->with(['evaluateur', 'identification'])->orderByDesc('date_debut');
        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'     => $baseE()->count(),
            'brouillon' => $baseE()->where('statut', 'brouillon')->count(),
            'soumis'    => $baseE()->where('statut', 'soumis')->count(),
            'valide'    => $baseE()->where('statut', 'valide')->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        // ── Closure de base : fiches d'objectifs reçues ───────────────────────
        $baseF = function () use ($user, $agent) {
            return FicheObjectif::where(function ($q) use ($user, $agent) {
                $q->where('assignable_type', \App\Models\User::class)
                  ->where('assignable_id', $user->id);
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)
                           ->where('assignable_id', $agent->id);
                    });
                }
            });
        };

        $fichesQ = $baseF()->withCount('objectifs')->orderByDesc('date');
        if ($search && $tab === 'objectifs') {
            $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")
                ->orWhereHas('annee', fn ($a) => $a->where('annee', 'like', "%{$search}%")));
        }
        if ($statut && $tab === 'objectifs') {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        return view('personnel.mon-espace', compact(
            'user',
            'agent',
            'tab',
            'evaluations',
            'evaluationsStats',
            'fiches',
            'fichesStats',
            'filters',
        ));
    }
}
