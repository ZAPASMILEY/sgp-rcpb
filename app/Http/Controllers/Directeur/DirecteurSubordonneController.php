<?php

namespace App\Http\Controllers\Directeur;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Caisse;
use Carbon\Carbon;
use App\Models\Evaluation;
use App\Models\FicheObjectif;
use App\Models\Service;
use App\Models\User;
use App\Services\EvaluationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function __construct(private readonly EvaluationService $evaluationService) {}

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
        if ($fiche->assignable_type !== User::class) {
            abort(403);
        }

        $user = User::find($fiche->assignable_id);
        if (! $user || ! $user->agent_id) {
            abort(403);
        }

        // Le chef doit diriger un service appartenant à ce directeur.
        $serviceIds = $ctx->getServiceIds();
        $service = Service::whereIn('id', $serviceIds)
            ->where('chef_agent_id', $user->agent_id)
            ->first();

        if (! $service) {
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

    // ── Index — Chefs de service (persons) ────────────────────────────────

    /**
     * Liste des chefs de service — affiche la PERSONNE responsable (Agent),
     * non la structure. Pour chaque service, charge le chef (chef_agent_id),
     * son compte User, sa dernière éval et ses compteurs.
     */
    public function indexChefs(): View
    {
        $ctx      = $this->getContext();
        $direction = $ctx->entity;

        $chefsData = $ctx->getServicesWithAgents()->map(function (Service $service) {
            $chef     = $service->chef; // Agent|null (via chef_agent_id)
            $chefUser = $chef ? User::where('agent_id', $chef->id)->first() : null;

            $latestEval = Evaluation::where('evaluable_type', Service::class)
                ->where('evaluable_id', $service->id)
                ->where('evaluable_role', 'manager')
                ->where('evaluateur_id', Auth::id())
                ->orderByDesc('date_debut')
                ->first();

            return [
                'service'     => $service,
                'chef'        => $chef,
                'chefUser'    => $chefUser,
                'latestEval'  => $latestEval,
                'evalCount'   => Evaluation::where('evaluable_type', Service::class)
                    ->where('evaluable_id', $service->id)
                    ->where('evaluateur_id', Auth::id())->count(),
                'ficheCount'  => ($chefUser)
                    ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $chefUser->id)->count()
                    : 0,
                'agentsCount' => $service->agents->count(),
            ];
        });

        return view('directeur.subordonnes.chefs', compact('ctx', 'direction', 'chefsData'));
    }

    // ── Index — Directeurs de caisse (DT uniquement, persons) ─────────────

    /**
     * Liste des directeurs de caisse — pour le Directeur Technique uniquement.
     * Affiche la PERSONNE (Agent directeur_agent_id), pas la structure.
     */
    public function indexDirecteurs(): View
    {
        $ctx = $this->getContext();
        if (! $ctx->hasCaisses()) {
            abort(403, 'Accès réservé au Directeur Technique.');
        }

        $direction = $ctx->entity;

        $directeursData = $ctx->getCaissesWithDirecteur()->map(function (Caisse $caisse) {
            $directeurAgent = $caisse->directeur_agent_id ? \App\Models\Agent::find($caisse->directeur_agent_id) : null;
            $directeurUser  = $caisse->directeur_agent_id ? User::where('agent_id', $caisse->directeur_agent_id)->first() : null;

            $latestEval = Evaluation::where('evaluable_type', Caisse::class)
                ->where('evaluable_id', $caisse->id)
                ->where('evaluable_role', 'manager')
                ->where('evaluateur_id', Auth::id())
                ->orderByDesc('date_debut')
                ->first();

            return [
                'caisse'         => $caisse,
                'directeurAgent' => $directeurAgent,
                'directeurUser'  => $directeurUser,
                'latestEval'     => $latestEval,
                'evalCount'      => Evaluation::where('evaluable_type', Caisse::class)
                    ->where('evaluable_id', $caisse->id)
                    ->where('evaluateur_id', Auth::id())->count(),
                'ficheCount'     => ($directeurUser)
                    ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $directeurUser->id)->count()
                    : 0,
                'agentsCount'    => $caisse->agents_count,
            ];
        });

        return view('directeur.subordonnes.directeurs', compact('ctx', 'direction', 'directeursData'));
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
                // Fiches d'objectifs assignées au chef de service (personne)
                'ficheCount'  => ($service->chef && ($cuid = User::where('agent_id', $service->chef->id)->value('id')))
                    ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $cuid)->count()
                    : 0,
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

        // Agences (Directeur_Caisse uniquement)
        $agencesData = collect();
        if ($ctx->hasAgences()) {
            $agencesData = $ctx->getAgencesWithGuichets()->map(function (Agence $agence) {
                $latestEval = Evaluation::where('evaluable_type', Agence::class)
                    ->where('evaluable_id', $agence->id)
                    ->where('evaluable_role', 'manager')
                    ->where('evaluateur_id', Auth::id())
                    ->orderByDesc('date_debut')
                    ->first();

                return [
                    'agence'        => $agence,
                    'latestEval'    => $latestEval,
                    'evalCount'     => Evaluation::where('evaluable_type', Agence::class)
                        ->where('evaluable_id', $agence->id)
                        ->where('evaluateur_id', Auth::id())
                        ->count(),
                    'ficheCount'    => ($agence->chef_agent_id && ($cuid = User::where('agent_id', $agence->chef_agent_id)->value('id')))
                        ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $cuid)->count()
                        : 0,
                    'agentsCount'   => $agence->agents_count,
                    'guichetsCount' => $agence->guichets->count(),
                ];
            });
        }

        // Caisses (Directeur_Technique uniquement)
        $caissesData = collect();
        if ($ctx->hasCaisses()) {
            $caissesData = $ctx->getCaissesWithDirecteur()->map(function (Caisse $caisse) {
                $directeurUser = $caisse->directeur_agent_id
                    ? User::where('agent_id', $caisse->directeur_agent_id)->first()
                    : null;

                $latestEval = Evaluation::where('evaluable_type', Caisse::class)
                    ->where('evaluable_id', $caisse->id)
                    ->where('evaluable_role', 'manager')
                    ->where('evaluateur_id', Auth::id())
                    ->orderByDesc('date_debut')
                    ->first();

                return [
                    'caisse'        => $caisse,
                    'directeurUser' => $directeurUser,
                    'latestEval'    => $latestEval,
                    'evalCount'     => Evaluation::where('evaluable_type', Caisse::class)
                        ->where('evaluable_id', $caisse->id)
                        ->where('evaluateur_id', Auth::id())
                        ->count(),
                    'ficheCount'    => ($directeurUser)
                        ? FicheObjectif::where('assignable_type', User::class)->where('assignable_id', $directeurUser->id)->count()
                        : 0,
                    'agentsCount'   => $caisse->agents_count,
                ];
            });
        }

        return view('directeur.subordonnes.index', compact(
            'ctx', 'direction', 'servicesData', 'secretaire',
            'secretaireEvalCount', 'secretaireObjectifCount', 'agencesData', 'caissesData'
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

        $service->load('chef');
        $chefUser = $service->chef
            ? User::where('agent_id', $service->chef->id)->first()
            : null;

        $fiches = $chefUser
            ? FicheObjectif::where('assignable_type', User::class)
                ->where('assignable_id', $chefUser->id)
                ->withCount('objectifs')
                ->orderByDesc('date')
                ->get()
            : collect();

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

        $subjectiveTemplates = $this->evaluationService->buildSubjectiveTemplates();

        // Valeurs précédentes (null = formulaire vierge → auto-fetch depuis la BDD)
        $oldFormations    = old('identification.formations');
        $oldExperiences   = old('identification.experiences');
        $openAnnee        = Annee::currentOpen();
        $openSemestres    = $openAnnee ? $openAnnee->semestres()->where('statut', 'ouvert')->orderBy('numero')->get() : collect();
        $openSemestre     = $openSemestres->first();
        $displayYear      = $openAnnee?->annee ?? now()->year;
        $prefilledAgentId = $secretaire->agent_id;
        $prefilledMatricule = $secretaire->agent?->matricule ?? null;

        return view('directeur.subordonnes.evaluations.create', compact(
            'direction', 'secretaire', 'objectiveOptions',
            'subjectiveTemplates', 'oldFormations', 'oldExperiences', 'displayYear',
            'prefilledAgentId', 'openAnnee', 'openSemestres', 'openSemestre', 'prefilledMatricule'
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

        // Dérivation automatique du semestre ouvert
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

        // Normalisation de la date d'évaluation dans la section identification
        $secretaire->loadMissing(['agent.entite', 'agent.direction', 'agent.delegationTechnique', 'agent.caisse', 'agent.agence']);
        $agentSec = $secretaire->agent;
        $identification = $validated['identification'] ?? [];
        $identification['semestre']          = (string) $semestre->numero;
        $identification['matricule']         = $agentSec?->matricule ?? null;
        $identification['nom_prenom']        = $agentSec ? trim($agentSec->prenom . ' ' . $agentSec->nom) : $secretaire->name;
        $identification['emploi']            = $agentSec?->poste ?: $agentSec?->role;
        $identification['direction']         = $agentSec?->entite?->sigle ?: ($agentSec?->entite?->nom ?? null);
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
        $normalizedSubjective = $this->evaluationService->normalizeCriteria((array) $request->input('subjective_criteres', []), 'subjectif', 1, 5, false);
        $normalizedObjective  = $this->evaluationService->normalizeCriteria((array) $request->input('objective_criteres', []), 'objectif', 1, 5);

        if ($normalizedSubjective === [] || $normalizedObjective === []) {
            return back()->withInput()->withErrors(['subjective_criteres' => 'Les critères subjectifs et objectifs doivent contenir au moins une ligne notée.']);
        }

        $scores = $this->evaluationService->computeScores($normalizedSubjective, $normalizedObjective);

        // ── Unicité : 1 évaluation par semestre ─────────────────────────────
        if ($this->evaluationService->dejaEvalueeSemestre($secretaire->id, User::class, $semestreId)) {
            return back()->withInput()->with('error', "Une évaluation existe déjà pour {$secretaire->name} sur ce semestre.");
        }

        // Transaction : Evaluation → Identification → Critères → SousCritères
        DB::transaction(function () use ($user, $secretaire, $dateDebut, $dateFin, $anneeId, $semestreId, $scores, $validated, $identification, $normalizedSubjective, $normalizedObjective) {
            $evaluation = Evaluation::create([
                'evaluable_type'            => User::class,
                'evaluable_id'              => $secretaire->id,
                'evaluable_role'            => 'secretaire', // rôle spécifique à la secrétaire
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
            'valide'      => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'soumis'      => 'border-amber-200 bg-amber-50 text-amber-700',
            'refuse'      => 'border-rose-200 bg-rose-50 text-rose-700',
            'reclamation' => 'border-orange-200 bg-orange-50 text-orange-700',
            'a_reviser'   => 'border-purple-200 bg-purple-50 text-purple-700',
            default       => 'border-slate-200 bg-slate-100 text-slate-700',
        };
        $statusLabel = match ($evaluation->statut) {
            'valide'      => 'Acceptée',
            'soumis'      => 'Soumise',
            'refuse'      => 'Refusée',
            'reclamation' => 'Réclamation',
            'a_reviser'   => 'À réviser',
            'brouillon'   => 'Brouillon',
            default       => ucfirst((string) $evaluation->statut),
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

        if (! in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS)) {
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

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        $service = Service::with('chef')->findOrFail($validated['service_id']);

        // On assigne à la PERSONNE (chef de service), pas à la structure.
        $chefUser = $service->chef
            ? User::where('agent_id', $service->chef->id)->first()
            : null;

        if (! $chefUser) {
            return back()->withInput()
                ->with('error', "Aucun chef avec un compte utilisateur n'est assigné au service « {$service->nom} ».");
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $chefUser->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce chef de service pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $chefUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $chefUser->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute'
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour le chef du service « {$service->nom} »."
            : "Fiche d'objectifs assignée au chef du service « {$service->nom} ».";

        return redirect()
            ->route('directeur.subordonnes.service', ['service' => $service->id, 'tab' => 'objectifs'])
            ->with('status', $msg);
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
        ) + ['secretaire' => null, 'agence' => null, 'caisse' => null]);
    }

    public function editServiceObjectif(FicheObjectif $fiche): View|RedirectResponse
    {
        [$ctx, $service] = $this->authorizeObjectifService($fiche);
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('directeur.subordonnes.service.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $chefUser = $service->chef ? User::where('agent_id', $service->chef->id)->first() : null;

        return view('directeur.subordonnes.objectifs.edit', [
            'fiche'        => $fiche,
            'direction'    => $ctx->entity,
            'updateRoute'  => 'directeur.subordonnes.service.objectifs.update',
            'cancelUrl'    => route('directeur.subordonnes.service.objectifs.show', $fiche),
            'cibleLabel'   => 'Chef de service — '.$service->nom,
            'assigneeUser' => $chefUser,
        ]);
    }

    public function updateServiceObjectif(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        [, $service] = $this->authorizeObjectifService($fiche);
        $fiche->load('objectifs');
        $chefUser = $service->chef ? User::where('agent_id', $service->chef->id)->first() : null;

        return $this->_performUpdateObjectif($fiche, $request, 'directeur.subordonnes.service.objectifs.show', $chefUser);
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

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $secretaire->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce secrétaire pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $secretaire->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $secretaire->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute'
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour {$secretaire->name}."
            : "Fiche d'objectifs assignée à {$secretaire->name}.";

        return redirect()->route('directeur.subordonnes.secretaire', ['tab' => 'objectifs'])
            ->with('status', $msg);
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
        ) + ['service' => null, 'agence' => null, 'caisse' => null]);
    }

    public function editSecretaireObjectif(FicheObjectif $fiche): View|RedirectResponse
    {
        $ctx        = $this->authorizeObjectifSecretaire($fiche);
        $secretaire = User::find($fiche->assignable_id);
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('directeur.subordonnes.secretaire.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        return view('directeur.subordonnes.objectifs.edit', [
            'fiche'        => $fiche,
            'direction'    => $ctx->entity,
            'updateRoute'  => 'directeur.subordonnes.secretaire.objectifs.update',
            'cancelUrl'    => route('directeur.subordonnes.secretaire.objectifs.show', $fiche),
            'cibleLabel'   => 'Secrétaire — '.($secretaire?->name ?? '—'),
            'assigneeUser' => $secretaire,
        ]);
    }

    public function updateSecretaireObjectif(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        $this->authorizeObjectifSecretaire($fiche);
        $fiche->load('objectifs');
        $secretaire = User::find($fiche->assignable_id);

        return $this->_performUpdateObjectif($fiche, $request, 'directeur.subordonnes.secretaire.objectifs.show', $secretaire);
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

    // ── Agences (Directeur_Caisse) ─────────────────────────────────────────

    private function authorizeAgence(Agence $agence): DirecteurEntity
    {
        $ctx = $this->getContext();
        if (! $ctx->agenceOwnedBy($agence)) {
            abort(403);
        }

        return $ctx;
    }

    private function authorizeObjectifAgence(FicheObjectif $fiche): array
    {
        $ctx = $this->getContext();
        if ($fiche->assignable_type !== User::class) {
            abort(403);
        }

        $user = User::find($fiche->assignable_id);
        if (! $user || ! $user->agent_id) {
            abort(403);
        }

        // Le chef doit diriger une agence appartenant à ce directeur.
        $agence = Agence::where('chef_agent_id', $user->agent_id)
            ->whereHas('caisse', fn ($q) => $q->where('delegation_technique_id', $ctx->getId()))
            ->first();

        if (! $agence || ! $ctx->agenceOwnedBy($agence)) {
            abort(403);
        }

        return [$ctx, $agence];
    }

    public function showAgence(Request $request, Agence $agence): View
    {
        $ctx       = $this->authorizeAgence($agence);
        $direction = $ctx->entity;
        $tab       = $request->get('tab', 'evaluations');

        $agence->load(['chef', 'guichets']);

        $evaluations = Evaluation::where('evaluable_type', Agence::class)
            ->where('evaluable_id', $agence->id)
            ->where('evaluable_role', 'manager')
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $chefAgenceUser = $agence->chef_agent_id
            ? User::where('agent_id', $agence->chef_agent_id)->first()
            : null;

        $fiches = $chefAgenceUser
            ? FicheObjectif::where('assignable_type', User::class)
                ->where('assignable_id', $chefAgenceUser->id)
                ->withCount('objectifs')
                ->orderByDesc('date')
                ->get()
            : collect();

        return view('directeur.subordonnes.agence', compact(
            'direction', 'agence', 'tab', 'evaluations', 'fiches'
        ));
    }

    public function createAgenceObjectif(Agence $agence): View
    {
        $ctx          = $this->authorizeAgence($agence);
        $direction    = $ctx->entity;
        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('directeur.subordonnes.objectifs.create', [
            'direction'    => $direction,
            'service'      => null,
            'secretaire'   => null,
            'agence'       => $agence,
            'oldObjectifs' => $oldObjectifs,
            'storeRoute'   => 'directeur.subordonnes.agence.objectifs.store',
            'hiddenField'  => 'agence_id',
            'hiddenValue'  => $agence->id,
            'cibleLabel'   => 'Agence — '.$agence->nom,
            'backRoute'    => route('directeur.subordonnes.agence', ['agence' => $agence->id, 'tab' => 'objectifs']),
        ]);
    }

    public function storeAgenceObjectif(Request $request): RedirectResponse
    {
        $agenceId = (int) $request->input('agence_id');
        $agence   = Agence::findOrFail($agenceId);
        $this->authorizeAgence($agence);

        $validated = $request->validate([
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        // On assigne à la PERSONNE (chef d'agence), pas à la structure.
        $chefAgenceUser = $agence->chef_agent_id
            ? User::where('agent_id', $agence->chef_agent_id)->first()
            : null;

        if (! $chefAgenceUser) {
            return back()->withInput()
                ->with('error', "Aucun chef avec un compte utilisateur n'est assigné à l'agence « {$agence->nom} ».");
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $chefAgenceUser->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce chef d\'agence pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $chefAgenceUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $chefAgenceUser->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute'
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour l'agence « {$agence->nom} »."
            : "Fiche d'objectifs assignée au chef de l'agence « {$agence->nom} ».";

        return redirect()
            ->route('directeur.subordonnes.agence', ['agence' => $agence->id, 'tab' => 'objectifs'])
            ->with('status', $msg);
    }

    public function showAgenceObjectif(FicheObjectif $fiche): View
    {
        [$ctx, $agence] = $this->authorizeObjectifAgence($fiche);
        $direction = $ctx->entity;
        $fiche->load('objectifs');

        $statusClass = $this->ficheStatusClass($fiche->statut);
        $statusLabel = $this->ficheStatusLabel($fiche->statut);

        return view('directeur.subordonnes.objectifs.show', compact(
            'fiche', 'direction', 'statusClass', 'statusLabel'
        ) + ['service' => null, 'secretaire' => null, 'agence' => $agence, 'caisse' => null]);
    }

    public function editAgenceObjectif(FicheObjectif $fiche): View|RedirectResponse
    {
        [$ctx, $agence] = $this->authorizeObjectifAgence($fiche);
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('directeur.subordonnes.agence.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $chefUser = $agence->chef_agent_id ? User::where('agent_id', $agence->chef_agent_id)->first() : null;

        return view('directeur.subordonnes.objectifs.edit', [
            'fiche'        => $fiche,
            'direction'    => $ctx->entity,
            'updateRoute'  => 'directeur.subordonnes.agence.objectifs.update',
            'cancelUrl'    => route('directeur.subordonnes.agence.objectifs.show', $fiche),
            'cibleLabel'   => 'Agence — '.$agence->nom,
            'assigneeUser' => $chefUser,
        ]);
    }

    public function updateAgenceObjectif(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        [, $agence] = $this->authorizeObjectifAgence($fiche);
        $fiche->load('objectifs');
        $chefUser = $agence->chef_agent_id ? User::where('agent_id', $agence->chef_agent_id)->first() : null;

        return $this->_performUpdateObjectif($fiche, $request, 'directeur.subordonnes.agence.objectifs.show', $chefUser);
    }

    public function destroyAgenceObjectif(FicheObjectif $fiche): RedirectResponse
    {
        [, $agence] = $this->authorizeObjectifAgence($fiche);
        $fiche->delete();

        return redirect()
            ->route('directeur.subordonnes.agence', ['agence' => $agence->id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs supprimée.");
    }

    // ── Helpers Caisse ─────────────────────────────────────────────────────

    private function authorizeCaisse(Caisse $caisse): DirecteurEntity
    {
        $ctx = $this->getContext();
        if (! $ctx->caisseOwnedBy($caisse)) {
            abort(403);
        }

        return $ctx;
    }

    private function authorizeObjectifCaisse(FicheObjectif $fiche): array
    {
        $ctx = $this->getContext();
        if ($fiche->assignable_type !== User::class) {
            abort(403);
        }

        $user = User::find($fiche->assignable_id);
        if (! $user || ! $user->agent_id) {
            abort(403);
        }

        // Le directeur doit gérer une caisse appartenant à ce DT.
        $caisse = Caisse::where('directeur_agent_id', $user->agent_id)
            ->where('delegation_technique_id', $ctx->getId())
            ->first();

        if (! $caisse || ! $ctx->caisseOwnedBy($caisse)) {
            abort(403);
        }

        return [$ctx, $caisse];
    }

    // ── Caisse detail ──────────────────────────────────────────────────────

    public function showCaisse(Request $request, Caisse $caisse): View
    {
        $ctx = $this->authorizeCaisse($caisse);
        $tab = $request->get('tab', 'evaluations');

        $evaluations = Evaluation::where('evaluable_type', Caisse::class)
            ->where('evaluable_id', $caisse->id)
            ->where('evaluateur_id', Auth::id())
            ->with('identification')
            ->orderByDesc('date_debut')
            ->get();

        $directeurUser = $caisse->directeur_agent_id
            ? User::where('agent_id', $caisse->directeur_agent_id)->first()
            : null;

        $fiches = $directeurUser
            ? FicheObjectif::where('assignable_type', User::class)
                ->where('assignable_id', $directeurUser->id)
                ->withCount('objectifs')
                ->orderByDesc('date')
                ->get()
            : collect();

        return view('directeur.subordonnes.caisse', compact('caisse', 'tab', 'evaluations', 'fiches', 'directeurUser', 'ctx'));
    }

    public function createCaisseObjectif(Caisse $caisse): View
    {
        $ctx = $this->authorizeCaisse($caisse);

        $oldObjectifs = old('objectifs', ['']);
        if (! is_array($oldObjectifs) || $oldObjectifs === []) {
            $oldObjectifs = [''];
        }

        return view('directeur.subordonnes.objectifs.create', [
            'direction'    => $ctx->entity,
            'service'      => null,
            'secretaire'   => null,
            'oldObjectifs' => $oldObjectifs,
            'storeRoute'   => 'directeur.subordonnes.caisse.objectifs.store',
            'hiddenField'  => ['name' => 'caisse_id', 'value' => $caisse->id],
            'cibleLabel'   => 'Caisse — '.$caisse->nom,
            'backRoute'    => route('directeur.subordonnes.caisse', ['caisse' => $caisse->id, 'tab' => 'objectifs']),
        ]);
    }

    public function storeCaisseObjectif(Request $request): RedirectResponse
    {
        $ctx = $this->getContext();

        $validated = $request->validate([
            'caisse_id'     => ['required', 'integer'],
            'titre_fiche'   => ['required', 'string', 'max:255'],
            'date_echeance' => ['required', 'date', 'after_or_equal:today'],
            'objectifs'     => ['required', 'array', 'min:1'],
            'objectifs.*'   => ['required', 'string', 'max:5000'],
        ]);

        $caisse = Caisse::findOrFail($validated['caisse_id']);
        if (! $ctx->caisseOwnedBy($caisse)) {
            abort(403);
        }

        $objectifs = array_values(array_filter(array_map('trim', $validated['objectifs']), fn ($v) => $v !== ''));
        if (count($objectifs) === 0) {
            return back()->withInput()->withErrors(['objectifs' => 'Vous devez renseigner au moins un objectif.']);
        }

        // On assigne à la PERSONNE (directeur de caisse), pas à la structure.
        $directeurUser = $caisse->directeur_agent_id
            ? User::where('agent_id', $caisse->directeur_agent_id)->first()
            : null;

        if (! $directeurUser) {
            return back()->withInput()
                ->with('error', "Aucun directeur avec un compte utilisateur n'est assigné à la caisse « {$caisse->nom} ».");
        }

        try {
            $anneeId = Annee::resolveOpenYearId(now());
            Annee::resolveOpenSemestreId(now()); // bloque si semestre clôturé
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (FicheObjectif::existsPourAnnee($anneeId, User::class, $directeurUser->id)) {
            return back()->withInput()->with('error', 'Une fiche d\'objectifs existe déjà pour ce directeur de caisse pour l\'année en cours.');
        }

        $isBrouillon = $request->input('action') === 'brouillon';

        $fiche = FicheObjectif::create([
            'titre'                 => $validated['titre_fiche'],
            'annee_id'              => $anneeId,
            'assignable_type'       => User::class,
            'assignable_id'         => $directeurUser->id,
            'date'                  => now()->toDateString(),
            'date_echeance'         => $validated['date_echeance'],
            'avancement_percentage' => 0,
            'statut'                => $isBrouillon ? 'brouillon' : 'en_attente',
        ]);

        foreach ($objectifs as $desc) {
            $fiche->objectifs()->create(['description' => $desc]);
        }

        if (! $isBrouillon) {
            Alerte::notifier(
                $directeurUser->id,
                'Nouvelle fiche d\'objectifs reçue',
                "Le Directeur Technique vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
                'haute'
            );
        }

        $msg = $isBrouillon
            ? "Brouillon enregistré pour la caisse {$caisse->nom}."
            : "Fiche d'objectifs assignée à la caisse {$caisse->nom}.";

        return redirect()
            ->route('directeur.subordonnes.caisse', ['caisse' => $caisse->id, 'tab' => 'objectifs'])
            ->with('status', $msg);
    }

    public function showCaisseObjectif(Request $request, FicheObjectif $fiche): View
    {
        [$ctx, $caisse] = $this->authorizeObjectifCaisse($fiche);
        $fiche->load('objectifs');

        return view('directeur.subordonnes.objectifs.show', [
            'fiche'       => $fiche,
            'direction'   => $ctx->entity,
            'service'     => null,
            'secretaire'  => null,
            'agence'      => null,
            'caisse'      => $caisse,
            'statusClass' => $this->ficheStatusClass($fiche->statut),
            'statusLabel' => $this->ficheStatusLabel($fiche->statut),
        ]);
    }

    public function editCaisseObjectif(FicheObjectif $fiche): View|RedirectResponse
    {
        [$ctx, $caisse] = $this->authorizeObjectifCaisse($fiche);
        $fiche->load('objectifs');

        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route('directeur.subordonnes.caisse.objectifs.show', $fiche)
                ->with('status', 'Cette fiche ne peut pas être modifiée.');
        }

        $directeurUser = $caisse->directeur_agent_id ? User::where('agent_id', $caisse->directeur_agent_id)->first() : null;

        return view('directeur.subordonnes.objectifs.edit', [
            'fiche'        => $fiche,
            'direction'    => $ctx->entity,
            'updateRoute'  => 'directeur.subordonnes.caisse.objectifs.update',
            'cancelUrl'    => route('directeur.subordonnes.caisse.objectifs.show', $fiche),
            'cibleLabel'   => 'Caisse — '.$caisse->nom,
            'assigneeUser' => $directeurUser,
        ]);
    }

    public function updateCaisseObjectif(Request $request, FicheObjectif $fiche): RedirectResponse
    {
        [, $caisse] = $this->authorizeObjectifCaisse($fiche);
        $fiche->load('objectifs');
        $directeurUser = $caisse->directeur_agent_id ? User::where('agent_id', $caisse->directeur_agent_id)->first() : null;

        return $this->_performUpdateObjectif($fiche, $request, 'directeur.subordonnes.caisse.objectifs.show', $directeurUser);
    }

    public function destroyCaisseObjectif(FicheObjectif $fiche): RedirectResponse
    {
        [$ctx, $caisse] = $this->authorizeObjectifCaisse($fiche);
        $caisse_id = $caisse->id;
        $fiche->delete();

        return redirect()
            ->route('directeur.subordonnes.caisse', ['caisse' => $caisse_id, 'tab' => 'objectifs'])
            ->with('status', "Fiche d'objectifs supprimée.");
    }

    // ── Soumettre un brouillon ─────────────────────────────────────────────

    /**
     * Passe une fiche d'objectifs de 'brouillon' à 'en_attente'.
     * Autorisé pour tous les types de subordonnés (service, agence, caisse, secrétaire).
     */
    public function soumettreObjectif(FicheObjectif $fiche): RedirectResponse
    {
        $this->authorize('objectifs.assigner');
        $ctx = $this->getContext();

        if ($fiche->statut !== 'brouillon') {
            return back()->with('error', "Cette fiche n'est pas en brouillon.");
        }

        if ($fiche->assignable_type !== User::class) {
            abort(403);
        }

        $assigneeUser = User::find($fiche->assignable_id);
        if (! $assigneeUser || ! $assigneeUser->agent_id) {
            abort(403);
        }

        $agentId       = $assigneeUser->agent_id;
        $redirectRoute = null;

        // Service chef ?
        $service = Service::whereIn('id', $ctx->getServiceIds())
            ->where('chef_agent_id', $agentId)
            ->first();
        if ($service) {
            $redirectRoute = route('directeur.subordonnes.service.objectifs.show', $fiche);
        }

        // Secrétaire ?
        if (! $redirectRoute && (int) $ctx->getSecretaireUserId() === $assigneeUser->id) {
            $redirectRoute = route('directeur.subordonnes.secretaire.objectifs.show', $fiche);
        }

        // Chef d'agence (Directeur_Caisse) ?
        if (! $redirectRoute && $ctx->hasAgences()) {
            $agence = Agence::where('chef_agent_id', $agentId)
                ->where('caisse_id', $ctx->getId())
                ->first();
            if ($agence) {
                $redirectRoute = route('directeur.subordonnes.agence.objectifs.show', $fiche);
            }
        }

        // Directeur de caisse (Directeur_Technique) ?
        if (! $redirectRoute && $ctx->hasCaisses()) {
            $caisse = Caisse::where('directeur_agent_id', $agentId)
                ->where('delegation_technique_id', $ctx->getId())
                ->first();
            if ($caisse) {
                $redirectRoute = route('directeur.subordonnes.caisse.objectifs.show', $fiche);
            }
        }

        if (! $redirectRoute) {
            abort(403);
        }

        $fiche->update(['statut' => 'en_attente']);

        Alerte::notifier(
            $assigneeUser->id,
            'Nouvelle fiche d\'objectifs reçue',
            "Le Directeur vous a assigné une fiche d'objectifs « {$fiche->titre} ».",
            'haute'
        );

        return redirect($redirectRoute)->with('status', 'Fiche soumise avec succès.');
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /** Retourne la classe CSS du badge de statut d'une fiche d'objectifs. */
    private function ficheStatusClass(?string $statut): string
    {
        return match ($statut) {
            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
            'contesté'   => 'border-orange-200 bg-orange-50 text-orange-700',
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
            'contesté'   => 'Contestée',
            default      => ucfirst((string) ($statut ?? 'En attente')),
        };
    }

    /** Logique partagée de mise à jour / renvoi d'une fiche d'objectifs. */
    private function _performUpdateObjectif(
        FicheObjectif $fiche,
        Request $request,
        string $showRoute,
        ?User $assigneeUser
    ): RedirectResponse {
        if (! in_array($fiche->statut, ['brouillon', 'contesté', 'refusee'], true)) {
            return redirect()->route($showRoute, $fiche)
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

            if ($assigneeUser) {
                Alerte::notifier(
                    $assigneeUser->id,
                    'Fiche d\'objectifs révisée',
                    "Le Directeur a révisé la fiche d'objectifs « {$fiche->titre} » suite à vos contestations.",
                    'haute'
                );
            }

            $msg = $wasRefusee ? 'Fiche corrigée et renvoyée.' : 'Fiche révisée et renvoyée.';

            return redirect()->route($showRoute, $fiche)->with('status', $msg);
        }

        return redirect()->route($showRoute, $fiche)->with('status', 'Brouillon mis à jour.');
    }

}
