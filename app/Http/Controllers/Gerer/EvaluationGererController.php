<?php

namespace App\Http\Controllers\Gerer;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Evaluation;
use App\Models\User;
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

        // ── Requête principale sécurisée via la relation identification ──
        $evaluations = Evaluation::with([
                'identification', // Contient déjà les instantanés (nom, prenom, matricule, direction, etc.)
                'semestre.annee',
                'evaluateur',
            ])
            ->whereHas('identification') // Sécurité : uniquement les fiches identifiées
            ->when($annee, fn ($q) => $q->where('annee_id', $annee->id))
            ->when($statut, fn ($q) => $q->where('statut', $statut))
            
            // Filtre de recherche textuelle sur l'identification fétiche
            ->when($search, fn ($q) => $q->whereHas('identification', fn ($i) => $i
                ->where('nom_prenom', 'like', "%{$search}%")
                ->orWhere('matricule', 'like', "%{$search}%")
                ->orWhere('emploi', 'like', "%{$search}%")
            ))
            
            // Filtre par Délégation Technique — couvre User::class ET Agent::class
            ->when($delegId, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHasMorph('evaluable', [User::class], fn ($u) => $u
                    ->whereHas('agent', fn ($a) => $a
                        ->where('delegation_technique_id', $delegId)
                        ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $delegId))
                    )
                )
                ->orWhereHasMorph('evaluable', [Agent::class], fn ($a) => $a
                    ->where('delegation_technique_id', $delegId)
                    ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $delegId))
                )
            ))

            // Filtre par Caisse — couvre User::class ET Agent::class
            ->when($caisseId, fn ($q) => $q->where(fn ($sub) => $sub
                ->whereHasMorph('evaluable', [User::class], fn ($u) => $u
                    ->whereHas('agent', fn ($a) => $a->where('caisse_id', $caisseId))
                )
                ->orWhereHasMorph('evaluable', [Agent::class], fn ($a) => $a
                    ->where('caisse_id', $caisseId)
                )
            ))
            ->latest()
            ->get();

        // Chargement des données pour les filtres de la vue
        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();
        $layout      = $this->layout();

        return view('gerer.evaluations.index', compact(
            'evaluations', 'annees', 'annee', 'delegations', 'caisses', 'layout'
        ));
    }
}