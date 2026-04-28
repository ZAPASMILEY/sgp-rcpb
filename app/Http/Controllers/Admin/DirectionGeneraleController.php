<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;

class DirectionGeneraleController extends Controller
{
    public function index(): View
    {
        $entite = Entite::latest()->first();
        $direction = $entite
            ? Direction::where('entite_id', $entite->id)->where('nom', 'Direction Générale')->first()
            : null;

        $membres     = collect();
        $secretaires = collect();
        $conseillers = collect();

        if ($entite) {
            // DG, DGA, Assistante : retrouvés via les FK inverses sur entites
            $agentIds = array_values(array_filter([
                $entite->dg_agent_id,
                $entite->dga_agent_id,
                $entite->assistante_agent_id,
            ]));
            $membres = $agentIds
                ? User::whereIn('agent_id', $agentIds)->get()
                : collect();

            // Secrétaires et Conseillers : retrouvés via agents.entite_id
            $secretaires = User::where('role', 'Secretaire_assistante')
                ->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))
                ->get();

            $conseillers = User::where('role', 'Conseillers_Dg')
                ->whereHas('agent', fn ($q) => $q->where('entite_id', $entite->id))
                ->get();
        }

        return view('admin.direction-generale.index', [
            'entite'      => $entite,
            'direction'   => $direction,
            'membres'     => $membres,
            'secretaires' => $secretaires,
            'conseillers' => $conseillers,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $dejaConfiguree = Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->exists();

        if ($dejaConfiguree) {
            return redirect()
                ->route('admin.direction-generale.index')
                ->with('error', 'La Direction Générale est déjà configurée. Utilisez le bouton Modifier pour apporter des changements.');
        }

        return view('admin.direction-generale.create', [
            'entite'      => $entite,
            'dg_agents'   => \App\Models\Agent::query()->where('fonction', 'Directeur Général')->orderBy('nom')->get(),
            'dga_agents'  => \App\Models\Agent::query()->where('fonction', 'DGA')->orderBy('nom')->get(),
            'assistantes' => \App\Models\Agent::query()->where('fonction', 'Assistante DG')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();

        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $dejaConfiguree = Direction::where('entite_id', $entite->id)
            ->where('nom', 'Direction Générale')
            ->exists();

        if ($dejaConfiguree) {
            return redirect()
                ->route('admin.direction-generale.index')
                ->with('error', 'La Direction Générale est déjà configurée.');
        }

        $validated = $request->validate([
            'dg_agent_id'         => ['nullable', 'integer', 'exists:agents,id'],
            'dga_agent_id'        => ['nullable', 'integer', 'exists:agents,id'],
            'assistante_agent_id' => ['nullable', 'integer', 'exists:agents,id'],
        ]);

        $entite->update($validated);

        Direction::create([
            'entite_id'          => $entite->id,
            'nom'                => 'Direction Générale',
            'directeur_agent_id' => $validated['dg_agent_id'] ?? null,
        ]);

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Direction Generale configuree avec succes.');
    }

    /**
     * Affiche le formulaire de modification d'un membre de la Direction Générale
     * (DG, DGA ou Assistante_Dg).
     */
    public function editMembre(User $user): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.direction-generale.index');
        }

        // Sécurité : seuls les membres de l'entite courante peuvent être édités
        $roleColumn = match ($user->role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            default         => null,
        };
        if (! $roleColumn || (int) $entite->{$roleColumn} !== (int) $user->agent_id) {
            abort(403);
        }

        return view('admin.direction-generale.edit-membre', compact('user', 'entite'));
    }

    /**
     * Met à jour le compte d'un membre principal (DG, DGA ou Assistante_Dg).
     *
     * Met également à jour les champs correspondants sur l'entite (directrice_generale_nom, etc.)
     * pour que l'affichage admin reste cohérent avec les comptes utilisateurs.
     */
    public function updateMembre(Request $request, User $user): RedirectResponse
    {
        $entite = Entite::latest()->first();
        $roleColumn = match ($user->role) {
            'DG'            => 'dg_agent_id',
            'DGA'           => 'dga_agent_id',
            'Assistante_Dg' => 'assistante_agent_id',
            default         => null,
        };
        if (! $entite || ! $roleColumn || (int) $entite->{$roleColumn} !== (int) $user->agent_id) {
            abort(403);
        }

        $validated = $request->validate([
            'prenom'              => ['required', 'string', 'max:255'],
            'nom'                 => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'sexe'                => ['required', 'in:Homme,Femme,Autres'],
            'date_prise_fonction' => ['required', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        // Mise à jour du compte utilisateur
        $user->update([
            'name'                => $validated['prenom'].' '.$validated['nom'],
            'email'               => $validated['email'],
            'sexe'                => $validated['sexe'],
            'date_prise_fonction' => $validated['date_prise_fonction'],
        ]);

        // Mise à jour des champs miroirs sur l'entite selon le rôle
        $entiteFields = match ($user->role) {
            'DG' => [
                'directrice_generale_prenom'              => $validated['prenom'],
                'directrice_generale_nom'                 => $validated['nom'],
                'directrice_generale_email'               => $validated['email'],
                'directrice_generale_sexe'                => $validated['sexe'],
                'directrice_generale_date_prise_fonction' => $validated['date_prise_fonction'],
            ],
            'DGA' => [
                'dga_prenom'              => $validated['prenom'],
                'dga_nom'                 => $validated['nom'],
                'dga_email'               => $validated['email'],
                'dga_sexe'                => $validated['sexe'],
                'dga_date_prise_fonction' => $validated['date_prise_fonction'],
            ],
            'Assistante_Dg' => [
                'assistante_dg_prenom'              => $validated['prenom'],
                'assistante_dg_nom'                 => $validated['nom'],
                'assistante_dg_email'               => $validated['email'],
                'assistante_dg_sexe'                => $validated['sexe'],
                'assistante_dg_date_prise_fonction'  => $validated['date_prise_fonction'],
            ],
            default => [],
        };

        if ($entiteFields) {
            $entite->update($entiteFields);
        }

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Membre mis à jour avec succès.');
    }

    public function createSecretaire(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $agents = \App\Models\Agent::query()
            ->where('fonction', 'Secrétaire Assistante')
            ->with('user')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-generale.create-secretaire', compact('entite', 'agents'));
    }

    public function storeSecretaire(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
        ]);

        $agent = \App\Models\Agent::with('user')->findOrFail($validated['agent_id']);

        if (! $agent->user) {
            return redirect()->back()->withErrors([
                'agent_id' => "Cet agent n'a pas encore de compte utilisateur. Créez-le d'abord dans la section Comptes.",
            ])->withInput();
        }

        $agent->entite_id = $entite->id;
        $agent->save();
        $agent->user->update(['role' => 'Secretaire_assistante']);

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) comme Secrétaire Assistante.');
    }

    public function destroySecretaire(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Secrétaire supprimé.');
    }

    public function createConseiller(): View|RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create')
                ->with('error', 'Configurez d\'abord la faitiere.');
        }

        $agents = \App\Models\Agent::query()
            ->where('fonction', 'Conseiller DG')
            ->with('user')
            ->orderBy('nom')->orderBy('prenom')
            ->get();

        return view('admin.direction-generale.create-conseiller', compact('entite', 'agents'));
    }

    public function storeConseiller(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (! $entite) {
            return redirect()->route('admin.entites.create');
        }

        $validated = $request->validate([
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ], [
            'agent_id.required' => 'Veuillez sélectionner un agent.',
            'agent_id.exists'   => 'Agent introuvable.',
        ]);

        $agent = \App\Models\Agent::with('user')->findOrFail($validated['agent_id']);

        if (! $agent->user) {
            return redirect()->back()->withErrors([
                'agent_id' => "Cet agent n'a pas encore de compte utilisateur. Créez-le d'abord dans la section Comptes.",
            ])->withInput();
        }

        $agent->entite_id = $entite->id;
        $agent->save();
        $agent->user->update(['role' => 'Conseillers_Dg']);

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', $agent->prenom.' '.$agent->nom.' affecté(e) comme Conseiller DG.');
    }

    public function destroyConseiller(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('admin.direction-generale.index')
            ->with('status', 'Conseiller supprimé.');
    }
}
