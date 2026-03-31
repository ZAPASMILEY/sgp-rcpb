<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agent;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
use App\Models\Service;
use App\Models\User;
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
            ->withCount('directions')
            ->orderBy('region')
            ->orderBy('ville')
            ->get();

        $directions = Direction::query()
            ->with('delegationTechnique')
            ->withCount('services')
            ->latest()
            ->get();

        $servicesCount = Service::query()->count();
        $secretariatsCount = Direction::query()
            ->whereNotNull('secretaire_nom')
            ->count();
        $agentsCount = Agent::query()->count();

        $recentServices = Service::query()
            ->with('direction')
            ->latest()
            ->limit(5)
            ->get();

        $secretaires = Agent::query()
            ->with('service.direction')
            ->where('fonction', 'like', '%secretaire%')
            ->latest()
            ->limit(5)
            ->get();

        $recentAgents = Agent::query()
            ->with('service.direction')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.directions.index', [
            'directions'        => $directions,
            'directionsCount'   => $directions->count(),
            'servicesCount'     => $servicesCount,
            'secretariatsCount' => $secretariatsCount,
            'agentsCount'       => $agentsCount,
            'recentServices'    => $recentServices,
            'secretaires'       => $secretaires,
            'recentAgents'      => $recentAgents,
            'delegations'       => $delegations,
        ]);
    }

    public function storeDelegation(Request $request): RedirectResponse
    {
        if (DelegationTechnique::query()->count() >= 3) {
            return redirect()
                ->route('admin.directions.index')
                ->with('status', 'Maximum 3 delegations techniques configurees.');
        }

        $validated = $request->validate([
            'region'                => ['required', 'string', 'max:255'],
            'ville'                 => ['required', 'string', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
        ]);

        $alreadyExists = DelegationTechnique::query()
            ->where('region', $validated['region'])
            ->where('ville', $validated['ville'])
            ->exists();

        if ($alreadyExists) {
            return redirect()
                ->route('admin.directions.index')
                ->with('status', 'Cette delegation existe deja.');
        }

        DelegationTechnique::query()->create($validated);

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Delegation technique configuree avec succes.');
    }

    public function editDelegation(DelegationTechnique $delegationTechnique): View
    {
        return view('admin.delegations_techniques.edit', [
            'delegationTechnique' => $delegationTechnique,
        ]);
    }

    public function updateDelegation(Request $request, DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $validated = $this->validateDelegation($request, $delegationTechnique);

        $delegationTechnique->update($validated);

        Direction::query()
            ->where('delegation_technique_id', $delegationTechnique->id)
            ->update([
                'directeur_region' => $delegationTechnique->region,
                'secretariat_telephone' => $delegationTechnique->secretariat_telephone,
            ]);

        return redirect()
            ->route('admin.directions.index')
            ->with('status', 'Delegation technique mise a jour avec succes.');
    }

    public function destroyDelegation(DelegationTechnique $delegationTechnique): RedirectResponse
    {
        $delegationTechnique->delete();

        return redirect()
            ->route('admin.directions.index')
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
        $validated['nom']                   = 'Delegation Technique '.$delegation->region.' - '.$delegation->ville;
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
            ->route('admin.delegations-techniques.directeurs.index', ['delegation_id' => $delegation->id])
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
            'region' => ['required', 'string', 'max:255'],
            'ville' => ['required', 'string', 'max:255'],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
        ]);
    }
}
