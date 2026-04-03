<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\User;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DirectionController extends Controller
{
    public function directeursIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $directeurs = Direction::query()
            ->with('delegationTechnique')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->where('delegation_technique_id', $delegationId);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.directeurs_index', [
            'directeurs' => $directeurs,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
    }

    public function servicesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $search = trim((string) $request->query('search', ''));
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $services = Service::query()
            ->with('direction.delegationTechnique')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->whereHas('direction', function ($subQuery) use ($delegationId): void {
                    $subQuery->where('delegation_technique_id', $delegationId);
                });
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('nom', 'like', "%{$search}%")
                        ->orWhere('chef_prenom', 'like', "%{$search}%")
                        ->orWhere('chef_nom', 'like', "%{$search}%")
                        ->orWhereHas('direction', function ($directionQuery) use ($search): void {
                            $directionQuery
                                ->where('nom', 'like', "%{$search}%")
                                ->orWhereHas('delegationTechnique', function ($delegationQuery) use ($search): void {
                                    $delegationQuery
                                        ->where('region', 'like', "%{$search}%")
                                        ->orWhere('ville', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.services_index', [
            'services' => $services,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
            'search' => $search,
        ]);
    }

    public function secretairesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $secretaires = Direction::query()
            ->with('delegationTechnique')
            ->whereNotNull('secretaire_nom')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->where('delegation_technique_id', $delegationId);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.secretaires_index', [
            'secretaires' => $secretaires,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
    }

    public function agentsIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();
        ['selectedDelegation' => $selectedDelegation, 'delegationServices' => $delegationServices] = $this->delegationContext($delegationId);

        $agents = Agent::query()
            ->with('service.direction.delegationTechnique')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->whereHas('service.direction', function ($subQuery) use ($delegationId): void {
                    $subQuery->where('delegation_technique_id', $delegationId);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.agents_index', [
            'agents' => $agents,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $delegationServices,
        ]);
    }

    public function index(): View
    {
        $delegations = DelegationTechnique::query()
            ->withCount(['caisses'])
            ->with(['directions' => fn ($q) => $q->orderBy('nom'), 'villes' => fn ($q) => $q->orderBy('nom')])
            ->orderBy('region')
            ->orderBy('ville')
            ->get();

        $agentsCount = Agent::query()
            ->where(function ($q) {
                $q->whereNotNull('delegation_technique_id')
                  ->orWhereHas('service.direction', function ($sq) {
                      $sq->whereNotNull('delegation_technique_id');
                  });
            })
            ->count();

        $servicesCount = Service::query()
            ->whereNotNull('delegation_technique_id')
            ->count();

        $stats = [
            'delegations' => $delegations->count(),
            'caisses'     => $delegations->sum('caisses_count'),
            'agents'      => $agentsCount,
            'services'    => $servicesCount,
        ];

        return view('admin.delegations_techniques.index', [
            'delegations' => $delegations,
            'stats'       => $stats,
            'services'    => Service::query()->orderBy('nom')->get(),
        ]);
    }

    public function showDelegation(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->loadCount(['caisses', 'agents']);
        $delegationTechnique->load('villes');

        $caisses = $delegationTechnique->caisses()->with('ville')->orderBy('nom')->get();
        $agents  = $delegationTechnique->agents()->orderBy('nom')->get();

        return view('admin.delegations_techniques.show', [
            'delegation' => $delegationTechnique,
            'caisses'    => $caisses,
            'agents'     => $agents,
        ]);
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        if (DelegationTechnique::query()->count() >= 3) {
            return redirect()
                ->route('admin.delegations-techniques.index')
                ->with('status', 'Maximum 3 delegations techniques configurees.');
        }

        $validated = $request->validate([
            'region'                      => ['required', 'string', 'max:255'],
            'ville'                       => ['required', 'string', 'max:255'],
            'secretariat_telephone'       => ['required', 'string', 'max:30'],
            'directeur_prenom'            => ['required', 'string', 'max:255'],
            'directeur_nom'               => ['required', 'string', 'max:255'],
            'directeur_sexe'              => ['required', 'in:Masculin,Feminin'],
            'directeur_email'             => ['required', 'email', 'max:255'],
            'directeur_telephone'         => ['nullable', 'string', 'max:30'],
            'directeur_date_debut_mois'   => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'directeur_photo'             => ['nullable', 'image', 'max:2048'],
            'secretaire_prenom'           => ['required', 'string', 'max:255'],
            'secretaire_nom'              => ['required', 'string', 'max:255'],
            'secretaire_sexe'             => ['required', 'in:Masculin,Feminin'],
            'secretaire_email'            => ['required', 'email', 'max:255'],
            'secretaire_telephone'        => ['nullable', 'string', 'max:30'],
            'secretaire_date_debut_mois'  => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $alreadyExists = DelegationTechnique::query()
            ->where('region', $validated['region'])
            ->where('ville', $validated['ville'])
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->route('admin.delegations-techniques.index')
                ->with('status', 'Cette delegation existe deja.');
        }

        if ($request->hasFile('directeur_photo')) {
            $validated['directeur_photo_path'] = $request->file('directeur_photo')->store('delegations/photos', 'public');
        }
        unset($validated['directeur_photo']);

        DelegationTechnique::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Delegation technique creee avec succes.');
    }

    public function storeCaisse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'ville_id'                => ['required', 'exists:villes,id'],
            'nom'                     => ['required', 'string', 'max:255'],
            'annee_ouverture'         => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'quartier'                => ['nullable', 'string', 'max:255'],
            'directeur_prenom'        => ['required', 'string', 'max:255'],
            'directeur_nom'           => ['required', 'string', 'max:255'],
            'directeur_sexe'          => ['required', 'in:Masculin,Feminin'],
            'directeur_email'         => ['required', 'email', 'max:255'],
            'directeur_telephone'     => ['required', 'string', 'max:30'],
            'directeur_date_debut_mois' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'secretariat_telephone'   => ['required', 'string', 'max:30'],
            'secretaire_prenom'       => ['required', 'string', 'max:255'],
            'secretaire_nom'          => ['required', 'string', 'max:255'],
            'secretaire_sexe'         => ['required', 'in:Masculin,Feminin'],
            'secretaire_email'        => ['required', 'email', 'max:255'],
            'secretaire_telephone'    => ['nullable', 'string', 'max:30'],
            'secretaire_date_debut_mois' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        Caisse::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.show', $validated['delegation_technique_id'])
            ->with('status', 'Caisse creee avec succes.');
    }

    public function storeDelegationAgent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'service_id'              => ['nullable', 'exists:services,id'],
            'prenom'                  => ['required', 'string', 'max:255'],
            'nom'                     => ['required', 'string', 'max:255'],
            'sexe'                    => ['required', 'in:Masculin,Feminin'],
            'fonction'                => ['required', 'string', 'max:255'],
            'email'                   => ['required', 'email', 'max:255'],
            'numero_telephone'        => ['nullable', 'string', 'max:30'],
            'date_debut_fonction'     => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        Agent::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.show', $validated['delegation_technique_id'])
            ->with('status', 'Agent ajoute avec succes.');
    }

    public function storeDelegationService(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delegation_technique_id' => ['required', 'exists:delegation_techniques,id'],
            'nom'                     => ['required', 'string', 'max:255'],
            'chef_prenom'             => ['required', 'string', 'max:255'],
            'chef_nom'                => ['required', 'string', 'max:255'],
            'chef_sexe'               => ['required', 'in:Masculin,Feminin'],
            'chef_email'              => ['required', 'email', 'max:255'],
            'chef_telephone'          => ['required', 'string', 'max:30'],
            'chef_date_debut_mois'    => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        Service::query()->create($validated);

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Service cree avec succes.');
    }

    public function editDelegation(DelegationTechnique $delegationTechnique): View
    {
        $delegationTechnique->load('villes');

        return view('admin.delegations_techniques.edit', [
            'delegationTechnique' => $delegationTechnique,
        ]);
    }

    public function updateDelegation(Request $request, DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $validated = $this->validateDelegation($request, $delegationTechnique);

        // Handle director photo
        if ($request->hasFile('directeur_photo')) {
            $validated['directeur_photo_path'] = $request->file('directeur_photo')->store('delegations/photos', 'public');
        }
        unset($validated['directeur_photo']);

        // Extract villes before updating delegation
        $villesData = $validated['villes'] ?? [];
        unset($validated['villes']);

        // Check uniqueness of ville names across other delegations
        foreach ($villesData as $villeItem) {
            $existsElsewhere = Ville::where('nom', $villeItem['nom'])
                ->where('delegation_technique_id', '!=', $delegationTechnique->id)
                ->when(!empty($villeItem['id']), fn ($q) => $q->where('id', '!=', $villeItem['id']))
                ->exists();

            if ($existsElsewhere) {
                return redirect()->back()->withInput()->withErrors([
                    'villes' => "La ville \"{$villeItem['nom']}\" est déjà couverte par une autre délégation.",
                ]);
            }
        }

        $delegationTechnique->update($validated);

        // Sync villes
        $existingIds = [];
        foreach ($villesData as $villeItem) {
            if (!empty($villeItem['id'])) {
                $ville = Ville::find($villeItem['id']);
                if ($ville && $ville->delegation_technique_id === $delegationTechnique->id) {
                    $ville->update(['nom' => $villeItem['nom']]);
                    $existingIds[] = $ville->id;
                }
            } else {
                $new = $delegationTechnique->villes()->create(['nom' => $villeItem['nom']]);
                $existingIds[] = $new->id;
            }
        }
        $delegationTechnique->villes()->whereNotIn('id', $existingIds)->delete();

        Direction::query()
            ->where('delegation_technique_id', $delegationTechnique->id)
            ->update([
                'directeur_region' => $delegationTechnique->region,
                'secretariat_telephone' => $delegationTechnique->secretariat_telephone,
            ]);

        return redirect()
            ->route('admin.delegations-techniques.show', $delegationTechnique)
            ->with('status', 'Delegation technique mise a jour avec succes.');
    }

    public function destroyDelegation(DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $delegationTechnique->delete();

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Delegation technique supprimee avec succes.');
    }

    public function create(): View
    {
        return view('admin.directions.create', [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
        ]);
    }

    public function show(Direction $direction): View
    {
        // Charger les relations
        $direction->load('delegationTechnique');

        // Récupérer les évaluations validées et les dernières
        $evaluations = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->where('evaluable_id', $direction->id)
            ->where('statut', 'valide')
            ->latest()
            ->get();

        // Récupérer les objectifs assignés à cette direction
        $objectifs = Objectif::query()
            ->where('assignable_type', Direction::class)
            ->where('assignable_id', $direction->id)
            ->latest()
            ->get();

        return view('admin.directions.show', [
            'direction'   => $direction,
            'evaluations' => $evaluations,
            'objectifs'   => $objectifs,
        ]);
    }

    public function edit(Direction $direction): View
    {
        return view('admin.directions.edit', [
            'direction' => $direction,
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDirection($request);
        $delegation = DelegationTechnique::query()->findOrFail($validated['delegation_technique_id']);

        $entite = Entite::query()->latest()->first();
        if (! $entite) {
            return redirect()
                ->route('admin.entites.index')
                ->with('status', 'Configurez d abord la Faitiere avant de creer un Directeur Technique.');
        }

        $validated['entite_id']             = $entite?->id;
        // On conserve le nom saisi par l'utilisateur
        $validated['directeur_region']      = $delegation->region;
        $validated['secretariat_telephone'] = $delegation->secretariat_telephone;

        $dirPassword = Str::random(12);
        $secPassword = Str::random(12);
        $mails       = [];

        DB::transaction(function () use (&$validated, &$mails, $dirPassword, $secPassword): void {
            $dirUser = User::create([
                'name'     => trim($validated['directeur_prenom'].' '.$validated['directeur_nom']),
                'email'    => $validated['directeur_email'],
                'password' => Hash::make($dirPassword),
                'role'     => 'directeur',
            ]);
            $validated['user_id'] = $dirUser->id;
            $mails[] = ['user' => $dirUser, 'password' => $dirPassword, 'role' => 'directeur'];

            if (! empty($validated['secretaire_email'])) {
                $secUser = User::create([
                    'name'     => trim($validated['secretaire_prenom'].' '.$validated['secretaire_nom']),
                    'email'    => $validated['secretaire_email'],
                    'password' => Hash::make($secPassword),
                    'role'     => 'secretaire',
                ]);
                $validated['secretaire_user_id'] = $secUser->id;
                $mails[] = ['user' => $secUser, 'password' => $secPassword, 'role' => 'secretaire'];
            }

            Direction::query()->create($validated);
        });

        $loginUrl = rtrim((string) config('app.url'), '/').'/login';
        foreach ($mails as $m) {
            Mail::to($m['user']->email)->send(new WelcomeMail(
                recipientName:  $m['user']->name,
                recipientEmail: $m['user']->email,
                plainPassword:  $m['password'],
                role:           $m['role'],
                loginUrl:       $loginUrl,
            ));
        }

        return redirect()
            ->route('admin.delegations-techniques.index')
            ->with('status', 'Directeur technique cree avec succes. Les mots de passe ont ete envoyes par e-mail.');
    }

    public function update(Request $request, Direction $direction): RedirectResponse
    {
        $validated = $this->validateDirection($request, $direction);
        $delegation = DelegationTechnique::query()->findOrFail($validated['delegation_technique_id']);

        $validated['nom']                   = 'Delegation Technique '.$delegation->region.' - '.$delegation->ville;
        $validated['directeur_region']      = $delegation->region;
        $validated['secretariat_telephone'] = $delegation->secretariat_telephone;

        if ($direction->user) {
            $direction->user->update([
                'name'  => trim($validated['directeur_prenom'].' '.$validated['directeur_nom']),
                'email' => $validated['directeur_email'],
            ]);
        }

        if (! empty($validated['secretaire_email'])) {
            if ($direction->secretaireUser) {
                $direction->secretaireUser->update([
                    'name'  => trim($validated['secretaire_prenom'].' '.$validated['secretaire_nom']),
                    'email' => $validated['secretaire_email'],
                ]);
            } else {
                $secPassword = Str::random(12);
                $secUser     = User::create([
                    'name'     => trim($validated['secretaire_prenom'].' '.$validated['secretaire_nom']),
                    'email'    => $validated['secretaire_email'],
                    'password' => Hash::make($secPassword),
                    'role'     => 'secretaire',
                ]);
                $validated['secretaire_user_id'] = $secUser->id;
                Mail::to($secUser->email)->send(new WelcomeMail(
                    recipientName:  $secUser->name,
                    recipientEmail: $secUser->email,
                    plainPassword:  $secPassword,
                    role:           'secretaire',
                    loginUrl:       rtrim((string) config('app.url'), '/').'/login',
                ));
            }
        }

        $direction->update($validated);

        return redirect()
            ->route('admin.directions.show', $direction)
            ->with('status', 'Direction mise a jour avec succes.');
    }

    public function destroy(Direction $direction): RedirectResponse
    {
        $direction->user?->delete();
        $direction->secretaireUser?->delete();
        $direction->delete();

        return redirect()
            ->back()
            ->with('status', 'Direction supprimee avec succes.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDirection(Request $request, ?Direction $direction = null): array
    {
        $dirEmailRule = ['required', 'email', 'max:255'];
        $secEmailRule = ['required', 'email', 'max:255'];

        if ($direction === null) {
            $dirEmailRule[] = Rule::unique('users', 'email');
            $secEmailRule[] = Rule::unique('users', 'email');
        } else {
            $dirEmailRule[] = Rule::unique('users', 'email')->ignore($direction->user_id);
            $secEmailRule[] = Rule::unique('users', 'email')->ignore($direction->secretaire_user_id);
        }

        return $request->validate([
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'directeur_prenom'     => ['required', 'string', 'max:255'],
            'directeur_nom'        => ['required', 'string', 'max:255'],
            'directeur_email'      => [...$dirEmailRule, 'different:secretaire_email'],
            'directeur_numero'     => ['required', 'string', 'max:30'],
            'secretaire_prenom'    => ['required', 'string', 'max:255'],
            'secretaire_nom'       => ['required', 'string', 'max:255'],
            'secretaire_email'     => [...$secEmailRule, 'different:directeur_email'],
            'secretaire_telephone' => ['required', 'string', 'max:30'],
        ], [
            'directeur_email.different' => 'Le mail du directeur doit etre different de celui du secretaire.',
            'secretaire_email.different' => 'Le mail du secretaire doit etre different de celui du directeur.',
        ]);
    }

    /**
     * @return array{selectedDelegation: ?DelegationTechnique, delegationServices: Collection<int, Service>}
     */
    private function delegationContext(int $delegationId): array
    {
        if ($delegationId <= 0) {
            return [
                'selectedDelegation' => null,
                'delegationServices' => new Collection(),
            ];
        }

        $selectedDelegation = DelegationTechnique::query()->find($delegationId);

        if (! $selectedDelegation) {
            return [
                'selectedDelegation' => null,
                'delegationServices' => new Collection(),
            ];
        }

        return [
            'selectedDelegation' => $selectedDelegation,
            'delegationServices' => $selectedDelegation->services()
                ->with('direction')
                ->orderBy('nom')
                ->get(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validateDelegation(Request $request, ?DelegationTechnique $delegationTechnique = null): array
    {
        return $request->validate([
            'region'                      => ['required', 'string', 'max:255'],
            'ville'                       => ['required', 'string', 'max:255'],
            'secretariat_telephone'       => ['required', 'string', 'max:30'],
            'directeur_prenom'            => ['nullable', 'string', 'max:255'],
            'directeur_nom'               => ['nullable', 'string', 'max:255'],
            'directeur_sexe'              => ['nullable', 'in:Masculin,Feminin'],
            'directeur_email'             => ['nullable', 'email', 'max:255'],
            'directeur_telephone'         => ['nullable', 'string', 'max:30'],
            'directeur_date_debut_mois'   => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'directeur_photo'             => ['nullable', 'image', 'max:2048'],
            'secretaire_prenom'           => ['nullable', 'string', 'max:255'],
            'secretaire_nom'              => ['nullable', 'string', 'max:255'],
            'secretaire_sexe'             => ['nullable', 'in:Masculin,Feminin'],
            'secretaire_email'            => ['nullable', 'email', 'max:255'],
            'secretaire_telephone'        => ['nullable', 'string', 'max:30'],
            'secretaire_date_debut_mois'  => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'villes'                      => ['nullable', 'array'],
            'villes.*.id'                 => ['nullable', 'integer'],
            'villes.*.nom'                => ['required_with:villes', 'string', 'max:255'],
        ]);
    }
}
