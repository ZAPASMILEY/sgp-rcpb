<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Caisse;
use App\Models\FicheObjectif;
use App\Models\LigneFicheObjectif;
use App\Models\Service;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * ChefReceivedFicheController — Fiches d'objectifs reçues par le chef
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Distinct de ChefObjectifController qui gère les fiches que le chef ASSIGNE
 * à ses agents. Ce contrôleur gère les fiches que le chef lui-même REÇOIT
 * de son supérieur hiérarchique (directeur ou DGA).
 *
 * Une fiche reçue peut être adressée de deux façons :
 *   1. assignable_type = Agent::class  → assignée via l'agent lié au chef
 *   2. assignable_type = User::class   → assignée directement au compte User
 *      du chef par le directeur (cas le plus fréquent)
 *
 * Route prefix : /chef/mes-fiches  — Name prefix : chef.mes-fiches
 * ──────────────────────────────────────────────────────────────────────────────
 */
class ChefReceivedFicheController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Récupère le contexte chef du User connecté.
     * Déclenche un 403 si l'utilisateur n'est pas lié à une structure valide.
     */
    private function getContext(): ChefEntity
    {
        return ChefEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que la fiche est bien adressée à ce chef.
     *
     * Accepte deux cas :
     *   a) assignable_type = User::class  → directeur assigne directement au User du chef
     *   b) assignable_type = Agent::class → assigne via l'Agent lié au chef
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function checkOwnership(FicheObjectif $fiche, ChefEntity $ctx): void
    {
        $user  = Auth::user();
        $agent = $ctx->agent;

        // Cas a : fiche adressée directement au compte User du chef
        $isForUser = $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === $user->id;

        // Cas b : fiche adressée via l'Agent lié au chef
        $isForAgent = $agent
            && $fiche->assignable_type === Agent::class
            && (int) $fiche->assignable_id === $agent->id;

        if (! $isForUser && ! $isForAgent) {
            abort(403, "Cette fiche d'objectifs ne vous est pas adressée.");
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Affiche le détail d'une fiche d'objectifs reçue par le chef.
     *
     * Charge les objectifs de la fiche et prépare les badges de statut.
     * Le chef peut accepter ou refuser si la fiche est encore en attente.
     */
    public function show(FicheObjectif $fiche): View
    {
        $ctx = $this->getContext();
        $this->checkOwnership($fiche, $ctx);

        // Charge les lignes d'objectifs de la fiche (relation hasMany)
        $fiche->load(['objectifs', 'annee']);

        return view('objectifs.show', [
            'layout'          => 'layouts.chef',
            'fiche'           => $fiche,
            'backRoute'       => route('chef.mon-espace') . '?tab=fiches',
            'statusRoute'     => 'chef.mes-fiches.statut',
            'avancementRoute' => 'chef.mes-fiches.lignes.avancement',
            'contesterRoute'  => 'chef.mes-fiches.lignes.contester',
            'pdfRoute'        => 'chef.mes-fiches.pdf',
            'isAssignee'      => true,
        ]);
    }

    /**
     * Accepte ou refuse une fiche d'objectifs reçue par le chef.
     *
     * Seule une fiche en statut 'en_attente' peut être traitée.
     * Action valide : 'accepter' ou 'refuser'.
     */
    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $ctx = $this->getContext();
        $this->checkOwnership($fiche, $ctx);

        // Une fiche déjà acceptée ou refusée ne peut plus être modifiée
        if (($fiche->statut ?? 'en_attente') !== 'en_attente') {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action        = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        if ($action === 'accepter') {
            $fiche->date_validation = now()->toDateString();
        }
        $fiche->save();

        // Notifier le supérieur hiérarchique qui a assigné la fiche
        $assigneur = $this->resolveAssigneurUser($ctx);
        if ($assigneur) {
            $evalue      = Auth::user();
            $roleLabel   = $ctx->getRoleLabel();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                $assigneur->id,
                "Fiche d'objectifs {$actionLabel}e",
                "{$roleLabel} {$evalue->name} a {$actionLabel} la fiche d'objectifs « {$fiche->titre} » que vous lui avez assignée.",
                $action === 'accepter' ? 'moyenne' : 'haute',
                route('directeur.dashboard')
            );
        }

        $msg = $action === 'accepter'
            ? "Fiche d'objectifs acceptée."
            : "Fiche d'objectifs refusée.";

        return redirect()
            ->route('chef.mes-fiches.show', $fiche)
            ->with('status', $msg);
    }

    /**
     * Met à jour le pourcentage d'avancement de la fiche.
     *
     * Disponible uniquement sur les fiches acceptées.
     * Valeur attendue : entier entre 0 et 100.
     */
    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $ctx = $this->getContext();
        $this->checkOwnership($fiche, $ctx);

        if ($fiche->statut !== 'acceptee') {
            return back()->with('error', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $fiche->avancement_percentage = (int) $request->input('avancement_percentage');
        $fiche->save();

        return redirect()
            ->route('chef.mes-fiches.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    /**
     * Met à jour l'avancement d'une ligne d'objectif.
     */
    public function avancementLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $ctx   = $this->getContext();
        $fiche = FicheObjectif::findOrFail($ficheId);
        $this->checkOwnership($fiche, $ctx);

        if ($fiche->statut !== 'acceptee') {
            return redirect()->route('chef.mes-fiches.show', $fiche)
                ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $val = (int) $request->avancement_percentage;
        if ($val % 5 !== 0) {
            return redirect()->route('chef.mes-fiches.show', $fiche)
                ->with('status', "L'avancement doit être un multiple de 5.");
        }

        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $ligne->update(['avancement_percentage' => $val]);
        $fiche->recalculateAvancement();

        return redirect()->route('chef.mes-fiches.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    /**
     * Conteste un objectif d'une ligne de fiche.
     */
    public function contesterLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $ctx   = $this->getContext();
        $fiche = FicheObjectif::findOrFail($ficheId);
        $this->checkOwnership($fiche, $ctx);

        if ($fiche->statut === 'acceptee') {
            return redirect()->route('chef.mes-fiches.show', $fiche)
                ->with('status', 'Impossible de contester une fiche déjà acceptée.');
        }

        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $ligne->update(['statut' => 'contesté']);
        $fiche->update(['statut' => 'contesté']);

        $evalue    = Auth::user();
        $roleLabel = $ctx->getRoleLabel();

        $assigneur = $this->resolveAssigneurUser($ctx);
        if ($assigneur) {
            Alerte::notifier(
                $assigneur->id,
                'Objectif contesté',
                "{$roleLabel} {$evalue->name} a contesté un objectif dans la fiche « {$fiche->titre} ».",
                'haute',
                route('directeur.dashboard')
            );
        }

        $notifMsg = $assigneur ? 'Votre supérieur a été notifié.' : '';
        return redirect()->route('chef.mes-fiches.show', $fiche)
            ->with('status', "Objectif contesté. {$notifMsg}");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Résout le User qui a assigné la fiche au chef connecté,
     * en remontant la hiérarchie à partir du contexte.
     *
     *  Chef_Guichet  → Chef d'Agence (agence.chef_agent_id)
     *  Chef_Agence   → Directeur de Caisse (caisse.directeur_agent_id)
     *  Chef_Service  → Directeur de la structure parente (direction/caisse/DT)
     */
    private function resolveAssigneurUser(ChefEntity $ctx): ?User
    {
        return match ($ctx->type) {
            'guichet' => $this->chefAgenceUserFromGuichet($ctx),
            'agence'  => $this->directeurUserFromAgence($ctx),
            'service' => $this->directeurUserFromService($ctx),
            default   => null,
        };
    }

    /** Chef de Guichet → remonte à l'Agence → trouve le Chef d'Agence User */
    private function chefAgenceUserFromGuichet(ChefEntity $ctx): ?User
    {
        $agenceId = $ctx->entity->agence_id ?? null;
        if (! $agenceId) {
            return null;
        }
        $agence = Agence::find($agenceId);
        if (! $agence?->chef_agent_id) {
            return null;
        }
        return User::where('agent_id', $agence->chef_agent_id)->first();
    }

    /** Chef d'Agence → remonte à la Caisse → trouve le Directeur de Caisse User */
    private function directeurUserFromAgence(ChefEntity $ctx): ?User
    {
        $caisseId = $ctx->entity->caisse_id ?? null;
        if (! $caisseId) {
            return null;
        }
        $caisse = Caisse::find($caisseId);
        if (! $caisse?->directeur_agent_id) {
            return null;
        }
        return User::where('agent_id', $caisse->directeur_agent_id)->first();
    }

    /** Chef de Service → remonte à la structure parente (Direction, Caisse ou DT) */
    private function directeurUserFromService(ChefEntity $ctx): ?User
    {
        /** @var Service $service */
        $service = $ctx->entity;

        if ($service->direction_id) {
            $direction = \App\Models\Direction::find($service->direction_id);
            if ($direction?->directeur_agent_id) {
                return User::where('agent_id', $direction->directeur_agent_id)->first();
            }
        }

        if ($service->caisse_id) {
            $caisse = Caisse::find($service->caisse_id);
            if ($caisse?->directeur_agent_id) {
                return User::where('agent_id', $caisse->directeur_agent_id)->first();
            }
        }

        return null;
    }

    /**
     * Exporte la fiche d'objectifs reçue en PDF.
     *
     * Utilise le template pdf/fiche-objectifs.blade.php partagé.
     */
    public function exportPdf(FicheObjectif $fiche): \Illuminate\Http\Response
    {
        $ctx = $this->getContext();
        $this->checkOwnership($fiche, $ctx);

        $fiche->load(['objectifs', 'annee']);

        $assigneNom    = $ctx->getChefNomPrenom();
        $assigneRole   = $ctx->getRoleLabel();
        $assigneurNom  = '-';
        $assigneurRole = 'Supérieur hiérarchique';

        $pdf = Pdf::loadView('pdf.fiche-objectifs', compact(
            'fiche', 'assigneNom', 'assigneRole', 'assigneurNom', 'assigneurRole'
        ))->setPaper('a4', 'portrait');

        $filename = 'fiche-objectifs-chef-' . $fiche->id . '.pdf';

        return $pdf->download($filename);
    }
}
