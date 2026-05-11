<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Entite;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\ObjectifService;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DgObjectifController extends Controller
{
    use ResolvesEntite;

    public function __construct(private readonly ObjectifService $objectifService) {}

    /** Retourne tous les subordonnés du DG connecté. */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDG();
        $subordonnes = collect();

        if (! $entite) {
            return $subordonnes;
        }

        if ($entite->dga_agent_id) {
            $dga = User::where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
            if ($dga) {
                $subordonnes->push(['id' => $dga->id, 'nom' => $dga->name, 'role_label' => 'DGA']);
            }
        }

        if ($entite->assistante_agent_id) {
            $assistante = User::where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
            if ($assistante) {
                $subordonnes->push(['id' => $assistante->id, 'nom' => $assistante->name, 'role_label' => 'Assistante']);
            }
        }

        $conseillers = User::where('role', 'Conseillers_Dg')->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))->get();
        foreach ($conseillers as $c) {
            $subordonnes->push(['id' => $c->id, 'nom' => $c->name, 'role_label' => 'Conseiller']);
        }

        return $subordonnes;
    }

    public function create(Request $request): View
    {
        $this->authorize('objectifs.assigner');

        $subordonnes = $this->getSubordonnes()->values();
        $requestedSubordonneId = (int) $request->integer('subordonne_id');
        $selectedSubordonne = $subordonnes->firstWhere('id', $requestedSubordonneId);

        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        return view('dg.objectifs.create', [
            'subordonnes' => $subordonnes,
            'selectedSubordonne' => $selectedSubordonne,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('objectifs.assigner');

        $subordonnes = $this->getSubordonnes()->values();
        $allowedSubordonneIds = $subordonnes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (blank($request->input('subordonne_id')) && count($allowedSubordonneIds) === 1) {
            $request->merge(['subordonne_id' => $allowedSubordonneIds[0]]);
        }

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'subordonne_id' => ['required', 'integer', Rule::in($allowedSubordonneIds)],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

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

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        // Notifier le subordonné assigné
        $subordonne = User::findOrFail($validated['subordonne_id']);
        Alerte::notifier(
            $subordonne->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le Directeur Général vous a assigné une fiche d'objectifs « {$fiche->titre} ». Connectez-vous pour l'examiner.",
            'haute'
        );

        // Redirection vers la page du subordonné dans l'interface DG
        $redirect   = match ($subordonne->role) {
            'DGA'          => route('dg.dga').'?tab=objectifs',
            'Assistante_Dg'=> route('dg.assistante').'?tab=objectifs',
            default        => route('dg.conseillers.show', $subordonne).'?tab=objectifs',
        };

        return redirect($redirect)->with('status', "Fiche d'objectifs assignée avec succès à {$subordonne->name}.");
    }

    public function show($fiche): View
    {
        $this->authorize('objectifs.voir-equipe');
        $fiche = FicheObjectif::with('objectifs')->findOrFail($fiche);

        return view('dg.objectifs.show', compact('fiche'));
    }

    public function statut(Request $request, $fiche): RedirectResponse
    {
        $this->authorize('objectifs.accepter');
        $fiche = FicheObjectif::findOrFail($fiche);

        $request->validate([
            'statut' => ['required', 'in:acceptee,refusee'],
        ]);

        $fiche->statut = $request->statut;
        $fiche->save();

        return redirect()
            ->route('dg.objectifs.show', $fiche)
            ->with('status', 'Statut mis a jour.');
    }
    public function avancement(Request $request, $fiche): RedirectResponse
    {
        $this->authorize('objectifs.avancement');
        $fiche = FicheObjectif::findOrFail($fiche);

        $this->objectifService->assertUserOwns($fiche, Auth::id());

        $request->validate([
            'avancement_percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (((int) $request->avancement_percentage) % 5 !== 0) {
            return redirect()
                ->route('dg.objectifs.show', $fiche)
                ->with('status', "L'avancement doit être un multiple de 5.");
        }

        $this->objectifService->updateAvancement($fiche, (int) $request->avancement_percentage);

        return redirect()->route('dg.objectifs.show', $fiche)->with('status', 'Avancement mis à jour.');
    }

    public function exportPdf($ficheId)
    {
        $this->authorize('objectifs.voir-equipe');
        $user = Auth::user();

        $fiche      = FicheObjectif::with('objectifs', 'assignable')->findOrFail($ficheId);
        $assignable = $fiche->assignable;
        $entite     = $this->getEntiteForDG();

        $roleLabels = [
            'DGA'            => 'Directeur General Adjoint',
            'Assistante_Dg'  => 'Assistante DG',
            'Conseillers_Dg' => 'Conseiller DG',
        ];

        $nom = strtolower(trim((string) ($entite?->nom ?? '')));
        $institutionSigle = ($nom !== '' && (str_contains($nom, 'faitiere') || str_contains($nom, 'fcpb'))) ? 'FCPB' : 'RCPB';

        $pdf = Pdf::loadView('pdf.contrat-objectif', [
            'contrat'                => $fiche,
            'partieCollaborateur'    => (object) [
                'name' => $assignable?->name ?? '-',
                'role' => $roleLabels[$assignable?->role ?? ''] ?? ($assignable?->role ?? '-'),
            ],
            'partieFaitiere'         => $entite,
            'partieFaitiereNomComplet' => $user->name,
            'partieFaitiereRole'     => 'Directeur General',
            'objectifs'              => $fiche->objectifs,
            'dateDebut'              => $fiche->date,
            'dateFin'                => $fiche->date_echeance,
            'institution_sigle'      => $institutionSigle,
        ]);

        return $pdf->download('contrat-objectifs-'.$fiche->id.'.pdf');
    }

    public function destroy($fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');

        $fiche = FicheObjectif::findOrFail($fiche);
        $fiche->delete();

        return redirect()->route('dg.mon-espace')->with('status', "Fiche d'objectifs supprimée avec succès.");
    }
}
