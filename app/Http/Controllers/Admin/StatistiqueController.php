<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Caisse;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Guichet;
use App\Models\Objectif;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StatistiqueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $availableYears = Annee::query()
            ->orderByDesc('annee')
            ->pluck('annee')
            ->all();

        $selectedYear = $availableYears[0] ?? (int) now()->year;
        $requestedYear = (int) $request->query('annee', $selectedYear);

        if (in_array($requestedYear, $availableYears, true)) {
            $selectedYear = $requestedYear;
        }

        $selectedAnneeId = (int) (Annee::query()->where('annee', $selectedYear)->value('id') ?? 0);

        $objectifQuery = fn () => Objectif::query()
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date', $selectedYear));

        $evaluationQuery = fn () => Evaluation::query()
            ->when($selectedAnneeId > 0, fn ($query) => $query->where('annee_id', $selectedAnneeId), fn ($query) => $query->whereYear('date_debut', $selectedYear));

        $entitesCount = Entite::query()->whereYear('created_at', $selectedYear)->count();
        $directionsCount = Direction::query()->whereYear('created_at', $selectedYear)->count();
        $servicesCount = Service::query()->whereYear('created_at', $selectedYear)->count();
        $caissesCount = Caisse::query()->whereYear('created_at', $selectedYear)->count();
        $agencesCount = Agence::query()->whereYear('created_at', $selectedYear)->count();
        $guichetsCount = Guichet::query()->whereYear('created_at', $selectedYear)->count();
        $agentsCount = Agent::query()
            ->whereYear('date_debut_fonction', $selectedYear)
            ->count();
        $objectifsCount = $objectifQuery()->count();
        $evaluationsCount = $evaluationQuery()->count();

        $agentsBySexe = [
            'Hommes' => Agent::query()->where('sexe', 'homme')->count(),
            'Femmes' => Agent::query()->where('sexe', 'femme')->count(),
        ];

        $evaluationsByStatut = [
            'Brouillon' => $evaluationQuery()->where('statut', 'brouillon')->count(),
            'Soumis' => $evaluationQuery()->where('statut', 'soumis')->count(),
            'Valide' => $evaluationQuery()->where('statut', 'valide')->count(),
        ];

        $objectifsTermines = $objectifQuery()->where('avancement_percentage', '>=', 100)->count();
        $objectifsEnCours = max(0, $objectifsCount - $objectifsTermines);

        $avancementMoyen = (int) round((float) ($objectifQuery()->avg('avancement_percentage') ?? 0));

        return view('admin.statistiques.index', compact(
            'entitesCount',
            'directionsCount',
            'servicesCount',
            'caissesCount',
            'agencesCount',
            'guichetsCount',
            'agentsCount',
            'agentsBySexe',
            'objectifsCount',
            'evaluationsCount',
            'evaluationsByStatut',
            'objectifsTermines',
            'objectifsEnCours',
            'avancementMoyen',
            'availableYears',
            'selectedYear',
        ));
    }
}
