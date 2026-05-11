<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour les Chefs — ses propres formations + celles de ses agents.
 */
class ChefFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $user = Auth::user();
        $ctx  = ChefEntity::resolve($user);

        if (! $ctx) {
            return [];
        }

        // Propre agent du chef + agents subordonnés
        $ids = $ctx->getAgentIds();

        if ($ctx->agent) {
            $ids[] = $ctx->agent->id;
        }

        return array_unique($ids);
    }

    protected function getLayoutName(): string
    {
        return 'layouts.chef';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'chef';
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
