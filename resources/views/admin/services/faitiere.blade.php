@extends('layouts.app')

@section('title', 'Services de la Faîtière | '.config('app.name', 'SGP-RCPB'))

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

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Services de la Faîtière</h1>
                    <p class="mt-1 text-sm text-slate-400">Liste des services rattachés aux directions de la faîtière avec les coordonnées du chef de service.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Card --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-cogs text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $services->count() }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Services</p>
            </div>
        </div>

        {{-- Search + Table --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-6">
                <label for="sf-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative mt-1.5">
                    <input
                        id="sf-search"
                        type="text"
                        placeholder="Rechercher par service, direction, chef, email ou téléphone"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                        autocomplete="off"
                    >
                    <div id="sf-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">#</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Service</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Direction</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Chef de service</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Email</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Téléphone</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr class="border-b border-slate-50 transition hover:bg-slate-50" data-search-content="{{ strtolower(trim($service->nom.' '.($service->direction?->nom ?? '').' '.$service->chef_prenom.' '.$service->chef_nom.' '.$service->chef_email.' '.$service->chef_telephone)) }}">
                                <td class="whitespace-nowrap px-3 py-3">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-800">{{ $service->nom }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $service->direction?->nom ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $service->chef_prenom }} {{ $service->chef_nom }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $service->chef_email }}</td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $service->chef_telephone }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.services.show', $service) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Voir"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?');" class="inline-flex">
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
                                    Aucun service trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var searchInput = document.getElementById('sf-search');
            var suggestionsBox = document.getElementById('sf-suggestions');
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
