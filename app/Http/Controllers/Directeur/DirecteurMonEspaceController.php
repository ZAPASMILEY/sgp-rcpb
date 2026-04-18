<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirecteurMonEspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user      = Auth::user();
        $direction = Direction::where('user_id', $user->id)
            ->with(['services' => fn ($q) => $q->with('agents')])
            ->first();

        if (! $direction) {
            abort(403, 'Aucune direction associée à votre compte. Contactez l\'administrateur.');
        }

        $tab = $request->query('tab', 'dashboard');

        // ── Évaluations reçues (direction = évaluée, rôle manager) ──────────
        $evaluationsRecues = Evaluation::where('evaluable_type', Direction::class)
            ->where('evaluable_id', $direction->id)
            ->where('evaluable_role', 'manager')
            ->with(['evaluateur', 'identification'])
            ->orderByDesc('date_debut')
            ->get();

        $evaluationsStats = [
            'total'     => $evaluationsRecues->count(),
            'soumis'    => $evaluationsRecues->where('statut', 'soumis')->count(),
            'valide'    => $evaluationsRecues->where('statut', 'valide')->count(),
            'refuse'    => $evaluationsRecues->where('statut', 'refuse')->count(),
            'brouillon' => $evaluationsRecues->where('statut', 'brouillon')->count(),
        ];

        // ── Objectifs reçus (assignés à la direction) ────────────────────────
        $fichesObjectifs = FicheObjectif::where('assignable_type', Direction::class)
            ->where('assignable_id', $direction->id)
            ->with('objectifs')
            ->orderByDesc('date')
            ->get();

        $fichesStats = [
            'total'      => $fichesObjectifs->count(),
            'acceptees'  => $fichesObjectifs->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesObjectifs->where('statut', 'en_attente')->count(),
            'refusees'   => $fichesObjectifs->where('statut', 'refusee')->count(),
        ];

        // ── Vue d'ensemble des services / chefs ──────────────────────────────
        $servicesOverview = $direction->services->map(function (Service $service) {
            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->whereIn('statut', ['soumis', 'valide'])
                ->orderByDesc('date_debut')
                ->first();

            return [
                'service'      => $service,
                'eval'         => $latestEval,
                'agents_count' => $service->agents->count(),
            ];
        });

        // Note moyenne de la direction (basée sur les chefs évalués)
        $notesChefs  = $servicesOverview->pluck('eval')->filter()->pluck('note_finale')->map(fn ($n) => (float) $n);
        $noteMoyenne = $notesChefs->isNotEmpty() ? round($notesChefs->avg(), 2) : null;

        // Évaluations créées par le directeur pour ses chefs de service
        $evaluationsCreees = Evaluation::where('evaluateur_id', $user->id)
            ->where('evaluable_type', Service::class)
            ->where('evaluable_role', 'manager')
            ->with(['evaluable', 'identification'])
            ->orderByDesc('created_at')
            ->get();

        return view('directeur.mon-espace', compact(
            'user',
            'direction',
            'tab',
            'servicesOverview',
            'evaluationsRecues',
            'evaluationsStats',
            'fichesObjectifs',
            'fichesStats',
            'evaluationsCreees',
            'noteMoyenne',
        ));
    }
}
