<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guichet;
use App\Models\DelegationTechnique;
use Illuminate\Http\Request;

class GuichetController extends Controller
{
   // app/Http/Controllers/Admin/GuichetController.php

        public function index()
        {
            $guichets = Guichet::with(['chef', 'agence.delegationTechnique'])
                ->latest()
                ->paginate(10);

            // On récupère les délégations et on compte manuellement pour éviter l'erreur de méthode
            $delegations = DelegationTechnique::all()->map(function($dt) {
                // On compte les guichets qui appartiennent aux agences de cette DT
                $dt->guichets_count = Guichet::whereHas('agence', function($q) use ($dt) {
                    $q->where('delegation_technique_id', $dt->id);
                })->count();
                return $dt;
            });

            $stats = [
                'total' => Guichet::count(),
                'par_delegation' => $delegations
            ];

            return view('admin.guichets.index', compact('guichets', 'stats'));
         }
}