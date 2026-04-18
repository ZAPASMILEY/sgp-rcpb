<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\SubjectiveCriteriaTemplate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * ──────────────────────────────────────────────────────────────────────────────
 * DirecteurSubordonneController — Gestion des subordonnés du directeur
 * ──────────────────────────────────────────────────────────────────────────────
 *
 * Ce controller gère les deux catégories de subordonnés directs d'un directeur :
 *
 *  1. Chefs de service  — rattachés via la relation Service → entité du directeur.
 *     Le directeur peut :
 *       • Consulter le tableau de bord d'un service (évaluations + objectifs)
 *       • Créer et gérer des fiches d'objectifs pour chaque service (CRUD)
 *       (Les évaluations des chefs de service sont gérées par DirecteurEvaluationController)
 *
 *  2. Secrétaire        — identifiée par secretaire_user_id dans l'entité du directeur.
 *     Le directeur peut :
 *       • Consulter le tableau de bord de la secrétaire (évaluations + objectifs)
 *       • Créer, soumettre et supprimer des évaluations pour la secrétaire (CRUD)
 *       • Créer et supprimer des fiches d'objectifs pour la secrétaire (CRUD)
 *
 * Chaque action vérifie via DirecteurEntity que le directeur est bien autorisé
 * à agir sur la ressource ciblée (service ou secrétaire).
 *
 * NOTE : La variable $direction est passée aux vues même si l'entité est une Caisse
 * ou DelegationTechnique, pour assurer la compatibilité ascendante avec les templates Blade.
 * ──────────────────────────────────────────────────────────────────────────────
 */
class DirecteurSubordonneController extends Controller
{
    // ── Authorization helpers ───────────────────────────────────────────────

    /**
     * Résout et retourne le contexte du directeur connecté.
     * Déclenche un 403 si aucune entité n'est associée au compte.
     */
    private function getContext(): DirecteurEntity
    {
        return DirecteurEntity::resolveOrFail(Auth::user());
    }

    /**
     * Vérifie que le service appartient bien à l'entité du directeur connecté.
     * Retourne le contexte pour permettre le chaînage dans les méthodes publiques.
     */
    private function authorizeService(Service $service): DirecteurEntity
    {
        $ctx = $this->getContext();
        if (! $ctx->serviceOwnedBy($service)) {
            abort(403);
        }

        return $ctx;
    }

    /**
     * Vérifie que l'évaluation cible bien la secrétaire du directeur connecté.
     * Contrôles : evaluable_type = User, evaluable_id = secretaire_user_id, evaluateur = directeur.
     */
    private function authorizeSecretaireEval(Evaluation $evaluation): DirecteurEntity
    {
        $ctx = $this->getContext();
        if (
            $evaluation->evaluable_type !== User::class ||
            (int) $evaluation->evaluable_id !== (int) $ctx->getSecretaireUserId() ||
            (int) $evaluation->evaluateur_id !== Auth::id()
        ) {
            abort(403);
        }

        return $ctx;
    }

    /**
     * Vérifie que la fiche d'objectifs est assignée à un service du directeur.
     * Retourne [DirecteurEntity, Service] pour les méthodes qui en ont besoin.
     */
    private function authorizeObjectifService(FicheObjectif $fiche): array
    {
        $ctx = $this->getContext();
        if ($fiche->assignable_type !== Service::class) {
            abort(403);
        }
        $service = Service::find($fiche->assignable_id);
        if (! $service || ! $ctx->serviceOwnedBy($service)) {
            abort(403);
        }

        return [$ctx, $service];
    }

    /**
     * Vérifie que la fiche d'objectifs est assignée à la secrétaire du directeur.
     * Contrôles : assignable_type = User, assignable_id = secretaire_user_id.
     */
    private function authorizeObjectifSecretaire(FicheObjectif $fiche): DirecteurEntity
    {
        $ctx = $this->getContext();
        if (
            $fiche->assignable_type !== User::class ||
            (int) $fiche->assignable_id !== (int) $ctx->getSecretaireUserId()
        ) {
            abort(403);
        }

        return $ctx;
    }

    // ── Index — Vue d'ensemble des subordonnés ─────────────────────────────

