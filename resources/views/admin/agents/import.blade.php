@extends('layouts.app')

@section('title', 'Import CSV Agents | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="space-y-6 pb-8">

    {{-- En-tête --}}
    <div class="rounded-2xl overflow-hidden shadow-lg">
        <div style="background: linear-gradient(135deg, #059669 0%, #0891b2 60%, #2563eb 100%)" class="px-8 py-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-white text-2xl">
                        <i class="fas fa-file-csv"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">Import CSV — Agents</h1>
                        <p class="text-xs text-white/70 mt-1 font-semibold">Importer plusieurs centaines d'agents en une seule opération</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.agents.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 border border-white/30 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-white/30">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                    <a href="{{ route('admin.agents.import.template') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold shadow-sm transition hover:bg-slate-100"
                       style="color:#059669">
                        <i class="fas fa-download text-xs"></i> Télécharger le modèle
                    </a>
                </div>
            </div>
        </div>

        {{-- Bande info --}}
        <div class="bg-white border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100">
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Délimiteur auto-détecté (<code>;</code> ou <code>,</code>)</span>
            </div>
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Doublons ignorés ligne par ligne</span>
            </div>
            <div class="px-6 py-3 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500 text-sm"></i>
                <span class="text-xs font-bold text-slate-600">Compte de connexion créé automatiquement</span>
            </div>
        </div>
    </div>

    {{-- Résultats import précédent --}}
    @if(session('import_result'))
        @php $result = session('import_result'); @endphp
        <div class="rounded-2xl overflow-hidden shadow-sm border border-slate-100">
            <div class="px-6 py-4 flex items-center gap-3"
                 style="background: linear-gradient(to right, #ecfdf5, #f0fdf4)">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check-double"></i>
                </div>
                <div>
                    <p class="font-black text-emerald-800">
                        {{ $result['imported'] }} agent(s) importé(s) avec succès
                        @if(count($result['errors']) > 0)
                            — {{ count($result['errors']) }} ligne(s) ignorée(s)
                        @endif
                    </p>
                    <p class="text-xs text-emerald-600 mt-0.5">Mot de passe par défaut pour tous les nouveaux comptes : <strong>11111111</strong></p>
                </div>
            </div>
            @if(count($result['errors']) > 0)
                <div class="p-6 space-y-2">
                    <p class="text-xs font-black uppercase tracking-wider text-rose-600 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Lignes ignorées
                    </p>
                    @foreach($result['errors'] as $err)
                        <div class="flex items-start gap-2 rounded-xl bg-rose-50 border border-rose-100 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-times-circle mt-0.5 shrink-0 text-rose-400"></i>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Bouton d'accès au formulaire manuel --}}
    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold text-slate-700">Ajouter un agent manuellement</p>
            <p class="text-xs text-slate-400">Remplir le formulaire directement, sans passer par un fichier CSV</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-agent').classList.remove('hidden')"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:opacity-90"
                style="background: linear-gradient(to right, #7c3aed, #6d28d9)">
            <i class="fas fa-plus text-xs"></i> Ouvrir le formulaire
        </button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- Formulaire upload --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-100">
            <div class="px-6 py-3 border-b border-slate-100 flex items-center gap-2"
                 style="background: linear-gradient(to right, #ecfdf5, #f0fdf4)">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xs">
                    <i class="fas fa-upload"></i>
                </div>
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">Charger un fichier CSV</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('admin.agents.import.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    {{-- Zone de dépôt --}}
                    <div id="drop-zone"
                         class="relative rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center transition hover:border-emerald-400 hover:bg-emerald-50/30 cursor-pointer"
                         onclick="document.getElementById('csv_file').click()">
                        <div id="drop-icon" class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 text-2xl mx-auto mb-3">
                            <i class="fas fa-file-csv"></i>
                        </div>
                        <p id="drop-label" class="text-sm font-bold text-slate-500">Glissez votre fichier CSV ici</p>
                        <p class="text-xs text-slate-400 mt-1">ou cliquez pour parcourir — max 10 Mo</p>
                        <input id="csv_file" name="csv_file" type="file" accept=".csv,.txt" class="hidden"
                               onchange="handleFileSelect(this)">
                    </div>

                    @error('csv_file')
                        <p class="text-xs text-rose-600 font-bold"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                    @enderror

                    <button type="submit" id="submit-btn" disabled
                            class="w-full py-3.5 rounded-2xl text-sm font-black text-white shadow-lg transition disabled:opacity-40 disabled:cursor-not-allowed hover:opacity-90"
                            style="background: linear-gradient(to right, #059669, #0891b2)">
                        <i class="fas fa-rocket mr-2"></i> Lancer l'import
                    </button>
                </form>
            </div>
        </div>

        {{-- Guide de remplissage --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-100">
            <div class="px-6 py-3 border-b border-slate-100 flex items-center justify-between"
                 style="background: linear-gradient(to right, #eff6ff, #f5f3ff)">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600 text-xs">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-blue-700">Guide de remplissage</p>
                </div>
                <a href="{{ route('admin.agents.import.template') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-[11px] font-black text-white shadow-sm hover:bg-blue-700 transition">
                    <i class="fas fa-download text-[10px]"></i> Télécharger le modèle
                </a>
            </div>

            <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height:520px">

                {{-- Règles générales --}}
                <div class="px-5 py-4 bg-amber-50/60">
                    <p class="text-[10px] font-black uppercase tracking-wider text-amber-600 mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Règles générales</p>
                    <ul class="space-y-1 text-xs text-slate-600">
                        <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>La <strong>ligne 1</strong> doit être l'en-tête exacte (noms de colonnes)</li>
                        <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>Délimiteur : <code class="bg-white border border-slate-200 px-1 rounded font-bold">;</code> ou <code class="bg-white border border-slate-200 px-1 rounded font-bold">,</code> (détecté automatiquement)</li>
                        <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>Encodage : <strong>UTF-8</strong> (le modèle téléchargeable est déjà configuré)</li>
                        <li class="flex items-start gap-2"><i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>L'ordre des colonnes est <strong>libre</strong> — seuls les noms exacts comptent</li>
                        <li class="flex items-start gap-2"><i class="fas fa-info-circle text-blue-400 mt-0.5 shrink-0"></i>Les lignes avec erreurs sont <strong>ignorées</strong> (les autres sont importées)</li>
                    </ul>
                </div>

                {{-- Colonnes --}}
                @php
                    $guide = [
                        ['nom',                 true,  'Nom de famille',       'SAWADOGO',        'Texte libre — max 255 caractères'],
                        ['prenom',              true,  'Prénom',               'Fatima',           'Texte libre — max 255 caractères'],
                        ['sexe',                true,  'Sexe',                 'femme',            'Valeurs acceptées : homme, femme, H, F, masculin, féminin'],
                        ['matricule',           true,  'Matricule',            'MAT-2026-001',     'Unique dans toute la base — requis'],
                        ['role',                true,  'Rôle',                 'Agent',            'Doit correspondre exactement à l\'un des rôles valides (voir ci-dessous)'],
                        ['poste',               false, 'Fonction occupée',     'Agent de crédit',  'Obligatoire uniquement si rôle = Agent ou Conseiller DG — optionnel sinon'],
                        ['email',               false, 'Email professionnel',  'f.sawadogo@rcpb.bf','Unique dans la base — optionnel mais recommandé (sert d\'identifiant de connexion)'],
                        ['numero_telephone',    false, 'Téléphone',            '+22670111111',     'Unique dans la base — optionnel'],
                        ['date_debut_fonction', false, 'Date de prise de fonction', '2022-01-15', 'Format : AAAA-MM-JJ (ou JJ/MM/AAAA, JJ-MM-AAAA) — optionnel'],
                    ];
                @endphp

                @foreach($guide as [$col, $req, $label, $exemple, $regle])
                <div class="px-5 py-3 {{ $req ? 'bg-white' : 'bg-slate-50/40' }}">
                    <div class="flex items-center gap-2 mb-1">
                        <code class="text-xs font-black {{ $req ? 'text-slate-800' : 'text-slate-500' }} bg-slate-100 px-2 py-0.5 rounded-lg">{{ $col }}</code>
                        @if($req)
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[9px] font-black text-rose-600">OBLIGATOIRE</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-black text-slate-400">OPTIONNEL</span>
                        @endif
                        <span class="text-xs font-semibold text-slate-600">{{ $label }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-1">{{ $regle }}</p>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-slate-400 font-semibold">Exemple :</span>
                        <code class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-100 px-1.5 py-0.5 rounded font-bold">{{ $exemple }}</code>
                    </div>
                </div>
                @endforeach

                {{-- Rôles valides --}}
                <div class="px-5 py-4">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3"><i class="fas fa-id-badge mr-1"></i>Rôles valides (colonne <code>role</code>)</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(array_keys($roles) as $r)
                            <span class="rounded-lg bg-white border border-slate-200 px-2 py-1 text-[10px] font-bold text-slate-700 shadow-sm">{{ $r }}</span>
                        @endforeach
                    </div>
                    <p class="mt-3 text-[11px] text-slate-400"><i class="fas fa-exclamation-circle text-amber-400 mr-1"></i>La casse doit être <strong>exacte</strong> — ex : <code class="bg-slate-100 px-1 rounded">Chef d'Agence</code> et non <code class="bg-slate-100 px-1 rounded">chef d'agence</code></p>
                </div>

                {{-- Valeurs sexe --}}
                <div class="px-5 py-4 bg-slate-50/40">
                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2"><i class="fas fa-venus-mars mr-1"></i>Valeurs acceptées — colonne <code>sexe</code></p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['homme', 'femme', 'H', 'F', 'masculin', 'féminin'] as $v)
                            <code class="rounded-lg bg-white border border-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $v }}</code>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     MODALE — Formulaire de création d'agent
══════════════════════════════════════════════════════════════ --}}
<div id="modal-agent" class="hidden fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 py-10">
    {{-- Fond --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
         onclick="document.getElementById('modal-agent').classList.add('hidden')"></div>

    {{-- Panneau --}}
    <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">

        {{-- En-tête --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100"
             style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/20 text-white">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-200">Administration</p>
                    <h2 class="text-lg font-black text-white">Nouvel agent</h2>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-agent').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white/80 hover:bg-white/25 hover:text-white transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        {{-- Erreurs --}}
        @if ($errors->any() && old('_from_modal'))
            <div class="mx-6 mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('admin.agents.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="_from_modal" value="1">
            <input type="hidden" name="redirect_to" value="{{ route('admin.agents.import') }}">

            {{-- Identité --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Nom <span class="text-rose-500">*</span></label>
                    <input name="nom" type="text" value="{{ old('nom') }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                           placeholder="Nom de famille">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                    <input name="prenom" type="text" value="{{ old('prenom') }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                           placeholder="Prénom">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                    <select name="sexe" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0">
                        <option value="">Sélectionner</option>
                        <option value="homme" @selected(old('sexe') === 'homme')>Homme</option>
                        <option value="femme" @selected(old('sexe') === 'femme')>Femme</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Matricule <span class="text-rose-500">*</span></label>
                    <input name="matricule" type="text" value="{{ old('matricule') }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                           placeholder="Ex : AGT-2026-001">
                </div>
            </div>

            {{-- Rôle --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Rôle <span class="text-rose-500">*</span></label>
                <select id="modal-role" name="role" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0">
                    <option value="">Sélectionner un rôle</option>
                    @foreach(\App\Models\Agent::ROLES as $val => $label)
                        <option value="{{ $val }}" @selected(old('role') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fonction --}}
            <div id="modal-poste-wrap" class="space-y-1.5 hidden">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                    Fonction <span class="text-rose-500">*</span>
                </label>
                <input id="modal-poste" name="poste" type="text" value="{{ old('poste') }}"
                       list="modal-postes-list"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                       placeholder="Ex : Caissier, Chargé de crédit…">
                <datalist id="modal-postes-list">
                    @foreach(array_merge(...array_values($postesByFonction)) as $libelle)
                        <option value="{{ $libelle }}">
                    @endforeach
                </datalist>
            </div>

            {{-- Contact --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                    <input name="numero_telephone" type="text" value="{{ old('numero_telephone') }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                           placeholder="+226 70 00 00 00">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Email <span class="text-rose-500">*</span></label>
                    <input name="email" type="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0"
                           placeholder="agent@rcpb.bf">
                </div>
            </div>

            {{-- Date --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Date de prise de fonction <span class="text-rose-500">*</span></label>
                <input name="date_debut_fonction" type="date" value="{{ old('date_debut_fonction') }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 focus:border-violet-400 focus:ring-0">
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button"
                        onclick="document.getElementById('modal-agent').classList.add('hidden')"
                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-black text-white shadow-sm transition hover:opacity-90"
                        style="background: linear-gradient(to right, #7c3aed, #6d28d9)">
                    <i class="fas fa-save text-xs"></i> Enregistrer l'agent
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function handleFileSelect(input) {
    var file = input.files[0];
    if (!file) return;
    var label   = document.getElementById('drop-label');
    var icon    = document.getElementById('drop-icon');
    var btn     = document.getElementById('submit-btn');
    var zone    = document.getElementById('drop-zone');

    label.textContent = file.name;
    label.classList.add('text-emerald-700');
    icon.classList.remove('bg-slate-100', 'text-slate-400');
    icon.classList.add('bg-emerald-100', 'text-emerald-600');
    zone.classList.add('border-emerald-400', 'bg-emerald-50/30');
    zone.classList.remove('border-slate-200', 'bg-slate-50');
    btn.disabled = false;
}

// Champ Fonction dans la modale : visible uniquement pour Agent et Conseiller DG
(function () {
    var ROLES_AVEC_FONCTION = ['Agent', 'Conseiller DG'];
    var roleSelect = document.getElementById('modal-role');
    var posteWrap  = document.getElementById('modal-poste-wrap');
    var posteInput = document.getElementById('modal-poste');

    function updatePosteModal() {
        var role = roleSelect ? roleSelect.value : '';
        var show = ROLES_AVEC_FONCTION.includes(role);
        if (posteWrap)  posteWrap.classList.toggle('hidden', !show);
        if (posteInput) { posteInput.required = show; if (!show) posteInput.value = ''; }
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', updatePosteModal);
        updatePosteModal();
    }

    // Réouvrir la modale si erreur de validation (retour serveur)
    @if($errors->any() && old('_from_modal'))
        document.getElementById('modal-agent').classList.remove('hidden');
    @endif
})();

// Drag & drop
var zone = document.getElementById('drop-zone');
zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('border-emerald-400'); });
zone.addEventListener('dragleave', function() { zone.classList.remove('border-emerald-400'); });
zone.addEventListener('drop', function(e) {
    e.preventDefault();
    var file = e.dataTransfer.files[0];
    if (!file) return;
    var input = document.getElementById('csv_file');
    var dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    handleFileSelect(input);
});
</script>
@endpush
