<div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-black text-slate-800">Secretaires de la faitiere</h2>
        <p class="mt-1 text-sm text-slate-400">Liste des secretaires rattachees aux directions du siege.</p>
    </div>
    <button type="button" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-fuchsia-500 to-pink-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-fuchsia-200 transition hover:-translate-y-0.5" onclick="document.getElementById('modal-secretaire').classList.remove('hidden')">
        Ajouter une secretaire
    </button>
</div>

<div id="modal-secretaire" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4">
    <div class="relative w-full max-w-2xl rounded-[32px] bg-white p-8 shadow-2xl">
        <button type="button" class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500" onclick="document.getElementById('modal-secretaire').classList.add('hidden')">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black tracking-tight text-slate-800">Ajouter une secretaire</h3>
        <p class="mt-2 text-sm text-slate-400">La secretaire sera rattachee a une direction de la faitiere.</p>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="prenom" class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Prenom</label>
                    <input id="prenom" name="prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="nom" class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Nom</label>
                    <input id="nom" name="nom" type="text" class="ent-input w-full" required>
                </div>
            </div>

            <div>
                <label for="email" class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Email</label>
                <input id="email" name="email" type="email" class="ent-input w-full" required>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="direction_id" class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Direction rattachee</label>
                    <select id="direction_id" name="direction_id" class="ent-input w-full" required>
                        <option value="">Selectionner une direction</option>
                        @foreach($allDirections as $direction)
                            <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_prise_fonction" class="mb-2 block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Date de prise de fonction</label>
                    <input id="date_prise_fonction" name="date_prise_fonction" type="date" class="ent-input w-full" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="ent-btn ent-btn-soft" onclick="document.getElementById('modal-secretaire').classList.add('hidden')">Annuler</button>
                <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@if($secretaires->isEmpty())
    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
        Aucune secretaire enregistree pour les directions de la faitiere.
    </div>
@else
    <div class="space-y-4">
        @foreach($secretaires as $direction)
            <article class="flex flex-col gap-4 rounded-[24px] border border-slate-100 bg-slate-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[linear-gradient(135deg,_#fae8ff,_#ffffff)] text-fuchsia-500 shadow-inner">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800">
                            {{ trim(($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '')) ?: 'Nom non renseigne' }}
                        </h3>
                        <p class="text-base text-slate-500">{{ $direction->nom }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                        Voir fiche
                    </a>
                    <a href="{{ route('admin.secretaires.edit', $direction->id) }}" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-fuchsia-500 to-pink-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-fuchsia-200 transition hover:-translate-y-0.5">
                        Modifier
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif

<script>
window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.getElementById('modal-secretaire')?.classList.add('hidden');
    }
});
</script>
