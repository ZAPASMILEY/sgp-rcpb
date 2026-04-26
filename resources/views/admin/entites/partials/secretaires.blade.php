<div class="mb-5 flex items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-slate-800">Secretaires de la faitiere</h2>
        <p class="mt-1 text-sm text-slate-400">Liste des secretaires rattachees aux directions du siege.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.entites.secretaires.index') }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
            Voir liste
        </a>
        <button type="button" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-fuchsia-500 to-pink-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-fuchsia-200 transition hover:-translate-y-0.5"
                onclick="document.getElementById('modal-secretaire').style.display='flex'">
            Ajouter
        </button>
    </div>
</div>

<div id="modal-secretaire" class="fixed inset-0 z-50 items-center justify-center bg-slate-950/40 p-4" style="display:none">
    <div class="relative w-full max-w-lg rounded-[32px] bg-white p-8 shadow-2xl">
        <button type="button"
                class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500"
                onclick="document.getElementById('modal-secretaire').style.display='none'">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black tracking-tight text-slate-800">Affecter une secrétaire</h3>
        <p class="mt-2 text-sm text-slate-400">Sélectionnez un agent existant et la direction à laquelle l'affecter.</p>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="sec-agent" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                    Secrétaire <span class="text-red-500">*</span>
                </label>
                @if(($secretaires_direction ?? collect())->isEmpty())
                    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent avec la fonction <strong>Secrétaire de Direction</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                    </div>
                @endif
                <select id="sec-agent" name="secretaire_agent_id" class="ent-input w-full" required>
                    <option value="">— Sélectionner un agent —</option>
                    @foreach($secretaires_direction ?? [] as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->prenom }} {{ $agent->nom }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400">Agents avec la fonction "Secrétaire de Direction".</p>
            </div>

            <div class="space-y-2">
                <label for="sec-direction" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                    Direction rattachée <span class="text-red-500">*</span>
                </label>
                <select id="sec-direction" name="direction_id" class="ent-input w-full" required>
                    <option value="">— Sélectionner une direction —</option>
                    @foreach($allDirections as $direction)
                        <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="ent-btn ent-btn-soft"
                        onclick="document.getElementById('modal-secretaire').style.display='none'">
                    Annuler
                </button>
                <button type="submit" class="ent-btn ent-btn-primary">Affecter</button>
            </div>
        </form>
    </div>
</div>

@if($secretaires->isEmpty())
    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
        Aucune secretaire enregistree pour les directions de la faitiere.
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($secretaires as $direction)
            <article class="rounded-[24px] border border-slate-100 bg-slate-50/80 p-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-fuchsia-500 shadow-sm">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="truncate font-black text-slate-800">
                            @if ($direction->secretaire)
                                {{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}
                            @else
                                Nom non renseigne
                            @endif
                        </h3>
                        <p class="truncate text-sm text-slate-500">{{ $direction->nom }}</p>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif

<script>
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('modal-secretaire').style.display = 'none';
});
</script>
