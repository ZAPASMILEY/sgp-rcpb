<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgenceController extends Controller
{
    public function index(): View
    {
        return view('admin.agences.index', [
            'agences' => Agence::query()
                ->with([
                    'delegationTechnique',
                    'superviseurCaisse.superviseur.delegationTechnique',
                ])
                ->latest()
                ->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.agences.create', [
            'delegations' => DelegationTechnique::query()
                ->orderBy('region')
                ->orderBy('ville')
                ->get(),
            'caisses' => Caisse::query()
                ->with('superviseur.delegationTechnique')
                ->orderBy('nom')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('agences', 'nom')->where(function (Builder $query) use ($request): void {
                    $query->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                }),
            ],
            'chef_nom' => ['required', 'string', 'max:255'],
            'chef_email' => ['required', 'email', 'max:255', Rule::unique('agences', 'chef_email'), 'different:secretaire_email'],
            'chef_telephone' => ['required', 'string', 'max:30'],
            'secretaire_nom' => ['required', 'string', 'max:255'],
            'secretaire_email' => ['required', 'email', 'max:255', Rule::unique('agences', 'secretaire_email'), 'different:chef_email'],
            'secretaire_telephone' => ['required', 'string', 'max:30'],
            'delegation_technique_id' => ['required', 'integer', 'exists:delegation_techniques,id'],
            'superviseur_caisse_id' => [
                'required',
                'integer',
                Rule::exists('caisses', 'id')->where(function (Builder $query) use ($request): void {
                    $query->whereIn('superviseur_direction_id', function ($subQuery) use ($request): void {
                        $subQuery->select('id')
                            ->from('directions')
                            ->where('delegation_technique_id', $request->integer('delegation_technique_id'));
                    });
                }),
            ],
        ], [
            'nom.unique' => 'Cette agence existe deja pour la delegation technique selectionnee.',
            'chef_email.unique' => 'Cet email du chef est deja utilise.',
            'secretaire_email.unique' => 'Cet email du secretaire est deja utilise.',
            'chef_email.different' => 'Le mail du chef doit etre different de celui du secretaire.',
            'secretaire_email.different' => 'Le mail du secretaire doit etre different de celui du chef.',
        ]);

        Agence::query()->create($validated);

        return redirect()
            ->route('admin.agences.index')
            ->with('status', 'Agence creee avec succes.');
    }

    public function agentsIndex(Agence $agence): View
    {
        return view('admin.agences.agents.index', [
            'agence' => $agence->load(['delegationTechnique', 'superviseurCaisse']),
            'agents' => Agent::query()
                ->where('agence_id', $agence->id)
                ->latest()
                ->paginate(12),
        ]);
    }

    public function createAgent(Agence $agence): View
    {
        return view('admin.agences.agents.create', [
            'agence' => $agence->load(['delegationTechnique', 'superviseurCaisse']),
        ]);
    }

    public function storeAgent(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'fonction' => ['required', 'string', 'max:255'],
            'numero_telephone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);

        $plainPassword = Str::random(12);

        $user = User::query()->create([
            'name' => trim($validated['prenom'].' '.$validated['nom']),
            'email' => $validated['email'],
            'password' => Hash::make($plainPassword),
            'role' => 'agent',
        ]);

        Agent::query()->create([
            'user_id' => $user->id,
            'agence_id' => $agence->id,
            'service_id' => null,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'fonction' => $validated['fonction'],
            'numero_telephone' => $validated['numero_telephone'],
            'email' => $validated['email'],
            'photo_path' => null,
        ]);

        Mail::to($user->email)->send(new WelcomeMail(
            recipientName: $user->name,
            recipientEmail: $user->email,
            plainPassword: $plainPassword,
            role: 'agent',
            loginUrl: rtrim((string) config('app.url'), '/').'/login',
        ));

        return redirect()
            ->route('admin.agences.agents.index', $agence)
            ->with('status', 'Agent cree avec succes pour cette agence.');
    }
}
