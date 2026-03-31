@extends('layouts.app')

@section('title', 'Services | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                            Administration / Référentiel
                            @if(($filters['source'] ?? '') === 'faitiere')
                                / <span class="text-green-600">Faîtière</span>
                            @endif
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                            @if(($filters['source'] ?? '') === 'faitiere')
                                Services de la Faîtière
                            @else
                                Services
                            @endif
                        </h1>
                        <p class="mt-2 text-sm text-slate-600">
                            @if(($filters['source'] ?? '') === 'faitiere')
                                Services rattachés aux directions de la faîtière uniquement.
                            @else
                                Liste de tous les services.
                            @endif
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="status-pill">Total {{ $services->total() }}</span>
                            @if(($filters['source'] ?? '') === 'faitiere')
                                <span class="status-pill bg-green-100 text-green-700 border-green-200">Faîtière</span>
                            @endif
                            @if ($filters['search'] || $filters['direction_id'])
                                <span class="status-pill">Filtres actifs</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('admin.services.index', ($filters['source'] ?? '') === 'faitiere' ? ['source' => 'faitiere'] : []) }}"
                       class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
                        Réinitialiser
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif


            <section class="admin-panel px-6 py-6 lg:px-8">
                <form id="service-filters-form" method="GET" action="{{ route('admin.services.index') }}" class="ent-filters mb-6 grid gap-3 lg:grid-cols-[1.2fr_0.8fr_auto_auto] lg:items-end">
                    <div class="relative space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $filters['search'] }}"
                            placeholder="Service, direction, chef, email"
                            class="ent-input"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-controls="service-search-suggestions"
                        >
                        <div id="service-search-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                    <div class="space-y-2">
                        <label for="direction_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction</label>
                        <select id="direction_id" name="direction_id" class="ent-select">
                            <option value="">Toutes les directions</option>
                            @foreach ($directions as $direction)
                                <option value="{{ $direction->id }}" @selected((string) $filters['direction_id'] === (string) $direction->id)>
                                    {{ $direction->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="ent-btn ent-btn-primary">Filtrer</button>
                    <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="ent-table-wrap overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Service</th>
                                <th>Direction</th>
                                <th>Entite</th>
                                <th>Chef de service</th>
                                <th>Email</th>
                                <th>Telephone</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($services as $service)
                                <tr data-search-content="{{ strtolower(trim($service->nom.' '.$service->direction?->nom.' '.$service->direction?->entite?->nom.' '.$service->chef_prenom.' '.$service->chef_nom.' '.$service->chef_email.' '.$service->chef_telephone)) }}">
                                    <td><p class="ent-identity">{{ ($services->firstItem() ?? 1) + $loop->index }}</p></td>
                                    <td><p class="ent-identity">{{ $service->nom }}</p></td>
                                    <td><p class="ent-identity">{{ $service->direction?->nom }}</p></td>
                                    <td><p class="ent-identity">{{ $service->direction?->entite?->nom }}</p></td>
                                    <td><p class="ent-identity">{{ $service->chef_prenom }} {{ $service->chef_nom }}</p></td>
                                    <td><p class="ent-subtext">{{ $service->chef_email }}</p></td>
                                    <td><p class="ent-identity">{{ $service->chef_telephone }}</p></td>
                                    <td class="whitespace-nowrap">
                                        <div class="ent-actions flex-nowrap">
                                            <a href="{{ route('admin.services.show', $service) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir le service" aria-label="Voir le service">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.services.edit', $service) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier le service" aria-label="Modifier le service">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer le service" aria-label="Supprimer le service">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18M9.75 6.75V5.625A1.875 1.875 0 0 1 11.625 3.75h.75A1.875 1.875 0 0 1 14.25 5.625V6.75m3.75 0V18A2.25 2.25 0 0 1 15.75 20.25h-7.5A2.25 2.25 0 0 1 6 18V6.75h12Zm-8.25 4.5v5.25m4.5-5.25v5.25" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-sm text-slate-500">
                                        Aucun service enregistre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($services->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $services->links() }}
                    </div>
                @endif
            </section>
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
