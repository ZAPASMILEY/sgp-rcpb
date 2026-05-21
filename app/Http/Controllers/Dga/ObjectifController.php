<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Entite;
use App\Models\FicheObjectif;
use App\Models\LigneFicheObjectif;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ObjectifController extends Controller
{
    use ResolvesEntite;

    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    private const ROLE_LABELS = [
        'DGA'            => 'Directeur Général Adjoint',
        'Assistante_Dg'  => 'Assistante DG',
        'Conseillers_Dg' => 'Conseiller DG',
    ];

    private function authorizeObjectif(FicheObjectif $fiche): void
    {
        $user = Auth::user();
        if (! $user || ! in_array($user->role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }
        if ($fiche->assignable_type !== User::class || (int) $fiche->assignable_id !== $user->id) {
            abort(403);
        }
    }

    public function show(FicheObjectif $fiche): View
    {
        $this->authorizeObjectif($fiche);
        $user = Auth::user();
        $fiche->load('objectifs');

        $statutClass = match ($fiche->statut ?? 'en_attente') {
            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
            default    => 'border-amber-200 bg-amber-50 text-amber-700',
        };
        $statutLabel = match ($fiche->statut ?? 'en_attente') {
            'acceptee' => 'Acceptee',
            'refusee'  => 'Refusee',
            default    => 'En attente',
        };

        return view($this->espaceViewPrefix().'.objectifs.show', compact('fiche', 'user', 'statutClass', 'statutLabel'));
    }

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeObjectif($fiche);

        if (! in_array($fiche->statut ?? 'en_attente', ['en_attente', null], true)) {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action      = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        if ($action === 'accepter') {
            $fiche->date_validation = now()->toDateString();
        }
        $fiche->save();

        // Notifier le DG (créateur de la fiche)
        $evalue  = Auth::user();
        $entite  = $this->getEntite();
        $dgUser  = $this->getDGUser($entite);
        if ($dgUser) {
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            $roleLabel   = self::ROLE_LABELS[$evalue?->role] ?? ($evalue?->role ?? '');
            Alerte::notifier(
                $dgUser->id,
                "Fiche d'objectifs {$actionLabel}e",
                "{$roleLabel} {$evalue?->name} a {$actionLabel} la fiche d'objectifs « {$fiche->titre} » que vous lui avez assignée.",
                $action === 'accepter' ? 'moyenne' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';

        return redirect()
            ->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
            ->with('status', $msg);
    }

    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeObjectif($fiche);

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $pct = (int) $request->avancement_percentage;

        if ($pct % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        $fiche->avancement_percentage = $pct;
        $fiche->save();

        return redirect()
            ->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    public function avancementLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $fiche = FicheObjectif::findOrFail($ficheId);
        $this->authorizeObjectif($fiche);

        if ($fiche->statut !== 'acceptee') {
            return redirect()->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
                ->with('status', "L'avancement ne peut être modifié que sur une fiche acceptée.");
        }

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $val = (int) $request->avancement_percentage;
        if ($val % 5 !== 0) {
            return redirect()->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
                ->with('status', "L'avancement doit être un multiple de 5.");
        }

        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $ligne->update(['avancement_percentage' => $val]);
        $fiche->recalculateAvancement();

        return redirect()->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    public function contesterLigne(Request $request, $ficheId, $ligneId): RedirectResponse
    {
        $fiche = FicheObjectif::findOrFail($ficheId);
        $this->authorizeObjectif($fiche);

        if ($fiche->statut === 'acceptee') {
            return redirect()->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
                ->with('status', 'Impossible de contester une fiche déjà acceptée.');
        }

        $ligne = LigneFicheObjectif::where('fiche_objectif_id', $ficheId)->findOrFail($ligneId);
        $ligne->update(['statut' => 'contesté']);
        $fiche->update(['statut' => 'contesté']);

        $evalue  = Auth::user();
        $dgUsers = User::where('role', 'DG')->get();
        foreach ($dgUsers as $dg) {
            Alerte::notifier(
                $dg->id,
                'Objectif contesté',
                "{$evalue->name} a contesté un objectif dans la fiche « {$fiche->titre} ».",
                'haute'
            );
        }

        return redirect()->route($this->espaceRoutePrefix().'.objectifs.show', $fiche)
            ->with('status', 'Objectif contesté. Le DG a été notifié.');
    }

    public function exportPdf(FicheObjectif $fiche)
    {
        $this->authorizeObjectif($fiche);
        $user   = Auth::user();
        $entite = $this->getEntite();
        $dgUser = $this->getDGUser($entite);
        $fiche->load('objectifs');

        $institutionSigle = $this->resolveInstitutionSigle($entite);

        $pdf = Pdf::loadView('pdf.contrat-objectif', [
            'contrat'                  => $fiche,
            'partieCollaborateur'      => (object) [
                'name' => $user->name,
                'role' => self::ROLE_LABELS[$user->role] ?? $user->role,
            ],
            'partieFaitiere'           => $entite,
            'partieFaitiereNomComplet' => $dgUser?->name ?? '',
            'partieFaitiereRole'       => 'Directeur Général',
            'objectifs'                => $fiche->objectifs,
            'dateDebut'                => $fiche->date,
            'dateFin'                  => $fiche->date_echeance,
            'institution_sigle'        => $institutionSigle,
        ]);

        return $pdf->download('contrat-objectifs-'.$fiche->id.'.pdf');
    }

    private function resolveInstitutionSigle(?Entite $entite): string
    {
        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        return ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
    }
}
