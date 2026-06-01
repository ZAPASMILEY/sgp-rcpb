<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Guichet;
use App\Models\Service;
use App\Models\SubjectiveCriteriaTemplate;
use App\Models\User;
use App\Helpers\AgentStructure;
use App\Services\EvaluationService;
use App\Traits\ResolvesEntite;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    use ResolvesEntite;

    private const ALLOWED_ROLES_DGA = ['DGA', 'Assistante_Dg', 'Conseillers_Dg'];
    private const ROLE_LABELS_DGA = [
        'DGA'           => 'Directeur Général Adjoint',
        'Assistante_Dg' => 'Assistante DG',
        'Conseillers_Dg'=> 'Conseiller DG',
    ];

    public function __construct(private readonly EvaluationService $evaluationService) {}

    // =========================================================================
    // PUBLIC DISPATCH — show
    // =========================================================================

    public function show(Request $request, Evaluation $evaluation): View
    {
        return match (true) {
            $request->routeIs('pca.evaluations.show')                               => $this->pcaShow($evaluation),
            $request->routeIs('dg.evaluations.show')                                => $this->dgReceivedShow($evaluation),
            $request->routeIs('dg.sub-evaluations.show')                            => $this->dgSubShow($evaluation),
            $request->routeIs('dg.directions.evaluations.show')                     => $this->dgDirectionShow($evaluation),
            $request->routeIs('dga.evaluations.show', 'subordonne.evaluations.show')=> $this->dgaReceivedShow($evaluation),
            $request->routeIs('dga.sub-evaluations.show')                           => $this->dgaSubShow($evaluation),
            $request->routeIs('dga.notes-reseau.show')                              => $this->dgaNotesReseauShow($evaluation),
            $request->routeIs('directeur.evaluations.show')                         => $this->directeurShow($evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.show')  => $this->directeurSecretaireShow($evaluation),
            $request->routeIs('chef.evaluations.show')                              => $this->chefShow($evaluation),
            $request->routeIs('personnel.evaluations.show')                         => $this->personnelShow($evaluation),
            $request->routeIs('rh.evaluations.show')                                => $this->rhShow($evaluation),
            $request->routeIs('assistante.secretaire.evaluations.show')             => $this->assistanteShow($evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — index
    // =========================================================================

    public function index(Request $request): View
    {
        return match (true) {
            $request->routeIs('pca.evaluations.index') => $this->pcaIndex($request),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — create / createForDirection / createForGuichet
    // =========================================================================

    public function create(Request $request): View|RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.create')          => $this->pcaCreate($request),
            $request->routeIs('dg.sub-evaluations.create')       => $this->dgSubCreate($request),
            $request->routeIs('dga.sub-evaluations.create')      => $this->dgaSubCreate($request),
            $request->routeIs('directeur.evaluations.create')    => $this->directeurCreate($request),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.create') => $this->directeurSecretaireCreate($request),
            $request->routeIs('chef.evaluations.create')         => $this->chefCreate($request),
            $request->routeIs('assistante.secretaire.evaluations.create') => $this->assistanteCreate($request),
            default => abort(404),
        };
    }

    public function createForDirection(Request $request, Direction $direction): View
    {
        return $this->dgDirectionCreate($request, $direction);
    }

    public function createForGuichet(Guichet $guichet): View
    {
        return $this->chefCreateForGuichet($guichet);
    }

    // =========================================================================
    // PUBLIC DISPATCH — store
    // =========================================================================

    public function store(Request $request): RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.store')           => $this->pcaStore($request),
            $request->routeIs('dg.sub-evaluations.store')        => $this->dgSubStore($request),
            $request->routeIs('dg.directions.evaluations.store') => $this->dgDirectionStore($request),
            $request->routeIs('dga.sub-evaluations.store')       => $this->dgaSubStore($request),
            $request->routeIs('directeur.evaluations.store')     => $this->directeurStore($request),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.store') => $this->directeurSecretaireStore($request),
            $request->routeIs('chef.evaluations.store')          => $this->chefStore($request),
            $request->routeIs('chef.subordonnes.guichet.evaluations.store') => $this->chefStoreForGuichet($request),
            $request->routeIs('assistante.secretaire.evaluations.store')    => $this->assistanteStore($request),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — edit
    // =========================================================================

    public function edit(Request $request, Evaluation $evaluation): View|RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.edit')                              => $this->pcaEdit($evaluation),
            $request->routeIs('dg.sub-evaluations.edit')                           => $this->dgSubEdit($evaluation),
            $request->routeIs('dg.directions.evaluations.edit')                    => $this->dgDirectionEdit($evaluation),
            $request->routeIs('dga.sub-evaluations.edit')                          => $this->dgaSubEdit($evaluation),
            $request->routeIs('directeur.evaluations.edit')                        => $this->directeurEdit($evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.edit') => $this->directeurSecretaireEdit($evaluation),
            $request->routeIs('chef.evaluations.edit')                             => $this->chefEdit($evaluation),
            $request->routeIs('assistante.secretaire.evaluations.edit')            => $this->assistanteEdit($evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — update
    // =========================================================================

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.update')                              => $this->pcaUpdate($request, $evaluation),
            $request->routeIs('dg.sub-evaluations.update')                           => $this->dgSubUpdate($request, $evaluation),
            $request->routeIs('dg.directions.evaluations.update')                    => $this->dgDirectionUpdate($request, $evaluation),
            $request->routeIs('dga.sub-evaluations.update')                          => $this->dgaSubUpdate($request, $evaluation),
            $request->routeIs('directeur.evaluations.update')                        => $this->directeurUpdate($request, $evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.update') => $this->directeurSecretaireUpdate($request, $evaluation),
            $request->routeIs('chef.evaluations.update')                             => $this->chefUpdate($request, $evaluation),
            $request->routeIs('assistante.secretaire.evaluations.update')            => $this->assistanteUpdate($request, $evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — submit
    // =========================================================================

    public function submit(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.submit')          => $this->pcaSubmit($evaluation),
            $request->routeIs('dg.sub-evaluations.submit')       => $this->dgSubSubmit($evaluation),
            $request->routeIs('dg.directions.evaluations.submit')=> $this->dgDirectionSubmit($evaluation),
            $request->routeIs('dga.sub-evaluations.submit')      => $this->dgaSubSubmit($evaluation),
            $request->routeIs('directeur.evaluations.submit')    => $this->directeurSubmit($evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.submit') => $this->directeurSecretaireSubmit($evaluation),
            $request->routeIs('chef.evaluations.submit')         => $this->chefSubmit($evaluation),
            $request->routeIs('assistante.secretaire.evaluations.submit')            => $this->assistanteSubmit($evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — approve (PCA only)
    // =========================================================================

    public function approve(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->pcaApprove($request, $evaluation);
    }

    // =========================================================================
    // PUBLIC DISPATCH — statut
    // =========================================================================

    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return match (true) {
            $request->routeIs('dg.evaluations.statut')           => $this->dgReceivedStatut($request, $evaluation),
            $request->routeIs('dga.evaluations.statut', 'subordonne.evaluations.statut') => $this->dgaReceivedStatut($request, $evaluation),
            $request->routeIs('directeur.evaluations.statut')    => $this->directeurStatut($request, $evaluation),
            $request->routeIs('chef.evaluations.statut')         => $this->chefStatut($request, $evaluation),
            $request->routeIs('personnel.evaluations.statut')    => $this->personnelStatut($request, $evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — reclamer
    // =========================================================================

    public function reclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return match (true) {
            $request->routeIs('dg.evaluations.reclamer')           => $this->dgReceivedReclamer($request, $evaluation),
            $request->routeIs('dga.evaluations.reclamer', 'subordonne.evaluations.reclamer') => $this->dgaReceivedReclamer($request, $evaluation),
            $request->routeIs('directeur.evaluations.reclamer')    => $this->directeurReclamer($request, $evaluation),
            $request->routeIs('chef.evaluations.reclamer')         => $this->chefReclamer($request, $evaluation),
            $request->routeIs('personnel.evaluations.reclamer')    => $this->personnelReclamer($request, $evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — commentaire
    // =========================================================================

    public function commentaire(Request $request, Evaluation $evaluation): mixed
    {
        return match (true) {
            $request->routeIs('dg.evaluations.commentaire')                       => $this->dgReceivedCommentaire($request, $evaluation),
            $request->routeIs('dga.evaluations.commentaire', 'subordonne.evaluations.commentaire') => $this->dgaReceivedCommentaire($request, $evaluation),
            $request->routeIs('directeur.evaluations.commentaire')              => $this->directeurCommentaire($request, $evaluation),
            $request->routeIs('chef.evaluations.commentaire')                   => $this->chefCommentaire($request, $evaluation),
            $request->routeIs('personnel.evaluations.commentaire')              => $this->personnelCommentaire($request, $evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — exportPdf
    // =========================================================================

    public function exportPdf(Request $request, Evaluation $evaluation): Response
    {
        return match (true) {
            $request->routeIs('pca.evaluations.pdf')               => $this->pcaExportPdf($evaluation),
            $request->routeIs('dg.evaluations.pdf')                => $this->dgReceivedExportPdf($evaluation),
            $request->routeIs('dg.sub-evaluations.pdf')            => $this->dgSubExportPdf($evaluation),
            $request->routeIs('dg.directions.evaluations.pdf')     => $this->dgDirectionExportPdf($evaluation),
            $request->routeIs('dga.evaluations.pdf', 'subordonne.evaluations.pdf') => $this->dgaReceivedExportPdf($evaluation),
            $request->routeIs('dga.sub-evaluations.pdf')           => $this->dgaSubExportPdf($evaluation),
            $request->routeIs('directeur.evaluations.pdf')         => $this->directeurExportPdf($evaluation),
            $request->routeIs('chef.evaluations.pdf')              => $this->chefExportPdf($evaluation),
            $request->routeIs('personnel.evaluations.pdf')         => $this->personnelExportPdf($evaluation),
            $request->routeIs('assistante.secretaire.evaluations.pdf')           => $this->assistanteExportPdf($evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.pdf') => $this->directeurSecretaireExportPdf($evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // PUBLIC DISPATCH — destroy
    // =========================================================================

    public function destroy(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return match (true) {
            $request->routeIs('pca.evaluations.destroy')           => $this->pcaDestroy($evaluation),
            $request->routeIs('dg.sub-evaluations.destroy')        => $this->dgSubDestroy($evaluation),
            $request->routeIs('dg.directions.evaluations.destroy') => $this->dgDirectionDestroy($evaluation),
            $request->routeIs('dga.sub-evaluations.destroy')       => $this->dgaSubDestroy($evaluation),
            $request->routeIs('directeur.evaluations.destroy')     => $this->directeurDestroy($evaluation),
            $request->routeIs('directeur.subordonnes.secretaire.evaluations.destroy') => $this->directeurSecretaireDestroy($evaluation),
            $request->routeIs('chef.evaluations.destroy')          => $this->chefDestroy($evaluation),
            $request->routeIs('assistante.secretaire.evaluations.destroy')           => $this->assistanteDestroy($evaluation),
            default => abort(404),
        };
    }

    // =========================================================================
    // SHARED HELPERS
    // =========================================================================

    private function resolveEvaluationView(Evaluation $evaluation, array $extra = []): View
    {
        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);
        $note                = (float) ($evaluation->note_finale ?? 0);
        $mention             = $this->evaluationService->mention($note);
        $objectiveCriteria   = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria  = $evaluation->criteres->where('type', 'subjectif')->values();
        $subjectiveTemplates = $subjectiveCriteria->isEmpty()
            ? SubjectiveCriteriaTemplate::with('subcriteria')->where('is_active', true)->orderBy('ordre')->get()
            : collect();

        $statusClass = match ($evaluation->statut) {
            'brouillon'   => 'bg-gray-100 text-gray-700',
            'soumis'      => 'bg-blue-100 text-blue-700',
            'accepte'     => 'bg-green-100 text-green-700',
            'valide'      => 'bg-green-100 text-green-700',
            'reclamation' => 'bg-orange-100 text-orange-700',
            'a_reviser'   => 'bg-yellow-100 text-yellow-700',
            'finalise'    => 'bg-purple-100 text-purple-700',
            default       => 'bg-gray-100 text-gray-600',
        };
        $statusLabel = match ($evaluation->statut) {
            'brouillon'   => 'Brouillon',
            'soumis'      => 'Soumis',
            'accepte'     => 'Accepté',
            'valide'      => 'Validé',
            'reclamation' => 'Réclamation',
            'a_reviser'   => 'À réviser',
            'finalise'    => 'Finalisé',
            default       => ucfirst($evaluation->statut ?? ''),
        };

        return view('evaluations.show', array_merge([
            'evaluation'          => $evaluation,
            'objectiveCriteria'   => $objectiveCriteria,
            'subjectiveCriteria'  => $subjectiveCriteria,
            'note'                => $note,
            'mention'             => $mention,
            'ident'               => $evaluation->identification,
            'statusLabel'         => $statusLabel,
            'statusClass'         => $statusClass,
            'subjectiveTemplates' => $subjectiveTemplates,
        ], $extra));
    }

    private function pdfResponse(Evaluation $evaluation, string $viewName, string $filename, string $cibleType = '', string $cibleLabel = ''): Response
    {
        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);
        $note    = (float) ($evaluation->note_finale ?? 0);
        $mention = $this->evaluationService->mention($note);

        if ($cibleLabel === '') {
            $target     = $evaluation->evaluable;
            $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? ''))
                ?: ($target?->name ?? ($target?->nom ?? '-'));
        }
        if ($cibleType === '') {
            $cibleType = match (true) {
                $evaluation->evaluable_role === 'DG'         => 'Directeur Général',
                $evaluation->evaluable_role === 'DGA'        => 'Directeur Général Adjoint',
                $evaluation->evaluable_role === 'secretaire' => 'Secrétaire',
                $evaluation->evaluable_role === 'manager'    => 'Manager',
                default => str_replace('_', ' ', $evaluation->evaluable_role ?? 'Agent'),
            };
        }

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->sortBy('ordre')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->sortBy('ordre')->values();

        $pdf = Pdf::loadView($viewName, compact(
            'evaluation', 'note', 'mention',
            'cibleType', 'cibleLabel',
            'objectiveCriteria', 'subjectiveCriteria'
        ));
        return $pdf->download($filename);
    }

    /**
     * Retourne la fiche d'objectifs active d'un agent (une seule, la plus récente avec avancement > 0).
     * Cherche d'abord par Agent::class (fiches créées par le Chef), puis par User::class.
     */
    private function getObjectiveOptionsForAgent(int $agentId): array
    {
        // Chercher par Agent::class (Chef crée la fiche directement pour l'agent)
        $fiche = FicheObjectif::with('objectifs')
            ->where('assignable_type', \App\Models\Agent::class)
            ->where('assignable_id', $agentId)
            ->whereIn('statut', ['en_attente', 'acceptee'])
            ->where('avancement_percentage', '>', 0)
            ->orderByDesc('created_at')
            ->first();

        // Fallback : fiche assignée au compte User de l'agent
        if (! $fiche) {
            $userId = \App\Models\User::where('agent_id', $agentId)->value('id');
            if ($userId) {
                $fiche = FicheObjectif::with('objectifs')
                    ->where('assignable_type', \App\Models\User::class)
                    ->where('assignable_id', $userId)
                    ->whereIn('statut', ['en_attente', 'acceptee'])
                    ->where('avancement_percentage', '>', 0)
                    ->orderByDesc('created_at')
                    ->first();
            }
        }

        if (! $fiche) return [];

        return [[
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
        ]];
    }

    /**
     * Retourne la fiche d'objectifs active d'un utilisateur (User::class uniquement).
     * Utilisé pour DG, DGA, Directeur, Secrétaire, Chef de Guichet.
     */
    private function getObjectiveOptionsForUser(int $userId): array
    {
        $fiche = FicheObjectif::with('objectifs')
            ->where('assignable_type', \App\Models\User::class)
            ->where('assignable_id', $userId)
            ->whereIn('statut', ['en_attente', 'acceptee'])
            ->where('avancement_percentage', '>', 0)
            ->orderByDesc('created_at')
            ->first();

        if (! $fiche) return [];

        return [[
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
        ]];
    }

    /**
     * Retourne les options de fiche d'objectifs pour une entité structurelle (Service, Agence, Caisse).
     * La fiche est toujours assignée au compte User du chef/directeur de l'entité.
     */
    private function getObjectiveOptionsForEntity(mixed $entity): array
    {
        $agentId = null;

        if ($entity instanceof \App\Models\Service || $entity instanceof \App\Models\Agence) {
            $agentId = $entity->chef_agent_id ?? null;
        } elseif ($entity instanceof \App\Models\Caisse) {
            $agentId = $entity->directeur_agent_id ?? null;
        }

        if (! $agentId) {
            return [];
        }

        $userId = \App\Models\User::where('agent_id', $agentId)->value('id');

        return $userId ? $this->getObjectiveOptionsForUser($userId) : [];
    }

    /**
     * Serialize an evaluation's criteria into the array format expected by the JS editor.
     * Returns [$objectiveCriteria, $subjectiveCriteria].
     */
    private function serializeCriteriaForEdit(Evaluation $evaluation): array
    {
        $evaluation->loadMissing('criteres.sousCriteres');

        $objective = $evaluation->criteres->where('type', 'objectif')->values()
            ->map(fn ($c) => [
                'titre'                              => $c->titre,
                'source_fiche_objectif_id'           => $c->source_fiche_objectif_id,
                'source_fiche_objectif_objectif_id'  => $c->source_fiche_objectif_objectif_id,
                'observation'                        => $c->observation,
                'note_directe'                       => $c->note_globale,
                'subcriteria'                        => $c->sousCriteres->map(fn ($s) => [
                    'libelle'                             => $s->libelle,
                    'note'                                => $s->note,
                    'observation'                         => $s->observation,
                    'source_fiche_objectif_objectif_id'   => $s->source_fiche_objectif_objectif_id ?? null,
                ])->toArray(),
            ])->toArray();

        $subjective = $evaluation->criteres->where('type', 'subjectif')->values()
            ->map(fn ($c) => [
                'titre'             => $c->titre,
                'source_template_id'=> $c->source_template_id,
                'id'                => $c->source_template_id, // JS uses criterion.id as source_template_id in the hidden field
                'observation'       => $c->observation,
                'note_directe'      => $c->note_globale,
                'subcriteria'       => $c->sousCriteres->map(fn ($s) => [
                    'libelle'     => $s->libelle,
                    'note'        => $s->note,
                    'observation' => $s->observation,
                ])->toArray(),
            ])->toArray();

        return [$objective, $subjective];
    }

    /**
     * Shared data bag for create views.
     */
    private function createViewData(array $specific = []): array
    {
        $openAnnee    = Annee::currentOpen();
        $openSemestre = $openAnnee
            ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first()
            : null;
        $subjectiveTemplates = SubjectiveCriteriaTemplate::with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        return array_merge([
            'openAnnee'           => $openAnnee,
            'openSemestre'        => $openSemestre,
            'subjectiveTemplates' => $subjectiveTemplates,
            'oldFormations'       => old('identification.formations', null),
            'oldExperiences'      => old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]),
            'objectiveOptions'    => [],
        ], $specific);
    }

    // =========================================================================
    // HELPERS PARTAGÉS — persistance évaluation
    // =========================================================================

    /**
     * Résout le Semestre à partir de annee_id + code "S1"/"S2".
     * Lève une 404 si introuvable.
     */
    private function resolveSemestre(int $anneeId, string $code): \App\Models\Semestre
    {
        $numero = (int) ltrim($code, 'S'); // "S1" → 1, "S2" → 2
        return \App\Models\Semestre::where('annee_id', $anneeId)
            ->where('numero', $numero)
            ->firstOrFail();
    }

    /**
     * Construit les champs de base communs à tous les Evaluation::create().
     */
    private function evaluationBaseFields(\App\Models\Semestre $semestre): array
    {
        return [
            'semestre_id' => $semestre->id,
            'date_debut'  => $semestre->dateDebut()->toDateString(),
            'date_fin'    => $semestre->dateFin()->toDateString(),
        ];
    }

    /**
     * Vérifie doublon proprement : evaluableType, evaluableId, semestreId.
     */
    private function isDuplicate(string $evaluableType, int $evaluableId, int $semestreId, ?int $excludeId = null): bool
    {
        return $this->evaluationService->dejaEvalueeSemestre($evaluableId, $evaluableType, $semestreId, $excludeId);
    }

    /**
     * Normalise les critères depuis la Request, calcule les scores,
     * sauvegarde les critères, puis met à jour l'évaluation avec
     * scores + commentaire + plan + signatures + identification.
     */
    private function persistFullEvaluationData(Evaluation $evaluation, Request $request): void
    {
        // 1. Critères objectifs + subjectifs
        $objCriteria  = $this->evaluationService->normalizeCriteria(
            $request->input('objective_criteres', []), 'objectif', 1, 5, true
        );
        $subjCriteria = $this->evaluationService->normalizeCriteria(
            $request->input('subjective_criteres', []), 'subjectif', 1, 5, false
        );

        // Supprimer et re-créer les critères.
        // La sentinelle _subjective_criteres_submitted garantit que la section
        // subjective a bien été soumise depuis un formulaire valide.
        // Sans elle (appel API ou formulaire incomplet), on conserve les critères
        // subjectifs existants pour éviter toute perte accidentelle de données.
        $subjectiveSubmitted = $request->boolean('_subjective_criteres_submitted');

        if ($subjectiveSubmitted) {
            // Formulaire complet : supprimer les deux types puis re-créer
            $evaluation->criteres()->delete();
            $this->evaluationService->persistCriteria($evaluation, array_merge($objCriteria, $subjCriteria));
        } else {
            // Formulaire partiel : ne mettre à jour que les critères objectifs
            $evaluation->criteres()->where('type', 'objectif')->delete();
            $this->evaluationService->persistCriteria($evaluation, $objCriteria);
            // Récupérer les critères subjectifs existants pour le calcul des scores
            $subjCriteria = $evaluation->criteres()->where('type', 'subjectif')
                ->get()
                ->map(fn ($c) => ['note_globale' => (float) $c->note_globale])
                ->all();
        }

        // 2. Calcul des scores
        $scores = $this->evaluationService->computeScores($subjCriteria, $objCriteria);

        // 3. Plan d'amélioration, signatures, commentaire (sur Evaluation)
        $dateEvalNorm = $this->evaluationService->normalizeDateValue(
            $request->input('identification.date_evaluation', '')
        );
        $dateSigEvalue = $this->evaluationService->normalizeDateValue(
            $request->input('date_signature_evalue', '')
        );
        $dateSigEval = $this->evaluationService->normalizeDateValue(
            $request->input('date_signature_evaluateur', '')
        );

        $evaluation->update(array_merge($scores, [
            'commentaire'           => $request->input('commentaire'),
            'points_a_ameliorer'    => $request->input('points_a_ameliorer'),
            'strategies_amelioration' => $request->input('strategies_amelioration'),
            'signature_evalue_nom'    => $request->input('signature_evalue_nom'),
            'date_signature_evalue'   => $dateSigEvalue,
            'signature_evaluateur_nom'=> $request->input('signature_evaluateur_nom'),
            'date_signature_evaluateur' => $dateSigEval,
        ]));

        // 4. Identification (table séparée)
        $ident = $request->input('identification', []);
        $evaluation->identification()->updateOrCreate(
            ['evaluation_id' => $evaluation->id],
            [
                'nom_prenom'      => $ident['nom_prenom'] ?? null,
                'grade'           => $ident['grade'] ?? null,
                'matricule'       => $ident['matricule'] ?? null,
                'emploi'          => $ident['emploi'] ?? null,
                'direction'       => $ident['direction'] ?? null,
                'direction_service'=> $ident['direction_service'] ?? null,
                'semestre'        => $ident['semestre'] ?? null,
                'date_evaluation' => $dateEvalNorm,
                'formations'      => is_array($ident['formations'] ?? null) ? $ident['formations'] : null,
                'experiences'     => is_array($ident['experiences'] ?? null) ? $ident['experiences'] : null,
            ]
        );
    }

    // =========================================================================
    // PCA — private methods
    // =========================================================================

    private function getPcaEntiteId(): int
    {
        $user  = Auth::user();
        $entite = Entite::query()
            ->where('pca_agent_id', $user->agent_id)
            ->first() ?? Entite::query()->latest()->first();
        return (int) ($entite?->id ?? 0);
    }

    private function getDGOfDirectionGenerale(): ?User
    {
        $entiteId = $this->getPcaEntiteId();
        $entite   = Entite::find($entiteId);
        if (! $entite) {
            return null;
        }
        return User::where('agent_id', $entite->dg_agent_id)->first();
    }

    private function getPcaDirection(): ?Direction
    {
        $dgUser = $this->getDGOfDirectionGenerale();
        if (! $dgUser) {
            return null;
        }
        return Direction::where('directeur_agent_id', $dgUser->agent_id)->first();
    }

    private function pcaIndex(Request $request): View
    {
        $this->authorize('evaluations.voir-equipe');

        $search = trim((string) $request->query('search', ''));
        $statut = trim((string) $request->query('statut', ''));

        $dgUser    = $this->getDGOfDirectionGenerale();
        $baseQuery = Evaluation::query()
            ->with(['evaluable', 'evaluateur'])
            ->where('evaluable_type', User::class)
            ->when($dgUser, fn ($q) => $q->where('evaluable_id', $dgUser->id), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($search !== '', fn ($q) => $q->whereHasMorph('evaluable', [User::class], fn ($q2) => $q2->where('name', 'like', "%{$search}%")))
            ->when($statut !== '', fn ($q) => $q->where('statut', $statut));

        $evaluations = (clone $baseQuery)->latest()->paginate(10)->withQueryString();

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'brouillon' => (clone $baseQuery)->where('statut', 'brouillon')->count(),
            'soumis'    => (clone $baseQuery)->where('statut', 'soumis')->count(),
            'valide'    => (clone $baseQuery)->where('statut', 'valide')->count(),
            'refuse'    => (clone $baseQuery)->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        return view('pca.evaluations.index', [
            'evaluations' => $evaluations,
            'filters'     => ['search' => $search, 'statut' => $statut],
            'stats'       => $stats,
        ]);
    }

    private function pcaAuthorizeEvaluation(Evaluation $evaluation): void
    {
        $entiteId = $this->getPcaEntiteId();
        $allowed  = false;
        if ($evaluation->evaluable_type === User::class) {
            $dgUser = $this->getDGOfDirectionGenerale();
            if ($dgUser && (int) $evaluation->evaluable_id === $dgUser->id) {
                if ((int) $entiteId === ($evaluation->evaluateur->agent?->entite_id ?? null)) {
                    $allowed = true;
                }
                if (Auth::check() && (int) $evaluation->evaluateur_id === Auth::id()) {
                    $allowed = true;
                }
            }
        }
        if (! $allowed) {
            abort(403);
        }
    }

    private function pcaShow(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.creer');
        $this->pcaAuthorizeEvaluation($evaluation);

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = 'Directeur Général';

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.pca',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => $cibleType,
            'backRoute'      => route('pca.evaluations.index'),
            'breadcrumb'     => 'PCA · Évaluations',
            'editRoute'      => 'pca.evaluations.edit',
            'soumettreRoute' => 'pca.evaluations.submit',
            'destroyRoute'   => 'pca.evaluations.destroy',
            'pdfRoute'       => 'pca.evaluations.pdf',
        ]);
    }

    private function pcaCreate(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $dgUser = $this->getDGOfDirectionGenerale();

        if (! $dgUser) {
            return back()->with('error', 'Aucun DG trouvé pour cette entité.');
        }

        $dgAgent = $dgUser->agent_id ? \App\Models\Agent::find($dgUser->agent_id) : null;
        $entite  = \App\Models\Entite::where('dg_agent_id', $dgUser->agent_id)->first()
            ?? \App\Models\Entite::latest()->first();

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.pca',
            'heroSubtitle'              => 'PCA · Évaluations',
            'formAction'                => route('pca.evaluations.store'),
            'backUrl'                   => route('pca.evaluations.index'),
            'evalueLabel'               => 'Directeur Général',
            'evaluateurLabel'           => 'PCA',
            'targetType'                => 'user',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($dgUser->id),
            'subordonne'                => $dgUser,
            'prefilledMatricule'        => $dgAgent?->matricule ?? '',
            'prefilledNomPrenom'        => $dgAgent ? trim(($dgAgent->prenom ?? '') . ' ' . ($dgAgent->nom ?? '')) : $dgUser->name,
            'prefilledEmploi'           => $dgAgent?->role ?? 'Directeur Général',
            'entiteNom'                 => $entite?->nom ?? '',
            'prefilledDirectionService' => '',
        ]));
    }

    private function pcaStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $entiteId = $this->getPcaEntiteId();
        $dgUser   = $this->getDGOfDirectionGenerale();

        if (! $dgUser) {
            return back()->with('error', 'Aucun DG trouvé.');
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(User::class, $dgUser->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => User::class,
            'evaluable_id'   => $dgUser->id,
            'evaluable_role' => 'DG',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('pca.evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function pcaEdit(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.creer');
        $this->pcaAuthorizeEvaluation($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $subordonne = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                     => 'layouts.pca',
            'heroSubtitle'               => 'PCA · Évaluations',
            'formAction'                 => route('pca.evaluations.update', $evaluation),
            'backUrl'                    => route('pca.evaluations.show', $evaluation),
            'evalueLabel'                => 'Directeur Général',
            'evaluateurLabel'            => 'PCA',
            'evaluation'                 => $evaluation,
            'ident'                      => $ident,
            'openAnnee'                  => Annee::currentOpen(),
            'openSemestre'               => null,
            'objectiveOptions'           => $subordonne ? $this->getObjectiveOptionsForUser($subordonne->id) : [],
            'existingObjectiveCriteria'  => $objCriteria,
            'existingSubjectiveCriteria' => $subjCriteria,
            'oldFormations'              => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'             => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                   => $ident?->nom_prenom ?? ($subordonne?->name ?? '-'),
            'cibleType'                  => 'Directeur Général',
        ]);
    }

    private function pcaUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $this->pcaAuthorizeEvaluation($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            Alerte::notifier($evaluation->evaluable_id, 'Votre évaluation a été soumise par le PCA.', '', 'haute', route('dg.evaluations.show', $evaluation));
            return redirect()->route('pca.evaluations.index')->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('pca.evaluations.show', $evaluation)->with('success', 'Évaluation mise à jour.');
    }

    private function pcaSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->pcaAuthorizeEvaluation($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier($evaluation->evaluable_id, 'Votre évaluation a été soumise par le PCA.', '', 'haute', route('dg.evaluations.show', $evaluation));

        return redirect()->route('pca.evaluations.index')
            ->with('success', 'Évaluation soumise.');
    }

    private function pcaApprove(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->pcaAuthorizeEvaluation($evaluation);

        $evaluation->update(['statut' => 'valide']);

        Alerte::notifier($evaluation->evaluable_id, 'Votre évaluation a été validée.', '', 'haute', route('dg.evaluations.show', $evaluation));

        return redirect()->route('pca.evaluations.index')
            ->with('success', 'Évaluation validée.');
    }

    private function pcaExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $this->pcaAuthorizeEvaluation($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'pca.evaluations.pdf', "evaluation-{$evaluation->id}-pca.pdf");
    }

    private function pcaDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->pcaAuthorizeEvaluation($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('pca.evaluations.index')
            ->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // DG RECEIVED — private methods
    // =========================================================================

    private function dgReceivedAuthorize(Evaluation $evaluation): void
    {
        if ($evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== Auth::id()) {
            abort(403);
        }
    }

    private function dgReceivedShow(Evaluation $evaluation): View|RedirectResponse
    {
        $this->dgReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = 'Directeur Général';

        return $this->resolveEvaluationView($evaluation, [
            'layout'          => 'layouts.dg',
            'cibleLabel'      => $cibleLabel,
            'cibleType'       => $cibleType,
            'backRoute'       => route('dg.mon-espace'),
            'breadcrumb'      => 'DG · Mon évaluation',
            'isAssignee'      => true,
            'statutRoute'     => 'dg.evaluations.statut',
            'reclamerRoute'   => 'dg.evaluations.reclamer',
            'commentaireRoute'=> 'dg.evaluations.commentaire',
            'pdfRoute'        => 'dg.evaluations.pdf',
        ]);
    }

    private function dgReceivedStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->dgReceivedAuthorize($evaluation);

        $action = $request->input('action');
        $request->validate([
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $statut = $action === 'accepter' ? 'valide' : 'reclamation';
        $fields = ['statut' => $statut];
        if ($statut === 'reclamation') {
            $fields['motif_refus']        = $request->input('motif_refus');
            $fields['statut_reclamation'] = 'en_attente';
        }
        $evaluation->update($fields);

        $labelStatut = $statut === 'valide' ? 'validée' : 'refusée';
        Alerte::notifier($evaluation->evaluateur_id, "L'évaluation du DG a été {$labelStatut}.", '', 'haute', route('pca.evaluations.show', $evaluation));

        if ($statut === 'reclamation') {
            $rhUser = User::where('role', 'RH')->first();
            if ($rhUser) {
                Alerte::notifier($rhUser->id, "L'évaluation du DG a été refusée.", '', 'haute', route('rh.evaluations.show', $evaluation));
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    private function dgReceivedReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->dgReceivedAuthorize($evaluation);

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->update([
            'statut'              => 'reclamation',
            'reclamation'         => $request->input('reclamation'),
            'statut_reclamation'  => 'en_attente',
        ]);

        Alerte::notifier($evaluation->evaluateur_id, 'Le DG a déposé une réclamation sur son évaluation.', '', 'haute', route('pca.evaluations.show', $evaluation));
        $rhUser = User::where('role', 'RH')->first();
        if ($rhUser) {
            Alerte::notifier($rhUser->id, 'Une réclamation a été soumise (DG).', '', 'haute', route('rh.evaluations.show', $evaluation));
        }

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function dgReceivedCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        $this->dgReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function dgReceivedExportPdf(Evaluation $evaluation): Response
    {
        $this->dgReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-dg.pdf");
    }

    // =========================================================================
    // DG SUB (DGA, Assistante_Dg, Conseillers_Dg) — private methods
    // =========================================================================

    private function getDgSubordonnes(): \Illuminate\Support\Collection
    {
        $dg    = Auth::user();
        $entite = Entite::where('dg_agent_id', $dg->agent_id)->first()
            ?? Entite::latest()->first();

        $dgaUser        = User::where('role', 'DGA')->first();
        $assistanteUser = User::where('role', 'Assistante_Dg')->first();
        $conseillers    = User::where('role', 'Conseillers_Dg')->get();

        $all = collect();
        if ($dgaUser) {
            $all->push($dgaUser);
        }
        if ($assistanteUser) {
            $all->push($assistanteUser);
        }
        foreach ($conseillers as $c) {
            $all->push($c);
        }
        return $all;
    }

    private function dgSubAuthorizeCreated(Evaluation $evaluation): void
    {
        $allowedIds = $this->getDgSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array((int) $evaluation->evaluable_id, $allowedIds, true) ||
            (int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }
    }

    private function dgSubBackUrl(User $subordonne): string
    {
        return match ($subordonne->role) {
            'DGA'           => route('dg.dga') . '?tab=evaluations',
            'Assistante_Dg' => route('dg.assistante') . '?tab=evaluations',
            default         => route('dg.conseillers.show', $subordonne) . '?tab=evaluations',
        };
    }

    private function dgSubCreate(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $subordonne = User::findOrFail($request->integer('user_id'));
        $allowedIds = $this->getDgSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array($subordonne->id, $allowedIds, true)) {
            abort(403);
        }

        $agent = $subordonne->agent_id ? \App\Models\Agent::find($subordonne->agent_id) : null;
        $entite = \App\Models\Entite::where('dg_agent_id', Auth::user()->agent_id)->first()
            ?? \App\Models\Entite::latest()->first();

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.dg',
            'heroSubtitle'              => 'DG · Évaluations subordonnés',
            'formAction'                => route('dg.sub-evaluations.store'),
            'backUrl'                   => $this->dgSubBackUrl($subordonne),
            'evalueLabel'               => str_replace('_', ' ', $subordonne->role ?? 'Subordonné'),
            'evaluateurLabel'           => 'Directeur Général',
            'targetType'                => 'user',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($subordonne->id),
            'subordonne'                => $subordonne,
            'prefilledMatricule'        => $agent?->matricule ?? '',
            'prefilledNomPrenom'        => $agent ? trim(($agent->prenom ?? '') . ' ' . ($agent->nom ?? '')) : $subordonne->name,
            'prefilledEmploi'           => in_array($agent?->role, ['Agent', 'Conseiller DG'], true)
                                            ? ($agent?->poste ?? $agent?->role ?? '')
                                            : ($agent?->role ?? ''),
            'entiteNom'                 => $entite?->nom ?? '',
            'prefilledDirectionService' => '',
        ]));
    }

    private function dgSubStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $subordonne = User::findOrFail($request->integer('user_id'));
        $allowedIds = $this->getDgSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (! in_array($subordonne->id, $allowedIds, true)) {
            abort(403);
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(User::class, $subordonne->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => User::class,
            'evaluable_id'   => $subordonne->id,
            'evaluable_role' => $subordonne->role,
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('dg.sub-evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function dgSubShow(Evaluation $evaluation): View
    {
        $this->dgSubAuthorizeCreated($evaluation);

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = str_replace('_', ' ', $subordonne?->role ?? 'Subordonné');

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.dg',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => $cibleType,
            'backRoute'      => $subordonne ? $this->dgSubBackUrl($subordonne) : route('dg.subordonnes'),
            'breadcrumb'     => 'DG · Évaluations subordonnés',
            'editRoute'      => 'dg.sub-evaluations.edit',
            'soumettreRoute' => 'dg.sub-evaluations.submit',
            'destroyRoute'   => 'dg.sub-evaluations.destroy',
            'pdfRoute'       => 'dg.sub-evaluations.pdf',
        ]);
    }

    private function dgSubEdit(Evaluation $evaluation): View
    {
        $this->dgSubAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $subordonne = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                      => 'layouts.dg',
            'heroSubtitle'                => 'DG · Évaluations subordonnés',
            'formAction'                  => route('dg.sub-evaluations.update', $evaluation),
            'backUrl'                     => route('dg.sub-evaluations.show', $evaluation),
            'evalueLabel'                 => str_replace('_', ' ', $subordonne?->role ?? 'Subordonné'),
            'evaluateurLabel'             => 'Directeur Général',
            'evaluation'                  => $evaluation,
            'ident'                       => $ident,
            'openAnnee'                   => Annee::currentOpen(),
            'openSemestre'                => null,
            'objectiveOptions'            => $this->getObjectiveOptionsForUser($subordonne->id),
            'existingObjectiveCriteria'   => $objCriteria,
            'existingSubjectiveCriteria'  => $subjCriteria,
            'oldFormations'               => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'              => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                    => $subordonne?->name ?? '-',
            'cibleType'                   => str_replace('_', ' ', $subordonne?->role ?? 'Subordonné'),
        ]);
    }

    private function dgSubUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->dgSubAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route('dga.evaluations.show', $evaluation));
            $subordonne = $evaluation->evaluable;
            $backUrl    = $subordonne ? $this->dgSubBackUrl($subordonne) : route('dg.subordonnes');
            return redirect($backUrl)->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('dg.sub-evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour.');
    }

    private function dgSubSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->dgSubAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route('dga.evaluations.show', $evaluation));

        $subordonne = $evaluation->evaluable;
        $backUrl    = $subordonne ? $this->dgSubBackUrl($subordonne) : route('dg.subordonnes');

        return redirect($backUrl)->with('success', 'Évaluation soumise.');
    }

    private function dgSubExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $this->dgSubAuthorizeCreated($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-dg-sub.pdf");
    }

    private function dgSubDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->dgSubAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $subordonne = $evaluation->evaluable;
        $backUrl    = $subordonne ? $this->dgSubBackUrl($subordonne) : route('dg.subordonnes');
        $evaluation->delete();

        return redirect($backUrl)->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // DG DIRECTIONS — private methods
    // =========================================================================

    private function getDgEntiteId(): int
    {
        $dg     = Auth::user();
        $entite = Entite::query()->where('dg_agent_id', $dg->agent_id)->first()
            ?? Entite::query()->latest()->first();
        return (int) ($entite?->id ?? 0);
    }

    private function dgDirectionAuthorize(Evaluation $evaluation): Direction
    {
        if ($evaluation->evaluable_type !== Direction::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }
        $direction = Direction::find($evaluation->evaluable_id);
        if (! $direction || (int) $direction->entite_id !== $this->getDgEntiteId()) {
            abort(403);
        }
        return $direction;
    }

    private function dgDirectionCreate(Request $request, Direction $direction): View
    {
        $this->authorize('evaluations.creer');
        $entiteId = $this->getDgEntiteId();
        if ((int) $direction->entite_id !== $entiteId) {
            abort(403);
        }

        $dirAgent     = $direction->directeur ?? null;
        $dirUser      = $dirAgent ? \App\Models\User::where('agent_id', $dirAgent->id)->first() : null;
        $entite       = \App\Models\Entite::find($direction->entite_id);

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.dg',
            'heroSubtitle'              => 'DG · Évaluations directions',
            'formAction'                => route('dg.directions.evaluations.store'),
            'backUrl'                   => route('dg.directions.show', $direction),
            'evalueLabel'               => 'Directeur',
            'evaluateurLabel'           => 'Directeur Général',
            'targetType'                => 'direction',
            'direction'                 => $direction,
            'prefilledMatricule'        => $dirAgent?->matricule ?? '',
            'prefilledNomPrenom'        => $dirAgent ? trim(($dirAgent->prenom ?? '') . ' ' . ($dirAgent->nom ?? '')) : '',
            'prefilledEmploi'           => $dirAgent?->role ?? 'Directeur de Direction',
            'entiteNom'                 => $entite?->nom ?? '',
            'prefilledDirectionService' => $direction->nom ?? '',
            'objectiveOptions'          => $dirUser ? $this->getObjectiveOptionsForUser($dirUser->id) : [],
        ]));
    }

    private function dgDirectionStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $directionId = $request->integer('direction_id');
        $direction   = Direction::findOrFail($directionId);
        $entiteId    = $this->getDgEntiteId();

        if ((int) $direction->entite_id !== $entiteId) {
            abort(403);
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(Direction::class, $direction->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => Direction::class,
            'evaluable_id'   => $direction->id,
            'evaluable_role' => 'manager',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('dg.directions.evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function dgDirectionShow(Evaluation $evaluation): View
    {
        $direction  = $this->dgDirectionAuthorize($evaluation);
        $cibleLabel = $direction->nom ?? '-';
        $cibleType  = 'Direction';

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.dg',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => $cibleType,
            'backRoute'      => route('dg.directions.show', $direction),
            'breadcrumb'     => 'DG · Évaluations directions',
            'editRoute'      => 'dg.directions.evaluations.edit',
            'soumettreRoute' => 'dg.directions.evaluations.submit',
            'destroyRoute'   => 'dg.directions.evaluations.destroy',
            'pdfRoute'       => 'dg.directions.evaluations.pdf',
        ]);
    }

    private function dgDirectionEdit(Evaluation $evaluation): View
    {
        $direction = $this->dgDirectionAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                      => 'layouts.dg',
            'heroSubtitle'                => 'DG · Évaluations directions',
            'formAction'                  => route('dg.directions.evaluations.update', $evaluation),
            'backUrl'                     => route('dg.directions.evaluations.show', $evaluation),
            'evalueLabel'                 => 'Directeur',
            'evaluateurLabel'             => 'Directeur Général',
            'evaluation'                  => $evaluation,
            'ident'                       => $ident,
            'openAnnee'                   => Annee::currentOpen(),
            'openSemestre'                => null,
            'objectiveOptions'            => (function () use ($direction) {
                $dirAgent = $direction->directeur ?? null;
                $dirUser  = $dirAgent ? \App\Models\User::where('agent_id', $dirAgent->id)->first() : null;
                return $dirUser ? $this->getObjectiveOptionsForUser($dirUser->id) : [];
            })(),
            'existingObjectiveCriteria'   => $objCriteria,
            'existingSubjectiveCriteria'  => $subjCriteria,
            'oldFormations'               => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'              => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                    => $direction->nom ?? '-',
            'cibleType'                   => 'Direction',
        ]);
    }

    private function dgDirectionUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $direction = $this->dgDirectionAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            if ($direction->directeur_agent_id) {
                $directeurUser = User::where('agent_id', $direction->directeur_agent_id)->first();
                if ($directeurUser) {
                    Alerte::notifier($directeurUser->id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route('directeur.evaluations.show', $evaluation));
                }
            }
            return redirect()->route('dg.directions.show', $direction)
                ->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('dg.directions.evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour.');
    }

    private function dgDirectionSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $direction = $this->dgDirectionAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        if ($direction->directeur_agent_id) {
            $directeurUser = User::where('agent_id', $direction->directeur_agent_id)->first();
            if ($directeurUser) {
                Alerte::notifier($directeurUser->id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route('directeur.evaluations.show', $evaluation));
            }
        }

        return redirect()->route('dg.directions.show', $direction)
            ->with('success', 'Évaluation soumise.');
    }

    private function dgDirectionExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $this->dgDirectionAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-direction.pdf");
    }

    private function dgDirectionDestroy(Evaluation $evaluation): RedirectResponse
    {
        $direction = $this->dgDirectionAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('dg.directions.show', $direction)
            ->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // DGA RECEIVED — private methods
    // =========================================================================

    private function dgaReceivedAuthorize(Evaluation $evaluation): void
    {
        $role = Auth::user()?->role ?? '';
        if (! in_array($role, self::ALLOWED_ROLES_DGA, true)) {
            abort(403);
        }
        if ($evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== Auth::id()) {
            abort(403);
        }
    }

    private function dgaReceivedShow(Evaluation $evaluation): View|RedirectResponse
    {
        $this->dgaReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $routePrefix = $this->espaceRoutePrefix();
        $layout      = $routePrefix === 'dga' ? 'layouts.dga' : 'layouts.subordonne';
        $backRoute   = route("{$routePrefix}.mon-espace");

        $subordonne = $evaluation->evaluable;
        $role       = $subordonne?->role ?? '';
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = self::ROLE_LABELS_DGA[$role] ?? str_replace('_', ' ', $role);

        return $this->resolveEvaluationView($evaluation, [
            'layout'          => $layout,
            'cibleLabel'      => $cibleLabel,
            'cibleType'       => $cibleType,
            'backRoute'       => $backRoute,
            'breadcrumb'      => 'Mon évaluation',
            'isAssignee'      => true,
            'statutRoute'     => "{$routePrefix}.evaluations.statut",
            'reclamerRoute'   => "{$routePrefix}.evaluations.reclamer",
            'commentaireRoute'=> "{$routePrefix}.evaluations.commentaire",
            'pdfRoute'        => "{$routePrefix}.evaluations.pdf",
        ]);
    }

    private function dgaReceivedStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->dgaReceivedAuthorize($evaluation);

        $action = $request->input('action');
        $request->validate([
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $statut = $action === 'accepter' ? 'valide' : 'reclamation';
        $fields = ['statut' => $statut];
        if ($statut === 'reclamation') {
            $fields['motif_refus']        = $request->input('motif_refus');
            $fields['statut_reclamation'] = 'en_attente';
        }
        $evaluation->update($fields);

        $role        = Auth::user()?->role ?? '';
        $roleLabel   = self::ROLE_LABELS_DGA[$role] ?? $role;
        $labelStatut = $statut === 'valide' ? 'validée' : 'refusée';
        Alerte::notifier($evaluation->evaluateur_id, "L'évaluation de {$roleLabel} a été {$labelStatut}.", '', 'haute', route('dg.sub-evaluations.show', $evaluation));

        if ($statut === 'reclamation') {
            $rhUser = User::where('role', 'RH')->first();
            if ($rhUser) {
                Alerte::notifier($rhUser->id, "L'évaluation de {$roleLabel} a été refusée.", '', 'haute', route('rh.evaluations.show', $evaluation));
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    private function dgaReceivedReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->dgaReceivedAuthorize($evaluation);

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->update([
            'statut'             => 'reclamation',
            'reclamation'        => $request->input('reclamation'),
            'statut_reclamation' => 'en_attente',
        ]);

        $role      = Auth::user()?->role ?? '';
        $roleLabel = self::ROLE_LABELS_DGA[$role] ?? $role;
        Alerte::notifier($evaluation->evaluateur_id, "{$roleLabel} a déposé une réclamation.", '', 'haute', route('dg.sub-evaluations.show', $evaluation));
        $rhUser = User::where('role', 'RH')->first();
        if ($rhUser) {
            Alerte::notifier($rhUser->id, "Une réclamation a été soumise ({$roleLabel}).", '', 'haute', route('rh.evaluations.show', $evaluation));
        }

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function dgaReceivedCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        $this->dgaReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function dgaReceivedExportPdf(Evaluation $evaluation): Response
    {
        $this->dgaReceivedAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-dga.pdf");
    }

    // =========================================================================
    // DGA SUB — private methods
    // =========================================================================

    private function checkDga(): void
    {
        if (Auth::user()?->role !== 'DGA') {
            abort(403);
        }
    }

    private function directionDga(): ?Direction
    {
        return Direction::where('directeur_agent_id', Auth::user()?->agent_id)->first();
    }

    private function getDgaSubordonnes(): \Illuminate\Support\Collection
    {
        $this->checkDga();
        $entite    = $this->getEntiteForDGA();
        $direction = $this->directionDga();

        $list = collect();

        // DT directors
        $dtDirectors = DelegationTechnique::whereNotNull('directeur_agent_id')->get()
            ->map(function (DelegationTechnique $dt): ?array {
                $user = User::where('agent_id', $dt->directeur_agent_id)->first();
                if (! $user) {
                    return null;
                }
                return [
                    'id'           => $user->id,
                    'agent_id'     => $user->agent_id,
                    'nom'          => $user->name,
                    'role_label'   => 'Directeur Technique',
                    'entite_label' => $dt->region ?? $dt->nom ?? '',
                    'service_label'=> '',
                    'groupe'       => 'dt_director',
                    'redirect_to'  => 'subordonnes',
                ];
            })->filter()->values();

        $list = $list->merge($dtDirectors);

        // Secrétaire DGA
        if ($entite?->dga_secretaire_agent_id) {
            $secUser = User::where('agent_id', $entite->dga_secretaire_agent_id)->first();
            if ($secUser) {
                $list->push([
                    'id'           => $secUser->id,
                    'agent_id'     => $secUser->agent_id,
                    'nom'          => $secUser->name,
                    'role_label'   => 'Secrétaire DGA',
                    'entite_label' => '',
                    'service_label'=> '',
                    'groupe'       => 'secretaire',
                    'redirect_to'  => 'subordonnes',
                ]);
            }
        }

        // Chefs de services DGA direction
        if ($direction) {
            $serviceIds = Service::where('direction_id', $direction->id)->pluck('id');
            $chefs = Agent::whereIn('service_id', $serviceIds)
                ->whereHas('user')
                ->with('user')
                ->get();
            foreach ($chefs as $agent) {
                $user = $agent->user;
                if (! $user) {
                    continue;
                }
                $service = Service::find($agent->service_id);
                $list->push([
                    'id'           => $user->id,
                    'agent_id'     => $user->agent_id,
                    'nom'          => $user->name,
                    'role_label'   => 'Agent',
                    'entite_label' => $direction->nom ?? '',
                    'service_label'=> $service?->nom ?? '',
                    'groupe'       => 'direction_dga',
                    'redirect_to'  => 'direction',
                ]);
            }
        }

        return $list;
    }

    private function getDgaAllowedUserIds(): array
    {
        return $this->getDgaSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function dgaSubAuthorize(Evaluation $evaluation): void
    {
        $this->checkDga();
        $allowedIds = $this->getDgaAllowedUserIds();
        if (! in_array((int) $evaluation->evaluable_id, $allowedIds, true) ||
            (int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }
    }

    private function dgaSubRedirectAfterAction(Evaluation $evaluation): string
    {
        $subordonne = $evaluation->evaluable;
        if (! $subordonne) {
            return route('dga.subordonnes');
        }
        $entry = $this->getDgaSubordonnes()->firstWhere('id', $subordonne->id);
        if ($entry && ($entry['redirect_to'] ?? '') === 'direction') {
            return route('dga.direction', ['tab' => 'evaluations']);
        }
        return route('dga.subordonnes.show', $subordonne) . '?tab=evaluations';
    }

    private function dgaSubCreate(Request $request): View
    {
        $this->checkDga();
        $this->authorize('evaluations.creer');
        $subId = $request->integer('subordonne_id') ?: $request->integer('user_id');
        $subordonne = User::findOrFail($subId);
        $allowedIds = $this->getDgaAllowedUserIds();
        if (! in_array($subordonne->id, $allowedIds, true)) {
            abort(403);
        }

        $entry     = $this->getDgaSubordonnes()->firstWhere('id', $subordonne->id);
        $backUrl   = ($entry && ($entry['redirect_to'] ?? '') === 'direction')
            ? route('dga.direction', ['tab' => 'evaluations'])
            : route('dga.subordonnes.show', $subordonne) . '?tab=evaluations';

        $agent = $subordonne->agent_id ? \App\Models\Agent::find($subordonne->agent_id) : null;

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.dga',
            'heroSubtitle'              => 'DGA · Évaluations subordonnés',
            'formAction'                => route('dga.sub-evaluations.store'),
            'backUrl'                   => $backUrl,
            'evalueLabel'               => str_replace('_', ' ', $subordonne->role ?? 'Subordonné'),
            'evaluateurLabel'           => 'DGA',
            'targetType'                => 'user',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($subordonne->id),
            'subordonne'                => $subordonne,
            'prefilledMatricule'        => $agent?->matricule ?? '',
            'prefilledNomPrenom'        => $agent ? trim(($agent->prenom ?? '') . ' ' . ($agent->nom ?? '')) : $subordonne->name,
            'prefilledEmploi'           => in_array($agent?->role, ['Agent', 'Conseiller DG'], true)
                                            ? ($agent?->poste ?? $agent?->role ?? '')
                                            : ($agent?->role ?? ''),
            'entiteNom'                 => $entry['entite_label'] ?? '',
            'prefilledDirectionService' => $entry['service_label'] ?? '',
        ]));
    }

    private function dgaSubStore(Request $request): RedirectResponse
    {
        $this->checkDga();
        $this->authorize('evaluations.creer');
        $subordonne = User::findOrFail($request->integer('user_id'));
        $allowedIds = $this->getDgaAllowedUserIds();
        if (! in_array($subordonne->id, $allowedIds, true)) {
            abort(403);
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(User::class, $subordonne->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => User::class,
            'evaluable_id'   => $subordonne->id,
            'evaluable_role' => $subordonne->role,
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('dga.sub-evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function dgaSubShow(Evaluation $evaluation): View
    {
        $this->dgaSubAuthorize($evaluation);

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = str_replace('_', ' ', $subordonne?->role ?? 'Subordonné');

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.dga',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => $cibleType,
            'backRoute'      => $this->dgaSubRedirectAfterAction($evaluation),
            'breadcrumb'     => 'DGA · Évaluations subordonnés',
            'editRoute'      => 'dga.sub-evaluations.edit',
            'soumettreRoute' => 'dga.sub-evaluations.submit',
            'destroyRoute'   => 'dga.sub-evaluations.destroy',
            'pdfRoute'       => 'dga.sub-evaluations.pdf',
        ]);
    }

    private function dgaSubEdit(Evaluation $evaluation): View
    {
        $this->dgaSubAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $subordonne = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                      => 'layouts.dga',
            'heroSubtitle'                => 'DGA · Évaluations subordonnés',
            'formAction'                  => route('dga.sub-evaluations.update', $evaluation),
            'backUrl'                     => route('dga.sub-evaluations.show', $evaluation),
            'evalueLabel'                 => str_replace('_', ' ', $subordonne?->role ?? 'Subordonné'),
            'evaluateurLabel'             => 'DGA',
            'evaluation'                  => $evaluation,
            'ident'                       => $ident,
            'openAnnee'                   => Annee::currentOpen(),
            'openSemestre'                => null,
            'objectiveOptions'            => $this->getObjectiveOptionsForUser($subordonne->id),
            'existingObjectiveCriteria'   => $objCriteria,
            'existingSubjectiveCriteria'  => $subjCriteria,
            'oldFormations'               => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'              => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                    => $subordonne?->name ?? '-',
            'cibleType'                   => str_replace('_', ' ', $subordonne?->role ?? 'Subordonné'),
        ]);
    }

    private function dgaSubUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->dgaSubAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation du DGA.', '', 'haute', route('directeur.evaluations.show', $evaluation));
            return redirect($this->dgaSubRedirectAfterAction($evaluation))
                ->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('dga.sub-evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour.');
    }

    private function dgaSubSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->dgaSubAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation du DGA.', '', 'haute', route('directeur.evaluations.show', $evaluation));

        return redirect($this->dgaSubRedirectAfterAction($evaluation))
            ->with('success', 'Évaluation soumise.');
    }

    private function dgaSubExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $this->dgaSubAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-dga.pdf");
    }

    private function dgaSubDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->dgaSubAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $backUrl = $this->dgaSubRedirectAfterAction($evaluation);
        $evaluation->delete();

        return redirect($backUrl)->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // DGA NOTES RÉSEAU — private methods
    // =========================================================================

    private function dgaNotesReseauPerimetre(): \Illuminate\Support\Collection
    {
        $entite    = $this->getEntiteForDGA();
        $agentId   = Auth::user()?->agent_id;
        $direction = $agentId
            ? Direction::where('directeur_agent_id', $agentId)->first()
            : null;

        $agentIdsDt = Agent::whereNotNull('delegation_technique_id')
            ->whereNull('caisse_id')
            ->whereNull('agence_id')
            ->whereNull('guichet_id')
            ->pluck('id');

        $serviceIdsDt   = Service::whereNotNull('delegation_technique_id')->pluck('id');
        $agentIdsServDt = Agent::whereIn('service_id', $serviceIdsDt)->pluck('id');

        $agentIdsServicesDga = collect();
        if ($direction) {
            $serviceIds          = Service::where('direction_id', $direction->id)->pluck('id');
            $agentIdsServicesDga = Agent::whereIn('service_id', $serviceIds)->pluck('id');
        }

        $dtAgentIds = DelegationTechnique::whereNotNull('directeur_agent_id')->pluck('directeur_agent_id');
        $secAgentId = $entite?->dga_secretaire_agent_id ? collect([$entite->dga_secretaire_agent_id]) : collect();

        $allAgentIds = $agentIdsDt
            ->merge($agentIdsServDt)
            ->merge($agentIdsServicesDga)
            ->merge($dtAgentIds)
            ->merge($secAgentId)
            ->unique();

        return User::whereIn('agent_id', $allAgentIds)->pluck('id');
    }

    private function dgaNotesReseauShow(Evaluation $evaluation): View
    {
        $this->checkDga();

        $userIds = $this->dgaNotesReseauPerimetre();
        if (! $userIds->contains($evaluation->evaluable_id)) {
            abort(403, 'Cette évaluation ne fait pas partie de votre périmètre.');
        }

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = str_replace('_', ' ', $subordonne?->role ?? 'Subordonné');

        $statusClass = match ($evaluation->statut) {
            'brouillon'   => 'bg-gray-100 text-gray-700',
            'soumis'      => 'bg-blue-100 text-blue-700',
            'accepte'     => 'bg-green-100 text-green-700',
            'reclamation' => 'bg-orange-100 text-orange-700',
            'a_reviser'   => 'bg-yellow-100 text-yellow-700',
            'finalise'    => 'bg-purple-100 text-purple-700',
            default       => 'bg-gray-100 text-gray-600',
        };
        $statusLabel = match ($evaluation->statut) {
            'brouillon'   => 'Brouillon',
            'soumis'      => 'Soumis',
            'accepte'     => 'Accepté',
            'reclamation' => 'Réclamation',
            'a_reviser'   => 'À réviser',
            'finalise'    => 'Finalisé',
            default       => ucfirst($evaluation->statut ?? ''),
        };

        $evaluation->load(['evaluable', 'evaluateur', 'identification', 'criteres.sousCriteres']);
        $note                = (float) ($evaluation->note_finale ?? 0);
        $mention             = $this->evaluationService->mention($note);
        $objectiveCriteria   = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria  = $evaluation->criteres->where('type', 'subjectif')->values();
        $subjectiveTemplates = $subjectiveCriteria->isEmpty()
            ? SubjectiveCriteriaTemplate::with('subcriteria')->where('is_active', true)->orderBy('ordre')->get()
            : collect();

        return view('evaluations.show', [
            'evaluation'          => $evaluation,
            'objectiveCriteria'   => $objectiveCriteria,
            'subjectiveCriteria'  => $subjectiveCriteria,
            'note'                => $note,
            'mention'             => $mention,
            'ident'               => $evaluation->identification,
            'cibleLabel'          => $cibleLabel,
            'cibleType'           => $cibleType,
            'statusLabel'         => $statusLabel,
            'statusClass'         => $statusClass,
            'layout'              => 'layouts.dga',
            'backRoute'           => route('dga.notes-reseau.index'),
            'breadcrumb'          => 'DGA · Notes réseau',
            'subjectiveTemplates' => $subjectiveTemplates,
        ]);
    }

    // =========================================================================
    // DIRECTEUR — private methods
    // =========================================================================

    private function getDirecteurContext(): DirecteurEntity
    {
        return DirecteurEntity::resolveOrFail(Auth::user());
    }

    private function directeurAuthorizeReceived(Evaluation $evaluation): DirecteurEntity
    {
        $ctx = $this->getDirecteurContext();

        $isEntityBased = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        $isUserBased = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        if (! $isEntityBased && ! $isUserBased) {
            abort(403);
        }

        return $ctx;
    }

    private function directeurAuthorizeCreated(Evaluation $evaluation): DirecteurEntity
    {
        $ctx         = $this->getDirecteurContext();
        $validTypes  = [Service::class, Agence::class, Caisse::class];

        if (! in_array($evaluation->evaluable_type, $validTypes, true)) {
            abort(403);
        }
        if (strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager') {
            abort(403);
        }
        if ((int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }

        // Verify target belongs to directeur's entite
        $target = $evaluation->evaluable;
        if (! $target) {
            abort(403);
        }

        return $ctx;
    }

    private function directeurCreate(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $ctx = $this->getDirecteurContext();

        $services = $ctx->getServices()->load('chef');
        $agences  = $ctx->getAgences()->load('chef');
        $caisses  = $ctx->getCaisses()->load('directeurAgent');

        // Pré-sélection via paramètre GET (venant d'une page de subordonné)
        $selectedCaisse  = null;
        $selectedAgence  = null;
        $selectedService = null;

        if ($caisseId = $request->integer('caisse_id') ?: null) {
            $selectedCaisse = $caisses->firstWhere('id', $caisseId);
        } elseif ($agenceId = $request->integer('agence_id') ?: null) {
            $selectedAgence = $agences->firstWhere('id', $agenceId);
        } elseif ($serviceId = $request->integer('service_id') ?: null) {
            $selectedService = $services->firstWhere('id', $serviceId);
        }

        $entiteNomCtx = $ctx->getNom();

        $servicesJson = $services->map(fn ($s) => [
            'id'                => $s->id,
            'agent_id'          => $s->chef?->id ?? null,
            'nom_prenom'        => $s->chef ? trim(($s->chef->prenom ?? '') . ' ' . ($s->chef->nom ?? '')) : '',
            'matricule'         => $s->chef?->matricule ?? '',
            'emploi'            => 'Chef de Service',
            'entite_nom'        => $entiteNomCtx,
            'direction_service' => $s->nom,
        ])->values()->toArray();

        $agencesJson = $agences->map(fn ($a) => [
            'id'                => $a->id,
            'agent_id'          => $a->chef?->id ?? null,
            'nom_prenom'        => $a->chef ? trim(($a->chef->prenom ?? '') . ' ' . ($a->chef->nom ?? '')) : '',
            'matricule'         => $a->chef?->matricule ?? '',
            'emploi'            => "Chef d'Agence",
            'entite_nom'        => $entiteNomCtx,
            'direction_service' => $a->nom,
        ])->values()->toArray();

        $caissesJson = $caisses->map(fn ($c) => [
            'id'                => $c->id,
            'agent_id'          => $c->directeurAgent?->id ?? null,
            'nom_prenom'        => $c->directeurAgent ? trim(($c->directeurAgent->prenom ?? '') . ' ' . ($c->directeurAgent->nom ?? '')) : '',
            'matricule'         => $c->directeurAgent?->matricule ?? '',
            'emploi'            => 'Directeur de Caisse',
            'entite_nom'        => $entiteNomCtx,
            'direction_service' => $c->nom,
        ])->values()->toArray();

        $preselectedEntity = $selectedCaisse ?? $selectedAgence ?? $selectedService;
        $objectiveOptions  = $preselectedEntity ? $this->getObjectiveOptionsForEntity($preselectedEntity) : [];

        return view('evaluations.create', $this->createViewData([
            'layout'           => 'layouts.directeur',
            'heroSubtitle'     => 'Directeur · Évaluations',
            'formAction'       => route('directeur.evaluations.store'),
            'backUrl'          => route('directeur.mon-espace', ['tab' => 'dashboard']),
            'evalueLabel'      => 'Chef de service / Agence / Caisse',
            'evaluateurLabel'  => $ctx->getRoleLabel(),
            'targetType'       => 'service',
            'ctx'              => $ctx,
            'services'         => $services,
            'agences'          => $agences,
            'caisses'          => $caisses,
            'entiteNom'        => $entiteNomCtx,
            'servicesJson'     => $servicesJson,
            'agencesJson'      => $agencesJson,
            'caissesJson'      => $caissesJson,
            'selectedCaisse'   => $selectedCaisse,
            'selectedAgence'   => $selectedAgence,
            'selectedService'  => $selectedService,
            'objectiveOptions' => $objectiveOptions,
        ]));
    }

    private function directeurStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $ctx = $this->getDirecteurContext();

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        // Détecter quel type d'évalué a été sélectionné dans le formulaire
        $modelMap = [
            'caisse'  => [Caisse::class,   $request->integer('caisse_id')],
            'agence'  => [Agence::class,   $request->integer('agence_id')],
            'service' => [Service::class,  $request->integer('service_id')],
        ];
        $modelClass = null;
        $target     = null;
        foreach ($modelMap as [$class, $id]) {
            if ($id) {
                $modelClass = $class;
                $target     = $class::findOrFail($id);
                break;
            }
        }
        if (! $target) {
            return back()->with('error', 'Veuillez sélectionner un collaborateur à évaluer.')->withInput();
        }

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate($modelClass, $target->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => $modelClass,
            'evaluable_id'   => $target->id,
            'evaluable_role' => 'manager',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('directeur.evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function directeurShow(Evaluation $evaluation): View
    {
        $ctx = $this->getDirecteurContext();

        $isReceivedByEntity = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        $isReceivedByUser = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        $validTypes = [Service::class, Agence::class, Caisse::class];
        $isCreated  = in_array($evaluation->evaluable_type, $validTypes, true)
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager'
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceivedByEntity && ! $isReceivedByUser && ! $isCreated) {
            abort(403);
        }

        $isReceived = $isReceivedByEntity || $isReceivedByUser;

        // Un évalué ne peut pas consulter une évaluation encore en brouillon
        if ($isReceived && $evaluation->statut === 'brouillon') {
            abort(403, 'Cette évaluation n\'est pas encore disponible.');
        }
        $target     = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($target?->name ?? ($target?->nom ?? '-'));
        $cibleType  = $isReceivedByEntity ? ucfirst($evaluation->evaluable_type ?? '') : ($isReceivedByUser ? 'Directeur' : 'Structure');

        $extra = [
            'layout'     => 'layouts.directeur',
            'cibleLabel' => $cibleLabel,
            'cibleType'  => $cibleType,
            'backRoute'  => route('directeur.mon-espace', ['tab' => 'dashboard']),
            'breadcrumb' => 'Directeur · Évaluations',
            'pdfRoute'   => 'directeur.evaluations.pdf',
        ];

        if ($isReceived) {
            $extra['isAssignee']       = true;
            $extra['statutRoute']      = 'directeur.evaluations.statut';
            $extra['reclamerRoute']    = 'directeur.evaluations.reclamer';
            $extra['commentaireRoute'] = 'directeur.evaluations.commentaire';
        } else {
            $extra['editRoute']      = 'directeur.evaluations.edit';
            $extra['soumettreRoute'] = 'directeur.evaluations.submit';
            $extra['destroyRoute']   = 'directeur.evaluations.destroy';
        }

        return $this->resolveEvaluationView($evaluation, $extra);
    }

    private function directeurEdit(Evaluation $evaluation): View
    {
        $ctx = $this->directeurAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $target = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        $cibleNom = $ident?->nom_prenom
            ?? ($target instanceof Service || $target instanceof Agence || $target instanceof Caisse
                ? ($target->nom ?? '-')
                : '-');
        $cibleType = match (true) {
            $target instanceof Service => 'Service',
            $target instanceof Agence  => 'Agence',
            $target instanceof Caisse  => 'Caisse',
            default                    => 'Structure',
        };

        return view('evaluations.edit', [
            'layout'                      => 'layouts.directeur',
            'heroSubtitle'                => 'Directeur · Évaluations',
            'formAction'                  => route('directeur.evaluations.update', $evaluation),
            'backUrl'                     => route('directeur.evaluations.show', $evaluation),
            'evalueLabel'                 => $cibleType,
            'evaluateurLabel'             => $ctx->getRoleLabel(),
            'evaluation'                  => $evaluation,
            'ident'                       => $ident,
            'openAnnee'                   => Annee::currentOpen(),
            'openSemestre'                => null,
            'objectiveOptions'            => (function () use ($target) {
                $agentId = match (true) {
                    $target instanceof Service => $target->chef_agent_id,
                    $target instanceof Agence  => $target->chef_agent_id,
                    $target instanceof Caisse  => $target->directeur_agent_id,
                    default                    => null,
                };
                $managerUser = $agentId ? User::where('agent_id', $agentId)->first() : null;
                return $managerUser ? $this->getObjectiveOptionsForUser($managerUser->id) : [];
            })(),
            'existingObjectiveCriteria'   => $objCriteria,
            'existingSubjectiveCriteria'  => $subjCriteria,
            'oldFormations'               => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'              => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                    => $cibleNom,
            'cibleType'                   => $cibleType,
        ]);
    }

    private function directeurUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->directeurAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            $target = $evaluation->evaluable;
            if ($target) {
                $agentId = match (true) {
                    $target instanceof Service => $target->chef_agent_id,
                    $target instanceof Agence  => $target->chef_agent_id,
                    $target instanceof Caisse  => $target->directeur_agent_id,
                    default                    => null,
                };
                $user = $agentId ? User::where('agent_id', $agentId)->first() : null;
                if ($user) {
                    Alerte::notifier($user->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $evaluation));
                }
            }
            return redirect()->route('directeur.mon-espace', ['tab' => 'dashboard'])
                ->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('directeur.evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour.');
    }

    private function directeurSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->directeurAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        // Notify relevant user
        $target = $evaluation->evaluable;
        if ($target) {
            $agentId = match (true) {
                $target instanceof Service => $target->chef_agent_id,
                $target instanceof Agence  => $target->chef_agent_id,
                $target instanceof Caisse  => $target->directeur_agent_id,
                default                    => null,
            };
            $user = $agentId ? User::where('agent_id', $agentId)->first() : null;
            if ($user) {
                Alerte::notifier($user->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $evaluation));
            }
        }

        return redirect()->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('success', 'Évaluation soumise.');
    }

    private function directeurStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->directeurAuthorizeReceived($evaluation);

        $action = $request->input('action');
        $request->validate([
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $statut = $action === 'accepter' ? 'valide' : 'reclamation';
        $fields = ['statut' => $statut];
        if ($statut === 'reclamation') {
            $fields['motif_refus']        = $request->input('motif_refus');
            $fields['statut_reclamation'] = 'en_attente';
        }
        $evaluation->update($fields);

        $labelStatut = $statut === 'valide' ? 'validée' : 'refusée';
        Alerte::notifier($evaluation->evaluateur_id, "L'évaluation du directeur a été {$labelStatut}.", '', 'haute', route('directeur.evaluations.show', $evaluation));

        if ($statut === 'reclamation') {
            $rhUser = User::where('role', 'RH')->first();
            if ($rhUser) {
                Alerte::notifier($rhUser->id, "Une évaluation directeur a été refusée.", '', 'haute', route('rh.evaluations.show', $evaluation));
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    private function directeurReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->directeurAuthorizeReceived($evaluation);

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->update([
            'statut'             => 'reclamation',
            'reclamation'        => $request->input('reclamation'),
            'statut_reclamation' => 'en_attente',
        ]);

        Alerte::notifier($evaluation->evaluateur_id, 'Le directeur a déposé une réclamation.', '', 'haute', route('directeur.evaluations.show', $evaluation));
        $rhUser = User::where('role', 'RH')->first();
        if ($rhUser) {
            Alerte::notifier($rhUser->id, 'Une réclamation a été soumise (Directeur).', '', 'haute', route('rh.evaluations.show', $evaluation));
        }

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function directeurCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        $this->directeurAuthorizeReceived($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function directeurExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $ctx = $this->getDirecteurContext();

        $isReceived = ($evaluation->evaluable_type === $ctx->modelClass && (int) $evaluation->evaluable_id === $ctx->getId())
            || ($evaluation->evaluable_type === User::class && (int) $evaluation->evaluable_id === Auth::id());
        $validTypes = [Service::class, Agence::class, Caisse::class];
        $isCreated  = in_array($evaluation->evaluable_type, $validTypes, true)
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }
        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-directeur.pdf");
    }

    private function directeurDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->directeurAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // DIRECTEUR SECRÉTAIRE — private methods
    // =========================================================================

    private function directeurSecretaireAuthorize(Evaluation $evaluation): DirecteurEntity
    {
        $ctx = $this->getDirecteurContext();
        if ($evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== (int) $ctx->getSecretaireUserId() ||
            (int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }
        return $ctx;
    }

    private function directeurSecretaireCreate(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $ctx        = $this->getDirecteurContext();
        $secretaire = User::find($ctx->getSecretaireUserId());

        if (! $secretaire) {
            return back()->with('error', 'Aucun secrétaire trouvé.');
        }

        $direction  = Direction::where('directeur_agent_id', Auth::user()?->agent_id)->first();
        $secAgent   = $secretaire->agent_id ? \App\Models\Agent::find($secretaire->agent_id) : null;

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.directeur',
            'heroSubtitle'              => 'Directeur · Secrétaire',
            'formAction'                => route('directeur.subordonnes.secretaire.evaluations.store'),
            'backUrl'                   => route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            'evalueLabel'               => 'Secrétaire',
            'evaluateurLabel'           => $ctx->getRoleLabel(),
            'targetType'                => 'secretaire',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($secretaire->id),
            'secretaire'                => $secretaire,
            'direction'                 => $direction,
            'prefilledMatricule'        => $secAgent?->matricule ?? '',
            'prefilledNomPrenom'        => $secAgent ? trim(($secAgent->prenom ?? '') . ' ' . ($secAgent->nom ?? '')) : $secretaire->name,
            'prefilledEmploi'           => $secAgent?->role ?? 'Secrétaire',
            'entiteNom'                 => $ctx->getNom(),
            'prefilledDirectionService' => 'Secrétariat',
        ]));
    }

    private function directeurSecretaireStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $ctx        = $this->getDirecteurContext();
        $secretaire = User::findOrFail($ctx->getSecretaireUserId());

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(User::class, $secretaire->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => User::class,
            'evaluable_id'   => $secretaire->id,
            'evaluable_role' => 'secretaire',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation créée.');
    }

    private function directeurSecretaireShow(Evaluation $evaluation): View
    {
        $ctx = $this->directeurSecretaireAuthorize($evaluation);
        $secretaire = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($secretaire?->name ?? '-');

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.directeur',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => 'Secrétaire',
            'backRoute'      => route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            'breadcrumb'     => 'Directeur · Secrétaire',
            'editRoute'      => 'directeur.subordonnes.secretaire.evaluations.edit',
            'soumettreRoute' => 'directeur.subordonnes.secretaire.evaluations.submit',
            'destroyRoute'   => 'directeur.subordonnes.secretaire.evaluations.destroy',
            'pdfRoute'       => 'directeur.subordonnes.secretaire.evaluations.pdf',
        ]);
    }

    private function directeurSecretaireEdit(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.creer');
        $ctx = $this->directeurSecretaireAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $secretaire = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                     => 'layouts.directeur',
            'heroSubtitle'               => 'Directeur · Secrétaire',
            'formAction'                 => route('directeur.subordonnes.secretaire.evaluations.update', $evaluation),
            'backUrl'                    => route('directeur.subordonnes.secretaire.evaluations.show', $evaluation),
            'evalueLabel'                => 'Secrétaire',
            'evaluateurLabel'            => $ctx->getRoleLabel(),
            'evaluation'                 => $evaluation,
            'ident'                      => $ident,
            'openAnnee'                  => Annee::currentOpen(),
            'openSemestre'               => null,
            'objectiveOptions'           => $secretaire ? $this->getObjectiveOptionsForUser($secretaire->id) : [],
            'existingObjectiveCriteria'  => $objCriteria,
            'existingSubjectiveCriteria' => $subjCriteria,
            'oldFormations'              => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'             => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                   => $ident?->nom_prenom ?? ($secretaire?->name ?? '-'),
            'cibleType'                  => 'Secrétaire',
        ]);
    }

    private function directeurSecretaireUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $this->directeurSecretaireAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));
            return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('directeur.subordonnes.secretaire.evaluations.show', $evaluation)->with('success', 'Évaluation mise à jour.');
    }

    private function directeurSecretaireSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->directeurSecretaireAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation soumise.');
    }

    private function directeurSecretaireDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->directeurSecretaireAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation supprimée.');
    }

    // =========================================================================
    // CHEF — private methods
    // =========================================================================

    private function getChefContext(): ChefEntity
    {
        return ChefEntity::resolveOrFail(Auth::user());
    }

    private function chefAuthorizeCreated(Evaluation $evaluation): ChefEntity
    {
        $ctx = $this->getChefContext();

        if (strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager') {
            abort(403);
        }
        if ((int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }

        // Agent owned by chef
        if ($evaluation->evaluable_type === Agent::class) {
            $agent = Agent::find($evaluation->evaluable_id);
            if (! $agent || ! $ctx->agentOwnedBy($agent)) {
                abort(403);
            }
        } elseif ($evaluation->evaluable_type === Guichet::class) {
            if ($ctx->type !== 'agence') {
                abort(403);
            }
            $guichet = Guichet::find($evaluation->evaluable_id);
            if (! $guichet || (int) $guichet->agence_id !== $ctx->getId()) {
                abort(403);
            }
        } else {
            abort(403);
        }

        return $ctx;
    }

    private function chefAuthorizeReceived(Evaluation $evaluation): ChefEntity
    {
        $ctx = $this->getChefContext();

        $isReceivedAsAgent = $evaluation->evaluable_type === Agent::class
            && $ctx->agent?->id
            && (int) $evaluation->evaluable_id === $ctx->agent?->id;

        $isReceivedAsUser = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        $isReceivedAsStructure = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        if (! $isReceivedAsAgent && ! $isReceivedAsUser && ! $isReceivedAsStructure) {
            abort(403);
        }

        return $ctx;
    }

    /**
     * AJAX — retourne les fiches d'objectifs acceptées d'un agent donné.
     * Utilisé par le formulaire de création d'évaluation Chef pour recharger
     * dynamiquement le sélecteur de fiches quand l'agent change.
     */
    /**
     * AJAX — retourne les fiches d'objectifs d'une entité structurelle (Service, Agence, Caisse).
     * Utilisé par le formulaire de création d'évaluation Directeur.
     */
    public function objectivesForEntity(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('evaluations.creer');
        $ctx = $this->getDirecteurContext();

        $entity = null;
        if ($caisseId = $request->integer('caisse_id') ?: null) {
            $entity = \App\Models\Caisse::find($caisseId);
            if ($entity && ! $ctx->caisseOwnedBy($entity)) {
                abort(403);
            }
        } elseif ($agenceId = $request->integer('agence_id') ?: null) {
            $entity = \App\Models\Agence::find($agenceId);
            if ($entity && ! $ctx->agenceOwnedBy($entity)) {
                abort(403);
            }
        } elseif ($serviceId = $request->integer('service_id') ?: null) {
            $entity = \App\Models\Service::find($serviceId);
            if ($entity && ! $ctx->serviceOwnedBy($entity)) {
                abort(403);
            }
        }

        if (! $entity) {
            return response()->json([]);
        }

        return response()->json($this->getObjectiveOptionsForEntity($entity));
    }

    public function objectivesForAgent(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('evaluations.creer');
        $ctx     = $this->getChefContext();
        $agentId = $request->integer('agent_id');

        // Sécurité : l'agent doit appartenir au périmètre du chef
        $agent = Agent::findOrFail($agentId);
        if (! $ctx->agentOwnedBy($agent)) {
            abort(403);
        }

        $options = $this->getObjectiveOptionsForAgent($agentId);

        return response()->json($options);
    }

    private function chefCreate(Request $request): View
    {
        $this->authorize('evaluations.creer');
        $ctx    = $this->getChefContext();
        $agents = $ctx->getAgents();
        AgentStructure::loadRelations($agents);

        // Filter already evaluated this period
        $anneeId  = $request->integer('annee_id');
        $semestre = $request->input('semestre', 'S1');

        if ($anneeId) {
            $evaluatedIds = Evaluation::where('evaluable_type', Agent::class)
                ->where('evaluateur_id', Auth::id())
                ->where('annee_id', $anneeId)
                ->where('semestre', $semestre)
                ->pluck('evaluable_id')
                ->toArray();
            $agents = $agents->filter(fn ($a) => ! in_array($a->id, $evaluatedIds, true));
        }

        $parentNom  = $ctx->getParentNom() ?: $ctx->getNom();
        $agentsJson = $agents->map(fn ($a) => [
            'id'               => $a->id,
            'nom_prenom'       => trim(($a->prenom ?? '') . ' ' . ($a->nom ?? '')),
            'role'             => $a->role ?? '',
            'matricule'        => $a->matricule ?? '',
            'emploi'           => in_array($a->role, ['Agent', 'Conseiller DG'], true)
                                    ? ($a->poste ?? $a->role ?? '')
                                    : ($a->role ?? ''),
            'entite_nom'       => $parentNom,
            'direction_service'=> $ctx->getNom(),
        ])->values()->toArray();

        $prefilledAgentId = $request->integer('agent_id') ?: null;

        return view('evaluations.create', $this->createViewData([
            'layout'           => 'layouts.chef',
            'heroSubtitle'     => 'Chef · ' . $ctx->getTypeLabel() . ' ' . $ctx->getNom(),
            'formAction'       => route('chef.evaluations.store'),
            'backUrl'          => route('chef.equipe'),
            'evalueLabel'      => 'Agent',
            'evaluateurLabel'  => $ctx->getRoleLabel(),
            'targetType'       => 'agent',
            'agents'           => $agents->values(),
            'agentsJson'       => $agentsJson,
            'entiteNom'        => $ctx->getNom(),
            'lockAgent'        => false,
            'prefilledAgentId' => $prefilledAgentId,
            'objectiveOptions' => $prefilledAgentId
                ? $this->getObjectiveOptionsForAgent($prefilledAgentId)
                : [],
        ]));
    }

    private function chefStore(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $ctx   = $this->getChefContext();
        $agent = Agent::findOrFail($request->integer('agent_id'));

        if (! $ctx->agentOwnedBy($agent)) {
            abort(403);
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(Agent::class, $agent->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour cet agent ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => Agent::class,
            'evaluable_id'   => $agent->id,
            'evaluable_role' => 'manager',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        return redirect()->route('chef.evaluations.show', $evaluation)
            ->with('success', 'Évaluation créée.');
    }

    private function chefShow(Evaluation $evaluation): View
    {
        $ctx = $this->getChefContext();

        $isReceivedAsAgent = $evaluation->evaluable_type === Agent::class
            && $ctx->agent?->id
            && (int) $evaluation->evaluable_id === $ctx->agent?->id;

        $isReceivedAsUser = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        $isReceivedAsStructure = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        $isReceived = $isReceivedAsAgent || $isReceivedAsUser || $isReceivedAsStructure;

        $validCreatedTypes = [Agent::class, Guichet::class];
        $isCreated = in_array($evaluation->evaluable_type, $validCreatedTypes, true)
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager'
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        // Un évalué ne peut pas consulter une évaluation encore en brouillon
        if ($isReceived && $evaluation->statut === 'brouillon') {
            abort(403, 'Cette évaluation n\'est pas encore disponible.');
        }

        $target     = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($target?->name ?? ($target?->nom ?? '-'));
        $cibleType  = $isReceived ? 'Chef' : ($evaluation->evaluable_type === Guichet::class ? 'Guichet' : 'Agent');

        $extra = [
            'layout'     => 'layouts.chef',
            'cibleLabel' => $cibleLabel,
            'cibleType'  => $cibleType,
            'breadcrumb' => 'Chef · Évaluations',
            'pdfRoute'   => 'chef.evaluations.pdf',
        ];

        if ($isReceived) {
            $extra['backRoute']       = route('chef.mon-espace') . '?tab=evaluations';
            $extra['isAssignee']      = true;
            $extra['statutRoute']     = 'chef.evaluations.statut';
            $extra['reclamerRoute']   = 'chef.evaluations.reclamer';
            $extra['commentaireRoute']= 'chef.evaluations.commentaire';
        } else {
            $agentId = $evaluation->evaluable_type === Agent::class ? $evaluation->evaluable_id : null;
            $extra['backRoute']      = $agentId
                ? route('chef.agent.show', $agentId)
                : route('chef.equipe');
            $extra['editRoute']      = 'chef.evaluations.edit';
            $extra['soumettreRoute'] = 'chef.evaluations.submit';
            $extra['destroyRoute']   = 'chef.evaluations.destroy';
        }

        return $this->resolveEvaluationView($evaluation, $extra);
    }

    private function chefEdit(Evaluation $evaluation): View
    {
        $ctx = $this->chefAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $target = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        $cibleNom = $ident?->nom_prenom
            ?? ($target instanceof Agent
                ? trim(($target->prenom ?? '') . ' ' . ($target->nom ?? ''))
                : ($target?->nom ?? '-'));
        $cibleType = $evaluation->evaluable_type === Guichet::class ? 'Guichet' : 'Agent';

        $agentId = $evaluation->evaluable_type === Agent::class ? $evaluation->evaluable_id : null;
        $backUrl = $agentId
            ? route('chef.agent.show', $agentId)
            : route('chef.equipe');

        return view('evaluations.edit', [
            'layout'                      => 'layouts.chef',
            'heroSubtitle'                => 'Chef · ' . $ctx->getTypeLabel() . ' ' . $ctx->getNom(),
            'formAction'                  => route('chef.evaluations.update', $evaluation),
            'backUrl'                     => route('chef.evaluations.show', $evaluation),
            'evalueLabel'                 => $cibleType,
            'evaluateurLabel'             => $ctx->getRoleLabel(),
            'evaluation'                  => $evaluation,
            'ident'                       => $ident,
            'openAnnee'                   => Annee::currentOpen(),
            'openSemestre'                => null,
            'objectiveOptions'            => $agentId
                ? $this->getObjectiveOptionsForAgent($agentId)
                : [],
            'existingObjectiveCriteria'   => $objCriteria,
            'existingSubjectiveCriteria'  => $subjCriteria,
            'oldFormations'               => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'              => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                    => $cibleNom,
            'cibleType'                   => $cibleType,
        ]);
    }

    private function chefUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->chefAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            if ($evaluation->evaluable_type === Agent::class) {
                $agent = Agent::find($evaluation->evaluable_id);
                $agentUserId = $agent ? User::where('agent_id', $agent->id)->value('id') : null;
                if ($agentUserId) {
                    Alerte::notifier($agentUserId, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));
                }
            } elseif ($evaluation->evaluable_type === Guichet::class) {
                $guichet = Guichet::find($evaluation->evaluable_id);
                if ($guichet && $guichet->chef_agent_id) {
                    $chefGuichet = User::where('agent_id', $guichet->chef_agent_id)->first();
                    if ($chefGuichet) {
                        Alerte::notifier($chefGuichet->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $evaluation));
                    }
                }
            }
            return redirect()->route('chef.mon-espace')
                ->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('chef.evaluations.show', $evaluation)
            ->with('success', 'Évaluation mise à jour.');
    }

    private function chefSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->chefAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        // Notify: agent user or chef de guichet
        if ($evaluation->evaluable_type === Agent::class) {
            $agent = Agent::find($evaluation->evaluable_id);
            $agentUserId = $agent ? User::where('agent_id', $agent->id)->value('id') : null;
            if ($agentUserId) {
                Alerte::notifier($agentUserId, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));
            }
        } elseif ($evaluation->evaluable_type === Guichet::class) {
            $guichet = Guichet::find($evaluation->evaluable_id);
            if ($guichet && $guichet->chef_agent_id) {
                $chefGuichet = User::where('agent_id', $guichet->chef_agent_id)->first();
                if ($chefGuichet) {
                    Alerte::notifier($chefGuichet->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $evaluation));
                }
            }
        }

        return redirect()->route('chef.mon-espace')
            ->with('success', 'Évaluation soumise.');
    }

    private function chefStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->chefAuthorizeReceived($evaluation);

        $action = $request->input('action');
        $request->validate([
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $statut = $action === 'accepter' ? 'valide' : 'reclamation';
        $fields = ['statut' => $statut];
        if ($statut === 'reclamation') {
            $fields['motif_refus']        = $request->input('motif_refus');
            $fields['statut_reclamation'] = 'en_attente';
        }
        $evaluation->update($fields);

        $labelStatut = $statut === 'valide' ? 'validée' : 'refusée';
        Alerte::notifier($evaluation->evaluateur_id, "L'évaluation du chef a été {$labelStatut}.", '', 'haute', route('chef.evaluations.show', $evaluation));

        if ($statut === 'reclamation') {
            $rhUser = User::where('role', 'RH')->first();
            if ($rhUser) {
                Alerte::notifier($rhUser->id, "Une évaluation chef a été refusée.", '', 'haute', route('rh.evaluations.show', $evaluation));
            }
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    private function chefReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->chefAuthorizeReceived($evaluation);

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->update([
            'statut'             => 'reclamation',
            'reclamation'        => $request->input('reclamation'),
            'statut_reclamation' => 'en_attente',
        ]);

        Alerte::notifier($evaluation->evaluateur_id, 'Le chef a déposé une réclamation.', '', 'haute', route('chef.evaluations.show', $evaluation));
        $rhUser = User::where('role', 'RH')->first();
        if ($rhUser) {
            Alerte::notifier($rhUser->id, 'Une réclamation a été soumise (Chef).', '', 'haute', route('rh.evaluations.show', $evaluation));
        }

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function chefCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        $this->chefAuthorizeReceived($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function chefExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $ctx = $this->getChefContext();

        $isReceived = ($evaluation->evaluable_type === $ctx->modelClass && (int) $evaluation->evaluable_id === $ctx->getId())
            || ($evaluation->evaluable_type === Agent::class && $ctx->agent?->id && (int) $evaluation->evaluable_id === $ctx->agent?->id)
            || ($evaluation->evaluable_type === User::class && (int) $evaluation->evaluable_id === Auth::id());
        $isCreated = in_array($evaluation->evaluable_type, [Agent::class, Guichet::class], true)
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }
        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $pdfView = view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf';
        return $this->pdfResponse($evaluation, $pdfView, "evaluation-{$evaluation->id}-chef.pdf");
    }

    private function chefDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->chefAuthorizeCreated($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('chef.mon-espace')
            ->with('success', 'Évaluation supprimée.');
    }

    private function chefCreateForGuichet(Guichet $guichet): View
    {
        $this->authorize('evaluations.creer');
        $ctx = $this->getChefContext();

        if ($ctx->type !== 'agence' || (int) $guichet->agence_id !== $ctx->getId()) {
            abort(403);
        }

        $guichetChef = $guichet->chef ?? null;

        return view('evaluations.create', $this->createViewData([
            'layout'           => 'layouts.chef',
            'heroSubtitle'     => 'Chef · Agence ' . $ctx->getNom(),
            'formAction'       => route('chef.subordonnes.guichet.evaluations.store'),
            'backUrl'          => url()->previous(),
            'evalueLabel'      => 'Chef de Guichet',
            'evaluateurLabel'  => $ctx->getRoleLabel(),
            'targetType'       => 'guichet',
            'guichet'          => $guichet,
            'entiteNom'        => $ctx->getNom(),
            'objectiveOptions' => $guichetChef ? $this->getObjectiveOptionsForUser(
                User::where('agent_id', $guichetChef->id)->value('id') ?? 0
            ) : [],
        ]));
    }

    private function chefStoreForGuichet(Request $request): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $ctx    = $this->getChefContext();
        $guichet = Guichet::findOrFail($request->integer('guichet_id'));

        if ($ctx->type !== 'agence' || (int) $guichet->agence_id !== $ctx->getId()) {
            abort(403);
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(Guichet::class, $guichet->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce guichet ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => Guichet::class,
            'evaluable_id'   => $guichet->id,
            'evaluable_role' => 'manager',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        if ($guichet->chef_agent_id) {
            $chefGuichet = User::where('agent_id', $guichet->chef_agent_id)->first();
            if ($chefGuichet) {
                Alerte::notifier($chefGuichet->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $evaluation));
            }
        }

        return redirect()->route('chef.evaluations.show', $evaluation)
            ->with('success', 'Évaluation guichet créée.');
    }

    // =========================================================================
    // PERSONNEL — private methods
    // =========================================================================

    private function personnelAuthorize(Evaluation $evaluation): void
    {
        $user = Auth::user();
        $isForUser  = $evaluation->evaluable_type === User::class
            && (int) $evaluation->evaluable_id === Auth::id();
        $isForAgent = $evaluation->evaluable_type === Agent::class
            && $user->agent_id
            && (int) $evaluation->evaluable_id === $user->agent_id;

        if (! $isForUser && ! $isForAgent) {
            abort(403);
        }
    }

    private function personnelShow(Evaluation $evaluation): View|RedirectResponse
    {
        $this->personnelAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $target     = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: (Auth::user()?->name ?? '-');
        $cibleType  = 'Personnel';

        return $this->resolveEvaluationView($evaluation, [
            'layout'          => 'layouts.personnel',
            'cibleLabel'      => $cibleLabel,
            'cibleType'       => $cibleType,
            'backRoute'       => route('personnel.dashboard') . '?tab=evaluations',
            'breadcrumb'      => 'Personnel · Mon évaluation',
            'isAssignee'      => true,
            'statutRoute'     => 'personnel.evaluations.statut',
            'reclamerRoute'   => 'personnel.evaluations.reclamer',
            'commentaireRoute'=> 'personnel.evaluations.commentaire',
            'pdfRoute'        => 'personnel.evaluations.pdf',
        ]);
    }

    private function personnelStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        $this->personnelAuthorize($evaluation);

        $action = $request->input('action');
        $request->validate([
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $statut = $action === 'accepter' ? 'valide' : 'reclamation';
        $fields = ['statut' => $statut];
        if ($statut === 'reclamation') {
            $fields['motif_refus']        = $request->input('motif_refus');
            $fields['statut_reclamation'] = 'en_attente';
        }
        $evaluation->update($fields);

        Alerte::notifier($evaluation->evaluateur_id, "L'évaluation du personnel a été mise à jour.", '', 'haute', route('chef.evaluations.show', $evaluation));

        return back()->with('success', 'Statut mis à jour.');
    }

    private function personnelReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->personnelAuthorize($evaluation);

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->update([
            'statut'             => 'reclamation',
            'reclamation'        => $request->input('reclamation'),
            'statut_reclamation' => 'en_attente',
        ]);

        Alerte::notifier($evaluation->evaluateur_id, 'Un agent a déposé une réclamation.', '', 'haute', route('chef.evaluations.show', $evaluation));
        $rhUser = User::where('role', 'RH')->first();
        if ($rhUser) {
            Alerte::notifier($rhUser->id, 'Une réclamation a été soumise (Agent).', '', 'haute', route('rh.evaluations.show', $evaluation));
        }

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function personnelCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        $this->personnelAuthorize($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function personnelExportPdf(Evaluation $evaluation): Response
    {
        $this->personnelAuthorize($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $pdfView = view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf';
        return $this->pdfResponse($evaluation, $pdfView, "evaluation-{$evaluation->id}-personnel.pdf");
    }

    // =========================================================================
    // RH — private methods
    // =========================================================================

    private function rhShow(Evaluation $evaluation): View
    {
        $target     = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($target?->name ?? ($target?->nom ?? '-'));
        $cibleType  = 'Personnel';

        return $this->resolveEvaluationView($evaluation, [
            'layout'             => 'layouts.rh',
            'cibleLabel'         => $cibleLabel,
            'cibleType'          => $cibleType,
            'backRoute'          => route('rh.dashboard') . '?tab=evaluations',
            'breadcrumb'         => 'RH · Évaluations',
            'subjectiveTemplates'=> collect(),
        ]);
    }

    // =========================================================================
    // ASSISTANTE — private methods
    // =========================================================================

    private function assertIsAssistante(): void
    {
        if (Auth::user()?->role !== 'Assistante_Dg') {
            abort(403);
        }
    }

    private function findAssistanteSecretaire(): ?User
    {
        return User::where('role', 'Secretaire_Assistante')->first();
    }

    private function assistanteAuthorize(Evaluation $evaluation): void
    {
        $this->assertIsAssistante();
        $secretaire = $this->findAssistanteSecretaire();
        if ($evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== (int) $secretaire?->id ||
            (int) $evaluation->evaluateur_id !== Auth::id()) {
            abort(403);
        }
    }

    private function assistanteCreate(Request $request): View
    {
        $this->assertIsAssistante();
        $this->authorize('evaluations.creer');
        $secretaire = $this->findAssistanteSecretaire();

        if (! $secretaire) {
            return back()->with('error', 'Aucun secrétaire trouvé.');
        }

        $secAgent = $secretaire->agent_id ? \App\Models\Agent::find($secretaire->agent_id) : null;
        $entite   = \App\Models\Entite::where('assistante_agent_id', Auth::user()->agent_id)->first()
            ?? \App\Models\Entite::latest()->first();

        return view('evaluations.create', $this->createViewData([
            'layout'                    => 'layouts.subordonne',
            'heroSubtitle'              => 'Assistante DG · Secrétaire',
            'formAction'                => route('assistante.secretaire.evaluations.store'),
            'backUrl'                   => route('assistante.secretaire', ['tab' => 'evaluations']),
            'evalueLabel'               => 'Secrétaire',
            'evaluateurLabel'           => 'Assistante DG',
            'targetType'                => 'secretaire',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($secretaire->id),
            'secretaire'                => $secretaire,
            'direction'                 => null,
            'prefilledMatricule'        => $secAgent?->matricule ?? '',
            'prefilledNomPrenom'        => $secAgent ? trim(($secAgent->prenom ?? '') . ' ' . ($secAgent->nom ?? '')) : $secretaire->name,
            'prefilledEmploi'           => $secAgent?->role ?? 'Secrétaire',
            'entiteNom'                 => $entite?->nom ?? '',
            'prefilledDirectionService' => 'Secrétariat',
        ]));
    }

    private function assistanteStore(Request $request): RedirectResponse
    {
        $this->assertIsAssistante();
        $this->authorize('evaluations.creer');
        $secretaire = $this->findAssistanteSecretaire();

        if (! $secretaire) {
            return back()->with('error', 'Aucun secrétaire trouvé.');
        }

        $data = $request->validate([
            'annee_id' => ['required', 'integer', 'exists:annees,id'],
            'semestre' => ['required', 'in:S1,S2'],
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate(User::class, $secretaire->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => User::class,
            'evaluable_id'   => $secretaire->id,
            'evaluable_role' => 'secretaire',
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        // Assistante notifies on CREATE (unlike directeur which notifies on submit)
        Alerte::notifier($secretaire->id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));

        return redirect()->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation créée.');
    }

    private function assistanteEdit(Evaluation $evaluation): View
    {
        $this->authorize('evaluations.creer');
        $this->assistanteAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $secretaire = $evaluation->evaluable;
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                     => 'layouts.subordonne',
            'heroSubtitle'               => 'Assistante DG · Secrétaire',
            'formAction'                 => route('assistante.secretaire.evaluations.update', $evaluation),
            'backUrl'                    => route('assistante.secretaire.evaluations.show', $evaluation),
            'evalueLabel'                => 'Secrétaire',
            'evaluateurLabel'            => 'Assistante DG',
            'evaluation'                 => $evaluation,
            'ident'                      => $ident,
            'openAnnee'                  => Annee::currentOpen(),
            'openSemestre'               => null,
            'objectiveOptions'           => $secretaire ? $this->getObjectiveOptionsForUser($secretaire->id) : [],
            'existingObjectiveCriteria'  => $objCriteria,
            'existingSubjectiveCriteria' => $subjCriteria,
            'oldFormations'              => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'             => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                   => $ident?->nom_prenom ?? ($secretaire?->name ?? '-'),
            'cibleType'                  => 'Secrétaire',
        ]);
    }

    private function assistanteUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.creer');
        $this->assistanteAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $this->persistFullEvaluationData($evaluation, $request);

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');
            $evaluation->update(['statut' => 'soumis']);
            Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));
            return redirect()->route('assistante.secretaire', ['tab' => 'evaluations'])->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route('assistante.secretaire.evaluations.show', $evaluation)->with('success', 'Évaluation mise à jour.');
    }

    private function assistanteShow(Evaluation $evaluation): View
    {
        $this->assistanteAuthorize($evaluation);
        $secretaire = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($secretaire?->name ?? '-');

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => 'layouts.subordonne',
            'cibleLabel'     => $cibleLabel,
            'cibleType'      => 'Secrétaire',
            'backRoute'      => route('assistante.secretaire', ['tab' => 'evaluations']),
            'breadcrumb'     => 'Assistante · Secrétaire',
            'editRoute'      => 'assistante.secretaire.evaluations.edit',
            'soumettreRoute' => 'assistante.secretaire.evaluations.submit',
            'destroyRoute'   => 'assistante.secretaire.evaluations.destroy',
            'pdfRoute'       => 'assistante.secretaire.evaluations.pdf',
        ]);
    }

    private function assistanteExportPdf(Evaluation $evaluation): Response
    {
        $this->assistanteAuthorize($evaluation);

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-assistante.pdf");
    }

    private function directeurSecretaireExportPdf(Evaluation $evaluation): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        $this->directeurSecretaireAuthorize($evaluation);

        return $this->pdfResponse($evaluation, 'dg.evaluations.pdf', "evaluation-{$evaluation->id}-secretaire-dt.pdf");
    }

    private function assistanteSubmit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        $this->assistanteAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier($evaluation->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $evaluation));

        return redirect()->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation soumise.');
    }

    private function assistanteDestroy(Evaluation $evaluation): RedirectResponse
    {
        $this->assistanteAuthorize($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $evaluation->delete();

        return redirect()->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('success', 'Évaluation supprimée.');
    }
}
