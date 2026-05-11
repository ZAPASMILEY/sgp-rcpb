<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait StructureStats
{
    /**
     * Build an aggregated collection of all structure types with evaluation stats.
     * Chain: structure → agents (FK) → users (agent_id) → evaluations (evaluable_type=User)
     */
    protected function buildStructureStats(?string $typeFilter = null, string $sortBy = 'note'): Collection
    {
        $types = [
            [
                'table'    => 'delegation_techniques',
                'fk'       => 'delegation_technique_id',
                'type'     => 'Délégation Technique',
                'type_key' => 'delegation',
                'nom_sql'  => "CONCAT(s.region, ' \u{2013} ', s.ville)",
                'group'    => ['s.id', 's.region', 's.ville'],
            ],
            [
                'table'    => 'directions',
                'fk'       => 'direction_id',
                'type'     => 'Direction',
                'type_key' => 'direction',
                'nom_sql'  => 's.nom',
                'group'    => ['s.id', 's.nom'],
            ],
            [
                'table'    => 'caisses',
                'fk'       => 'caisse_id',
                'type'     => 'Caisse',
                'type_key' => 'caisse',
                'nom_sql'  => 's.nom',
                'group'    => ['s.id', 's.nom'],
            ],
            [
                'table'    => 'agences',
                'fk'       => 'agence_id',
                'type'     => 'Agence',
                'type_key' => 'agence',
                'nom_sql'  => 's.nom',
                'group'    => ['s.id', 's.nom'],
            ],
            [
                'table'    => 'guichets',
                'fk'       => 'guichet_id',
                'type'     => 'Guichet',
                'type_key' => 'guichet',
                'nom_sql'  => 's.nom',
                'group'    => ['s.id', 's.nom'],
            ],
            [
                'table'    => 'services',
                'fk'       => 'service_id',
                'type'     => 'Service',
                'type_key' => 'service',
                'nom_sql'  => 's.nom',
                'group'    => ['s.id', 's.nom'],
            ],
        ];

        $results = collect();

        foreach ($types as $def) {
            if ($typeFilter && $typeFilter !== $def['type_key']) {
                continue;
            }

            $rows = DB::table($def['table'] . ' as s')
                ->leftJoin('agents as a', 'a.' . $def['fk'], '=', 's.id')
                ->leftJoin('users as u', 'u.agent_id', '=', 'a.id')
                ->leftJoin('evaluations as e', function ($join) {
                    $join->on('e.evaluable_id', '=', 'u.id')
                         ->where('e.evaluable_type', '=', 'App\\Models\\User')
                         ->where('e.statut', '!=', 'brouillon')
                         ->where('e.note_finale', '>', 0);
                })
                ->select([
                    's.id',
                    DB::raw($def['nom_sql'] . ' as nom'),
                    DB::raw('COUNT(DISTINCT a.id) as nb_agents'),
                    DB::raw('COUNT(DISTINCT e.id) as nb_evaluations'),
                    DB::raw('AVG(e.note_finale) as note_moyenne'),
                    DB::raw('COUNT(DISTINCT CASE WHEN e.note_finale >= 8.5 THEN e.id END) as nb_excellent'),
                    DB::raw('COUNT(DISTINCT CASE WHEN e.note_finale >= 7   AND e.note_finale < 8.5 THEN e.id END) as nb_bien'),
                    DB::raw('COUNT(DISTINCT CASE WHEN e.note_finale >= 5   AND e.note_finale < 7   THEN e.id END) as nb_passable'),
                    DB::raw('COUNT(DISTINCT CASE WHEN e.note_finale > 0    AND e.note_finale < 5   THEN e.id END) as nb_insuffisant'),
                ])
                ->groupBy($def['group'])
                ->get()
                ->map(function ($row) use ($def) {
                    return (object) [
                        'id'             => $row->id,
                        'nom'            => $row->nom,
                        'type'           => $def['type'],
                        'type_key'       => $def['type_key'],
                        'nb_agents'      => (int) $row->nb_agents,
                        'nb_evaluations' => (int) $row->nb_evaluations,
                        'note_moyenne'   => $row->note_moyenne !== null ? round((float) $row->note_moyenne, 2) : null,
                        'nb_excellent'   => (int) $row->nb_excellent,
                        'nb_bien'        => (int) $row->nb_bien,
                        'nb_passable'    => (int) $row->nb_passable,
                        'nb_insuffisant' => (int) $row->nb_insuffisant,
                    ];
                });

            $results = $results->concat($rows);
        }

        return match($sortBy) {
            'nom'    => $results->sortBy('nom', SORT_NATURAL | SORT_FLAG_CASE)->values(),
            'type'   => $results->sortBy(['type', 'nom'])->values(),
            'agents' => $results->sortByDesc('nb_agents')->values(),
            'evals'  => $results->sortByDesc('nb_evaluations')->values(),
            default  => $results->sortByDesc(function ($item) {
                            return $item->note_moyenne ?? -PHP_FLOAT_MAX;
                        })->values(),
        };
    }

    /**
     * Calcule trois notes distinctes :
     * - faitiere : agents en Directions + Services (siège central)
     * - terrain  : agents en DT + Caisses + Agences + Guichets (réseau)
     * - globale  : toutes évaluations confondues (faitière + terrain)
     */
    protected function buildPerimetreStats(): array
    {
        $base = DB::table('agents as a')
            ->join('users as u', 'u.agent_id', '=', 'a.id')
            ->join('evaluations as e', function ($join) {
                $join->on('e.evaluable_id', '=', 'u.id')
                     ->where('e.evaluable_type', '=', 'App\\Models\\User')
                     ->where('e.statut', '!=', 'brouillon')
                     ->where('e.note_finale', '>', 0);
            });

        // Faîtière : Directions + Services
        $faitiere = (clone $base)
            ->where(function ($q) {
                $q->whereNotNull('a.direction_id')
                  ->orWhereNotNull('a.service_id');
            })
            ->selectRaw('AVG(e.note_finale) as note_moy, COUNT(DISTINCT e.id) as nb_evals')
            ->first();

        // Terrain : DT + Caisses + Agences + Guichets
        $terrain = (clone $base)
            ->where(function ($q) {
                $q->whereNotNull('a.delegation_technique_id')
                  ->orWhereNotNull('a.caisse_id')
                  ->orWhereNotNull('a.agence_id')
                  ->orWhereNotNull('a.guichet_id');
            })
            ->selectRaw('AVG(e.note_finale) as note_moy, COUNT(DISTINCT e.id) as nb_evals')
            ->first();

        // Globale : toutes évaluations (faîtière + terrain)
        $globale = (clone $base)
            ->selectRaw('AVG(e.note_finale) as note_moy, COUNT(DISTINCT e.id) as nb_evals')
            ->first();

        return [
            'faitiere' => ($faitiere && $faitiere->nb_evals > 0) ? round((float) $faitiere->note_moy, 2) : null,
            'terrain'  => ($terrain  && $terrain->nb_evals  > 0) ? round((float) $terrain->note_moy,  2) : null,
            'globale'  => ($globale  && $globale->nb_evals  > 0) ? round((float) $globale->note_moy,  2) : null,
        ];
    }
}
