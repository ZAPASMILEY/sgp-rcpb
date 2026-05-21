<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\FicheObjectif;
use App\Models\LigneFicheObjectif;
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

        $statut = $fiche->statut ?? 'en_attente';

        // Classes CSS et libellés pour le badge de statut
        $sc = match ($statut) {
            'acceptee'  => ['label' => 'Acceptée',   'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'border' => 'border-emerald-200'],
            'refusee'   => ['label' => 'Refusée',    'bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'dot' => 'bg-rose-500',    'border' => 'border-rose-200'],
            default     => ['label' => 'En attente', 'bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'dot' => 'bg-amber-400',   'border' => 'border-amber-200'],
        };

        $avancement    = (int) ($fiche->avancement_percentage ?? 0);
        $progressColor = $avancement >= 75 ? 'bg-emerald-500' : ($avancement >= 40 ? 'bg-sky-500' : ($avancement > 0 ? 'bg-amber-400' : 'bg-slate-200'));
        $echeance      = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
        $expired       = $echeance && $echeance->isPast();
        $isPending     = $statut === 'en_attente';

        return view('chef.mes-fiches.show', compact(
            'ctx',
            'fiche',
            'sc',
            'avancement',
            'progressColor',
            'echeance',
            'expired',
            'isPending',
        ));
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

        $evalue     = Auth::user();
        $directeurs = User::where('role', 'Directeur')->get();
        foreach ($directeurs as $directeur) {
            Alerte::notifier(
                $directeur->id,
                'Objectif contesté',
                "{$evalue->name} (Chef) a contesté un objectif dans la fiche « {$fiche->titre} ».",
                'haute'
            );
        }

        return redirect()->route('chef.mes-fiches.show', $fiche)
            ->with('status', 'Objectif contesté. Le directeur a été notifié.');
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
