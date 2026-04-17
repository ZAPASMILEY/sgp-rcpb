<?php

namespace App\Http\Controllers\Subordonne;

use App\Http\Controllers\Controller;
use App\Models\FicheObjectif;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubordonneObjectifController extends Controller
{
    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    public function show(FicheObjectif $fiche): View
    {
        $user = Auth::user();

        if (! $user || ! in_array($user->role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }

        // L'utilisateur ne peut voir que ses propres fiches
        if ($fiche->assignable_type !== User::class || (int) $fiche->assignable_id !== $user->id) {
            abort(403);
        }

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
}
