<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Annee;
use App\Models\Formation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Logique CRUD partagée entre FormationGererController et RhFormationController.
 *
 * La seule différence entre les deux contrôleurs est :
 *   - routePrefix()    : 'rh' ou 'gerer'  (détermine les routes de redirection)
 *   - formationLayout(): layout Blade dynamique (null = le template gère lui-même)
 */
trait HasFormationCrud
{
    // ── À implémenter dans chaque contrôleur ──────────────────────────────────

    abstract protected function routePrefix(): string;

    /** Retourner le nom du layout Blade ou null si le template n'en a pas besoin. */
    protected function formationLayout(): ?string
    {
        return null;
    }

    // ── Helpers internes ──────────────────────────────────────────────────────

    /**
     * Règles communes (dates, domaine, durée, type) sans agent.
     */
    private function formationCommonRules(): array
    {
        $anneeEnCours = Annee::currentOpen();
        $annee = $anneeEnCours?->annee ?? now()->year;

        return [
            'theme'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string', 'in:' . implode(',', array_keys(Formation::TYPES))],
            'domaine'      => ['required', 'string', 'in:' . implode(',', array_keys(Formation::DOMAINES))],
            'date_debut'   => ['required', 'date', 'before_or_equal:today', function ($_, $value, $fail) use ($annee) {
                if ((int) date('Y', strtotime($value)) !== $annee) {
                    $fail("La date de début doit appartenir à l'année {$annee}.");
                }
            }],
            'date_fin'     => ['required', 'date', 'after_or_equal:date_debut', 'before_or_equal:today', function ($_, $value, $fail) use ($annee) {
                if ((int) date('Y', strtotime($value)) !== $annee) {
                    $fail("La date de fin doit appartenir à l'année {$annee}.");
                }
            }],
            'duree_heures'  => ['required', 'integer', 'min:1', 'max:9999'],
            'formateur_id'  => ['nullable', 'integer', 'exists:agents,id'],
        ];
    }

    private function formationMessages(): array
    {
        return [
            'date_debut.before_or_equal' => 'La date de début ne peut pas être dans le futur.',
            'date_fin.before_or_equal'   => 'La date de fin ne peut pas être dans le futur.',
            'date_fin.after_or_equal'    => 'La date de fin doit être égale ou postérieure à la date de début.',
        ];
    }

    /**
     * Règles validation pour la CRÉATION (multi-agents, groupe).
     */
    private function formationStoreRules(): array
    {
        return array_merge($this->formationCommonRules(), [
            'agent_ids'   => ['required', 'array', 'min:1'],
            'agent_ids.*' => ['integer', 'exists:agents,id'],
        ]);
    }

    /**
     * Règles validation pour la MODIFICATION (un seul agent).
     */
    private function formationValidationRules(): array
    {
        return array_merge($this->formationCommonRules(), [
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
        ]);
    }

