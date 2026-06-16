@extends('layouts.app')

@section('title', 'Caisses | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="caisse-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('caisse-status-message')?.remove(), 3000);</script>
        @endif

        @php
            $gradients = [
                'from-violet-500 to-purple-600',
                'from-blue-500 to-indigo-600',
                'from-amber-400 to-orange-500',
                'from-rose-400 to-pink-500',
                'from-cyan-400 to-teal-500',
            ];
        @endphp

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Les Caisses de la RCPB</h1>
                    <p class="mt-1 text-sm text-slate-400">Liste des caisses avec les coordonnées du directeur, le numéro du secrétariat et la délégation technique.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.caisses.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter une caisse
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-{{ 1 + $stats['par_delegation']->count() }}">
            {{-- Total global --}}
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-university text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Caisses</p>
            </div>
            {{-- Par délégation --}}
            @foreach ($stats['par_delegation'] as $i => $delegation)
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </span>
                        <span class="text-3xl font-black">{{ $delegation->caisses_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Search + Table --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-6">
                <label for="caisse-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative mt-1.5">
                    <input
                        id="caisse-search"
                        type="text"
                        placeholder="Rechercher par caisse, directeur, contact, secrétariat ou délégation"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                        autocomplete="off"
                    >
                    <div id="caisse-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
            </div>

            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">N</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Caisse</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Directeur de caisse</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Contact directeur</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Secrétariat</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Délégation</th>
                            <th class="px-3 py-3 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400">Effectif</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
    @forelse ($caisses as $caisse)
        <tr class="border-b border-slate-50 transition hover:bg-slate-50" 
            data-search-content="{{ strtolower(trim($caisse->nom.' '.($caisse->directeur?->prenom).' '.($caisse->directeur?->nom).' '.($caisse->directeur?->email).' '.$caisse->secretariat_telephone.' '.($caisse->delegationTechnique?->region ?? '').' '.($caisse->delegationTechnique?->ville ?? ''))) }}">
            
            {{-- Numéro d'index --}}
            <td class="whitespace-nowrap px-3 py-3">{{ $loop->iteration }}</td>
            
            {{-- Nom de la Caisse --}}
            <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800">{{ $caisse->nom }}</td>
            
            {{-- Directeur de caisse --}}
            <td class="whitespace-nowrap px-3 py-3 font-medium text-slate-700">
                @if($caisse->directeur)
                    {{ $caisse->directeur->prenom }} {{ $caisse->directeur->nom }}
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        Non assigné
                    </span>
                @endif
            </td>
            
            {{-- Contact directeur (Email) --}}
            <td class="whitespace-nowrap px-3 py-3 text-slate-500">
                @if($caisse->directeur && $caisse->directeur->email)
                    <a href="mailto:{{ $caisse->directeur->email }}" class="text-sky-600 hover:text-sky-700 hover:underline transition">
                        {{ $caisse->directeur->email }}
                    </a>
                @else
                    <span class="text-xs text-slate-400">—</span>
                @endif
            </td>
            
            {{-- Secrétariat (Téléphone) --}}
            <td class="whitespace-nowrap px-3 py-3 text-slate-600 font-medium">
                @if($caisse->secretariat_telephone)
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-400">
                            <i class="fas fa-phone text-[10px]"></i>
                        </span>
                        <span>{{ $caisse->secretaire->numero_telephone }}</span>
                    </div>
                @else
                    {{-- Si vide, on tente d'afficher le téléphone de l'agent secrétaire rattaché --}}
                    @if($caisse->secretaire && $caisse->secretaire->numero_telephone)
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-400">
                                <i class="fas fa-phone text-[10px]"></i>
                            </span>
                            <span>{{ $caisse->secretaire->numero_telephone }}</span>
                        </div>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                @endif
            </td>
            
            {{-- Délégation --}}
            <td class="px-3 py-3">
                @if ($caisse->delegationTechnique)
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        {{ $caisse->delegationTechnique->region }} / {{ $caisse->delegationTechnique->ville }}
                    </span>
                @else
                    <span class="text-xs text-slate-400">—</span>
                @endif
            </td>
            
            {{-- Effectif des agents --}}
           {{-- Effectif des agents (Total Réel) --}}
<td class="whitespace-nowrap px-3 py-3 text-center">
    <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-100/50">
        <i class="fas fa-users text-[10px]"></i>
        {{ $caisse->effectif_reel }} agents
    </span>
</td>
            
            {{-- Actions --}}
            <td class="whitespace-nowrap px-3 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                    <a href="{{ route('admin.caisses.show', $caisse) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Voir les agents"><i class="fas fa-eye text-xs"></i></a>
                    <a href="{{ route('admin.caisses.edit', $caisse) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                    <form method="POST" action="{{ route('admin.caisses.destroy', $caisse) }}" onsubmit="return confirm('Supprimer cette caisse ?');" class="inline-flex">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="py-10 text-center text-sm text-slate-400">
                Aucune caisse enregistrée.
            </td>
        </tr>
    @endforelse
</tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $caisses->count() }} résultat(s)</div>
        </div>
    </div>
</div>

    {{-- Caisse creation modal --}}
    <div id="caisse-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" onclick="if(event.target===this)document.getElementById('caisse-form').classList.add('hidden')">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm pointer-events-none"></div>
        <div class="relative z-10 w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
            <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                <i class="fas fa-times"></i>
            </button>

            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Nouvelle caisse</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter une Caisse</h2>
            </div>

            <form method="POST" action="{{ route('admin.caisses.store') }}" class="space-y-6">
                @csrf

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-map-marker-alt text-emerald-500"></i>
                        Informations de la Caisse
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                            <select id="modal-delegation" name="delegation_technique_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                @foreach ($delegations as $d)
                                    <option value="{{ $d->id }}" {{ (int) old('delegation_technique_id') === $d->id ? 'selected' : '' }}>{{ $d->region }} — {{ $d->ville }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville <span class="text-rose-500">*</span></label>
                            <select id="modal-ville" name="ville_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir d'abord la délégation --</option>
                                @foreach ($villes as $v)
                                    <option value="{{ $v->id }}" data-delegation="{{ $v->delegation_technique_id }}" {{ (int) old('ville_id') === $v->id ? 'selected' : '' }} style="display:none">{{ $v->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom de la caisse <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Caisse Populaire de Koudougou">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Année d'ouverture <span class="text-rose-500">*</span></label>
                            <input type="text" name="annee_ouverture" value="{{ old('annee_ouverture') }}" required maxlength="4" pattern="\d{4}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="2020">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Quartier <span class="text-rose-500">*</span></label>
                            <input type="text" name="quartier" value="{{ old('quartier') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Secteur 5">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="+226 XX XX XX XX">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-tie text-sky-500"></i>
                        Directeur de Caisse
                    </h3>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Choisir le directeur <span class="text-rose-500">*</span></label>
                        <select name="directeur_agent_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Sélectionner un Directeur de Caisse --</option>
                            @foreach($directeurs as $d)
                                <option value="{{ $d->id }}" {{ old('directeur_agent_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->prenom }} {{ $d->nom }}{{ $d->role ? ' — '.$d->role : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($directeurs->isEmpty())
                            <p class="mt-1 text-[10px] text-amber-600">Aucun Directeur de Caisse disponible. Créez d'abord le personnel correspondant.</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-pen text-fuchsia-500"></i>
                        Secrétaire du Directeur
                    </h3>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Choisir la/le secrétaire <span class="text-rose-500">*</span></label>
                        <select name="secretaire_agent_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Sélectionner un(e) Secrétaire de Caisse --</option>
                            @foreach($secretaires as $s)
                                <option value="{{ $s->id }}" {{ old('secretaire_agent_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->prenom }} {{ $s->nom }}{{ $s->role ? ' — '.$s->role : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($secretaires->isEmpty())
                            <p class="mt-1 text-[10px] text-amber-600">Aucun(e) Secrétaire de Caisse disponible.</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-emerald-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fas fa-check"></i>
                        Enregistrer
                    </button>
                    <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('caisse-search');
            var suggestionsBox = document.getElementById('caisse-suggestions');
            if (!searchInput || !suggestionsBox) return;

            var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
            var pool = new Set();
            rows.forEach(function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length < 6) return;
                [cells[1], cells[2], cells[3], cells[4], cells[5]].forEach(function (cell) {
                    var txt = (cell.innerText || '').replace(/\s+/g, ' ').trim();
                    if (txt.length >= 2 && txt !== '-') pool.add(txt);
                });
            });
            var suggestions = Array.from(pool);

            function hide() { suggestionsBox.innerHTML = ''; suggestionsBox.classList.add('hidden'); }

            function render(query) {
                var q = query.trim().toLowerCase();
                if (q.length < 1) { hide(); return; }
                var matched = suggestions.filter(function (s) { return s.toLowerCase().includes(q); }).slice(0, 6);
                if (!matched.length) { hide(); return; }
                suggestionsBox.innerHTML = matched.map(function (s) {
                    return '<button type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">' + s + '</button>';
                }).join('');
                suggestionsBox.classList.remove('hidden');
                suggestionsBox.querySelectorAll('button').forEach(function (btn) {
                    btn.addEventListener('click', function () { searchInput.value = btn.textContent; filter(); hide(); });
                });
            }

            function filter() {
                var q = searchInput.value.trim().toLowerCase();
                rows.forEach(function (row) {
                    row.style.display = q === '' || (row.getAttribute('data-search-content') || '').includes(q) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', function () { render(searchInput.value); filter(); });
            searchInput.addEventListener('blur', function () { setTimeout(hide, 120); });
            searchInput.addEventListener('focus', function () { if (searchInput.value.trim()) render(searchInput.value); });
        });

        // Filtrage des villes par délégation dans le modal
        (function () {
            var delSel  = document.getElementById('modal-delegation');
            var villeSel = document.getElementById('modal-ville');
            if (!delSel || !villeSel) return;

            var allOptions = Array.from(villeSel.querySelectorAll('option[data-delegation]'));

            function filterVilles() {
                var delId = delSel.value;
                var hasMatch = false;
                allOptions.forEach(function (opt) {
                    var show = delId !== '' && opt.dataset.delegation === delId;
                    opt.style.display = show ? '' : 'none';
                    if (show) hasMatch = true;
                });
                // Reset placeholder
                villeSel.options[0].textContent = delId === ''
                    ? '-- Choisir d\'abord la délégation --'
                    : (hasMatch ? '-- Choisir une ville --' : '-- Aucune ville disponible --');
                // Reset selected value if current selection no longer visible
                if (villeSel.value !== '' && villeSel.selectedOptions[0] && villeSel.selectedOptions[0].style.display === 'none') {
                    villeSel.value = '';
                }
            }

            delSel.addEventListener('change', filterVilles);
            // Appel initial (si old() a présélectionné une délégation)
            filterVilles();
        })();
    </script>
@endpush
