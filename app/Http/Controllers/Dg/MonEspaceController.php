<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonEspaceController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();

        if (! $user || strtolower($user->role) !== 'dg') {
            abort(403);
        }

        $tab    = $request->get('tab', 'evaluations');
        $statut = trim((string) $request->get('statut', ''));
        $search = trim((string) $request->get('search', ''));

        // ── Evaluations reçues du PCA (DG est l'évalué) ──────────────────────
        $baseE = fn () => Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon');

        $evalsQ = Evaluation::query()
            ->with(['evaluateur', 'identification'])
            ->where('evaluable_type', User::class)
            ->where('evaluable_id', $user->id)
            ->where('statut', '!=', 'brouillon')
            ->orderByDesc('date_debut');

        if ($statut && $tab === 'evaluations') {
            $evalsQ->where('statut', $statut);
        }

        $evaluationsStats = [
            'total'  => $baseE()->count(),
            'soumis' => $baseE()->where('statut', 'soumis')->count(),
            'valide' => $baseE()->where('statut', 'valide')->count(),
            'refuse' => $baseE()->where('statut', 'refuse')->count(),
        ];

        $evaluations = $evalsQ->paginate(10)->withQueryString();

        // ── Fiches d'objectifs assignées par le PCA au DG ────────────────────
        $baseF = fn () => FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $user->id);

        $fichesQ = FicheObjectif::query()
            ->withCount('objectifs')
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->orderByDesc('date');

        if ($search && $tab === 'objectifs') {
            $fichesQ->where(fn ($q) => $q->where('titre', 'like', "%{$search}%")
                ->orWhere('annee', 'like', "%{$search}%"));
        }

        if ($statut && $tab === 'objectifs') {
            if ($statut === 'en_attente') {
                $fichesQ->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'));
            } else {
                $fichesQ->where('statut', $statut);
            }
        }

        $fichesStats = [
            'total'      => $baseF()->count(),
            'acceptees'  => $baseF()->where('statut', 'acceptee')->count(),
            'en_attente' => $baseF()->where(fn ($q) => $q->where('statut', 'en_attente')->orWhereNull('statut'))->count(),
            'refusees'   => $baseF()->where('statut', 'refusee')->count(),
        ];

        $fiches  = $fichesQ->paginate(10)->withQueryString();
        $filters = compact('tab', 'statut', 'search');

        return view('dg.mon-espace', compact(
            'user',
            'tab',
            'evaluations',
            'evaluationsStats',
            'fiches',
            'fichesStats',
            'filters',
        ));
    }
}
