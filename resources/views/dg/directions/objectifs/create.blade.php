@extends('layouts.dg')
@section('title', 'Assigner des objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $oldObjectifs = old('objectifs', ['']);
    if (! is_array($oldObjectifs) || $oldObjectifs === []) {
        $oldObjectifs = [''];
    }
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-300">
                    <span>Directions</span>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white">{{ $direction->nom }}</span>
                </div>
                <h1 class="mt-2 text-2xl font-black text-white leading-tight">Assigner une fiche d'objectifs</h1>
                <p class="mt-1 text-sm text-emerald-100/80">Créez une fiche et rattachez-la à la direction sélectionnée.</p>
            </div>
            <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs']) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20 self-start">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5 max-w-2xl">

        @if ($errors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">

            {{-- Direction cible --}}
            <div class="border-b border-slate-100 px-6 py-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Direction cible</p>
                <p class="mt-1 text-base font-black text-slate-900">{{ $direction->nom }}</p>
                @php $directeurNom = $direction->directeur ? trim($direction->directeur->prenom.' '.$direction->directeur->nom) : null; @endphp
                @if ($directeurNom)
                    <p class="mt-0.5 text-xs text-slate-500">Directeur : {{ $directeurNom }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('dg.directions.objectifs.store') }}" class="px-6 py-5 grid gap-5">
                @csrf
                <input type="hidden" name="direction_id" value="{{ $direction->id }}">

                @php $anneeOuverte = \App\Models\Annee::currentOpen(); @endphp
                <input type="hidden" name="date_echeance" value="{{ $anneeOuverte ? $anneeOuverte->annee.'-12-31' : now()->format('Y').'-12-31' }}">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Date</label>
                        <input type="text" value="{{ now()->format('d/m/Y') }}" readonly tabindex="-1"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 cursor-not-allowed outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Année</label>
                        <input type="text" value="{{ $anneeOuverte?->annee ?? now()->year }}" readonly tabindex="-1"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 cursor-not-allowed outline-none select-none">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="titre_fiche" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Titre de la fiche d'objectifs</label>
                    <input type="text" id="titre_fiche" name="titre_fiche" value="{{ old('titre_fiche') }}" required
                           placeholder="Ex : Contrat d'objectifs {{ now()->year }} — {{ $direction->nom }}"
                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Objectifs à assigner</label>
                    <div id="objectifs-container" class="space-y-2">
                        @foreach ($oldObjectifs as $objectif)
                            <div class="objectif-row flex items-center gap-2">
                                <input type="text" name="objectifs[]" value="{{ $objectif }}" required
                                       placeholder="Description de l'objectif"
                                       class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
                                <button type="button" class="remove-objectif-btn flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" style="display:none;">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" id="add-objectif-btn"
                            class="mt-1 inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-plus text-[10px]"></i> Ajouter un objectif
                    </button>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-paper-plane text-xs"></i> Assigner la fiche
                    </button>
                </div>
            </form>
        </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('objectifs-container');
    var addBtn = document.getElementById('add-objectif-btn');
    if (!container || !addBtn) return;

    var inputClass = 'flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100';
    var removeClass = 'remove-objectif-btn flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600';

    function updateRemoveButtons() {
        var rows = container.querySelectorAll('.objectif-row');
        rows.forEach(function (row) {
            var btn = row.querySelector('.remove-objectif-btn');
            if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'objectif-row flex items-center gap-2';
        row.innerHTML = '<input type="text" name="objectifs[]" required placeholder="Description de l\'objectif" class="' + inputClass + '">'
            + '<button type="button" class="' + removeClass + '"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
            row.remove(); updateRemoveButtons();
        });
        updateRemoveButtons();
    });

    updateRemoveButtons();
    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.objectif-row').remove(); updateRemoveButtons();
        });
    });
});
</script>
@endpush
