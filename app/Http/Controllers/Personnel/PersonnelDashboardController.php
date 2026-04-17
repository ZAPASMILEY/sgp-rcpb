<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PersonnelDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user  = $request->user();
        $tab   = $request->get('tab', 'evaluations');
        $statut= trim((string) $request->get('statut', ''));

        // Trouver l'agent lié au compte utilisateur
        $agent = Agent::with(['service.direction.entite', 'agence'])
            ->where('user_id', $user->id)
            ->first();

        // ── Evaluations ──────────────────────────────────────────────────────
        $evalsQ = null;
        $evaluationsStats = ['total' => 0, 'brouillon' => 0, 'soumis' => 0, 'valide' => 0];
        $evaluations = null;

        if ($agent) {
            $baseE = fn () => Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluable_id', $agent->id);

            $evalsQ = Evaluation::query()
                ->with(['evaluateur', 'identification'])
                ->where('evaluable_type', Agent::class)
                ->where('evaluable_id', $agent->id)
                ->orderByDesc('date_debut');

            if ($statut && $tab === 'evaluations') {
                $evalsQ->where('statut', $statut);
            }

            $evaluationsStats = [
                'total'    => $baseE()->count(),
                'brouillon'=> $baseE()->where('statut', 'brouillon')->count(),
                'soumis'   => $baseE()->where('statut', 'soumis')->count(),
                'valide'   => $baseE()->where('statut', 'valide')->count(),
            ];

            $evaluations = $evalsQ->paginate(10)->withQueryString();
        }

        // ── Fiches objectifs ─────────────────────────────────────────────────
        $fichesStats = ['total' => 0, 'acceptees' => 0, 'en_attente' => 0, 'refusees' => 0];
        $fiches      = null;
        $search      = trim((string) $request->get('search', ''));

        if ($agent) {
            $baseF = fn () => FicheObjectif::where('assignable_type', Agent::class)
                ->where('assignable_id', $agent->id);

            $fichesQ = FicheObjectif::query()
                ->withCount('objectifs')
                ->where('assignable_type', Agent::class)
                ->where('assignable_id', $agent->id)
                ->orderByDesc('date');

            if ($search && $tab === 'objectifs') {
                $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('annee', 'like', "%{$search}%"));
            }

            if ($statut && $tab === 'objectifs') {
                if ($statut === 'en_attente') {
                    $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
                } else {
                    $fichesQ->where('statut', $statut);
                }
            }

            $fichesStats = [
                'total'     => $baseF()->count(),
                'acceptees' => $baseF()->where('statut', 'acceptee')->count(),
                'en_attente'=> $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
                'refusees'  => $baseF()->where('statut', 'refusee')->count(),
            ];

            $fiches = $fichesQ->paginate(10)->withQueryString();
        }

        $filters = compact('tab', 'statut', 'search');

        return view('personnel.dashboard', compact(
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