    private function baseViewData(array $extra = []): array
    {
        $data = array_merge([
            'agents'      => Agent::orderBy('nom')->orderBy('prenom')->get(['id', 'nom', 'prenom', 'role', 'poste']),
            'formateurs'  => Agent::whereNull('caisse_id')->whereNull('agence_id')
                                  ->orderBy('nom')->orderBy('prenom')
                                  ->get(['id', 'nom', 'prenom', 'poste', 'role']),
            'domaines'    => Formation::DOMAINES,
            'types'       => Formation::TYPES,
            'routePrefix' => $this->routePrefix(),
        ], $extra);

        if ($layout = $this->formationLayout()) {
            $data['layout'] = $layout;
        }

        return $data;
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Formation::with(['agent', 'createdBy']);

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('theme', 'like', "%{$search}%")
                  ->orWhereHas('agent', fn ($a) =>
                      $a->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenom', 'like', "%{$search}%")
                  );
            });
        }

        if ($domaine = $request->query('domaine')) {
            $query->where('domaine', $domaine);
        }

        if ($annee = $request->query('annee')) {
            $query->whereYear('date_debut', $annee);
        }

        if ($agentId = $request->query('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        $formations    = $query->orderByDesc('created_at')->get();
        $annees        = range(now()->year + 1, now()->year - 4);
        $enAttente     = Formation::with('agent')->where('statut', 'en_attente')->orderByDesc('created_at')->get();
        $enAttenteCount = $enAttente->count();

        return view('rh.formations.index', $this->baseViewData(compact('formations', 'annees', 'enAttente', 'enAttenteCount')));
    }

    public function create(Request $request): View
    {
        $preselectedAgentId = (int) $request->query('agent_id', 0);
        $themesExistants    = Formation::distinct()->orderBy('theme')->pluck('theme');
        $anneeEnCours       = Annee::currentOpen();

        return view('rh.formations.create', $this->baseViewData(compact('preselectedAgentId', 'themesExistants', 'anneeEnCours')));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->formationStoreRules(), $this->formationMessages());

        // Attestation optionnelle (formation groupe RH)
        $attestationPath = null;
        if ($request->hasFile('attestation')) {
            $request->validate([
                'attestation' => ['file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            ]);
            $attestationPath = $request->file('attestation')->store('attestations', 'public');
        }

        $agentIds = $validated['agent_ids'];
        $count    = 0;

        foreach ($agentIds as $agentId) {
            $formation = Formation::create([
                'agent_id'         => $agentId,
                'theme'            => $validated['theme'],
                'type'             => $validated['type'],
                'domaine'          => $validated['domaine'],
                'date_debut'       => $validated['date_debut'],
                'date_fin'         => $validated['date_fin'],
                'duree_heures'     => $validated['duree_heures'],
                'attestation_path' => $attestationPath,
                'statut'           => 'validee',
                'formateur_id'     => $validated['formateur_id'] ?? null,
                'created_by'       => Auth::id(),
            ]);

            $this->notifyFormationAgent(
                $formation,
                'Nouvelle formation enregistrée',
                'Une formation « ' . $formation->theme . ' » a été ajoutée à votre dossier.'
            );

            $count++;
        }

        $msg = $count === 1
            ? 'Formation « ' . $validated['theme'] . ' » enregistrée pour 1 agent.'
            : 'Formation « ' . $validated['theme'] . ' » enregistrée pour ' . $count . ' agents.';

        return redirect()
            ->route($this->routePrefix() . '.formations.index')
            ->with('status', $msg);
    }

    public function edit(Formation $formation): View
    {
        $anneeEnCours = Annee::currentOpen();
        return view('rh.formations.edit', $this->baseViewData(compact('formation', 'anneeEnCours')));
    }

    public function update(Request $request, Formation $formation): RedirectResponse
    {
        $validated = $request->validate($this->formationValidationRules(), $this->formationMessages());

        $formation->update($validated);

        $this->notifyFormationAgent(
            $formation,
            'Formation mise à jour',
            'La formation « ' . $formation->theme . ' » dans votre dossier a été modifiée.'
        );

        return redirect()
            ->route($this->routePrefix() . '.formations.index')
            ->with('status', 'Formation « ' . $formation->theme . ' » mise à jour.');
    }

    public function destroy(Formation $formation): RedirectResponse
    {
        $theme    = $formation->theme;
        $agentId  = $formation->agent_id;

        $formation->delete();

        $this->notifyFormationAgentById(
            $agentId,
            'Formation supprimée',
            'La formation « ' . $theme . ' » a été retirée de votre dossier.',
            null // pas de lien, la formation n'existe plus
        );

        return redirect()
            ->route($this->routePrefix() . '.formations.index')
            ->with('status', 'Formation « ' . $theme . ' » supprimée.');
    }

    public function pdf(Formation $formation): \Illuminate\Http\Response
    {
        $formation->load('agent.service', 'createdBy');

        $pdf = Pdf::loadView('formations.pdf', compact('formation'))
            ->setPaper('a4', 'portrait');

        $filename = 'formation_' . $formation->id . '_' . str_replace(' ', '_', $formation->theme) . '.pdf';

        return $pdf->download($filename);
    }

    // ── Notifications ─────────────────────────────────────────────────────────

    private function notifyFormationAgent(Formation $formation, string $titre, string $message): void
    {
        $this->notifyFormationAgentById(
            $formation->agent_id,
            $titre,
            $message,
            $this->formationsUrlForAgent($formation->agent_id)
        );
    }

    private function notifyFormationAgentById(int $agentId, string $titre, string $message, ?string $lien): void
    {
        // Ne pas notifier si c'est le créateur lui-même (l'agent qui encode sa propre formation)
        $user = User::where('agent_id', $agentId)->first();
        if (! $user || $user->id === Auth::id()) {
            return;
        }

        Alerte::notifier($user->id, $titre, $message, 'moyenne', $lien);
    }

    /**
     * Résout l'URL de la page "Mes formations" en fonction du rôle de l'agent.
     */
    private function formationsUrlForAgent(int $agentId): ?string
    {
        $user = User::where('agent_id', $agentId)->first();
        if (! $user) {
            return null;
        }

        $routeName = match ($user->role) {
            'PCA'                                                                   => 'pca.formations.index',
            'DG'                                                                    => 'dg.formations.index',
            'DGA'                                                                   => 'dga.formations.index',
            'Directeur_Technique', 'Directeur_Direction', 'Directeur_Caisse'       => 'directeur.formations.index',
            'Chef_Service', 'Chef_Agence', 'Chef_Guichet'                          => 'chef.formations.index',
            'Assistante_Dg', 'Conseillers_Dg', 'Secretaire_Assistante'             => 'subordonne.formations.index',
            'RH'                                                                    => 'rh.formations.index',
            default                                                                 => 'personnel.formations.index',
        };

        try {
            return route($routeName);
        } catch (\Exception) {
            return null;
        }
    }
}
