<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasFormationCrud;
use App\Traits\GererLayout;

/**
 * Gestion des formations accessible à tout utilisateur disposant de
 * la permission 'formations.assigner', quel que soit son rôle.
 *
 * Le layout est déterminé dynamiquement par le trait GererLayout.
 * Toute la logique CRUD est mutualisée dans HasFormationCrud.
 */
class FormationGererController extends Controller
{
    use GererLayout, HasFormationCrud;

    protected function routePrefix(): string
    {
        return 'gerer';
    }

    protected function formationLayout(): ?string
    {
        return $this->layout();
    }
}
