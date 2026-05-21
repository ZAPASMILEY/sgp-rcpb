<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Traits\GererLayout;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationGererController extends Controller
{
    use GererLayout;

    public function index(Request $request): View
    {
        $annees  = Annee::orderByDesc('annee')->get();
        $annee   = $request->filled('annee_id')
            ? $annees->find($request->integer('annee_id'))
            : (Annee::currentOpen() ?? $annees->first());

        $search   = $request->input('search');
        $statut   = $request->input('statut');
        $delegId  = $request->integer('delegation_id') ?: null;
        $caisseId = $request->integer('caisse_id') ?: null;

        $evaluations = Evaluation::with([
                'agent.delegationTechnique',
                'agent.caisse.delegationTechnique',
                'agent.direction',
                'semestre',
                'evaluateur',
            ])
            ->when($annee, fn ($q) => $q->where('annee_id', $annee->id))
            ->when($statut, fn ($q) => $q->where('statut', $statut))
            ->when($search, fn ($q) => $q->whereHas('agent', fn ($a) => $a
                ->where('nom',        'like', "%{$search}%")
                ->orWhere('prenom',   'like', "%{$search}%")
                ->orWhere('matricule','like', "%{$search}%")
            ))
            ->when($delegId, fn ($q) => $q->whereHas('agent', fn ($a) => $a
                ->where(fn ($s) => $s
                    ->where('delegation_technique_id', $delegId)
                    ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $delegId))
                )
            ))
            ->when($caisseId, fn ($q) => $q->whereHas('agent', fn ($a) => $a->where('caisse_id', $caisseId)))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();
        $layout      = $this->layout();

        return view('gerer.evaluations.index', compact(
            'evaluations', 'annees', 'annee', 'delegations', 'caisses', 'layout'
        ));
    }
}
