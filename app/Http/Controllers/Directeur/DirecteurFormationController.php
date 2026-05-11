<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour les Directeurs — ses propres formations + celles de tout son personnel.
 */
class DirecteurFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $user = Auth::user();
        $ctx  = DirecteurEntity::resolve($user);

        if (! $ctx) {
            return [];
        }

        // Propre agent du directeur
        $ids = $ctx->agent ? [$ctx->agent->id] : [];

        // Agents subordonnés selon le type de structure
        if ($ctx->hasCaisses()) {
            // Directeur_Technique : tous les agents de la délégation
            $subordinateIds = Agent::where('delegation_technique_id', $ctx->entity->id)
                ->pluck('id')
                ->all();
        } else {
            // Direction ou Caisse : agents dans les services de la structure
            $serviceIds     = $ctx->getServiceIds();
            $subordinateIds = Agent::whereIn('service_id', $serviceIds)
                ->pluck('id')
                ->all();
        }

        return array_unique(array_merge($ids, $subordinateIds));
    }

    protected function getLayoutName(): string
    {
        return 'layouts.directeur';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'directeur';
    }

    public function __invoke(Request $request)
    {
        return $this->mesFormations($request);
    }

    public function pdf(Request $request, \App\Models\Formation $formation)
    {
        return $this->formationPdf($request, $formation);
    }
}
