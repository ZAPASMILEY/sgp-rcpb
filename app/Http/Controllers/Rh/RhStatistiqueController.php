<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Shared\StatistiqueBaseController;

class RhStatistiqueController extends StatistiqueBaseController
{
    protected function routeName(): string        { return 'rh.statistiques'; }
    protected function viewName(): string         { return 'rh.statistiques.index'; }
    protected function csvFilenamePrefix(): string { return 'statistiques_rh_'; }
}
