@extends('layouts.app')

@section('title', 'Services | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="service-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('service-status-message')?.remove(), 3000);</script>
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
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">
                        @if(($filters['source'] ?? '') === 'faitiere')
                            Services de la Faîtière
                        @else
                            Services
                        @endif
                    </h1>
                    <p class="mt-1 text-sm text-slate-400">
                        @if(($filters['source'] ?? '') === 'faitiere')
                            Services rattachés aux directions de la faîtière uniquement.
                        @else
                            Liste de tous les services.
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if(($filters['source'] ?? '') === 'faitiere')
                        <span class="inline-flex items-center rounded-xl bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600">Faîtière</span>
                    @endif
                    <a href="{{ route('admin.services.index', ($filters['source'] ?? '') === 'faitiere' ? ['source' => 'faitiere'] : []) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fas fa-rotate-right text-xs"></i> Réinitialiser
                    </a>
                    <a href="{{ route('admin.services.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter
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
                        <i class="fas fa-briefcase text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Services</p>
            </div>
            {{-- Par délégation --}}
            @foreach ($stats['par_delegation'] as $i => $delegation)
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                            <i class="fas fa-map-marker-alt text-sm"></i>
                        </span>
                        <span class="text-3xl font-black">{{ $delegation->services_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filters + Table --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <form id="service-filters-form" method="GET" action="{{ route('admin.services.index') }}" class="mb-0 grid gap-3 p-5 lg:grid-cols-[1.2fr_0.8fr_auto] lg:items-end border-b border-slate-100">
                @if(($filters['source'] ?? '') === 'faitiere')
                    <input type="hidden" name="source" value="faitiere">
                @endif
                <div class="relative space-y-1.5">
                    <label for="search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] }}"
                        placeholder="Service, direction, caisse, delegation, chef, email"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                        autocomplete="off"
                        aria-autocomplete="list"
                        aria-controls="service-search-suggestions"
                    >
                    <div id="service-search-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
                <div class="space-y-1.5">
                    <label for="direction_id" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Direction</label>
                    <select id="direction_id" name="direction_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">Toutes les directions</option>
                        @foreach ($directions as $direction)
                            <option value="{{ $direction->id }}" @selected((string) $filters['direction_id'] === (string) $direction->id)>
                                {{ $direction->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-search text-xs"></i> Filtrer
                </button>
            </form>

            @if($services->isEmpty())
            <div class="px-8 py-16 text-center">
                <i class="fas fa-briefcase text-slate-200 text-5xl mb-4 block"></i>
                <p class="text-sm font-semibold text-slate-400">Aucun service enregistré.</p>
            </div>
            @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">#</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Nom</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Structure</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Type d'entité</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Chef de service</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Email</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Téléphone</th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($services as $service)
                            @php
                                $structureNom = $service->direction?->nom ?? ($service->delegationTechnique?->region ?? $service->caisse?->nom ?? '');
                                $typeEntite   = $service->direction ? 'Direction Faîtière' : ($service->delegationTechnique ? 'Délégation' : ($service->caisse ? 'Caisse' : ''));
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors" data-search-content="{{ strtolower(trim($service->nom.' '.$structureNom.' '.$typeEntite.' '.$service->chef?->prenom.' '.$service->chef?->nom.' '.$service->chef?->email.' '.$service->chef?->numero_telephone)) }}">
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $service->nom }}</td>
                                <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap max-w-[200px] truncate" title="{{ $structureNom }}">
                                    @if($service->direction)
                                        {{ $service->direction->nom }}
                                    @elseif($service->delegationTechnique)
                                        {{ $service->delegationTechnique->region }}@if($service->delegationTechnique->ville) – {{ $service->delegationTechnique->ville }}@endif
                                    @elseif($service->caisse)
                                        {{ $service->caisse->nom }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($service->direction)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-bold text-blue-700">
                                            <i class="fas fa-building text-[9px]"></i> Direction Faîtière
                                        </span>
                                    @elseif($service->delegationTechnique)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 px-2.5 py-0.5 text-[11px] font-bold text-purple-700">
                                            <i class="fas fa-map-marker-alt text-[9px]"></i> Délégation
                                        </span>
                                    @elseif($service->caisse)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-bold text-amber-700">
                                            <i class="fas fa-wallet text-[9px]"></i> Caisse
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-700 whitespace-nowrap">
                                    @if($service->chef)
                                        {{ $service->chef->prenom }} {{ $service->chef->nom }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $service->chef?->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $service->chef?->numero_telephone ?? '—' }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.services.show', $service) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Voir"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
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
                <span id="services-count">{{ $services->count() }}</span>
                service{{ $services->count() > 1 ? 's' : '' }} affiché{{ $services->count() > 1 ? 's' : '' }}
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput     = document.getElementById('search');
        var directionSelect = document.getElementById('direction_id');
        var filtersForm     = document.getElementById('service-filters-form');
        var suggestionsBox  = document.getElementById('service-search-suggestions');
        var rows            = Array.from(document.querySelectorAll('tr[data-search-content]'));
        var suggestionPool  = new Set();

        rows.forEach(function (row) {
            var cells = row.querySelectorAll('td');
            [1,2,3,4,5,6].forEach(function(i){
                if (!cells[i]) return;
                var v = (cells[i].innerText || '').replace(/\s+/g,' ').trim();
                if (v.length >= 2) suggestionPool.add(v);
            });
        });

        function hideSuggestions() {
            suggestionsBox.innerHTML = '';
            suggestionsBox.classList.add('hidden');
        }

        function renderSuggestions(query) {
            var q = query.trim().toLowerCase();
            if (q.length < 1) { hideSuggestions(); return; }
            var matched = Array.from(suggestionPool).filter(function(item){
                return item.toLowerCase().includes(q);
            }).slice(0, 6);
            if (!matched.length) { hideSuggestions(); return; }
            suggestionsBox.innerHTML = matched.map(function(item){
                return '<button type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">' + item + '</button>';
            }).join('');
            suggestionsBox.classList.remove('hidden');
            suggestionsBox.querySelectorAll('button').forEach(function(btn){
                btn.addEventListener('click', function(){
                    searchInput.value = btn.textContent || '';
                    applyFilter(); hideSuggestions();
                });
            });
        }

        function applyFilter() {
            var q = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var visible = 0;
            rows.forEach(function(row, i){
                var c = row.getAttribute('data-search-content') || '';
                var show = q === '' || c.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) {
                    visible++;
                    var td = row.querySelector('td');
                    if (td) td.textContent = visible;
                }
            });
            var counter = document.getElementById('services-count');
            if (counter) counter.textContent = visible;
        }

        if (searchInput) {
            searchInput.addEventListener('input', function(){ renderSuggestions(searchInput.value); applyFilter(); });
            searchInput.addEventListener('blur',  function(){ window.setTimeout(hideSuggestions, 120); });
            searchInput.addEventListener('focus', function(){ if (searchInput.value.trim()) renderSuggestions(searchInput.value); });
        }

        if (directionSelect && filtersForm) {
            directionSelect.addEventListener('change', function(){ filtersForm.submit(); });
        }

        applyFilter();
    });
    </script>
@endpush