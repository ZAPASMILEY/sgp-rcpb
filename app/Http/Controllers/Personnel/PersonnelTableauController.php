<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Shared\TableauBaseController;

class PersonnelTableauController extends TableauBaseController
{
    protected function indexRoute(): string  { return 'personnel.tableaux.index'; }
    protected function exportRoute(): string { return 'personnel.tableaux.export'; }
    protected function viewName(): string    { return 'personnel.tableaux.index'; }
}
