@extends($layout ?? 'layouts.rh')

@section('title', 'Nouvelle formation | SGP-RCPB')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

<style>
    /* ── Tom Select custom theme (multi) ── */
    .ts-wrapper .ts-control {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #1e293b;
        box-shadow: none;
        cursor: pointer;
        min-height: 2.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
        align-items: center;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #34d399;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(52,211,153,0.15);
    }
    .ts-wrapper .ts-control input { color: #1e293b; font-size: 0.875rem; min-width: 120px; }
    /* Tags agents sélectionnés */
    .ts-wrapper .ts-control .item {
        background: #d1fae5;
        color: #065f46;
        border-radius: 0.5rem;
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .ts-wrapper .ts-control .item .remove {
        color: #065f46;
        opacity: 0.6;
        font-size: 0.9rem;
        line-height: 1;
        cursor: pointer;
    }
    .ts-wrapper .ts-control .item .remove:hover { opacity: 1; }
    .ts-dropdown { border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; font-size: 0.875rem; }
    .ts-dropdown .option { padding: 0.55rem 1rem; color: #334155; }
    .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #f0fdf4; color: #065f46; }
    .ts-dropdown .option.selected { background: #d1fae5; color: #065f46; font-weight: 700; }
    .ts-dropdown-content { max-height: 220px; }

    /* Autocomplete titre */
    .autocomplete-wrap { position: relative; }
    .autocomplete-list {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-height: 200px;
        overflow-y: auto; display: none;
    }
    .autocomplete-list li {
        padding: 0.55rem 1rem; font-size: 0.875rem; color: #334155;
        cursor: pointer; list-style: none;
    }
    .autocomplete-list li:hover { background: #f0fdf4; color: #065f46; }
    .autocomplete-list li mark { background: #bbf7d0; color: #065f46; border-radius: 2px; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="mx-auto max-w-2xl flex flex-col gap-6">

    <header class="admin-panel px-6 py-5">
        <div class="flex items-center gap-3">
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ressources Humaines</p>
                <h1 class="text-xl font-black tracking-tight text-slate-950">Enregistrer une formation</h1>
            </div>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Veuillez corriger les erreurs :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route(($routePrefix ?? 'rh').'.formations.store') }}" enctype="multipart/form-data" class="admin-panel px-6 py-6 flex flex-col gap-5">
        @csrf

        {{-- Agents (multi-select) --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Agents participants *
                <span class="ml-1 font-normal normal-case tracking-normal text-slate-400">(plusieurs sélections possibles)</span>
            </label>
            <select id="select-agent" name="agent_ids[]" multiple required
                    class="@error('agent_ids') border-rose-400 @enderror">
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}"
                            @selected(in_array($ag->id, old('agent_ids', $preselectedAgentId ? [$preselectedAgentId] : [])))>
{{ trim($ag->prenom . ' ' . $ag->nom) }}{{ $ag->poste ? ' — ' . $ag->poste : '' }}
                        </option>
                @endforeach
            </select>
            @error('agent_ids')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
            @error('agent_ids.*')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Titre (avec autocomplete) --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Theme *
            </label>
            <div class="autocomplete-wrap">
                <input type="text" id="input-theme" name="theme" value="{{ old('theme') }}"
                       required maxlength="255" autocomplete="off"
                       placeholder="Ex: Formation leadership et management d'équipe"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('theme') border-rose-400 @enderror">
                <ul id="autocomplete-list" class="autocomplete-list"></ul>
            </div>
            @error('theme')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Type + Domaine --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- Type --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Type *</label>
                <div class="flex gap-3">
                    @foreach($types as $key => $label)
                        <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border-2 px-4 py-3 transition
                            {{ old('type', 'interne') === $key ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                            <input type="radio" name="type" value="{{ $key }}"
                                   {{ old('type', 'interne') === $key ? 'checked' : '' }}
                                   class="accent-emerald-600">
                            <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
            {{-- Domaine --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Domaine *</label>
                <select name="domaine" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('domaine') border-rose-400 @enderror">
                    <option value="">— Choisir un domaine —</option>
                    @foreach($domaines as $key => $label)
                        <option value="{{ $key }}" @selected(old('domaine') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dates : année fixée par le semestre ouvert --}}
        @php
            $annee = $anneeEnCours?->annee ?? now()->year;
            $moisLabels = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
                           '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
            $oldDebut = old('date_debut');
            $oldFin   = old('date_fin');
            $debutJour  = $oldDebut ? substr($oldDebut, 8, 2) : '';
            $debutMois  = $oldDebut ? substr($oldDebut, 5, 2) : '';
            $finJour    = $oldFin   ? substr($oldFin,   8, 2) : '';
            $finMois    = $oldFin   ? substr($oldFin,   5, 2) : '';
        @endphp
        <div class="grid grid-cols-2 gap-4">
            {{-- Date de début --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de début *</label>
                <input type="hidden" name="date_debut" id="date_debut_hidden" value="{{ $oldDebut ?: '' }}">
                <div class="flex items-stretch gap-1.5">
                    <input type="number" id="date_debut_day" min="1" max="31"
                           value="{{ $debutJour !== '' ? (int)$debutJour : '' }}"
                           placeholder="Jour" required
                           class="w-20 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-center outline-none focus:border-emerald-400 focus:bg-white @error('date_debut') border-rose-400 @enderror">
                    <select id="date_debut_month" required
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('date_debut') border-rose-400 @enderror">
                        <option value="">Mois</option>
                        @foreach($moisLabels as $num => $nom)
                            <option value="{{ $num }}" @selected($debutMois === $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                    <span class="flex items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-500 select-none">
                        {{ $annee }}
                    </span>
                </div>
                @error('date_debut')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
            {{-- Date de fin --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de fin *</label>
                <input type="hidden" name="date_fin" id="date_fin_hidden" value="{{ $oldFin ?: '' }}">
                <div class="flex items-stretch gap-1.5">
                    <input type="number" id="date_fin_day" min="1" max="31"
                           value="{{ $finJour !== '' ? (int)$finJour : '' }}"
                           placeholder="Jour" required
                           class="w-20 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-center outline-none focus:border-emerald-400 focus:bg-white @error('date_fin') border-rose-400 @enderror">
                    <select id="date_fin_month" required
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('date_fin') border-rose-400 @enderror">
                        <option value="">Mois</option>
                        @foreach($moisLabels as $num => $nom)
                            <option value="{{ $num }}" @selected($finMois === $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                    <span class="flex items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-500 select-none">
                        {{ $annee }}
                    </span>
                </div>
                @error('date_fin')<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Durée --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Durée (heures) *</label>
            <div class="relative">
                <input type="number" name="duree_heures" value="{{ old('duree_heures') }}" required min="1" max="9999"
                       placeholder="Ex: 24"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-14 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('duree_heures') border-rose-400 @enderror">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">heures</span>
            </div>
        </div>

        {{-- Formateur --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Formateur
                <span class="ml-1 font-normal normal-case tracking-normal text-slate-400">(optionnel — agent de la Faitière)</span>
            </label>
            <select name="formateur_id"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('formateur_id') border-rose-400 @enderror">
                <option value="">— Aucun formateur —</option>
                @foreach($formateurs as $fm)
                    <option value="{{ $fm->id }}" @selected(old('formateur_id') == $fm->id)>
                        {{ trim($fm->prenom . ' ' . $fm->nom) }}{{ $fm->poste ? ' — ' . $fm->poste : '' }}
                    </option>
                @endforeach
            </select>
            @error('formateur_id')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Attestation --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Attestation
                <span class="ml-1 font-normal normal-case tracking-normal text-slate-400">(optionnelle — PDF ou image, 5 Mo max)</span>
            </label>
            <label id="drop-zone-rh"
                   class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-4 transition hover:border-emerald-400 hover:bg-emerald-50 @error('attestation') border-rose-300 bg-rose-50 @enderror">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm">
                    <i class="fas fa-paperclip text-lg text-emerald-500"></i>
                </div>
                <div class="min-w-0">
                    <p id="rh-file-name" class="truncate text-sm font-semibold text-slate-600">Cliquer pour joindre un fichier…</p>
                    <p class="text-xs text-slate-400">Sera téléchargeable par les participants après validation</p>
                </div>
                <input id="rh-attestation-input" type="file" name="attestation"
                       accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="hidden">
            </label>
            @error('attestation')
                <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-save text-xs"></i> Enregistrer
            </button>
        </div>
    </form>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    // ── Tom Select pour le champ Agents (multi-select) ──────────────────────
    new TomSelect('#select-agent', {
        plugins: ['remove_button'],
        placeholder: '— Rechercher et sélectionner des agents —',
        searchField: ['text'],
        maxOptions: 1000,
        render: {
            option: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            },
            item: function(data, escape) {
                return `<div class="flex items-center gap-1.5 text-xs font-semibold">${escape(data.text)}</div>`;
            },
            no_results: function() {
                return '<div class="no-results" style="padding:0.6rem 1rem;color:#94a3b8;font-size:0.8rem">Aucun agent trouvé</div>';
            }
        }
    });

    // ── Autocomplete Theme de formation ─────────────────────────────────────
    const theme = @json($themesExistants ?? []);
    const input  = document.getElementById('input-theme');
    const list   = document.getElementById('autocomplete-list');

    function highlight(text, query) {
        if (!query) return text;
        const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(re, '<mark>$1</mark>');
    }

    if(input && list) {
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            list.innerHTML = '';
            if (q.length < 2) { list.style.display = 'none'; return; }

            const matches = theme.filter(t => t.toLowerCase().includes(q)).slice(0, 8);
            if (!matches.length) { list.style.display = 'none'; return; }

            matches.forEach(t => {
                const li = document.createElement('li');
                li.innerHTML = highlight(t, this.value.trim());
                li.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    input.value = t;
                    list.style.display = 'none';
                });
                list.appendChild(li);
            });
            list.style.display = 'block';
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target)) list.style.display = 'none';
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') list.style.display = 'none';
        });
    }

    // ── Combinaison jour + mois + année (fixe) → champs hidden ──────────────
    const ANNEE = {{ $anneeEnCours?->annee ?? now()->year }};

    function buildDate(dayInput, monthSelect) {
        if (!dayInput || !monthSelect || !dayInput.value || !monthSelect.value) return '';
        const day   = String(dayInput.value).padStart(2, '0');
        const month = monthSelect.value;
        return `${ANNEE}-${month}-${day}`;
    }

    function syncDebut() {
        const hiddenInput = document.getElementById('date_debut_hidden');
        if(hiddenInput) {
            hiddenInput.value = buildDate(document.getElementById('date_debut_day'), document.getElementById('date_debut_month'));
        }
    }

    function syncFin() {
        const hiddenInput = document.getElementById('date_fin_hidden');
        if(hiddenInput) {
            hiddenInput.value = buildDate(document.getElementById('date_fin_day'), document.getElementById('date_fin_month'));
        }
    }

    function clampDay(input) {
        const v = parseInt(input.value, 10);
        if (!isNaN(v)) input.value = Math.min(31, Math.max(1, v));
    }

    const startDay = document.getElementById('date_debut_day');
    const startMonth = document.getElementById('date_debut_month');
    const endDay = document.getElementById('date_fin_day');
    const endMonth = document.getElementById('date_fin_month');

    if(startDay && startMonth && endDay && endMonth) {
        startDay.addEventListener('input', function() { clampDay(this); syncDebut(); });
        startMonth.addEventListener('change', syncDebut);
        endDay.addEventListener('input', function() { clampDay(this); syncFin(); });
        endMonth.addEventListener('change', syncFin);

        // Initialisation au chargement
        syncDebut();
        syncFin();
    }

    // Sync de sécurité juste avant la soumission
    const form = document.querySelector('form');
    if(form) {
        form.addEventListener('submit', function () {
            syncDebut();
            syncFin();
        });
    }

    // ── Attestation optionnelle ──────────────────────────────────────────────
    const rhInput  = document.getElementById('rh-attestation-input');
    const rhLabel  = document.getElementById('drop-zone-rh');
    const rhNameEl = document.getElementById('rh-file-name');

    if(rhInput && rhLabel && rhNameEl) {
        rhInput.addEventListener('change', () => {
            if (rhInput.files.length) {
                rhNameEl.textContent = rhInput.files[0].name;
                rhLabel.classList.add('border-emerald-500', 'bg-emerald-50');
                rhLabel.classList.remove('border-dashed', 'border-slate-200');
            }
        });
    }
</script>
@endpush