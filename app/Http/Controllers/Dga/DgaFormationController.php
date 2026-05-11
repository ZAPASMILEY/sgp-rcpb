<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormations;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Mes formations" pour le DGA — ses propres formations + celles de ses subordonnés directs
 * (Directeurs Techniques + secrétaire DGA).
 */
class DgaFormationController extends Controller
{
    use HasFormations;

    protected function getAgentIds(Request $request): array
    {
        $user = Auth::user();

        // Propre agent du DGA
        $ids = $user->agent_id ? [$user->agent_id] : [];

        // Agents des Directeurs Techniques (subordonnés directs du DGA)
        $dtAgentIds = User::where('role', 'Directeur_Technique')
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->all();

        // Secrétaire du DGA (via entite)
        $entite = Entite::query()
            ->where('dga_agent_id', $user->agent_id)
            ->first()
            ?? Entite::query()->latest()->first();

        if ($entite?->dga_secretaire_agent_id) {
            $ids[] = $entite->dga_secretaire_agent_id;
        }

        return array_unique(array_merge($ids, $dtAgentIds));
    }

    protected function getLayoutName(): string
    {
        return 'layouts.dga';
    }

    protected function getPdfRoutePrefix(): string
    {
        return 'dga';
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
