<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\StructureStats;
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

        $structures     = $this->buildStructureStats($typeFilter, $sortBy);
        $allStructures  = $typeFilter ? $this->buildStructureStats(null, $sortBy) : $structures;
        $globalStats    = $this->computeGlobalStats($allStructures);
        $perimetreStats = $this->buildPerimetreStats();

        return view('rh.structures', compact('structures', 'typeFilter', 'sortBy', 'globalStats', 'perimetreStats'));
    }

    public function pdf(Request $request): Response
    {
        $typeFilter    = $request->input('type') ?: null;
        $sortBy        = $request->input('sort', 'note');

        $structures     = $this->buildStructureStats($typeFilter, $sortBy);
        $allStructures  = $typeFilter ? $this->buildStructureStats(null, $sortBy) : $structures;
        $globalStats    = $this->computeGlobalStats($allStructures);
        $perimetreStats = $this->buildPerimetreStats();

        $pdf = Pdf::loadView('structures.pdf', compact('structures', 'typeFilter', 'sortBy', 'globalStats', 'perimetreStats'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('structures-reseau-' . now()->format('Y-m-d') . '.pdf');
    }

    private function computeGlobalStats(\Illuminate\Support\Collection $allStructures): array
    {
        $notesWithValue = $allStructures->filter(fn ($s) => $s->note_moyenne !== null);
        return [
            'nb_structures'   => $allStructures->count(),
            'nb_agents'       => $allStructures->sum('nb_agents'),
            'note_moy_reseau' => $notesWithValue->count() > 0
                ? round($notesWithValue->avg('note_moyenne'), 2)
                : null,
        ];
    }
}
