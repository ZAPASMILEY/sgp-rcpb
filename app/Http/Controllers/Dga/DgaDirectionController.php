<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaDirectionController extends Controller
{
    private function getDirection(): Direction
    {
        $dgaAgentId = Auth::user()->agent_id;

        $direction = Direction::where('directeur_agent_id', $dgaAgentId)
            ->with(['secretaire', 'services.chef', 'services.agents'])
            ->first();

        if (! $direction) {
            $direction = Direction::with(['secretaire', 'services.chef', 'services.agents'])->find(5);
        }

        abort_if(! $direction, 404, 'Direction DGA introuvable.');

        return $direction;
    }

    public function index(Request $request): View
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }

        $tab    = $request->get('tab', 'services');
        $search = trim((string) $request->get('search', ''));
        $statut = trim((string) $request->get('statut', ''));

        $direction = $this->getDirection();

        // ── Services avec stats ───────────────────────────────────────────────
        $chefUserIds = collect(); // pour les requêtes des autres onglets

        $services = $direction->services->map(function (Service $service) use (&$chefUserIds): array {
            $chef     = $service->chef;
            $chefUser = $chef ? User::where('agent_id', $chef->id)->first() : null;

            if ($chefUser) {
                $chefUserIds->push($chefUser->id);
            }

            $nbAgents = Agent::where('service_id', $service->id)->count();

            $nbEvals = $chefUser
                ? Evaluation::where('evaluable_type', User::class)
                    ->where('evaluable_id', $chefUser->id)
                    ->count()
                : 0;

            $noteAvg = $chefUser
                ? Evaluation::where('evaluable_type', User::class)
                    ->where('evaluable_id', $chefUser->id)
                    ->where('statut', 'valide')
                    ->whereNotNull('note_finale')
                    ->avg('note_finale')
                : null;

            $nbObjectifs = $chefUser
                ? FicheObjectif::where('assignable_type', User::class)
                    ->where('assignable_id', $chefUser->id)
                    ->count()
                : 0;

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

        // ── KPIs ─────────────────────────────────────────────────────────────
        $totalAgents = Agent::where('direction_id', $direction->id)->count();
        $notes       = $services->pluck('noteAvg')->filter()->values();
        $noteMoyenne = $notes->count() ? round($notes->avg(), 2) : null;
        $totalEvals  = $chefUserIds->isNotEmpty()
            ? Evaluation::where('evaluable_type', User::class)->whereIn('evaluable_id', $chefUserIds)->count()
            : 0;
        $totalObjs   = $chefUserIds->isNotEmpty()
            ? FicheObjectif::where('assignable_type', User::class)->whereIn('assignable_id', $chefUserIds)->count()
            : 0;

        // ── Onglet Évaluations ────────────────────────────────────────────────
        $evaluations = null;
        if ($tab === 'evaluations' && $chefUserIds->isNotEmpty()) {
            $q = Evaluation::with(['evaluable', 'evaluateur', 'identification'])
                ->where('evaluable_type', User::class)
                ->whereIn('evaluable_id', $chefUserIds)
                ->orderByDesc('updated_at');

            if ($statut !== '') {
                $q->where('statut', $statut);
            }
            if ($search !== '') {
                $q->whereHas('identification', fn ($iq) =>
                    $iq->where('nom_prenom', 'like', "%{$search}%")
                       ->orWhere('emploi', 'like', "%{$search}%")
                );
            }

            $evaluations = $q->paginate(15)->withQueryString();
        }

        // ── Onglet Objectifs ──────────────────────────────────────────────────
        $objectifs = null;
        if ($tab === 'objectifs' && $chefUserIds->isNotEmpty()) {
            $q = FicheObjectif::with('assignable')
                ->withCount('objectifs')
                ->where('assignable_type', User::class)
                ->whereIn('assignable_id', $chefUserIds)
                ->orderByDesc('date');

            if ($statut !== '') {
                $q->where('statut', $statut);
            }
            if ($search !== '') {
                $q->where('titre', 'like', "%{$search}%");
            }

            $objectifs = $q->paginate(15)->withQueryString();
        }

        // ── Collaborateurs directs ────────────────────────────────────────────
        $agentsDirects = Agent::where('direction_id', $direction->id)
            ->whereNull('service_id')
            ->whereNotIn('id', array_filter([
                $direction->directeur_agent_id,
                $direction->secretaire_agent_id,
            ]))
            ->orderBy('nom')
            ->get();

        $filters = compact('search', 'statut');

        return view('dga.direction.index', compact(
            'tab', 'direction', 'services', 'agentsDirects',
            'totalAgents', 'totalEvals', 'totalObjs', 'noteMoyenne',
            'evaluations', 'objectifs', 'filters'
        ));
    }
}
