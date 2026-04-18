<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Entite;
use App\Models\FicheObjectif;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DgaObjectifController extends Controller
{
    private function authorize(FicheObjectif $fiche): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'DGA') {
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

        return view('dga.objectifs.show', compact('fiche', 'user', 'statutClass', 'statutLabel'));
    }

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize($fiche);

        if (! in_array($fiche->statut ?? 'en_attente', ['en_attente', null], true)) {
            return back()->with('error', 'Cette fiche a déjà été traitée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $fiche->statut = $request->input('action') === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        $msg = $request->input('action') === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';

        return redirect()->route('dga.objectifs.show', $fiche)->with('status', $msg);
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

        return redirect()->route('dga.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
    }

    public function exportPdf(FicheObjectif $fiche)
    {
        $this->authorize($fiche);
        $user = Auth::user();
        $fiche->load('objectifs');

        $entite = Entite::find($user->pca_entite_id);
        $dgUser = User::where('role', 'DG')->where('pca_entite_id', $user->pca_entite_id)->first();
        $institutionSigle = $this->resolveInstitutionSigle($entite);

        $pdf = Pdf::loadView('pdf.contrat-objectif', [
            'contrat'                => $fiche,
            'partieCollaborateur'    => (object) ['name' => $user->name, 'role' => 'Directeur General Adjoint'],
            'partieFaitiere'         => $entite,
            'partieFaitiereNomComplet' => $dgUser?->name ?? '',
            'partieFaitiereRole'     => 'Directeur General',
            'objectifs'              => $fiche->objectifs,
            'dateDebut'              => $fiche->date,
            'dateFin'                => $fiche->date_echeance,
            'institution_sigle'      => $institutionSigle,
        ]);

        return $pdf->download('contrat-objectifs-'.$fiche->id.'-dga.pdf');
    }

    private function resolveInstitutionSigle(?Entite $entite): string
    {
        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        return ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';
    }
}
