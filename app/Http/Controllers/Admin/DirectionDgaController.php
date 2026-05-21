<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Service;
use App\Models\User;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DirectionDgaController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

    /** Résout la direction du DGA (directeur_agent_id = agent ayant fonction DGA). */
    private function getDirection(): ?Direction
    {
        return Direction::where('nom', 'Direction Générale Adjointe')
            ->orWhereHas('directeur', fn ($q) => $q->where('role', 'DGA'))
            ->with([
                'directeur',
                'secretaire',
                'services' => fn ($q) => $q->with(['chef', 'agents']),
            ])
            ->first();
    }

    /** Formulaire de configuration du DGA (directeur de la Direction Générale Adjointe). */
    public function configurerDga(): View|RedirectResponse
    {
        $direction = Direction::where('nom', 'Direction Générale Adjointe')->first();
        if (! $direction) {
            return redirect()->route('admin.direction-dga.index')
                ->with('error', 'La Direction Générale Adjointe n\'existe pas encore. Créez-la d\'abord via le menu Directions.');
        }

        $candidats = Agent::where('role', 'DGA')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-dga.configurer-dga', compact('direction', 'candidats'));
    }

    /** Enregistre le DGA comme directeur de la Direction Générale Adjointe. */
    public function stockerDga(Request $request): RedirectResponse
    {
        $direction = Direction::where('nom', 'Direction Générale Adjointe')->firstOrFail();

        $validated = $request->validate([
            'dga_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'dga_agent_id.required' => 'Veuillez sélectionner un agent.',
        ]);

        // Désactiver l'ancien DGA si changement
        if ($direction->directeur_agent_id && $direction->directeur_agent_id !== (int) $validated['dga_agent_id']) {
            $ancien = Agent::find($direction->directeur_agent_id);
            if ($ancien) {
                $this->accounts->deactivateAccount($ancien);
                $ancien->update(['direction_id' => null]);
            }
        }

        $direction->update(['directeur_agent_id' => $validated['dga_agent_id']]);

        $agent = Agent::findOrFail($validated['dga_agent_id']);
        $agent->update(['direction_id' => $direction->id, 'poste' => 'Directeur Général Adjoint']);
        $this->accounts->ensureAccount($agent->fresh());

        return redirect()
            ->route('admin.direction-dga.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' configuré(e) comme DGA.');
    }

    public function index(): View
    {
        $direction = $this->getDirection();

        $services = $direction
            ? $direction->services->map(function (Service $s): array {
                $chef     = $s->chef;
                $chefUser = $chef ? User::where('agent_id', $chef->id)->first() : null;
                return [
                    'service'  => $s,
                    'chef'     => $chef,
                    'chefUser' => $chefUser,
                    'nbAgents' => $s->agents->count(),
                ];
            })
            : collect();

        // Agents directs (sans service) dans la direction DGA
        $agentsDirects = $direction
            ? Agent::where('direction_id', $direction->id)
                ->whereNull('service_id')
                ->whereNotIn('id', array_filter([
                    $direction->directeur_agent_id,
                    $direction->secretaire_agent_id,
                ]))
                ->with('user')
                ->orderBy('nom')
                ->get()
            : collect();

        return view('admin.direction-dga.index', compact('direction', 'services', 'agentsDirects'));
    }

    /** Formulaire d'affectation d'un chef de service. */
    public function editChefService(Service $service): View|RedirectResponse
    {
        $direction = $this->getDirection();
        if (! $direction || $service->direction_id !== $direction->id) {
            abort(403);
        }

        $candidats = Agent::where('role', 'Chef de Service')
            ->where(function ($q) use ($service): void {
                $q->whereNull('service_id')->orWhere('service_id', $service->id);
            })
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-dga.edit-chef-service', compact('service', 'direction', 'candidats'));
    }

    public function updateChefService(Request $request, Service $service): RedirectResponse
    {
        $direction = $this->getDirection();
        if (! $direction || $service->direction_id !== $direction->id) {
            abort(403);
        }

        $validated = $request->validate([
            'chef_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ]);

        // Désactiver l'ancien chef si changement
        if ($service->chef_agent_id && $service->chef_agent_id !== (int) $validated['chef_agent_id']) {
            $ancien = Agent::find($service->chef_agent_id);
            if ($ancien) {
                $this->accounts->deactivateAccount($ancien);
                $ancien->update(['service_id' => null]);
            }
        }

        $service->update(['chef_agent_id' => $validated['chef_agent_id']]);

        $newChef = Agent::findOrFail($validated['chef_agent_id']);
        $newChef->update(['direction_id' => $direction->id, 'service_id' => $service->id, 'poste' => 'Chef du Service ' . $service->nom]);
        $this->accounts->ensureAccount($newChef);

        return redirect()
            ->route('admin.direction-dga.index')
            ->with('status', $newChef->prenom.' '.$newChef->nom.' affecté(e) comme chef du service « '.$service->nom.' ».');
    }

    /** Affectation / modification du secrétaire de la direction DGA. */
    public function editSecretaire(): View|RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction, 404);

        $candidats = Agent::where('role', 'Secrétaire de Direction')
            ->where(function ($q) use ($direction): void {
                $q->whereNull('direction_id')->orWhere('direction_id', $direction->id);
            })
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-dga.edit-secretaire', compact('direction', 'candidats'));
    }

    public function updateSecretaire(Request $request): RedirectResponse
    {
        $direction = $this->getDirection();
        abort_if(! $direction, 404);

        $validated = $request->validate([
            'secretaire_agent_id' => ['required', 'integer', 'exists:agents,id'],
        ]);

        // Désactiver l'ancien si changement
        if ($direction->secretaire_agent_id && $direction->secretaire_agent_id !== (int) $validated['secretaire_agent_id']) {
            $ancien = Agent::find($direction->secretaire_agent_id);
            if ($ancien) {
                $this->accounts->deactivateAccount($ancien);
                $ancien->update(['direction_id' => null]);
            }
        }

        $direction->update(['secretaire_agent_id' => $validated['secretaire_agent_id']]);

        $agent = Agent::findOrFail($validated['secretaire_agent_id']);
        $agent->update(['direction_id' => $direction->id, 'poste' => 'Secrétaire de la Direction ' . $direction->nom]);
        $this->accounts->ensureAccount($agent);

        return redirect()
            ->route('admin.direction-dga.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) comme secrétaire de la Direction DGA.');
    }
}
