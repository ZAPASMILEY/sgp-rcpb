<?php

namespace App\Http\Controllers\Pca;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour le PCA — uniquement ses propres formations.
 */
class PcaFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $agentId = Auth::user()?->agent_id;

        return $agentId ? [$agentId] : [];
    }

    protected function getLayoutName(): string
    {
        return 'layouts.pca';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'pca';
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
