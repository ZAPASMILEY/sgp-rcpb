<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChefsServiceController extends Controller
{
    private function checkDga(): void
    {
        $user = Auth::user();
        if (! $user || strtolower($user->role) !== 'dga') {
            abort(403, 'Action non autorisée pour ce profil.');
        }
    }

    /** Direction dont le DGA est le directeur (directeur_agent_id = dga.agent_id). */
    private function getDgaDirection(): ?Direction
    {
        $dgaAgentId = Auth::user()->agent_id;
        return Direction::where('directeur_agent_id', $dgaAgentId)->first();
    }

    public function __invoke(Request $request): View
    {
        $this->checkDga();

        $direction = $this->getDgaDirection();

        // IDs des chefs définis sur les services de la direction DGA
        $chefAgentIds = $direction
            ? Service::where('direction_id', $direction->id)
                ->whereNotNull('chef_agent_id')
                ->pluck('chef_agent_id')
            : collect();

        $search = trim((string) $request->input('search', ''));

        $query = Agent::whereIn('id', $chefAgentIds)
            ->with(['service', 'direction', 'user']);

        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('nom', 'like', "%{$search}%")
                ->orWhere('prenom', 'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%")
            );
        }

        $chefsService = $query->orderBy('nom')->orderBy('prenom')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'       => Agent::whereIn('id', $chefAgentIds)->count(),
            'actifs'      => Agent::whereIn('id', $chefAgentIds)->whereHas('user')->count(),
            'sans_compte' => Agent::whereIn('id', $chefAgentIds)->whereDoesntHave('user')->count(),
        ];

        $filters = compact('search');

        return view('dga.chefs-service.index', compact(
            'chefsService',
            'direction',
            'stats',
            'filters'
        ));
    }
}
