<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * ChefEvaluationController — Évaluations du chef
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Gère deux flux d'évaluations pour un chef (Chef_Service, Chef_Agence,
 * ou Chef_Guichet) :
 *
 * A) Évaluations REÇUES par le chef (show uniquement)
 *    → evaluable_type = Agent::class, evaluable_id = agent du chef
 *    → Créées par un directeur ou supérieur hiérarchique
 *    → Le chef peut accepter (valide) ou refuser (refuse) si statut = soumis
 *
 * B) Évaluations CRÉÉES par le chef pour ses agents subordonnés
 *    → evaluable_type = Agent::class, evaluable_id = agent subordonné
 *    → evaluateur_id  = Auth::id()
 *    → evaluable_role = 'manager' (le chef évalue en tant que manager)
 *    → Le chef crée en brouillon, soumet, ou supprime
 *
 * Calcul de la note finale :
 *   note_criteres_objectifs  = moyenne_objectifs  × 0,75
 *   note_criteres_subjectifs = moyenne_subjectifs × 0,25
 *   note_finale              = (objectifs + subjectifs) × 2   → sur 10
 * ──────────────────────────────────────────────────────────────────────────────
 */
class ChefEvaluationController extends Controller
{
    /**
     * Injecte l'EvaluationService partagé qui centralise :
     *  - buildSubjectiveTemplates() : templates de critères subjectifs
     *  - normalizeCriteria()        : nettoyage des critères du formulaire
     *  - computeScores()            : calcul des notes pondérées
     *  - persistCriteria()          : enregistrement en base des critères
     *  - normalizeDateValue()       : conversion des dates JJ/MM/AAAA → SQL
     */
    public function __construct(private readonly EvaluationService $evaluationService) {}

    // ══════════════════════════════════════════════════════════════════════════
    // HELPERS D'AUTORISATION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Récupère le contexte chef du User connecté.
     * Déclenche un 403 si l'utilisateur n'est pas lié à une structure valide.
     */
    private function getContext(): ChefEntity
    {
        return ChefEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que l'évaluation a été créée par ce chef pour un de ses agents.
     *
     * Conditions requises :
     *  1. evaluable_type = Agent::class
     *  2. evaluable_role = 'manager'
     *  3. evaluateur_id  = Auth::id()
     *  4. L'agent cible appartient bien à la structure du chef
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function authorizeCreatedEval(Evaluation $evaluation): ChefEntity
    {
        $ctx = $this->getContext();

        // Vérifie les conditions métier de base
        if (
            $evaluation->evaluable_type !== Agent::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403, 'Cette évaluation ne vous appartient pas.');
        }

        // Vérifie que l'agent évalué est bien dans la structure de ce chef
        $agent = Agent::find($evaluation->evaluable_id);
        if (! $agent || ! $ctx->agentOwnedBy($agent)) {
            abort(403, 'Cet agent n\'est pas dans votre structure.');
        }

        return $ctx;
    }

