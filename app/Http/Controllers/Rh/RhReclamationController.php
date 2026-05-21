<?php

namespace App\Http\Controllers\Rh;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RhReclamationController extends Controller
{
    public function index(): View
    {
        $evaluations = Evaluation::query()
            ->where('statut', 'refuse')
            ->with(['evaluable', 'evaluateur', 'identification'])
            ->latest()
            ->get();

        return view('rh.reclamations.index', compact('evaluations'));
    }

    public function repondre(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $request->validate([
            'reponse' => ['required', 'in:maintenu,rouvert'],
        ]);

        $evaluation->statut_reclamation = $request->input('reponse');

        if ($request->input('reponse') === 'rouvert') {
            $evaluation->statut      = 'brouillon';
            $evaluation->motif_refus = null;
        }

        $evaluation->save();

        $msg = $request->input('reponse') === 'maintenu'
            ? 'Le refus a été maintenu.'
            : 'L\'évaluation a été rouverte pour correction.';

        return redirect()->route('rh.reclamations.index')->with('status', $msg);
    }
}
