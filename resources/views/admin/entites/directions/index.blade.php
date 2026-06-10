@extends('layouts.app')

@section('title', 'Directions Faîtières | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 px-6 py-8 lg:px-10">
        {{-- Decorative blobs --}}
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-emerald-500/10 blur-3xl"></div>
            <div class="absolute -bottom-12 left-8 h-40 w-40 rounded-full bg-teal-400/10 blur-2xl"></div>
        </div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            {{-- Left: Breadcrumb + Title --}}
            <div>
                <a href="{{ url()->previous() }}"
                   class="mb-3 inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">Administration</span>
                    <i class="fas fa-chevron-right text-[9px] text-slate-600"></i>
                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-400">Unités Centrales</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-white">Directions Faîtières</h1>
                <p class="mt-1 text-sm text-slate-400">Gestion des directions et de leurs responsables</p>
            </div>

            {{-- Right: Stats chip + CTA --}}
            <div class="flex shrink-0 items-center gap-3">
                <div class="rounded-2xl bg-white/10 px-5 py-3 text-center ring-1 ring-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Directions</p>
                    <p class="mt-0.5 text-3xl font-black text-white">{{ $directions->count() }}</p>
                </div>
                <a href="{{ route('admin.entites.directions.create') }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-emerald-900/30 transition hover:bg-emerald-400">
                    <i class="fas fa-plus text-xs"></i> Nouvelle Direction
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-screen-xl px-4 pt-6 lg:px-8 space-y-4">

        {{-- ── Search bar ──────────────────────────────────────────────────── --}}
        <div class="relative max-w-sm">
            <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                <i class="fas fa-search text-xs"></i>
            </div>
            <input id="direction-search" type="text"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 shadow-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                   placeholder="Rechercher une direction..."
                   autocomplete="off">
            <div id="direction-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
        </div>

        {{-- ── Table card ───────────────────────────────────────────────────── --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">

            {{-- Table header --}}
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des directions</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">
                    {{ $directions->count() }} entrée{{ $directions->count() > 1 ? 's' : '' }}
                </span>
            </div>

            @if($directions->isEmpty())
                <div class="px-6 py-20 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-300">
                        <i class="fas fa-sitemap text-2xl"></i>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucune direction trouvée.</p>
                    <a href="{{ route('admin.entites.directions.create') }}"
                       class="mt-3 inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-black text-white hover:bg-emerald-600 transition">
                        <i class="fas fa-plus text-[10px]"></i> Créer la première direction
                    </a>
                </div>
            @else
                <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 z-10">
                            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 w-12">#</th>
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Direction</th>
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Responsable</th>
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Services</th>
                                <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($directions as $direction)
                            @php
                                $directeur    = $direction->directeur;
                                $directeurNom = $directeur ? trim($directeur->prenom . ' ' . $directeur->nom) : null;
                                $initiale     = strtoupper(substr($direction->nom, 0, 1));
                                $colors = [
                                    ['bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
                                    ['bg'=>'bg-blue-100',   'text'=>'text-blue-700'],
                                    ['bg'=>'bg-violet-100', 'text'=>'text-violet-700'],
                                    ['bg'=>'bg-amber-100',  'text'=>'text-amber-700'],
                                    ['bg'=>'bg-rose-100',   'text'=>'text-rose-700'],
                                    ['bg'=>'bg-teal-100',   'text'=>'text-teal-700'],
                                    ['bg'=>'bg-indigo-100', 'text'=>'text-indigo-700'],
                                ];
                                $c = $colors[$loop->index % count($colors)];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors group"
                                data-search-content="{{ strtolower($direction->nom . ' ' . $directeurNom . ' ' . ($directeur->email ?? '')) }}">

                                {{-- # --}}
                                <td class="px-5 py-4">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg {{ $c['bg'] }} {{ $c['text'] }} text-xs font-black">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>

                                {{-- Direction --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $c['bg'] }} {{ $c['text'] }} text-base font-black">
                                            {{ $initiale }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 leading-tight group-hover:text-emerald-600 transition-colors truncate max-w-xs">
                                                {{ $direction->nom }}
                                            </p>
                                            <p class="mt-0.5 text-[10px] text-slate-400 font-medium">ID #{{ $direction->id }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Responsable --}}
                                <td class="px-5 py-4">
                                    @if ($directeur)
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 text-xs font-black">
                                                {{ strtoupper(substr($directeurNom, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-700 text-sm">{{ $directeurNom }}</p>
                                                @if($directeur->email)
                                                    <p class="text-[11px] text-emerald-500 font-medium">{{ $directeur->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-400">
                                            <i class="fas fa-exclamation-circle text-[10px]"></i> Non assigné
                                        </span>
                                    @endif
                                </td>

                                {{-- Services --}}
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('admin.services.index', ['direction_id' => $direction->id, 'source' => 'faitiere']) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 transition hover:bg-emerald-100 hover:text-emerald-700">
                                        <i class="fas fa-layer-group text-[10px]"></i>
                                        {{ $direction->services_count ?? 0 }} service{{ ($direction->services_count ?? 0) > 1 ? 's' : '' }}
                                    </a>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.directions.show', $direction) }}"
                                           title="Voir"
                                           class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600 border border-transparent hover:border-emerald-100">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.directions.edit', $direction) }}"
                                           title="Modifier"
                                           class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 border border-transparent hover:border-blue-100">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.directions.destroy', $direction) }}"
                                              onsubmit="return confirm('Supprimer « {{ addslashes($direction->nom) }} » ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Supprimer"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 border border-transparent hover:border-rose-100">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $directions->count() }} résultat(s)</div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('direction-search');
    const suggestionsBox = document.getElementById('direction-suggestions');
    if (!searchInput || !suggestionsBox) return;

    const rows = Array.from(document.querySelectorAll('tr[data-search-content]'));

    function filter() {
        const q = searchInput.value.trim().toLowerCase();
        rows.forEach(row => {
            row.style.display = (!q || (row.dataset.searchContent || '').includes(q)) ? '' : 'none';
        });
    }

    function hide() { suggestionsBox.innerHTML = ''; suggestionsBox.classList.add('hidden'); }

    function renderSuggestions(query) {
        const q = query.trim().toLowerCase();
        if (q.length < 1) { hide(); return; }
        const matched = [];
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            [1, 2].forEach(i => {
                const txt = (cells[i]?.innerText || '').replace(/\s+/g, ' ').trim();
                if (txt.length >= 2 && txt.toLowerCase().includes(q) && !matched.includes(txt)) matched.push(txt);
            });
        });
        if (!matched.length) { hide(); return; }
        suggestionsBox.innerHTML = matched.slice(0, 6).map(s =>
            `<button type="button" class="block w-full border-b border-slate-100 last:border-0 px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">${s}</button>`
        ).join('');
        suggestionsBox.classList.remove('hidden');
        suggestionsBox.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => { searchInput.value = btn.textContent.trim(); filter(); hide(); });
        });
    }

    searchInput.addEventListener('input',  () => { renderSuggestions(searchInput.value); filter(); });
    searchInput.addEventListener('blur',   () => setTimeout(hide, 150));
    searchInput.addEventListener('focus',  () => { if (searchInput.value.trim()) renderSuggestions(searchInput.value); });
});
</script>
@endpush