    /**
     * Affiche la liste de tous les chefs de service et la secrétaire du directeur.
     *
     * Pour chaque service : dernière évaluation créée par ce directeur, nombre
     * total d'évaluations et de fiches d'objectifs, nombre d'agents.
     * Pour la secrétaire : nombre d'évaluations et d'objectifs reçus.
     */
    public function index(): View
    {
        $ctx        = $this->getContext();
        $direction  = $ctx->entity; // Direction|Caisse|DelegationTechnique (compat Blade)
        $services   = $ctx->getServicesWithAgents();
        $secretaire = $ctx->getSecretaireUserId() ? User::find($ctx->getSecretaireUserId()) : null;

        $servicesData = $services->map(function (Service $service) {
            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->where('evaluateur_id', Auth::id())
                ->orderByDesc('date_debut')
                ->first();

            return [
                'service'     => $service,
                'latestEval'  => $latestEval,
                // Évaluations créées par ce directeur pour ce service
                'evalCount'   => Evaluation::where('evaluable_type', Service::class)
                    ->where('evaluable_id', $service->id)
                    ->where('evaluateur_id', Auth::id())
                    ->count(),
                // Fiches d'objectifs assignées à ce service (tous auteurs confondus)
                'ficheCount'  => FicheObjectif::where('assignable_type', Service::class)
                    ->where('assignable_id', $service->id)
                    ->count(),
                'agentsCount' => $service->agents->count(),
            ];
        });

        // Compteurs pour la secrétaire (si elle existe)
        $secretaireEvalCount    = 0;
        $secretaireObjectifCount = 0;
        if ($secretaire) {
            $secretaireEvalCount    = Evaluation::where('evaluable_type', User::class)->where('evaluable_id', $secretaire->id)->where('evaluateur_id', Auth::id())->count();
            $secretaireObjectifCount = FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $secretaire->id)->count();
        }

