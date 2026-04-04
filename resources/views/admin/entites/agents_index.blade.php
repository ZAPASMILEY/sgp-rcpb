@extends('layouts.app')

@section('title', 'Agents Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="mb-4">
        <a href="{{ route('admin.entites.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour Faîtière</span>
        </a>
    </div>
    <div class="max-w-[1500px] mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Agents de la Faîtière</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Directions centrales</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Agents rattachés</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-soft">Retour Faîtière</a>
                <a href="{{ route('admin.agents.create') }}" class="ent-btn ent-btn-primary">Ajouter un agent</a>
            </div>
        </div>

        <section class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
                <div class="flex-1 max-w-xl relative">
                    <label for="agent-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <div class="relative mt-1.5">
                        <input
                            id="agent-search"
                            type="text"
                            placeholder="Rechercher un agent, fonction, service, direction..."
                            class="ent-input w-full"
                            autocomplete="off"
                        >
                        <div id="agent-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">
                    {{ $agents->total() }} agent(s)
                </div>
            </div>

            <div class="ent-table-wrap overflow-x-auto">
                <table class="ent-table text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Agent</th>
                            <th>Fonction</th>
                            <th>Service</th>
                            <th>Direction</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($agents as $agent)
                            <tr data-search-content="{{ strtolower(trim($agent->prenom.' '.$agent->nom.' '.($agent->fonction ?? '').' '.($agent->service?->nom ?? '').' '.($agent->service?->direction?->nom ?? ''))) }}">
                                <td>{{ ($agents->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $agent->prenom }} {{ $agent->nom }}</td>
                                <td>{{ $agent->fonction ?? '-' }}</td>
                                <td>{{ $agent->service?->nom ?? '-' }}</td>
                                <td>{{ $agent->service?->direction?->nom ?? '-' }}</td>
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.agents.show', $agent) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-sky-600" title="Voir">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-amber-600" title="Modifier">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Supprimer cet agent ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-red-200 bg-white text-red-400 shadow-sm transition hover:bg-red-50 hover:text-red-600" title="Supprimer">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">Aucun agent trouvé pour la faîtière.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($agents->hasPages())
                <div class="mt-6">{{ $agents->links() }}</div>
            @endif
        </section>
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
    </script>
@endpush
