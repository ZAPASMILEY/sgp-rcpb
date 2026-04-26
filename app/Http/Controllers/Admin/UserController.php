<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Correspondance fonction (agents.fonction) → rôle système (users.role).
     * Permet de pré-remplir le select rôle lors de la création d'un compte.
     */
    public const FONCTION_TO_ROLE = [
        'PCA'                     => 'PCA',
        'Directeur Général'       => 'DG',
        'DGA'                     => 'DGA',
        'Assistante DG'           => 'Assistante_Dg',
        'Conseiller DG'           => 'Conseillers_Dg',
        'Secrétaire Assistante'   => 'Secretaire_assistante',
        'Directeur de Direction'  => 'Directeur_Direction',
        'Secrétaire de Direction' => 'Secretaire_Direction',
        'Directeur Technique'     => 'Directeur_Tehnique',
        'Secrétaire Technique'    => 'Secretaire_Technique',
        'Directeur de Caisse'     => 'Directeur_Caisse',
        'Secrétaire de Caisse'    => 'Secretaire_Caisse',
        "Chef d'Agence"           => "chef d'agence",
        "Secrétaire d'Agence"     => 'Secretaire_Agence',
        'Chef de Guichet'         => "chef d'agence",
        'Chef de Service'         => 'Chefs de service',
        'Agent'                   => 'Agent',
    ];

    /**
     * Rôles disponibles dans le système (valeur stockée en BD → libellé affiché).
     */
    public const ROLES = [
        'admin'                 => 'Administrateur',
        'PCA'                   => 'PCA',
        'DG'                    => 'Directeur Général',
        'DGA'                   => 'DGA',
        'Assistante_Dg'         => 'Assistante DG',
        'Secretaire_assistante' => 'Secrétaire Assistante DG',
        'Conseillers_Dg'        => 'Conseiller DG',
        'Directeur_Direction'   => 'Directeur de Direction',
        'Directeur_Caisse'      => 'Directeur de Caisse',
        'Directeur_Tehnique'    => 'Directeur Technique',
        'Secretaire_Direction'  => 'Secrétaire de Direction',
        'Secretaire_Technique'  => 'Secrétaire Technique',
        'Secretaire_Caisse'     => 'Secrétaire de Caisse',
        'Secretaire_Agence'     => "Secrétaire d'Agence",
        'Chefs de service'      => 'Chef de Service',
        "chef d'agence"         => "Chef d'Agence",
        'Agent'                 => 'Agent',
    ];

    public function index(): View
    {
        $users = User::query()
            ->with(['agent', 'manager'])
            ->orderBy('name')
            ->get();

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $agents = Agent::query()
            ->doesntHave('user')
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'email', 'fonction']);

        $managers = User::query()->orderBy('name')->get(['id', 'name', 'role']);

        return view('admin.users.create', [
            'agents'          => $agents,
            'managers'        => $managers,
            'roles'           => self::ROLES,
            'fonctionToRole'  => self::FONCTION_TO_ROLE,
            'entites'         => \App\Models\Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agent_id'       => ['required', 'integer', 'exists:agents,id', Rule::unique('users', 'agent_id')],
            'role'           => ['required', 'string', Rule::in(array_keys(self::ROLES))],
            'manager_id'     => ['nullable', 'integer', 'exists:users,id'],
            'email'          => ['required', 'email', 'max:191', Rule::unique('users', 'email')],
            'password'       => ['required', 'confirmed', Password::min(8)],
            'pca_entite_id'  => [
                Rule::requiredIf($request->input('role') === 'PCA'),
                'nullable', 'integer', 'exists:entites,id',
            ],
        ], [
            'pca_entite_id.required' => 'Le rôle PCA nécessite de sélectionner une entité faîtière.',
        ]);

        $agent = Agent::findOrFail($validated['agent_id']);

        User::create([
            'agent_id'              => $agent->id,
            'name'                  => $agent->prenom . ' ' . $agent->nom,
            'email'                 => $validated['email'],
            'password'              => Hash::make($validated['password']),
            'role'                  => $validated['role'],
            'manager_id'            => $validated['manager_id'] ?? null,
            'pca_entite_id'         => $validated['pca_entite_id'] ?? null,
            'must_change_password'  => true,
        ]);

        // Synchronise le champ FK de l'entité faîtière selon le rôle
        $this->syncEntiteAgentField($validated['role'], $agent->id);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte utilisateur créé avec succès.');
    }

    public function edit(User $user): View
    {
        $managers = User::query()
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.users.edit', [
            'user'     => $user->load('agent'),
            'managers' => $managers,
            'roles'    => self::ROLES,
            'entites'  => \App\Models\Entite::query()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role'          => ['required', 'string', Rule::in(array_keys(self::ROLES))],
            'manager_id'    => ['nullable', 'integer', 'exists:users,id', Rule::notIn([$user->id])],
            'email'         => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => ['nullable', 'confirmed', Password::min(8)],
            'pca_entite_id' => [
                Rule::requiredIf($request->input('role') === 'PCA'),
                'nullable', 'integer', 'exists:entites,id',
            ],
        ], [
            'pca_entite_id.required' => 'Le rôle PCA nécessite de sélectionner une entité faîtière.',
        ]);

        $data = [
            'role'          => $validated['role'],
            'manager_id'    => $validated['manager_id'] ?? null,
            'email'         => $validated['email'],
            'pca_entite_id' => $validated['role'] === 'PCA' ? ($validated['pca_entite_id'] ?? null) : null,
        ];

        if (! empty($validated['password'])) {
            $data['password']             = Hash::make($validated['password']);
            $data['must_change_password'] = true;
        }

        $user->update($data);

        // Synchronise le champ FK de l'entité faîtière si l'agent est défini
        if ($user->agent_id) {
            $this->syncEntiteAgentField($validated['role'], $user->agent_id);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte mis à jour avec succès.');
    }

    /**
     * Met à jour le champ FK correspondant sur l'entité faîtière
     * quand un compte DG / DGA / Assistante_Dg / PCA est créé ou modifié.
     */
    private function syncEntiteAgentField(string $role, int $agentId): void
    {
        $column = match ($role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            'PCA'           => 'pca_agent_id',
            default         => null,
        };

        if ($column === null) {
            return;
        }

        $entite = Entite::query()->latest()->first();
        if ($entite) {
            $entite->update([$column => $agentId]);
        }
    }

    public function destroy(User $user): RedirectResponse
    {
        // Supprime le compte mais conserve l'agent
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Compte supprimé. L\'agent est conservé.');
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur et retourne le mot de passe généré.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $plain = Str::random(12);
        $user->update([
            'password'            => Hash::make($plain),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Mot de passe réinitialisé : {$plain} — Communiquez-le à l'utilisateur.")
            ->with('generated_password', $plain);
    }
}
