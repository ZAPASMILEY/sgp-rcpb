@extends('layouts.chef')
@section('title', 'Modifier fiche | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $mode = $fiche->statut ?? 'brouillon';
    $isContested = $mode === 'contesté';
    $isRefusee   = $mode === 'refusee';

    /** @var \App\Models\Agent|null $agent */
    $agent = $fiche->assignable;
    $agentNom = $agent ? trim($agent->prenom . ' ' . $agent->nom) : '—';
@endphp
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- En-tête --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Espace Chef / {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                        @if ($isContested)
                            Réviser les objectifs contestés
                        @elseif ($isRefusee)
                            Corriger la fiche refusée
                        @else
                            Modifier le brouillon
                        @endif
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Agent : <span class="font-semibold">{{ $agentNom }}</span>
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('chef.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">
                        Retour
                    </a>
                </div>
            </div>
        </header>

        {{-- Messages d'erreur --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                <p class="font-black mb-1">Veuillez corriger les erreurs suivantes :</p>
                <ul class="space-y-0.5 text-xs">
                    @foreach($errors->all() as $e)
                        <li>• {{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Bannière --}}
        @if ($isContested)
        <div class="flex items-start gap-4 rounded-2xl border-2 border-orange-200 bg-orange-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                <i class="fas fa-flag text-base"></i>
            </div>
            <div>
                <p class="font-black text-orange-900">Objectifs contestés par {{ $agentNom }}</p>
                <p class="mt-0.5 text-sm text-orange-700">
                    {{ $fiche->objectifs->where('statut', 'contesté')->count() }}
                    objectif(s) signalé(s). Corrigez-les puis renvoyez la fiche à l'agent.
                </p>
            </div>
        </div>
        @elseif ($isRefusee)
        <div class="flex items-start gap-4 rounded-2xl border-2 border-rose-200 bg-rose-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                <i class="fas fa-times-circle text-base"></i>
            </div>
            <div>
                <p class="font-black text-rose-900">Fiche refusée par l'agent</p>
                <p class="mt-0.5 text-sm text-rose-700">Modifiez les objectifs et renvoyez-la à l'agent.</p>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route('chef.objectifs.update', $fiche) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')

            {{-- Informations générales --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-base font-black text-slate-900 mb-5">Informations générales</h2>
                <div class="grid gap-5 sm:grid-cols-2">
                    {{-- Destinataire --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Destinataire</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-600">
                                <i class="fas fa-user text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-black text-slate-800">{{ $agentNom }}</p>
                                @if ($agent?->role)
                                    <p class="text-[10px] text-slate-400">{{ $agent->role }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Titre --}}
                    <div class="sm:col-span-2">
                        <label for="titre" class="mb-1 block text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                            Titre de la fiche
                            @if (!$isContested)<span class="text-rose-500">*</span>@endif
                        </label>
                        @if ($isContested)
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <i class="fas fa-lock text-xs text-slate-300"></i>
                                <span class="text-sm font-semibold text-slate-500">{{ $fiche->titre }}</span>
                            </div>
                            <input type="hidden" name="titre" value="{{ $fiche->titre }}">
                        @else
                            <input type="text" id="titre" name="titre"
                                   value="{{ old('titre', $fiche->titre) }}" required
                                   placeholder="Ex : Objectifs {{ now()->year }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                        @endif
                    </div>
                </div>
            </section>

            {{-- Liste des objectifs --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-black text-slate-900">
                        Objectifs
                        <span class="ml-2 rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-bold text-slate-600">
                            {{ $fiche->objectifs->count() }}
                        </span>
                    </h2>
                    @if (!$isContested)
                        <button type="button" id="add-objectif-btn" class="ent-btn bg-emerald-600 text-white hover:bg-emerald-700">
                            <i class="fas fa-plus mr-2"></i>Ajouter
                        </button>
                    @endif
                </div>

                <div id="objectifs-container" class="flex flex-col gap-3">
                    @foreach ($fiche->objectifs as $index => $objectif)
                    @php $contested = ($objectif->statut ?? 'normal') === 'contesté'; @endphp

                    @if ($isContested && !$contested)
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-black text-slate-400">
                                {{ $index + 1 }}
                            </span>
                            <input type="hidden" name="objectifs[{{ $index }}][description]" value="{{ $objectif->description }}">
                            <div class="flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-400 select-none">
                                {{ $objectif->description }}
                            </div>
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-300">
                                <i class="fas fa-lock text-xs"></i>
                            </span>
                        </div>

                    @elseif ($isContested && $contested)
                        <div>
                            <div class="mb-1.5 flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-[11px] font-black text-orange-600">
                                    {{ $index + 1 }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-[10px] font-black text-orange-600">
                                    <i class="fas fa-flag text-[8px]"></i> Contesté — à corriger
                                </span>
                            </div>
                            <input type="text" name="objectifs[{{ $index }}][description]" required
                                   value="{{ old('objectifs.'.$index.'.description', $objectif->description) }}"
                                   placeholder="Corrigez cet objectif…"
                                   class="w-full rounded-2xl border-2 border-orange-300 bg-orange-50/50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>

                    @else
                        <div class="objectif-row flex items-center gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[11px] font-black text-emerald-600 row-num">
                                {{ $index + 1 }}
                            </span>
                            <input type="text" name="objectifs[{{ $index }}][description]" required
                                   value="{{ old('objectifs.'.$index.'.description', $objectif->description) }}"
                                   placeholder="Description de l'objectif"
                                   class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                            <button type="button" class="remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    @endif
                    @endforeach
                </div>
            </section>

            {{-- Actions --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="{{ route('chef.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Annuler
                    </a>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($isContested)
                            <button type="submit" name="action" value="brouillon" class="ent-btn ent-btn-soft">
                                <i class="fas fa-floppy-disk mr-2"></i>Enregistrer sans renvoyer
                            </button>
                            <button type="submit" name="action" value="renvoyer"
                                    class="ent-btn bg-orange-600 text-white hover:bg-orange-700">
                                <i class="fas fa-paper-plane mr-2"></i>Enregistrer et renvoyer à l'agent
                            </button>
                        @elseif ($isRefusee)
                            <button type="submit" name="action" value="renvoyer"
                                    class="ent-btn bg-rose-600 text-white hover:bg-rose-700">
                                <i class="fas fa-paper-plane mr-2"></i>Corriger et renvoyer à l'agent
                            </button>
                        @else
                            <button type="submit" name="action" value="brouillon"
                                    class="ent-btn bg-amber-500 text-white hover:bg-amber-600">
                                <i class="fas fa-floppy-disk mr-2"></i>Enregistrer le brouillon
                            </button>
                        @endif
                    </div>
                </div>
            </section>

        </form>
    </div>
</div>
@endsection

@push('scripts')
@if (!$isContested)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('objectifs-container');
    var addBtn    = document.getElementById('add-objectif-btn');
    if (!container || !addBtn) return;

    var inputClass = 'flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100';
    var rmClass    = 'remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600';

    function getRows() { return container.querySelectorAll('.objectif-row'); }
    function reindex() {
        getRows().forEach(function (row, i) {
            var inp = row.querySelector('input[type="text"]');
            if (inp) inp.name = 'objectifs[' + i + '][description]';
            var num = row.querySelector('.row-num');
            if (num) num.textContent = i + 1;
            row.querySelector('.remove-objectif-btn').style.display = getRows().length > 1 ? '' : 'none';
        });
    }

    addBtn.addEventListener('click', function () {
        var idx = getRows().length;
        var row = document.createElement('div');
        row.className = 'objectif-row flex items-center gap-3';
        row.innerHTML =
            '<span class="row-num flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-[11px] font-black text-emerald-600">' + (idx + 1) + '</span>'
            + '<input type="text" name="objectifs[' + idx + '][description]" required placeholder="Description de l\'objectif" class="' + inputClass + '">'
            + '<button type="button" class="' + rmClass + '"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('.remove-objectif-btn').addEventListener('click', function () { row.remove(); reindex(); });
        reindex();
        row.querySelector('input').focus();
    });

    reindex();
    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.objectif-row').remove(); reindex(); });
    });
});
</script>
@endif
@endpush
