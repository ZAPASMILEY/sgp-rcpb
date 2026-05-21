<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\DelegationTechnique;
use App\Models\Caisse;
use App\Models\Direction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RhDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tab = $request->input('tab', 'evaluations');
        
        // Extraction et nettoyage des filtres globaux
        $search    = $request->filled('search') ? trim((string) $request->input('search')) : null;
        $statut    = $request->filled('statut') ? trim((string) $request->input('statut')) : null;
        $annee     = $request->filled('annee') ? trim((string) $request->input('annee')) : null;
        $sexe      = $request->filled('sexe') ? trim((string) $request->input('sexe')) : '';
        $fonction  = $request->filled('fonction') ? trim((string) $request->input('fonction')) : '';
        
        // Filtres de structure (Délégations, Caisses, Directions)
        $dt_id     = $request->filled('dt_id') ? $request->input('dt_id') : null;
        $caisse_id = $request->filled('caisse_id') ? $request->input('caisse_id') : null;
        $dir_id    = $request->filled('dir_id') ? $request->input('dir_id') : null;

        // Normalisation du statut pour les fiches d'objectifs
        $statutDb = null;
        if ($statut) {
            $statutDb = match (mb_strtolower($statut, 'UTF-8')) {
                'acceptée', 'acceptee', 'valide' => 'acceptee',
                'en attente', 'en_attente', 'soumis', 'brouillon' => 'en_attente',
                'rejetée', 'refusee', 'refusée', 'refuse' => 'refusee',
                default => $statut,
            };
        }

        // ── 1. CONSTRUCTION DE LA REQUÊTE ÉVALUATIONS FILTRÉE ──
        $evalQuery = Evaluation::query();

        if ($statut) {
            // Adaptabilité si ta table utilise 'refuse' au lieu de 'refusee'
            $evalQuery->where('statut', $statut === 'refusee' ? 'refuse' : ($statut === 'acceptee' ? 'valide' : $statut));
        }
        if ($annee) {
            $evalQuery->whereYear('date_debut', $annee);
        }
        if ($search) {
            $evalQuery->where(function ($s) use ($search) {
                $s->whereHas('identification', fn ($i) =>
                        $i->where('nom_prenom', 'like', "%{$search}%")
                          ->orWhere('emploi', 'like', "%{$search}%")
                  )
                  ->orWhereHas('evaluateur', fn ($e) =>
                        $e->where('name', 'like', "%{$search}%")
                  );
            });
        }
        if ($sexe !== '') {
            $evalQuery->where(function ($s) use ($sexe) {
                $s->where(function ($s2) use ($sexe) {
                    $s2->where('evaluable_type', Agent::class)
                       ->whereHas('evaluable', fn ($qa) => $qa->where('sexe', $sexe));
                })->orWhere(function ($s2) use ($sexe) {
                    $s2->where('evaluable_type', \App\Models\User::class)
                       ->whereHas('evaluable', fn ($qu) =>
                           $qu->whereHas('agent', fn ($qa) => $qa->where('sexe', $sexe))
                       );
                });
            });
        }
        if ($fonction !== '') {
            $evalQuery->whereHas('identification', fn ($i) => $i->where('emploi', $fonction));
        }
        // Application transversale des filtres de structure sur les évaluations
        if ($dt_id) {
            $evalQuery->whereHas('identification.agent', fn($q) => $q->where('delegation_technique_id', $dt_id));
        }
        if ($caisse_id) {
            $evalQuery->whereHas('identification.agent', fn($q) => $q->where('caisse_id', $caisse_id));
        }
        if ($dir_id) {
            $evalQuery->whereHas('identification.agent', fn($q) => $q->where('direction_id', $dir_id));
        }

        // ── 2. KPI RÉACTIFS ET DYNAMIQUES ──
        $stats = [
            'agents'      => Agent::personnel()->count(),
            'total'       => (clone $evalQuery)->count(),
            'soumis'      => (clone $evalQuery)->where('statut', 'soumis')->count(),
            'valide'      => (clone $evalQuery)->where('statut', 'valide')->count(),
            'refuse'      => (clone $evalQuery)->where('statut', 'refuse')->count(),
            'brouillon'   => (clone $evalQuery)->where('statut', 'brouillon')->count(),
            
            'excellent'   => (clone $evalQuery)->where('statut', '!=', 'brouillon')->where('note_finale', '>=', 8.5)->count(),
            'bien'        => (clone $evalQuery)->where('statut', '!=', 'brouillon')->where('note_finale', '>=', 7)->where('note_finale', '<', 8.5)->count(),
            'passable'    => (clone $evalQuery)->where('statut', '!=', 'brouillon')->where('note_finale', '>=', 5)->where('note_finale', '<', 7)->count(),
            'insuffisant' => (clone $evalQuery)->where('statut', '!=', 'brouillon')->where('note_finale', '>', 0)->where('note_finale', '<', 5)->count(),
        ];

        // ── 3. CHARGEMENT DES DONNÉES DE L'ONGLET SÉLECTIONNÉ ──
        
        // Onglet Évaluations
        $evaluations = null;
        if ($tab === 'evaluations') {
            $evaluations = $evalQuery->with([
                'evaluateur:id,name,role',
                'evaluable',
                'identification:id,evaluation_id,nom_prenom,emploi',
            ])
            ->orderByDesc('date_debut')
            ->paginate(25)
            ->withQueryString();
        }

        // Onglet Agents
        $agents = null;
        if ($tab === 'agents') {
            $agentQuery = Agent::with([
                'delegationTechnique:id,region,ville',
                'caisse:id,nom',
                'direction:id,nom',
                'user:id,agent_id,role',
            ])->personnel()->orderBy('nom')->orderBy('prenom');

            if ($search) {
                $agentQuery->where(fn ($s) =>
                    $s->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%")
                );
            }
            if ($fonction !== '')   $agentQuery->where('role', $fonction);
            if ($sexe !== '')       $agentQuery->where('sexe', $sexe);
            if ($dt_id)             $agentQuery->where('delegation_technique_id', $dt_id);
            if ($caisse_id)         $agentQuery->where('caisse_id', $caisse_id);
            if ($dir_id)            $agentQuery->where('direction_id', $dir_id);

            $agents = $agentQuery->paginate(20)->withQueryString();
        }

        // Onglet Objectifs
        $fiches = null;
        $ficheStats = null;
        if ($tab === 'objectifs') {
            // Requête de base pour les fiches d'objectifs
            $ficheQuery = FicheObjectif::with(['assignable'])->withCount('objectifs');

            // Application des filtres sur les fiches d'objectifs
            if ($statutDb)   $ficheQuery->where('statut', $statutDb);
            if ($annee)      $ficheQuery->whereYear('date', $annee);
            if ($search) {
                $ficheQuery->where(function($q) use ($search) {
                    $q->where('titre', 'like', "%{$search}%")
                      ->orWhereHas('assignable', fn($a) => $a->where('nom', 'like', "%{$search}%")->orWhere('prenom', 'like', "%{$search}%"));
                });
            }
            if ($dt_id) {
                $ficheQuery->whereHas('assignable', fn($q) => $q->where('delegation_technique_id', $dt_id));
            }
            if ($caisse_id) {
                $ficheQuery->whereHas('assignable', fn($q) => $q->where('caisse_id', $caisse_id));
            }
            if ($dir_id) {
                $ficheQuery->whereHas('assignable', fn($q) => $q->where('direction_id', $dir_id));
            }

            // Calcul des compteurs d'objectifs synchronisés avec les filtres actuels
            $ficheStats = [
                'total'      => (clone $ficheQuery)->count(),
                'acceptee'   => (clone $ficheQuery)->where('statut', 'acceptee')->count(),
                'en_attente' => (clone $ficheQuery)->whereIn('statut', ['en_attente', 'brouillon'])->count(),
                'refusee'    => (clone $ficheQuery)->where('statut', 'refusee')->count(),
            ];

            $fiches = $ficheQuery->orderByDesc('date')->paginate(20)->withQueryString();
        }

        // ── 4. DONNÉES FIXES DU FORMULAIRE ET COUVERTURE ANNUELLE ──
        $delegations = DelegationTechnique::orderBy('region')->get(['id', 'region', 'ville']);
        $caisses     = Caisse::orderBy('nom')->get(['id', 'nom']);
        $directions  = Direction::orderBy('nom')->get(['id', 'nom']);
        $fonctions   = Agent::ROLES;

        $openAnnee      = Annee::currentOpen();
        $agentsSansEval = 0;
        $totalAgents    = Agent::personnel()->count();
        $agentsEvalues  = 0;

        if ($openAnnee) {
            $agentsSansEval = Agent::personnel()
                ->whereDoesntHave('evaluations', function ($q) use ($openAnnee) {
                    $q->where('statut', 'valide')->where('annee_id', $openAnnee->id);
                })->count();
            $agentsEvalues = $totalAgents - $agentsSansEval;
        }

        $filters = compact('tab', 'statut', 'search', 'annee', 'sexe', 'fonction', 'dt_id', 'caisse_id', 'dir_id');

        return view('rh.dashboard', compact(
            'stats', 'tab', 'filters', 'delegations', 'caisses', 'directions',
            'agents', 'evaluations', 'fiches', 'ficheStats', 'openAnnee',
            'agentsSansEval', 'totalAgents', 'agentsEvalues', 'fonctions'
        ));
    }
}