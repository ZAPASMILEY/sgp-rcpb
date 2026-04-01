<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
    <div>
        <h2 class="text-xl font-black text-slate-800">Secrétaires des directions de la faîtière</h2>
        <p class="mt-1 text-sm text-slate-400">Enregistrez une secrétaire sur une direction centrale et consultez la liste existante.</p>
    </div>
    <button type="button" class="ent-btn ent-btn-primary" onclick="document.getElementById('modal-secretaire').classList.remove('hidden')">
        Ajouter une secrétaire
    </button>
</div>

<div id="modal-secretaire" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4">
    <div class="w-full max-w-2xl rounded-[32px] bg-white p-8 shadow-2xl relative">
        <button type="button" class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500" onclick="document.getElementById('modal-secretaire').classList.add('hidden')">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Ajouter une secrétaire</h3>
        <p class="mt-2 text-sm text-slate-400">La secrétaire sera rattachée à une direction de la faîtière et son compte sera créé.</p>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="prenom" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Prénom</label>
                    <input id="prenom" name="prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label for="nom" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Nom</label>
                    <input id="nom" name="nom" type="text" class="ent-input w-full" required>
                </div>
            </div>

            <div>
                <label for="email" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Email</label>
                <input id="email" name="email" type="email" class="ent-input w-full" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="direction_id" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Direction rattachée</label>
                    <select id="direction_id" name="direction_id" class="ent-input w-full" required>
                        <option value="">Sélectionner une direction</option>
                        @foreach($allDirections as $direction)
                            <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_prise_fonction" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Date de prise de fonction</label>
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
    <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
        Aucune secrétaire enregistrée pour les directions de la faîtière.
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach($secretaires as $direction)
            <article class="rounded-[28px] border border-slate-100 bg-slate-50 p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-fuchsia-500">Secrétaire</p>
                        <h3 class="mt-2 text-lg font-black text-slate-800">
                            {{ trim(($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '')) ?: 'Nom non renseigné' }}
                        </h3>
                    </div>
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-fuchsia-500 shadow-sm">
                        <i class="fas fa-user-tie"></i>
                    </span>
                </div>

                <div class="mt-5 space-y-2 text-sm text-slate-500">
                    <p><span class="font-semibold text-slate-700">Direction :</span> {{ $direction->nom }}</p>
                    <p><span class="font-semibold text-slate-700">Email :</span> {{ $direction->secretaire_email ?: 'Non renseigné' }}</p>
                </div>
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="ent-btn ent-btn-soft text-cyan-500 hover:text-cyan-700 flex items-center gap-1" title="Voir">
                        <i class="fas fa-eye"></i> <span class="hidden md:inline">Voir</span>
                    </a>
                    <a href="{{ route('admin.secretaires.edit', $direction->id) }}" class="ent-btn ent-btn-soft text-amber-500 hover:text-amber-700 flex items-center gap-1" title="Modifier">
                        <i class="fas fa-edit"></i> <span class="hidden md:inline">Modifier</span>
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
