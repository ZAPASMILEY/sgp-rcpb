<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PcaStatistiqueController extends Controller
{
    public function __invoke(Request $request): View
    {
        // 1. Entité faîtière et DG
        $entite = Entite::with('dg')
            ->where('pca_agent_id', $request->user()->agent_id)
            ->firstOrFail();

        $dgUser   = $entite->dg_agent_id
            ? User::where('agent_id', $entite->dg_agent_id)->first()
            : null;
        $dgUserId = $dgUser?->id ?? 0;

        // 2. Gestion de l'année
        $availableYears = Annee::query()->orderByDesc('annee')->pluck('annee')->all();
        $selectedYear   = $availableYears[0] ?? (int) now()->year;
        $requestedYear  = (int) $request->query('annee', $selectedYear);
        if (in_array($requestedYear, $availableYears, true)) {
            $selectedYear = $requestedYear;
        }

        // 3. Fiches d'objectifs — DG uniquement
        $fichesBase = FicheObjectif::query()
            ->where('assignable_type', \App\Models\User::class)
            ->where('assignable_id', $dgUserId)
            ->whereYear('date', $selectedYear);

        $fichesDGCount   = (clone $fichesBase)->count();
        $fichesAcceptees = (clone $fichesBase)->where('statut', 'acceptee')->count();
        $fichesEnAttente = (clone $fichesBase)->where('statut', 'en_attente')->count();
        $fichesRefusees  = (clone $fichesBase)->where('statut', 'refusee')->count();
        $avancementMoyen = (int) round((clone $fichesBase)->avg('avancement_percentage') ?? 0);

        // 4. Évaluations — DG uniquement
        $evalsBase = Evaluation::query()
            ->where('evaluable_type', \App\Models\User::class)
            ->where('evaluable_id', $dgUserId)
            ->whereYear('date_debut', $selectedYear);

        $evaluationsDGCount   = (clone $evalsBase)->count();
        $evaluationsSoumises  = (clone $evalsBase)->where('statut', 'soumis')->count();
        $evaluationsAcceptees = (clone $evalsBase)->where('statut', 'valide')->count();
        $evaluationsRejetees  = (clone $evalsBase)->whereIn('statut', ['rejete', 'rejetee'])->count();
        $meilleureNoteDG      = (int) ((clone $evalsBase)->where('statut', 'valide')->max('note_finale') ?? 0);

        $evaluationsByStatut = [
            'Brouillon' => (clone $evalsBase)->where('statut', 'brouillon')->count(),
            'Soumis'    => $evaluationsSoumises,
            'Valide'    => $evaluationsAcceptees,
        ];

        $distribution = [
            'Fiches DG'   => $fichesDGCount,
            'Évals DG'    => $evaluationsDGCount,
            'Acceptées'   => $fichesAcceptees,
            'Validées'    => $evaluationsAcceptees,
        ];

        return view('pca.statistiques.index', compact(
            'entite',
            'dgUser',
            'fichesDGCount',
            'fichesAcceptees',
            'fichesEnAttente',
            'fichesRefusees',
            'avancementMoyen',
            'evaluationsDGCount',
            'evaluationsSoumises',
            'evaluationsAcceptees',
            'evaluationsRejetees',
            'meilleureNoteDG',
            'evaluationsByStatut',
            'distribution',
            'availableYears',
            'selectedYear',
        ));
    }
}
