<?php

namespace App\Http\Controllers\Assistante;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\User;
use App\Services\EvaluationService;
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

    /** Retourne la secrétaire de l'assistante ou null si non configurée. */
    private function findSecretaire(): ?User
    {
        return User::where('role', 'Secretaire_Assistante')->first();
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

        return view('subordonne.secretaire', compact('secretaire', 'tab', 'evaluations', 'fiches'));
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
            'objectifs'     => $f->objectifs->map(fn ($item) => [
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

        $identification = $validated['identification'] ?? [];
        $identification['semestre'] = (string) $semestre->numero;
        $identification['matricule'] = $secretaire->agent?->matricule ?? null;
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
            'haute'
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

        return view('directeur.subordonnes.evaluations.show', [
            'evaluation' => $evaluation,
            'secretaire' => $secretaire,
            'direction'  => (object) ['nom' => 'Direction Générale'],
            'submitRoute'  => route('assistante.secretaire.evaluations.submit', $evaluation),
            'destroyRoute' => route('assistante.secretaire.evaluations.destroy', $evaluation),
            'backRoute'    => route('assistante.secretaire', ['tab' => 'evaluations']),
            'layout'       => 'layouts.subordonne',
        ]);
    }

    public function submitEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Seules les évaluations en brouillon peuvent être soumises.');
        }

        $evaluation->update(['statut' => 'soumis']);

        Alerte::notifier(
            $evaluation->evaluable_id,
            'Évaluation soumise',
            "L'Assistante DG a soumis votre évaluation pour validation.",
            'normale'
        );

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'evaluations'])
            ->with('status', 'Évaluation soumise.');
    }

    public function destroyEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
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
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeIdFiche,
            'assignable_type'       => User::class,
            'assignable_id'         => $secretaire->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente',
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        Alerte::notifier(
            $secretaire->id,
            'Nouvelle fiche d\'objectifs reçue',
            "L'Assistante DG vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
            'haute'
        );

        return redirect()
            ->route('assistante.secretaire', ['tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs assignée à {$secretaire->name}.");
    }

    public function showObjectif(FicheObjectif $fiche): View
    {
        $this->authorizeFiche($fiche);
        $secretaire = $this->findSecretaire();
        $fiche->load('objectifs');

        $statusClass = match ($fiche->statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($fiche->statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) ($fiche->statut ?? 'En attente')),
        };

        return view('directeur.subordonnes.objectifs.show', [
            'fiche'       => $fiche,
            'direction'   => (object) ['nom' => 'Direction Générale'],
            'service'     => null,
            'secretaire'  => $secretaire,
            'statusClass' => $statusClass,
            'statusLabel' => $statusLabel,
            'layout'      => 'layouts.subordonne',
        ]);
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
