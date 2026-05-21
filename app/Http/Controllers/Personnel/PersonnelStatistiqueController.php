<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Shared\StatistiqueBaseController;

class PersonnelStatistiqueController extends StatistiqueBaseController
{
    protected function routeName(): string        { return 'personnel.statistiques'; }
    protected function viewName(): string         { return 'personnel.statistiques.index'; }
    protected function csvFilenamePrefix(): string { return 'statistiques_personnel_'; }
}
