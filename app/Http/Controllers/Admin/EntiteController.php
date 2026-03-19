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
    public function index(): View
    {
        $entite = Entite::query()->latest()->first();

        if ($entite === null) {
            return view('admin.entites.index', [
                'entite' => null,
                'stats' => [
                    'directions' => 0,
                    'services' => 0,
                    'secretaires' => 0,
                    'agents' => 0,
                ],
                'directions' => collect(),
                'services' => collect(),
                'secretaires' => collect(),
                'agents' => collect(),
            ]);
        }

        $directionsQuery = Direction::query()
            ->where('entite_id', $entite->id)
            ->withCount('services')
            ->latest();

        $servicesQuery = Service::query()
            ->with('direction')
            ->whereHas('direction', function ($query) use ($entite): void {
                $query->where('entite_id', $entite->id);
            })
            ->latest();

        $agentsQuery = Agent::query()
            ->with('service.direction')
            ->whereHas('service.direction', function ($query) use ($entite): void {
                $query->where('entite_id', $entite->id);
            })
            ->latest();

        $secretairesQuery = Agent::query()
            ->with('service.direction')
            ->whereHas('service.direction', function ($query) use ($entite): void {
                $query->where('entite_id', $entite->id);
            })
            ->where('fonction', 'like', '%secretaire%')
            ->latest();

        return view('admin.entites.index', [
            'entite' => $entite,
            'stats' => [
                'directions' => (clone $directionsQuery)->count(),
                'services' => (clone $servicesQuery)->count(),
                'secretaires' => (clone $secretairesQuery)->count(),
                'agents' => (clone $agentsQuery)->count(),
            ],
            'directions' => $directionsQuery->take(6)->get(),
            'services' => $servicesQuery->take(6)->get(),
            'secretaires' => $secretairesQuery->take(6)->get(),
            'agents' => $agentsQuery->take(8)->get(),
        ]);
    }

    public function show(Entite $entite): View
    {
        return view('admin.entites.show', [
            'entite' => $entite,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        if (Entite::query()->exists()) {
            return redirect()
                ->route('admin.entites.index')
                ->with('status', 'La faitiere est deja configuree.');
        }

        return view('admin.entites.create');
    }

    public function edit(Entite $entite): View
    {
        return view('admin.entites.edit', [
            'entite' => $entite,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (Entite::query()->exists()) {
            return redirect()
                ->route('admin.entites.index')
                ->with('status', 'Une seule faitiere peut etre configuree.');
        }

        $validated = $this->validateEntite($request);

        $entite = null;
        $createdAccounts = [];

        DB::transaction(function () use ($validated, &$entite, &$createdAccounts): void {
            $entite = Entite::query()->create($validated);

            $createdAccounts[] = $this->createPersonnelAccount(
                name: $validated['directrice_generale_prenom'].' '.$validated['directrice_generale_nom'],
                email: $validated['directrice_generale_email'],
                role: 'directeur',
            );

            $createdAccounts[] = $this->createPersonnelAccount(
                name: $validated['dga_prenom'].' '.$validated['dga_nom'],
                email: $validated['dga_email'],
                role: 'directeur_adjoint',
            );

            $createdAccounts[] = $this->createPersonnelAccount(
                name: $validated['assistante_dg_prenom'].' '.$validated['assistante_dg_nom'],
                email: $validated['assistante_dg_email'],
                role: 'assistant',
            );

            $createdAccounts[] = $this->createPersonnelAccount(
                name: $validated['pca_prenom'].' '.$validated['pca_nom'],
                email: $validated['pca_email'],
                role: 'pca',
                extra: ['pca_entite_id' => $entite->id],
            );
        });

        foreach ($createdAccounts as $account) {
            Mail::to($account['email'])->send(new WelcomeMail(
                recipientName: $account['name'],
                recipientEmail: $account['email'],
                plainPassword: $account['plain_password'],
                role: $account['role'],
                loginUrl: rtrim((string) config('app.url'), '/').'/login',
            ));
        }

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Faitiere configuree avec succes.');
    }

    public function update(Request $request, Entite $entite): RedirectResponse
    {
        $validated = $this->validateEntite($request, $entite);

        $previousEmails = $this->responsibleEmails($entite);
        $createdAccounts = [];

        DB::transaction(function () use ($validated, $entite, $previousEmails, &$createdAccounts): void {
            $entite->update($validated);

            $createdAccounts[] = $this->syncPersonnelAccount(
                currentEmail: $previousEmails['directeur_general'],
                name: $validated['directrice_generale_prenom'].' '.$validated['directrice_generale_nom'],
                email: $validated['directrice_generale_email'],
                role: 'directeur',
            );

            $createdAccounts[] = $this->syncPersonnelAccount(
                currentEmail: $previousEmails['dga'],
                name: $validated['dga_prenom'].' '.$validated['dga_nom'],
                email: $validated['dga_email'],
                role: 'directeur_adjoint',
            );

            $createdAccounts[] = $this->syncPersonnelAccount(
                currentEmail: $previousEmails['assistante_dg'],
                name: $validated['assistante_dg_prenom'].' '.$validated['assistante_dg_nom'],
                email: $validated['assistante_dg_email'],
                role: 'assistant',
            );

            $createdAccounts[] = $this->syncPersonnelAccount(
                currentEmail: $previousEmails['pca'],
                name: $validated['pca_prenom'].' '.$validated['pca_nom'],
                email: $validated['pca_email'],
                role: 'pca',
                extra: ['pca_entite_id' => $entite->id],
            );
        });

        foreach (array_filter($createdAccounts) as $account) {
            Mail::to($account['email'])->send(new WelcomeMail(
                recipientName: $account['name'],
                recipientEmail: $account['email'],
                plainPassword: $account['plain_password'],
                role: $account['role'],
                loginUrl: rtrim((string) config('app.url'), '/').'/login',
            ));
        }

        return redirect()
            ->route('admin.entites.show', $entite)
            ->with('status', 'Faitiere mise a jour avec succes.');
    }

    public function destroy(Entite $entite): RedirectResponse
    {
        $emails = array_values(array_filter($this->responsibleEmails($entite)));

        User::query()->whereIn('email', $emails)->delete();
        $entite->delete();

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'Faitiere supprimee avec succes.');
    }

    public function reset(): RedirectResponse
    {
        $entiteIds = Entite::query()->pluck('id');

        if ($entiteIds->isEmpty()) {
            return redirect()
                ->route('admin.entites.index')
                ->with('status', 'La liste faitiere est deja vide.');
        }

        $directionIds = Direction::query()
            ->whereIn('entite_id', $entiteIds)
            ->pluck('id');

        $serviceIds = Service::query()
            ->whereIn('direction_id', $directionIds)
            ->pluck('id');

        $agentIds = Agent::query()
            ->whereIn('service_id', $serviceIds)
            ->pluck('id');

        DB::transaction(function () use ($entiteIds, $directionIds, $serviceIds, $agentIds): void {
            Objectif::query()->where('assignable_type', Entite::class)->whereIn('assignable_id', $entiteIds)->delete();
            Objectif::query()->where('assignable_type', Direction::class)->whereIn('assignable_id', $directionIds)->delete();
            Objectif::query()->where('assignable_type', Service::class)->whereIn('assignable_id', $serviceIds)->delete();
            Objectif::query()->where('assignable_type', Agent::class)->whereIn('assignable_id', $agentIds)->delete();

            Evaluation::query()->where('evaluable_type', Entite::class)->whereIn('evaluable_id', $entiteIds)->delete();
            Evaluation::query()->where('evaluable_type', Direction::class)->whereIn('evaluable_id', $directionIds)->delete();
            Evaluation::query()->where('evaluable_type', Service::class)->whereIn('evaluable_id', $serviceIds)->delete();
            Evaluation::query()->where('evaluable_type', Agent::class)->whereIn('evaluable_id', $agentIds)->delete();

            $responsibleEmails = Entite::query()
                ->whereIn('id', $entiteIds)
                ->get([
                    'directrice_generale_email',
                    'dga_email',
                    'assistante_dg_email',
                    'pca_email',
                ])
                ->flatMap(function (Entite $entite): array {
                    return [
                        $entite->directrice_generale_email,
                        $entite->dga_email,
                        $entite->assistante_dg_email,
                        $entite->pca_email,
                    ];
                })
                ->filter()
                ->values();

            User::query()->whereIn('email', $responsibleEmails)->delete();

            Entite::query()->whereIn('id', $entiteIds)->delete();
        });

        return redirect()
            ->route('admin.entites.index')
            ->with('status', 'La liste faitiere a ete videe completement.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateEntite(Request $request, ?Entite $entite = null): array
    {
        $validated = $request->validate([
            'ville' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'directrice_generale_prenom' => ['required', 'string', 'max:255'],
            'directrice_generale_nom' => ['required', 'string', 'max:255'],
            'directrice_generale_email' => [
                'required',
                'email',
                'max:255',
                $this->uniqueUserEmailRule($entite?->directrice_generale_email),
                'different:dga_email',
                'different:assistante_dg_email',
                'different:pca_email',
            ],
            'dga_prenom' => ['required', 'string', 'max:255'],
            'dga_nom' => ['required', 'string', 'max:255'],
            'dga_email' => [
                'required',
                'email',
                'max:255',
                $this->uniqueUserEmailRule($entite?->dga_email),
                'different:directrice_generale_email',
                'different:assistante_dg_email',
                'different:pca_email',
            ],
            'assistante_dg_prenom' => ['required', 'string', 'max:255'],
            'assistante_dg_nom' => ['required', 'string', 'max:255'],
            'assistante_dg_email' => [
                'required',
                'email',
                'max:255',
                $this->uniqueUserEmailRule($entite?->assistante_dg_email),
                'different:directrice_generale_email',
                'different:dga_email',
                'different:pca_email',
            ],
            'pca_prenom' => ['required', 'string', 'max:255'],
            'pca_nom' => ['required', 'string', 'max:255'],
            'pca_email' => [
                'required',
                'email',
                'max:255',
                $this->uniqueUserEmailRule($entite?->pca_email),
                'different:directrice_generale_email',
                'different:dga_email',
                'different:assistante_dg_email',
            ],
            'secretariat_telephone' => ['required', 'string', 'max:30'],
        ]);

        $validated['nom'] = 'Faitiere';

        return $validated;
    }

    private function uniqueUserEmailRule(?string $currentEmail)
    {
        $userId = null;

        if ($currentEmail !== null && $currentEmail !== '') {
            $userId = User::query()->where('email', $currentEmail)->value('id');
        }

        return Rule::unique('users', 'email')->ignore($userId);
    }

    /**
     * @return array{name: string, email: string, role: string, plain_password: string}
     */
    private function createPersonnelAccount(string $name, string $email, string $role, array $extra = []): array
    {
        $plainPassword = Str::random(12);

        User::query()->create(array_merge([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'role' => $role,
        ], $extra));

        return [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'plain_password' => $plainPassword,
        ];
    }

    /**
     * @return array{name: string, email: string, role: string, plain_password: string}|null
     */
    private function syncPersonnelAccount(string $currentEmail, string $name, string $email, string $role, array $extra = []): ?array
    {
        $user = User::query()->where('email', $currentEmail)->first();

        if ($user) {
            $user->update(array_merge([
                'name' => $name,
                'email' => $email,
                'role' => $role,
            ], $extra));

            return null;
        }

        $existingByNewEmail = User::query()->where('email', $email)->first();

        if ($existingByNewEmail) {
            $existingByNewEmail->update(array_merge([
                'name' => $name,
                'role' => $role,
            ], $extra));

            return null;
        }

        return $this->createPersonnelAccount($name, $email, $role, $extra);
    }

    /**
     * @return array{directeur_general: string, dga: string, assistante_dg: string, pca: string}
     */
    private function responsibleEmails(Entite $entite): array
    {
        return [
            'directeur_general' => (string) $entite->directrice_generale_email,
            'dga' => (string) $entite->dga_email,
            'assistante_dg' => (string) $entite->assistante_dg_email,
            'pca' => (string) $entite->pca_email,
        ];
    }
}
