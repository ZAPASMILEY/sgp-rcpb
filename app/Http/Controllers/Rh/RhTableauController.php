<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Shared\TableauBaseController;

class RhTableauController extends TableauBaseController
{
    protected function indexRoute(): string  { return 'rh.tableaux.index'; }
    protected function exportRoute(): string { return 'rh.tableaux.export'; }
    protected function viewName(): string    { return 'rh.tableaux.index'; }
}
