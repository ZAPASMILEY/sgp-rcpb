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
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <form id="service-filters-form" method="GET" action="{{ route('admin.services.index') }}" class="mb-6 grid gap-3 lg:grid-cols-[1.2fr_0.8fr_auto] lg:items-end">
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
                        placeholder="Service, direction, chef, email"
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

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">#</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Service</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Direction</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Entité</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Chef de service</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Email</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Téléphone</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr class="border-b border-slate-50 transition hover:bg-slate-50" data-search-content="{{ strtolower(trim($service->nom.' '.$service->direction?->nom.' '.$service->direction?->entite?->nom.' '.$service->chef?->prenom.' '.$service->chef?->nom.' '.$service->chef?->email.' '.$service->chef?->numero_telephone)) }}">
                                <td class="whitespace-nowrap px-3 py-3">{{ ($services->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="px-3 py-3 font-semibold text-slate-800">{{ $service->nom }}</td>
                                <td class="px-3 py-3">{{ $service->direction?->nom }}</td>
                                <td class="px-3 py-3">{{ $service->direction?->entite?->nom }}</td>
                                <td class="px-3 py-3 font-semibold text-slate-700">
                                    @if($service->chef)
                                        {{ $service->chef->prenom }} {{ $service->chef->nom }}
                                    @else
                                        <span class="text-slate-300 italic text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-slate-500">{{ $service->chef?->email ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $service->chef?->numero_telephone ?? '—' }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.services.show', $service) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Voir"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
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
                                    Aucun service enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($services->hasPages())
                <div class="mt-6 border-t border-slate-100 pt-4">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('search');
            var directionSelect = document.getElementById('direction_id');
            var filtersForm = document.getElementById('service-filters-form');
            var suggestionsBox = document.getElementById('service-search-suggestions');

            if (!searchInput || !suggestionsBox) {
                return;
            }

            var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
            var suggestionPool = new Set();

            rows.forEach(function (row) {
                var cells = row.querySelectorAll('td');
                if (cells.length < 7) {
                    return;
                }

                [cells[1].innerText, cells[2].innerText, cells[3].innerText, cells[4].innerText, cells[5].innerText, cells[6].innerText].forEach(function (value) {
                    var cleaned = value.replace(/\s+/g, ' ').trim();
                    if (cleaned.length >= 2) {
                        suggestionPool.add(cleaned);
                    }
                });
            });

            var suggestions = Array.from(suggestionPool);

            function hideSuggestions() {
                suggestionsBox.innerHTML = '';
                suggestionsBox.classList.add('hidden');
            }

            function renderSuggestions(query) {
                var q = query.trim().toLowerCase();

                if (q.length < 1) {
                    hideSuggestions();
                    return;
                }

                var matched = suggestions.filter(function (item) {
                    return item.toLowerCase().includes(q);
                }).slice(0, 6);

                if (matched.length === 0) {
                    hideSuggestions();
                    return;
                }

                suggestionsBox.innerHTML = matched.map(function (item) {
                    return '<button type="button" class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">' + item + '</button>';
                }).join('');

                suggestionsBox.classList.remove('hidden');

                suggestionsBox.querySelectorAll('button').forEach(function (button) {
                    button.addEventListener('click', function () {
                        searchInput.value = button.textContent || '';
                        applyFilter();
                        hideSuggestions();
                    });
                });
            }

            function applyFilter() {
                var query = searchInput.value.trim().toLowerCase();

                rows.forEach(function (row) {
                    var content = row.getAttribute('data-search-content') || '';
                    row.style.display = query === '' || content.includes(query) ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', function () {
                renderSuggestions(searchInput.value);
                applyFilter();
            });

            searchInput.addEventListener('blur', function () {
                window.setTimeout(hideSuggestions, 120);
            });

            searchInput.addEventListener('focus', function () {
                if (searchInput.value.trim() !== '') {
                    renderSuggestions(searchInput.value);
                }
            });

            if (directionSelect && filtersForm) {
                directionSelect.addEventListener('change', function () {
                    filtersForm.submit();
                });
            }

            applyFilter();
        });
    </script>
@endpush
