@extends('layouts.app')

@section('title', 'Agents | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .agents-page .agents-hero {
            border-radius: 28px;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background:
                radial-gradient(circle at 12% 8%, rgba(16, 185, 129, 0.16), transparent 40%),
                radial-gradient(circle at 92% 0%, rgba(59, 130, 246, 0.14), transparent 36%),
                linear-gradient(180deg, rgba(22, 189, 55, 0.96), rgba(212, 205, 4, 0.95));
            box-shadow: 0 20px 44px rgba(15, 23, 42, 0.12);
        }

        .agents-page .agents-kpi {
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: rgba(255, 255, 255, 0.88);
            padding: 0.75rem 0.9rem;
        }

        .agents-page .agents-kpi-label {
            font-size: 0.66rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }

        .agents-page .agents-kpi-value {
            margin-top: 0.2rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .agents-page .agents-filters {
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(243, 248, 255, 0.94));
            border-color: rgba(186, 198, 216, 0.72);
        }

        .agents-page .agents-table {
            border-radius: 20px;
            border-color: rgba(186, 198, 216, 0.72);
            box-shadow: 0 10px 30px rgba(30, 41, 59, 0.08) inset;
        }

        .agents-page .agents-table .ent-table thead th {
            background: linear-gradient(180deg, #f6f8fd 0%, #eef3fb 100%);
            border-bottom-color: rgba(190, 201, 220, 0.85);
        }
    </style>
@endpush

@section('content')
    <div class="agents-page admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="agents-hero admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration / Referentiel</p>
                            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Agents</h1>
                            <p class="mt-2 text-sm text-slate-600">Gestion centralisee des agents, services et contacts.</p>
                        </div>
                        <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
                            Reinitialiser
                        </a>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:max-w-md">
                        <article class="agents-kpi">
                            <p class="agents-kpi-label">Total agents</p>
                            <p class="agents-kpi-value">{{ $agents->total() }}</p>
                        </article>
                        <article class="agents-kpi">
                            <p class="agents-kpi-label">Etat recherche</p>
                            <p class="agents-kpi-value">{{ $filters['search'] ? 'Filtres actifs' : 'Tous les agents' }}</p>
                        </article>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            

            <section class="admin-panel px-6 py-6 lg:px-8">
                <form id="agent-filters-form" method="GET" action="{{ route('admin.agents.index') }}" class="agents-filters ent-filters mb-6 grid gap-3 lg:grid-cols-[1.2fr_auto_auto] lg:items-end">
                    <div class="relative space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $filters['search'] }}"
                            placeholder="Nom, prenom, fonction, numero, mail"
                            class="ent-input"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-controls="agent-search-suggestions"
                        >
                        <div id="agent-search-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                    <button type="submit" class="ent-btn ent-btn-primary">Filtrer</button>
                    <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="agents-table ent-table-wrap overflow-x-auto">
                    <table class="ent-table ent-table--stack text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Photo</th>
                                <th>Nom</th>
                                <th>Prenom</th>
                                <th>Sexe</th>
                                <th>Service</th>
                                <th>Fonction</th>
                                <th>Debut fonction</th>
                                <th>Numero</th>
                                <th>Mail</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agents as $agent)
                                <tr data-search-content="{{ strtolower(trim($agent->nom.' '.$agent->prenom.' '.($agent->sexe ?? '').' '.($agent->service?->nom ?? '').' '.$agent->fonction.' '.$agent->numero_telephone.' '.$agent->email)) }}">
                                    <td data-label="#"><p class="ent-identity">{{ ($agents->firstItem() ?? 1) + $loop->index }}</p></td>
                                    <td data-label="Photo">
                                        @if ($agent->photo_path)
                                            <img src="{{ Storage::url($agent->photo_path) }}" alt="Photo de {{ $agent->prenom }} {{ $agent->nom }}" class="h-11 w-11 rounded-xl object-cover ring-1 ring-slate-200">
                                        @else
                                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-xs font-semibold uppercase tracking-[0.16em] text-slate-500 ring-1 ring-slate-200">
                                                {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td data-label="Nom"><p class="ent-identity">{{ $agent->nom }}</p></td>
                                    <td data-label="Prenom"><p class="ent-identity">{{ $agent->prenom }}</p></td>
                                    <td data-label="Sexe"><p class="ent-identity">{{ ucfirst($agent->sexe ?? '-') }}</p></td>
                                    <td data-label="Service"><p class="ent-identity">{{ $agent->service?->nom ?? '-' }}</p></td>
                                    <td data-label="Fonction"><p class="ent-identity">{{ $agent->fonction }}</p></td>
                                    <td data-label="Debut"><p class="ent-subtext">{{ optional($agent->date_debut_fonction)->format('d/m/Y') ?: '-' }}</p></td>
                                    <td data-label="Numero"><p class="ent-identity">{{ $agent->numero_telephone }}</p></td>
                                    <td data-label="Mail"><p class="ent-subtext">{{ $agent->email }}</p></td>
                                    <td data-label="Actions" class="whitespace-nowrap">
                                        <div class="ent-actions flex-nowrap">
                                            <a href="{{ route('admin.agents.show', $agent) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir l'agent" aria-label="Voir l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.agents.edit', $agent) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier l'agent" aria-label="Modifier l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Supprimer cet agent ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer l'agent" aria-label="Supprimer l'agent">
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
                                    <td colspan="11" class="py-10 text-center text-sm text-slate-500">
                                        Aucun agent enregistre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($agents->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $agents->links() }}
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
            var suggestions = document.getElementById('agent-search-suggestions');
            var rows = Array.from(document.querySelectorAll('tbody tr[data-search-content]'));

            if (!searchInput || !suggestions || rows.length === 0) {
                return;
            }

            function hideSuggestions() {
                suggestions.innerHTML = '';
                suggestions.classList.add('hidden');
            }

            function filterRows(query) {
                var value = query.trim().toLowerCase();

                rows.forEach(function (row) {
                    row.style.display = value === '' || row.dataset.searchContent.indexOf(value) !== -1 ? '' : 'none';
                });
            }

            function renderSuggestions(query) {
                var value = query.trim().toLowerCase();

                if (value === '') {
                    hideSuggestions();
                    return;
                }

                var matches = rows
                    .filter(function (row) {
                        return row.dataset.searchContent.indexOf(value) !== -1;
                    })
                    .slice(0, 5)
                    .map(function (row) {
                        return row.dataset.searchContent;
                    });

                if (matches.length === 0) {
                    hideSuggestions();
                    return;
                }

                suggestions.innerHTML = matches.map(function (match) {
                    return '<button type="button" class="flex w-full items-center px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">' + match + '</button>';
                }).join('');

                suggestions.querySelectorAll('button').forEach(function (button) {
                    button.addEventListener('click', function () {
                        searchInput.value = button.textContent.trim();
                        filterRows(searchInput.value);
                        hideSuggestions();
                    });
                });

                suggestions.classList.remove('hidden');
            }

            searchInput.addEventListener('input', function () {
                filterRows(searchInput.value);
                renderSuggestions(searchInput.value);
            });

            document.addEventListener('click', function (event) {
                if (!suggestions.contains(event.target) && event.target !== searchInput) {
                    hideSuggestions();
                }
            });
        });
    </script>
@endpush
