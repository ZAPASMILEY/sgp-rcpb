<!-- Modal d'ajout de direction -->
<div id="modal-direction" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4" style="display:none">
    <div class="relative w-full max-w-lg rounded-[32px] bg-white p-8 shadow-2xl">
        <button type="button"
                class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500"
                onclick="document.getElementById('modal-direction').style.display='none'">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black tracking-tight text-slate-800">Nouvelle direction</h3>
        <p class="mt-2 text-sm text-slate-400">Renseignez le nom et sélectionnez le directeur parmi les agents existants.</p>

        <form method="POST" action="{{ route('admin.entites.directions.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="dir-nom" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                    Nom de la direction <span class="text-red-500">*</span>
                </label>
                <input id="dir-nom" name="nom" type="text" class="ent-input w-full" required placeholder="Ex: Direction des Ressources Humaines">
            </div>

            <div class="space-y-2">
                <label for="dir-directeur" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                    Directeur
                </label>
                @if(($directeurs_direction ?? collect())->isEmpty())
                    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent avec la fonction <strong>Directeur de Direction</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                    </div>
                @endif
                <select id="dir-directeur" name="directeur_agent_id" class="ent-input w-full">
                    <option value="">— Aucun directeur pour l'instant —</option>
                    @foreach($directeurs_direction ?? [] as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->prenom }} {{ $agent->nom }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400">Agents avec la fonction "Directeur de Direction".</p>
            </div>

            <div class="space-y-2">
                <label for="dir-secretaire" class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">
                    Secrétaire
                </label>
                @if(($secretaires_direction ?? collect())->isEmpty())
                    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent avec la fonction <strong>Secrétaire de Direction</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                    </div>
                @endif
                <select id="dir-secretaire" name="secretaire_agent_id" class="ent-input w-full">
                    <option value="">— Aucune secrétaire pour l'instant —</option>
                    @foreach($secretaires_direction ?? [] as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->prenom }} {{ $agent->nom }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400">Agents avec la fonction "Secrétaire de Direction".</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="ent-btn ent-btn-soft"
                        onclick="document.getElementById('modal-direction').style.display='none'">
                    Annuler
                </button>
                <button type="submit" class="ent-btn ent-btn-primary">
                    Enregistrer la direction
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('[onclick*="modal-direction"]').forEach(function(el) {
    el.addEventListener('click', function() {
        document.getElementById('modal-direction').style.display = 'flex';
    });
});
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') document.getElementById('modal-direction').style.display = 'none';
});
</script>
