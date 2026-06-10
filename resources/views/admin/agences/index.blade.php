@extends('layouts.app')

@section('title', 'Agences | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="agence-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('agence-status-message')?.remove(), 3000);</script>
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
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Index des agences</h1>
                    <p class="mt-1 text-sm text-slate-400">Liste des agences avec le chef d'agence, la secrétaire, la délégation technique et le directeur de caisse superviseur.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.agences.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter une agence
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
                        <i class="fas fa-building text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Agences</p>
            </div>
            {{-- Par délégation --}}
            @foreach ($stats['par_delegation'] as $i => $delegation)
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </span>
                        <span class="text-3xl font-black">{{ $delegation->agences_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            {{-- Filtres --}}
            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                {{-- Filtre Caisse --}}
                <form method="GET" action="{{ route('admin.agences.index') }}" class="flex items-end gap-2">
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
                        <a href="{{ route('admin.agences.index') }}" class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-500 transition hover:bg-rose-50 hover:text-rose-500" title="Effacer le filtre">
                            <i class="fas fa-times text-[10px]"></i> Effacer
                        </a>
                    @endif
                </form>

                {{-- Recherche texte --}}
                <div class="flex-1">
                    <label for="agence-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <div class="relative mt-1.5">
                        <input
                            id="agence-search"
                            type="text"
                            placeholder="Rechercher par agence, chef, secrétaire, délégation ou superviseur"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                            autocomplete="off"
                        >
                        <div id="agence-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                </div>
            </div>

            @if ($caisseId)
                <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2.5">
                    <i class="fas fa-filter text-xs text-emerald-500"></i>
                    <span class="text-sm text-emerald-700">Filtre actif : <strong>{{ $caisses->firstWhere('id', $caisseId)?->nom }}</strong></span>
                    <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ $agences->count() }} agence(s)</span>
                </div>
            @endif

            @if($agences->isEmpty())
            <div class="px-8 py-16 text-center">
                <i class="fas fa-store text-slate-200 text-5xl mb-4 block"></i>
                <p class="text-sm font-semibold text-slate-400">Aucune agence enregistrée.</p>
            </div>
            @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">#</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Agence</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Chef d'agence</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Secrétaire</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Délégation</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Superviseur (Dir. caisse)</th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($agences as $agence)
                            <tr class="hover:bg-slate-50/60 transition-colors" data-search-content="{{ strtolower(trim($agence->nom.' '.($agence->chef?->prenom ?? '').' '.($agence->chef?->nom ?? '').' '.($agence->chef?->numero_telephone ?? '').' '.($agence->secretaire?->prenom ?? '').' '.($agence->secretaire?->nom ?? '').' '.($agence->delegationTechnique?->region ?? '').' '.($agence->delegationTechnique?->ville ?? '').' '.($agence->caisse?->nom ?? ''))) }}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-xs font-bold text-white shadow-sm">{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="font-semibold text-slate-800">{{ $agence->nom }}</p>
                                    <p class="mt-0.5 text-[11px] uppercase tracking-wider text-slate-400">Structure locale</p>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($agence->chef)
                                        <p class="text-sm font-semibold text-slate-700">{{ $agence->chef->prenom }} {{ $agence->chef->nom }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $agence->chef->numero_telephone }}</p>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($agence->secretaire)
                                        <p class="text-sm font-semibold text-slate-700">{{ $agence->secretaire->prenom }} {{ $agence->secretaire->nom }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $agence->secretaire->numero_telephone }}</p>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($agence->delegationTechnique)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">{{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}</span>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($agence->caisse)
                                        <p class="text-sm text-slate-700">{{ $agence->caisse->nom }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $agence->caisse->directeur_nom }}</p>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.agences.agents.index', $agence) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-600">
                                            <i class="fas fa-users text-[10px]"></i> Agents
                                        </a>
                                        <a href="{{ route('admin.agences.edit', $agence) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.agences.destroy', $agence) }}" onsubmit="return confirm('Supprimer cette agence ?');" class="inline-flex">
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
                <span id="agences-count">{{ $agences->count() }}</span>
                agence{{ $agences->count() > 1 ? 's' : '' }} affichée{{ $agences->count() > 1 ? 's' : '' }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('agence-search');
            var suggestionsBox = document.getElementById('agence-suggestions');
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
                var visible = 0;
                rows.forEach(function (row, i) {
                    var show = q === '' || (row.getAttribute('data-search-content') || '').includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) {
                        visible++;
                        var numEl = row.querySelector('td span');
                        if (numEl) numEl.textContent = visible;
                    }
                });
                var counter = document.getElementById('agences-count');
                if (counter) counter.textContent = visible;
            }

            searchInput.addEventListener('input', function () { render(searchInput.value); filter(); });
            searchInput.addEventListener('blur', function () { setTimeout(hide, 120); });
            searchInput.addEventListener('focus', function () { if (searchInput.value.trim()) render(searchInput.value); });
        });
    </script>
@endpush
