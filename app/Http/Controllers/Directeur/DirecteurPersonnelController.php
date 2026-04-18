<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurPersonnelController — Vue du personnel de l'entité du directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Permet au directeur de consulter l'ensemble des agents rattachés aux services
 * de son entité (Direction, Caisse ou DelegationTechnique), avec :
 *
 *  • Recherche textuelle sur nom, prénom et fonction
 *  • Filtre par service
 *  • Tri : nom (défaut) | service | fonction | note croissante | note décroissante
 *  • Statistiques rapides : total agents, nombre d'évalués, note moyenne
 *
 * Pour chaque agent, on affiche sa dernière évaluation reçue (statut, note,
 * mention) en chargeant la relation eagerly pour éviter les N+1.
 *
 * NOTE : Cette vue est en lecture seule. Le directeur ne peut pas noter les
 * agents depuis ici — il évalue les chefs de service via le module Subordonnés.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurPersonnelController extends Controller
{
    public function index(Request $request): View
    {
        // Résolution du contexte : identifie l'entité (Direction/Caisse/Délégation) du directeur
        $ctx        = DirecteurEntity::resolveOrFail(Auth::user());
        $direction  = $ctx->entity; // passé à la vue pour compatibilité Blade
        $serviceIds = $ctx->getServiceIds(); // IDs des services rattachés à l'entité

        // Paramètres de tri et de filtrage depuis l'URL
        $sortBy        = $request->query('sort', 'nom');
        $filterService = $request->query('service');  // ID du service à filtrer (optionnel)
        $search        = $request->query('search');   // Texte de recherche (optionnel)

        // ── Construction de la requête de base ────────────────────────────
        // Charge tous les agents appartenant aux services de l'entité.
        // La relation `evaluations` est filtrée pour ne garder que les évaluations
        // de type Agent (evaluable_type = Agent::class) et triée par date décroissante.
        $query = Agent::whereIn('service_id', $serviceIds)
            ->with(['service', 'evaluations' => function ($q) {
                $q->where('evaluable_type', Agent::class)
                  ->orderByDesc('date_debut');
            }]);

        // Filtre par service sélectionné dans le menu déroulant
        if ($filterService) {
            $query->where('service_id', (int) $filterService);
        }

        // Recherche insensible à la casse sur nom, prénom et fonction
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('fonction', 'like', "%{$search}%");
            });
        }

        // ── Enrichissement des données en mémoire ─────────────────────────
        // On récupère la dernière évaluation de chaque agent pour calculer sa note
        // et sa mention, sans requêtes supplémentaires grâce au eager loading.
        $agents = $query->get()->map(function (Agent $agent) {
            $lastEval = $agent->evaluations->first(); // Déjà trié par date décroissante
            return [
                'agent'    => $agent,
                'service'  => $agent->service,
                'lastEval' => $lastEval,
                'note'     => $lastEval ? (float) $lastEval->note_finale : null,
                'mention'  => $lastEval ? $this->mention((float) $lastEval->note_finale) : null,
                'statut'   => $lastEval?->statut,
            ];
        });

        // ── Tri des résultats (en mémoire, après enrichissement) ──────────
        $agents = match ($sortBy) {
            'note_asc'  => $agents->sortBy('note'),
            'note_desc' => $agents->sortByDesc('note'),
            'service'   => $agents->sortBy(fn ($a) => $a['service']?->nom ?? ''),
            'fonction'  => $agents->sortBy(fn ($a) => $a['agent']->fonction ?? ''),
            default     => $agents->sortBy(fn ($a) => $a['agent']->nom.' '.$a['agent']->prenom),
        };

        // Services pour le menu déroulant de filtre (tous les services de l'entité)
        $services = $ctx->getServices();

        // ── Statistiques KPI ──────────────────────────────────────────────
        $stats = [
            'total'    => $agents->count(),
            'evalues'  => $agents->filter(fn ($a) => $a['lastEval'] !== null)->count(),
            'note_moy' => $agents->filter(fn ($a) => $a['note'] !== null)->avg('note'),
        ];

        return view('directeur.personnel.index', compact(
            'direction', 'agents', 'services', 'stats',
            'sortBy', 'filterService', 'search'
        ));
    }

    /**
     * Traduit une note numérique en mention qualitative.
     *
     * Barème :
     *  ≥ 8,5 → Excellent
     *  ≥ 7   → Bien
     *  ≥ 5   → Passable
     *  < 5   → Insuffisant
     */
    private function mention(float $note): string
    {
        return match (true) {
            $note >= 8.5 => 'Excellent',
            $note >= 7   => 'Bien',
            $note >= 5   => 'Passable',
            default      => 'Insuffisant',
        };
    }
}
