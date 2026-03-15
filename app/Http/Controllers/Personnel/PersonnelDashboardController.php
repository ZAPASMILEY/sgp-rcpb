<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PersonnelDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('personnel.dashboard', [
            'user' => $request->user(),
        ]);
    }
}
