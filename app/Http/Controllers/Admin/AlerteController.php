<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DiffuserAlerteJob;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\LoginFailure;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AlerteController extends Controller
{
    /** Rôles disponibles pour le ciblage des alertes. */
    public const ROLES_DISPONIBLES = [
        'tous'                => 'Tous les utilisateurs',
        'PCA'                 => 'PCA',
        'DG'                  => 'Directeur Général',
        'DGA'                 => 'Directeur Général Adjoint',
        'Assistante_Dg'       => 'Assistante DG',
        'Conseillers_Dg'      => 'Conseillers DG',
        'Directeur_Technique' => 'Directeurs Techniques / Directions',
        'Directeur_Caisse'    => 'Directeurs de Caisse',
        'Chef_Service'        => 'Chefs de Service',
        'Chef_Agence'         => "Chefs d'Agence",
        'Chef_Guichet'        => 'Chefs de Guichet',
        'Agent'               => 'Agents',
    ];

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'toutes');

        // --- Alertes personnalisées ---
        $alertesQuery = Alerte::with('createur')->latest();
        $alertesPersonnalisees = $alertesQuery->get();

        // --- Alertes de sécurité (login failures) ---
        $loginFailures = LoginFailure::latest('attempted_at')->get();

        // --- Statistiques ---
        $totalPersonnalisees = $alertesPersonnalisees->count();
        $totalSecurite = $loginFailures->count();
        $totalActives = $alertesPersonnalisees->where('statut', 'active')->count();
        $totalCritiques = $alertesPersonnalisees->where('priorite', 'critique')->count();
        $totalResolues = $alertesPersonnalisees->where('statut', 'resolue')->count();
        $tentativesAujourdhui = LoginFailure::whereDate('attempted_at', today())->count();

        // --- Données combinées pour l'onglet "Toutes" ---
        $combined = collect();

        foreach ($alertesPersonnalisees as $alerte) {
            $combined->push([
                'id'         => $alerte->id,
                'type'       => $alerte->type, // 'systeme' ou 'personnalisee'
                'priorite'   => $alerte->priorite,
                'titre'      => $alerte->titre,
                'message'    => $alerte->message,
                'statut'     => $alerte->statut,
                'ip_address' => $alerte->ip_address,
                'auteur'     => $alerte->createur?->name ?? null,
                'date'       => $alerte->created_at,
            ]);
        }

        foreach ($loginFailures as $failure) {
            $combined->push([
                'id'         => $failure->id,
                'type'       => 'securite',
                'priorite'   => 'haute',
                'titre'      => 'Tentative de connexion échouée',
                'message'    => 'Email: ' . ($failure->email ?? 'inconnu'),
                'statut'     => 'active',
                'ip_address' => $failure->ip_address,
                'auteur'     => $failure->email ?? '-',
                'date'       => $failure->attempted_at,
            ]);
        }

        $combined = $combined->sortByDesc('date')->values();

        // --- Filtrage par onglet ---
        $filtered = match ($tab) {
            'securite'       => $combined->where('type', 'securite')->values(),
            'personnalisees' => $combined->whereIn('type', ['personnalisee', 'systeme'])->values(),
            'critiques'      => $combined->filter(fn ($a) => $a['priorite'] === 'critique' || $a['priorite'] === 'haute')->values(),
            default          => $combined,
        };

        $items = $filtered;

        $counts = [
            'toutes'         => $combined->count(),
            'securite'       => $combined->where('type', 'securite')->count(),
            'personnalisees' => $combined->whereIn('type', ['personnalisee', 'systeme'])->count(),
            'critiques'      => $combined->filter(fn ($a) => $a['priorite'] === 'critique' || $a['priorite'] === 'haute')->count(),
        ];

        // --- Chart: alertes 7 derniers jours ---
        $chartCategories = [];
        $chartSecurite = [];
        $chartPersonnalisees = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartCategories[] = $day->translatedFormat('D d');
            $chartSecurite[] = LoginFailure::whereDate('attempted_at', $day->toDateString())->count();
            $chartPersonnalisees[] = Alerte::whereDate('created_at', $day->toDateString())->count();
        }

        $rolesDisponibles = self::ROLES_DISPONIBLES;

        return view('admin.alertes.index', compact(
            'items',
            'tab',
            'counts',
            'totalPersonnalisees',
            'totalSecurite',
            'totalActives',
            'totalCritiques',
            'totalResolues',
            'tentativesAujourdhui',
            'chartCategories',
            'chartSecurite',
            'chartPersonnalisees',
            'rolesDisponibles',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $rolesValides = array_keys(self::ROLES_DISPONIBLES);

        $validated = $request->validate([
            'titre'          => ['required', 'string', 'max:255'],
            'message'        => ['nullable', 'string', 'max:2000'],
            'type'           => ['required', 'in:personnalisee,securite'],
            'priorite'       => ['required', 'in:basse,moyenne,haute,critique'],
            'diffuser_email' => ['nullable', 'in:1'],
            'roles_cibles'   => ['nullable', 'array'],
            'roles_cibles.*' => ['string', Rule::in($rolesValides)],
        ]);

        $rolesCibles   = $validated['roles_cibles'] ?? ['tous'];
        $diffuserEmail = $request->boolean('diffuser_email');

        $alerte = Alerte::create([
            'titre'      => $validated['titre'],
            'message'    => $validated['message'] ?? null,
            'type'       => $validated['type'],
            'priorite'   => $validated['priorite'],
            'statut'     => 'active',
            'ip_address' => $request->ip(),
            'created_by' => $request->user()->id,
        ]);

        // Diffusion en arrière-plan (notifications in-app + emails éventuels)
        DiffuserAlerteJob::dispatch($alerte->id, $rolesCibles, $diffuserEmail);

        $envoyerATous = in_array('tous', $rolesCibles, true);
        $labelsRoles  = $envoyerATous
            ? 'tous les utilisateurs'
            : implode(', ', array_map(fn ($r) => self::ROLES_DISPONIBLES[$r] ?? $r, $rolesCibles));

        $message = "Alerte créée. Diffusion en cours pour : {$labelsRoles}.";
        if ($diffuserEmail) {
            $message .= ' Les emails sont en cours d\'envoi.';
        }

        return redirect()->route('admin.alertes.index', ['tab' => 'personnalisees'])
            ->with('status', $message);
    }

    public function updateStatut(Request $request, Alerte $alerte): RedirectResponse
    {
        $request->validate([
            'statut' => ['required', 'in:active,resolue,ignoree'],
        ]);

        $alerte->update(['statut' => $request->statut]);

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Statut mis à jour.');
    }

    public function destroy(Alerte $alerte): RedirectResponse
    {
        $alerte->delete();

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Alerte supprimée.');
    }

    public function destroyAll(): RedirectResponse
    {
        Alerte::query()->delete();

        return redirect()->route('admin.alertes.index')
            ->with('status', 'Toutes les alertes ont été supprimées.');
    }
public function relancerSansEval(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre'    => ['required', 'string', 'max:255'],
            'message'  => ['nullable', 'string', 'max:2000'],
            'priorite' => ['required', 'in:basse,moyenne,haute,critique'],
        ]);

        try {
            $openAnnee = Annee::currentOpen();
            if (! $openAnnee) {
                return back()->with('error', 'Aucune année d\'évaluation ouverte actuellement.');
            }

            // 1. Récupération de tous les agents qui n'ont pas d'évaluation validée cette année
            $agentsSansEvaluation = Agent::whereDoesntHave('evaluationsPersonnel', function ($q) use ($openAnnee) {
                    $q->where('annee_id', $openAnnee->id)->where('statut', 'valide');
                })
                ->whereDoesntHave('evaluations', function ($q) use ($openAnnee) {
                    $q->where('annee_id', $openAnnee->id)->where('statut', 'valide');
                })
                ->with(['direction', 'caisse', 'agence', 'service.direction', 'guichet'])
                ->get();

            if ($agentsSansEvaluation->isEmpty()) {
                return back()->with('status', 'Tous les agents ont déjà une évaluation validée.');
            }

            // 2. Identification des responsables selon la structure de notation
            $responsableAgentIds = [];
            foreach ($agentsSansEvaluation as $agent) {
                
                // CAS 1 : Si l'agent non évalué est un Chef de Service (Comparaison sécurisée avec (int))
                if ($agent->service_id && $agent->service && (int)$agent->id === (int)$agent->service->chef_agent_id) {
                    
                    // Option A : On cherche le directeur via la direction de l'agent
                    if ($agent->direction_id && $agent->direction && $agent->direction->directeur_agent_id) {
                        $responsableAgentIds[] = $agent->direction->directeur_agent_id;
                    } 
                    // Option B (Secours) : On remonte via la direction rattachée au Service
                    elseif ($agent->service->direction && $agent->service->direction->directeur_agent_id) {
                        $responsableAgentIds[] = $agent->service->direction->directeur_agent_id;
                    }
                    continue;
                }

                // CAS 2 : Si l'agent non évalué est un Chef d'Agence -> l'alerte va au Directeur de la Caisse
                if ($agent->agence_id && $agent->agence && (int)$agent->id === (int)$agent->agence->chef_agent_id) {
                    if ($agent->caisse_id && $agent->caisse && $agent->caisse->directeur_agent_id) {
                        $responsableAgentIds[] = $agent->caisse->directeur_agent_id;
                    }
                    continue;
                }

                // CAS 3 : Si l'agent non évalué est un Chef de Guichet -> l'alerte va au Chef d'Agence
                if ($agent->guichet_id && $agent->guichet && (int)$agent->id === (int)$agent->guichet->chef_agent_id) {
                    if ($agent->agence_id && $agent->agence && $agent->agence->chef_agent_id) {
                        $responsableAgentIds[] = $agent->agence->chef_agent_id;
                    }
                    continue;
                }

                // CAS 4 : Agents standards (Leur évaluation est faite par leur N+1 direct)
                if ($agent->service_id && $agent->service) {
                    $responsableAgentIds[] = $agent->service->chef_agent_id;
                } elseif ($agent->guichet_id && $agent->guichet) {
                    $responsableAgentIds[] = $agent->guichet->chef_agent_id;
                } elseif ($agent->agence_id && $agent->agence) {
                    $responsableAgentIds[] = $agent->agence->chef_agent_id;
                } elseif ($agent->caisse_id && $agent->caisse) {
                    $responsableAgentIds[] = $agent->caisse->directeur_agent_id;
                } elseif ($agent->direction_id && $agent->direction) {
                    $responsableAgentIds[] = $agent->direction->directeur_agent_id;
                }
            }

            // Nettoyage des doublons et des IDs vides/nulls
            $responsableAgentIds = array_filter(array_unique($responsableAgentIds));

            if (empty($responsableAgentIds)) {
                return back()->with('error', 'Aucun évaluateur hiérarchique n\'a pu être identifié pour relancer ces profils.');
            }

            // 3. Trouver les comptes UTILISATEURS actifs de ces supérieurs évaluateurs
            $utilisateursCiblesIds = User::whereIn('agent_id', $responsableAgentIds)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            if (empty($utilisateursCiblesIds)) {
                return back()->with('error', 'Aucun compte utilisateur actif trouvé pour les évaluateurs concernés.');
            }

            // 4. Création de l'alerte unique en base de données
            $alerte = Alerte::create([
                'titre'      => $validated['titre'],
                'message'    => $validated['message'] ?? null,
                'type'       => 'personnalisee',
                'priorite'   => $validated['priorite'],
                'statut'     => 'active',
                'ip_address' => $request->ip(),
                'created_by' => $request->user()->id,
            ]);

            // 5. Attachement de l'alerte aux utilisateurs cibles
            $alerte->destinataires()->syncWithoutDetaching(
                collect($utilisateursCiblesIds)->mapWithKeys(fn ($id) => [$id => ['lu' => false]])->all()
            );

            return back()->with('status', "L'alerte de relance a été envoyée avec succès aux " . count($utilisateursCiblesIds) . " évaluateur(s) / supérieur(s) concerné(s).");

        } catch (\Exception $e) {
            return back()->with('error', "Une erreur est survenue lors du traitement : " . $e->getMessage());
        }
    }

    public function nonLues(Request $request): JsonResponse
    {
        $user   = $request->user();
        $alertes = $user->alertesNonLues()->latest('alertes.created_at')->take(8)->get();
        $count   = $user->alertesNonLues()->count();

        return response()->json([
            'count' => $count,
            'items' => $alertes->map(fn ($a) => [
                'id'      => $a->id,
                'titre'   => $a->titre,
                'message' => Str::limit($a->message ?? '', 70),
                'priorite'=> $a->priorite,
                'age'     => $a->created_at->diffForHumans(),
                'lien'    => $a->lien,
            ]),
        ]);
    }

    public function lireTout(Request $request): RedirectResponse|JsonResponse
    {
        DB::table('alerte_user')
            ->where('user_id', $request->user()->id)
            ->where('lu', false)
            ->update(['lu' => true, 'lu_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('status', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function lireUne(Request $request, Alerte $alerte): JsonResponse
    {
        DB::table('alerte_user')
            ->where('user_id', $request->user()->id)
            ->where('alerte_id', $alerte->id)
            ->update(['lu' => true, 'lu_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