        return view('directeur.subordonnes.index', compact(
            'direction', 'servicesData', 'secretaire',
            'secretaireEvalCount', 'secretaireObjectifCount'
        ));
    }

    // ── Service detail ─────────────────────────────────────────────────────

    /**
     * Affiche le tableau de bord d'un service (tabs : évaluations | objectifs).
     *
     * Évaluations : créées par ce directeur pour le chef du service.
     * Objectifs    : fiches assignées à ce service (tous auteurs).
     */
    public function showService(Request $request, Service $service): View
    {
        $ctx       = $this->authorizeService($service);
        $direction = $ctx->entity;
        $tab       = $request->get('tab', 'evaluations');

        $evaluations = Evaluation::where('evaluable_type', Service::class)
            ->where('evaluable_id', $service->id)
            ->where('evaluable_role', 'manager')
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $fiches = FicheObjectif::where('assignable_type', Service::class)
            ->where('assignable_id', $service->id)
            ->withCount('objectifs')
            ->orderByDesc('date')
            ->get();

        return view('directeur.subordonnes.service', compact(
            'direction', 'service', 'tab', 'evaluations', 'fiches'
        ));
    }

    // ── Secrétaire detail ──────────────────────────────────────────────────

    /**
     * Affiche le tableau de bord de la secrétaire (tabs : évaluations | objectifs).
     *
     * Redirige avec une erreur si aucune secrétaire n'est enregistrée pour l'entité.
     */
    public function showSecretaire(Request $request): RedirectResponse|View
    {
        $ctx       = $this->getContext();
        $direction = $ctx->entity;

        if (! $ctx->getSecretaireUserId()) {
            return redirect()->route('directeur.subordonnes')
                ->with('error', 'Aucun(e) secrétaire enregistré(e) pour votre entité.');
        }

        $secretaire = User::findOrFail($ctx->getSecretaireUserId());
        $tab        = $request->get('tab', 'evaluations');

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

        return view('directeur.subordonnes.secretaire', compact(
            'direction', 'secretaire', 'tab', 'evaluations', 'fiches'
        ));
    }

    // ── Évaluations secrétaire — CRUD ──────────────────────────────────────

    /**
     * Affiche le formulaire de création d'une évaluation pour la secrétaire.
     *
     * Charge les fiches d'objectifs acceptées et non échues de la secrétaire
     * pour pré-remplir les critères objectifs via le moteur JS du formulaire.
     * Charge aussi les templates de critères subjectifs actifs.
     */
    public function createSecretaireEval(): RedirectResponse|View
    {
        $ctx = $this->getContext();
        if (! $ctx->getSecretaireUserId()) {
            return redirect()->route('directeur.subordonnes')->with('error', 'Aucun(e) secrétaire enregistré(e).');
        }
        $direction  = $ctx->entity;
        $secretaire = User::findOrFail($ctx->getSecretaireUserId());

        // Fiches d'objectifs acceptées et non échues de la secrétaire
        $today  = now()->toDateString();
        $fiches = FicheObjectif::with('objectifs')
            ->where('statut', 'acceptee')
            ->whereDate('date_echeance', '>=', $today)
            ->where('assignable_type', User::class)
            ->where('assignable_id', $secretaire->id)
            ->orderBy('titre')
            ->get();

        // Format JSON pour le moteur JS du formulaire d'évaluation
        $objectiveOptions = $fiches->map(fn ($f) => [
            'id'            => $f->id,
            'titre'         => $f->titre,
            'date_echeance' => $f->date_echeance instanceof Carbon ? $f->date_echeance->toDateString() : (string) $f->date_echeance,
            'objectifs'     => $f->objectifs->map(fn ($item) => [
                'source_fiche_objectif_objectif_id' => $item->id,
                'titre'                             => $item->description,
            ])->values()->all(),
        ])->values()->all();

        $subjectiveTemplates = $this->buildSubjectiveTemplates();

        // Valeurs précédentes pour les tableaux dynamiques (après erreur de validation)
        $oldFormations = old('identification.formations', [['periode' => '', 'libelle' => '', 'domaine' => '']]);
        if (! is_array($oldFormations) || $oldFormations === []) {
            $oldFormations = [['periode' => '', 'libelle' => '', 'domaine' => '']];
        }
        $oldExperiences = old('identification.experiences', [['periode' => '', 'poste' => '', 'observations' => '']]);
        if (! is_array($oldExperiences) || $oldExperiences === []) {
            $oldExperiences = [['periode' => '', 'poste' => '', 'observations' => '']];
        }
        $displayYear = now()->year;

        return view('directeur.subordonnes.evaluations.create', compact(
            'direction', 'secretaire', 'objectiveOptions',
            'subjectiveTemplates', 'oldFormations', 'oldExperiences', 'displayYear'
        ));
    }

    /**
     * Persiste une nouvelle évaluation pour la secrétaire.
     *
     * Pipeline identique à DirecteurEvaluationController::store() mais
     * l'évaluée est un User (secrétaire) et le rôle est 'secretaire'.
     * Stockage en transaction : Evaluation → Identification → Critères → SousCritères.
     */
    public function storeSecretaireEval(Request $request): RedirectResponse
    {
        $ctx = $this->getContext();
        if (! $ctx->getSecretaireUserId()) {
            abort(404);
        }
        $secretaire = User::findOrFail($ctx->getSecretaireUserId());
        $user       = Auth::user();

        $validated = $request->validate([
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

        // Conversion des dates MM/AAAA → AAAA-MM-01
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

        // Suppression des lignes vides dans formations et expériences
        $identification['formations'] = collect($identification['formations'] ?? [])
            ->map(fn ($r) => ['periode' => trim((string) ($r['periode'] ?? '')), 'libelle' => trim((string) ($r['libelle'] ?? '')), 'domaine' => trim((string) ($r['domaine'] ?? ''))])
            ->filter(fn ($r) => $r['periode'] !== '' || $r['libelle'] !== '' || $r['domaine'] !== '')
            ->values()->all();

        $identification['experiences'] = collect($identification['experiences'] ?? [])
            ->map(fn ($r) => ['periode' => trim((string) ($r['periode'] ?? '')), 'poste' => trim((string) ($r['poste'] ?? '')), 'observations' => trim((string) ($r['observations'] ?? ''))])
            ->filter(fn ($r) => $r['periode'] !== '' || $r['poste'] !== '' || $r['observations'] !== '')
            ->values()->all();

        // Normalisation des critères et calcul des scores
        $normalizedSubjective = $this->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.']);
        }

        $scores = $this->computeScores($normalizedSubjective, $normalizedObjective);

        try {
            $anneeId = Annee::resolveIdForDate($dateDebut);
        } catch (\Throwable) {
            $anneeId = null;
        }

        // Transaction : Evaluation → Identification → Critères → SousCritères
        DB::transaction(function () use ($user, $secretaire, $dateDebut, $dateFin, $anneeId, $scores, $validated, $identification, $normalizedSubjective, $normalizedObjective) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => User::class,
                'evaluable_id'              => $secretaire->id,
                'evaluable_role'            => 'secretaire', // rôle spécifique à la secrétaire
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

            $evaluation->identification()->create($identification);

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
                foreach ($criterion['subcriteria'] as $sub) {
                    $critere->sousCriteres()->create(['ordre' => $sub['ordre'], 'libelle' => $sub['libelle'], 'note' => $sub['note'], 'observation' => $sub['observation']]);
                }
            }
        });

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('status', "Évaluation créée pour {$secretaire->name}.");
    }

    /**
     * Affiche le détail d'une évaluation créée pour la secrétaire.
     * Affiche les boutons soumettre/supprimer si le statut est 'brouillon'.
     */
    public function showSecretaireEval(Evaluation $evaluation): View
    {
        $ctx        = $this->authorizeSecretaireEval($evaluation);
        $direction  = $ctx->entity;
        $secretaire = User::findOrFail($evaluation->evaluable_id);
        $evaluation->load(['identification', 'criteres.sousCriteres']);

        $objectiveCriteria  = $evaluation->criteres->where('type', 'objectif')->values();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif')->values();
        $note    = (float) $evaluation->note_finale;
        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
        $ident   = $evaluation->identification;

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

        return view('directeur.subordonnes.evaluations.show', compact(
            'evaluation', 'direction', 'secretaire',
            'objectiveCriteria', 'subjectiveCriteria',
            'note', 'mention', 'ident', 'statusClass', 'statusLabel'
        ));
    }

    /**
     * Soumet l'évaluation de la secrétaire (brouillon → soumis).
     * Envoie une alerte à la secrétaire pour l'informer.
     */
    public function submitSecretaireEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeSecretaireEval($evaluation);

        if ($evaluation->statut !== 'brouillon') {
            return back()->with('error', 'Cette évaluation ne peut plus être soumise.');
        }

        $evaluation->statut = 'soumis';
        $evaluation->save();

        // Notification à la secrétaire via le système d'alertes
        Alerte::notifier(
            (int) $evaluation->evaluable_id,
            'Nouvelle fiche d\'évaluation reçue',
            'Le Directeur vous a soumis une fiche d\'évaluation. Connectez-vous pour la consulter.',
            'haute'
        );

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('status', 'Évaluation soumise à la secrétaire.');
    }

    /**
     * Supprime une évaluation de la secrétaire (brouillon ou soumis uniquement).
     * Une évaluation validée ne peut pas être supprimée.
     */
    public function destroySecretaireEval(Evaluation $evaluation): RedirectResponse
    {
        $this->authorizeSecretaireEval($evaluation);

        if ($evaluation->statut === 'valide') {
            return back()->with('error', 'Une évaluation validée ne peut pas être supprimée.');
        }

        $evaluation->delete();

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'evaluations'])
            ->with('status', 'Évaluation supprimée.');
    }

    // ── Objectifs — Services ───────────────────────────────────────────────

    /**
     * Affiche le formulaire de création d'une fiche d'objectifs pour un service.
     *
     * Passe les variables de routing ($storeRoute, $hiddenField, $cibleLabel, $backRoute)
     * à la vue partagée directeur.subordonnes.objectifs.create, qui sert à la fois
     * pour les services et pour la secrétaire.
     */
    public function createServiceObjectif(Service $service): View
    {
        $ctx       = $this->authorizeService($service);
        $direction = $ctx->entity;

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('directeur.subordonnes.objectifs.create', [
            'direction'    => $direction,
            'service'      => $service,
            'secretaire'   => null,
            'oldObjectifs' => $oldObjectifs,
            'storeRoute'   => 'directeur.subordonnes.service.objectifs.store',
            'hiddenField'  => ['name' => 'service_id', 'value' => $service->id], // identifie le service cible
            'cibleLabel'   => 'Chef de service — '.$service->nom,
            'backRoute'    => route('directeur.subordonnes.service', ['service' => $service->id, 'tab' => 'objectifs']),
        ]);
    }

    /**
     * Persiste une fiche d'objectifs pour un service.
     *
     * Valide que service_id fait partie de la whitelist des services de l'entité
     * (empêche l'assignation à un service d'un autre directeur).
     */
    public function storeServiceObjectif(Request $request): RedirectResponse
    {
        $ctx        = $this->getContext();
        $serviceIds = $ctx->getServiceIds(); // IDs autorisés pour ce directeur

        $validated = $request->validate([
            'service_id'    => ['required', 'integer', 'in:'.implode(',', $serviceIds ?: [0])],
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $service = Service::findOrFail($validated['service_id']);

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
            'assignable_type'       => Service::class,
            'assignable_id'         => $service->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => 'en_attente', // doit être acceptée par le chef de service
        ]);

        foreach ($validated['objectifs'] as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        return redirect()
            ->route('directeur.subordonnes.service', ['service' => $service->id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs assignée au service « {$service->nom} ».");
    }

    /**
     * Affiche le détail d'une fiche d'objectifs assignée à un service.
     * Utilise la vue partagée avec $secretaire = null.
     */
    public function showServiceObjectif(FicheObjectif $fiche): View
    {
        [$ctx, $service] = $this->authorizeObjectifService($fiche);
        $direction = $ctx->entity;
        $fiche->load('objectifs');

        $statusClass = $this->ficheStatusClass($fiche->statut);
        $statusLabel = $this->ficheStatusLabel($fiche->statut);

        return view('directeur.subordonnes.objectifs.show', compact(
            'fiche', 'direction', 'service', 'statusClass', 'statusLabel'
        ) + ['secretaire' => null]);
    }

    /**
     * Supprime une fiche d'objectifs assignée à un service.
     * Redirige vers l'onglet objectifs du service concerné.
     */
    public function destroyServiceObjectif(FicheObjectif $fiche): RedirectResponse
    {
        [, $service] = $this->authorizeObjectifService($fiche);
        $fiche->delete();

        return redirect()
            ->route('directeur.subordonnes.service', ['service' => $service->id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs supprimée.");
    }

    // ── Objectifs — Secrétaire ─────────────────────────────────────────────

    /**
     * Affiche le formulaire de création d'une fiche d'objectifs pour la secrétaire.
     *
     * Utilise la même vue partagée que createServiceObjectif() mais avec
     * $service = null et $secretaire = User, et les routes de la secrétaire.
     */
    public function createSecretaireObjectif(): RedirectResponse|View
    {
        $ctx = $this->getContext();
        if (! $ctx->getSecretaireUserId()) {
            return redirect()->route('directeur.subordonnes')->with('error', 'Aucun(e) secrétaire enregistré(e).');
        }
        $direction    = $ctx->entity;
        $secretaire   = User::findOrFail($ctx->getSecretaireUserId());
        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('directeur.subordonnes.objectifs.create', [
            'direction'    => $direction,
            'service'      => null,
            'secretaire'   => $secretaire,
            'oldObjectifs' => $oldObjectifs,
            'storeRoute'   => 'directeur.subordonnes.secretaire.objectifs.store',
            'hiddenField'  => null, // pas de service_id à passer pour la secrétaire
            'cibleLabel'   => 'Secrétaire — '.$secretaire->name,
            'backRoute'    => route('directeur.subordonnes.secretaire', ['tab' => 'objectifs']),
        ]);
    }

    /**
     * Persiste une fiche d'objectifs pour la secrétaire.
     * Envoie une alerte à la secrétaire après création.
     */
    public function storeSecretaireObjectif(Request $request): RedirectResponse
    {
        $ctx = $this->getContext();
        if (! $ctx->getSecretaireUserId()) {
            abort(404);
        }
        $secretaire = User::findOrFail($ctx->getSecretaireUserId());

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee'                 => now()->year,
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

        // Notification à la secrétaire
        Alerte::notifier(
            $secretaire->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le Directeur vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
            'haute'
        );

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs assignée à {$secretaire->name}.");
    }

    /**
     * Affiche le détail d'une fiche d'objectifs de la secrétaire.
     * Utilise la vue partagée avec $service = null.
     */
    public function showSecretaireObjectif(FicheObjectif $fiche): View
    {
        $ctx        = $this->authorizeObjectifSecretaire($fiche);
        $direction  = $ctx->entity;
        $secretaire = User::findOrFail($fiche->assignable_id);
        $fiche->load('objectifs');

        $statusClass = $this->ficheStatusClass($fiche->statut);
        $statusLabel = $this->ficheStatusLabel($fiche->statut);

        return view('directeur.subordonnes.objectifs.show', compact(
            'fiche', 'direction', 'secretaire', 'statusClass', 'statusLabel'
        ) + ['service' => null]);
    }

    /**
     * Supprime une fiche d'objectifs de la secrétaire.
     */
    public function destroySecretaireObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeObjectifSecretaire($fiche);
        $fiche->delete();

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs supprimée.");
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /** Retourne la classe CSS du badge de statut d'une fiche d'objectifs. */
    private function ficheStatusClass(?string $statut): string
    {
        return match ($statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            default      => 'border-slate-200 bg-slate-100 text-slate-700',
        };
    }

    /** Retourne le libellé textuel du statut d'une fiche d'objectifs. */
    private function ficheStatusLabel(?string $statut): string
    {
        return match ($statut) {
            'acceptee'   => 'Acceptée',
            'en_attente' => 'En attente',
            'refusee'    => 'Refusée',
            default      => ucfirst((string) ($statut ?? 'En attente')),
        };
    }

    /**
     * Charge les templates de critères subjectifs actifs (triés par ordre).
     * Le résultat est encodé en JSON dans la vue pour alimenter le moteur JS.
     */
    private function buildSubjectiveTemplates(): array
    {
        return SubjectiveCriteriaTemplate::query()
            ->with('subcriteria')
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get()
            ->map(fn ($t) => [
                'id'          => $t->id,
                'ordre'       => $t->ordre,
                'titre'       => $t->titre,
                'description' => $t->description,
                'subcriteria' => $t->subcriteria->map(fn ($s) => ['libelle' => $s->libelle, 'ordre' => $s->ordre])->values()->all(),
            ])
            ->values()->all();
    }

    /**
     * Normalise les critères bruts issus du formulaire JS.
     *
     * @param  bool  $strict  Si false (critères subjectifs), injecte un sous-critère
     *                        fictif '-' quand aucun libellé n'est renseigné.
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
                if ($label === '') { if ($strict) continue; $label = '-'; }
                $note = max($minNote, min($maxNote, (float) ($sub['note'] ?? $minNote)));
                $subcriteria[] = ['ordre' => $subIdx + 1, 'libelle' => $label, 'note' => $note, 'observation' => filled($sub['observation'] ?? null) ? trim((string) $sub['observation']) : null];
            }
            if ($strict && $subcriteria === []) continue;
            if (! $strict && $subcriteria === []) $subcriteria = [['ordre' => 1, 'libelle' => '-', 'note' => $minNote, 'observation' => null]];
            $normalized[] = [
                'type' => $type, 'ordre' => $idx + 1, 'titre' => $title,
                'description' => filled($criterion['description'] ?? null) ? trim((string) $criterion['description']) : null,
                'note_globale' => round(collect($subcriteria)->avg('note') ?? 0, 2),
                'observation'  => filled($criterion['observation'] ?? null) ? trim((string) $criterion['observation']) : null,
                'source_template_id'                => isset($criterion['source_template_id']) ? (int) $criterion['source_template_id'] : null,
                'source_fiche_objectif_id'          => isset($criterion['source_fiche_objectif_id']) ? (int) $criterion['source_fiche_objectif_id'] : null,
                'source_fiche_objectif_objectif_id' => isset($criterion['source_fiche_objectif_objectif_id']) ? (int) $criterion['source_fiche_objectif_objectif_id'] : null,
                'subcriteria' => $subcriteria,
            ];
        }
        return $normalized;
    }

    /**
     * Calcule les scores pondérés à partir des critères normalisés.
     * Formule : note_finale = (obj×0,75 + subj×0,25) × 2  → sur 10.
     */
    private function computeScores(array $sub, array $obj): array
    {
        $mObj  = round(collect($obj)->avg('note_globale') ?? 0, 2);
        $mSubj = round(collect($sub)->avg('note_globale') ?? 0, 2);
        $nObj  = round($mObj * 0.75, 2);
        $nSubj = round($mSubj * 0.25, 2);
        return ['moyenne_objectifs' => $mObj, 'moyenne_subjectifs' => $mSubj, 'note_criteres_objectifs' => $nObj, 'note_criteres_subjectifs' => $nSubj, 'note_finale' => round(($nObj + $nSubj) * 2, 2)];
    }

    /**
     * Parse une date saisie librement (JJ/MM/AAAA ou AAAA-MM-JJ) → AAAA-MM-JJ.
     * Retourne null si le format est invalide.
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
