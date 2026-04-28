<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $entite = Entite::with(['objectifs', 'dg', 'dga', 'assistante'])
            ->where('pca_agent_id', $request->user()->agent_id)
            ->firstOrFail();
        $entiteId = $entite->id;

        // Find the DG user : entites.dg_agent_id → agents.id ← users.agent_id
        $dgUser = $entite->dg_agent_id
            ? User::query()->where('role', 'DG')->where('agent_id', $entite->dg_agent_id)->first()
            : null;

        $dgUserId = $dgUser?->id;

        // Fiches d'objectifs assigned to the DG user
        $fichesObjectifsDGQuery = FicheObjectif::query()
            ->where('assignable_type', User::class)
            ->when($dgUserId, fn ($q) => $q->where('assignable_id', $dgUserId), fn ($q) => $q->whereRaw('1 = 0'));

        $nbFichesObjectifsDG     = (clone $fichesObjectifsDGQuery)->count();
        $nbFichesObjectifsAttente = (clone $fichesObjectifsDGQuery)->where('statut', 'en_attente')->count();
        $nbFichesObjectifsAcceptees = (clone $fichesObjectifsDGQuery)->where('statut', 'acceptee')->count();

        $fichesStatsDG = [
            'acceptées' => $nbFichesObjectifsAcceptees,
            'en_attente' => $nbFichesObjectifsAttente,
            'refusées' => (clone $fichesObjectifsDGQuery)->where('statut', 'refusee')->count(),
        ];

        $dernieresFichesDG = (clone $fichesObjectifsDGQuery)
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // Evaluations for the DG user
        $evaluationsDGQuery = Evaluation::query()
            ->where('evaluable_type', User::class)
            ->when($dgUserId, fn ($q) => $q->where('evaluable_id', $dgUserId), fn ($q) => $q->whereRaw('1 = 0'));

        $nbEvaluationsDG = (clone $evaluationsDGQuery)->count();

        $dernieresEvaluationsDG = (clone $evaluationsDGQuery)
            ->latest('date_debut')
            ->take(5)
            ->get();

        // Chart data — evaluations grouped by month (last 6 months)
        $evaluationsParMois = (clone $evaluationsDGQuery)
            ->where('date_debut', '>=', now()->subMonths(6)->startOfMonth())
            ->get()
            ->groupBy(fn ($e) => Carbon::parse($e->date_debut)->format('M Y'));

        $evaluationsDGLabels = $evaluationsParMois->keys()->values()->toArray();
        $evaluationsDGData   = $evaluationsParMois->map->count()->values()->toArray();

        if (empty($evaluationsDGLabels)) {
            $evaluationsDGLabels = [now()->format('M Y')];
            $evaluationsDGData   = [0];
        }

        // Alertes — fiches en attente créées dans les 7 derniers jours
        $alertesDGCollection = (clone $fichesObjectifsDGQuery)
            ->where('statut', 'en_attente')
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('date')
            ->get();

        $alertesDG = $alertesDGCollection
            ->map(fn ($f) => "Fiche \"{$f->titre}\" en attente de validation depuis le ".Carbon::parse($f->date)->format('d/m/Y').'.')
            ->toArray();

        // Alertes chart — last 7 days
        $alertesParJour = collect(range(6, 0))->mapWithKeys(function ($daysAgo) use ($fichesObjectifsDGQuery) {
            $day = now()->subDays($daysAgo)->toDateString();
            $count = (clone $fichesObjectifsDGQuery)
                ->where('statut', 'en_attente')
                ->whereDate('date', $day)
                ->count();
            return [Carbon::parse($day)->format('d/m') => $count];
        });

        $alertesDGLabels = $alertesParJour->keys()->values()->toArray();
        $alertesDGData   = $alertesParJour->values()->toArray();

        // Structural counts
        $directions = Direction::query()
            ->where('entite_id', $entiteId)
            ->get();

        $directionsRattacheesCount = $directions->count();
        $directionIds = $directions->pluck('id')->all();

        $servicesRattachesCount = Service::query()
            ->whereIn('direction_id', $directionIds)
            ->count();

        $serviceIds = Service::query()
            ->whereIn('direction_id', $directionIds)
            ->pluck('id')
            ->all();

        $agentsRattachesCount = Agent::query()
            ->whereIn('service_id', $serviceIds)
            ->count();

        $personnelRattache = collect([
            [
                'fonction' => 'Directeur(trice) Général(e)',
                'nom'      => $entite->dg       ? trim($entite->dg->prenom.' '.$entite->dg->nom)             : '',
                'icone'    => 'fas fa-user-tie',
            ],
            [
                'fonction' => 'Assistante DG',
                'nom'      => $entite->assistante ? trim($entite->assistante->prenom.' '.$entite->assistante->nom) : '',
                'icone'    => 'fas fa-user',
            ],
            [
                'fonction' => 'DGA',
                'nom'      => $entite->dga      ? trim($entite->dga->prenom.' '.$entite->dga->nom)           : '',
                'icone'    => 'fas fa-user-shield',
            ],
        ])->filter(fn (array $personne): bool => $personne['nom'] !== '')->values();

        $personnelRattacheCount = $personnelRattache->count();

        $objectifsPendingCount = Objectif::query()
            ->where(function ($q) use ($entiteId, $directionIds): void {
                $q->where(function ($sub) use ($entiteId): void {
                    $sub->where('assignable_type', \App\Models\Entite::class)
                        ->where('assignable_id', $entiteId);
                })->orWhere(function ($sub) use ($directionIds): void {
                    $sub->where('assignable_type', Direction::class)
                        ->whereIn('assignable_id', $directionIds);
                });
            })
            ->where('avancement_percentage', '<', 100)
            ->count();

        return view('pca.dashboard', compact(
            'entite',
            'directions',
            'directionsRattacheesCount',
            'servicesRattachesCount',
            'agentsRattachesCount',
            'personnelRattache',
            'personnelRattacheCount',
            'objectifsPendingCount',
            'fichesStatsDG',
            'nbFichesObjectifsDG',
            'nbEvaluationsDG',
            'nbFichesObjectifsAttente',
            'nbFichesObjectifsAcceptees',
            'dernieresFichesDG',
            'dernieresEvaluationsDG',
            'alertesDG',
            'evaluationsDGLabels',
            'evaluationsDGData',
            'alertesDGLabels',
            'alertesDGData',
        ));
    }
}
