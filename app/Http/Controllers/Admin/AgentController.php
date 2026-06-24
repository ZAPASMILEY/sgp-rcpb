<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Poste;
use App\Services\AgentAccountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function __construct(private AgentAccountService $accounts) {}

   public function index(Request $request): View
{
    // 1. Récupération des filtres depuis la requête
    $role        = $request->input('role');
    $search      = $request->input('search');
    $affectation = $request->input('affectation');
    $sansDate    = $request->boolean('sans_date');

    // 2. Construction de la requête pour récupérer les agents
    $query = Agent::query()->orderBy('nom', 'asc')->orderBy('prenom', 'asc');

    // Liste des colonnes FK pour les affectations directes (membre d'une structure)
    $directFks = ['entite_id', 'direction_id', 'delegation_technique_id', 'caisse_id', 'agence_id', 'guichet_id', 'service_id'];
    // Relations inverses (agent DIRIGE une structure)
    $inverseRelations = [
        'pcaedEntite', 'assistantedEntite',
        'directedDirection', 'secretariedDirection',
        'directedDelegation', 'secretariedDelegation',
        'directedCaisse', 'secretariedCaisse',
        'ledAgence', 'ledGuichet', 'ledService',
    ];

    // Filtrage par rôle si sélectionné
    if ($role) {
        $query->where('role', $role);
    }

    // Filtrage par recherche (Nom / Prénom)
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('nom', 'like', "%{$search}%")
              ->orWhere('prenom', 'like', "%{$search}%");
        });
    }

    // Filtrage agents sans date de prise de fonction
    if ($sansDate) {
        $query->whereNull('date_debut_fonction');
    }

    // Filtrage par affectation
    if ($affectation === 'affecte') {
        $query->where(function ($q) use ($directFks, $inverseRelations): void {
            foreach ($directFks as $col) {
                $q->orWhereNotNull($col);
            }
            foreach ($inverseRelations as $rel) {
                $q->orWhereHas($rel);
            }
        });
    } elseif ($affectation === 'non_affecte') {
        $query->where(function ($q) use ($directFks, $inverseRelations): void {
            foreach ($directFks as $col) {
                $q->whereNull($col);
            }
            foreach ($inverseRelations as $rel) {
                $q->whereDoesntHave($rel);
            }
        });
    }

    // Exécution de la requête pour alimenter la variable $agents
    $agents = $query->get();

    // 3. Calcul des statistiques pour les badges et filtres
    $countsByRole = Agent::query()
        ->selectRaw('role, count(*) as total')
        ->groupBy('role')
        ->pluck('total', 'role');

    $totalAffectes = Agent::query()->where(function ($q) use ($directFks, $inverseRelations): void {
        foreach ($directFks as $col) {
            $q->orWhereNotNull($col);
        }
        foreach ($inverseRelations as $rel) {
            $q->orWhereHas($rel);
        }
    })->count();

    $sansDatCount    = Agent::whereNull('date_debut_fonction')->count();
    $totalReseau     = Agent::count();
    $totalNonAffectes = $totalReseau - $totalAffectes;
    $sansCompte      = Agent::doesntHave('user')->count();

    // Compteurs filtrés pour les cartes de stats
    $isFiltered = (bool) ($role || $search || $affectation || $sansDate);
    $filteredTotal = $agents->count();

    // 4. Envoi complet à la vue
    return view('admin.agents.index', [
        'agents'           => $agents,
        'roleActive'       => $role,
        'affectation'      => $affectation,
        'sansDate'         => $sansDate,
        'search'           => $search,
        'roles'            => Agent::ROLES,
        'countsByRole'     => $countsByRole,
        'totalAgents'      => $totalReseau,
        'totalAffectes'    => $totalAffectes,
        'totalNonAffectes' => $totalNonAffectes,
        'isFiltered'       => $isFiltered,
        'filteredTotal'    => $filteredTotal,
        'sansDatCount'     => $sansDatCount,
        'sansCompte'       => $sansCompte,
    ]);
}
    public function create(Request $request): View
    {
        $redirectTo = (string) $request->query('redirect_to', '');

        return view('admin.agents.create', array_merge(
            $this->formData(),
            ['redirectTo' => $redirectTo]
        ));
    }

    public function show(Agent $agent): View
{
    return view('admin.agents.show', [
        'agent' => $agent->load([
            'entite',
            'direction',
            'service.direction.entite',
            'service.delegationTechnique',
            'service.caisse',
            'delegationTechnique',
            'caisse',
            'agence',
            'guichet',
            'user',
        ]),
        'postes' => Poste::orderBy('libelle')->pluck('libelle'),
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

        DB::transaction(function () use ($validated): void {
            $agent = Agent::query()->create($validated);
            $this->accounts->ensureAccount($agent);
        });

        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/'))) {
            return redirect()
                ->to($redirectTo)
                ->with('status', 'Agent créé avec succès. Compte de connexion généré (mot de passe : 11111111).');
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('status', 'Agent créé avec succès. Compte de connexion généré (mot de passe : 11111111).');
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

        DB::transaction(function () use ($agent, $validated): void {
            $agent->update($validated);
            $this->accounts->ensureAccount($agent->fresh());
        });

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Agent mis à jour avec succès.');
    }

    public function destroy(Request $request, Agent $agent): RedirectResponse
    {
        $this->deletePhoto($agent->photo_path);
        $agent->user?->delete();
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

    /**
     * Met à jour uniquement la fonction (poste) d'un agent depuis son profil.
     */
    public function updatePoste(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'poste' => ['required', 'string', 'max:150'],
        ], [
            'poste.required' => 'La fonction est obligatoire.',
        ]);

        $agent->update(['poste' => $validated['poste']]);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', 'Fonction de '.$agent->prenom.' '.$agent->nom.' mise à jour.');
    }

    /**
     * Crée (ou réactive) le compte de connexion d'un agent existant.
     * Mot de passe par défaut : 11111111.
     */
    public function activateAccount(Agent $agent): RedirectResponse
    {
        $this->accounts->ensureAccount($agent);

        return redirect()
            ->back()
            ->with('status', "Compte de {$agent->prenom} {$agent->nom} activé (mot de passe : 11111111).");
    }

    /**
     * Accorde ou révoque la permission 'formations.valider' directement sur l'utilisateur.
     * Permet de déléguer la validation des formations à un agent non-RH.
     */
    public function toggleFormationValider(Agent $agent): RedirectResponse
    {
        $user = $agent->user;

        if (! $user) {
            return redirect()->back()->with('error', "Cet agent n'a pas de compte utilisateur.");
        }

        if ($user->hasDirectPermission('formations.valider')) {
            $user->revokePermissionTo('formations.valider');
            $msg = "Permission de validation des formations retirée à {$agent->prenom} {$agent->nom}.";
        } else {
            $user->givePermissionTo('formations.valider');
            $msg = "{$agent->prenom} {$agent->nom} peut maintenant valider les formations.";
        }

        return redirect()->back()->with('status', $msg);
    }

    /**
     * Crée les comptes manquants pour tous les agents qui n'en ont pas.
     * Utilise une requête DB directe pour éviter les faux positifs liés aux scopes Eloquent.
     */
    public function syncAllAccounts(): RedirectResponse
    {
        // Requête directe sans scope pour trouver les agents sans aucun user (actif ou non)
        $agentsIds = DB::table('agents')
            ->whereNotIn('id', DB::table('users')->whereNotNull('agent_id')->pluck('agent_id'))
            ->pluck('id');

        if ($agentsIds->isEmpty()) {
            return redirect()
                ->route('admin.agents.index')
                ->with('status', 'Tous les agents ont déjà un compte.');
        }

        // Hash calculé UNE seule fois pour tous les comptes (bcrypt ~200ms/appel)
        $hashedPassword = Hash::make('11111111');
        $now            = now();
        $count          = 0;

        $agents = Agent::whereIn('id', $agentsIds)->get();

        foreach ($agents as $agent) {
            $role = AgentAccountService::roleForFonction($agent->role);

            $exists = DB::table('users')->where('agent_id', $agent->id)->exists();
            if ($exists) {
                DB::table('users')->where('agent_id', $agent->id)
                    ->update(['is_active' => true, 'role' => $role]);
            } else {
                DB::table('users')->insert([
                    'agent_id'             => $agent->id,
                    'name'                 => trim($agent->prenom . ' ' . $agent->nom),
                    'email'                => $agent->email,
                    'password'             => $hashedPassword,
                    'role'                 => $role,
                    'is_active'            => true,
                    'must_change_password' => true,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
                $count++;
            }
        }

        $message = $count > 0
            ? "{$count} compte(s) créé(s) avec succès. Mot de passe par défaut : 11111111."
            : 'Tous les agents ont déjà un compte.';

        return redirect()
            ->route('admin.agents.index')
            ->with('status', $message);
    }

    // ── Import CSV ────────────────────────────────────────────────────────────

    public function importForm(): View
    {
        return view('admin.agents.import', [
            'roles'           => Agent::ROLES,
            'postesByFonction' => Poste::byFonction(),
        ]);
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $roles = implode(' / ', array_keys(Agent::ROLES));

        $consignes = [
            '# ================================================================',
            '# MODELE IMPORT AGENTS — SGP-RCPB',
            '# ================================================================',
            '#',
            '# Ces lignes commencent par # : elles sont des CONSIGNES.',
            '# Le système les ignore automatiquement lors de l\'import.',
            '# Ne pas les supprimer — elles guident le remplissage.',
            '#',
            '# ---------------------------------------------------------------',
            '# COLONNES OBLIGATOIRES (à ne pas laisser vides)',
            '# ---------------------------------------------------------------',
            '# nom               : Nom de famille       Ex : SAWADOGO',
            '# prenom            : Prénom               Ex : Fatima',
            '# sexe              : homme ou femme       Valeurs acceptées : homme, femme, H, F',
            '# matricule         : Matricule unique      Ex : MAT-2026-001',
            '# role              : Rôle de l\'agent      Voir liste ci-dessous (casse exacte)',
            '#',
            '# ---------------------------------------------------------------',
            '# COLONNES OPTIONNELLES',
            '# ---------------------------------------------------------------',
            '# fonction          : Intitulé du poste     OBLIGATOIRE si rôle = Agent ou Conseiller DG',
            '#                                          Ex : Agent de crédit, Caissier, Chargé de recouvrement',
            '#                   NE PAS CONFONDRE avec date_debut (qui est la date de prise de poste)',
            '# email             : Email professionnel  Unique — Ex : f.sawadogo@rcpb.bf',
            '# numero_telephone  : Numéro de téléphone  Unique — Ex : +22670111111',
            '# date_debut          : Date à laquelle l\'agent a pris son poste  Format : AAAA-MM-JJ  Ex : 2022-01-15',
            '#',
            '# ---------------------------------------------------------------',
            '# ROLES VALIDES (copier-coller exactement, respecter la casse)',
            '# ---------------------------------------------------------------',
            '# ' . $roles,
            '#',
            '# ---------------------------------------------------------------',
            '# REMARQUES IMPORTANTES',
            '# ---------------------------------------------------------------',
            '# - Le matricule doit être unique pour chaque agent',
            '# - L\'email doit être unique si renseigné',
            '# - Le téléphone doit être unique si renseigné',
            '# - Les lignes incomplètes ou incorrectes sont ignorées à l\'import',
            '# - Un compte de connexion est créé automatiquement après import',
            '# ================================================================',
            '# DEBUT DES DONNEES — Remplir à partir de la ligne suivante',
            '# ================================================================',
        ];

        $header = ['nom', 'prenom', 'sexe', 'matricule', 'role', 'fonction', 'email', 'numero_telephone', 'date_debut'];

        return response()->streamDownload(function () use ($consignes, $header): void {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel

            // Lignes de consignes (ignorées à l'import)
            foreach ($consignes as $ligne) {
                fputcsv($f, [$ligne], ';');
            }

            // En-tête des données
            fputcsv($f, $header, ';');

            // 50 lignes vides pour la saisie
            $vide = array_fill(0, count($header), '');
            for ($i = 0; $i < 50; $i++) {
                fputcsv($f, $vide, ';');
            }

            fclose($f);
        }, 'modele_import_agents_RCPB.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ], [
            'csv_file.required' => 'Veuillez sélectionner un fichier CSV.',
            'csv_file.mimes'    => 'Le fichier doit être au format CSV.',
            'csv_file.max'      => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $path    = $request->file('csv_file')->getRealPath();
        $content = file_get_contents($path);

        // Supprimer BOM UTF-8 si présent
        $content = ltrim($content, "\xEF\xBB\xBF");

        $lines = preg_split('/\r\n|\n|\r/', trim($content));

        // Détecter le délimiteur sur la première ligne non-commentaire
        $firstDataLine = collect($lines)->first(fn ($l) => !str_starts_with(trim($l), '#') && trim($l) !== '');
        if (!$firstDataLine) {
            return redirect()->back()->with('error', 'Le fichier CSV est vide ou ne contient que des commentaires.');
        }
        $delimiter = substr_count($firstDataLine, ';') >= substr_count($firstDataLine, ',') ? ';' : ',';

        // Trouver l'index de la ligne d'en-tête (première ligne non-commentaire, non-vide)
        $headerIdx = null;
        foreach ($lines as $idx => $line) {
            if (str_starts_with(trim($line), '#') || trim($line) === '') continue;
            $headerIdx = $idx;
            break;
        }
        if ($headerIdx === null) {
            return redirect()->back()->with('error', 'Aucune ligne d\'en-tête trouvée dans le fichier.');
        }

        // Lire l'en-tête
        $header = array_map('trim', str_getcsv($lines[$headerIdx], $delimiter));
        $header = array_map('strtolower', $header);

        $required = ['nom', 'prenom', 'sexe', 'matricule', 'role'];
        $missing  = array_diff($required, $header);
        if (!empty($missing)) {
            return redirect()->back()->with('error', 'Colonnes manquantes dans le CSV : ' . implode(', ', $missing));
        }

        $validRoles  = array_keys(Agent::ROLES);
        $imported    = 0;
        $errors      = [];
        $existingEmails      = Agent::whereNotNull('email')->pluck('email')->flip();
        $existingMatricules  = Agent::whereNotNull('matricule')->pluck('matricule')->flip();
        $existingPhones      = Agent::whereNotNull('numero_telephone')->pluck('numero_telephone')->flip();

        DB::beginTransaction();
        try {
            for ($i = $headerIdx + 1; $i < count($lines); $i++) {
                $line = trim($lines[$i]);

                // Ignorer les lignes de commentaires/consignes
                if (str_starts_with($line, '#')) continue;
                if ($line === '') {
                    continue;
                }

                $row    = array_map('trim', str_getcsv($line, $delimiter));
                $data   = [];
                foreach ($header as $idx => $col) {
                    $data[$col] = $row[$idx] ?? '';
                }

                // Normaliser les alias de colonnes CSV
                // "fonction" ou "poste" → champ poste en base
                if (!isset($data['poste']) || $data['poste'] === '') {
                    $data['poste'] = $data['fonction'] ?? '';
                }
                // "date_debut" → champ date_debut_fonction en base
                if (!isset($data['date_debut_fonction']) || $data['date_debut_fonction'] === '') {
                    $data['date_debut_fonction'] = $data['date_debut'] ?? '';
                }

                $lineNum = $i + 1;
                $rowErrors = [];

                // Champs obligatoires
                if (empty($data['nom']))       $rowErrors[] = 'nom manquant';
                if (empty($data['prenom']))     $rowErrors[] = 'prénom manquant';
                if (empty($data['matricule']))  $rowErrors[] = 'matricule manquant';
                if (empty($data['role']))       $rowErrors[] = 'rôle manquant';

                // Poste obligatoire pour les rôles Agent et Conseiller DG
                $rolesRequiringPoste = ['Agent', 'Conseiller DG'];
                if (in_array($data['role'] ?? '', $rolesRequiringPoste, true) && empty($data['poste'])) {
                    $rowErrors[] = "poste obligatoire pour le rôle « {$data['role']} »";
                }

                // Sexe
                $sexe = strtolower($data['sexe'] ?? '');
                if (in_array($sexe, ['h', 'm', 'masculin', 'homme'], true)) {
                    $sexe = 'homme';
                } elseif (in_array($sexe, ['f', 'féminin', 'feminin', 'femme'], true)) {
                    $sexe = 'femme';
                } else {
                    $rowErrors[] = 'sexe invalide (attendu : homme/femme ou H/F)';
                }

                // Rôle
                $role = $data['role'] ?? '';
                if (!in_array($role, $validRoles, true)) {
                    $rowErrors[] = "rôle « {$role} » invalide";
                }

                // Email unique
                $email = $data['email'] ?? '';
                if ($email !== '' && isset($existingEmails[$email])) {
                    $rowErrors[] = "email « {$email} » déjà utilisé";
                }

                // Matricule unique
                if (!empty($data['matricule']) && isset($existingMatricules[$data['matricule']])) {
                    $rowErrors[] = "matricule « {$data['matricule']} » déjà utilisé";
                }

                // Téléphone unique
                $tel = $data['numero_telephone'] ?? '';
                if ($tel !== '' && isset($existingPhones[$tel])) {
                    $rowErrors[] = "téléphone « {$tel} » déjà utilisé";
                }

                // Date
                $date = null;
                if (!empty($data['date_debut_fonction'])) {
                    foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $fmt) {
                        $d = \DateTime::createFromFormat($fmt, $data['date_debut_fonction']);
                        if ($d && $d->format($fmt) === $data['date_debut_fonction']) {
                            $date = $d->format('Y-m-d');
                            break;
                        }
                    }
                    if ($date === null) {
                        $rowErrors[] = 'date_debut_fonction invalide (format attendu : AAAA-MM-JJ)';
                    }
                }

                if (!empty($rowErrors)) {
                    $errors[] = "Ligne {$lineNum} (" . ($data['nom'] ?? '?') . ' ' . ($data['prenom'] ?? '') . ") : " . implode(', ', $rowErrors);
                    continue;
                }

                $agent = Agent::create([
                    'nom'                 => $data['nom'],
                    'prenom'              => $data['prenom'],
                    'sexe'                => $sexe,
                    'email'               => $email ?: null,
                    'numero_telephone'    => $tel ?: null,
                    'matricule'           => $data['matricule'],
                    'role'                => $role,
                    'poste'               => $data['poste'] ?? null ?: null,
                    'date_debut_fonction' => $date,
                ]);

                $this->accounts->ensureAccount($agent);

                // Mise à jour des ensembles pour détecter les doublons dans le même fichier
                if ($email)            $existingEmails[$email]              = true;
                if ($data['matricule']) $existingMatricules[$data['matricule']] = true;
                if ($tel)              $existingPhones[$tel]                = true;

                $imported++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.agents.import')
            ->with('import_result', [
                'imported' => $imported,
                'errors'   => $errors,
            ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'postesByFonction' => Poste::byFonction(),
        ];
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

        $matriculeRule = ['required', 'string', 'max:50'];
        if ($agent === null) {
            $matriculeRule[] = Rule::unique('agents', 'matricule');
        } else {
            $matriculeRule[] = Rule::unique('agents', 'matricule')->ignore($agent->id);
        }

        return $request->validate([
            // Données personnelles
            'nom'              => ['required', 'string', 'max:100'],
            'prenom'           => ['required', 'string', 'max:100'],
            'sexe'             => ['required', 'in:homme,femme'],
            'email'            => $emailRule,
            'numero_telephone' => [
                'required',
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
            'matricule'           => $matriculeRule,
            'role'                => [
                'required',
                'string',
                Rule::in(array_keys(Agent::ROLES)),
                // Rôles uniques : un seul agent autorisé par rôle
                function (string $attribute, mixed $value, \Closure $fail) use ($agent): void {
                    $singletonRoles = ['PCA', 'Directeur Général', 'DGA'];
                    if (in_array($value, $singletonRoles, true)) {
                        $exists = Agent::where('role', $value)
                            ->when($agent, fn ($q) => $q->where('id', '!=', $agent->id))
                            ->exists();
                        if ($exists) {
                            $fail("Un agent avec le rôle « {$value} » existe déjà. Ce rôle ne peut être attribué qu'à une seule personne.");
                        }
                    }
                },
            ],
            'poste'               => [
                Rule::requiredIf(in_array($request->input('role'), ['Agent', 'Conseiller DG'], true)),
                'nullable', 'string', 'max:150',
            ],
            'date_debut_fonction' => ['required', 'date'],
        ], [
            'nom.required'              => 'Le nom est obligatoire.',
            'prenom.required'           => 'Le prénom est obligatoire.',
            'email.required'            => "L'email est obligatoire.",
            'email.email'               => "L'email n'est pas valide.",
            'email.unique'              => 'Cet email est déjà utilisé par un autre agent.',
            'numero_telephone.unique'   => 'Ce numéro de téléphone est déjà utilisé par un autre agent.',
            'sexe.required'             => 'Le sexe est obligatoire.',
            'sexe.in'                   => 'Le sexe doit être "homme" ou "femme".',
            'matricule.required'        => 'Le matricule est obligatoire.',
            'matricule.unique'          => 'Ce matricule est déjà utilisé par un autre agent.',
            'role.required'              => 'Le rôle est obligatoire.',
            'role.in'                    => 'Le rôle sélectionné est invalide.',
            'poste.required'             => 'La fonction est obligatoire pour ce rôle.',
            'date_debut_fonction.required' => 'La date de prise de fonction est obligatoire.',
            'date_debut_fonction.date'     => 'La date de prise de fonction est invalide.',
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
