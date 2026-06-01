<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Shared\TableauBaseController;

class DgTableauController extends TableauBaseController
{
    protected function indexRoute(): string  { return 'dg.tableaux.index'; }
    protected function exportRoute(): string { return 'dg.tableaux.export'; }
    protected function viewName(): string    { return 'dg.tableaux.index'; }
}
