<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search  = trim((string) $request->get('search', ''));
        $action  = trim((string) $request->get('action', ''));
        $type    = trim((string) $request->get('type', ''));
        $userId  = (int) $request->get('user_id', 0);
        $dateFrom = $request->get('date_from', '');
        $dateTo   = $request->get('date_to', '');

        $query = AuditLog::with('user')->latest('created_at');

        if ($action !== '') {
            $query->where('action', $action);
        }
        if ($type !== '') {
            $query->where('auditable_type', 'like', '%'.$type);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }
        if ($search !== '') {
            $query->where('description', 'like', '%'.$search.'%');
        }
        if ($dateFrom !== '') {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $logs  = $query->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name']);

        $stats = [
            'total'         => AuditLog::count(),
            'today'         => AuditLog::whereDate('created_at', today())->count(),
            'statut_change' => AuditLog::where('action', 'statut_change')->count(),
            'deleted'       => AuditLog::where('action', 'deleted')->count(),
        ];

        $filters = compact('search', 'action', 'type', 'userId', 'dateFrom', 'dateTo');

        return view('admin.audit.index', compact('logs', 'users', 'stats', 'filters'));
    }
}
