<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\FicheObjectif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirecteurObjectifController extends Controller
{
    private function getDirection(): Direction
    {
        $direction = Direction::where('user_id', Auth::id())->first();
        if (! $direction) {
            abort(403, 'Aucune direction associée à votre compte.');
        }

        return $direction;
    }

    public function show(FicheObjectif $fiche): View
    {
        $direction = $this->getDirection();

        if (
            $fiche->assignable_type !== Direction::class ||
            (int) $fiche->assignable_id !== $direction->id
        ) {
            abort(403);
        }

        $fiche->load('objectifs');

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

        return view('directeur.objectifs.show', compact(
            'fiche',
            'direction',
            'statusClass',
            'statusLabel',
        ));
    }

    public function statut(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $direction = $this->getDirection();

        if (
            $fiche->assignable_type !== Direction::class ||
            (int) $fiche->assignable_id !== $direction->id
        ) {
            abort(403);
        }

        if ($fiche->statut !== 'en_attente') {
            return back()->with('error', 'Cette fiche ne peut plus être modifiée.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action        = $request->input('action');
        $fiche->statut = $action === 'accepter' ? 'acceptee' : 'refusee';
        $fiche->save();

        $msg = $action === 'accepter' ? 'Fiche d\'objectifs acceptée.' : 'Fiche d\'objectifs refusée.';

        return redirect()->route('directeur.objectifs.show', $fiche)->with('status', $msg);
    }
}
