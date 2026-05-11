<?php

namespace App\Http\Controllers\Directeur;

use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurMonEspaceController — Tableau de bord du directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Page d'accueil de l'espace directeur. Elle centralise trois blocs :
 *
 *  1. Évaluations reçues  — fiches d'évaluation que le DG a adressées
 *                           au directeur (evaluable = entité du directeur,
 *                           evaluable_role = 'manager').
 *
 *  2. Objectifs reçus     — fiches d'objectifs assignées par le DG
 *                           à l'entité du directeur (assignable = entité).
 *
 *  3. Vue d'ensemble des services — liste des services rattachés à l'entité,
 *                           avec le dernier statut d'évaluation de chaque chef,
 *                           le nombre d'agents et la note moyenne des chefs
 *                           déjà évalués.
 *
 * La même vue sert les trois types de directeurs (Direction / Caisse /
 * DelegationTechnique) grâce à DirecteurEntity qui abstrait l'entité.
 *
 * Tabs disponibles : 'dashboard' (défaut) | 'evaluations' | 'objectifs'
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurMonEspaceController extends \App\Http\Controllers\Controller
{
    public function __invoke(Request $request): View
    {
        $user = Auth::user();

        // Résout l'entité (Direction, Caisse ou DelegationTechnique) liée au compte connecté.
        $ctx = DirecteurEntity::resolveOrFail($user);

        // Tab actif transmis dans l'URL (?tab=evaluations, ?tab=objectifs)
        $tab = $request->query('tab', 'evaluations');

        // ── 1. Évaluations reçues par le directeur ────────────────────────
        // Deux sources possibles :
        //  a) DG/PCA assigne à l'entité (evaluable_type = entité, role = 'manager')
        //  b) DGA assigne directement à l'User (evaluable_type = User, role = rôle du directeur)
        $evaluationsRecues = Evaluation::where(function ($q) use ($ctx) {
                $q->where('evaluable_type', $ctx->modelClass)
                  ->where('evaluable_id', $ctx->getId())
                  ->where('evaluable_role', 'manager');
            })->orWhere(function ($q) use ($user) {
                $q->where('evaluable_type', \App\Models\User::class)
                  ->where('evaluable_id', $user->id);
            })
            ->with(['evaluateur', 'identification'])
            ->orderByDesc('date_debut')
            ->get();

        // Statistiques rapides affichées dans les cartes KPI du dashboard
        $evaluationsStats = [
            'total'     => $evaluationsRecues->count(),
            'soumis'    => $evaluationsRecues->where('statut', 'soumis')->count(),
            'valide'    => $evaluationsRecues->where('statut', 'valide')->count(),
            'refuse'    => $evaluationsRecues->where('statut', 'refuse')->count(),
            'brouillon' => $evaluationsRecues->where('statut', 'brouillon')->count(),
        ];

        // ── 2. Fiches d'objectifs reçues par le directeur ─────────────────
        // Deux cas possibles :
        //  a) DG/PCA assigne à l'entité (Direction, Caisse, DelegationTechnique)
        //  b) DGA assigne directement au User (Directeur_Technique ou secrétaire)
        $fichesObjectifs = FicheObjectif::where(function ($q) use ($ctx) {
                $q->where('assignable_type', $ctx->modelClass)
                  ->where('assignable_id', $ctx->getId());
            })->orWhere(function ($q) use ($user) {
                $q->where('assignable_type', \App\Models\User::class)
                  ->where('assignable_id', $user->id);
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

        // ── 3. Vue d'ensemble des services / chefs ────────────────────────
        // Charge tous les services rattachés à l'entité avec leurs agents.
        $servicesWithAgents = $ctx->getServicesWithAgents();

        // Pour chaque service, on cherche la dernière évaluation soumise ou validée
        // du chef (evaluable = Service, role = manager) et le nombre d'agents.
        $servicesOverview = $servicesWithAgents->map(function (Service $service) use ($ctx) {
            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->whereIn('statut', ['soumis', 'valide'])
                ->orderByDesc('date_debut')
                ->first();

            return [
                'service'      => $service,
                'eval'         => $latestEval,
                'agents_count' => $service->agents->count(),
            ];
        });

        // Note moyenne calculée sur les chefs de service déjà évalués
        $notesChefs  = $servicesOverview->pluck('eval')->filter()->pluck('note_finale')->map(fn ($n) => (float) $n);
        $noteMoyenne = $notesChefs->isNotEmpty() ? round($notesChefs->avg(), 2) : null;

        // Aperçu des caisses (Directeur_Technique uniquement)
        $caissesOverview = collect();
        if ($ctx->hasCaisses()) {
            $caissesOverview = $ctx->getCaissesWithDirecteur()->map(function ($caisse) use ($user) {
                $directeurUser = $caisse->directeur_agent_id
                    ? \App\Models\User::where('agent_id', $caisse->directeur_agent_id)->first()
                    : null;

                $latestEval = \App\Models\Evaluation::where('evaluable_type', \App\Models\Caisse::class)
                    ->where('evaluable_id', $caisse->id)
                    ->where('evaluable_role', 'manager')
                    ->whereIn('statut', ['soumis', 'valide'])
                    ->orderByDesc('date_debut')
                    ->first();

                return [
                    'caisse'        => $caisse,
                    'directeurUser' => $directeurUser,
                    'eval'          => $latestEval,
                    'agents_count'  => $caisse->agents_count,
                ];
            });
        }

        // ── 4. Évaluations créées par le directeur pour ses chefs ─────────
        // Le directeur est ici l'évaluateur. Cible : les chefs de service (evaluable = Service).
        $evaluationsCreees = Evaluation::where('evaluateur_id', $user->id)
            ->where('evaluable_type', Service::class)
            ->where('evaluable_role', 'manager')
            ->with(['evaluable', 'identification'])
            ->orderByDesc('created_at')
            ->get();

        // Passe $direction = entité pour la compatibilité avec les vues existantes
        // (les vues Blade utilisent $direction qu'il s'agisse d'une Direction, Caisse ou Délégation).
        $direction = $ctx->entity;

        return view('directeur.mon-espace', compact(
            'user',
            'direction',
            'ctx',
            'tab',
            'servicesOverview',
            'caissesOverview',
            'evaluationsRecues',
            'evaluationsStats',
            'fichesObjectifs',
            'fichesStats',
            'evaluationsCreees',
            'noteMoyenne',
        ));
    }
}
