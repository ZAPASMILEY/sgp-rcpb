<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\FicheObjectif;
use App\Models\LigneFicheObjectif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * ChefObjectifController — Objectifs assignés par le chef à ses agents
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Le chef peut :
 *  • Créer une fiche d'objectifs pour un agent de sa structure (create / store)
 *  • Consulter une fiche d'objectifs qu'il a créée (show)
 *  • Supprimer une fiche non encore acceptée (destroy)
 *
 * Relation polymorphique :
 *   FicheObjectif.assignable_type = Agent::class
 *   FicheObjectif.assignable_id   = agent.id
 *
 * Le chef ne peut assigner des objectifs qu'aux agents de SA structure.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class ChefObjectifController extends Controller
{
    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Récupère le contexte chef du User connecté.
     * Déclenche un 403 si l'utilisateur n'est pas lié à une structure valide.
     */
    private function getContext(): ChefEntity
    {
        return ChefEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que la fiche a été créée par ce chef (il est l'assigning user)
     * et que l'agent cible appartient bien à sa structure.
     *
     * Note : on identifie la paternité par :
     *   assignable_type = Agent::class
     *   Pas d'evaluateur_id sur FicheObjectif → on vérifie que l'agent cible
     *   est bien dans la structure du chef.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function authorizeFiche(FicheObjectif $fiche): ChefEntity
    {
        $ctx = $this->getContext();

        // La fiche doit cibler un Agent (pas une structure)
        if ($fiche->assignable_type !== Agent::class) {
            abort(403, 'Cette fiche n\'est pas assignée à un agent.');
        }

        // L'agent cible doit appartenir à la structure du chef
        $agent = Agent::find($fiche->assignable_id);
        if (! $agent || ! $ctx->agentOwnedBy($agent)) {
            abort(403, 'Cet agent n\'est pas sous votre responsabilité.');
        }

        return $ctx;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CRÉER UNE FICHE D'OBJECTIFS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Affiche le formulaire de création d'une fiche d'objectifs pour un agent.
     *
     * Paramètre optionnel ?agent_id=X pour pré-sélectionner un agent.
     */
    public function create(Request $request): View
    {
        $ctx = $this->getContext();

        // Charge tous les agents subordonnés
        $agents = $ctx->getAgents();

        // Pré-sélection via ?agent_id=X (lien depuis la liste agents du dashboard)
        $preselectedId = (int) $request->get('agent_id', 0);
        $selectedAgent = $agents->firstWhere('id', $preselectedId);

        // Auto-sélection si un seul agent
        if (! $selectedAgent && $agents->count() === 1) {
            $selectedAgent = $agents->first();
        }

        return view('chef.subordonnes.objectifs.create', compact(
            'ctx',
            'agents',
            'selectedAgent',
        ));
    }

    /**
     * Persiste une nouvelle fiche d'objectifs pour un agent.
     *
     * Étapes :
     *  1. Validation du formulaire
     *  2. Vérification que l'agent appartient à la structure du chef
     *  3. Création de la FicheObjectif
     *  4. Création des LigneFicheObjectif (objectifs individuels)
     *  5. Notification à l'agent
     */
    public function store(Request $request): RedirectResponse
    {
        $ctx      = $this->getContext();
        $agentIds = $ctx->getAgentIds();

        // Validation du formulaire
        $validated = $request->validate([
            'agent_id'                   => ['required', 'integer', 'in:' . implode(',', $agentIds ?: [0])],
            'titre'                      => ['required', 'string', 'max:255'],
            'date_echeance'              => ['required', 'date'],
            'objectifs'                  => ['required', 'array', 'min:1'],
            'objectifs.*.description'    => ['required', 'string', 'max:500'],
        ]);

        // Vérification stricte de l'appartenance de l'agent
        $agent = Agent::findOrFail($validated['agent_id']);
        if (! $ctx->agentOwnedBy($agent)) {
            abort(403, 'Cet agent n\'est pas sous votre responsabilité.');
        }

        // Nettoyage des lignes d'objectifs vides
        $objectifsData = collect($validated['objectifs'])
            ->map(fn ($row) => ['description' => trim($row['description'] ?? '')])
            ->filter(fn ($row) => $row['description'] !== '')
            ->values()
            ->all();

        if (empty($objectifsData)) {
            return back()->withInput()->withErrors([
                'objectifs' => 'Vous devez renseigner au moins un objectif.',
            ]);
        }

        // Résolution de l'année depuis la date d'échéance
        try {
            $anneeId = Annee::resolveIdForDate($validated['date_echeance']);
        } catch (\Throwable) {
            $anneeId = null;
        }

        $user = Auth::user();

        // Transaction : FicheObjectif + LigneFicheObjectif
        $fiche = DB::transaction(function () use (
            $validated, $agent, $anneeId, $objectifsData
        ) {
            // Création de la fiche en attente (l'agent doit l'accepter)
            $fiche = FicheObjectif::create([
                'assignable_type'       => Agent::class,
                'assignable_id'         => $agent->id,
                'titre'                 => $validated['titre'],
                'annee_id'              => $anneeId,
                'date'                  => now()->toDateString(),
                'date_echeance'         => $validated['date_echeance'],
                'avancement_percentage' => 0,
                'statut'                => 'en_attente', // L'agent doit accepter
            ]);

            // Création de chaque ligne d'objectif
            foreach ($objectifsData as $objectifRow) {
                LigneFicheObjectif::create([
                    'fiche_objectif_id' => $fiche->id,
                    'description'       => $objectifRow['description'],
                ]);
            }

            return $fiche;
        });

        // Notification à l'agent : il doit accepter ou refuser la fiche
        $agentUser = \App\Models\User::where('agent_id', $agent->id)->first();
        if ($agentUser) {
            Alerte::notifier(
                $agentUser->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Votre chef {$user->name} vous a assigné une fiche d'objectifs : « {$fiche->titre} ». Veuillez la consulter et l'accepter ou la refuser.",
                'moyenne'
            );
        }

        return redirect()
            ->route('chef.objectifs.show', $fiche)
            ->with('status', 'Fiche d\'objectifs créée et transmise à l\'agent.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AFFICHER UNE FICHE D'OBJECTIFS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Affiche le détail d'une fiche d'objectifs créée par ce chef.
     *
     * La fiche doit cibler un agent de la structure du chef.
     */
    public function show(FicheObjectif $fiche): View
    {
        $ctx = $this->authorizeFiche($fiche);

        // Chargement des objectifs (lignes de la fiche)
        $fiche->load(['objectifs', 'assignable']);

        // Badge de statut
        $statusClass = match ($fiche->statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($fiche->statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) $fiche->statut),
        };

        return view('chef.subordonnes.objectifs.show', compact(
            'ctx',
            'fiche',
            'statusClass',
            'statusLabel',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SUPPRIMER UNE FICHE D'OBJECTIFS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Supprime une fiche d'objectifs créée par ce chef.
     *
     * Une fiche acceptée ne peut plus être supprimée (l'agent a déjà commencé
     * à travailler sur ses objectifs). Seules les fiches en_attente ou refusées
     * peuvent être supprimées.
     */
    public function destroy(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeFiche($fiche);

        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Une fiche d\'objectifs acceptée ne peut pas être supprimée.');
        }

        // Suppression en cascade (les LigneFicheObjectif sont supprimées par la FK)
        $fiche->delete();

        return redirect()
            ->route('chef.mon-espace', ['tab' => 'agents'])
            ->with('status', 'Fiche d\'objectifs supprimée.');
    }
}
