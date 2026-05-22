<?php

namespace App\Helpers;

use App\Models\Agent;
use App\Models\Entite;

/**
 * Calcule les labels d'entité et de structure d'un agent
 * pour les champs "Entité" et "Direction / Service" des fiches d'évaluation.
 *
 * Règle :
 *  – entite_nom      : toujours le nom de la faîtière RCPB
 *  – direction_service : structure réelle de l'agent
 *      • a un service                → "Service.nom — Caisse.nom" ou "Service.nom — Agence.nom"
 *      • a une caisse (sans service) → "Caisse.nom"
 *      • a une agence (sans service) → "Agence.nom"
 *      • a une DT (sans caisse)      → "DT Région / Ville"
 *      • a une direction             → "Direction.nom"
 *      • rattaché directement à la faîtière (entite_id) → nom de la faîtière
 *      • sinon                       → ''
 */
class AgentStructure
{
    /** Retourne un tableau ['entite_nom' => …, 'direction_service' => …] */
    public static function labels(Agent $agent): array
    {
        static $faitiere = null;
        if ($faitiere === null) {
            $faitiere = Entite::first();
        }

        $entiteNom = $faitiere?->sigle ?: ($faitiere?->nom ?? 'RCPB');

        $ds = self::resolveDirectionService($agent);

        return [
            'entite_nom'        => $entiteNom,
            'direction_service' => $ds,
        ];
    }

    private static function resolveDirectionService(Agent $agent): string
    {
        // Service + parent (caisse ou agence)
        if ($agent->service_id && $agent->relationLoaded('service') && $agent->service) {
            $parts = [$agent->service->nom];
            if ($agent->caisse_id && $agent->relationLoaded('caisse') && $agent->caisse) {
                $parts[] = $agent->caisse->nom;
            } elseif ($agent->agence_id && $agent->relationLoaded('agence') && $agent->agence) {
                $parts[] = $agent->agence->nom;
            }
            return implode(' — ', $parts);
        }

        // Caisse seule
        if ($agent->caisse_id && $agent->relationLoaded('caisse') && $agent->caisse) {
            return $agent->caisse->nom;
        }

        // Agence seule
        if ($agent->agence_id && $agent->relationLoaded('agence') && $agent->agence) {
            return $agent->agence->nom;
        }

        // Délégation Technique
        if ($agent->delegation_technique_id && $agent->relationLoaded('delegationTechnique') && $agent->delegationTechnique) {
            $dt = $agent->delegationTechnique;
            return 'DT ' . trim(($dt->region ?? '') . ($dt->ville ? ' / ' . $dt->ville : ''));
        }

        // Direction interne
        if ($agent->direction_id && $agent->relationLoaded('direction') && $agent->direction) {
            return $agent->direction->nom;
        }

        // Rattaché directement à la faîtière
        if ($agent->entite_id && $agent->relationLoaded('entite') && $agent->entite) {
            return $agent->entite->nom;
        }

        return '';
    }

    /**
     * Charge les relations nécessaires sur une collection d'agents.
     * À appeler avant de boucler sur les agents.
     */
    public static function loadRelations(\Illuminate\Database\Eloquent\Collection $agents): void
    {
        $agents->loadMissing([
            'service',
            'caisse',
            'agence',
            'delegationTechnique',
            'direction',
            'entite',
        ]);
    }
}
