<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agent;
use App\Models\DelegationTechnique;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Service;
use App\Models\User;
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
        ]);
    }

    public function servicesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();

        $services = Service::query()
            ->with('direction.delegationTechnique')
            ->when($delegationId > 0, function ($query) use ($delegationId): void {
                $query->whereHas('direction', function ($subQuery) use ($delegationId): void {
                    $subQuery->where('delegation_technique_id', $delegationId);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.delegations_techniques.services_index', [
            'services' => $services,
            'delegations' => $delegations,
            'activeDelegationId' => $delegationId,
        ]);
    }

    public function secretairesIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();

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
        ]);
    }

    public function agentsIndex(Request $request): View
    {
        $delegationId = (int) $request->query('delegation_id', 0);
        $delegations = DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get();

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

        $servicesCount     = Service::query()->count();
        $secretariatsCount = $delegations->count();
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

    public function create(): View
    {
        return view('admin.directions.create', [
            'delegations' => DelegationTechnique::query()->orderBy('region')->orderBy('ville')->get(),
        ]);
    }

    public function show(Direction $direction): View
    {
        return view('admin.directions.show', [
            'direction' => $direction->load('delegationTechnique'),
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
            ->route('admin.directions.index')
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
            'directeur_email'      => $dirEmailRule,
            'directeur_numero'     => ['required', 'string', 'max:30'],
            'secretaire_prenom'    => ['required', 'string', 'max:255'],
            'secretaire_nom'       => ['required', 'string', 'max:255'],
            'secretaire_email'     => $secEmailRule,
            'secretaire_telephone' => ['required', 'string', 'max:30'],
        ]);
    }
}
