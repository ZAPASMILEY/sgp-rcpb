<?php

namespace App\Http\Controllers\Subordonne;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ObjectifController extends Controller
{
    /** Vérifie que la fiche appartient bien au subordonné connecté. */
    private function authorizeAccess(FicheObjectif $fiche): void
    {
        $user = auth()->user();
        if (
            $fiche->assignable_type !== User::class ||
            (int) $fiche->assignable_id !== $user->id
        ) {
            abort(403, 'Accès non autorisé à cette fiche.');
        }
    }

    public function show(FicheObjectif $fiche): View
    {
        $this->authorizeAccess($fiche);
        $fiche->load('objectifs');

        return view('subordonne.objectifs.show', compact('fiche'));
    }

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeAccess($fiche);

        if ($fiche->statut !== 'en_attente') {
            return back()->with('error', 'Cette fiche ne peut plus être modifiée.');
        }

        $request->validate(['statut' => ['required', 'in:acceptee,refusee']]);

        $fiche->statut = $request->statut;
        $fiche->save();

        $message = $fiche->statut === 'acceptee'
            ? 'Fiche acceptée avec succès.'
            : 'Fiche refusée.';

        return redirect()->route('subordonne.objectifs.show', $fiche)->with('status', $message);
    }

    public function contrat(FicheObjectif $fiche): View
    {
        $this->authorizeAccess($fiche);
        $fiche->load('objectifs', 'assignable');

        return view('subordonne.objectifs.contrat', compact('fiche'));
    }

    public function pdf(FicheObjectif $fiche): Response
    {
        $this->authorizeAccess($fiche);
        $fiche->load('objectifs', 'assignable');

        $pdf = Pdf::loadView('subordonne.objectifs.contrat', compact('fiche'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('contrat-objectifs-'.$fiche->id.'.pdf');
    }
}
