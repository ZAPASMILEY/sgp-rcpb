<?php

namespace App\Http\Controllers\Subordonne;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Entite;
use App\Models\FicheObjectif;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubordonneObjectifController extends Controller
{
    private const ALLOWED_ROLES = ['Assistante_Dg', 'Conseillers_Dg'];

    private function authorize(FicheObjectif $fiche): void
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
        $this->authorize($fiche);
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

        return view('subordonne.objectifs.show', compact('fiche', 'user', 'statutClass', 'statutLabel'));
    }

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize($fiche);

        if (! in_array($fiche->statut ?? 'en_attente', ['en_attente', null], true)) {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        // Notifier le DG (créateur de la fiche)
        $subordonne = Auth::user();
        $dgUser = User::where('role', 'DG')->where('pca_entite_id', $subordonne?->pca_entite_id)->first();
        if ($dgUser) {
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                $dgUser->id,
                "Fiche d'objectifs {$actionLabel}e",
                "{$subordonne?->name} a {$actionLabel} la fiche d'objectifs « {$fiche->titre} » que vous lui avez assignée.",
                $action === 'accepter' ? 'moyenne' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';

        return redirect()->route('subordonne.objectifs.show', $fiche)->with('status', $msg);
    }

    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize($fiche);

        $request->validate(['avancement_percentage' => ['required', 'integer', 'min:0', 'max:100']]);
        $pct = (int) $request->avancement_percentage;

        if ($pct % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        $fiche->avancement_percentage = $pct;
        $fiche->save();

        return redirect()->route('subordonne.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
    }

    public function exportPdf(FicheObjectif $fiche)
    {
        $this->authorize($fiche);
        $user = Auth::user();
        $fiche->load('objectifs');

        $entite = Entite::find($user->pca_entite_id);
        $dgUser = User::where('role', 'DG')->where('pca_entite_id', $user->pca_entite_id)->first();
        $institutionSigle = $this->resolveInstitutionSigle($entite);

        $roleLabels = ['Assistante_Dg' => 'Assistante DG', 'Conseillers_Dg' => 'Conseiller DG'];

        $pdf = Pdf::loadView('pdf.contrat-objectif', [
            'contrat'                => $fiche,
            'partieCollaborateur'    => (object) ['name' => $user->name, 'role' => $roleLabels[$user->role] ?? $user->role],
            'partieFaitiere'         => $entite,
            'partieFaitiereNomComplet' => $dgUser?->name ?? '',
            'partieFaitiereRole'     => 'Directeur General',
            'objectifs'              => $fiche->objectifs,
            'dateDebut'              => $fiche->date,
            'dateFin'                => $fiche->date_echeance,
            'institution_sigle'      => $institutionSigle,
        ]);

        return $pdf->download('contrat-objectifs-'.$fiche->id.'.pdf');
    }

    private function resolveInstitutionSigle(?Entite $entite): string
    {
        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        return ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
    }
}
