<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\DelegationTechnique;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\ObjectifService;
use App\Traits\ResolvesEntite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DgaSubObjectifController extends Controller
{
    use ResolvesEntite;

    public function __construct(private readonly ObjectifService $objectifService) {}

    /**
     * Vérifie si l'utilisateur est bien un DGA.
     */
    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /**
     * Retourne tous les subordonnés du DGA connecté (DTs + secrétaire).
     */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite = $this->getEntiteForDGA();
        $subordonnes = collect();

        if (!$entite) {
            return $subordonnes;
        }

        // Directeurs Techniques : on récupère les utilisateurs ayant le rôle DT
        $dts = User::where('role', 'Directeur_Technique')
            ->with('agent.directedDelegation')
            ->get();

        foreach ($dts as $dt) {
            $subordonnes->push([
                'id'         => $dt->id,
                'nom'        => $dt->name,
                'role_label' => 'Directeur Technique' . ($dt->agent?->directedDelegation ? ' — ' . $dt->agent->directedDelegation->region : ''),
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

    /**
     * Affiche le formulaire de création de fiche d'objectifs.
     */
    public function create(Request $request): View
    {
        $this->checkDga();
        $this->authorize('objectifs.assigner');

        $subordonnes = $this->getSubordonnes()->values();
        $requestedId = (int) $request->integer('subordonne_id');
        $selectedSubordonne = $subordonnes->firstWhere('id', $requestedId);

        if (!$selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        return view('dga.subordonnes.objectifs.create', [
            'subordonnes'        => $subordonnes,
            'selectedSubordonne' => $selectedSubordonne,
        ]);
    }

    /**
     * Enregistre la fiche d'objectifs et redirige (Méthode POST).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('objectifs.assigner');

        $subordonnes = $this->getSubordonnes()->values();
        $allowedIds = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer', Rule::in($allowedIds)],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $subordonne = User::findOrFail($validated['subordonne_id']);

        // ASSIGNATION STRICTE A LA PERSONNE (User)
        try {
            $anneeId = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $subordonne->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        Alerte::notifier(
            $subordonne->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le DGA vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
            'haute'
        );

        // RÉPARATION DE LA REDIRECTION : On redirige vers le profil du subordonné (Route GET)
        return redirect()->route('dga.subordonnes.show', $subordonne->id)
            ->with('status', "Fiche d'objectifs assignée avec succès.");
    }

    /**
     * Affiche une fiche d'objectifs spécifique.
     */
    public function show(FicheObjectif $fiche): View
    {
        $this->checkDga();
        $this->authorize('objectifs.voir-equipe');

        $fiche->load(['objectifs', 'assignable']);
        
        // Résolution du subordonné pour l'affichage (cas DT ou User)
        $subordonne = $fiche->assignable_type === User::class 
            ? $fiche->assignable 
            : User::where('agent_id', $fiche->assignable->directeur_agent_id)->first();

        return view('dga.subordonnes.objectifs.show', compact('fiche', 'subordonne'));
    }

    /**
     * Supprime une fiche d'objectifs.
     */
    public function destroy(FicheObjectif $fiche): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('objectifs.assigner');

        if ($fiche->statut === 'acceptee') {
            return back()->with('error', 'Impossible de supprimer une fiche acceptée.');
        }

        // Récupération de l'ID pour la redirection avant suppression
        $subordonneId = $fiche->assignable_type === User::class 
            ? $fiche->assignable_id 
            : User::where('agent_id', $fiche->assignable->directeur_agent_id)->value('id');

        $fiche->delete();

        return redirect()->route('dga.subordonnes.show', $subordonneId)
            ->with('status', "Fiche d'objectifs supprimée.");
    }
}