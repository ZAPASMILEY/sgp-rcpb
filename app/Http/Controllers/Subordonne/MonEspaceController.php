<?php

namespace App\Http\Controllers\Subordonne;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonEspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $fiches = FicheObjectif::query()
            ->with('objectifs')
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->orderByDesc('date')
            ->get();

        // Le subordonné ne voit ses évaluations que si elles sont soumises ou validées
        $evaluations = Evaluation::query()
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->whereIn('statut', ['soumis', 'valide'])
            ->orderByDesc('date_debut')
            ->get();

        $fichesStats = [
            'total'      => $fiches->count(),
            'acceptees'  => $fiches->where('statut', 'acceptee')->count(),
            'en_attente' => $fiches->whereIn('statut', ['en_attente', null])->count(),
            'refusees'   => $fiches->where('statut', 'refusee')->count(),
        ];

        $tab = $request->get('tab', 'objectifs');

        $roleLabel = match ($user->role) {
            'DGA'           => 'Directeur Général Adjoint',
            'Assistante_Dg' => 'Assistante du DG',
            'Conseillers_Dg'=> 'Conseiller du DG',
            default         => $user->role,
        };

        return view('subordonne.mon-espace', compact('fiches', 'evaluations', 'fichesStats', 'tab', 'roleLabel'));
    }
}
