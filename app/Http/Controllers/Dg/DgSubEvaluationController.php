<?php

namespace App\Http\Controllers\Dg;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\EvaluationService;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DgSubEvaluationController extends Controller
{
    use ResolvesEntite;

    public function __construct(private readonly EvaluationService $evaluationService) {}

    private const ALLOWED_ROLES = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];

    /** Retourne tous les subordonnés du DG connecté. */
    private function getSubordonnes(): \Illuminate\Support\Collection
    {
        $entite = $this->getEntiteForDG();
        $subordonnes = collect();

        if (!$entite) return $subordonnes;

        $entiteNom = $entite->nom ?? '';

        // Résoudre le nom de la Direction Générale Adjointe
        $dirDga = \App\Models\Direction::where('nom', 'Direction Générale Adjointe')
            ->where('entite_id', $entite->id)->first();
        $dirGen = \App\Models\Direction::where('nom', 'Direction Générale')
            ->where('entite_id', $entite->id)->first();

        if ($entite->dga_agent_id) {
            $dga = User::with(['agent.direction', 'agent.entite'])
                ->where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
            if ($dga) {
                $dgaServiceLabel = $dirDga?->nom ?? $dga->agent?->direction?->nom ?? 'Direction Générale Adjointe';
                $subordonnes->push(['id' => $dga->id, 'agent_id' => $dga->agent_id, 'nom' => $dga->name, 'role_label' => 'DGA', 'entite_label' => $entiteNom, 'service_label' => $dgaServiceLabel]);
            }
        }

        if ($entite->assistante_agent_id) {
            $assistante = User::with(['agent.direction', 'agent.entite'])
                ->where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
            if ($assistante) {
                $assServiceLabel = $dirGen?->nom ?? $assistante->agent?->direction?->nom ?? 'Direction Générale';
                $subordonnes->push(['id' => $assistante->id, 'agent_id' => $assistante->agent_id, 'nom' => $assistante->name, 'role_label' => 'Assistante', 'entite_label' => $entiteNom, 'service_label' => $assServiceLabel]);
            }
        }

        $conseillers = User::with(['agent.direction', 'agent.entite'])
            ->where('role', 'Conseillers_Dg')
            ->whereHas('agent', fn($q) => $q->where('entite_id', $entite->id))
            ->get();
        foreach ($conseillers as $c) {
            $cServiceLabel = $c->agent?->direction?->nom ?? $dirGen?->nom ?? 'Direction Générale';
            $subordonnes->push(['id' => $c->id, 'agent_id' => $c->agent_id, 'nom' => $c->name, 'role_label' => 'Conseiller', 'entite_label' => $entiteNom, 'service_label' => $cServiceLabel]);
        }

        return $subordonnes;
    }

    public function create(Request $request): View
    {
        $this->authorize('evaluations.creer');

        $subordonnes = $this->getSubordonnes();
        $preselectedId = (int) $request->get('subordonne_id', 0);
        $selectedSubordonne = $subordonnes->firstWhere('id', $preselectedId) ?: ($subordonnes->count() === 1 ? $subordonnes->first() : null);

        $objectiveOptions = $this->getObjectiveOptionsForUser($selectedSubordonne['id'] ?? null);
        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();

        return view('dg.subordonnes.evaluations.create', [
            'subordonnes' => $subordonnes,
            'selectedSubordonne' => $selectedSubordonne,
            'objectiveOptions' => $objectiveOptions,
            'subjectiveTemplates' => $subjectiveTemplates,
            'oldFormations' => old('identification.formations'),
            'oldExperiences' => old('identification.experiences'),
            'prefilledAgentId' => $selectedSubordonne['agent_id'] ?? null,
            'openAnnee'     => $openAnnee,
            'openSemestres' => $openSemestres,
            'openSemestre'  => $openSemestre,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        
        $validated = $this->validateEvaluation($request);
        $subordonne = User::findOrFail($validated['subordonne_id']);

        $evaluationData = $this->prepareEvaluationData($validated, $request);
        if ($evaluationData instanceof RedirectResponse) return $evaluationData;

        // ── Unicité : 1 évaluation par semestre ─────────────────────────────
        if ($this->evaluationService->dejaEvalueeSemestre($subordonne->id, User::class, $evaluationData['main']['semestre_id'])) {
            return back()->withInput()->with('error', "Une évaluation existe déjà pour {$subordonne->name} sur ce semestre.");
        }

        DB::transaction(function () use ($evaluationData, $validated, $subordonne) {
            $evaluation = Evaluation::create(array_merge($evaluationData['main'], [
                'evaluable_type' => User::class,
                'evaluable_id' => $subordonne->id,
                'evaluable_role' => $subordonne->role,
                'evaluateur_id' => Auth::id(),
                'statut' => 'brouillon',
            ]));

            $evaluation->identification()->create($evaluationData['identification']);
            $this->evaluationService->persistCriteria($evaluation, $evaluationData['criteria']);
        });

        return redirect($this->backUrlForSubordonne($subordonne))->with('status', "Brouillon créé pour {$subordonne->name}.");
    }

    public function edit(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.creer');

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
            return redirect($this->backUrlForSubordonne($evaluation->evaluable))->with('error', 'Seules les évaluations en brouillon sont modifiables.');
        }

        $evaluation->load(['identification', 'criteres.sousCriteres']);
        $subordonne = $evaluation->evaluable;
        $ident      = $evaluation->identification;

        $existingSubjectiveCriteria = $evaluation->criteres
            ->where('type', 'subjectif')
            ->map(fn ($c) => [
                'titre'              => $c->titre,
                'observation'        => $c->observation ?? '',
                'source_template_id' => $c->source_template_id ?? '',
                'subcriteria'        => $c->sousCriteres->map(fn ($s) => [
                    'libelle'     => $s->libelle,
                    'note'        => $s->note,
                    'observation' => $s->observation ?? '',
                ])->values()->all(),
            ])->values()->all();

        $existingObjectiveCriteria = $evaluation->criteres
            ->where('type', 'objectif')
            ->map(fn ($c) => [
                'titre'                             => $c->titre,
                'observation'                       => $c->observation ?? '',
                'source_fiche_objectif_id'          => $c->source_fiche_objectif_id ?? '',
                'source_fiche_objectif_objectif_id' => $c->source_fiche_objectif_objectif_id ?? '',
                'subcriteria'                       => $c->sousCriteres->map(fn ($s) => [
                    'libelle'     => $s->libelle,
                    'note'        => $s->note,
                    'observation' => $s->observation ?? '',
                    'source_fiche_objectif_objectif_id' => $s->source_fiche_objectif_objectif_id ?? '',
                ])->values()->all(),
            ])->values()->all();

        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();

        $formations  = $ident?->formations ?? [];
        $experiences = $ident?->experiences ?? [];

        return view('dg.subordonnes.evaluations.edit', [
            'evaluation'                => $evaluation,
            'subordonne'                => $subordonne,
            'ident'                     => $ident,
            'subordonnes'               => $this->getSubordonnes(),
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($subordonne->id),
            'subjectiveTemplates'       => $this->evaluationService->buildSubjectiveTemplates(),
            'existingSubjectiveCriteria'=> $existingSubjectiveCriteria,
            'existingObjectiveCriteria' => $existingObjectiveCriteria,
            'openAnnee'                 => $openAnnee,
            'openSemestres'             => $openSemestres,
            'formationsData'            => $formations,
            'experiencesData'           => $experiences,
        ]);
    }

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
            abort(403, 'Modification interdite pour une évaluation déjà soumise.');
        }

        $validated = $this->validateEvaluation($request, $evaluation->evaluable_id);
        $evaluationData = $this->prepareEvaluationData($validated, $request);
        
        if ($evaluationData instanceof RedirectResponse) return $evaluationData;

        DB::transaction(function () use ($evaluation, $evaluationData) {
            $evaluation->update($evaluationData['main']);
            $evaluation->identification()->update($evaluationData['identification']);
            $evaluation->criteres()->delete();
            $this->evaluationService->persistCriteria($evaluation, $evaluationData['criteria']);
        });

        return redirect($this->backUrlForSubordonne($evaluation->evaluable))->with('status', 'Brouillon mis à jour.');
    }

    // --- Helpers pour éviter la répétition ---

    private function validateEvaluation(Request $request, $targetId = null)
    {
        $allowedIds = $targetId ? [$targetId] : $this->getSubordonnes()->pluck('id')->all();
        
        return $request->validate([
            'subordonne_id' => ['required', 'integer', 'in:'.implode(',', $allowedIds)],
            'identification.date_evaluation' => ['nullable', 'string'],
            'identification.grade' => ['nullable', 'string', 'max:255'],
            'identification.formations' => ['nullable', 'array'],
            'identification.experiences' => ['nullable', 'array'],
            'subjective_criteres' => ['required', 'array', 'min:1'],
            'objective_criteres' => ['required', 'array', 'min:1'],
            'commentaire' => ['nullable', 'string', 'max:2000'],
            'points_a_ameliorer' => ['nullable', 'string'],
            'strategies_amelioration' => ['nullable', 'string'],
        ]);
    }

    private function prepareEvaluationData($validated, $request)
    {
        $openAnnee = Annee::currentOpen();
        if (! $openAnnee) {
            return back()->withInput()->with('error', "Aucune année d'exercice ouverte.");
        }
        $semestre = $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first();
        if (! $semestre) {
            return back()->withInput()->with('error', "Aucun semestre ouvert pour {$openAnnee->annee}.");
        }

        $normSub = $this->evaluationService->normalizeCriteria((array)$request->input('subjective_criteres'), 'subjectif', 1, 5, false);
        $normObj = $this->evaluationService->normalizeCriteria((array)$request->input('objective_criteres'), 'objectif', 1, 5);
        $scores  = $this->evaluationService->computeScores($normSub, $normObj);

        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;

        // Inject identification fields from the subordonne user's agent
        $subordonneUser = User::with(['agent.entite', 'agent.direction', 'agent.delegationTechnique', 'agent.caisse', 'agent.agence', 'agent.service'])->find($validated['subordonne_id']);
        $agent = $subordonneUser?->agent;
        $identification['matricule']        = $agent?->matricule ?? null;
        $identification['nom_prenom']       = $agent ? trim($agent->prenom . ' ' . $agent->nom) : $subordonneUser?->name;
        $identification['emploi']           = $agent?->poste ?: $agent?->role;
        if ($agent) {
            $structLabels = \App\Helpers\AgentStructure::labels($agent);
            $identification['direction']         = $structLabels['entite_nom'];
            $identification['direction_service'] = $structLabels['direction_service'];
        } else {
            $identification['direction']         = null;
            $identification['direction_service'] = null;
        }

        // Normalize date_evaluation from d/m/Y → Y-m-d
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        return [
            'main' => [
                'date_debut'               => $semestre->dateDebut()->toDateString(),
                'date_fin'                 => $semestre->dateFin()->toDateString(),
                'annee_id'                 => $openAnnee->id,
                'semestre_id'              => $semestre->id,
                'moyenne_subjectifs'       => $scores['moyenne_subjectifs'],
                'moyenne_objectifs'        => $scores['moyenne_objectifs'],
                'note_criteres_subjectifs' => $scores['note_criteres_subjectifs'],
                'note_criteres_objectifs'  => $scores['note_criteres_objectifs'],
                'note_finale'              => $scores['note_finale'],
                'commentaire'              => $validated['commentaire'] ?? null,
                'points_a_ameliorer'       => $validated['points_a_ameliorer'] ?? null,
                'strategies_amelioration'  => $validated['strategies_amelioration'] ?? null,
            ],
            'identification' => $identification,
            'criteria'       => array_merge($normSub, $normObj),
        ];
    }

    private function getObjectiveOptionsForUser($userId)
    {
        if (!$userId) return [];
        return FicheObjectif::with('objectifs')->where('statut', 'acceptee')
            ->where('assignable_id', (int) $userId)
            ->get()->map(fn($f) => [
                'id' => $f->id, 'titre' => $f->titre,
                'objectifs' => $f->objectifs
                    ->filter(fn($o) => (int) ($o->avancement_percentage ?? 0) > 0)
                    ->map(fn($o) => ['source_fiche_objectif_objectif_id' => $o->id, 'titre' => $o->description])
                    ->values()
            ])->all();
    }

    // --- Méthodes existantes (Show, Submit, Destroy, etc.) ---

    public function show(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.voir-equipe');
        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);
        
        return view('dg.subordonnes.evaluations.show', [
            'evaluation' => $evaluation,
            'subordonne' => $evaluation->evaluable,
            'mention' => $this->evaluationService->mention((float) $evaluation->note_finale),
            'periodeLabel' => $this->evaluationService->periodeLabel($evaluation),
            'cibleLabel' => $this->cibleLabel($evaluation),
            'backUrl' => $this->backUrlForSubordonne($evaluation->evaluable),
            'objectiveCriteria' => $evaluation->criteres->where('type', 'objectif')->values(),
            'subjectiveCriteria' => $evaluation->criteres->where('type', 'subjectif')->values()
        ]);
    }

    public function submit(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) return back()->with('error', 'Déjà soumis.');

        $evaluation->update(['statut' => 'soumis']);
        Alerte::notifier($evaluation->evaluable_id, 'Évaluation reçue', 'Le DG vous a soumis votre fiche.', 'haute');

        return redirect($this->backUrlForSubordonne($evaluation->evaluable))->with('status', 'Soumis au subordonné.');
    }

    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        if ($evaluation->statut === 'valide') return back()->with('error', 'Impossible de supprimer une fiche validée.');

        $sub = $evaluation->evaluable;
        $evaluation->delete();
        return redirect($this->backUrlForSubordonne($sub))->with('status', 'Supprimé.');
    }

    private function backUrlForSubordonne(User $subordonne): string
    {
        return match ($subordonne->role) {
            'DGA' => route('dg.dga').'?tab=evaluations',
            'Assistante_Dg' => route('dg.assistante').'?tab=evaluations',
            default => route('dg.conseillers.show', $subordonne).'?tab=evaluations',
        };
    }

    private function cibleLabel(Evaluation $evaluation): string
    {
        return $evaluation->identification?->nom_prenom ?: ($evaluation->evaluable?->name ?? '-');
    }
}