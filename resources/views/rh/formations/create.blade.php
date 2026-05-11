@extends('layouts.rh')

@section('title', 'Nouvelle formation | SGP-RCPB')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
<style>
    /* ── Tom Select custom theme ── */
    .ts-wrapper.single .ts-control {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        color: #1e293b;
        box-shadow: none;
        cursor: pointer;
    }
    .ts-wrapper.single.focus .ts-control,
    .ts-wrapper.single .ts-control:focus {
        border-color: #34d399;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(52,211,153,0.15);
    }
    .ts-wrapper .ts-control input { color: #1e293b; font-size: 0.875rem; }
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
            <a href="{{ route('rh.formations.index') }}"
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

    <form method="POST" action="{{ route('rh.formations.store') }}" class="admin-panel px-6 py-6 flex flex-col gap-5">
        @csrf

        {{-- Agent (searchable) --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Agent *
            </label>
            <select id="select-agent" name="agent_id" required
                    class="@error('agent_id') border-rose-400 @enderror">
                <option value="">— Sélectionner un agent —</option>
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}"
                            data-search="{{ strtolower($ag->prenom . ' ' . $ag->nom . ' ' . $ag->fonction) }}"
                            @selected(old('agent_id', $preselectedAgentId) == $ag->id)>
                        {{ trim($ag->prenom . ' ' . $ag->nom) }} — {{ $ag->fonction }}
                    </option>
                @endforeach
            </select>
            @error('agent_id')
                <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Titre (avec autocomplete) --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Titre de la formation *
            </label>
            <div class="autocomplete-wrap">
                <input type="text" id="input-titre" name="titre" value="{{ old('titre') }}"
                       required maxlength="255" autocomplete="off"
                       placeholder="Ex: Formation leadership et management d'équipe"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('titre') border-rose-400 @enderror">
                <ul id="autocomplete-list" class="autocomplete-list"></ul>
            </div>
            @error('titre')
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

        {{-- Dates --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de début *</label>
                <input type="date" name="date_debut" value="{{ old('date_debut') }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('date_debut') border-rose-400 @enderror">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de fin *</label>
                <input type="date" name="date_fin" value="{{ old('date_fin') }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white @error('date_fin') border-rose-400 @enderror">
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

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route('rh.formations.index') }}"
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    // ── Tom Select pour le champ Agent ──────────────────────────────────────
    new TomSelect('#select-agent', {
        placeholder: '— Rechercher un agent —',
        searchField: ['text'],
        maxOptions: 100,
        render: {
            option: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            },
            item: function(data, escape) {
                return `<div>${escape(data.text)}</div>`;
            },
            no_results: function() {
                return '<div class="no-results" style="padding:0.6rem 1rem;color:#94a3b8;font-size:0.8rem">Aucun agent trouvé</div>';
            }
        }
    });

    // ── Autocomplete Titre de formation ─────────────────────────────────────
    const titres = @json($titresExistants);
    const input  = document.getElementById('input-titre');
    const list   = document.getElementById('autocomplete-list');

    function highlight(text, query) {
        if (!query) return text;
        const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(re, '<mark>$1</mark>');
    }

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        list.innerHTML = '';
        if (q.length < 2) { list.style.display = 'none'; return; }

        const matches = titres.filter(t => t.toLowerCase().includes(q)).slice(0, 8);
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
</script>
@endpush
