@php
    // On vérifie si cet objectif précis est celui qui pose problème
    $isThisRowContested = isset($objectif) && ($objectif->statut === 'conteste' || ($objectif->contestation ?? false));
    
    // Si la fiche globale est en mode contesté, mais que CET objectif n'est pas contesté, il doit être gelé
    $isLocked = $isContested && !$isThisRowContested;
@endphp

<div class="objectif-row flex flex-col gap-2 p-3 rounded-2xl transition border {{ $isThisRowContested ? 'border-orange-200 bg-orange-50/60' : 'border-transparent' }}">
    <div class="flex items-start gap-3">
        {{-- Index de la ligne --}}
        <span class="objectif-num flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600 mt-2">
            {{ $index }}
        </span>

        {{-- ID caché pour la mise à jour --}}
        <input type="hidden" name="objectifs[{{ $index }}][id]" value="{{ $objectif->id ?? '' }}">

        {{-- Champ Texte de l'objectif --}}
        <div class="flex-1">
            <input type="text" 
                   name="objectifs[{{ $index }}][libelle]" 
                   value="{{ old('objectifs.'.$index.'.libelle', $objectif->libelle ?? '') }}" 
                   required 
                   {{ $isLocked ? 'readonly' : '' }}
                   placeholder="Formuler l'objectif opérationnel..."
                   class="w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition 
                   {{ $isLocked ? 'border-slate-200 bg-slate-50 text-slate-400 cursor-not-allowed shadow-none' : 'border-slate-200 bg-white text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100' }}
                   {{ $isThisRowContested ? 'focus:border-orange-400 focus:ring-orange-100 border-orange-300' : '' }}">
        </div>

        {{-- Bouton Supprimer (uniquement si la fiche n'est pas en cours de contestation globale) --}}
        @if(!$isContested)
            <button type="button" class="remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100 mt-0.5">
                <i class="fas fa-trash-can text-xs"></i>
            </button>
        @endif
    </div>

    {{-- Bloc d'affichage du motif de contestation (Visible uniquement par le Manager en mode Edit) --}}
    @if($isThisRowContested && !empty($objectif->motif_contestation))
        <div class="ml-9 rounded-xl bg-orange-100/70 p-3 border border-orange-200">
            <p class="text-[10px] font-black uppercase tracking-wider text-orange-800 flex items-center gap-1.5">
                <i class="fas fa-comment-dots"></i> Motif de contestation de l'agent :
            </p>
            <p class="mt-1 text-xs font-medium text-orange-900 leading-relaxed">
                "{{ $objectif->motif_contestation }}"
            </p>
        </div>
    @endif
</div>