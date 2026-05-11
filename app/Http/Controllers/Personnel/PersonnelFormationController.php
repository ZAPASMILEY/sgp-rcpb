<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour le Personnel — uniquement ses propres formations.
 */
class PersonnelFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $user  = Auth::user();
        $agent = $user->agent_id ? Agent::find($user->agent_id) : null;

        return $agent ? [$agent->id] : [];
    }

    protected function getLayoutName(): string
    {
        return 'layouts.personnel';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'personnel';
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
