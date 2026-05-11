<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Page "Ma Direction" du DGA.
 * Affiche la Direction du DGA (id=5) avec ses services, agents et statistiques.
 */
class DgaDirectionController extends Controller
{
    /** Résout la Direction du DGA connecté. */
    private function getDirection(): Direction
    {
        $dgaAgentId = Auth::user()->agent_id;

        // La direction dont le DGA est le directeur
        $direction = Direction::where('directeur_agent_id', $dgaAgentId)
            ->with(['secretaire', 'services.chef', 'services.agents'])
            ->first();

        if (! $direction) {
            // Fallback : Direction du Directeur Général Adjoint (id=5)
            $direction = Direction::with(['secretaire', 'services.chef', 'services.agents'])
                ->find(5);
        }

        abort_if(! $direction, 404, 'Direction DGA introuvable.');

        return $direction;
    }

    public function index(): View
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }

        $direction = $this->getDirection();

        // Agents rattachés directement à la direction (pas via un service)
        $agentsDirects = Agent::where('direction_id', $direction->id)
            ->whereNull('service_id')
            ->whereNotIn('id', array_filter([
                $direction->directeur_agent_id,
                $direction->secretaire_agent_id,
            ]))
            ->orderBy('nom')
            ->get();

        // Services avec chefs + agents
        $services = $direction->services->map(function (Service $service): array {
            $chef       = $service->chef;
            $chefUser   = $chef ? User::where('agent_id', $chef->id)->first() : null;
            $nbAgents   = Agent::where('service_id', $service->id)->count();

            // Évaluations du chef par le DGA connecté
            $nbEvals = $chefUser ? Evaluation::where('evaluable_type', User::class)
                ->where('evaluable_id', $chefUser->id)
                ->where('evaluateur_id', Auth::id())
                ->count() : 0;

            $noteAvg = $chefUser ? Evaluation::where('evaluable_type', User::class)
                ->where('evaluable_id', $chefUser->id)
                ->where('evaluateur_id', Auth::id())
                ->whereNotNull('note_finale')
                ->avg('note_finale') : null;

            // Objectifs assignés par le DGA au chef
            $nbObjectifs = $chefUser ? FicheObjectif::where('assignable_type', User::class)
                ->where('assignable_id', $chefUser->id)
                ->count() : 0;

            return [
                'service'     => $service,
                'chef'        => $chef,
                'chefUser'    => $chefUser,
                'nbAgents'    => $nbAgents,
                'nbEvals'     => $nbEvals,
                'noteAvg'     => $noteAvg !== null ? round((float) $noteAvg, 2) : null,
                'nbObjectifs' => $nbObjectifs,
            ];
        });

        // KPIs globaux de la direction
        $totalAgents = Agent::where('direction_id', $direction->id)->count();
        $totalEvals  = 0;
        $notes       = [];
        foreach ($services as $s) {
            $totalEvals += $s['nbEvals'];
            if ($s['noteAvg'] !== null) {
                $notes[] = $s['noteAvg'];
            }
        }
        $noteMoyenne = count($notes) ? round(array_sum($notes) / count($notes), 2) : null;

        return view('dga.direction.index', compact(
            'direction',
            'services',
            'agentsDirects',
            'totalAgents',
            'totalEvals',
            'noteMoyenne',
        ));
    }
}
