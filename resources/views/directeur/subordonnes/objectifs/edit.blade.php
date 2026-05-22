@extends('layouts.directeur')
@section('title', 'Modifier fiche | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $mode = $fiche->statut ?? 'brouillon';
    $isContested = $mode === 'contesté';
    $isRefusee   = $mode === 'refusee';

    $heroStyle = match ($mode) {
        'contesté' => 'background: linear-gradient(135deg, #c2410c, #ea580c, #d97706)',
        'refusee'  => 'background: linear-gradient(135deg, #be123c, #e11d48, #db2777)',
        default    => 'background: linear-gradient(135deg, #5b21b6, #7c3aed, #6d28d9)',
    };
    $heroTitle = match ($mode) {
        'contesté' => 'Réviser les objectifs contestés',
        'refusee'  => 'Corriger la fiche refusée',
        default    => 'Modifier le brouillon',
    };
    $heroDesc = match ($mode) {
        'contesté' => 'Seuls les objectifs contestés (en rouge) peuvent être modifiés. Les autres sont verrouillés.',
        'refusee'  => 'Le subordonné a refusé cette fiche. Modifiez-la puis renvoyez-la.',
        default    => 'Modifiez la fiche avant de l\'envoyer au subordonné.',
    };
    $heroIcon = match ($mode) {
        'contesté' => 'fa-flag',
        'refusee'  => 'fa-times-circle',
        default    => 'fa-pen',
    };
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="{{ $heroStyle }}">
        <div class="pointer-events-none absolute -right-20 -top-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="hidden lg:flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
                    <i class="fas {{ $heroIcon }} text-2xl text-white"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-white/60">
                        <a href="{{ $cancelUrl }}" class="hover:text-white transition">Fiche</a>
                        <i class="fas fa-chevron-right text-[8px]"></i>
                        <span class="text-white/80">{{ $fiche->titre }}</span>
                    </div>
                    <h1 class="mt-1.5 text-2xl font-black text-white leading-tight">{{ $heroTitle }}</h1>
                    <p class="mt-1 text-sm text-white/70 max-w-xl">{{ $heroDesc }}</p>
                </div>
            </div>
            <a href="{{ $cancelUrl }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white ring-1 ring-white/20 transition hover:bg-white/25 self-start shrink-0">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="mx-auto max-w-3xl flex flex-col gap-5">

        @if ($errors->any())
            <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                <i class="fas fa-circle-exclamation mt-0.5 shrink-0"></i>
                <div>
                    <p class="font-black">Veuillez corriger les erreurs suivantes :</p>
                    <ul class="mt-1 space-y-0.5 text-xs">
                        @foreach($errors->all() as $e)
                            <li>• {{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @if ($isContested)
        <div class="flex items-start gap-4 rounded-[24px] border-2 border-orange-200 bg-orange-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                <i class="fas fa-flag text-base"></i>
            </div>
            <div>
                <p class="font-black text-orange-900">Objectifs contestés par {{ $assigneeUser?->name ?? 'le subordonné' }}</p>
                <p class="mt-0.5 text-sm text-orange-700">
                    {{ $fiche->objectifs->where('statut', 'contesté')->count() }}
                    objectif(s) signalé(s) en rouge. Corrigez-les puis renvoyez la fiche.
                </p>
            </div>
        </div>
        @elseif ($isRefusee)
        <div class="flex items-start gap-4 rounded-[24px] border-2 border-rose-200 bg-rose-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                <i class="fas fa-times-circle text-base"></i>
            </div>
            <div>
                <p class="font-black text-rose-900">Fiche refusée</p>
                <p class="mt-0.5 text-sm text-rose-700">Le subordonné a refusé cette fiche. Modifiez les objectifs et renvoyez-la.</p>
            </div>
        </div>
        @endif

        <form method="POST" action="{{ route($updateRoute, $fiche) }}" class="flex flex-col gap-5">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <i class="fas fa-bullseye text-sm"></i>
                    </span>
                    <p class="text-sm font-black text-slate-800">Informations générales</p>
                </div>

                <div class="grid gap-5 px-6 py-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Destinataire</label>
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-600">
                                <i class="fas fa-user text-xs"></i>
                            </span>
                            <div>
                                <p class="text-sm font-black text-slate-800">{{ $cibleLabel }}</p>
                                @if ($assigneeUser)
                                    <p class="text-[10px] text-slate-400">{{ $assigneeUser->name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="titre_fiche" class="mb-1 block text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                            Titre de la fiche
                            @if (!$isContested)<span class="text-rose-500">*</span>@endif
                        </label>
                        @if ($isContested)
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <i class="fas fa-lock text-xs text-slate-300"></i>
                                <span class="text-sm font-semibold text-slate-500">{{ $fiche->titre }}</span>
                            </div>
                            <input type="hidden" name="titre_fiche" value="{{ $fiche->titre }}">
                        @else
                            <input type="text" id="titre_fiche" name="titre_fiche"
                                   value="{{ old('titre_fiche', $fiche->titre) }}" required
                                   placeholder="Ex : Contrat d'objectifs {{ now()->year }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                        @endif
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl
                            {{ $isContested ? 'bg-orange-100 text-orange-600' : 'bg-violet-100 text-violet-600' }}">
                            <i class="fas fa-list-check text-sm"></i>
                        </span>
                        <div>
                            <p class="text-sm font-black text-slate-800">
                                Objectifs
                                <span class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">
                                    {{ $fiche->objectifs->count() }}
                                </span>
                            </p>
                            @if ($isContested)
                                <p class="text-[10px] text-orange-500 font-semibold">
                                    <i class="fas fa-flag mr-0.5"></i>
                                    {{ $fiche->objectifs->where('statut', 'contesté')->count() }} contesté(s) — modifiables uniquement
                                </p>
                            @endif
                        </div>
                    </div>
                    @if (!$isContested)
                        <button type="button" id="add-objectif-btn"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-violet-700">
                            <i class="fas fa-plus text-[10px]"></i> Ajouter
                        </button>
                    @endif
                </div>

                <div id="objectifs-container" class="divide-y divide-slate-50 px-6 py-4 space-y-2">
                    @foreach ($fiche->objectifs as $index => $objectif)
                    @php $contested = ($objectif->statut ?? 'normal') === 'contesté'; @endphp

                    @if ($isContested && !$contested)
                        <div class="flex items-center gap-3 py-2">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[11px] font-black text-slate-400">
                                {{ $index + 1 }}
                            </div>
                            <input type="hidden" name="objectifs[]" value="{{ $objectif->description }}">
                            <div class="flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-400 select-none">
                                {{ $objectif->description }}
                            </div>
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-300">
                                <i class="fas fa-lock text-xs"></i>
                            </div>
                        </div>

                    @elseif ($isContested && $contested)
                        <div class="py-2">
                            <div class="mb-1.5 flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-[11px] font-black text-orange-600">
                                    {{ $index + 1 }}
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-2.5 py-0.5 text-[10px] font-black text-orange-600">
                                    <i class="fas fa-flag text-[8px]"></i> Contesté — à corriger
                                </span>
                            </div>
                            <input type="text" name="objectifs[]" required
                                   value="{{ old('objectifs.'.$loop->index, $objectif->description) }}"
                                   placeholder="Corrigez cet objectif…"
                                   class="w-full rounded-2xl border-2 border-orange-300 bg-orange-50/50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-orange-300 focus:border-orange-400 focus:ring-4 focus:ring-orange-100">
                        </div>

                    @else
                        <div class="objectif-row flex items-center gap-3 py-2">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-[11px] font-black text-violet-600">
                                {{ $index + 1 }}
                            </div>
                            <input type="text" name="objectifs[]" required
                                   value="{{ old('objectifs.'.$loop->index, $objectif->description) }}"
                                   placeholder="Description de l'objectif"
                                   class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-violet-400 focus:ring-4 focus:ring-violet-100">
                            <button type="button" class="remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    @endif

                    @endforeach
                </div>
            </div>

            <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ $cancelUrl }}"
                       class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i> Annuler
                    </a>
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($isContested)
                            <button type="submit" name="action" value="brouillon"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50">
                                <i class="fas fa-floppy-disk text-xs"></i> Enregistrer sans renvoyer
                            </button>
                            <button type="submit" name="action" value="renvoyer"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-black text-white shadow-md transition"
                                    style="background:#ea580c" onmouseover="this.style.background='#c2410c'" onmouseout="this.style.background='#ea580c'">
                                <i class="fas fa-paper-plane text-xs"></i> Enregistrer et renvoyer
                            </button>
                        @elseif ($isRefusee)
                            <button type="submit" name="action" value="renvoyer"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl px-6 py-2.5 text-sm font-black text-white shadow-md transition"
                                    style="background:#e11d48" onmouseover="this.style.background='#be123c'" onmouseout="this.style.background='#e11d48'">
                                <i class="fas fa-paper-plane text-xs"></i> Corriger et renvoyer
                            </button>
                        @else
                            <button type="submit" name="action" value="brouillon"
                                    class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-5 py-2.5 text-sm font-black text-amber-700 shadow-sm transition hover:bg-amber-100">
                                <i class="fas fa-floppy-disk text-xs"></i> Enregistrer le brouillon
                            </button>
                        @endif
                    </div>
                </div>
            </div>

        </form>
        </div>
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

    var inputClass  = 'flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-violet-400 focus:ring-4 focus:ring-violet-100';
    var removeClass = 'remove-objectif-btn flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600';

    function getRows() { return container.querySelectorAll('.objectif-row'); }
    function updateRemoveButtons() {
        var rows = getRows();
        rows.forEach(function (row) {
            var btn = row.querySelector('.remove-objectif-btn');
            if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
        });
    }

    function makeRow() {
        var idx = getRows().length + 1;
        var row = document.createElement('div');
        row.className = 'objectif-row flex items-center gap-3 py-2';
        row.innerHTML =
            '<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-[11px] font-black text-violet-600">' + idx + '</div>'
            + '<input type="text" name="objectifs[]" required placeholder="Description de l\'objectif" class="' + inputClass + '">'
            + '<button type="button" class="' + removeClass + '"><i class="fas fa-trash text-xs"></i></button>';
        return row;
    }

    addBtn.addEventListener('click', function () {
        var row = makeRow();
        container.appendChild(row);
        row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
            row.remove(); updateRemoveButtons();
        });
        updateRemoveButtons();
        row.querySelector('input').focus();
    });

    updateRemoveButtons();
    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.objectif-row').remove(); updateRemoveButtons();
        });
    });
});
</script>
@endif
@endpush
