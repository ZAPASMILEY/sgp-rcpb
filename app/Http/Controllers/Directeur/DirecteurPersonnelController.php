<?php

namespace App\Http\Controllers\Directeur;

use App\Helpers\XlsxHelper;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirecteurPersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $ctx       = DirecteurEntity::resolveOrFail(Auth::user());
        $direction = $ctx->entity;

        $sortBy        = $request->query('sort', 'nom');
        $filterService = $request->query('service');
        $filterCaisse  = $request->query('caisse');
        $search        = $request->query('search');
        $statut        = $request->query('statut');
        $sexe          = trim((string) $request->query('sexe', ''));
        $fonction      = trim((string) $request->query('fonction', ''));

        $query = $this->baseQuery($ctx, $filterService, $filterCaisse, $search, $sexe, $fonction);

        $agents = $query->get()->map(function (Agent $agent) {
            $lastEval = $agent->evaluations->first();
            return [
                'agent'    => $agent,
                'service'  => $agent->service,
                'caisse'   => $agent->caisse,
                'lastEval' => $lastEval,
                'note'     => $lastEval ? (float) $lastEval->note_finale : null,
                'mention'  => $lastEval ? $this->mention((float) $lastEval->note_finale) : null,
                'statut'   => $lastEval?->statut,
            ];
        });

        // Filtre par statut évaluation
        if ($statut) {
            $agents = $agents->filter(fn ($a) => $a['statut'] === $statut);
        }

        $agents = match ($sortBy) {
            'note_asc'  => $agents->sortBy('note'),
            'note_desc' => $agents->sortByDesc('note'),
            'service'   => $agents->sortBy(fn ($a) => $a['service']?->nom ?? $a['caisse']?->nom ?? ''),
            'fonction'  => $agents->sortBy(fn ($a) => $a['agent']->role ?? ''),
            default     => $agents->sortBy(fn ($a) => $a['agent']->nom.' '.$a['agent']->prenom),
        };

        $services = $ctx->getServices();
        $caisses  = $ctx->hasCaisses() ? $ctx->getCaisses() : collect();

        $stats = [
            'total'    => $agents->count(),
            'evalues'  => $agents->filter(fn ($a) => $a['lastEval'] !== null)->count(),
            'note_moy' => $agents->filter(fn ($a) => $a['note'] !== null)->avg('note'),
        ];

        $fonctions = Agent::ROLES;

        return view('directeur.personnel.index', compact(
            'ctx', 'direction', 'agents', 'services', 'caisses', 'stats',
            'sortBy', 'filterService', 'filterCaisse', 'search', 'statut',
            'sexe', 'fonction', 'fonctions'
        ));
    }

    public function export(Request $request): Response
    {
        $ctx = DirecteurEntity::resolveOrFail(Auth::user());

        $filterService = $request->query('service');
        $filterCaisse  = $request->query('caisse');
        $search        = $request->query('search');
        $statut        = $request->query('statut');

        $query = $this->baseQuery($ctx, $filterService, $filterCaisse, $search);

        $agents = $query->get()->map(function (Agent $agent) {
            $lastEval = $agent->evaluations->first();
            return [
                'agent'    => $agent,
                'service'  => $agent->service,
                'caisse'   => $agent->caisse,
                'lastEval' => $lastEval,
                'note'     => $lastEval ? (float) $lastEval->note_finale : null,
                'mention'  => $lastEval ? $this->mention((float) $lastEval->note_finale) : null,
                'statut'   => $lastEval?->statut,
            ];
        });

        if ($statut) {
            $agents = $agents->filter(fn ($a) => $a['statut'] === $statut);
        }

        $filename = 'personnel_'.$ctx->getNom().'_'.now()->format('Ymd').'.xlsx';
        $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);

        $headers = ['Nom', 'Prénom', 'Email', 'Fonction', 'Service', 'Caisse', 'Dernière note', 'Mention', 'Statut éval.'];
        $rows    = [];

        foreach ($agents as $item) {
            $a      = $item['agent'];
            $rows[] = [
                $a->nom,
                $a->prenom,
                $a->email,
                $a->role ?? '',
                $item['service']?->nom ?? '',
                $item['caisse']?->nom ?? '',
                $item['note'] !== null ? (float) number_format($item['note'], 2, '.', '') : '',
                $item['mention'] ?? '',
                match($item['statut']) {
                    'valide'    => 'Validée',
                    'soumis'    => 'Soumise',
                    'refuse'    => 'Refusée',
                    'brouillon' => 'Brouillon',
                    default     => '',
                },
            ];
        }

        $content = XlsxHelper::build($headers, $rows, 'Personnel');

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function baseQuery(DirecteurEntity $ctx, ?string $filterService, ?string $filterCaisse, ?string $search, string $sexe = '', string $fonction = '')
    {
        // Pour Directeur_Technique : tous les agents avec delegation_technique_id = DT
        // Pour les autres : agents dans les services de l'entité
        if ($ctx->hasCaisses()) {
            $query = Agent::where('delegation_technique_id', $ctx->entity->id)
                ->with(['service', 'caisse', 'evaluations' => function ($q) {
                    $q->where('evaluable_type', Agent::class)->orderByDesc('date_debut');
                }]);

            if ($filterCaisse) {
                $query->where('caisse_id', (int) $filterCaisse);
            }
            if ($filterService) {
                $query->where('service_id', (int) $filterService);
            }
        } else {
            $serviceIds = $ctx->getServiceIds();
            $query = Agent::whereIn('service_id', $serviceIds)
                ->with(['service', 'caisse', 'evaluations' => function ($q) {
                    $q->where('evaluable_type', Agent::class)->orderByDesc('date_debut');
                }]);

            if ($filterService) {
                $query->where('service_id', (int) $filterService);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        if ($sexe !== '') {
            $query->where('sexe', $sexe);
        }
        if ($fonction !== '') {
            $query->where('role', $fonction);
        }

        return $query;
    }

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
