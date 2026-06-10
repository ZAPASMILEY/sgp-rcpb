@extends('layouts.app')

@section('title', 'Guichets | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="guichet-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('guichet-status-message')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Index des guichets</h1>
                    <p class="mt-1 text-sm text-slate-400">Gestion des guichets du réseau RCPB.</p>
                </div>
                <a href="{{ route('admin.guichets.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">
                    <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter un guichet
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-{{ 1 + $stats['par_delegation']->count() }}">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20"><i class="fas fa-window-maximize"></i></span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Guichets</p>
            </div>
            @foreach ($stats['par_delegation'] as $i => $delegation)
                @php $gradients = ['from-violet-500 to-purple-600', 'from-blue-500 to-indigo-600', 'from-amber-400 to-orange-500']; @endphp
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20"><i class="fas fa-map-marker-alt"></i></span>
                        <span class="text-3xl font-black">{{ $delegation->guichets_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            {{-- Filtres --}}
            <div class="mb-0 flex flex-col gap-3 p-5 border-b border-slate-100 sm:flex-row sm:items-end">
                {{-- Filtre Caisse --}}
                <form method="GET" action="{{ route('admin.guichets.index') }}" class="flex items-end gap-2">
                    <div class="flex-1 sm:w-64">
                        <label for="caisse-filter" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filtrer par caisse</label>
                        <select
                            id="caisse-filter"
                            name="caisse_id"
                            onchange="this.form.submit()"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                        >
                            <option value="">Toutes les caisses</option>
                            @foreach ($caisses as $caisse)
                                <option value="{{ $caisse->id }}" @selected($caisseId == $caisse->id)>{{ $caisse->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($caisseId)
                        <a href="{{ route('admin.guichets.index') }}" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-500 transition hover:bg-rose-50 hover:text-rose-500" title="Effacer le filtre">
                            <i class="fas fa-times text-[10px]"></i> Effacer
                        </a>
                    @endif
                </form>

                {{-- Recherche texte --}}
                <div class="flex-1">
                    <label for="guichet-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <div class="relative mt-1.5">
                        <input
                            id="guichet-search"
                            type="text"
                            placeholder="Rechercher un guichet, un chef, une agence ou une caisse..."
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                            autocomplete="off"
                        >
                        <div id="guichet-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                </div>
            </div>

            @if ($caisseId)
                <div class="mx-5 mb-4 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2.5">
                    <i class="fas fa-filter text-xs text-emerald-500"></i>
                    <span class="text-sm text-emerald-700">Filtre actif : <strong>{{ $caisses->firstWhere('id', $caisseId)?->nom }}</strong></span>
                    <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $guichets->count() }} guichet(s)</span>
                </div>
            @endif

            @if($guichets->isEmpty())
            <div class="px-8 py-16 text-center">
                <i class="fas fa-window-maximize text-slate-200 text-5xl mb-4 block"></i>
                <p class="text-sm font-semibold text-slate-400">Aucun guichet enregistré.</p>
            </div>
            @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">#</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Guichet</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Chef de guichet</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Agence</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Caisse</th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($guichets as $guichet)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                data-search-content="{{ strtolower($guichet->nom.' '.($guichet->chef?->prenom ?? '').' '.($guichet->chef?->nom ?? '').' '.($guichet->agence?->nom ?? '').' '.($guichet->agence?->caisse?->nom ?? '')) }}">
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $guichet->nom }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($guichet->chef)
                                        <p class="text-sm font-semibold text-slate-700">{{ $guichet->chef->prenom }} {{ $guichet->chef->nom }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $guichet->chef->numero_telephone }}</p>
                                    @else
                                        <span class="text-slate-300 text-xs">Non assigné</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-sm text-slate-700">{{ $guichet->agence?->nom ?? '—' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $guichet->agence?->delegationTechnique?->region }}</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($guichet->agence?->caisse)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">{{ $guichet->agence->caisse->nom }}</span>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('admin.guichets.agents.index', $guichet) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600">
                                            <i class="fas fa-users text-[10px]"></i> Agents
                                        </a>
                                        <a href="{{ route('admin.guichets.edit', $guichet) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form action="{{ route('admin.guichets.destroy', $guichet) }}" method="POST" onsubmit="return confirm('Supprimer ce guichet ?')" class="inline-flex">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">
                <span id="guichets-count">{{ $guichets->count() }}</span>
                guichet{{ $guichets->count() > 1 ? 's' : '' }} affiché{{ $guichets->count() > 1 ? 's' : '' }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('guichet-search');
            var suggestionsBox = document.getElementById('guichet-suggestions');
            if (!searchInput || !suggestionsBox) return;

            var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
            var pool = new Set();
            rows.forEach(function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length < 4) return;
                [cells[0], cells[1], cells[2], cells[3]].forEach(function (cell) {
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
                var visible = 0;
                rows.forEach(function (row, i) {
                    var show = q === '' || (row.getAttribute('data-search-content') || '').includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) {
                        visible++;
                        var numEl = row.querySelector('td:first-child');
                        if (numEl) numEl.textContent = visible;
                    }
                });
                var counter = document.getElementById('guichets-count');
                if (counter) counter.textContent = visible;
            }

            searchInput.addEventListener('input', function () { render(searchInput.value); filter(); });
            searchInput.addEventListener('blur', function () { setTimeout(hide, 120); });
            searchInput.addEventListener('focus', function () { if (searchInput.value.trim()) render(searchInput.value); });
        });
    </script>
@endpush