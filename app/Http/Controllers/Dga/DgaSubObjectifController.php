<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\DelegationTechnique;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Traits\ResolvesEntite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DgaSubObjectifController extends Controller
{
    use ResolvesEntite;

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /** Retourne tous les subordonnés du DGA connecté (DTs + secrétaire). */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDGA();
        $subordonnes = collect();

        if (! $entite) {
            return $subordonnes;
        }

        // Directeurs Techniques
        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')
            ->pluck('directeur_agent_id');

        $dts = User::where('role', 'Directeur_Technique')
            ->whereIn('agent_id', $dtAgentIds)
            ->with('agent.directedDelegation')
            ->get();

        foreach ($dts as $dt) {
            $subordonnes->push([
                'id'         => $dt->id,
                'nom'        => $dt->name,
                'role_label' => 'Directeur Technique'.($dt->agent?->directedDelegation ? ' — '.$dt->agent->directedDelegation->region : ''),
            ]);
        }

        // Secrétaire du DGA
        $secretaire = $this->getDgaSecretaireUser($entite);
        if ($secretaire) {
            $subordonnes->push([
                'id'         => $secretaire->id,
                'nom'        => $secretaire->name,
                'role_label' => 'Secrétaire DGA',
            ]);
        }

        return $subordonnes;
    }

    public function create(Request $request): View
    {
        $this->checkDga();


        $subordonnes = $this->getSubordonnes()->values();
        $requestedId = (int) $request->integer('subordonne_id');
        $selectedSubordonne = $subordonnes->firstWhere('id', $requestedId);

        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        return view('dga.subordonnes.objectifs.create', [
            'subordonnes'        => $subordonnes,
            'selectedSubordonne' => $selectedSubordonne,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkDga();


        $subordonnes       = $this->getSubordonnes()->values();
        $allowedIds        = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        if (blank($request->input('subordonne_id')) && count($allowedIds) === 1) {
            $request->merge(['subordonne_id' => $allowedIds[0]]);
        }

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer', Rule::in($allowedIds)],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'annee_id'              => Annee::resolveIdForDate(now()),
            'assignable_type'       => User::class,
            'assignable_id'         => $validated['subordonne_id'],
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        $subordonne = User::findOrFail($validated['subordonne_id']);

        Alerte::notifier(
            $subordonne->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le Directeur Général Adjoint vous a assigné une fiche d'objectifs « {$fiche->titre} ». Connectez-vous pour l'examiner.",
            'haute'
        );

        return redirect(route('dga.subordonnes.show', $subordonne).'?tab=objectifs')
            ->with('status', "Fiche d'objectifs assignée avec succès à {$subordonne->name}.");
    }

    public function show(FicheObjectif $fiche): View
    {
        $this->checkDga();

        // Vérifier que la fiche appartient à un subordonné du DGA
        $subordonnes = $this->getSubordonnes();
        $allowedIds  = $subordonnes->pluck('id')->all();

        if (
            $fiche->assignable_type !== User::class ||
            ! in_array((int) $fiche->assignable_id, $allowedIds, true)
        ) {
            abort(403);
        }

        $fiche->load(['objectifs', 'assignable']);
        $subordonne = $fiche->assignable;

        return view('dga.subordonnes.objectifs.show', compact('fiche', 'subordonne'));
    }

    public function avancement(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->checkDga();


        $subordonnes = $this->getSubordonnes();
        $allowedIds  = $subordonnes->pluck('id')->all();

        if (
            $fiche->assignable_type !== User::class ||
            ! in_array((int) $fiche->assignable_id, $allowedIds, true)
        ) {
            abort(403);
        }

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (((int) $request->avancement_percentage) % 5 !== 0) {
            return back()->with('error', "L'avancement doit être un multiple de 5.");
        }

        $fiche->avancement_percentage = $request->avancement_percentage;
        $fiche->save();

        return redirect()->route('dga.sub-objectifs.show', $fiche)
            ->with('status', 'Avancement mis à jour.');
    }

    public function destroy(FicheObjectif $fiche): RedirectResponse
    {
        $this->checkDga();

        $subordonnes = $this->getSubordonnes();
        $allowedIds  = $subordonnes->pluck('id')->all();

        if (
            $fiche->assignable_type !== User::class ||
            ! in_array((int) $fiche->assignable_id, $allowedIds, true)
        ) {
            abort(403);
        }

        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Une fiche acceptée ne peut pas être supprimée.');
        }

        $subordonne = User::find($fiche->assignable_id);
        $fiche->delete();

        $redirectUrl = $subordonne
            ? route('dga.subordonnes.show', $subordonne).'?tab=objectifs'
            : route('dga.subordonnes.index');

        return redirect($redirectUrl)->with('status', "Fiche d'objectifs supprimée.");
    }
}
