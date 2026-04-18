@extends('layouts.app')

@section('title', 'Agents | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="agent-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('agent-status-message')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Agents</h1>
                    <p class="mt-1 text-sm text-slate-400">Gestion centralisée des agents par structure organisationnelle.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="document.getElementById('agent-create-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter un agent
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        @php
            $gradients = [
                'from-violet-500 to-purple-600',
                'from-blue-500 to-indigo-600',
                'from-amber-400 to-orange-500',
                'from-rose-400 to-pink-500',
                'from-cyan-400 to-teal-500',
            ];
        @endphp
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-{{ 1 + $stats['par_delegation']->count() }}">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-users text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Agents</p>
            </div>
            @foreach ($stats['par_delegation'] as $i => $delegation)
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </span>
                        <span class="text-3xl font-black">{{ $delegation->agents_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        @php
            $tabs = [
                'faitiere'    => ['label' => 'Faîtière',     'icon' => 'fa-building'],
                'delegations' => ['label' => 'Délégations',  'icon' => 'fa-sitemap'],
                'caisses'     => ['label' => 'Caisses',      'icon' => 'fa-university'],
                'agences'     => ['label' => 'Agences',      'icon' => 'fa-building-columns'],
                'guichets'    => ['label' => 'Guichets',     'icon' => 'fa-window-maximize'],
            ];
        @endphp
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            {{-- Tab navigation --}}
            <div class="flex flex-wrap gap-1 border-b border-slate-100 pb-4 mb-6">
                @foreach ($tabs as $key => $t)
                    <a href="{{ route('admin.agents.index', ['tab' => $key]) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition
                              {{ $tab === $key ? 'bg-blue-500 text-white shadow-sm' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600' }}">
                        <i class="fas {{ $t['icon'] }} text-xs {{ $tab === $key ? 'text-white' : 'text-slate-300' }}"></i>
                        {{ $t['label'] }}
                        <span class="ml-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-black
                                     {{ $tab === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-400' }}">
                            {{ $counts[$key] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="mb-6">
                <label for="agent-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative mt-1.5">
                    <input
                        id="agent-search"
                        type="text"
                        placeholder="Rechercher par nom, prénom, fonction, mail ou région"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400"
                        autocomplete="off"
                    >
                    <div id="agent-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">#</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nom</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Prénom</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Fonction</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Mail</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Région</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            @php
                                $region = $agent->delegationTechnique?->region
                                    ?? $agent->agence?->delegationTechnique?->region
                                    ?? ($agent->service?->direction?->delegationTechnique?->region
                                        ? $agent->service->direction->delegationTechnique->region
                                        : (($agent->service?->direction && $agent->service->direction->delegation_technique_id === null) ? 'Faîtière' : '-'));
                            @endphp
                            <tr class="border-b border-slate-50 transition hover:bg-slate-50" data-search-content="{{ strtolower(trim($agent->nom.' '.$agent->prenom.' '.$agent->fonction.' '.$agent->email.' '.$region)) }}">
                                <td class="whitespace-nowrap px-3 py-3">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800">{{ $agent->nom }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $agent->prenom }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $agent->fonction }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-slate-500">{{ $agent->email }}</td>
                                <td class="px-3 py-3">
                                    @if ($region !== '-')
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $region }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.agents.show', $agent) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Voir"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.agents.edit', $agent) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Supprimer cet agent ?');" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-sm text-slate-400">
                                    Aucun agent enregistré pour cette catégorie.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    {{-- Agent creation modal --}}
    <div id="agent-create-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('agent-create-modal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
            <button type="button" onclick="document.getElementById('agent-create-modal').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                <i class="fas fa-times"></i>
            </button>

            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Nouvel agent</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter un Agent</h2>
            </div>

            <form method="POST" action="{{ route('admin.agents.store') }}" class="space-y-6">
                @csrf

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-briefcase text-emerald-500"></i>
                        Affectation
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Service <span class="text-rose-500">*</span></label>
                            <select name="service_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                @foreach ($services as $svc)
                                    <option value="{{ $svc->id }}" {{ (int) old('service_id') === $svc->id ? 'selected' : '' }}>{{ $svc->nom }} {{ $svc->direction ? '('.$svc->direction->nom.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Fonction <span class="text-rose-500">*</span></label>
                            <input type="text" name="fonction" value="{{ old('fonction') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Caissier, Comptable...">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user text-emerald-500"></i>
                        Identité de l'agent
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                            <select name="sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                <option value="homme" {{ old('sexe') === 'homme' ? 'selected' : '' }}>Homme</option>
                                <option value="femme" {{ old('sexe') === 'femme' ? 'selected' : '' }}>Femme</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                            <input type="text" name="numero_telephone" value="{{ old('numero_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Date début fonction <span class="text-rose-500">*</span></label>
                            <input type="date" name="date_debut_fonction" value="{{ old('date_debut_fonction') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-emerald-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fas fa-check"></i>
                        Enregistrer
                    </button>
                    <button type="button" onclick="document.getElementById('agent-create-modal').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
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
            var searchInput = document.getElementById('agent-search');
            var suggestionsBox = document.getElementById('agent-suggestions');
            if (!searchInput || !suggestionsBox) return;

            var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
            var pool = new Set();
            rows.forEach(function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length < 5) return;
                [cells[1], cells[2], cells[3], cells[4]].forEach(function (cell) {
                    var txt = (cell.innerText || '').replace(/\s+/g, ' ').trim();
                    if (txt.length >= 2 && txt !== '-') pool.add(txt);
                });
            });
            var terms = Array.from(pool);

            function hideSuggestions() {
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('hidden');
            }

            function filterRows(query) {
                var q = query.trim().toLowerCase();
                rows.forEach(function (row) {
                    row.style.display = q === '' || row.dataset.searchContent.indexOf(q) !== -1 ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', function () {
                var q = searchInput.value.trim().toLowerCase();
                filterRows(q);
                if (q.length < 1) { hideSuggestions(); return; }
                var matches = terms.filter(function (t) { return t.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
                if (matches.length === 0) { hideSuggestions(); return; }
                suggestionsBox.innerHTML = matches.map(function (m) {
                    return '<button type="button" class="flex w-full items-center px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">' + m + '</button>';
                }).join('');
                suggestionsBox.querySelectorAll('button').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        searchInput.value = btn.textContent.trim();
                        filterRows(searchInput.value);
                        hideSuggestions();
                    });
                });
                suggestionsBox.classList.remove('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!suggestionsBox.contains(e.target) && e.target !== searchInput) hideSuggestions();
            });
        });
    </script>
@endpush
