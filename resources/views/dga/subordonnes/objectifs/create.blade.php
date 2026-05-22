@extends('layouts.dga')
@section('title', 'Assigner une fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $resolvedSubordonneId = (int) old('subordonne_id', $selectedSubordonne['id'] ?? 0);
    $resolvedSubordonne = $subordonnes->firstWhere('id', $resolvedSubordonneId);
    $lockSubordonne = $subordonnes->count() === 1 || $selectedSubordonne !== null;
    $oldObjectifs = old('objectifs', ['']);
    if (! is_array($oldObjectifs) || $oldObjectifs === []) {
        $oldObjectifs = [''];
    }
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-200">Espace DGA · Subordonnés</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Assigner une fiche d'objectifs</h1>
                <p class="mt-0.5 text-sm text-indigo-100/80">Créez une fiche et rattachez-la au collaborateur concerné.</p>
            </div>
            <a href="{{ url()->previous() }}"
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

        @if ($subordonnes->isEmpty())
            <div class="rounded-[24px] bg-white px-6 py-12 text-center shadow-sm ring-1 ring-slate-100">
                <i class="fas fa-users-slash text-2xl text-slate-300"></i>
                <p class="mt-3 text-sm font-black text-slate-700">Aucun subordonné disponible</p>
                <p class="mt-1 text-xs text-slate-500">Aucun Directeur Technique ou secrétaire configuré.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">

                {{-- Cible --}}
                @if ($lockSubordonne && $resolvedSubordonne)
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Collaborateur cible</p>
                        <p class="mt-1 text-base font-black text-slate-900">{{ $resolvedSubordonne['nom'] }}</p>
                        @if (filled($resolvedSubordonne['role_label'] ?? null))
                            <p class="mt-0.5 text-xs text-slate-500">{{ $resolvedSubordonne['role_label'] }} — La fiche sera assignée automatiquement à ce collaborateur.</p>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('dga.sub-objectifs.store') }}" class="px-6 py-5 grid gap-5">
                    @csrf

                    @if ($lockSubordonne && $resolvedSubordonne)
                        <input type="hidden" name="subordonne_id" value="{{ $resolvedSubordonne['id'] }}">
                    @else
                        <div class="space-y-1.5">
                            <label for="subordonne_id" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Collaborateur cible</label>
                            <select id="subordonne_id" name="subordonne_id" required
                                    class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100 cursor-pointer">
                                <option value="">Sélectionner un collaborateur</option>
                                @foreach ($subordonnes as $sub)
                                    <option value="{{ $sub['id'] }}" @selected($resolvedSubordonneId === (int) $sub['id'])>
                                        {{ $sub['nom'] }}{{ filled($sub['role_label'] ?? null) ? ' ('.$sub['role_label'].')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

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
                               placeholder="Ex : Contrat d'objectifs {{ now()->year }}"
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Objectifs à assigner</label>
                        <div id="objectifs-container" class="space-y-2">
                            @foreach ($oldObjectifs as $objectif)
                                <div class="objectif-row flex items-center gap-2">
                                    <input type="text" name="objectifs[]" value="{{ $objectif }}" required
                                           placeholder="Description de l'objectif"
                                           class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100">
                                    <button type="button" class="remove-objectif-btn flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" style="display:none;">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-objectif-btn"
                                class="mt-1 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700">
                            <i class="fas fa-plus text-[10px]"></i> Ajouter un objectif
                        </button>
                    </div>

                    <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row gap-3">
                        <button type="submit" name="action" value="brouillon"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                            <i class="fas fa-floppy-disk text-xs"></i> Enregistrer en brouillon
                        </button>
                        <button type="submit" name="action" value="envoyer"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700">
                            <i class="fas fa-paper-plane text-xs"></i> Assigner la fiche
                        </button>
                    </div>
                </form>
            </div>
        @endif

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

    var inputClass = 'flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100';
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
