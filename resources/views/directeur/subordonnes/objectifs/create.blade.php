@extends($layout ?? 'layouts.directeur')
@section('title', 'Assigner des objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $oldObjectifs = is_array($oldObjectifs) && $oldObjectifs !== [] ? $oldObjectifs : [''];
    $anneeOuverte = \App\Models\Annee::currentOpen();
    $anneeVal     = $anneeOuverte?->annee ?? now()->year;
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-10 lg:px-10">
        <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-10 bottom-0 h-48 w-80 rounded-full bg-purple-500/20 blur-2xl"></div>
        <div class="relative mx-auto flex max-w-6xl flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-violet-200 ring-1 ring-white/15">
                    <i class="fas fa-bullseye text-[9px]"></i> Espace Directeur · Subordonnés
                </div>
                <h1 class="text-3xl font-black leading-tight text-white">Assigner une fiche d'objectifs</h1>
                <p class="mt-1.5 max-w-lg text-sm text-violet-100/80">
                    Définissez les objectifs à atteindre pour
                    <span class="font-bold text-white">{{ $cibleLabel }}</span>
                    sur l'année <span class="font-bold text-white">{{ $anneeVal }}</span>.
                </p>
            </div>
            <a href="{{ $backRoute }}"
               class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-white/10 px-5 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 pt-8 lg:px-8">

        @if ($errors->any())
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation shrink-0"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route($storeRoute) }}" novalidate
              class="flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_320px] lg:gap-6">
            @csrf
            @if ($hiddenField)
                <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
            @endif
            <input type="hidden" name="date_echeance"
                   value="{{ $anneeOuverte ? $anneeOuverte->annee.'-12-31' : now()->format('Y').'-12-31' }}">

            {{-- ── Colonne gauche : formulaire ─────────────────────────────── --}}
            <div class="flex flex-col gap-5">

                {{-- Bloc 1 : Informations générales --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">1</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Informations générales</p>
                            <p class="text-xs text-slate-500">Période et destinataire de la fiche</p>
                        </div>
                    </div>
                    <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Date</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <i class="fas fa-calendar text-xs text-slate-400"></i>
                                <span class="text-sm text-slate-500">{{ now()->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Année</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-violet-200 bg-violet-50 px-4 py-3">
                                <i class="fas fa-layer-group text-xs text-violet-500"></i>
                                <span class="text-sm font-black text-violet-700">{{ $anneeVal }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Statut initial</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <i class="fas fa-hourglass-half text-xs text-amber-500"></i>
                                <span class="text-sm font-black text-amber-700">En attente</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 2 : Titre --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">2</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Titre de la fiche</p>
                            <p class="text-xs text-slate-500">Un titre clair et mémorable</p>
                        </div>
                    </div>
                    <div class="px-6 py-5">
                        <input type="text" id="titre_fiche" name="titre_fiche"
                               value="{{ old('titre_fiche') }}" required
                               placeholder="Ex : Contrat d'objectifs {{ $anneeVal }} — {{ $cibleLabel }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        @error('titre_fiche')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Bloc 3 : Objectifs --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">3</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Objectifs à atteindre</p>
                                <p class="text-xs text-slate-500">Chaque ligne est un objectif distinct et mesurable</p>
                            </div>
                        </div>
                        <button type="button" id="add-objectif-btn"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-violet-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-violet-700">
                            <i class="fas fa-plus text-[9px]"></i> Ajouter
                        </button>
                    </div>
                    <div class="px-6 py-5">
                        <div id="objectifs-container" class="space-y-3">
                            @foreach ($oldObjectifs as $idx => $objectif)
                                <div class="objectif-row flex items-start gap-3">
                                    <span class="objectif-num mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-xs font-black text-violet-600">{{ $idx + 1 }}</span>
                                    <input type="text" name="objectifs[]" value="{{ $objectif }}" required
                                           placeholder="Décrivez l'objectif à atteindre..."
                                           class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                                    <button type="button"
                                            class="remove-objectif-btn mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"
                                            style="display:none;">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @error('objectifs')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>

            {{-- ── Colonne droite : résumé + actions ───────────────────────── --}}
            <div class="flex flex-col gap-4">

                {{-- Carte cible --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="bg-gradient-to-br from-violet-600 to-purple-600 px-5 py-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-200">Assigné à</p>
                        <p class="mt-1 text-lg font-black leading-tight text-white">{{ $cibleLabel }}</p>
                        @if ($service)
                            @php $chef = trim(($service->chef_prenom ?? '').' '.($service->chef_nom ?? '')); @endphp
                            @if ($chef)
                                <p class="mt-1 text-xs text-violet-200">Chef de service : {{ $chef }}</p>
                            @endif
                        @endif
                        @if ($secretaire)
                            <p class="mt-1 text-xs text-violet-200">{{ $secretaire->email }}</p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="sticky top-4 flex flex-col gap-3">
                    <button type="submit" name="action" value="envoyer"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                        <i class="fas fa-paper-plane text-xs"></i> Assigner la fiche
                    </button>
                    <button type="submit" name="action" value="brouillon"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fas fa-floppy-disk text-xs"></i> Enregistrer en brouillon
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('objectifs-container');
    var addBtn    = document.getElementById('add-objectif-btn');
    if (!container || !addBtn) return;

    var inputCls = 'flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:ring-4 focus:ring-violet-100';
    var numCls   = 'objectif-num mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-xs font-black text-violet-600';
    var rmCls    = 'remove-objectif-btn mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600';

    function rows() { return container.querySelectorAll('.objectif-row'); }

    function refresh() {
        rows().forEach(function (row, i) {
            var num = row.querySelector('.objectif-num');
            if (num) num.textContent = i + 1;
            var btn = row.querySelector('.remove-objectif-btn');
            if (btn) btn.style.display = rows().length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'objectif-row flex items-start gap-3';
        row.innerHTML = '<span class="' + numCls + '">' + (rows().length + 1) + '</span>'
            + '<input type="text" name="objectifs[]" required placeholder="Décrivez l\'objectif à atteindre..." class="' + inputCls + '">'
            + '<button type="button" class="' + rmCls + '"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
            row.remove(); refresh();
        });
        row.querySelector('input').focus();
        refresh();
    });

    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.objectif-row').remove(); refresh();
        });
    });

    refresh();
});
</script>
@endpush