    /**
     * Vérifie que l'évaluation a été reçue par le chef connecté.
     *
     * L'évaluation est reçue si :
     *   evaluable_type = Agent::class
     *   evaluable_id   = agent du chef connecté
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException (403)
     */
    private function authorizeReceivedEval(Evaluation $evaluation): ChefEntity
    {
        $ctx   = $this->getContext();
        $agent = $ctx->agent;

        // Cas a : directeur évalue le chef via son Agent
        $isReceivedAsAgent = $evaluation->evaluable_type === Agent::class
            && $agent
            && (int) $evaluation->evaluable_id === $agent->id;

        // Cas b : directeur évalue le chef directement via son compte User
        $isReceivedAsUser = $evaluation->evaluable_type === \App\Models\User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        if (! $isReceivedAsAgent && ! $isReceivedAsUser) {
            abort(403, 'Cette évaluation ne vous est pas adressée.');
        }

        return $ctx;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // CRÉER UNE ÉVALUATION POUR UN AGENT
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Affiche le formulaire de création d'une évaluation pour un agent.
     *
     * Si ?agent_id=X est passé en URL, l'agent est pré-sélectionné.
     * Les fiches d'objectifs acceptées et non échues de l'agent sont chargées
     * pour pré-remplir les critères objectifs.
     */
    public function create(Request $request): View
    {
        $ctx = $this->getContext();

        // Charge les agents subordonnés de la structure avec leurs informations
        $agents = $ctx->getAgents()->load([]); // déjà ordonnés par nom dans getAgents()

        // Pré-sélection via ?agent_id=X
        $preselectedId    = (int) $request->input('agent_id', 0);
        $selectedAgent    = $agents->firstWhere('id', $preselectedId);

        // Si un seul agent dans la structure, on le pré-sélectionne automatiquement
        if (! $selectedAgent && $agents->count() === 1) {
            $selectedAgent = $agents->first();
        }

        // ── Fiches d'objectifs disponibles pour l'agent sélectionné ──────────
        // On ne charge que les fiches :
        //   • statut = 'acceptee' (l'agent a accepté la fiche)
        //   • date_echeance >= aujourd'hui (la fiche est encore active)
        //   • assignable = cet agent
        $objectiveOptions = [];
        $today = now()->toDateString();

        if ($selectedAgent) {
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
                ->where('assignable_type', Agent::class)
                ->where('assignable_id', $selectedAgent->id)
                ->orderBy('titre')
                ->get();

            // Formate les fiches pour le sélecteur JS dans la vue
            foreach ($fiches as $fiche) {
                $objectiveOptions[] = [
                    'id'            => $fiche->id,
                    'titre'         => $fiche->titre,
                    'date_echeance' => $fiche->date_echeance
                        ? (is_string($fiche->date_echeance) ? $fiche->date_echeance : $fiche->date_echeance->toDateString())
                        : '',
                    // Seuls les objectifs démarrés (avancement > 0) peuvent être évalués
                    'objectifs'     => $fiche->objectifs
                        ->filter(fn ($item) => (int) ($item->avancement_percentage ?? 0) > 0)
                        ->map(fn ($item) => [
                            'source_fiche_objectif_objectif_id' => $item->id,
                            'titre'                             => $item->description,
                        ])->values()->all(),
                ];
            }
        }

        // ── Templates de critères subjectifs ─────────────────────────────────
        // Critères standard actifs, récupérés depuis SubjectiveCriteriaTemplate
        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        // ── Récupération des anciennes valeurs après erreur de validation ─────
        $oldFormations  = old('identification.formations');
        $oldExperiences = old('identification.experiences');

        // ── Données JSON pour auto-remplissage des champs identification ──────
        $agentsJson = $agents->map(fn ($a) => [
            'id'         => $a->id,
            'nom_prenom' => trim($a->prenom . ' ' . $a->nom),
            'emploi'     => $a->role ?? 'Agent',
            'entite_nom' => $ctx->getNom(),
            'matricule'  => $a->matricule ?? '',
        ])->values()->toArray();

        // Pré-remplissage initial des champs si agent pré-sélectionné
        $prefilledNomPrenom        = null;
        $prefilledEmploi           = null;
        $prefilledDirectionService = null;
        $prefilledAgentId          = $selectedAgent?->id ?? null;

        if ($selectedAgent) {
            $prefilledNomPrenom        = trim($selectedAgent->prenom . ' ' . $selectedAgent->nom);
            $prefilledEmploi           = $selectedAgent->role ?? 'Agent';
            $prefilledDirectionService = $ctx->getNom();
        }

        $openAnnee      = Annee::currentOpen();
        $openSemestres  = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre   = $openSemestres->first();
        $displayYear    = $openAnnee?->annee ?? now()->year;
        $entiteNom      = $ctx->getNom();

        // Pré-remplissage du matricule pour l'agent pré-sélectionné
        $prefilledMatricule = $selectedAgent?->matricule ?? null;

        return view('chef.evaluations.create', compact(
            'ctx',
            'agents',
            'selectedAgent',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
            'agentsJson',
            'prefilledNomPrenom',
            'prefilledEmploi',
            'prefilledDirectionService',
            'prefilledAgentId',
            'displayYear',
            'entiteNom',
            'openAnnee',
            'openSemestres',
            'openSemestre',
            'prefilledMatricule',
        ));
    }

    /**
     * Persiste une nouvelle évaluation pour un agent subordonné.
     *
     * Étapes :
     *  1. Validation des champs du formulaire
     *  2. Vérification que agent_id appartient à la structure du chef
     *  3. Conversion des dates MM/AAAA → AAAA-MM-01
     *  4. Nettoyage des formations / expériences (lignes vides supprimées)
     *  5. Normalisation des critères objectifs et subjectifs
     *  6. Calcul des scores (pondération 75 % / 25 %)
     *  7. Persistance en transaction (Evaluation + Identification + Critères)
     *  8. Notification à l'agent évalué
     */
    public function store(Request $request): RedirectResponse
    {
        $ctx = $this->getContext();

        // Liste des IDs d'agents autorisés (appartenant à la structure du chef)
        $agentIds = $ctx->getAgentIds();

        // Validation de base du formulaire
        $validated = $request->validate([
            'agent_id'                         => ['required', 'integer', 'in:' . implode(',', $agentIds ?: [0])],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.grade'             => ['required', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'               => ['nullable', 'array'],
            'identification.formations.*.periode'     => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle'     => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine'     => ['nullable', 'string', 'max:255'],
            'identification.experiences'              => ['nullable', 'array'],
            'identification.experiences.*.periode'    => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
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

        // ── Vérification stricte que l'agent appartient à ce chef ────────────
        $evaluableModel = Agent::findOrFail($validated['agent_id']);
        if (! $ctx->agentOwnedBy($evaluableModel)) {
            abort(403, 'Cet agent n\'est pas sous votre responsabilité.');
        }

        // ── Résolution automatique du semestre ouvert ─────────────────────────
        $openAnnee = Annee::currentOpen();
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

        // ── Normalisation de la date d'évaluation dans l'identification ───────
        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;
        $identification['matricule'] = $evaluableModel->matricule ?? null;
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors([
                    'identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.',
                ]);
            }
            $identification['date_evaluation'] = $normalized;
        }

        // ── Nettoyage des formations (lignes entièrement vides supprimées) ─────
        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        // ── Nettoyage des expériences (lignes entièrement vides supprimées) ────
        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        // ── Normalisation des critères ────────────────────────────────────────
        // Subjectifs : strict=false → les lignes sans libellé deviennent '-'
        // Objectifs  : strict=true  → les lignes sans sous-critère noté sont supprimées
        $normalizedSubjective = $this->evaluationService->normalizeCriteria(
            (array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false
        );
        $normalizedObjective = $this->evaluationService->normalizeCriteria(
            (array) $request->input('objective_criteres', []), 'objectif', 1, 5
        );

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.',
            ]);
        }

