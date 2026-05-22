<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Traits\GererLayout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiviteGererController extends Controller
{
    use GererLayout;

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $date   = $request->input('date');

        $activites = AuditLog::with('user')
            ->when($search, fn ($q) => $q->where(fn ($s) => $s
                ->where('action',       'like', "%{$search}%")
                ->orWhere('description','like', "%{$search}%")
                ->orWhere('user_name',  'like', "%{$search}%")
            ))
            ->when($date, fn ($q) => $q->whereDate('created_at', $date))
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $layout = $this->layout();

        return view('gerer.activites.index', compact('activites', 'layout'));
    }
}
