<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    /**
     * Résout le layout Blade selon le rôle de l'utilisateur connecté.
     */
    private function layout(): string
    {
        $role = Auth::user()?->role ?? '';

        return match (true) {
            $role === 'Admin'
                => 'layouts.app',
            strtolower($role) === 'dg'
                => 'layouts.dg',
            $role === 'DGA'
                => 'layouts.dga',
            $role === 'PCA'
                => 'layouts.pca',
            in_array($role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'])
                => 'layouts.chef',
            in_array($role, ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'])
                => 'layouts.directeur',
            $role === 'RH'
                => 'layouts.rh',
            in_array($role, ['Assistante_Dg', 'Conseillers_Dg'])
                => 'layouts.subordonne',
            in_array($role, [
                'Agent',
                'Secretaire_Assistante',
                'Secretaire_Direction',
                'Secretaire_Technique',
                'Secretaire_Caisse',
                'Secretaire_Agence',
            ])  => 'layouts.personnel',
            default
                => 'layouts.personnel',
        };
    }

    /**
     * Page principale : toutes les notifications de l'utilisateur connecté.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->alertes()
            ->withPivot('lu', 'lu_at')
            ->orderByDesc('alertes.created_at')
            ->paginate(20);

        $totalNonLues = $user->alertesNonLues()->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'totalNonLues'  => $totalNonLues,
            'layout'        => $this->layout(),
        ]);
    }

    /**
     * Marquer une notification individuelle comme lue.
     */
    public function marquerLu(Request $request, Alerte $alerte): RedirectResponse|JsonResponse
    {
        DB::table('alerte_user')
            ->where('user_id', $request->user()->id)
            ->where('alerte_id', $alerte->id)
            ->update(['lu' => true, 'lu_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function marquerToutLu(Request $request): RedirectResponse
    {
        DB::table('alerte_user')
            ->where('user_id', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true, 'lu_at' => now()]);

        return back()->with('status', 'Toutes les notifications ont été marquées comme lues.');
    }
}