        // ── Calcul des scores (pondération 75 % objectifs / 25 % subjectifs) ──
        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);


        $user = Auth::user();

        // ── Transaction : Evaluation + Identification + Critères + SousCritères
        $evaluation = DB::transaction(function () use (
            $user, $evaluableModel, $dateDebut, $dateFin, $anneeId, $semestreId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            // Création de l'évaluation principale
            $evaluation = Evaluation::create([
                'evaluable_type'            => Agent::class,
                'evaluable_id'              => $evaluableModel->id,
                'evaluable_role'            => 'manager',  // Le chef évalue en tant que manager
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
                'signature_evalue_nom'      => $validated['signature_evalue_nom']
                    ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom'  => $validated['signature_evaluateur_nom']
                    ?? $user->name,
                'date_signature_evalue'     => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
                'statut'                    => 'brouillon', // Toujours créé en brouillon
            ]);

            // Section identification (renseignements de l'agent évalué)
            $evaluation->identification()->create($identification);

            // Critères objectifs + subjectifs (avec leurs sous-critères)
            $this->evaluationService->persistCriteria(
                $evaluation,
                array_merge($normalizedSubjective, $normalizedObjective)
            );

            return $evaluation;
        });

        // ── Notification à l'agent évalué ─────────────────────────────────────
        // Cherche le User associé à l'agent pour lui envoyer une notification
        $agentUser = \App\Models\User::where('agent_id', $evaluableModel->id)->first();
        if ($agentUser) {
            Alerte::notifier(
                $agentUser->id,
                'Nouvelle évaluation créée',
                "Une évaluation a été créée pour vous par {$user->name}. Elle est actuellement en brouillon.",
                'basse'
            );
        }

        return redirect()
            ->route('chef.evaluations.show', $evaluation)
            ->with('status', 'Évaluation créée en brouillon. Relisez-la avant de la soumettre à l\'agent.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // AFFICHER UNE ÉVALUATION
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Affiche le détail d'une évaluation.
     *
     * La même route sert les deux sens :
     *  - Évaluation REÇUE ($isReceived = true) : le chef est l'évalué
     *  - Évaluation CRÉÉE ($isCreated  = true) : le chef est l'évaluateur
     */
    public function show(Evaluation $evaluation): View
    {
        $ctx   = $this->getContext();
        $agent = $ctx->agent;

        // ── Déterminer si l'évaluation est reçue par ce chef ─────────────────
        // Une évaluation peut être reçue de deux façons :
        //   a) evaluable_type = Agent::class  → créée par un chef de niveau supérieur
        //      qui cible l'agent lié à ce chef
        //   b) evaluable_type = User::class   → créée par un directeur ou DGA
        //      qui cible directement le compte User du chef (cas le plus fréquent)
        $isReceivedAsAgent = $evaluation->evaluable_type === Agent::class
            && $agent
            && (int) $evaluation->evaluable_id === $agent->id;

        $isReceivedAsUser = $evaluation->evaluable_type === \App\Models\User::class
            && (int) $evaluation->evaluable_id === Auth::id();

        $isReceived = $isReceivedAsAgent || $isReceivedAsUser;

        // ── Déterminer si l'évaluation a été créée par ce chef ───────────────
        // Créée = le chef est l'évaluateur, l'évalué est un de ses agents
        $isCreated = $evaluation->evaluable_type === Agent::class
            && (int) $evaluation->evaluateur_id === Auth::id()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        // Si créée, vérifie que l'agent évalué appartient bien à ce chef
        if ($isCreated) {
            $agentEvalue = Agent::find($evaluation->evaluable_id);
            if (! $agentEvalue || ! $ctx->agentOwnedBy($agentEvalue)) {
                $isCreated = false;
            }
        }

        // L'un ou l'autre doit être vrai, sinon 403
        if (! $isReceived && ! $isCreated) {
            abort(403, 'Vous n\'avez pas accès à cette évaluation.');
        }

        // ── Chargement eager des relations ────────────────────────────────────
        $evaluation->load(['evaluateur', 'evaluable', 'identification', 'criteres.sousCriteres']);

        // Séparation objectifs / subjectifs pour l'affichage
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));

        // ── Libellé de la cible selon le sens de l'évaluation ─────────────────
        $cibleLabel = $evaluation->identification?->nom_prenom
            ?? ($isReceived
                ? $ctx->getChefNomPrenom()
                : ($evaluation->evaluable
                    ? trim(($evaluation->evaluable->prenom ?? '') . ' ' . ($evaluation->evaluable->nom ?? ''))
                    : '-'));

        $cibleType = $isReceived
            ? $ctx->getRoleLabel()
            : 'Agent — ' . ($evaluation->identification?->direction_service ?? $ctx->getNom());

        // ── Badge de statut ────────────────────────────────────────────────────
        $statusClass = match ($evaluation->statut) {
            'valide'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis'    => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default     => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide'    => 'Acceptée',
            'soumis'    => 'Soumise',
            'refuse'    => 'Refusée',
            'brouillon' => 'Brouillon',
            default     => ucfirst((string) $evaluation->statut),
        };

        $ident = $evaluation->identification;

        return view('chef.evaluations.show', compact(
            'ctx',
            'evaluation',
            'isReceived',
            'isCreated',
            'objectiveCriteria',
            'subjectiveCriteria',
            'note',
            'mention',
            'cibleLabel',
            'cibleType',
            'statusClass',
            'statusLabel',
            'ident',
        ));
    }

    // ══════════════════════════════════════════════════════════════════════════
    // ACCEPTER / REFUSER UNE ÉVALUATION REÇUE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Accepte ou refuse une évaluation reçue (créée par un directeur).
     *
     * Seule une évaluation au statut 'soumis' peut être traitée.
     * Une notification est envoyée à l'évaluateur après l'action.
     */
    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeReceivedEval($evaluation);

        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
        }

        $request->validate([
            'action'      => ['required', 'in:accepter,refuser'],
            'motif_refus' => ['required_if:action,refuser', 'nullable', 'string', 'max:1000'],
        ]);

        $action             = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        if ($action === 'refuser') {
            $evaluation->motif_refus        = $request->input('motif_refus');
            $evaluation->statut_reclamation = 'en_attente';
        }
        $evaluation->save();

        // Notification à l'évaluateur (directeur) du résultat
        if ($evaluation->evaluateur_id) {
            $chef        = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                (int) $evaluation->evaluateur_id,
                "Fiche d'évaluation {$actionLabel}e par le chef",
                "Le chef {$chef?->name} a {$actionLabel} la fiche d'évaluation que vous lui avez soumise.",
                $action === 'accepter' ? 'basse' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()
            ->route('chef.evaluations.show', $evaluation)
            ->with('status', $msg);
    }

    public function reclamer(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeReceivedEval($evaluation);

        if ($evaluation->statut !== 'refuse') {
            return back()->with('error', "La réclamation n'est possible que sur une évaluation refusée.");
        }

        $request->validate([
            'reclamation' => ['required', 'string', 'max:1000'],
        ]);

        $evaluation->reclamation = $request->input('reclamation');
        $evaluation->save();

        return redirect()
            ->route('chef.evaluations.show', $evaluation)
            ->with('status', 'Votre réclamation a été enregistrée.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // MODIFIER UN BROUILLON
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Affiche le formulaire de modification d'un brouillon.
     */
    public function edit(Evaluation $evaluation): View|RedirectResponse
    {
        $ctx = $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return redirect()->route('chef.evaluations.show', $evaluation)
                ->with('error', 'Seules les évaluations en brouillon sont modifiables.');
        }

        $evaluation->load(['identification', 'criteres.sousCriteres', 'evaluable']);
        $agent = $evaluation->evaluable;

        // Fiches d'objectifs actives pour cet agent
        $today = now()->toDateString();
        $fiches = FicheObjectif::query()
            ->with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', Agent::class)
            ->where('assignable_id', $agent->id)
            ->orderBy('titre')
            ->get();

        $objectiveOptions = [];
        foreach ($fiches as $fiche) {
            $objectiveOptions[] = [
                'id'            => $fiche->id,
                'titre'         => $fiche->titre,
                'date_echeance' => $fiche->date_echeance
                    ? (is_string($fiche->date_echeance) ? $fiche->date_echeance : $fiche->date_echeance->toDateString())
                    : '',
                'objectifs' => $fiche->objectifs
                    ->filter(fn ($item) => (int) ($item->avancement_percentage ?? 0) > 0)
                    ->map(fn ($item) => [
                        'source_fiche_objectif_objectif_id' => $item->id,
                        'titre'                             => $item->description,
                    ])->values()->all(),
            ];
        }

        // Critères existants → format attendu par le JS du formulaire
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

        $ident         = $evaluation->identification;
        $openAnnee     = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();
        $displayYear   = $openAnnee?->annee ?? now()->year;
        $entiteNom     = $ctx->getNom();

        return view('chef.evaluations.edit', compact(
            'ctx',
            'evaluation',
            'agent',
            'ident',
            'objectiveOptions',
            'existingSubjectiveCriteria',
            'existingObjectiveCriteria',
            'entiteNom',
            'displayYear',
            'openAnnee',
            'openSemestres',
            'openSemestre',
        ));
    }

    /**
     * Met à jour un brouillon existant.
     */
    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            abort(403, 'Modification interdite pour une évaluation déjà soumise.');
        }

        $evaluableModel = Agent::findOrFail($evaluation->evaluable_id);

        $validated = $request->validate([
            'identification.nom_prenom'               => ['nullable', 'string', 'max:255'],
            'identification.date_evaluation'          => ['nullable', 'string', 'max:20'],
            'identification.matricule'                => ['nullable', 'string', 'max:255'],
            'identification.grade'                    => ['required', 'string', 'max:255'],
            'identification.emploi'                   => ['nullable', 'string', 'max:255'],
            'identification.direction'                => ['nullable', 'string', 'max:255'],
            'identification.direction_service'        => ['nullable', 'string', 'max:255'],
            'identification.formations'               => ['nullable', 'array'],
            'identification.formations.*.periode'     => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle'     => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine'     => ['nullable', 'string', 'max:255'],
            'identification.experiences'              => ['nullable', 'array'],
            'identification.experiences.*.periode'    => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.observations' => ['nullable', 'string', 'max:255'],
            'subjective_criteres'                     => ['required', 'array', 'min:1'],
            'objective_criteres'                      => ['required', 'array', 'min:1'],
            'points_a_ameliorer'                      => ['nullable', 'string'],
            'strategies_amelioration'                 => ['nullable', 'string'],
            'commentaire'                             => ['nullable', 'string', 'max:2000'],
            'signature_evalue_nom'                    => ['nullable', 'string', 'max:255'],
            'signature_evaluateur_nom'                => ['nullable', 'string', 'max:255'],
            'date_signature_evalue'                   => ['nullable', 'date'],
            'date_signature_evaluateur'               => ['nullable', 'date'],
        ]);

        // Résolution automatique du semestre ouvert
        $openAnnee = Annee::currentOpen();
        if (! $openAnnee) {
            return back()->withInput()->with('error', "Aucune année d'exercice ouverte.");
        }
        $semestre = $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->first();
        if (! $semestre) {
            return back()->withInput()->with('error', "Aucun semestre ouvert pour {$openAnnee->annee}.");
        }
        $dateDebut = $semestre->dateDebut()->toDateString();
        $dateFin   = $semestre->dateFin()->toDateString();

        // Normalisation de la date d'évaluation
        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;
        $identification['matricule'] = $evaluableModel->matricule ?? null;
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors([
                    'identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.',
                ]);
            }
            $identification['date_evaluation'] = $normalized;
        }

        // Nettoyage formations
        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        // Nettoyage expériences
        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        // Normalisation des critères
        $normalizedSubjective = $this->evaluationService->normalizeCriteria(
            (array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false
        );
        $normalizedObjective = $this->evaluationService->normalizeCriteria(
            (array) $request->input('objective_criteres', []), 'objectif', 1, 5
        );

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors([
                'subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.',
            ]);
        }

        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);
        $user   = Auth::user();

        DB::transaction(function () use (
            $evaluation, $user, $dateDebut, $dateFin,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation->update([
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
                'signature_evalue_nom'      => $validated['signature_evalue_nom']
                    ?? ($identification['nom_prenom'] ?? null),
                'signature_evaluateur_nom'  => $validated['signature_evaluateur_nom']
                    ?? $user->name,
                'date_signature_evalue'     => $validated['date_signature_evalue'] ?? null,
                'date_signature_evaluateur' => $validated['date_signature_evaluateur'] ?? null,
            ]);

            $evaluation->identification()->update($identification);

            $evaluation->criteres()->delete();
            $this->evaluationService->persistCriteria(
                $evaluation,
                array_merge($normalizedSubjective, $normalizedObjective)
            );
        });

        return redirect()
            ->route('chef.evaluations.show', $evaluation)
            ->with('status', 'Brouillon mis à jour avec succès.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SOUMETTRE UNE ÉVALUATION CRÉÉE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Soumet une évaluation brouillon créée par ce chef.
     *
     * Passe le statut de 'brouillon' → 'soumis'.
     * Notifie l'agent évalué de la soumission.
     * Seule une évaluation en brouillon peut être soumise.
     */
    public function submit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise (statut actuel : ' . $evaluation->statut . ').');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        // Notification à l'agent évalué
        $agentUser = \App\Models\User::where('agent_id', $evaluation->evaluable_id)->first();
        if ($agentUser) {
            Alerte::notifier(
                $agentUser->id,
                'Évaluation soumise',
                'Votre chef vient de vous soumettre une fiche d\'évaluation. Veuillez la consulter et l\'accepter ou la refuser.',
                'moyenne'
            );
        }

        return redirect()
            ->route('chef.evaluations.show', $evaluation)
            ->with('status', 'Évaluation soumise à l\'agent.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXPORTER UNE ÉVALUATION EN PDF
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Exporte une évaluation en PDF.
     *
     * Accessible pour les évaluations reçues ET créées par le chef.
     * Utilise le template PDF partagé si disponible, sinon celui de la DGA.
     */
    public function exportPdf(Evaluation $evaluation): \Illuminate\Http\Response
    {
        $ctx   = $this->getContext();
        $agent = $ctx->agent;

        // Vérifie que le chef est bien concerné par cette évaluation (reçue ou créée)
        $isReceived = ($evaluation->evaluable_type === Agent::class && $agent && (int) $evaluation->evaluable_id === $agent->id)
            || ($evaluation->evaluable_type === \App\Models\User::class && (int) $evaluation->evaluable_id === Auth::id());

        $isCreated = $evaluation->evaluable_type === Agent::class
            && (int) $evaluation->evaluateur_id === Auth::id()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        if ($evaluation->statut === 'brouillon') {
            abort(403, "Cette évaluation n'est pas encore disponible.");
        }

        $evaluation->load(['evaluateur', 'identification', 'criteres.sousCriteres', 'evaluable']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel         = $evaluation->identification?->nom_prenom
            ?? ($isReceived
                ? ($ctx->getChefNomPrenom() ?? Auth::user()?->name ?? '-')
                : ($evaluation->evaluable
                    ? trim(($evaluation->evaluable->prenom ?? '') . ' ' . ($evaluation->evaluable->nom ?? ''))
                    : '-'));
        $cibleType          = $isReceived ? $ctx->getRoleLabel() : 'Agent';

        $pdfView = view()->exists('pdf.evaluation') ? 'pdf.evaluation' : 'dg.evaluations.pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($pdfView, compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('evaluation-' . $evaluation->id . '.pdf');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // SUPPRIMER UNE ÉVALUATION CRÉÉE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Supprime une évaluation créée par ce chef.
     *
     * Une évaluation validée ne peut pas être supprimée.
     * Seules les évaluations en brouillon ou refusées peuvent l'être.
     */
    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()
            ->route('chef.mon-espace')
            ->with('status', 'Évaluation supprimée.');
    }
}
