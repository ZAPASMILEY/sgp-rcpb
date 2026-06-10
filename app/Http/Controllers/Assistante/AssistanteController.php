<?php

namespace App\Http\Controllers\Assistante;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\EvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * AssistanteController — Gestion de la secrétaire par l'Assistante DG
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * L'Assistante DG peut :
 *  - Consulter le dossier de sa secrétaire (évaluations + objectifs)
 *  - Créer, soumettre et supprimer des évaluations pour sa secrétaire
 *  - Assigner et supprimer des fiches d'objectifs pour sa secrétaire
 *
 * La secrétaire est identifiée par son rôle 'Secretaire_Assistante'.
 * Comme l'entité faîtière est un singleton, il n'y a qu'une seule secrétaire
 * de l'assistante dans le système.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class AssistanteController extends Controller
{
    public function __construct(private readonly EvaluationService $evaluationService) {}

    // ── Helpers ────────────────────────────────────────────────────────────

    /** Vérifie que l'utilisateur connecté est bien l'Assistante DG. */
    private function assertIsAssistante(): void
    {
        if (Auth::user()?->role !== 'Assistante_Dg') {
            abort(403);
        }
    }

    /** Retourne la secrétaire de l'assistante ou null si non configurée.
     *  Exclut la secrétaire du DGA (identifiée via entites.dga_secretaire_agent_id). */
    private function findSecretaire(): ?User
    {
        $entite = Entite::latest()->first();
        return User::where('role', 'Secretaire_Assistante')
            ->when($entite?->dga_secretaire_agent_id, fn ($q) =>
                $q->where('agent_id', '!=', $entite->dga_secretaire_agent_id)
            )
            ->first();
    }

    /** Retourne la secrétaire ou redirige avec une erreur si absente. */
    private function requireSecretaire(): User|RedirectResponse
    {
        $s = $this->findSecretaire();
        if (! $s) {
            return redirect()->route('subordonne.mon-espace')
                ->with('error', 'Aucune secrétaire enregistrée dans le système.');
        }

        return $s;
    }

    /** Vérifie que l'évaluation appartient bien à la secrétaire de l'assistante. */
    private function authorizeEval(Evaluation $evaluation): void
    {
        $this->assertIsAssistante();
        $secretaire = $this->findSecretaire();

        if (
            $evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== (int) $secretaire?->id ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }
    }

    /** Vérifie que la fiche d'objectifs appartient bien à la secrétaire. */
    private function authorizeFiche(FicheObjectif $fiche): void
    {
        $this->assertIsAssistante();
        $secretaire = $this->findSecretaire();

        if (
            $fiche->assignable_type !== User::class ||
            (int) $fiche->assignable_id !== (int) $secretaire?->id
        ) {
            abort(403);
        }
    }

    // ── Dossier secrétaire ─────────────────────────────────────────────────

    public function secretaire(Request $request): View|RedirectResponse
    {
        $this->assertIsAssistante();
        $secretaire = $this->requireSecretaire();

        if ($secretaire instanceof RedirectResponse) {
            return $secretaire;
        }

        $tab = $request->get('tab', 'evaluations');

        $evaluations = Evaluation::where('evaluable_type', User::class)
            ->where('evaluable_id', $secretaire->id)
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $fiches = FicheObjectif::where('assignable_type', User::class)
            ->where('assignable_id', $secretaire->id)
            ->withCount('objectifs')
            ->orderByDesc('date')
            ->get();

        $ficheBlocksNew    = FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $secretaire->id)->whereNotIn('statut', ['refusee'])->exists();
        $ficheAcceptee     = FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $secretaire->id)->where('statut', 'acceptee')->exists();
        $evaluationEnCours = Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $secretaire->id)->where('evaluateur_id', Auth::id())->whereIn('statut', ['soumis', 'brouillon'])->exists();

        return view('subordonne.secretaire', compact('secretaire', 'tab', 'evaluations', 'fiches', 'ficheBlocksNew', 'ficheAcceptee', 'evaluationEnCours'));
    }

    // ── Évaluations ────────────────────────────────────────────────────────

    public function createEval(): View|RedirectResponse
    {
        $this->assertIsAssistante();
        $secretaire = $this->requireSecretaire();

        if ($secretaire instanceof RedirectResponse) {
            return $secretaire;
        }

        $today  = now()->toDateString();
        $fiches = FicheObjectif::with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', User::class)
            ->where('assignable_id', $secretaire->id)
            ->orderBy('titre')
            ->get();

        $objectiveOptions = $fiches->map(fn ($f) => [
            'id'            => $f->id,
            'titre'         => $f->titre,
            'date_echeance' => $f->date_echeance instanceof Carbon
                ? $f->date_echeance->toDateString()
                : (string) $f->date_echeance,
            'objectifs'     => $f->objectifs->filter(fn ($item) => (int) ($item->avancement_percentage ?? 0) > 0)->map(fn ($item) => [
                'source_fiche_objectif_objectif_id' => $item->id,
                'titre'                             => $item->description,
            ])->values()->all(),
        ])->values()->all();

        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        $oldFormations = old('identification.formations', [['periode' => '', 'libelle' => '', 'domaine' => '']]);
        if (! is_array($oldFormations) || $oldFormations === []) {
            $oldFormations = [['periode' => '', 'libelle' => '', 'domaine' => '']];
        }
        $oldExperiences = old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]);
        if (! is_array($oldExperiences) || $oldExperiences === []) {
            $oldExperiences = [['periode' => '', 'poste' => '', 'observations' => '']];
        }

        $direction   = (object) ['nom' => 'Direction Générale'];
        $openAnnee   = Annee::currentOpen();
        $openSemestres = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre  = $openSemestres->first();
        $displayYear = $openAnnee?->annee ?? now()->year;
        $backRoute   = route('assistante.secretaire', ['tab' => 'evaluations']);
        $storeRoute  = route('assistante.secretaire.evaluations.store');
        $layout      = 'layouts.subordonne';
        $prefilledMatricule = $secretaire->agent?->matricule ?? null;

        return view('directeur.subordonnes.evaluations.create', compact(
            'secretaire', 'direction', 'objectiveOptions',
            'subjectiveTemplates', 'oldFormations', 'oldExperiences',
            'displayYear', 'backRoute', 'storeRoute', 'layout',
            'openAnnee', 'openSemestres', 'openSemestre', 'prefilledMatricule'
        ));
    }

    public function storeEval(Request $request): RedirectResponse
    {
        $this->assertIsAssistante();
        $secretaire = $this->findSecretaire();

        if (! $secretaire) {
            abort(404);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'identification.nom_prenom'        => ['nullable', 'string', 'max:255'],
            'identification.date_evaluation'   => ['nullable', 'string', 'max:20'],
            'identification.matricule'         => ['nullable', 'string', 'max:255'],
            'identification.grade'             => ['required', 'string', 'max:255'],
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

        // Auto-detect open semestre
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

        $secretaire->loadMissing(['agent.entite', 'agent.direction', 'agent.delegationTechnique', 'agent.caisse', 'agent.agence']);
        $agentSec = $secretaire->agent;
        $identification = $validated['identification'] ?? [];
        $identification['semestre']          = (string) $semestre->numero;
        $identification['matricule']         = $agentSec?->matricule ?? null;
        $identification['nom_prenom']        = $agentSec ? trim($agentSec->prenom . ' ' . $agentSec->nom) : $secretaire->name;
        $identification['emploi']            = $agentSec?->poste ?: $agentSec?->role;
        $identification['direction']         = 'Faitière des Caisses Populaires du Burkina';
        $identification['direction_service'] = $agentSec?->direction?->nom
            ?? $agentSec?->delegationTechnique?->nom
            ?? $agentSec?->caisse?->nom
            ?? $agentSec?->agence?->nom
            ?? null;
        $raw = $identification['date_evaluation'] ?? null;
        if (! blank($raw)) {
            $normalized = $this->evaluationService->normalizeDateValue($raw);
            if ($normalized === null) {
                return back()->withInput()->withErrors(['identification.date_evaluation' => 'Format de date invalide. Utilisez JJ/MM/AAAA.']);
            }
            $identification['date_evaluation'] = $normalized;
        }

        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($r) => ['periode' => trim((string) ($r['periode'] ?? '')), 'libelle' => trim((string) ($r['libelle'] ?? '')), 'domaine' => trim((string) ($r['domaine'] ?? ''))])
            ->filter(fn ($r) => $r['periode'] !== '' || $r['libelle'] !== '' || $r['domaine'] !== '')
            ->values()->all();

        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($r) => ['periode' => trim((string) ($r['periode'] ?? '')), 'poste' => trim((string) ($r['poste'] ?? '')), 'observations' => trim((string) ($r['observations'] ?? ''))])
            ->filter(fn ($r) => $r['periode'] !== '' || $r['poste'] !== '' || $r['observations'] !== '')
            ->values()->all();

        $normalizedSubjective = $this->evaluationService->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->evaluationService->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères doivent contenir au moins une ligne notée.']);
        }

        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);

        // ── Unicité : 1 évaluation par semestre ─────────────────────────────
        if ($this->evaluationService->dejaEvalueeSemestre($secretaire->id, User::class, $semestreId)) {
            return back()->withInput()->with('error', "Une évaluation existe déjà pour {$secretaire->name} sur ce semestre.");
        }

        DB::transaction(function () use ($user, $secretaire, $dateDebut, $dateFin, $anneeId, $semestreId, $scores, $validated, $identification, $normalizedSubjective, $normalizedObjective) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => User::class,
                'evaluable_id'              => $secretaire->id,
                'evaluable_role'            => 'secretaire',
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
        });

        Alerte::notifier(
            $secretaire->id,
            'Nouvelle évaluation reçue',
            "L'Assistante DG vous a soumis une évaluation.",
            'haute',
            route('personnel.evaluations.show', $evaluation)
        );

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('status', "Évaluation créée pour {$secretaire->name}.");
    }

    public function showEval(Evaluation $evaluation): View
    {
        $this->authorizeEval($evaluation);
        $secretaire = $this->findSecretaire();
        $evaluation->load(['identification', 'criteres.sousCriteres']);

        $note               = (float) $evaluation->note_finale;
        $mention            = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $ident              = $evaluation->identification;
        $subjectiveTemplates = $subjectiveCriteria->isEmpty()
            ? \App\Models\SubjectiveCriteriaTemplate::with('subcriteria')->where('is_active', true)->orderBy('ordre')->get()
            : collect();
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

        return view('evaluations.show', [
            'evaluation'          => $evaluation,
            'objectiveCriteria'   => $objectiveCriteria,
            'subjectiveCriteria'  => $subjectiveCriteria,
            'note'                => $note,
            'mention'             => $mention,
            'ident'               => $ident,
            'cibleLabel'          => $secretaire?->name ?? '—',
            'cibleType'           => 'Secrétaire',
            'statusLabel'         => $statusLabel,
            'statusClass'         => $statusClass,
            'layout'              => 'layouts.subordonne',
            'backRoute'           => route('assistante.secretaire', ['tab' => 'evaluations']),
            'breadcrumb'          => 'Espace Assistante · Secrétaire',
            'soumettreRoute'      => 'assistante.secretaire.evaluations.submit',
            'destroyRoute'        => 'assistante.secretaire.evaluations.destroy',
            'subjectiveTemplates' => $subjectiveTemplates,
        ]);
    }

    public function submitEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEval($evaluation);

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
            return back()->with('error', 'Seules les évaluations en brouillon peuvent être soumises.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier(
            $evaluation->evaluable_id,
            'Évaluation soumise',
            "L'Assistante DG a soumis votre évaluation pour validation.",
            'normale',
            route('personnel.evaluations.show', $evaluation)
        );

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('status', 'Évaluation soumise.');
    }

    public function destroyEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEval($evaluation);

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
            return back()->with('error', 'Seules les évaluations en brouillon peuvent être supprimées.');
        }

        $evaluation->delete();

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('status', 'Évaluation supprimée.');
    }

    // ── Objectifs ──────────────────────────────────────────────────────────

    public function createObjectif(): View|RedirectResponse
    {
        $this->assertIsAssistante();
        $secretaire = $this->requireSecretaire();

        if ($secretaire instanceof RedirectResponse) {
            return $secretaire;
        }

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('directeur.subordonnes.objectifs.create', [
            'direction'    => (object) ['nom' => 'Direction Générale'],
            'service'      => null,
            'secretaire'   => $secretaire,
            'oldObjectifs' => $oldObjectifs,
            'storeRoute'   => 'assistante.secretaire.objectifs.store',
            'hiddenField'  => null,
            'cibleLabel'   => 'Secrétaire — '.$secretaire->name,
            'backRoute'    => route('assistante.secretaire', ['tab' => 'objectifs']),
            'layout'       => 'layouts.subordonne',
        ]);
    }

    public function storeObjectif(Request $request): RedirectResponse
    {
        $this->assertIsAssistante();
        $secretaire = $this->findSecretaire();

        if (! $secretaire) {
            abort(404);
        }

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        try {
            $anneeIdFiche = Annee::resolveOpenYearId(now());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeIdFiche, User::class, $secretaire->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce secrétaire pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeIdFiche,
            'assignable_type'       => User::class,
            'assignable_id'         => $secretaire->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
            'created_by'            => \Illuminate\Support\Facades\Auth::id(),
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $secretaire->id,
                'Nouvelle fiche d\'objectifs reçue',
                "L'Assistante DG vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute',
                route('personnel.fiches.show', $fiche)
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour {$secretaire->name}."
            : "Fiche d'objectifs assignée à {$secretaire->name}.";

        return redirect()
            ->route('assistante.secretaire.objectifs.show', $fiche)
            ->with('status', $msg);
    }

    public function showObjectif(FicheObjectif $fiche): View
    {
        $this->authorizeFiche($fiche);
        $fiche->load(['objectifs', 'annee']);

        return view('objectifs.show', [
            'layout'         => 'layouts.subordonne',
            'fiche'          => $fiche,
            'backRoute'      => route('assistante.secretaire', ['tab' => 'objectifs']),
            'editRoute'      => 'assistante.secretaire.objectifs.edit',
            'destroyRoute'   => 'assistante.secretaire.objectifs.destroy',
            'soumettreRoute' => 'assistante.secretaire.objectifs.soumettre',
            'pdfRoute'       => 'assistante.secretaire.objectifs.pdf',
            'isAssignee'     => false,
        ]);
    }

    public function pdfObjectif(FicheObjectif $fiche): \Illuminate\Http\Response
    {
        $this->authorizeFiche($fiche);
        $secretaire = $this->findSecretaire();
        $fiche->load(['objectifs', 'annee']);

        $pdf = Pdf::loadView('pdf.fiche-objectifs', [
            'fiche'         => $fiche,
            'assigneNom'    => $secretaire?->name ?? '—',
            'assigneRole'   => 'Secrétaire',
            'assigneurNom'  => Auth::user()->name ?? '—',
            'assigneurRole' => 'Assistante DG',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('fiche-objectifs-' . $fiche->id . '.pdf');
    }

    public function editObjectif(FicheObjectif $fiche): \Illuminate\View\View|RedirectResponse
    {
        $this->authorizeFiche($fiche);
        $secretaire = $this->findSecretaire();
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()
                ->route('assistante.secretaire.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        return view('directeur.subordonnes.objectifs.edit', [
            'fiche'        => $fiche,
            'direction'    => (object) ['nom' => 'Direction Générale'],
            'updateRoute'  => 'assistante.secretaire.objectifs.update',
            'cancelUrl'    => route('assistante.secretaire', ['tab' => 'objectifs']),
            'cibleLabel'   => 'Secrétaire — '.($secretaire?->name ?? '—'),
            'assigneeUser' => $secretaire,
            'layout'       => 'layouts.subordonne',
        ]);
    }

    public function updateObjectif(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeFiche($fiche);
        $secretaire = $this->findSecretaire();
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()
                ->route('assistante.secretaire.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $wasContested = $fiche->statut === 'contesté';
        $wasRefusee   = $fiche->statut === 'refusee';
        $action       = $request->input('action', 'brouillon');

        $validated = $request->validate([
            'titre_fiche' => ['required', 'string', 'max:255'],
            'objectifs'   => ['required', 'array', 'min:1'],
            'objectifs.*' => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $fiche->update(['titre' => $validated['titre_fiche']]);
        $fiche->objectifs()->delete();
        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (($wasContested || $wasRefusee) && $action === 'renvoyer') {
            $fiche->update(['statut' => 'en_attente']);

            if ($secretaire) {
                Alerte::notifier(
                    $secretaire->id,
                    'Fiche d\'objectifs révisée',
                    "L'Assistante DG a révisé la fiche d'objectifs « {$fiche->titre} ».",
                    'haute',
                    route('personnel.fiches.show', $fiche)
                );
            }

            $msg = $wasRefusee ? 'Fiche corrigée et renvoyée.' : 'Fiche révisée et renvoyée.';

            return redirect()
                ->route('assistante.secretaire.objectifs.show', $fiche)
                ->with('status', $msg);
        }

        return redirect()
            ->route('assistante.secretaire.objectifs.show', $fiche)
            ->with('status', 'Brouillon mis à jour.');
    }

    public function soumettreObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeFiche($fiche);
        $secretaire = $this->findSecretaire();

        if ($fiche->statut !== 'brouillon') {
            return redirect()
                ->route('assistante.secretaire.objectifs.show', $fiche)
                ->with('status', "Cette fiche n'est pas en brouillon.");
        }

        $fiche->update(['statut' => 'en_attente']);

        if ($secretaire) {
            Alerte::notifier(
                $secretaire->id,
                'Nouvelle fiche d\'objectifs reçue',
                "L'Assistante DG vous a soumis une fiche d'objectifs « {$fiche->titre} ».",
                'haute',
                route('personnel.fiches.show', $fiche)
            );
        }

        return redirect()
            ->route('assistante.secretaire.objectifs.show', $fiche)
            ->with('status', 'Fiche soumise à la secrétaire.');
    }

    public function destroyObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeFiche($fiche);
        $fiche->delete();

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs supprimée.");
    }
}
