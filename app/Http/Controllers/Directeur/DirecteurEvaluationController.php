<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\SubjectiveCriteriaTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurEvaluationController — Évaluations du directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Gère deux flux d'évaluations distincts :
 *
 *  A) Évaluations REÇUES par le directeur
 *     - Créées par le DG ou la PCA pour l'entité du directeur.
 *     - evaluable_type = entité (Direction|Caisse|DelegationTechnique)
 *     - evaluable_role = 'manager'
 *     - Le directeur peut accepter (valide) ou refuser (refuse) une fiche soumise.
 *
 *  B) Évaluations CRÉÉES par le directeur pour ses chefs de service
 *     - evaluable_type = Service::class
 *     - evaluable_role = 'manager'
 *     - evaluateur_id  = Auth::id()
 *     - Le directeur crée la fiche en brouillon, la soumet ou la supprime.
 *
 * Calcul de la note finale :
 *   note_criteres_objectifs  = moyenne_objectifs  × 0,75
 *   note_criteres_subjectifs = moyenne_subjectifs × 0,25
 *   note_finale              = (objectifs + subjectifs) × 2   → sur 10
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurEvaluationController extends Controller
{
    // ── Authorization helpers ──────────────────────────────────────────────

    /**
     * Résout et retourne le contexte du directeur connecté.
     */
    private function getContext(): DirecteurEntity
    {
        return DirecteurEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que l'évaluation a bien été reçue (adressée) au directeur connecté.
     * Contrôle : evaluable_type = entité du directeur, evaluable_id = id de l'entité, role = 'manager'.
     */
    private function authorizeReceivedEval(Evaluation $evaluation): DirecteurEntity
    {
        $ctx = $this->getContext();

        if (
            $evaluation->evaluable_type !== $ctx->modelClass ||
            (int) $evaluation->evaluable_id !== $ctx->getId() ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager'
        ) {
            abort(403);
        }

        return $ctx;
    }

    /**
     * Vérifie que l'évaluation a été créée par le directeur connecté
     * pour l'un de ses chefs de service (Service appartenant à son entité).
     */
    private function authorizeCreatedEval(Evaluation $evaluation): DirecteurEntity
    {
        $ctx = $this->getContext();

        if (
            $evaluation->evaluable_type !== Service::class ||
            strtolower((string) ($evaluation->evaluable_role ?? '')) !== 'manager' ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }

        // Vérifie que le service évalué appartient bien à l'entité de ce directeur.
        $service = Service::find($evaluation->evaluable_id);
        if (! $service || ! $ctx->serviceOwnedBy($service)) {
            abort(403);
        }

        return $ctx;
    }

    // ── Créer une évaluation pour un chef de service ──────────────────────

    /**
     * Affiche le formulaire de création d'une évaluation pour un chef de service.
     *
     * - Charge la liste des services rattachés à l'entité du directeur.
     * - Si un service est pré-sélectionné (via ?service_id=X), charge ses fiches
     *   d'objectifs acceptées et non échues pour pré-remplir les critères objectifs.
     * - Charge les templates de critères subjectifs actifs (SubjectiveCriteriaTemplate).
     */
    public function create(Request $request): View
    {
        $ctx       = $this->getContext();
        $direction = $ctx->entity;

        // Tous les services rattachés à l'entité du directeur
        $services = $ctx->getServices();

        // Pré-sélection du service via paramètre URL (ex: depuis la page subordonné)
        $preselectedId   = (int) $request->get('service_id', 0);
        $selectedService = $services->firstWhere('id', $preselectedId);

        // Si l'entité n'a qu'un seul service, on le sélectionne automatiquement
        if (! $selectedService && $services->count() === 1) {
            $selectedService = $services->first();
        }

        // Fiches d'objectifs acceptées pour le service sélectionné (non échues)
        // Utilisées pour pré-remplir les critères objectifs du formulaire JS
        $objectiveOptions = [];
        if ($selectedService) {
            $today  = now()->toDateString();
            $fiches = FicheObjectif::query()
                ->with('objectifs')
                ->where('statut', 'acceptee')
                ->whereDate('date_echeance', '>=', $today)
                ->where('assignable_type', Service::class)
                ->where('assignable_id', $selectedService->id)
                ->orderBy('titre')
                ->get();

            foreach ($fiches as $fiche) {
                $objectiveOptions[] = [
                    'id'            => $fiche->id,
                    'titre'         => $fiche->titre,
                    'date_echeance' => $fiche->date_echeance instanceof Carbon
                        ? $fiche->date_echeance->toDateString()
                        : (string) $fiche->date_echeance,
                    'objectifs'     => $fiche->objectifs->map(fn ($item) => [
                        'source_fiche_objectif_objectif_id' => $item->id,
                        'titre'                             => $item->description,
                    ])->values()->all(),
                ];
            }
        }

        // Templates de critères subjectifs actifs, triés par ordre
        $subjectiveTemplates = $this->buildSubjectiveTemplates();

        // Valeurs précédentes pour les tableaux formations/expériences (après erreur de validation)
        $oldFormations = old('identification.formations', [['periode' => '', 'libelle' => '', 'domaine' => '']]);
        if (! is_array($oldFormations) || $oldFormations === []) {
            $oldFormations = [['periode' => '', 'libelle' => '', 'domaine' => '']];
        }

        $oldExperiences = old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]);
        if (! is_array($oldExperiences) || $oldExperiences === []) {
            $oldExperiences = [['periode' => '', 'poste' => '', 'observations' => '']];
        }

        $displayYear = now()->year;

        return view('directeur.evaluations.create', compact(
            'direction',
            'services',
            'selectedService',
            'objectiveOptions',
            'subjectiveTemplates',
            'oldFormations',
            'oldExperiences',
            'displayYear',
        ));
    }

    /**
     * Persiste une nouvelle évaluation pour un chef de service.
     *
     * Étapes :
     *  1. Validation des champs du formulaire.
     *  2. Vérification que service_id fait partie des services de l'entité.
     *  3. Conversion des dates MM/AAAA → AAAA-MM-01.
     *  4. Nettoyage des formations / expériences (suppression des lignes vides).
     *  5. Normalisation et scoring des critères subjectifs et objectifs.
     *  6. Persistance en transaction (Evaluation + Identification + Critères + SousCritères).
     */
    public function store(Request $request): RedirectResponse
    {
        $ctx        = $this->getContext();
        $user       = Auth::user();
        $serviceIds = $ctx->getServiceIds(); // Whitelist des IDs autorisés

        $validated = $request->validate([
            'service_id'                       => ['required', 'integer', 'in:'.implode(',', $serviceIds ?: [0])],
            'date_debut'                       => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'date_fin'                         => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{4}$/'],
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.semestre'          => ['required', 'in:1,2'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.emploi'            => ['nullable', 'string', 'max:255'],
            'identification.direction'         => ['nullable', 'string', 'max:255'],
            'identification.direction_service' => ['nullable', 'string', 'max:255'],
            'identification.formations'        => ['nullable', 'array'],
            'identification.formations.*.periode' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.libelle' => ['nullable', 'string', 'max:255'],
            'identification.formations.*.domaine' => ['nullable', 'string', 'max:255'],
            'identification.experiences'       => ['nullable', 'array'],
            'identification.experiences.*.periode'      => ['nullable', 'string', 'max:255'],
            'identification.experiences.*.poste'        => ['nullable', 'string', 'max:255'],
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

        $service = Service::findOrFail($validated['service_id']);

        // Conversion du format MM/AAAA → AAAA-MM-01 (stocké comme date SQL)
        $dateDebut = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_debut']);
        $dateFin   = preg_replace_callback('/^(0[1-9]|1[0-2])\/(\d{4})$/', fn ($m) => $m[2].'-'.$m[1].'-01', $validated['date_fin']);

        if (strtotime($dateFin) < strtotime($dateDebut)) {
            return back()->withInput()->withErrors(['date_fin' => 'La date de fin doit être postérieure à la date de début.']);
        }

        // Normalisation de la date d'évaluation dans la section identification
        $identification = $validated['identification'] ?? [];
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        // Nettoyage : suppression des lignes de formation entièrement vides
        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($row) => [
                'periode' => trim((string) ($row['periode'] ?? '')),
                'libelle' => trim((string) ($row['libelle'] ?? '')),
                'domaine' => trim((string) ($row['domaine'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['libelle'] !== '' || $row['domaine'] !== '')
            ->values()->all();

        // Nettoyage : suppression des lignes d'expérience entièrement vides
        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($row) => [
                'periode'      => trim((string) ($row['periode'] ?? '')),
                'poste'        => trim((string) ($row['poste'] ?? '')),
                'observations' => trim((string) ($row['observations'] ?? '')),
            ])
            ->filter(fn ($row) => $row['periode'] !== '' || $row['poste'] !== '' || $row['observations'] !== '')
            ->values()->all();

        // Normalisation des critères :
        //   - subjectifs : strict=false → les lignes sans libellé de sous-critère sont gardées (libellé = '-')
        //   - objectifs  : strict=true  → les lignes sans sous-critère noté sont ignorées
        $normalizedSubjective = $this->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.']);
        }

        // Calcul des scores (pondération 75 % objectifs / 25 % subjectifs × 2 = note /10)
        $scores = $this->computeScores($normalizedSubjective, $normalizedObjective);

        // Résolution de l'année de notation (table annees)
        try {
            $anneeId = Annee::resolveIdForDate($dateDebut);
        } catch (\Throwable) {
            $anneeId = null;
        }

        // Transaction : Evaluation → Identification → Critères → SousCritères
        $evaluation = DB::transaction(function () use (
            $user, $service, $dateDebut, $dateFin, $anneeId,
            $scores, $validated, $identification,
            $normalizedSubjective, $normalizedObjective
        ) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => Service::class,
                'evaluable_id'              => $service->id,
                'evaluable_role'            => 'manager',
                'annee_id'                  => $anneeId,
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

            // Section identification (fiche de renseignements du chef de service)
            $evaluation->identification()->create($identification);

            // Critères subjectifs puis objectifs (fusionnés dans la boucle)
            foreach (array_merge($normalizedSubjective, $normalizedObjective) as $criterion) {
                $critere = $evaluation->criteres()->create([
                    'type'                              => $criterion['type'],
                    'ordre'                             => $criterion['ordre'],
                    'titre'                             => $criterion['titre'],
                    'description'                       => $criterion['description'],
                    'note_globale'                      => $criterion['note_globale'],
                    'observation'                       => $criterion['observation'],
                    'source_template_id'                => $criterion['source_template_id'],
                    'source_fiche_objectif_id'          => $criterion['source_fiche_objectif_id'],
                    'source_fiche_objectif_objectif_id' => $criterion['source_fiche_objectif_objectif_id'],
                ]);
                // Sous-critères du critère courant
                foreach ($criterion['subcriteria'] as $sub) {
                    $critere->sousCriteres()->create([
                        'ordre'       => $sub['ordre'],
                        'libelle'     => $sub['libelle'],
                        'note'        => $sub['note'],
                        'observation' => $sub['observation'],
                    ]);
                }
            }

            return $evaluation;
        });

        return redirect()
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', "Évaluation créée pour le chef du service « {$service->nom} ».");
    }

    /**
     * Affiche le détail d'une évaluation (reçue ou créée).
     *
     * La même route sert les deux sens :
     *  - Évaluation reçue ($isReceived = true) : le directeur est l'évalué.
     *  - Évaluation créée ($isCreated = true)  : le directeur est l'évaluateur.
     * Les deux variables sont passées à la vue pour adapter l'affichage et les actions.
     */
    public function show(Evaluation $evaluation): View
    {
        $ctx       = $this->getContext();
        $direction = $ctx->entity;

        // Déterminer si l'évaluation est reçue par ce directeur
        $isReceived = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId()
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager';

        // Déterminer si l'évaluation a été créée par ce directeur pour un de ses services
        $isCreated = $evaluation->evaluable_type === Service::class
            && strtolower((string) ($evaluation->evaluable_role ?? '')) === 'manager'
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        // Pour une évaluation créée, vérifier que le service cible appartient à l'entité
        if ($isCreated) {
            $service = Service::find($evaluation->evaluable_id);
            if (! $service || ! $ctx->serviceOwnedBy($service)) {
                abort(403);
            }
        }

        // Chargement eager des relations pour éviter les N+1
        $evaluation->load(['evaluateur', 'evaluable', 'identification', 'criteres.sousCriteres']);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();

        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));

        // Libellé et type de la cible selon le sens de l'évaluation
        $cibleLabel = $evaluation->identification?->nom_prenom
            ?? ($isReceived
                ? $ctx->getDirecteurNomPrenom()
                : ($evaluation->evaluable?->nom ?? '-'));
        $cibleType  = $isReceived
            ? $ctx->getRoleLabel()
            : 'Chef de service — '.($evaluation->evaluable?->nom ?? '-');

        // Badge de statut
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

        return view('directeur.evaluations.show', compact(
            'evaluation',
            'direction',
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

    /**
     * Accepte ou refuse une évaluation reçue (soumise par le DG/PCA).
     *
     * Seule une évaluation au statut 'soumis' peut être traitée.
     * Une notification est envoyée à l'évaluateur (DG/PCA) après l'action.
     */
    public function statut(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeReceivedEval($evaluation);

        if ($evaluation->statut !== 'soumis') {
            return back()->with('error', 'Cette action n\'est possible que sur une évaluation soumise.');
        }

        $request->validate(['action' => ['required', 'in:accepter,refuser']]);

        $action             = $request->input('action');
        $evaluation->statut = $action === 'accepter' ? 'valide' : 'refuse';
        $evaluation->save();

        // Notifie l'évaluateur (DG/PCA) du résultat
        if ($evaluation->evaluateur_id) {
            $directeur   = Auth::user();
            $actionLabel = $action === 'accepter' ? 'accepté' : 'refusé';
            Alerte::notifier(
                (int) $evaluation->evaluateur_id,
                "Fiche d'évaluation {$actionLabel}e par le Directeur",
                "Le Directeur {$directeur?->name} a {$actionLabel} la fiche d'évaluation que vous lui avez soumise.",
                $action === 'accepter' ? 'moyenne' : 'haute'
            );
        }

        $msg = $action === 'accepter' ? 'Évaluation acceptée.' : 'Évaluation refusée.';

        return redirect()->route('directeur.evaluations.show', $evaluation)->with('status', $msg);
    }

    /**
     * Soumet une évaluation brouillon (créée par le directeur pour un chef de service).
     *
     * Passe le statut de 'brouillon' à 'soumis'.
     * Seule une évaluation en brouillon peut être soumise.
     */
    public function submit(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        return redirect()
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', 'Évaluation soumise au chef de service.');
    }

    /**
     * Supprime une évaluation créée par le directeur.
     *
     * Une évaluation validée ne peut pas être supprimée.
     */
    public function destroy(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeCreatedEval($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()
            ->route('directeur.mon-espace', ['tab' => 'dashboard'])
            ->with('status', 'Évaluation supprimée.');
    }

    /**
     * Génère et télécharge le PDF d'une évaluation (reçue ou créée).
     *
     * Réutilise la vue PDF de l'espace DG pour une cohérence visuelle.
     */
    public function exportPdf(Evaluation $evaluation)
    {
        $ctx = $this->getContext();

        // Vérifie les droits d'accès (reçue ou créée par ce directeur)
        $isReceived = $evaluation->evaluable_type === $ctx->modelClass
            && (int) $evaluation->evaluable_id === $ctx->getId();
        $isCreated  = $evaluation->evaluable_type === Service::class
            && (int) $evaluation->evaluateur_id === Auth::id();

        if (! $isReceived && ! $isCreated) {
            abort(403);
        }

        $evaluation->load(['evaluateur', 'evaluable', 'identification', 'criteres.sousCriteres']);

        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $note               = (float) $evaluation->note_finale;
        $mention            = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $cibleLabel         = $evaluation->identification?->nom_prenom ?? '-';
        $cibleType          = $isReceived ? $ctx->getRoleLabel() : 'Chef de service';

        $pdf = Pdf::loadView('dg.evaluations.pdf', compact(
            'evaluation', 'subjectiveCriteria', 'objectiveCriteria', 'mention', 'cibleLabel', 'cibleType'
        ));

        return $pdf->download('evaluation-'.$evaluation->id.'-directeur.pdf');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Charge tous les templates de critères subjectifs actifs depuis la base.
     * Retourne un tableau PHP brut (encodé en JSON dans la vue pour le moteur JS).
     */
    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn ($template) => [
                'id'          => $template->id,
                'ordre'       => $template->ordre,
                'titre'       => $template->titre,
                'description' => $template->description,
                'subcriteria' => $template->subcriteria->map(fn ($sub) => [
                    'libelle' => $sub->libelle,
                    'ordre'   => $sub->ordre,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Normalise et valide les critères soumis par le formulaire JS.
     *
     * @param  array   $criteria  Tableau de critères bruts (depuis request)
     * @param  string  $type      'subjectif' ou 'objectif'
     * @param  int     $minNote   Note minimale autorisée (1 pour les deux types)
     * @param  int     $maxNote   Note maximale autorisée (5 pour les deux types)
     * @param  bool    $strict    Si true, les critères sans sous-critères sont ignorés.
     *                            Si false (subjectifs), un sous-critère par défaut '-' est injecté.
     * @return array   Critères normalisés prêts à être persistés
     */
    private function normalizeCriteria(array $criteria, string $type, int $minNote, int $maxNote, bool $strict = true): array
    {
        $normalized = [];
        foreach (array_values($criteria) as $idx => $criterion) {
            if (! is_array($criterion)) continue;
            $title = trim((string) ($criterion['titre'] ?? ''));
            if ($title === '') continue;

            $subcriteria = [];
            foreach (array_values((array) ($criterion['subcriteria'] ?? [])) as $subIdx => $sub) {
                if (! is_array($sub)) continue;
                $label = trim((string) ($sub['libelle'] ?? ''));
                if ($label === '') {
                    if ($strict) continue;
                    $label = '-';
                }
                $note          = max($minNote, min($maxNote, (float) ($sub['note'] ?? $minNote)));
                $subcriteria[] = [
                    'ordre'       => $subIdx + 1,
                    'libelle'     => $label,
                    'note'        => $note,
                    'observation' => filled($sub['observation'] ?? null) ? trim((string) $sub['observation']) : null,
                ];
            }

            if ($strict && $subcriteria === []) continue;
            if (! $strict && $subcriteria === []) {
                $subcriteria = [['ordre' => 1, 'libelle' => '-', 'note' => $minNote, 'observation' => null]];
            }

            $normalized[] = [
                'type'                              => $type,
                'ordre'                             => $idx + 1,
                'titre'                             => $title,
                'description'                       => filled($criterion['description'] ?? null) ? trim((string) $criterion['description']) : null,
                'note_globale'                      => round(collect($subcriteria)->avg('note') ?? 0, 2),
                'observation'                       => filled($criterion['observation'] ?? null) ? trim((string) $criterion['observation']) : null,
                'source_template_id'                => isset($criterion['source_template_id']) ? (int) $criterion['source_template_id'] : null,
                'source_fiche_objectif_id'          => isset($criterion['source_fiche_objectif_id']) ? (int) $criterion['source_fiche_objectif_id'] : null,
                'source_fiche_objectif_objectif_id' => isset($criterion['source_fiche_objectif_objectif_id']) ? (int) $criterion['source_fiche_objectif_objectif_id'] : null,
                'subcriteria'                       => $subcriteria,
            ];
        }

        return $normalized;
    }

    /**
     * Calcule les scores pondérés à partir des critères normalisés.
     *
     * Pondération :
     *   objectifs  × 0,75 = note_criteres_objectifs   (sur 3,75)
     *   subjectifs × 0,25 = note_criteres_subjectifs  (sur 1,25)
     *   note_finale = (objectifs + subjectifs) × 2    (ramène sur 10)
     */
    private function computeScores(array $subjectiveCriteria, array $objectiveCriteria): array
    {
        $moyObj  = round(collect($objectiveCriteria)->avg('note_globale') ?? 0, 2);
        $moySubj = round(collect($subjectiveCriteria)->avg('note_globale') ?? 0, 2);
        $noteObj  = round($moyObj * 0.75, 2);
        $noteSubj = round($moySubj * 0.25, 2);

        return [
            'moyenne_objectifs'        => $moyObj,
            'moyenne_subjectifs'       => $moySubj,
            'note_criteres_objectifs'  => $noteObj,
            'note_criteres_subjectifs' => $noteSubj,
            'note_finale'              => round(($noteObj + $noteSubj) * 2, 2),
        ];
    }

    /**
     * Tente de parser une date saisie librement (JJ/MM/AAAA ou AAAA-MM-JJ)
     * et la retourne au format AAAA-MM-JJ (stockage SQL).
     * Retourne null si le format ne correspond à aucun des deux attendus.
     */
    private function normalizeDateValue(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date && $date->format($format) === $value) return $date->toDateString();
            } catch (\Throwable) {}
        }

        return null;
    }
}
