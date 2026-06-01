<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\StructureStats;
use App\Models\Agent;
use App\Models\Annee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RhStructureController extends Controller
{
    use StructureStats;

    public function __invoke(Request $request): View
    {
        $typeFilter    = $request->input('type') ?: null;
        $sortBy        = $request->input('sort', 'note');

        $anneeOuverte  = Annee::currentOpen();
        $notesVisibles = $anneeOuverte === null;
        $derniereAnnee = Annee::where('statut', 'cloture')->orderByDesc('annee')->first();
        $anneeId       = $derniereAnnee?->id;

        $structures     = $this->buildStructureStats($typeFilter, $sortBy, $notesVisibles ? $anneeId : null);
        $allStructures  = $typeFilter ? $this->buildStructureStats(null, $sortBy, $notesVisibles ? $anneeId : null) : $structures;
        $globalStats    = $this->computeGlobalStats($allStructures);
        $perimetreStats = $this->buildPerimetreStats($notesVisibles ? $anneeId : null);

        return view('rh.structures', compact(
            'structures', 'typeFilter', 'sortBy', 'globalStats', 'perimetreStats',
            'notesVisibles', 'anneeOuverte', 'derniereAnnee'
        ));
    }

    public function pdf(Request $request): Response
    {
        $typeFilter    = $request->input('type') ?: null;
        $sortBy        = $request->input('sort', 'note');
        $anneeOuverte  = Annee::currentOpen();
        $notesVisibles = $anneeOuverte === null;
        $derniereAnnee = Annee::where('statut', 'cloture')->orderByDesc('annee')->first();
        $anneeId       = $derniereAnnee?->id;

        $structures     = $this->buildStructureStats($typeFilter, $sortBy, $notesVisibles ? $anneeId : null);
        $allStructures  = $typeFilter ? $this->buildStructureStats(null, $sortBy, $notesVisibles ? $anneeId : null) : $structures;
        $globalStats    = $this->computeGlobalStats($allStructures);
        $perimetreStats = $this->buildPerimetreStats($notesVisibles ? $anneeId : null);

        $pdf = Pdf::loadView('structures.pdf', compact('structures', 'typeFilter', 'sortBy', 'globalStats', 'perimetreStats'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('structures-reseau-' . now()->format('Y-m-d') . '.pdf');
    }

    private function computeGlobalStats(\Illuminate\Support\Collection $allStructures): array
    {
        $notesWithValue = $allStructures->filter(fn ($s) => $s->note_moyenne !== null);
        return [
            'nb_structures'   => $allStructures->count(),
            // Comptage direct pour éviter le double-comptage des agents
            // qui ont plusieurs FKs renseignés (agence_id + guichet_id, etc.)
            'nb_agents'       => Agent::personnel()->count(),
            'note_moy_reseau' => $notesWithValue->count() > 0
                ? round($notesWithValue->avg('note_moyenne'), 2)
                : null,
        ];
    }
}
