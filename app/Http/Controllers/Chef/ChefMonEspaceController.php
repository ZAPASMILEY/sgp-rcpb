<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * ChefMonEspaceController — Tableau de bord de l'espace chef
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Point d'entrée unique de l'espace chef. Centralise quatre blocs :
 *
 *  1. Évaluations REÇUES par le chef
 *     → evaluable_type = Agent::class, evaluable_id = agent du chef
 *     → Le chef peut accepter ou refuser ces fiches
 *
 *  2. Fiches d'objectifs REÇUES par le chef
 *     → assignable_type = Agent::class, assignable_id = agent du chef
 *     → Assignées par le directeur ou le chef de niveau supérieur
 *
 *  3. Liste des agents subordonnés
 *     → Agents dont la FK agentField pointe vers la structure du chef
 *     → Avec leur dernière évaluation créée par ce chef
 *
 *  4. Statistiques rapides (KPI cards)
 *
 * Tab actif transmis via ?tab=evaluations|objectifs|agents (défaut : evaluations)
 * ──────────────────────────────────────────────────────────────────────────────
 */
class ChefMonEspaceController extends Controller
{
    /**
     * Invokable : affiche le tableau de bord chef.
     *
     * Cette méthode est appelée par la route chef.mon-espace.
     */
    public function __invoke(Request $request): View
    {
        $user = Auth::user();

        // ── Résolution du contexte chef ───────────────────────────────────────
        // ChefEntity::resolveOrFail() lève un 403 si le User n'est pas lié
        // à une structure valide (Service, Agence ou Guichet).
        $ctx = ChefEntity::resolveOrFail($user);

        // ── Agent chef (le User connecté en tant qu'Agent) ────────────────────
        // L'agent est résolu dans ChefEntity via User::agent_id → Agent.
        // On le récupère directement depuis le contexte.
        $agent = $ctx->agent;

        // Tab actif : uniquement 'evaluations' ou 'objectifs' (les agents sont sur chef.equipe)
        $tab = in_array($request->query('tab'), ['evaluations', 'objectifs'])
            ? $request->query('tab')
            : 'evaluations';

        // ── 1. Évaluations REÇUES par le chef ────────────────────────────────
        // Un chef peut recevoir une évaluation de deux façons selon qui l'évalue :
        //   a) evaluable_type = Agent::class  → créée par un autre chef (rare, cas de sous-délégation)
        //   b) evaluable_type = User::class   → créée par un directeur ou DGA qui cible
        //      directement le compte User du chef (cas le plus fréquent)
        // On réunit les deux via une clause OR pour ne jamais manquer une évaluation reçue.
        $evaluationsRecues = Evaluation::where(function ($q) use ($user, $agent) {
                // Cas le plus fréquent : directeur évalue le chef via son compte User
                $q->where('evaluable_type', \App\Models\User::class)
                  ->where('evaluable_id', $user->id);

                // Cas alternatif : évaluation ciblant l'Agent lié au chef (si existant)
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('evaluable_type', Agent::class)
                           ->where('evaluable_id', $agent->id);
                    });
                }
            })
            ->with(['evaluateur', 'identification'])
            ->orderByDesc('date_debut')
            ->get();

        // Statistiques sur les évaluations reçues pour les KPI cards
        $evaluationsStats = [
            'total'     => $evaluationsRecues->count(),
            'soumis'    => $evaluationsRecues->where('statut', 'soumis')->count(),
            'valide'    => $evaluationsRecues->where('statut', 'valide')->count(),
            'refuse'    => $evaluationsRecues->whereIn('statut', ['refuse', 'reclamation'])->count(),
            'brouillon' => $evaluationsRecues->where('statut', 'brouillon')->count(),
        ];

        // ── 2. Fiches d'objectifs REÇUES par le chef ─────────────────────────
        // Même logique duale que les évaluations reçues :
        //   a) assignable_type = Agent::class  → fiche assignée par un chef de niveau supérieur
        //   b) assignable_type = User::class   → fiche assignée par un directeur/DGA ciblant
        //      directement le compte User du chef (cas standard : directeur → chef)
        // La clause OR garantit que toutes les fiches reçues sont visibles.
        $fichesObjectifs = FicheObjectif::where(function ($q) use ($user, $agent) {
                // Cas standard : directeur assigne une fiche via le compte User du chef
                $q->where('assignable_type', \App\Models\User::class)
                  ->where('assignable_id', $user->id);

                // Cas alternatif : fiche assignée via l'Agent lié au chef (si existant)
                if ($agent) {
                    $q->orWhere(function ($q2) use ($agent) {
                        $q2->where('assignable_type', Agent::class)
                           ->where('assignable_id', $agent->id);
                    });
                }
            })
            ->with('objectifs')
            ->orderByDesc('date')
            ->get();

        // Statistiques sur les fiches d'objectifs
        $fichesStats = [
            'total'      => $fichesObjectifs->count(),
            'acceptees'  => $fichesObjectifs->where('statut', 'acceptee')->count(),
            'en_attente' => $fichesObjectifs->where('statut', 'en_attente')->count(),
            'refusees'   => $fichesObjectifs->where('statut', 'refusee')->count(),
        ];

        return view('chef.mon-espace', compact(
            'user',
            'ctx',
            'agent',
            'tab',
            'evaluationsRecues',
            'evaluationsStats',
            'fichesObjectifs',
            'fichesStats',
        ));
    }
}
