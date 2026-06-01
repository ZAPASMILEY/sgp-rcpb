@extends($layout ?? 'layouts.app')
@section('title', 'Assigner des objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $oldObjectifs = is_array($oldObjectifs) && $oldObjectifs !== [] ? $oldObjectifs : [''];
    $anneeOuverte = \App\Models\Annee::currentOpen();
    $anneeVal     = $anneeOuverte?->annee ?? now()->year;
    $titreDefault = old('titre_fiche', "Contrat d'objectifs {$anneeVal} — {$cibleLabel}");
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-violet-300">Attribution d'objectifs</p>
                <h1 class="mt-1 text-2xl font-black leading-tight text-white">Assigner une fiche d'objectifs</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">
                    Pour <span class="font-bold text-white">{{ $cibleLabel }}</span> · Année <span class="font-bold text-white">{{ $anneeVal }}</span>
                </p>
            </div>
            <a href="{{ $backRoute }}"
               class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-5 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation shrink-0"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route($storeRoute) }}" novalidate
              class="flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_300px] lg:items-start lg:gap-6">
            @csrf
            @if ($hiddenField)
                <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
            @endif
            <input type="hidden" name="date_echeance" value="{{ $anneeOuverte ? $anneeOuverte->annee.'-12-31' : now()->format('Y').'-12-31' }}">

            {{-- ══════════════ COLONNE PRINCIPALE ══════════════ --}}
            <div class="flex flex-col gap-5">

                {{-- Sélection subordonné (multi-cibles) --}}
                @if (!empty($subordonnes) && count($subordonnes) > 0)
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">
                            <i class="fas fa-user text-[10px]"></i>
                        </span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Destinataire</p>
                            <p class="text-xs text-slate-500">Sélectionnez la personne à qui assigner ces objectifs</p>
                        </div>
                    </div>
                    <div class="px-6 py-5">
                        <select name="{{ $subordonneField ?? 'subordonne_id' }}" required class="ent-select">
                            <option value="">— Choisir un collaborateur —</option>
                            @foreach ($subordonnes as $s)
                                <option value="{{ $s['id'] }}" @selected(old($subordonneField ?? 'subordonne_id') == $s['id'])>
                                    {{ $s['nom'] }}@if(!empty($s['role_label'])) — {{ $s['role_label'] }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                {{-- Bloc 1 : Informations générales --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">1</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Informations générales</p>
                            <p class="text-xs text-slate-500">Période et statut de la fiche</p>
                        </div>
                    </div>
                    <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Date d'assignation</p>
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <i class="fas fa-calendar text-xs text-slate-400"></i>
                                <span class="text-sm text-slate-600">{{ now()->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Année</p>
                            <div class="flex items-center gap-2 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3">
                                <i class="fas fa-layer-group text-xs text-violet-500"></i>
                                <span class="text-sm font-black text-violet-700">{{ $anneeVal }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Statut initial</p>
                            <div class="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <i class="fas fa-hourglass-half text-xs text-amber-500"></i>
                                <span class="text-sm font-black text-amber-700">En attente</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 2 : Titre de la fiche --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">2</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Titre de la fiche</p>
                            <p class="text-xs text-slate-500">Un titre clair et identifiable pour ce contrat d'objectifs</p>
                        </div>
                    </div>
                    <div class="px-6 py-5">
                        <input type="text" id="titre_fiche" name="titre_fiche"
                               value="{{ $titreDefault }}" required
                               placeholder="Ex : Contrat d'objectifs {{ $anneeVal }} — {{ $cibleLabel }}"
                               class="ent-input">
                    </div>
                </div>

                {{-- Bloc 3 : Lignes d'objectifs --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">3</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Objectifs à atteindre</p>
                                <p class="text-xs text-slate-500">Minimum 1 objectif requis</p>
                            </div>
                        </div>
                        <button type="button" id="add-objectif-btn"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-violet-700">
                            <i class="fas fa-plus text-[9px]"></i> Ajouter
                        </button>
                    </div>
                    <div class="px-6 py-5">
                        <div id="objectifs-container" class="space-y-3">
                            @foreach ($oldObjectifs as $idx => $objectif)
                            <div class="objectif-row flex items-center gap-3">
                                <span class="objectif-num flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-xs font-black text-violet-600">{{ $idx + 1 }}</span>
                                <input type="text" name="objectifs[]" value="{{ $objectif }}" required
                                       placeholder="Décrivez l'objectif à atteindre..."
                                       class="ent-input flex-1">
                                <button type="button" class="remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" style="display:none;">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══════════════ SIDEBAR ══════════════ --}}
            <div class="sticky top-4 flex flex-col gap-4">

                {{-- Carte info + actions --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="bg-gradient-to-br from-violet-600 to-purple-600 px-5 py-5 text-white">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-200">Assigné à</p>
                        <p class="mt-1 text-lg font-black leading-tight">{{ $cibleLabel }}</p>
                        <p class="mt-1 text-xs text-violet-200">Année {{ $anneeVal }}</p>
                    </div>
                    <div class="border-t border-slate-100 px-5 py-4">
                        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Sections</p>
                        <ol class="space-y-1.5 text-xs text-slate-500">
                            @if (!empty($subordonnes) && count($subordonnes) > 0)
                            <li class="flex items-center gap-2">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700"><i class="fas fa-user text-[7px]"></i></span>
                                Destinataire
                            </li>
                            @endif
                            <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">1</span> Informations générales</li>
                            <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">2</span> Titre de la fiche</li>
                            <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">3</span> Objectifs à atteindre</li>
                        </ol>
                    </div>
                </div>

                <button type="submit" name="action" value="envoyer"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                    <i class="fas fa-paper-plane text-xs"></i> Assigner la fiche
                </button>
                <button type="submit" name="action" value="brouillon"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-floppy-disk text-xs"></i> Enregistrer en brouillon
                </button>

            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('objectifs-container');
    const addBtn    = document.getElementById('add-objectif-btn');
    if (!container || !addBtn) return;

    const inputCls = 'ent-input flex-1';
    const numCls   = 'objectif-num flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-xs font-black text-violet-600';
    const rmCls    = 'remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600';

    function rows() { return container.querySelectorAll('.objectif-row'); }

    function refresh() {
        rows().forEach(function (row, i) {
            const num = row.querySelector('.objectif-num');
            if (num) num.textContent = i + 1;
            const btn = row.querySelector('.remove-objectif-btn');
            if (btn) btn.style.display = rows().length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'objectif-row flex items-center gap-3';
        row.innerHTML = '<span class="' + numCls + '">' + (rows().length + 1) + '</span>'
            + '<input type="text" name="objectifs[]" required placeholder="Décrivez l\'objectif à atteindre..." class="' + inputCls + '">'
            + '<button type="button" class="' + rmCls + '"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('.remove-objectif-btn').addEventListener('click', function () { row.remove(); refresh(); });
        row.querySelector('input').focus();
        refresh();
    });

    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.objectif-row').remove(); refresh(); });
    });
    refresh();
});
</script>
@endpush
