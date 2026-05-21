<?php

namespace App\Http\Controllers\Dga;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use App\Services\EvaluationService;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DgaSubEvaluationController extends Controller
{
    use ResolvesEntite;

    public function __construct(private readonly EvaluationService $evaluationService) {}

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    /**
     * Direction du DGA (celle dont il est directeur_agent_id).
     */
    private function directionDga(): ?Direction
    {
        $agentId = Auth::user()?->agent_id;
        if (! $agentId) {
            return null;
        }

        return Direction::where('directeur_agent_id', $agentId)->first();
    }

    /**
     * Retourne tous les subordonnés évaluables par le DGA :
     *   – Directeurs Techniques
     *   – Secrétaire DGA
     *   – Chefs de services de la direction DGA
     *
     * Chaque entrée contient une clé 'groupe' pour distinguer les catégories.
     */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite      = $this->getEntiteForDGA();
        $direction   = $this->directionDga();
        $subordonnes = collect();

        // ── Directeurs Techniques ─────────────────────────────────────────
        $delegations = DelegationTechnique::whereNotNull('directeur_agent_id')->get();
        foreach ($delegations as $delegation) {
            $dt = User::where('agent_id', $delegation->directeur_agent_id)
                ->where('role', 'Directeur_Technique')
                ->first();
            if (! $dt) {
                continue;
            }
            $delegationLabel = trim($delegation->region . ($delegation->ville ? ' — ' . $delegation->ville : ''));
            $subordonnes->push([
                'id'            => $dt->id,
                'agent_id'      => $dt->agent_id,
                'nom'           => $dt->name,
                'role_label'    => 'Directeur Technique',
                'entite_label'  => $delegationLabel ?: 'Délégation Technique',
                'service_label' => '',
                'groupe'        => 'Directeurs Techniques',
                'redirect_to'   => 'subordonnes',
            ]);
        }

        // ── Secrétaire DGA ────────────────────────────────────────────────
        if ($entite && $entite->dga_secretaire_agent_id) {
            $sec = User::where('agent_id', $entite->dga_secretaire_agent_id)->first();
            if ($sec) {
                $subordonnes->push([
                    'id'            => $sec->id,
                    'agent_id'      => $sec->agent_id,
                    'nom'           => $sec->name,
                    'role_label'    => 'Secrétaire DGA',
                    'entite_label'  => $entite->nom ?? '',
                    'service_label' => 'Secrétariat DGA',
                    'groupe'        => 'Secrétariat',
                    'redirect_to'   => 'subordonnes',
                ]);
            }
        }

        // ── Chefs de services de la direction DGA ─────────────────────────
        if ($direction) {
            $services = Service::where('direction_id', $direction->id)
                ->whereNotNull('chef_agent_id')
                ->with('chef')
                ->get();

            foreach ($services as $service) {
                if (! $service->chef) {
                    continue;
                }
                $chefUser = User::where('agent_id', $service->chef_agent_id)->first();
                if (! $chefUser) {
                    continue;
                }
                $subordonnes->push([
                    'id'            => $chefUser->id,
                    'agent_id'      => $chefUser->agent_id,
                    'nom'           => $chefUser->name,
                    'role_label'    => 'Chef de Service',
                    'entite_label'  => $direction->nom ?? 'Direction DGA',
                    'service_label' => $service->nom,
                    'groupe'        => 'Chefs de Services',
                    'redirect_to'   => 'direction',
                ]);
            }

            // ── Collaborateurs directs (agents direction sans service) ────
            $exclusions = array_filter([
                $direction->directeur_agent_id,
                $direction->secretaire_agent_id,
            ]);
            $agentsDirects = Agent::where('direction_id', $direction->id)
                ->whereNull('service_id')
                ->whereNotIn('id', $exclusions)
                ->get();

            foreach ($agentsDirects as $agent) {
                $agentUser = User::where('agent_id', $agent->id)->first();
                if (! $agentUser) {
                    continue;
                }
                $subordonnes->push([
                    'id'            => $agentUser->id,
                    'agent_id'      => $agentUser->agent_id,
                    'nom'           => $agentUser->name,
                    'role_label'    => 'Agent',
                    'entite_label'  => $direction->nom ?? 'Direction DGA',
                    'service_label' => $agent->role ?? '',
                    'groupe'        => 'Collaborateurs directs',
                    'redirect_to'   => 'direction',
                ]);
            }
        }

