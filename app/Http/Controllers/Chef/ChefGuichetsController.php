<?php

namespace App\Http\Controllers\Chef;

use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Guichet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class ChefGuichetsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();
        $ctx  = ChefEntity::resolveOrFail($user);

        if ($ctx->type !== 'agence') {
            abort(403, 'Cette page est réservée aux Chefs d\'Agence.');
        }

        $search = trim((string) $request->input('search', ''));

        // Guichets de l'agence avec leur chef
        $guichetsQuery = Guichet::where('agence_id', $ctx->entity->id)
            ->with(['chef.user']);

        if ($search !== '') {
            $guichetsQuery->where('nom', 'like', "%{$search}%");
        }

        $guichets = $guichetsQuery->orderBy('nom')->get();

        // Stats par chef de guichet
        $guichets = $guichets->map(function (Guichet $guichet): array {
            $chef     = $guichet->chef;
            $chefUser = $chef ? User::where('agent_id', $chef->id)->first() : null;

            $nbEvals = $chefUser
                ? Evaluation::where('evaluable_type', User::class)
                    ->where('evaluable_id', $chefUser->id)
                    ->count()
                : 0;

            $nbObjectifs = $chefUser
                ? FicheObjectif::where('assignable_type', User::class)
                    ->where('assignable_id', $chefUser->id)
                    ->count()
                : 0;

            $noteAvg = $chefUser
                ? Evaluation::where('evaluable_type', User::class)
                    ->where('evaluable_id', $chefUser->id)
                    ->where('statut', 'valide')
                    ->whereNotNull('note_finale')
                    ->avg('note_finale')
                : null;

            return [
                'guichet'     => $guichet,
                'chef'        => $chef,
                'chefUser'    => $chefUser,
                'nbEvals'     => $nbEvals,
                'nbObjectifs' => $nbObjectifs,
                'noteAvg'     => $noteAvg !== null ? round((float) $noteAvg, 2) : null,
            ];
        });

        $stats = [
            'total'       => $guichets->count(),
            'avec_chef'   => $guichets->filter(fn ($g) => $g['chef'])->count(),
            'sans_chef'   => $guichets->filter(fn ($g) => ! $g['chef'])->count(),
        ];

        $filters = compact('search');

        return view('chef.guichets.index', compact('ctx', 'guichets', 'stats', 'filters'));
    }
}
