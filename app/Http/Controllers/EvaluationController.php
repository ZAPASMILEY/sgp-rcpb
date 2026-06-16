<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Chef\ChefEntity;
use App\Http\Controllers\Directeur\DirecteurEntity;
use App\Http\Controllers\Support\RoleEvaluationAssignerConfig;
use App\Http\Controllers\Support\RoleEvaluationReceivedConfig;
use App\Http\Controllers\Support\RoleEvaluationStoreConfig;
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

    private const ALLOWED_ROLES_DGA = ['DGA', 'Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'];
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

        // Normalise l'entité pour le personnel du siège (identification->direction peut contenir
        // l'ancien nom complet de l'entité au lieu de "Faitière des Caisses Populaires").
        if ($evaluation->identification) {
            $rolesSiege = ['DG', 'DGA', 'Assistante_Dg', 'Conseillers_Dg',
                           'Secretaire_Assistante', 'Directeur_Direction'];
            $evaluableUser = $evaluation->evaluable instanceof User ? $evaluation->evaluable : null;
            if ($evaluableUser && in_array($evaluableUser->role, $rolesSiege, true)) {
                $evaluation->identification->direction = 'Faitière des Caisses Populaires';
            }
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
     * Retourne la fiche d'objectifs de l'année donnée si elle n'est PAS acceptée.
     * Pour un Agent, on cherche aussi via son compte User.
     * Retourne null si aucune fiche bloquante n'existe.
     */
    private function ficheObjectifNonAcceptee(string $evaluableType, int $evaluableId, int $anneeId): ?FicheObjectif
    {
        $fiche = FicheObjectif::where('assignable_type', $evaluableType)
            ->where('assignable_id', $evaluableId)
            ->where('annee_id', $anneeId)
            ->whereNotIn('statut', ['acceptee'])
            ->whereNotNull('statut')
            ->latest()
            ->first();

        if ($fiche) {
            return $fiche;
        }

        // Pour un Agent, vérifier aussi la fiche assignée à son compte User
        if ($evaluableType === Agent::class) {
            $user = \App\Models\User::where('agent_id', $evaluableId)->first();
            if ($user) {
                return FicheObjectif::where('assignable_type', \App\Models\User::class)
                    ->where('assignable_id', $user->id)
                    ->where('annee_id', $anneeId)
                    ->whereNotIn('statut', ['acceptee'])
                    ->whereNotNull('statut')
                    ->latest()
                    ->first();
            }
        }

        return null;
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
                'formations'          => is_array($ident['formations'] ?? null) ? $ident['formations'] : null,
                'experiences'         => is_array($ident['experiences'] ?? null) ? $ident['experiences'] : null,
                'date_prise_fonction' => $this->evaluationService->normalizeDateValue($ident['date_prise_fonction'] ?? ''),
            ]
        );
    }

    // =========================================================================
    // SHARED ASSIGNER OPERATIONS — méthodes unifiées côté évaluateur
    // =========================================================================

    private function sharedAssignerShow(Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): View
    {
        ($cfg->checkOwnership)($evaluation);
        $evaluation->loadMissing('evaluable', 'identification');

        return $this->resolveEvaluationView($evaluation, [
            'layout'         => $cfg->layout,
            'cibleLabel'     => ($cfg->getCibleNom)($evaluation),
            'cibleType'      => ($cfg->getCibleType)($evaluation),
            'backRoute'      => ($cfg->getBackRoute)($evaluation),
            'breadcrumb'     => $cfg->breadcrumb,
            'editRoute'      => $cfg->editRoute,
            'soumettreRoute' => $cfg->submitRoute,
            'destroyRoute'   => $cfg->destroyRoute,
            'pdfRoute'       => $cfg->pdfRoute,
        ]);
    }

    private function sharedEdit(Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): View
    {
        ($cfg->checkOwnership)($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $evaluation->loadMissing('evaluable', 'identification');
        [$objCriteria, $subjCriteria] = $this->serializeCriteriaForEdit($evaluation);
        $ident = $evaluation->identification;

        return view('evaluations.edit', [
            'layout'                     => $cfg->layout,
            'heroSubtitle'               => ($cfg->getHeroSubtitle)($evaluation),
            'formAction'                 => route($cfg->updateRoute, $evaluation),
            'backUrl'                    => route($cfg->showRoute, $evaluation),
            'destroyRoute'               => $cfg->destroyRoute,
            'evalueLabel'                => ($cfg->getEvalueLabel)($evaluation),
            'evaluateurLabel'            => ($cfg->getEvaluateurLabel)($evaluation),
            'evaluation'                 => $evaluation,
            'ident'                      => $ident,
            'openAnnee'                  => Annee::currentOpen(),
            'openSemestre'               => null,
            'objectiveOptions'           => ($cfg->getObjectiveOptions)($evaluation),
            'existingObjectiveCriteria'  => $objCriteria,
            'existingSubjectiveCriteria' => $subjCriteria,
            'oldFormations'              => old('identification.formations', $ident?->formations ?? null),
            'oldExperiences'             => old('identification.experiences', $ident?->experiences ?? []),
            'cibleNom'                   => ($cfg->getCibleNom)($evaluation),
            'cibleType'                  => ($cfg->getCibleType)($evaluation),
        ]);
    }

    private function sharedUpdate(Request $request, Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être modifiée.');
        }

        $request->validate([
            'identification.grade'           => ['required', 'string', 'max:255'],
            'identification.date_prise_fonction' => ['required', 'string'],
        ], [
            'identification.grade.required'                  => 'Le champ Grade est obligatoire.',
            'identification.date_prise_fonction.required'    => 'La date de prise de fonction est obligatoire.',
        ]);

        $this->persistFullEvaluationData($evaluation, $request);
        $evaluation->refresh(); // Recharge note_finale calculée par persistFullEvaluationData

        if ($request->boolean('_renvoyer')) {
            $this->authorize('evaluations.soumettre');

            if ((float) $evaluation->note_finale <= 0) {
                return back()->with('error', 'Impossible de soumettre : aucune note n\'a été saisie. Veuillez remplir les critères d\'évaluation avant de soumettre.');
            }

            $evaluation->update(['statut' => 'soumis']);
            ($cfg->notifyOnSubmit)($evaluation);
            return redirect(($cfg->resolveRedirectAfterSubmit)($evaluation))
                ->with('success', 'Évaluation mise à jour et soumise.');
        }

        return redirect()->route($cfg->showRoute, $evaluation)->with('success', 'Évaluation mise à jour.');
    }

    private function sharedSubmit(Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): RedirectResponse
    {
        $this->authorize('evaluations.soumettre');
        ($cfg->checkOwnership)($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Cette évaluation ne peut pas être soumise.');
        }

        if ((float) $evaluation->note_finale <= 0) {
            return back()->with('error', 'Impossible de soumettre : aucune note n\'a été saisie. Veuillez remplir les critères d\'évaluation avant de soumettre.');
        }

        $evaluation->update(['statut' => 'soumis']);
        ($cfg->notifyOnSubmit)($evaluation);

        return redirect(($cfg->resolveRedirectAfterSubmit)($evaluation))->with('success', 'Évaluation soumise.');
    }

    private function sharedDestroy(Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($evaluation);

        if (! in_array($evaluation->statut, Evaluation::EDITABLE_STATUTS, true)) {
            return back()->with('error', 'Impossible de supprimer cette évaluation.');
        }

        $redirectUrl = ($cfg->resolveRedirectAfterDestroy)($evaluation);
        $evaluation->delete();

        return redirect($redirectUrl)->with('success', 'Évaluation supprimée.');
    }

    private function sharedAssignerExportPdf(Evaluation $evaluation, RoleEvaluationAssignerConfig $cfg): Response
    {
        $this->authorize('evaluations.exporter-pdf');
        ($cfg->checkOwnership)($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, $cfg->pdfView, "evaluation-{$evaluation->id}-{$cfg->pdfFilenamePrefix}.pdf");
    }

    // =========================================================================
    // SHARED RECEIVED OPERATIONS — méthodes unifiées côté évalué
    // =========================================================================

    private function sharedStatut(Request $request, Evaluation $evaluation, RoleEvaluationReceivedConfig $cfg): RedirectResponse
    {
        $this->authorize('evaluations.accepter');
        ($cfg->checkOwnership)($evaluation);

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
        ($cfg->notifyStatut)($evaluation, $labelStatut);

        return back()->with('success', 'Statut mis à jour.');
    }

    private function sharedReclamer(Request $request, Evaluation $evaluation, RoleEvaluationReceivedConfig $cfg): RedirectResponse
    {
        ($cfg->checkOwnership)($evaluation);

        $request->validate(['reclamation' => ['required', 'string', 'max:1000']]);

        $evaluation->update([
            'statut'             => 'reclamation',
            'reclamation'        => $request->input('reclamation'),
            'statut_reclamation' => 'en_attente',
        ]);

        ($cfg->notifyReclamer)($evaluation);

        return back()->with('success', 'Réclamation enregistrée.');
    }

    private function sharedCommentaire(Request $request, Evaluation $evaluation, RoleEvaluationReceivedConfig $cfg): mixed
    {
        ($cfg->checkOwnership)($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }
        if ($evaluation->statut === 'valide') {
            return back()->with('info', 'Cette évaluation est déjà validée.');
        }

        $evaluation->update(['commentaires_evalue' => $request->input('commentaire')]);

        return back()->with('success', 'Commentaire enregistré.');
    }

    private function sharedReceivedExportPdf(Evaluation $evaluation, RoleEvaluationReceivedConfig $cfg): Response
    {
        ($cfg->checkOwnership)($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        return $this->pdfResponse($evaluation, $cfg->pdfView, "evaluation-{$evaluation->id}-{$cfg->pdfFilenamePrefix}.pdf");
    }

    private function sharedReceivedShow(Evaluation $evaluation, RoleEvaluationReceivedConfig $cfg): View|RedirectResponse
    {
        ($cfg->checkOwnership)($evaluation);

        if ($evaluation->statut === 'brouillon') {
            return back()->with('error', 'Cette évaluation est encore en brouillon et n\'est pas accessible.');
        }

        $subordonne = $evaluation->evaluable;
        $cibleLabel = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($subordonne?->name ?? '-');
        $cibleType  = ($cfg->getCibleType)($evaluation);

        return $this->resolveEvaluationView($evaluation, [
            'layout'           => $cfg->layout,
            'cibleLabel'       => $cibleLabel,
            'cibleType'        => $cibleType,
            'backRoute'        => ($cfg->getBackRoute)($evaluation),
            'breadcrumb'       => $cfg->breadcrumb,
            'isAssignee'       => true,
            'statutRoute'      => $cfg->statutRoute,
            'reclamerRoute'    => $cfg->reclamerRoute,
            'commentaireRoute' => $cfg->commentaireRoute,
            'pdfRoute'         => $cfg->showPdfRoute,
        ]);
    }

    private function sharedStore(Request $request, RoleEvaluationStoreConfig $cfg): RedirectResponse
    {
        $this->authorize('evaluations.creer');

        [$evaluableType, $evaluable] = ($cfg->resolveEvaluable)($request);

        if (! $evaluable) {
            $msg = $cfg->missingEvaluableMessage ?? 'Évalué introuvable.';
            return back()->with('error', $msg)->withInput();
        }

        $data = $request->validate([
            'annee_id'                           => ['required', 'integer', 'exists:annees,id'],
            'semestre'                           => ['required', 'in:S1,S2'],
            'identification.grade'               => ['required', 'string', 'max:255'],
            'identification.date_prise_fonction' => ['required', 'string'],
        ], [
            'identification.grade.required'               => 'Le champ Grade est obligatoire.',
            'identification.date_prise_fonction.required' => 'La date de prise de fonction est obligatoire.',
        ]);

        $semestre = $this->resolveSemestre($data['annee_id'], $data['semestre']);

        if ($this->isDuplicate($evaluableType, $evaluable->id, $semestre->id)) {
            return back()->with('error', 'Une évaluation existe déjà pour ce semestre.')->withInput();
        }

        // ── Garde : pas de réclamation active en cours ───────────────────────
        $reclamationActive = Evaluation::where('evaluable_type', $evaluableType)
            ->where('evaluable_id', $evaluable->id)
            ->where('annee_id', $data['annee_id'])
            ->where('statut', 'reclamation')
            ->where(fn ($q) => $q->whereNull('statut_reclamation')
                ->orWhere('statut_reclamation', '!=', 'maintenu'))
            ->first();
        if ($reclamationActive) {
            return back()
                ->with('error', "Impossible de créer une nouvelle évaluation : une réclamation est en cours sur l'évaluation existante. Elle doit être traitée par le RH avant de pouvoir procéder à une nouvelle évaluation.")
                ->withInput();
        }

        // ── Garde : la fiche d'objectifs doit être acceptée ──────────────────
        if (in_array($evaluableType, [Agent::class, User::class], true)) {
            $fichePendante = $this->ficheObjectifNonAcceptee($evaluableType, $evaluable->id, $data['annee_id']);
            if ($fichePendante !== null) {
                $statutLabel = match ($fichePendante->statut) {
                    'en_attente' => 'en attente d\'acceptation',
                    'brouillon'  => 'encore en brouillon',
                    'refusee'    => 'refusée',
                    'contesté'   => 'contestée',
                    default      => 'non acceptée',
                };
                return back()
                    ->with('error', "Impossible de créer l'évaluation : la fiche d'objectifs « {$fichePendante->titre} » est {$statutLabel}. Elle doit être acceptée par l'évalué avant de pouvoir procéder à l'évaluation.")
                    ->withInput();
            }
        }

        $evaluation = Evaluation::create(array_merge($this->evaluationBaseFields($semestre), [
            'evaluable_type' => $evaluableType,
            'evaluable_id'   => $evaluable->id,
            'evaluable_role' => is_string($cfg->evaluableRole)
                ? $cfg->evaluableRole
                : ($cfg->evaluableRole)($evaluable),
            'evaluateur_id'  => Auth::id(),
            'annee_id'       => $data['annee_id'],
            'statut'         => 'brouillon',
        ]));

        $this->persistFullEvaluationData($evaluation, $request);

        if ($cfg->notifyOnCreate) {
            ($cfg->notifyOnCreate)($evaluation);
        }

        return redirect(($cfg->redirectAfterStore)($evaluation))
            ->with('success', $cfg->successMessage ?? 'Évaluation créée.');
    }

    // =========================================================================
    // CONFIG BUILDERS — côté évaluateur (assigner)
    // =========================================================================

    private function pcaAssignerConfig(): RoleEvaluationAssignerConfig
    {
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.pca',
            getHeroSubtitle: fn (Evaluation $e) => 'PCA · Évaluations',
            updateRoute:     'pca.evaluations.update',
            showRoute:       'pca.evaluations.show',
            editRoute:       'pca.evaluations.edit',
            submitRoute:     'pca.evaluations.submit',
            destroyRoute:    'pca.evaluations.destroy',
            pdfRoute:        'pca.evaluations.pdf',
            breadcrumb:      'PCA · Évaluations',
            getEvalueLabel:     fn (Evaluation $e) => 'Directeur Général',
            getEvaluateurLabel: fn (Evaluation $e) => 'PCA',
            getCibleNom: fn (Evaluation $e) =>
                trim((string) ($e->identification?->nom_prenom ?? '')) ?: ($e->evaluable?->name ?? '-'),
            getCibleType:        fn (Evaluation $e) => 'Directeur Général',
            getObjectiveOptions: fn (Evaluation $e) =>
                $e->evaluable ? $this->getObjectiveOptionsForUser($e->evaluable->id) : [],
            getBackRoute:   fn (Evaluation $e) => route('pca.evaluations.index'),
            checkOwnership: function (Evaluation $e): void {
                $this->authorize('evaluations.creer');
                $this->pcaAuthorizeEvaluation($e);
            },
            notifyOnSubmit: function (Evaluation $e): void {
                Alerte::notifier($e->evaluable_id, 'Votre évaluation a été soumise par le PCA.', '', 'haute', route('dg.evaluations.show', $e));
            },
            resolveRedirectAfterSubmit:  fn (Evaluation $e) => route('pca.evaluations.index'),
            resolveRedirectAfterDestroy: fn (Evaluation $e) => route('pca.evaluations.index'),
            pdfView:           'pca.evaluations.pdf',
            pdfFilenamePrefix: 'pca',
        );
    }

    private function dgSubAssignerConfig(): RoleEvaluationAssignerConfig
    {
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.dg',
            getHeroSubtitle: fn (Evaluation $e) => 'DG · Évaluations subordonnés',
            updateRoute:     'dg.sub-evaluations.update',
            showRoute:       'dg.sub-evaluations.show',
            editRoute:       'dg.sub-evaluations.edit',
            submitRoute:     'dg.sub-evaluations.submit',
            destroyRoute:    'dg.sub-evaluations.destroy',
            pdfRoute:        'dg.sub-evaluations.pdf',
            breadcrumb:      'DG · Évaluations subordonnés',
            getEvalueLabel:     fn (Evaluation $e) =>
                str_replace('_', ' ', $e->evaluable?->role ?? 'Subordonné'),
            getEvaluateurLabel: fn (Evaluation $e) => 'Directeur Général',
            getCibleNom: fn (Evaluation $e) =>
                trim((string) ($e->identification?->nom_prenom ?? '')) ?: ($e->evaluable?->name ?? '-'),
            getCibleType: fn (Evaluation $e) =>
                str_replace('_', ' ', $e->evaluable?->role ?? 'Subordonné'),
            getObjectiveOptions: fn (Evaluation $e) =>
                $e->evaluable ? $this->getObjectiveOptionsForUser($e->evaluable->id) : [],
            getBackRoute: fn (Evaluation $e) =>
                $e->evaluable ? $this->dgSubBackUrl($e->evaluable) : route('dg.subordonnes'),
            checkOwnership: fn (Evaluation $e) => $this->dgSubAuthorizeCreated($e),
            notifyOnSubmit: function (Evaluation $e): void {
                $routeName = ($e->evaluable instanceof User && $e->evaluable->role === 'DGA')
                    ? 'dga.evaluations.show'
                    : 'subordonne.evaluations.show';
                Alerte::notifier($e->evaluable_id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route($routeName, $e));
            },
            resolveRedirectAfterSubmit: fn (Evaluation $e): string =>
                $e->evaluable ? $this->dgSubBackUrl($e->evaluable) : route('dg.subordonnes'),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string =>
                $e->evaluable ? $this->dgSubBackUrl($e->evaluable) : route('dg.subordonnes'),
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'dg-sub',
        );
    }

    private function dgDirectionAssignerConfig(): RoleEvaluationAssignerConfig
    {
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.dg',
            getHeroSubtitle: fn (Evaluation $e) => 'DG · Évaluations directions',
            updateRoute:     'dg.directions.evaluations.update',
            showRoute:       'dg.directions.evaluations.show',
            editRoute:       'dg.directions.evaluations.edit',
            submitRoute:     'dg.directions.evaluations.submit',
            destroyRoute:    'dg.directions.evaluations.destroy',
            pdfRoute:        'dg.directions.evaluations.pdf',
            breadcrumb:      'DG · Évaluations directions',
            getEvalueLabel:     fn (Evaluation $e) => 'Directeur',
            getEvaluateurLabel: fn (Evaluation $e) => 'Directeur Général',
            getCibleNom: fn (Evaluation $e) =>
                ($e->evaluable instanceof Direction) ? ($e->evaluable->nom ?? '-') : '-',
            getCibleType:        fn (Evaluation $e) => 'Direction',
            getObjectiveOptions: function (Evaluation $e): array {
                $direction = $e->evaluable;
                if (! ($direction instanceof Direction)) {
                    return [];
                }
                $dirAgent = $direction->directeur ?? null;
                $dirUser  = $dirAgent ? User::where('agent_id', $dirAgent->id)->first() : null;
                return $dirUser ? $this->getObjectiveOptionsForUser($dirUser->id) : [];
            },
            getBackRoute: fn (Evaluation $e): string =>
                ($e->evaluable instanceof Direction)
                    ? route('dg.directions.show', $e->evaluable)
                    : route('dg.mon-espace', ['tab' => 'dashboard']),
            checkOwnership: fn (Evaluation $e) => $this->dgDirectionAuthorize($e),
            notifyOnSubmit: function (Evaluation $e): void {
                $direction = $e->evaluable;
                if ($direction instanceof Direction && $direction->directeur_agent_id) {
                    $directeurUser = User::where('agent_id', $direction->directeur_agent_id)->first();
                    if ($directeurUser) {
                        Alerte::notifier($directeurUser->id, 'Vous avez reçu une évaluation du DG.', '', 'haute', route('directeur.evaluations.show', $e));
                    }
                }
            },
            resolveRedirectAfterSubmit: fn (Evaluation $e): string =>
                ($e->evaluable instanceof Direction)
                    ? route('dg.directions.show', $e->evaluable)
                    : route('dg.mon-espace', ['tab' => 'dashboard']),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string =>
                ($e->evaluable instanceof Direction)
                    ? route('dg.directions.show', $e->evaluable)
                    : route('dg.mon-espace', ['tab' => 'dashboard']),
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'direction',
        );
    }

    private function dgaSubAssignerConfig(): RoleEvaluationAssignerConfig
    {
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.dga',
            getHeroSubtitle: fn (Evaluation $e) => 'DGA · Évaluations subordonnés',
            updateRoute:     'dga.sub-evaluations.update',
            showRoute:       'dga.sub-evaluations.show',
            editRoute:       'dga.sub-evaluations.edit',
            submitRoute:     'dga.sub-evaluations.submit',
            destroyRoute:    'dga.sub-evaluations.destroy',
            pdfRoute:        'dga.sub-evaluations.pdf',
            breadcrumb:      'DGA · Évaluations subordonnés',
            getEvalueLabel: function (Evaluation $e): string {
                $role = $e->evaluable?->role ?? '';
                if ($role === 'Secretaire_Assistante') {
                    return 'Secrétaire du DGA';
                }
                return str_replace('_', ' ', $role ?: 'Subordonné');
            },
            getEvaluateurLabel: fn (Evaluation $e) => 'DGA',
            getCibleNom: fn (Evaluation $e) =>
                trim((string) ($e->identification?->nom_prenom ?? '')) ?: ($e->evaluable?->name ?? '-'),
            getCibleType: function (Evaluation $e): string {
                $role = $e->evaluable?->role ?? '';
                if ($role === 'Secretaire_Assistante') {
                    return 'Secrétaire du DGA';
                }
                return str_replace('_', ' ', $role ?: 'Subordonné');
            },
            getObjectiveOptions: fn (Evaluation $e) =>
                $e->evaluable ? $this->getObjectiveOptionsForUser($e->evaluable->id) : [],
            getBackRoute:   fn (Evaluation $e): string => $this->dgaSubRedirectAfterAction($e),
            checkOwnership: fn (Evaluation $e) => $this->dgaSubAuthorize($e),
            notifyOnSubmit: function (Evaluation $e): void {
                $role       = $e->evaluable?->role ?? '';
                $showRoute  = match ($role) {
                    'Directeur_Technique' => route('directeur.evaluations.show', $e),
                    default               => route('subordonne.evaluations.show', $e),
                };
                Alerte::notifier($e->evaluable_id, 'Vous avez reçu une évaluation du DGA.', '', 'haute', $showRoute);
            },
            resolveRedirectAfterSubmit:  fn (Evaluation $e): string => $this->dgaSubRedirectAfterAction($e),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string => $this->dgaSubRedirectAfterAction($e),
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'dga-sub',
        );
    }

    private function directeurAssignerConfig(): RoleEvaluationAssignerConfig
    {
        $ctx = $this->getDirecteurContext();
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.directeur',
            getHeroSubtitle: fn (Evaluation $e) => 'Directeur · Évaluations',
            updateRoute:     'directeur.evaluations.update',
            showRoute:       'directeur.evaluations.show',
            editRoute:       'directeur.evaluations.edit',
            submitRoute:     'directeur.evaluations.submit',
            destroyRoute:    'directeur.evaluations.destroy',
            pdfRoute:        'directeur.evaluations.pdf',
            breadcrumb:      'Directeur · Évaluations',
            getEvalueLabel: function (Evaluation $e): string {
                $target = $e->evaluable;
                return match (true) {
                    $target instanceof Service => 'Service',
                    $target instanceof Agence  => 'Agence',
                    $target instanceof Caisse  => 'Caisse',
                    default                    => 'Structure',
                };
            },
            getEvaluateurLabel: fn (Evaluation $e) => $ctx->getRoleLabel(),
            getCibleNom: fn (Evaluation $e) =>
                $e->identification?->nom_prenom ?? ($e->evaluable?->nom ?? '-'),
            getCibleType: function (Evaluation $e): string {
                $target = $e->evaluable;
                return match (true) {
                    $target instanceof Service => 'Service',
                    $target instanceof Agence  => 'Agence',
                    $target instanceof Caisse  => 'Caisse',
                    default                    => 'Structure',
                };
            },
            getObjectiveOptions: function (Evaluation $e): array {
                $target  = $e->evaluable;
                $agentId = match (true) {
                    $target instanceof Service => $target->chef_agent_id ?? null,
                    $target instanceof Agence  => $target->chef_agent_id ?? null,
                    $target instanceof Caisse  => $target->directeur_agent_id ?? null,
                    default                    => null,
                };
                $managerUser = $agentId ? User::where('agent_id', $agentId)->first() : null;
                return $managerUser ? $this->getObjectiveOptionsForUser($managerUser->id) : [];
            },
            getBackRoute: function (Evaluation $e): string {
                $target = $e->evaluable;
                return match (true) {
                    $target instanceof Service => route('directeur.subordonnes.service', ['service' => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Agence  => route('directeur.subordonnes.agence',  ['agence'  => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Caisse  => route('directeur.subordonnes.caisse',  ['caisse'  => $target->id, 'tab' => 'evaluations']),
                    default                    => route('directeur.mon-espace', ['tab' => 'dashboard']),
                };
            },
            checkOwnership: fn (Evaluation $e) => $this->directeurAuthorizeCreated($e),
            notifyOnSubmit: function (Evaluation $e): void {
                $target  = $e->evaluable;
                $agentId = match (true) {
                    $target instanceof Service => $target->chef_agent_id ?? null,
                    $target instanceof Agence  => $target->chef_agent_id ?? null,
                    $target instanceof Caisse  => $target->directeur_agent_id ?? null,
                    default                    => null,
                };
                $user = $agentId ? User::where('agent_id', $agentId)->first() : null;
                if ($user) {
                    // DC (Directeur_Caisse) → route directeur ; chefs de service/agence → route chef
                    $evalShowRoute = ($target instanceof Caisse)
                        ? 'directeur.evaluations.show'
                        : 'chef.evaluations.show';
                    Alerte::notifier($user->id, 'Vous avez reçu une évaluation.', '', 'haute', route($evalShowRoute, $e));
                }
            },
            resolveRedirectAfterSubmit: function (Evaluation $e): string {
                $target = $e->evaluable;
                return match (true) {
                    $target instanceof Service => route('directeur.subordonnes.service', ['service' => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Agence  => route('directeur.subordonnes.agence',  ['agence'  => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Caisse  => route('directeur.subordonnes.caisse',  ['caisse'  => $target->id, 'tab' => 'evaluations']),
                    default                    => route('directeur.mon-espace', ['tab' => 'dashboard']),
                };
            },
            resolveRedirectAfterDestroy: function (Evaluation $e): string {
                $target = $e->evaluable;
                return match (true) {
                    $target instanceof Service => route('directeur.subordonnes.service', ['service' => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Agence  => route('directeur.subordonnes.agence',  ['agence'  => $target->id, 'tab' => 'evaluations']),
                    $target instanceof Caisse  => route('directeur.subordonnes.caisse',  ['caisse'  => $target->id, 'tab' => 'evaluations']),
                    default                    => route('directeur.mon-espace', ['tab' => 'dashboard']),
                };
            },
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'directeur',
        );
    }

    private function directeurSecretaireAssignerConfig(): RoleEvaluationAssignerConfig
    {
        $ctx = $this->getDirecteurContext();
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.directeur',
            getHeroSubtitle: fn (Evaluation $e) => 'Directeur · Secrétaire',
            updateRoute:     'directeur.subordonnes.secretaire.evaluations.update',
            showRoute:       'directeur.subordonnes.secretaire.evaluations.show',
            editRoute:       'directeur.subordonnes.secretaire.evaluations.edit',
            submitRoute:     'directeur.subordonnes.secretaire.evaluations.submit',
            destroyRoute:    'directeur.subordonnes.secretaire.evaluations.destroy',
            pdfRoute:        'directeur.subordonnes.secretaire.evaluations.pdf',
            breadcrumb:      'Directeur · Secrétaire',
            getEvalueLabel:     fn (Evaluation $e) => 'Secrétaire',
            getEvaluateurLabel: fn (Evaluation $e) => $ctx->getRoleLabel(),
            getCibleNom: fn (Evaluation $e) =>
                $e->identification?->nom_prenom ?? ($e->evaluable?->name ?? '-'),
            getCibleType:        fn (Evaluation $e) => 'Secrétaire',
            getObjectiveOptions: fn (Evaluation $e) =>
                $e->evaluable ? $this->getObjectiveOptionsForUser($e->evaluable->id) : [],
            getBackRoute:   fn (Evaluation $e): string =>
                route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            checkOwnership: function (Evaluation $e): void {
                $this->authorize('evaluations.creer');
                $this->directeurSecretaireAuthorize($e);
            },
            notifyOnSubmit: function (Evaluation $e): void {
                Alerte::notifier($e->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $e));
            },
            resolveRedirectAfterSubmit:  fn (Evaluation $e): string =>
                route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string =>
                route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'secretaire-dt',
        );
    }

    private function chefAssignerConfig(): RoleEvaluationAssignerConfig
    {
        $ctx = $this->getChefContext();
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.chef',
            getHeroSubtitle: fn (Evaluation $e) => 'Chef · ' . $ctx->getTypeLabel() . ' ' . $ctx->getNom(),
            updateRoute:     'chef.evaluations.update',
            showRoute:       'chef.evaluations.show',
            editRoute:       'chef.evaluations.edit',
            submitRoute:     'chef.evaluations.submit',
            destroyRoute:    'chef.evaluations.destroy',
            pdfRoute:        'chef.evaluations.pdf',
            breadcrumb:      'Chef · Évaluations',
            getEvalueLabel:     fn (Evaluation $e) =>
                $e->evaluable_type === Guichet::class ? 'Guichet' : 'Agent',
            getEvaluateurLabel: fn (Evaluation $e) => $ctx->getRoleLabel(),
            getCibleNom: function (Evaluation $e): string {
                $target = $e->evaluable;
                return $e->identification?->nom_prenom
                    ?? ($target instanceof Agent
                        ? trim(($target->prenom ?? '') . ' ' . ($target->nom ?? ''))
                        : ($target?->nom ?? '-'));
            },
            getCibleType: fn (Evaluation $e) =>
                $e->evaluable_type === Guichet::class ? 'Guichet' : 'Agent',
            getObjectiveOptions: fn (Evaluation $e): array =>
                $e->evaluable_type === Agent::class
                    ? $this->getObjectiveOptionsForAgent($e->evaluable_id)
                    : [],
            getBackRoute: fn (Evaluation $e): string =>
                $e->evaluable_type === Agent::class
                    ? route('chef.agent.show', $e->evaluable_id)
                    : route('chef.guichets'),
            checkOwnership: fn (Evaluation $e) => $this->chefAuthorizeCreated($e),
            notifyOnSubmit: function (Evaluation $e): void {
                if ($e->evaluable_type === Agent::class) {
                    $agent       = Agent::find($e->evaluable_id);
                    $agentUserId = $agent ? User::where('agent_id', $agent->id)->value('id') : null;
                    if ($agentUserId) {
                        Alerte::notifier($agentUserId, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $e));
                    }
                } elseif ($e->evaluable_type === Guichet::class) {
                    $guichet = Guichet::find($e->evaluable_id);
                    if ($guichet && $guichet->chef_agent_id) {
                        $chefGuichet = User::where('agent_id', $guichet->chef_agent_id)->first();
                        if ($chefGuichet) {
                            Alerte::notifier($chefGuichet->id, 'Vous avez reçu une évaluation.', '', 'haute', route('chef.evaluations.show', $e));
                        }
                    }
                }
            },
            resolveRedirectAfterSubmit:  fn (Evaluation $e): string =>
                $e->evaluable_type === \App\Models\Agent::class
                    ? route('chef.agent.show', $e->evaluable_id)
                    : route('chef.guichets'),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string =>
                $e->evaluable_type === \App\Models\Agent::class
                    ? route('chef.agent.show', $e->evaluable_id)
                    : route('chef.guichets'),
            pdfView:           view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf',
            pdfFilenamePrefix: 'chef',
        );
    }

    private function assistanteAssignerConfig(): RoleEvaluationAssignerConfig
    {
        return new RoleEvaluationAssignerConfig(
            layout:          'layouts.subordonne',
            getHeroSubtitle: fn (Evaluation $e) => 'Assistante DG · Secrétaire',
            updateRoute:     'assistante.secretaire.evaluations.update',
            showRoute:       'assistante.secretaire.evaluations.show',
            editRoute:       'assistante.secretaire.evaluations.edit',
            submitRoute:     'assistante.secretaire.evaluations.submit',
            destroyRoute:    'assistante.secretaire.evaluations.destroy',
            pdfRoute:        'assistante.secretaire.evaluations.pdf',
            breadcrumb:      'Assistante · Secrétaire',
            getEvalueLabel:     fn (Evaluation $e) => 'Secrétaire',
            getEvaluateurLabel: fn (Evaluation $e) => 'Assistante DG',
            getCibleNom: fn (Evaluation $e) =>
                $e->identification?->nom_prenom ?? ($e->evaluable?->name ?? '-'),
            getCibleType:        fn (Evaluation $e) => 'Secrétaire',
            getObjectiveOptions: fn (Evaluation $e) =>
                $e->evaluable ? $this->getObjectiveOptionsForUser($e->evaluable->id) : [],
            getBackRoute:   fn (Evaluation $e): string =>
                route('assistante.secretaire', ['tab' => 'evaluations']),
            checkOwnership: function (Evaluation $e): void {
                $this->authorize('evaluations.creer');
                $this->assistanteAuthorize($e);
            },
            notifyOnSubmit: function (Evaluation $e): void {
                Alerte::notifier($e->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $e));
            },
            resolveRedirectAfterSubmit:  fn (Evaluation $e): string =>
                route('assistante.secretaire', ['tab' => 'evaluations']),
            resolveRedirectAfterDestroy: fn (Evaluation $e): string =>
                route('assistante.secretaire', ['tab' => 'evaluations']),
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'assistante',
        );
    }

    // =========================================================================
    // CONFIG BUILDERS — côté évalué (received)
    // =========================================================================

    /**
     * Retourne le nom complet de la personne évaluée dans une évaluation.
     * Utilisé pour personnaliser les messages de notification.
     */
    private function evalCibleNom(Evaluation $e): string
    {
        $evaluable = $e->evaluable;
        if ($evaluable instanceof \App\Models\Agent) {
            return trim(($evaluable->prenom ?? '') . ' ' . ($evaluable->nom ?? '')) ?: 'l\'agent';
        }
        return $evaluable?->nom ?? 'l\'agent';
    }

    private function dgReceivedConfig(): RoleEvaluationReceivedConfig
    {
        return new RoleEvaluationReceivedConfig(
            checkOwnership: fn (Evaluation $e) => $this->dgReceivedAuthorize($e),
            notifyStatut: function (Evaluation $e, string $labelStatut): void {
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "L'évaluation de {$nom} (DG) a été {$labelStatut}.", '', 'haute', route('pca.evaluations.show', $e));
                if ($labelStatut === 'refusée') {
                    $rhUser = User::where('role', 'RH')->first();
                    if ($rhUser) {
                        Alerte::notifier($rhUser->id, "L'évaluation de {$nom} (DG) a été refusée.", '', 'haute', route('rh.evaluations.show', $e));
                    }
                }
            },
            notifyReclamer: function (Evaluation $e): void {
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "{$nom} (DG) a déposé une réclamation.", '', 'haute', route('pca.evaluations.show', $e));
                $rhUser = User::where('role', 'RH')->first();
                if ($rhUser) {
                    Alerte::notifier($rhUser->id, "Réclamation de {$nom} (DG).", '', 'haute', route('rh.evaluations.show', $e));
                }
            },
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'dg',
            layout:            'layouts.dg',
            getCibleType:      fn (Evaluation $e) => 'Directeur Général',
            getBackRoute:      fn (Evaluation $e) => route('dg.mon-espace'),
            breadcrumb:        'DG · Mon évaluation',
            statutRoute:       'dg.evaluations.statut',
            reclamerRoute:     'dg.evaluations.reclamer',
            commentaireRoute:  'dg.evaluations.commentaire',
            showPdfRoute:      'dg.evaluations.pdf',
        );
    }

    private function dgaReceivedConfig(): RoleEvaluationReceivedConfig
    {
        $prefix = $this->espaceRoutePrefix();
        $layout = $prefix === 'dga' ? 'layouts.dga' : 'layouts.subordonne';

        return new RoleEvaluationReceivedConfig(
            checkOwnership: fn (Evaluation $e) => $this->dgaReceivedAuthorize($e),
            notifyStatut: function (Evaluation $e, string $labelStatut): void {
                $role      = Auth::user()?->role ?? '';
                $roleLabel = self::ROLE_LABELS_DGA[$role] ?? $role;
                $nom       = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "L'évaluation de {$nom} ({$roleLabel}) a été {$labelStatut}.", '', 'haute', route('dg.sub-evaluations.show', $e));
                if ($labelStatut === 'refusée') {
                    $rhUser = User::where('role', 'RH')->first();
                    if ($rhUser) {
                        Alerte::notifier($rhUser->id, "L'évaluation de {$nom} ({$roleLabel}) a été refusée.", '', 'haute', route('rh.evaluations.show', $e));
                    }
                }
            },
            notifyReclamer: function (Evaluation $e): void {
                $role      = Auth::user()?->role ?? '';
                $roleLabel = self::ROLE_LABELS_DGA[$role] ?? $role;
                $nom       = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "{$nom} ({$roleLabel}) a déposé une réclamation.", '', 'haute', route('dg.sub-evaluations.show', $e));
                $rhUser = User::where('role', 'RH')->first();
                if ($rhUser) {
                    Alerte::notifier($rhUser->id, "Réclamation de {$nom} ({$roleLabel}).", '', 'haute', route('rh.evaluations.show', $e));
                }
            },
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'dga',
            layout:           $layout,
            getCibleType:     function (Evaluation $e): string {
                $role = $e->evaluable?->role ?? '';
                return self::ROLE_LABELS_DGA[$role] ?? str_replace('_', ' ', $role);
            },
            getBackRoute:     fn (Evaluation $e) => route($this->espaceRoutePrefix() . '.mon-espace'),
            breadcrumb:       'Mon évaluation',
            statutRoute:      "{$prefix}.evaluations.statut",
            reclamerRoute:    "{$prefix}.evaluations.reclamer",
            commentaireRoute: "{$prefix}.evaluations.commentaire",
            showPdfRoute:     "{$prefix}.evaluations.pdf",
        );
    }

    private function directeurReceivedConfig(): RoleEvaluationReceivedConfig
    {
        return new RoleEvaluationReceivedConfig(
            checkOwnership: fn (Evaluation $e) => $this->directeurAuthorizeReceived($e),
            notifyStatut: function (Evaluation $e, string $labelStatut): void {
                $showRoute = match ($e->evaluateur?->role ?? '') {
                    'DG'  => 'dg.directions.evaluations.show',
                    'DGA' => 'dga.sub-evaluations.show',
                    default => 'directeur.evaluations.show',
                };
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "L'évaluation de {$nom} a été {$labelStatut}.", '', 'haute', route($showRoute, $e));
                if ($labelStatut === 'refusée') {
                    $rhUser = User::where('role', 'RH')->first();
                    if ($rhUser) {
                        Alerte::notifier($rhUser->id, "L'évaluation de {$nom} (Directeur) a été refusée.", '', 'haute', route('rh.evaluations.show', $e));
                    }
                }
            },
            notifyReclamer: function (Evaluation $e): void {
                $showRoute = match ($e->evaluateur?->role ?? '') {
                    'DG'  => 'dg.directions.evaluations.show',
                    'DGA' => 'dga.sub-evaluations.show',
                    default => 'directeur.evaluations.show',
                };
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "{$nom} a déposé une réclamation.", '', 'haute', route($showRoute, $e));
                $rhUser = User::where('role', 'RH')->first();
                if ($rhUser) {
                    Alerte::notifier($rhUser->id, "Réclamation de {$nom} (Directeur).", '', 'haute', route('rh.evaluations.show', $e));
                }
            },
            pdfView:           'dg.evaluations.pdf',
            pdfFilenamePrefix: 'directeur-received',
        );
    }

    private function chefReceivedConfig(): RoleEvaluationReceivedConfig
    {
        return new RoleEvaluationReceivedConfig(
            checkOwnership: fn (Evaluation $e) => $this->chefAuthorizeReceived($e),
            notifyStatut: function (Evaluation $e, string $labelStatut): void {
                $directeurRoles = ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'];
                $showRoute = in_array($e->evaluateur?->role ?? '', $directeurRoles, true)
                    ? 'directeur.evaluations.show'
                    : 'chef.evaluations.show';
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "L'évaluation de {$nom} a été {$labelStatut}.", '', 'haute', route($showRoute, $e));
                if ($labelStatut === 'refusée') {
                    $rhUser = User::where('role', 'RH')->first();
                    if ($rhUser) {
                        Alerte::notifier($rhUser->id, "L'évaluation de {$nom} (Chef) a été refusée.", '', 'haute', route('rh.evaluations.show', $e));
                    }
                }
            },
            notifyReclamer: function (Evaluation $e): void {
                $directeurRoles = ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'];
                $showRoute = in_array($e->evaluateur?->role ?? '', $directeurRoles, true)
                    ? 'directeur.evaluations.show'
                    : 'chef.evaluations.show';
                $nom = $this->evalCibleNom($e);
                Alerte::notifier($e->evaluateur_id, "{$nom} a déposé une réclamation.", '', 'haute', route($showRoute, $e));
                $rhUser = User::where('role', 'RH')->first();
                if ($rhUser) {
                    Alerte::notifier($rhUser->id, "Réclamation de {$nom} (Chef).", '', 'haute', route('rh.evaluations.show', $e));
                }
            },
            pdfView:           view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf',
            pdfFilenamePrefix: 'chef-received',
        );
    }

    private function personnelReceivedConfig(): RoleEvaluationReceivedConfig
    {
        return new RoleEvaluationReceivedConfig(
            checkOwnership: fn (Evaluation $e) => $this->personnelAuthorize($e),
            notifyStatut: function (Evaluation $e, string $labelStatut): void {
                $nom            = $this->evalCibleNom($e);
                $directeurRoles = ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'];
                $evalShowRoute  = in_array($e->evaluateur?->role ?? '', $directeurRoles, true)
                    ? 'directeur.subordonnes.secretaire.evaluations.show'
                    : 'chef.evaluations.show';
                Alerte::notifier($e->evaluateur_id, "L'évaluation de {$nom} a été {$labelStatut}.", '', 'haute', route($evalShowRoute, $e));
                if ($labelStatut === 'refusée') {
                    $rhUser = User::where('role', 'RH')->first();
                    if ($rhUser) {
                        Alerte::notifier($rhUser->id, "L'évaluation de {$nom} (Agent) a été refusée.", '', 'haute', route('rh.evaluations.show', $e));
                    }
                }
            },
            notifyReclamer: function (Evaluation $e): void {
                $nom            = $this->evalCibleNom($e);
                $directeurRoles = ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'];
                $evalShowRoute  = in_array($e->evaluateur?->role ?? '', $directeurRoles, true)
                    ? 'directeur.subordonnes.secretaire.evaluations.show'
                    : 'chef.evaluations.show';
                Alerte::notifier($e->evaluateur_id, "{$nom} a déposé une réclamation.", '', 'haute', route($evalShowRoute, $e));
                $rhUser = User::where('role', 'RH')->first();
                if ($rhUser) {
                    Alerte::notifier($rhUser->id, "Réclamation de {$nom} (Agent).", '', 'haute', route('rh.evaluations.show', $e));
                }
            },
            pdfView:           view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf',
            pdfFilenamePrefix: 'personnel',
            layout:           'layouts.personnel',
            getCibleType:     fn (Evaluation $e) => 'Personnel',
            getBackRoute:     fn (Evaluation $e) => route('personnel.dashboard') . '?tab=evaluations',
            breadcrumb:       'Personnel · Mon évaluation',
            statutRoute:      'personnel.evaluations.statut',
            reclamerRoute:    'personnel.evaluations.reclamer',
            commentaireRoute: 'personnel.evaluations.commentaire',
            showPdfRoute:     'personnel.evaluations.pdf',
        );
    }

    // =========================================================================
    // CONFIG BUILDERS — store (création côté évaluateur)
    // =========================================================================

    private function pcaStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $dgUser = $this->getDGOfDirectionGenerale();
                return [User::class, $dgUser];
            },
            evaluableRole:       'DG',
            redirectAfterStore:  fn (Evaluation $e) => route('pca.evaluations.show', $e),
            missingEvaluableMessage: 'Aucun DG trouvé.',
        );
    }

    private function dgSubStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $subordonne = User::findOrFail($r->integer('user_id'));
                $allowedIds = $this->getDgSubordonnes()->pluck('id')->map(fn ($id) => (int) $id)->all();
                if (! in_array($subordonne->id, $allowedIds, true)) {
                    abort(403);
                }
                return [User::class, $subordonne];
            },
            evaluableRole:      fn ($sub) => $sub->role,
            redirectAfterStore: fn (Evaluation $e) => route('dg.sub-evaluations.show', $e),
        );
    }

    private function dgDirectionStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $direction = Direction::findOrFail($r->integer('direction_id'));
                if ((int) $direction->entite_id !== $this->getDgEntiteId()) {
                    abort(403);
                }
                return [Direction::class, $direction];
            },
            evaluableRole:      'manager',
            redirectAfterStore: fn (Evaluation $e) => route('dg.directions.evaluations.show', $e),
        );
    }

    private function dgaSubStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $this->checkDga();
                $subordonne = User::findOrFail($r->integer('user_id'));
                $allowedIds = $this->getDgaAllowedUserIds();
                if (! in_array($subordonne->id, $allowedIds, true)) {
                    abort(403);
                }
                return [User::class, $subordonne];
            },
            evaluableRole:      fn ($sub) => $sub->role,
            redirectAfterStore: fn (Evaluation $e) => route('dga.sub-evaluations.show', $e),
        );
    }

    private function directeurStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $modelMap = [
                    'caisse'  => [Caisse::class,  $r->integer('caisse_id')],
                    'agence'  => [Agence::class,  $r->integer('agence_id')],
                    'service' => [Service::class, $r->integer('service_id')],
                ];
                foreach ($modelMap as [$class, $id]) {
                    if ($id) {
                        return [$class, $class::find($id)];
                    }
                }
                return [Service::class, null];
            },
            evaluableRole:           'manager',
            redirectAfterStore:      fn (Evaluation $e) => route('directeur.evaluations.show', $e),
            missingEvaluableMessage: 'Veuillez sélectionner un collaborateur à évaluer.',
        );
    }

    private function directeurSecretaireStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $ctx        = $this->getDirecteurContext();
                $secretaire = User::find($ctx->getSecretaireUserId());
                return [User::class, $secretaire];
            },
            evaluableRole:           'secretaire',
            redirectAfterStore:      fn (Evaluation $e) => route('directeur.subordonnes.secretaire', ['tab' => 'evaluations']),
            missingEvaluableMessage: 'Aucun secrétaire trouvé.',
        );
    }

    private function chefStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $ctx   = $this->getChefContext();
                $agent = Agent::findOrFail($r->integer('agent_id'));
                if (! $ctx->agentOwnedBy($agent)) {
                    abort(403);
                }
                return [Agent::class, $agent];
            },
            evaluableRole:      'manager',
            redirectAfterStore: fn (Evaluation $e) => route('chef.evaluations.show', $e),
        );
    }

    private function chefGuichetStoreConfig(Guichet $guichet, ChefEntity $ctx): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r) use ($guichet, $ctx): array {
                if ($ctx->type !== 'agence' || (int) $guichet->agence_id !== $ctx->getId()) {
                    abort(403);
                }
                return [Guichet::class, $guichet];
            },
            evaluableRole:      'manager',
            redirectAfterStore: fn (Evaluation $e) => route('chef.evaluations.show', $e),
            notifyOnCreate:     null,
            successMessage:     'Évaluation guichet enregistrée en brouillon. Complétez-la et soumettez-la pour que le Chef de Guichet la reçoive.',
        );
    }

    private function assistanteStoreConfig(): RoleEvaluationStoreConfig
    {
        return new RoleEvaluationStoreConfig(
            resolveEvaluable: function (Request $r): array {
                $this->assertIsAssistante();
                $secretaire = $this->findAssistanteSecretaire();
                return [User::class, $secretaire];
            },
            evaluableRole:      'secretaire',
            redirectAfterStore: fn (Evaluation $e) => route('assistante.secretaire', ['tab' => 'evaluations']),
            notifyOnCreate:     function (Evaluation $e): void {
                Alerte::notifier($e->evaluable_id, 'Vous avez reçu une évaluation.', '', 'haute', route('personnel.evaluations.show', $e));
            },
            missingEvaluableMessage: 'Aucun secrétaire trouvé.',
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

        $evaluations = (clone $baseQuery)->latest()->get();

        $stats = [
            'total'     => (clone $baseQuery)->count(),
            'brouillon' => (clone $baseQuery)->where('statut', 'brouillon')->count(),
            'soumis'    => (clone $baseQuery)->where('statut', 'soumis')->count(),
            'valide'    => (clone $baseQuery)->where('statut', 'valide')->count(),
            'refuse'    => (clone $baseQuery)->whereIn('statut', ['refuse', 'reclamation'])->count(),
        ];

        $ficheAcceptee    = $dgUser
            ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $dgUser->id)->where('statut', 'acceptee')->exists()
            : false;
        $evaluationEnCours = $dgUser
            ? Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $dgUser->id)->whereIn('statut', ['soumis', 'brouillon'])->exists()
            : false;

        return view('pca.evaluations.index', [
            'evaluations'       => $evaluations,
            'filters'           => ['search' => $search, 'statut' => $statut],
            'stats'             => $stats,
            'ficheAcceptee'     => $ficheAcceptee,
            'evaluationEnCours' => $evaluationEnCours,
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
        return $this->sharedAssignerShow($evaluation, $this->pcaAssignerConfig());
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
            'prefilledMatricule'          => $dgAgent?->matricule ?? '',
            'prefilledNomPrenom'          => $dgAgent ? trim(($dgAgent->prenom ?? '') . ' ' . ($dgAgent->nom ?? '')) : $dgUser->name,
            'prefilledEmploi'             => $dgAgent?->role ?? 'Directeur Général',
            'prefilledAgentId'            => $dgAgent?->id,
            'prefilledDatePriseFonction'  => $dgAgent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'entiteNom'                   => 'Faitière des Caisses Populaires',
            'prefilledDirectionService'   => '',
        ]));
    }

    private function pcaStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->pcaStoreConfig());
    }

    private function pcaEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->pcaAssignerConfig());
    }

    private function pcaUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->pcaAssignerConfig());
    }

    private function pcaSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->pcaAssignerConfig());
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
        return $this->sharedAssignerExportPdf($evaluation, $this->pcaAssignerConfig());
    }

    private function pcaDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->pcaAssignerConfig());
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
        return $this->sharedReceivedShow($evaluation, $this->dgReceivedConfig());
    }

    private function dgReceivedStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedStatut($request, $evaluation, $this->dgReceivedConfig());
    }

    private function dgReceivedReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedReclamer($request, $evaluation, $this->dgReceivedConfig());
    }

    private function dgReceivedCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        return $this->sharedCommentaire($request, $evaluation, $this->dgReceivedConfig());
    }

    private function dgReceivedExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedReceivedExportPdf($evaluation, $this->dgReceivedConfig());
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
        $subordonne = User::findOrFail($request->integer('subordonne_id'));
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
            'prefilledMatricule'          => $agent?->matricule ?? '',
            'prefilledNomPrenom'          => $agent ? trim(($agent->prenom ?? '') . ' ' . ($agent->nom ?? '')) : $subordonne->name,
            'prefilledEmploi'             => in_array($agent?->role, ['Agent', 'Conseiller DG'], true)
                                                ? ($agent?->poste ?? $agent?->role ?? '')
                                                : ($agent?->role ?? ''),
            'prefilledAgentId'            => $agent?->id,
            'prefilledDatePriseFonction'  => $agent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'entiteNom'                   => 'Faitière des Caisses Populaires',
            'prefilledDirectionService'   => '',
        ]));
    }

    private function dgSubStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->dgSubStoreConfig());
    }

    private function dgSubShow(Evaluation $evaluation): View
    {
        return $this->sharedAssignerShow($evaluation, $this->dgSubAssignerConfig());
    }

    private function dgSubEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->dgSubAssignerConfig());
    }

    private function dgSubUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->dgSubAssignerConfig());
    }

    private function dgSubSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->dgSubAssignerConfig());
    }

    private function dgSubExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedAssignerExportPdf($evaluation, $this->dgSubAssignerConfig());
    }

    private function dgSubDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->dgSubAssignerConfig());
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
            'prefilledMatricule'          => $dirAgent?->matricule ?? '',
            'prefilledNomPrenom'          => $dirAgent ? trim(($dirAgent->prenom ?? '') . ' ' . ($dirAgent->nom ?? '')) : '',
            'prefilledEmploi'             => $dirAgent?->role ?? 'Directeur de Direction',
            'entiteNom'                   => 'Faitière des Caisses Populaires',
            'prefilledAgentId'            => $dirAgent?->id,
            'prefilledDatePriseFonction'  => $dirAgent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'prefilledDirectionService'   => $direction->nom ?? '',
            'objectiveOptions'          => $dirUser ? $this->getObjectiveOptionsForUser($dirUser->id) : [],
        ]));
    }

    private function dgDirectionStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->dgDirectionStoreConfig());
    }

    private function dgDirectionShow(Evaluation $evaluation): View
    {
        return $this->sharedAssignerShow($evaluation, $this->dgDirectionAssignerConfig());
    }

    private function dgDirectionEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->dgDirectionAssignerConfig());
    }

    private function dgDirectionUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->dgDirectionAssignerConfig());
    }

    private function dgDirectionSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->dgDirectionAssignerConfig());
    }

    private function dgDirectionExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedAssignerExportPdf($evaluation, $this->dgDirectionAssignerConfig());
    }

    private function dgDirectionDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->dgDirectionAssignerConfig());
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
        return $this->sharedReceivedShow($evaluation, $this->dgaReceivedConfig());
    }

    private function dgaReceivedStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedStatut($request, $evaluation, $this->dgaReceivedConfig());
    }

    private function dgaReceivedReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedReclamer($request, $evaluation, $this->dgaReceivedConfig());
    }

    private function dgaReceivedCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        return $this->sharedCommentaire($request, $evaluation, $this->dgaReceivedConfig());
    }

    private function dgaReceivedExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedReceivedExportPdf($evaluation, $this->dgaReceivedConfig());
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
                    'role_label'   => 'Secrétaire du DGA',
                    'entite_label' => 'Faitière des Caisses Populaires',
                    'service_label'=> $direction?->nom ?? '',
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
                    'entite_label' => 'Faitière des Caisses Populaires',
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
            'evalueLabel'               => $entry['role_label'] ?? str_replace('_', ' ', $subordonne->role ?? 'Subordonné'),
            'evaluateurLabel'           => 'DGA',
            'targetType'                => 'user',
            'objectiveOptions'          => $this->getObjectiveOptionsForUser($subordonne->id),
            'subordonne'                => $subordonne,
            'prefilledMatricule'          => $agent?->matricule ?? '',
            'prefilledNomPrenom'          => $agent ? trim(($agent->prenom ?? '') . ' ' . ($agent->nom ?? '')) : $subordonne->name,
            'prefilledEmploi'             => in_array($agent?->role, ['Agent', 'Conseiller DG'], true)
                                                ? ($agent?->poste ?? $agent?->role ?? '')
                                                : ($agent?->role ?? ''),
            'prefilledAgentId'            => $agent?->id,
            'prefilledDatePriseFonction'  => $agent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'entiteNom'                   => $entry['entite_label'] ?? '',
            'prefilledDirectionService'   => $entry['service_label'] ?? '',
        ]));
    }

    private function dgaSubStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->dgaSubStoreConfig());
    }

    private function dgaSubShow(Evaluation $evaluation): View
    {
        return $this->sharedAssignerShow($evaluation, $this->dgaSubAssignerConfig());
    }

    private function dgaSubEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->dgaSubAssignerConfig());
    }

    private function dgaSubUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->dgaSubAssignerConfig());
    }

    private function dgaSubSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->dgaSubAssignerConfig());
    }

    private function dgaSubExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedAssignerExportPdf($evaluation, $this->dgaSubAssignerConfig());
    }

    private function dgaSubDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->dgaSubAssignerConfig());
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
            'id'                  => $s->id,
            'agent_id'            => $s->chef?->id ?? null,
            'nom_prenom'          => $s->chef ? trim(($s->chef->prenom ?? '') . ' ' . ($s->chef->nom ?? '')) : '',
            'matricule'           => $s->chef?->matricule ?? '',
            'emploi'              => 'Chef de Service',
            'entite_nom'          => $entiteNomCtx,
            'direction_service'   => $s->nom,
            'date_prise_fonction' => $s->chef?->date_debut_fonction?->format('d/m/Y') ?? '',
        ])->values()->toArray();

        $agencesJson = $agences->map(fn ($a) => [
            'id'                  => $a->id,
            'agent_id'            => $a->chef?->id ?? null,
            'nom_prenom'          => $a->chef ? trim(($a->chef->prenom ?? '') . ' ' . ($a->chef->nom ?? '')) : '',
            'matricule'           => $a->chef?->matricule ?? '',
            'emploi'              => "Chef d'Agence",
            'entite_nom'          => $entiteNomCtx,
            'direction_service'   => $a->nom,
            'date_prise_fonction' => $a->chef?->date_debut_fonction?->format('d/m/Y') ?? '',
        ])->values()->toArray();

        $caissesJson = $caisses->map(fn ($c) => [
            'id'                  => $c->id,
            'agent_id'            => $c->directeurAgent?->id ?? null,
            'nom_prenom'          => $c->directeurAgent ? trim(($c->directeurAgent->prenom ?? '') . ' ' . ($c->directeurAgent->nom ?? '')) : '',
            'matricule'           => $c->directeurAgent?->matricule ?? '',
            'emploi'              => 'Directeur de Caisse',
            'entite_nom'          => $entiteNomCtx,
            'direction_service'   => $c->nom,
            'date_prise_fonction' => $c->directeurAgent?->date_debut_fonction?->format('d/m/Y') ?? '',
        ])->values()->toArray();

        $preselectedEntity = $selectedCaisse ?? $selectedAgence ?? $selectedService;
        $objectiveOptions  = $preselectedEntity ? $this->getObjectiveOptionsForEntity($preselectedEntity) : [];

        $createBackUrl = match (true) {
            $selectedCaisse  !== null => route('directeur.subordonnes.caisse',  ['caisse'  => $selectedCaisse->id,  'tab' => 'evaluations']),
            $selectedAgence  !== null => route('directeur.subordonnes.agence',  ['agence'  => $selectedAgence->id,  'tab' => 'evaluations']),
            $selectedService !== null => route('directeur.subordonnes.service', ['service' => $selectedService->id, 'tab' => 'evaluations']),
            default                   => route('directeur.mon-espace', ['tab' => 'dashboard']),
        };

        return view('evaluations.create', $this->createViewData([
            'layout'           => 'layouts.directeur',
            'heroSubtitle'     => 'Directeur · Évaluations',
            'formAction'       => route('directeur.evaluations.store'),
            'backUrl'          => $createBackUrl,
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
        return $this->sharedStore($request, $this->directeurStoreConfig());
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

        $showBackRoute = $isReceived
            ? route('directeur.mon-espace', ['tab' => 'evaluations'])
            : match (true) {
                $target instanceof Service => route('directeur.subordonnes.service', ['service' => $target->id, 'tab' => 'evaluations']),
                $target instanceof Agence  => route('directeur.subordonnes.agence',  ['agence'  => $target->id, 'tab' => 'evaluations']),
                $target instanceof Caisse  => route('directeur.subordonnes.caisse',  ['caisse'  => $target->id, 'tab' => 'evaluations']),
                default                    => route('directeur.mon-espace', ['tab' => 'dashboard']),
            };

        $extra = [
            'layout'     => 'layouts.directeur',
            'cibleLabel' => $cibleLabel,
            'cibleType'  => $cibleType,
            'backRoute'  => $showBackRoute,
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
        return $this->sharedEdit($evaluation, $this->directeurAssignerConfig());
    }

    private function directeurUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->directeurAssignerConfig());
    }

    private function directeurSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->directeurAssignerConfig());
    }

    private function directeurStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedStatut($request, $evaluation, $this->directeurReceivedConfig());
    }

    private function directeurReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedReclamer($request, $evaluation, $this->directeurReceivedConfig());
    }

    private function directeurCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        return $this->sharedCommentaire($request, $evaluation, $this->directeurReceivedConfig());
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
        return $this->sharedDestroy($evaluation, $this->directeurAssignerConfig());
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
            'prefilledMatricule'          => $secAgent?->matricule ?? '',
            'prefilledNomPrenom'          => $secAgent ? trim(($secAgent->prenom ?? '') . ' ' . ($secAgent->nom ?? '')) : $secretaire->name,
            'prefilledAgentId'            => $secAgent?->id,
            'prefilledEmploi'             => $secAgent?->role ?? 'Secrétaire',
            'prefilledDatePriseFonction'  => $secAgent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'entiteNom'                   => $ctx->getNom(),
            'prefilledDirectionService'   => 'Secrétariat',
        ]));
    }

    private function directeurSecretaireStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->directeurSecretaireStoreConfig());
    }

    private function directeurSecretaireShow(Evaluation $evaluation): View
    {
        return $this->sharedAssignerShow($evaluation, $this->directeurSecretaireAssignerConfig());
    }

    private function directeurSecretaireEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->directeurSecretaireAssignerConfig());
    }

    private function directeurSecretaireUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->directeurSecretaireAssignerConfig());
    }

    private function directeurSecretaireSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->directeurSecretaireAssignerConfig());
    }

    private function directeurSecretaireDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->directeurSecretaireAssignerConfig());
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
            'id'                  => $a->id,
            'nom_prenom'          => trim(($a->prenom ?? '') . ' ' . ($a->nom ?? '')),
            'role'                => $a->role ?? '',
            'matricule'           => $a->matricule ?? '',
            'emploi'              => in_array($a->role, ['Agent', 'Conseiller DG'], true)
                                        ? ($a->poste ?? $a->role ?? '')
                                        : ($a->role ?? ''),
            'entite_nom'          => $parentNom,
            'direction_service'   => $ctx->getNom(),
            'date_prise_fonction' => $a->date_debut_fonction?->format('d/m/Y') ?? '',
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
        return $this->sharedStore($request, $this->chefStoreConfig());
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
                : route('chef.guichets');
            $extra['editRoute']      = 'chef.evaluations.edit';
            $extra['soumettreRoute'] = 'chef.evaluations.submit';
            $extra['destroyRoute']   = 'chef.evaluations.destroy';
        }

        return $this->resolveEvaluationView($evaluation, $extra);
    }

    private function chefEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->chefAssignerConfig());
    }

    private function chefUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->chefAssignerConfig());
    }

    private function chefSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->chefAssignerConfig());
    }

    private function chefStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedStatut($request, $evaluation, $this->chefReceivedConfig());
    }

    private function chefReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedReclamer($request, $evaluation, $this->chefReceivedConfig());
    }

    private function chefCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        return $this->sharedCommentaire($request, $evaluation, $this->chefReceivedConfig());
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
        return $this->sharedDestroy($evaluation, $this->chefAssignerConfig());
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
            'backUrl'          => route('chef.guichets'),
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
        $ctx     = $this->getChefContext();
        $guichet = Guichet::findOrFail($request->integer('guichet_id'));
        return $this->sharedStore($request, $this->chefGuichetStoreConfig($guichet, $ctx));
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
        return $this->sharedReceivedShow($evaluation, $this->personnelReceivedConfig());
    }

    private function personnelStatut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedStatut($request, $evaluation, $this->personnelReceivedConfig());
    }

    private function personnelReclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedReclamer($request, $evaluation, $this->personnelReceivedConfig());
    }

    private function personnelCommentaire(Request $request, Evaluation $evaluation): mixed
    {
        return $this->sharedCommentaire($request, $evaluation, $this->personnelReceivedConfig());
    }

    private function personnelExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedReceivedExportPdf($evaluation, $this->personnelReceivedConfig());
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
            'isRh'               => true,
            'repondreRoute'      => 'rh.reclamations.repondre',
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
        $entite = Entite::latest()->first();
        return User::where('role', 'Secretaire_Assistante')
            ->when($entite?->dga_secretaire_agent_id, fn ($q) =>
                $q->where('agent_id', '!=', $entite->dga_secretaire_agent_id)
            )
            ->first();
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
            'prefilledMatricule'          => $secAgent?->matricule ?? '',
            'prefilledNomPrenom'          => $secAgent ? trim(($secAgent->prenom ?? '') . ' ' . ($secAgent->nom ?? '')) : $secretaire->name,
            'prefilledAgentId'            => $secAgent?->id,
            'prefilledEmploi'             => $secAgent?->role ?? 'Secrétaire',
            'prefilledDatePriseFonction'  => $secAgent?->date_debut_fonction?->format('d/m/Y') ?? '',
            'entiteNom'                   => 'Faitière des Caisses Populaires',
            'prefilledDirectionService'   => 'Secrétariat',
        ]));
    }

    private function assistanteStore(Request $request): RedirectResponse
    {
        return $this->sharedStore($request, $this->assistanteStoreConfig());
    }

    private function assistanteEdit(Evaluation $evaluation): View
    {
        return $this->sharedEdit($evaluation, $this->assistanteAssignerConfig());
    }

    private function assistanteUpdate(Request $request, Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedUpdate($request, $evaluation, $this->assistanteAssignerConfig());
    }

    private function assistanteShow(Evaluation $evaluation): View
    {
        return $this->sharedAssignerShow($evaluation, $this->assistanteAssignerConfig());
    }

    private function assistanteExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedAssignerExportPdf($evaluation, $this->assistanteAssignerConfig());
    }

    private function directeurSecretaireExportPdf(Evaluation $evaluation): Response
    {
        return $this->sharedAssignerExportPdf($evaluation, $this->directeurSecretaireAssignerConfig());
    }

    private function assistanteSubmit(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedSubmit($evaluation, $this->assistanteAssignerConfig());
    }

    private function assistanteDestroy(Evaluation $evaluation): RedirectResponse
    {
        return $this->sharedDestroy($evaluation, $this->assistanteAssignerConfig());
    }
}
