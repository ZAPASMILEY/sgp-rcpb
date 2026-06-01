@php
    $isRowContested = isset($objectif) && $objectif->statut === 'conteste';
    $canContest = ($statut === 'en_attente' || $statut === 'soumis') && $isOwnFiche && !$isRowContested;
@endphp

<div class="py-4 flex flex-col gap-3 {{ $isRowContested ? 'bg-orange-50/40 -mx-6 px-6 border-l-4 border-orange-500' : '' }}">
    <div class="flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-500 mt-0.5">
                {{ $index }}
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-700 leading-relaxed">
                    {{ $objectif->libelle }}
                </p>
                
                @if($isRowContested)
                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700">
                        <i class="fas fa-triangle-exclamation text-[9px]"></i> Cet objectif a été contesté
                    </span>
                @endif
            </div>
        </div>

        {{-- Action de contestation individuelle pour l'agent --}}
        @if($canContest)
            <button type="button" 
                    onclick="document.getElementById('form-contester-{{ $objectif->id }}').classList.toggle('hidden')" 
                    class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-orange-200 bg-orange-50 px-2.5 py-1 text-xs font-bold text-orange-700 transition hover:bg-orange-100">
                <i class="fas fa-flag text-[10px]"></i> Contester
            </button>
        @endif
    </div>

    {{-- Formulaire masqué qui se révèle au clic sur "Contester" --}}
    @if($canContest)
        <div id="form-contester-{{ $objectif->id }}" class="hidden ml-9 rounded-xl border border-orange-200 bg-white p-4 shadow-sm animate-fade-in">
            <form action="{{ route('objectifs.contester', $objectif->id) }}" method="POST">
                @csrf
                <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-1.5">Expliquez votre désaccord ou proposez une alternative :</label>
                <textarea name="motif_contestation" rows="2" required placeholder="Ex: Le volume cible de traitement de dossiers est trop élevé par rapport aux ressources actuelles..."
                          class="w-full rounded-xl border border-slate-200 p-3 text-xs text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100"></textarea>
                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('form-contester-{{ $objectif->id }}').classList.add('hidden')" class="rounded-lg px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-100">Annuler</button>
                    <button type="submit" class="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-orange-700">Soumettre la réserve</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Historique du motif si déjà validé/envoyé en contestation --}}
    @if($isRowContested && !empty($objectif->motif_contestation))
        <div class="ml-9 rounded-xl bg-orange-100/50 p-3 border border-orange-200/60">
            <p class="text-[10px] font-bold text-orange-800">Votre motif transmis :</p>
            <p class="mt-0.5 text-xs italic text-orange-950">"{{ $objectif->motif_contestation }}"</p>
        </div>
    @endif
</div>