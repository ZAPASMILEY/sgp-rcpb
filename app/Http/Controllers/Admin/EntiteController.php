<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Agent;
use App\Models\Direction;
use App\Models\Entite;
use App\Models\Evaluation;
use App\Models\Objectif;
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

class EntiteController extends Controller
{
    /**
     * Enregistre un secretaire depuis la modale de la Faitiere.
     */
    public function storeSecretaire(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prenom' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'direction_id' => 'required|exists:directions,id',
            'date_prise_fonction' => 'required|date',
        ]);

        $password = Str::random(10);
        $user = User::create([
            'name' => $validated['prenom'].' '.$validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => 'secretaire',
        ]);

        $direction = Direction::find($validated['direction_id']);
        $direction->update([
            'secretaire_user_id' => $user->id,
            'secretaire_prenom' => $validated['prenom'],
            'secretaire_nom' => $validated['nom'],
            'secretaire_email' => $validated['email'],
            'date_prise_fonction' => $validated['date_prise_fonction'],
        ]);

        return redirect()->route('admin.entites.index')->with('status', 'Secretaire ajoute avec succes.');
    }

    public function index(): View
    {
        $entite = Entite::query()->latest()->first();

        if (!$entite) {
            return view('admin.entites.index', [
                'entite' => null,
                'stats' => ['directions' => 0, 'services' => 0, 'secretaires' => 0, 'agents' => 0],
                'directions' => collect(),
                'allDirections' => collect(),
                'services' => collect(),
                'secretaires' => collect(),
                'agents' => collect(),
                'bestNow' => [
                    'directions' => ['name' => null, 'note' => null],
                    'services' => ['name' => null, 'note' => null],
                    'secretaires' => ['name' => null, 'note' => null],
                    'agents' => ['name' => null, 'note' => null],
                ],
                'notesByType' => ['directions' => collect(), 'services' => collect(), 'agents' => collect()],
            ]);
        }

        $directionsQuery = Direction::where('entite_id', $entite->id)
            ->whereNull('delegation_technique_id');

        $servicesQuery = Service::whereHas('direction', function ($q) use ($entite) {
            $q->where('entite_id', $entite->id)->whereNull('delegation_technique_id');
        });

        $agentsQuery = Agent::whereHas('service.direction', function ($q) use ($entite) {
            $q->where('entite_id', $entite->id)->whereNull('delegation_technique_id');
        });

        $secretairesQuery = (clone $agentsQuery)->where('fonction', 'like', '%secretaire%');

        $dirIds = (clone $directionsQuery)->pluck('id');
        $serIds = (clone $servicesQuery)->pluck('id');
        $ageIds = (clone $agentsQuery)->pluck('id');
        $secIds = (clone $secretairesQuery)->pluck('id');

        return view('admin.entites.index', [
            'entite' => $entite,
            'stats' => [
                'directions' => $directionsQuery->count(),
                'services' => $servicesQuery->count(),
                'secretaires' => $secretairesQuery->count(),
                'agents' => $agentsQuery->count(),
            ],
            'directions' => (clone $directionsQuery)->withCount('services')->latest()->get(),
            'allDirections' => (clone $directionsQuery)->get(),
            'services' => (clone $servicesQuery)->with('direction')->latest()->take(6)->get(),
            'secretaires' => (clone $secretairesQuery)->latest()->take(6)->get(),
            'agents' => (clone $agentsQuery)->with('service')->latest()->take(8)->get(),
            'bestNow' => [
                'directions' => $this->getBestEval(Direction::class, $dirIds),
                'services' => $this->getBestEval(Service::class, $serIds, 'nom'),
                'secretaires' => $this->getBestEval(Agent::class, $secIds),
                'agents' => $this->getBestEval(Agent::class, $ageIds),
            ],
            'notesByType' => [
                'directions' => $this->getBestNotes(Direction::class, $dirIds),
                'services' => $this->getBestNotes(Service::class, $serIds),
                'agents' => $this->getBestNotes(Agent::class, $ageIds),
            ],
        ]);
    }

    /**
     * Affiche le formulaire d'edition d'une faitiere.
     */
    public function edit(Entite $entite): View
    {
        return view('admin.entites.edit', compact('entite'));
    }

    /**
     * Met a jour une faitiere existante.
     */
    public function update(Request $request, Entite $entite): RedirectResponse
    {
        $validated = $this->validateEntite($request, $entite);

        DB::transaction(function () use ($entite, $validated) {
            $originalEmails = [
                'directrice_generale_email' => $entite->directrice_generale_email,
                'dga_email' => $entite->dga_email,
                'assistante_dg_email' => $entite->assistante_dg_email,
                'pca_email' => $entite->pca_email,
            ];

            $entite->update($validated);

            $this->syncPersonnelAccount(
                $originalEmails['directrice_generale_email'],
                $validated['directrice_generale_email'],
                $validated['directrice_generale_prenom'].' '.$validated['directrice_generale_nom'],
                'directeur'
            );

            $this->syncPersonnelAccount(
                $originalEmails['dga_email'],
                $validated['dga_email'],
                $validated['dga_prenom'].' '.$validated['dga_nom'],
                'directeur_adjoint'
            );

            $this->syncPersonnelAccount(
                $originalEmails['assistante_dg_email'],
                $validated['assistante_dg_email'],
                $validated['assistante_dg_prenom'].' '.$validated['assistante_dg_nom'],
                'assistant'
            );

            $this->syncPersonnelAccount(
                $originalEmails['pca_email'],
                $validated['pca_email'],
                $validated['pca_prenom'].' '.$validated['pca_nom'],
                'pca',
                ['pca_entite_id' => $entite->id]
            );
        });

        return redirect()->route('admin.entites.index')->with('success', 'Faitiere modifiee avec succes.');
    }

    private function getBestEval($type, $ids, $nameField = null): array
    {
        $eval = Evaluation::where('evaluable_type', $type)
            ->whereIn('evaluable_id', $ids)
            ->where('statut', 'valide')
            ->orderByDesc('note_finale')
            ->first();

        $model = $eval?->evaluable;
        $name = null;

        if ($model) {
            $name = $nameField
                ? $model->$nameField
                : trim(($model->prenom ?? $model->directeur_prenom ?? '').' '.($model->nom ?? $model->directeur_nom ?? ''));
        }

        return ['name' => $name, 'note' => $eval?->note_finale];
    }

    public function indexDirections(Request $request): View
    {
        $entite = Entite::latest()->first();
        $search = trim((string) $request->query('search', ''));
        $sort = trim((string) $request->query('sort', ''));

        $directionsBuilder = Direction::query()
            ->whereNull('delegation_technique_id')
            ->when($entite, fn ($q) => $q->where('entite_id', $entite->id))
            ->when($search !== '', fn ($q) => $q->where(function ($sub) use ($search) {
                $sub->where('nom', 'like', "%{$search}%")
                    ->orWhere('directeur_prenom', 'like', "%{$search}%")
                    ->orWhere('directeur_nom', 'like', "%{$search}%");
            }))
            ->withCount('services');

        if ($sort === 'highest') {
            $directionsBuilder->leftJoinSub(
                Evaluation::selectRaw('evaluable_id, note_finale')
                    ->where('evaluable_type', Direction::class)
                    ->where('statut', 'valide')
                    ->latest('id')
                    ->distinct('evaluable_id'),
                'latest_eval',
                'directions.id',
                '=',
                'latest_eval.evaluable_id'
            )->orderByDesc('latest_eval.note_finale');
        } elseif ($sort === 'lowest') {
            $directionsBuilder->leftJoinSub(
                Evaluation::selectRaw('evaluable_id, note_finale')
                    ->where('evaluable_type', Direction::class)
                    ->where('statut', 'valide')
                    ->latest('id')
                    ->distinct('evaluable_id'),
                'latest_eval',
                'directions.id',
                '=',
                'latest_eval.evaluable_id'
            )->orderBy('latest_eval.note_finale');
        } elseif ($sort === 'not_rated') {
            $directionsBuilder->leftJoinSub(
                Evaluation::selectRaw('evaluable_id, note_finale')
                    ->where('evaluable_type', Direction::class)
                    ->where('statut', 'valide')
                    ->latest('id')
                    ->distinct('evaluable_id'),
                'latest_eval',
                'directions.id',
                '=',
                'latest_eval.evaluable_id'
            )->whereNull('latest_eval.note_finale')
                ->latest('directions.id');
        } elseif ($sort === 'rated') {
            $directionsBuilder->leftJoinSub(
                Evaluation::selectRaw('evaluable_id, note_finale')
                    ->where('evaluable_type', Direction::class)
                    ->where('statut', 'valide')
                    ->latest('id')
                    ->distinct('evaluable_id'),
                'latest_eval',
                'directions.id',
                '=',
                'latest_eval.evaluable_id'
            )->whereNotNull('latest_eval.note_finale')
                ->orderByDesc('latest_eval.note_finale');
        } else {
            $directionsBuilder->latest('directions.id');
        }

        $directions = $directionsBuilder
            ->paginate(15)
            ->withQueryString();

        $notes = Evaluation::query()
            ->where('evaluable_type', Direction::class)
            ->whereIn('evaluable_id', $directions->pluck('id'))
            ->where('statut', 'valide')
            ->latest('id')
            ->pluck('note_finale', 'evaluable_id');

        return view('admin.entites.directions.index', [
            'entite' => $entite,
            'directions' => $directions,
            'notes' => $notes,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    private function getBestNotes($type, $ids)
    {
        return Evaluation::selectRaw('evaluable_id, MAX(note_finale) as best_note')
            ->where('evaluable_type', $type)
            ->whereIn('evaluable_id', $ids)
            ->where('statut', 'valide')
            ->groupBy('evaluable_id')
            ->pluck('best_note', 'evaluable_id');
    }

    public function storeDirection(Request $request): RedirectResponse
    {
        $entite = Entite::latest()->first();
        if (!$entite) {
            return redirect()->route('admin.entites.index')->with('error', 'Configurez la Faitiere.');
        }

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('directions')->where('entite_id', $entite->id)],
            'directeur_prenom' => 'required|string|max:255',
            'directeur_nom' => 'required|string|max:255',
            'directeur_email' => 'required|email|unique:users,email',
            'directeur_numero' => 'nullable|string',
            'date_prise_fonction' => 'required|date',
        ]);

        $password = Str::random(12);
        $name = $validated['directeur_prenom'].' '.$validated['directeur_nom'];

        DB::transaction(function () use ($validated, $entite, $name, $password) {
            $user = User::create([
                'name' => $name,
                'email' => $validated['directeur_email'],
                'password' => Hash::make($password),
                'role' => 'directeur',
                'date_prise_fonction' => $validated['date_prise_fonction'],
            ]);

            Direction::create(array_merge($validated, [
                'user_id' => $user->id,
                'entite_id' => $entite->id,
                'date_prise_fonction' => $validated['date_prise_fonction'],
            ]));
        });

        Mail::to($validated['directeur_email'])->send(new WelcomeMail($name, $validated['directeur_email'], $password, 'directeur', url('/login')));

        return redirect()->route('admin.entites.index')->with('status', 'Direction creee.');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Entite::exists()) {
            return redirect()->route('admin.entites.index');
        }

        $validated = $this->validateEntite($request);

        DB::transaction(function () use ($validated) {
            $entite = Entite::create($validated);

            $personnels = [
                ['prenom' => 'directrice_generale_prenom', 'nom' => 'directrice_generale_nom', 'email' => 'directrice_generale_email', 'role' => 'directeur'],
                ['prenom' => 'dga_prenom', 'nom' => 'dga_nom', 'email' => 'dga_email', 'role' => 'directeur_adjoint'],
                ['prenom' => 'assistante_dg_prenom', 'nom' => 'assistante_dg_nom', 'email' => 'assistante_dg_email', 'role' => 'assistant'],
                ['prenom' => 'pca_prenom', 'nom' => 'pca_nom', 'email' => 'pca_email', 'role' => 'pca'],
            ];

            foreach ($personnels as $p) {
                $acc = $this->createPersonnelAccount(
                    $validated[$p['prenom']].' '.$validated[$p['nom']],
                    $validated[$p['email']],
                    $p['role'],
                    $p['role'] === 'pca' ? ['pca_entite_id' => $entite->id] : []
                );

                Mail::to($acc['email'])->send(new WelcomeMail($acc['name'], $acc['email'], $acc['plain_password'], $acc['role'], url('/login')));
            }
        });

        return redirect()->route('admin.entites.index')->with('status', 'Faitiere configuree.');
    }

    public function reset(): RedirectResponse
    {
        DB::transaction(function () {
            $entites = Entite::all();

            foreach ($entites as $entite) {
                $emails = array_filter([
                    $entite->directrice_generale_email,
                    $entite->dga_email,
                    $entite->assistante_dg_email,
                    $entite->pca_email,
                ]);

                User::whereIn('email', $emails)->delete();

                Objectif::where('assignable_type', Entite::class)->where('assignable_id', $entite->id)->delete();
                Evaluation::where('evaluable_type', Entite::class)->where('evaluable_id', $entite->id)->delete();

                $entite->delete();
            }
        });

        return redirect()->route('admin.entites.index')->with('status', 'Systeme reinitialise.');
    }

    private function validateEntite(Request $request, ?Entite $entite = null): array
    {
        return $request->validate([
            'ville' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'directrice_generale_prenom' => 'required|string',
            'directrice_generale_nom' => 'required|string',
            'directrice_generale_email' => ['required', 'email', $this->uniqueUserEmailRule($entite?->directrice_generale_email)],
            'dga_prenom' => 'required|string',
            'dga_nom' => 'required|string',
            'dga_email' => ['required', 'email', $this->uniqueUserEmailRule($entite?->dga_email)],
            'assistante_dg_prenom' => 'required|string',
            'assistante_dg_nom' => 'required|string',
            'assistante_dg_email' => ['required', 'email', $this->uniqueUserEmailRule($entite?->assistante_dg_email)],
            'pca_prenom' => 'required|string',
            'pca_nom' => 'required|string',
            'pca_email' => ['required', 'email', $this->uniqueUserEmailRule($entite?->pca_email)],
            'secretariat_telephone' => 'required|string',
        ]) + ['nom' => 'Faitiere'];
    }

    private function uniqueUserEmailRule(?string $email)
    {
        return Rule::unique('users', 'email')->ignore(User::where('email', $email)->value('id'));
    }

    private function createPersonnelAccount(string $name, string $email, string $role, array $extra = []): array
    {
        $password = Str::random(12);

        User::create(array_merge([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ], $extra));

        return [
            'name' => $name,
            'email' => $email,
            'plain_password' => $password,
            'role' => $role,
        ];
    }

    private function syncPersonnelAccount(string $currentEmail, string $newEmail, string $name, string $role, array $extra = []): void
    {
        $user = User::where('email', $currentEmail)->first();

        if (!$user) {
            return;
        }

        $user->update(array_merge([
            'name' => $name,
            'email' => $newEmail,
            'role' => $role,
        ], $extra));
    }

    /**
     * Affiche le formulaire de creation d'une direction pour la faitiere.
     */
    public function createDirection(): View
    {
        $entite = Entite::latest()->first();

        if (!$entite) {
            return redirect()->route('admin.entites.index')->with('error', 'Configurez la Faitiere.');
        }

        return view('admin.directions.create', [
            'entite' => $entite,
            'delegations' => collect(),
            'faitiere' => true,
        ]);
    }
}