        return $subordonnes;
    }

    /**
     * Retourne les IDs de tous les subordonnés évaluables.
     */
    private function getAllowedUserIds(): array
    {
        return $this->getSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function create(Request $request): View
    {
        $this->checkDga();
        $this->authorize('evaluations.creer');

        $subordonnes        = $this->getSubordonnes();
        $preselectedId      = (int) $request->get('subordonne_id', 0);
        $selectedSubordonne = $subordonnes->firstWhere('id', $preselectedId);

        if (! $selectedSubordonne && $subordonnes->count() === 1) {
            $selectedSubordonne = $subordonnes->first();
        }

        $objectiveOptions = [];
        if ($selectedSubordonne) {
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', now()->toDateString())
                ->where('assignable_type', User::class)
                ->where('assignable_id', (int) $selectedSubordonne['id'])
                ->orderBy('titre')
                ->get();

            foreach ($fiches as $fiche) {
                $objectiveOptions[] = [
                    'id'            => $fiche->id,
                    'titre'         => $fiche->titre,
                    'date_echeance' => $fiche->date_echeance instanceof \Carbon\Carbon
                        ? $fiche->date_echeance->toDateString()
                        : (string) $fiche->date_echeance,
                    'objectifs'     => $fiche->objectifs
                        ->filter(fn ($item) => (int) ($item->avancement_percentage ?? 0) > 0)
                        ->map(fn ($item) => [
                            'source_fiche_objectif_objectif_id' => $item->id,
                            'titre'                             => $item->description,
                        ])->values()->all(),
                ];
            }
        }

        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        $oldFormations  = old('identification.formations');
        $oldExperiences = old('identification.experiences');

        $prefilledAgentId = $selectedSubordonne['agent_id'] ?? null;

        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();

        return view('dga.subordonnes.evaluations.create', compact(
            'subordonnes',
            'selectedSubordonne',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
            'prefilledAgentId',
            'openAnnee',
            'openSemestres',
            'openSemestre',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('evaluations.creer');

        $subordonnes = $this->getSubordonnes();
        $allowedIds  = $subordonnes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allowedIds  = $allowedIds ?: [0];

        $validated = $request->validate([
            'subordonne_id'                    => ['required', 'integer', 'in:'.implode(',', $allowedIds)],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.grade'             => ['required', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'            => ['nullable', 'array'],
            'identification.formations.*.periode'  => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle'  => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine'  => ['nullable', 'string', 'max:255'],
            'identification.experiences'               => ['nullable', 'array'],
            'identification.experiences.*.periode'     => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'       => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations'=> ['nullable', 'string', 'max:255'],
            'subjective_criteres'              => ['required', 'array', 'min:1'],
            'objective_criteres'               => ['required', 'array', 'min:1'],
            'points_a_ameliorer'               => ['nullable', 'string'],
            'strategies_amelioration'          => ['nullable', 'string'],
            'commentaire'                      => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom'             => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom'         => ['nullable', 'string', 'max:255'],
            'date_signature_evalue'            => ['nullable', 'date'],
            'date_signature_evaluateur'        => ['nullable', 'date'],
        ]);

        $subordonne = User::findOrFail($validated['subordonne_id']);
        $openAnnee  = Annee::currentOpen();
        if (! $openAnnee) {
            return back()->withInput()->with('error', "Aucune année d'exercice ouverte.");
        }
        $semestre = $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first();
        if (! $semestre) {
            return back()->withInput()->with('error', "Aucun semestre ouvert pour {$openAnnee->annee}.");
        }
        $dateDebut  = $semestre->dateDebut()->toDateString();
        $dateFin    = $semestre->dateFin()->toDateString();
        $anneeId    = $openAnnee->id;
        $semestreId = $semestre->id;

        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;
        $identification['matricule'] = $subordonne->agent?->matricule ?? null;
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        $normalizedSubjective = $this->evaluationService->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->evaluationService->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.',
            ]);
        }

        $scores  = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);

        $user = Auth::user();

        $evaluation = DB::transaction(function () use (
            $user, $subordonne, $dateDebut, $dateFin, $anneeId, $semestreId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => User::class,
                'evaluable_id'              => $subordonne->id,
                'evaluable_role'            => $subordonne->role,
                'annee_id'                  => $anneeId,
                'semestre_id'               => $semestreId,
                'evaluateur_id'             => $user->id,
                'date_debut'                => $dateDebut,
                'date_fin'                  => $dateFin,
                'moyenne_subjectifs'        => $scores['moyenne_subjectifs'],
                'note_criteres_subjectifs'  => $scores['note_criteres_subjectifs'],
                'moyenne_objectifs'         => $scores['moyenne_objectifs'],
                'note_criteres_objectifs'   => $scores['note_criteres_objectifs'],
                'note_finale'               => $scores['note_finale'],
                'commentaire'               => $validated['commentaire'] ?? null,
                'points_a_ameliorer'        => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration'   => $validated['strategies_amelioration'] ?? null,
                'signature_evalue_nom'      => $validated['signature_evalue_nom'] ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom'  => $validated['signature_evaluateur_nom'] ?? $user->name,
                'date_signature_evalue'     => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
                'statut'                    => 'brouillon',
            ]);

            $evaluation->identification()->create($identification);
            $this->evaluationService->persistCriteria($evaluation, array_merge($normalizedSubjective, $normalizedObjective));

            return $evaluation;
        });

        // Redirection selon le type de subordonné
        $entry       = $subordonnes->firstWhere('id', $subordonne->id);
        $redirectTo  = $entry['redirect_to'] ?? 'subordonnes';

        $redirectUrl = $redirectTo === 'direction'
            ? route('dga.direction', ['tab' => 'evaluations'])
            : route('dga.subordonnes.show', $subordonne).'?tab=evaluations';

        return redirect($redirectUrl)
            ->with('status', "Évaluation créée pour {$subordonne->name}.");
    }

    public function show(Evaluation $evaluation): View
    {
        $this->checkDga();
        $this->authorize('evaluations.voir-equipe');

        if (! in_array($evaluation->evaluable_id, $this->getAllowedUserIds(), true)) {
            abort(403);
        }

        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);

        $subordonne         = $evaluation->evaluable;
        $mention            = $this->evaluationService->mention((float) $evaluation->note_finale);
        $periodeLabel       = $this->evaluationService->periodeLabel($evaluation);
        $cibleLabel  = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $entry       = $this->getSubordonnes()->firstWhere('id', $evaluation->evaluable_id);
        $backUrl     = ($entry['redirect_to'] ?? 'subordonnes') === 'direction'
            ? route('dga.direction', ['tab' => 'evaluations'])
            : route('dga.subordonnes.show', $subordonne).'?tab=evaluations';
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        return view('dga.subordonnes.evaluations.show', compact(
            'evaluation', 'subordonne', 'mention', 'periodeLabel',
            'cibleLabel', 'backUrl', 'objectiveCriteria', 'subjectiveCriteria'
        ));
    }

    public function submit(Evaluation $evaluation): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('evaluations.soumettre');

        if (! in_array($evaluation->evaluable_id, $this->getAllowedUserIds(), true)) {
            abort(403);
        }
        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        if ($evaluation->evaluable) {
            Alerte::notifier(
                $evaluation->evaluable_id,
                'Nouvelle fiche d\'évaluation reçue',
                'Le Directeur Général Adjoint vous a soumis une fiche d\'évaluation.',
                'haute'
            );
        }

        $entry      = $this->getSubordonnes()->firstWhere('id', $evaluation->evaluable_id);
        $redirectUrl = ($entry['redirect_to'] ?? 'subordonnes') === 'direction'
            ? route('dga.direction', ['tab' => 'evaluations'])
            : route('dga.subordonnes.show', $evaluation->evaluable).'?tab=evaluations';

        return redirect($redirectUrl)->with('status', 'Évaluation soumise avec succès.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('evaluations.creer');

        if (! in_array($evaluation->evaluable_id, $this->getAllowedUserIds(), true)) {
            abort(403);
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $entry       = $this->getSubordonnes()->firstWhere('id', $evaluation->evaluable_id);
        $subordonne  = $evaluation->evaluable;
        $evaluation->delete();

        $redirectUrl = ($entry['redirect_to'] ?? 'subordonnes') === 'direction'
            ? route('dga.direction', ['tab' => 'evaluations'])
            : route('dga.subordonnes.show', $subordonne).'?tab=evaluations';

        return redirect($redirectUrl)->with('status', 'Évaluation supprimée.');
    }

    public function exportPdf(Evaluation $evaluation)
    {
        $this->checkDga();
        $this->authorize('evaluations.exporter-pdf');

        if (! in_array($evaluation->evaluable_id, $this->getAllowedUserIds(), true)) {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres', 'evaluable']);
        $evaluable          = $evaluation->evaluable;
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $this->evaluationService->mention($note);
        $cibleLabel         = $evaluation->identification->nom_prenom ?? ($evaluable?->name ?? 'Subordonné');
        $cibleType          = str_replace('_', ' ', $evaluable?->role ?? 'Subordonné');

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-dga.pdf');
    }

}
