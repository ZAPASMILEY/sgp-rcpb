<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Shared\StatistiqueBaseController;

class DgStatistiqueController extends StatistiqueBaseController
{
    protected function routeName(): string        { return 'dg.statistiques'; }
    protected function viewName(): string         { return 'dg.statistiques.index'; }
    protected function csvFilenamePrefix(): string { return 'statistiques_dg_'; }
}
