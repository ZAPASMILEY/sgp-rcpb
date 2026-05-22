<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour le DG — uniquement ses propres formations.
 */
class DgFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $agentId = Auth::user()?->agent_id;

        return $agentId ? [$agentId] : [];
    }

    protected function getLayoutName(): string
    {
        return 'layouts.dg';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'dg';
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
