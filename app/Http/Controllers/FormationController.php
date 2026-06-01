<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Concerns\HasFormations;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Models\Agent;
use App\Models\Entite;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur unifié "Mes formations" — remplace les 7 contrôleurs par rôle.
 *
 * Rôles couverts : PCA, DG, DGA, Assistante_Dg, Conseillers_Dg,
 *                  Directeur_Direction, Directeur_Caisse, Directeur_Technique,
 *                  Chef_Service, Chef_Agence, Chef_Guichet, et tout le Personnel.
 *
 * Le dispatch s'effectue via Auth::user()->role dans les trois méthodes
 * du trait HasFormations.
 */
class FormationController extends Controller
{
    use HasFormations;

    // ══════════════════════════════════════════════════════════════════════════
    // Implémentation des abstraits du trait HasFormations
    // ══════════════════════════════════════════════════════════════════════════

    protected function getAgentIds(Request $request): array
    {
        $user = Auth::user();
        $role = $user?->role;

        return match (true) {
            in_array($role, ['PCA', 'DG', 'Assistante_Dg', 'Conseillers_Dg'], true)
                => $user->agent_id ? [$user->agent_id] : [],

            $role === 'DGA'
                => $this->agentIdsDga($user),

            in_array($role, ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'], true)
                => $this->agentIdsDirecteur($user),

            in_array($role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)
                => $this->agentIdsChef($user),

            default
                => $user->agent_id ? [$user->agent_id] : [],
        };
    }

    protected function getLayoutName(): string
    {
        return match (Auth::user()?->role) {
            'PCA'                                                                => 'layouts.pca',
            'DG'                                                                 => 'layouts.dg',
            'DGA'                                                                => 'layouts.dga',
            'Assistante_Dg', 'Conseillers_Dg'                                   => 'layouts.subordonne',
            'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'    => 'layouts.directeur',
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                       => 'layouts.chef',
            default                                                              => 'layouts.personnel',
        };
    }

    protected function getPdfRoutePrefix(): string
    {
        return match (Auth::user()?->role) {
            'PCA'                                                                => 'pca',
            'DG'                                                                 => 'dg',
            'DGA'                                                                => 'dga',
            'Assistante_Dg', 'Conseillers_Dg'                                   => 'subordonne',
            'Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'    => 'directeur',
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                       => 'chef',
            default                                                              => 'personnel',
        };
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Points d'entrée publics
    // ══════════════════════════════════════════════════════════════════════════

    public function __invoke(Request $request)
    {
        return $this->mesFormations($request);
    }

    public function pdf(Request $request, Formation $formation)
    {
        return $this->formationPdf($request, $formation);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Résolution des IDs agents par rôle (privé)
    // ══════════════════════════════════════════════════════════════════════════

    /** DGA : soi + Directeurs Techniques + secrétaire DGA. */
    private function agentIdsDga(User $user): array
    {
        $ids = $user->agent_id ? [$user->agent_id] : [];

        $dtIds = User::where('role', 'Directeur_Technique')
            ->whereNotNull('agent_id')
            ->pluck('agent_id')
            ->all();

        $entite = Entite::where('dga_agent_id', $user->agent_id)->first()
            ?? Entite::latest()->first();

        if ($entite?->dga_secretaire_agent_id) {
            $ids[] = $entite->dga_secretaire_agent_id;
        }

        return array_unique(array_merge($ids, $dtIds));
    }

    /** Directeurs (Direction / Caisse / Technique) : soi + personnel de la structure. */
    private function agentIdsDirecteur(User $user): array
    {
        $ctx = DirecteurEntity::resolve($user);

        if (! $ctx) {
            return [];
        }

        $ids = $ctx->agent ? [$ctx->agent->id] : [];

        if ($ctx->hasCaisses()) {
            $subordinateIds = Agent::where('delegation_technique_id', $ctx->entity->id)
                ->pluck('id')
                ->all();
        } else {
            $subordinateIds = Agent::whereIn('service_id', $ctx->getServiceIds())
                ->pluck('id')
                ->all();
        }

        return array_unique(array_merge($ids, $subordinateIds));
    }

    /** Chefs (Service / Agence / Guichet) : soi + agents subordonnés. */
    private function agentIdsChef(User $user): array
    {
        $ctx = ChefEntity::resolve($user);

        if (! $ctx) {
            return [];
        }

        $ids = $ctx->getAgentIds();

        if ($ctx->agent) {
            $ids[] = $ctx->agent->id;
        }

        return array_unique($ids);
    }
}
