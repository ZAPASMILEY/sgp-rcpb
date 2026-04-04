@extends('layouts.app')

@section('title', 'Secrétaires Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="max-w-[1500px] mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Secrétaires de la Faîtière</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Directions centrales</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Secrétaires rattachées</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-soft">Retour Faîtière</a>
                <button type="button" class="ent-btn ent-btn-primary" onclick="document.getElementById('secretaire-page-modal').classList.remove('hidden')">
                    Ajouter une secrétaire
                </button>
            </div>
        </div>

        <section class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
                <div class="flex-1 max-w-xl relative">
                    <label for="secretaire-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <div class="relative mt-1.5">
                        <input
                            id="secretaire-search"
                            type="text"
                            placeholder="Rechercher une secrétaire ou une direction..."
                            class="ent-input w-full"
                            autocomplete="off"
                        >
                        <div id="secretaire-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                </div>
                <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">
                    {{ $secretaires->total() }} secrétaire(s)
                </div>
            </div>

            <div class="ent-table-wrap overflow-x-auto">
                <table class="ent-table text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Direction</th>
                            <th>Secrétaire</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($secretaires as $direction)
                            <tr data-search-content="{{ strtolower(trim($direction->nom.' '.($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '').' '.($direction->secretaire_email ?? '').' '.($direction->secretaire_telephone ?? ''))) }}">
                                <td>{{ ($secretaires->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $direction->nom }}</td>
                                <td>{{ trim(($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '')) ?: 'Non renseignée' }}</td>
                                <td>{{ $direction->secretaire_email ?: 'Non renseigné' }}</td>
                                <td>{{ $direction->secretaire_telephone ?: 'Non renseigné' }}</td>
                                <td class="whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-emerald-500" title="Voir"><i class="fas fa-eye text-xs"></i></a>
                                        <a href="{{ route('admin.secretaires.edit', $direction->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-blue-500" title="Modifier"><i class="fas fa-pen text-xs"></i></a>
                                        <form method="POST" action="{{ route('admin.secretaires.destroy', $direction->id) }}" onsubmit="return confirm('Supprimer cette secrétaire ?');" class="inline-flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-red-200 bg-white text-red-400 shadow-sm transition hover:bg-red-50 hover:text-red-600" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-slate-400">Aucune secrétaire trouvée pour la faîtrière.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($secretaires->hasPages())
                <div class="mt-6">{{ $secretaires->links() }}</div>
            @endif
        </section>
    </div>
</div>

<div id="secretaire-page-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4">
    <div class="w-full max-w-2xl rounded-[32px] bg-white p-8 shadow-2xl relative">
        <button type="button" class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500" onclick="document.getElementById('secretaire-page-modal').classList.add('hidden')">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Ajouter une secrétaire</h3>
        <p class="mt-2 text-sm text-slate-400">Associez une secrétaire à une direction de la faîtière.</p>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Prénom</label>
                    <input name="prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Nom</label>
                    <input name="nom" type="text" class="ent-input w-full" required>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Email</label>
                <input name="email" type="email" class="ent-input w-full" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Direction rattachée</label>
                    <select name="direction_id" class="ent-input w-full" required>
                        <option value="">Sélectionner une direction</option>
                        @foreach($directions as $direction)
                            <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Date de prise de fonction</label>
                    <input name="date_prise_fonction" type="date" class="ent-input w-full" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="ent-btn ent-btn-soft" onclick="document.getElementById('secretaire-page-modal').classList.add('hidden')">Annuler</button>
                <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.getElementById('secretaire-page-modal')?.classList.add('hidden');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('secretaire-search');
    var suggestionsBox = document.getElementById('secretaire-suggestions');
    if (!searchInput || !suggestionsBox) return;

    var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
    var pool = new Set();
    rows.forEach(function (row) {
        var cells = row.querySelectorAll('td');
        if (cells.length < 5) return;
        [cells[1], cells[2], cells[3], cells[4]].forEach(function (cell) {
            var txt = (cell.innerText || '').replace(/\s+/g, ' ').trim();
            if (txt.length >= 2 && txt !== '-' && txt !== 'Non renseign\u00e9' && txt !== 'Non renseign\u00e9e') pool.add(txt);
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
