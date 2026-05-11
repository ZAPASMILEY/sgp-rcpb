<?php

namespace App\Http\Controllers\Personnel;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\FicheObjectif;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * PersonnelFicheController — Fiches d'objectifs reçues par le personnel
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Utilisé par les agents simples, les secrétaires, et tout rôle sous la
 * protection du middleware 'personnel'. Gère les fiches reçues seulement.
 *
 * Une fiche peut être assignée de deux façons :
 *   1. assignable_type = Agent::class  → assignée par un chef direct
 *   2. assignable_type = User::class   → assignée par un directeur ou DGA
 *      directement sur le compte User (cas fréquent pour les secrétaires)
 *
 * Actions disponibles :
 *   - show   : consulter le détail de la fiche
 *   - statut : accepter ou refuser une fiche en attente
 *   - avancement : mettre à jour le pourcentage d'avancement
 *   - exportPdf  : télécharger la fiche en PDF
 * ──────────────────────────────────────────────────────────────────────────────
 */
class PersonnelFicheController extends Controller
{
    // ── Autorisation ──────────────────────────────────────────────────────────

    /**
     * Vérifie que la fiche est bien adressée à l'utilisateur connecté.
     *
     * Accepte deux cas :
     *   a) assignable_type = User::class  → directeur/DGA assigne directement
     *   b) assignable_type = Agent::class → chef assigne via l'agent lié
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function checkOwnership(FicheObjectif $fiche): void
    {
        $user  = Auth::user();
        $agent = $user?->agent_id ? Agent::find($user->agent_id) : null;

        // Cas a : fiche adressée directement au compte User
        $isForUser = $fiche->assignable_type === User::class
            && (int) $fiche->assignable_id === $user->id;

        // Cas b : fiche adressée via l'Agent lié au compte
        $isForAgent = $agent
            && $fiche->assignable_type === Agent::class
            && (int) $fiche->assignable_id === $agent->id;

        if (! $isForUser && ! $isForAgent) {
            abort(403, "Cette fiche d'objectifs ne vous est pas adressée.");
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Affiche le détail d'une fiche d'objectifs reçue.
     *
     * Charge les objectifs de la fiche (évite N+1) et prépare les
     * variables de statut pour la vue Blade.
     */
    public function show(FicheObjectif $fiche): View
    {
        $this->checkOwnership($fiche);

        // Charge les lignes d'objectifs de la fiche
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

        return view('personnel.fiches.show', compact(
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
     * Accepte ou refuse une fiche d'objectifs reçue.
     *
     * Seule une fiche en statut 'en_attente' peut être traitée.
     * Action valide : 'accepter' ou 'refuser'.
     */
    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->checkOwnership($fiche);

        // Une fiche déjà traitée ne peut plus être modifiée
        if (($fiche->statut ?? 'en_attente') !== 'en_attente') {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action        = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        $msg = $action === 'accepter'
            ? "Fiche d'objectifs acceptée."
            : "Fiche d'objectifs refusée.";

        return redirect()
            ->route('personnel.fiches.show', $fiche)
            ->with('status', $msg);
    }

    /**
     * Met à jour le pourcentage d'avancement de la fiche.
     *
     * Disponible uniquement sur les fiches acceptées.
     * La valeur doit être un entier entre 0 et 100.
     */
    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->checkOwnership($fiche);

        if ($fiche->statut !== 'acceptee') {
            return back()->with('error', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $fiche->avancement_percentage = (int) $request->input('avancement_percentage');
        $fiche->save();

        return redirect()
            ->route('personnel.fiches.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    /**
     * Exporte la fiche d'objectifs en PDF.
     *
     * Utilise le template pdf/fiche-objectifs.blade.php partagé.
     * Le fichier est retourné en téléchargement direct (inline).
     */
    public function exportPdf(FicheObjectif $fiche): \Illuminate\Http\Response
    {
        $this->checkOwnership($fiche);

        $fiche->load(['objectifs', 'annee']);

        $user = Auth::user();

        $roleLabels = [
            'DGA'                 => 'Directeur Général Adjoint',
            'Directeur_Technique' => 'Directeur Technique',
            'Directeur_Caisse'    => 'Directeur de Caisse',
            'Chef_Service'        => 'Chef de Service',
            'Chef_Agence'         => "Chef d'Agence",
            'Chef_Guichet'        => 'Chef de Guichet',
            'Assistante_Dg'       => 'Assistante DG',
            'Conseillers_Dg'      => 'Conseiller DG',
            'Secretaire_Assistante' => 'Secrétaire',
        ];

        $assigneNom    = $user->name ?? '-';
        $assigneRole   = $roleLabels[$user->role ?? ''] ?? ($user->role ?? 'Personnel');
        $assigneurNom  = '-';
        $assigneurRole = 'Supérieur hiérarchique';

        $pdf = Pdf::loadView('pdf.fiche-objectifs', compact(
            'fiche', 'assigneNom', 'assigneRole', 'assigneurNom', 'assigneurRole'
        ))->setPaper('a4', 'portrait');

        $filename = 'fiche-objectifs-' . $fiche->id . '.pdf';

        return $pdf->download($filename);
    }
}
