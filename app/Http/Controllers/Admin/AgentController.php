<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class AgentController extends Controller
{
    public function index(Request $request): View
    {
        $fonction    = (string) $request->query('fonction', '');
        $search      = trim((string) $request->query('search', ''));
        $affectation = (string) $request->query('affectation', ''); // 'affecte' | 'non_affecte' | ''

        // FK directes (agent membre d'une structure)
        $directFks = ['entite_id', 'direction_id', 'delegation_technique_id', 'caisse_id', 'agence_id', 'guichet_id', 'service_id'];
        // FK inverses (agent EST le responsable d'une structure)
        $inverseRelations = [
            'directedDirection', 'secretariedDirection',
            'directedDelegation', 'secretariedDelegation',
            'directedCaisse', 'secretariedCaisse',
            'ledAgence', 'secretariedAgence',
            'ledGuichet', 'ledService',
        ];

        $scopeAffecte = function ($q) use ($directFks, $inverseRelations): void {
            $q->where(function ($sub) use ($directFks, $inverseRelations): void {
                foreach ($directFks as $col) {
                    $sub->orWhereNotNull($col);
                }
                foreach ($inverseRelations as $rel) {
                    $sub->orWhereHas($rel);
                }
            });
        };

        $agents = Agent::query()
            ->with([
                'user',
                // FK directes
                'entite', 'direction', 'delegationTechnique', 'caisse', 'agence', 'guichet', 'service',
                // FK inverses — postes de responsabilité
                'directedDirection', 'secretariedDirection',
                'directedDelegation', 'secretariedDelegation',
                'directedCaisse', 'secretariedCaisse',
                'ledAgence', 'ledGuichet', 'ledService',
            ])
            ->when($fonction !== '', fn ($q) => $q->where('fonction', $fonction))
            ->when($search !== '', function ($q) use ($search): void {
                $q->where(function ($sub) use ($search): void {
                    $sub->where('nom',    'like', "%{$search}%")
                        ->orWhere('prenom',  'like', "%{$search}%")
                        ->orWhere('email',   'like', "%{$search}%")
                        ->orWhere('fonction','like', "%{$search}%");
                });
            })
            ->when($affectation === 'affecte', $scopeAffecte)
            ->when($affectation === 'non_affecte', function ($q) use ($directFks, $inverseRelations): void {
                foreach ($directFks as $col) {
                    $q->whereNull($col);
                }
                foreach ($inverseRelations as $rel) {
                    $q->whereDoesntHave($rel);
                }
            })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();

        // Compteur par fonction pour les badges du filtre
        $countsByFonction = Agent::query()
            ->selectRaw('fonction, count(*) as total')
            ->groupBy('fonction')
            ->pluck('total', 'fonction');

        // Compteurs affectation (FK directes + FK inverses)
        $totalAffectes = Agent::query()->where(function ($q) use ($directFks, $inverseRelations): void {
            foreach ($directFks as $col) {
                $q->orWhereNotNull($col);
            }
            foreach ($inverseRelations as $rel) {
                $q->orWhereHas($rel);
            }
        })->count();

        return view('admin.agents.index', [
            'agents'           => $agents,
            'fonctionActive'   => $fonction,
            'affectation'      => $affectation,
            'search'           => $search,
            'fonctions'        => Agent::FONCTIONS,
            'countsByFonction' => $countsByFonction,
            'totalAgents'      => Agent::count(),
            'totalAffectes'    => $totalAffectes,
        ]);
    }

    public function create(): View
    {
        return view('admin.agents.create', $this->formData());
    }

    public function show(Agent $agent): View
    {
        return view('admin.agents.show', [
            'agent' => $agent->load(['service.direction.entite', 'service.delegationTechnique', 'service.caisse', 'delegationTechnique', 'caisse', 'agence', 'guichet', 'user']),
        ]);
    }

    public function edit(Agent $agent): View
    {
        return view('admin.agents.edit', array_merge(
            $this->formData(),
            ['agent' => $agent]
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAgent($request);
        $validated['photo_path'] = $this->storeSelectedPhoto($request);

        Agent::query()->create($validated);

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent cree avec succes.');
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $this->validateAgent($request, $agent);

        $photo = $this->storeSelectedPhoto($request);
        if ($photo !== null) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = $photo;
        } elseif ($request->boolean('remove_photo')) {
            $this->deletePhoto($agent->photo_path);
            $validated['photo_path'] = null;
        }

        $agent->update($validated);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Agent mis a jour avec succes.');
    }

    public function destroy(Request $request, Agent $agent): RedirectResponse
    {
        $this->deletePhoto($agent->photo_path);
        $agent->delete();

        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo !== '' && str_starts_with($redirectTo, (string) url('/'))) {
            return redirect()
                ->to($redirectTo)
                ->with('status', 'Agent supprime avec succes.');
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent supprime avec succes.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAgent(Request $request, ?Agent $agent = null): array
    {
        $emailRule = ['required', 'email', 'max:191'];
        if ($agent === null) {
            $emailRule[] = Rule::unique('agents', 'email');
        } else {
            $emailRule[] = Rule::unique('agents', 'email')->ignore($agent->id);
        }

        return $request->validate([
            // Données personnelles
            'nom'              => ['required', 'string', 'max:100'],
            'prenom'           => ['required', 'string', 'max:100'],
            'sexe'             => ['nullable', 'in:homme,femme'],
            'email'            => $emailRule,
            'numero_telephone' => [
                'nullable',
                'string',
                'max:30',
                $agent
                    ? Rule::unique('agents', 'numero_telephone')->ignore($agent->id)
                    : Rule::unique('agents', 'numero_telephone'),
            ],
            'photo_import'     => ['nullable', 'image', 'max:3072'],
            'photo_camera'     => ['nullable', 'image', 'max:3072'],
            'remove_photo'     => ['nullable', 'boolean'],

            // Données professionnelles
            'fonction'            => ['required', 'string', Rule::in(array_keys(Agent::FONCTIONS))],
            'date_debut_fonction' => ['nullable', 'date'],
        ], [
            'nom.required'              => 'Le nom est obligatoire.',
            'prenom.required'           => 'Le prénom est obligatoire.',
            'email.required'            => "L'email est obligatoire.",
            'email.email'               => "L'email n'est pas valide.",
            'email.unique'              => 'Cet email est déjà utilisé par un autre agent.',
            'numero_telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé par un autre agent.',
            'fonction.required'         => 'La fonction est obligatoire.',
            'fonction.in'               => 'La fonction sélectionnée est invalide.',
            'photo_import.image'        => 'Le fichier doit être une image.',
            'photo_import.max'          => 'La photo ne doit pas dépasser 3 Mo.',
            'photo_camera.image'        => 'Le fichier doit être une image.',
            'photo_camera.max'          => 'La photo ne doit pas dépasser 3 Mo.',
        ]);
    }

    private function storeSelectedPhoto(Request $request): ?string
    {
        $photo = $this->selectedPhoto($request);

        if (! $photo instanceof UploadedFile) {
            return null;
        }

        return $photo->store('agents', 'public');
    }

    private function selectedPhoto(Request $request): ?UploadedFile
    {
        $photoCamera = $request->file('photo_camera');
        if ($photoCamera instanceof UploadedFile) {
            return $photoCamera;
        }

        $photoImport = $request->file('photo_import');
        if ($photoImport instanceof UploadedFile) {
            return $photoImport;
        }

        return null;
    }

    private function deletePhoto(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}
