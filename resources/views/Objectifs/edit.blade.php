@extends($layout ?? 'layouts.app')
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
                    <h1 class="mt-1.5 text-2xl font-black text-white leading-tight">{{ $heroTitle }}</h1>
                    <p class="mt-1 text-sm text-white/80 max-w-2xl">{{ $heroDesc }}</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 self-start lg:self-center">
                <a href="{{ $cancelUrl ?? '#' }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Annuler
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 pt-8 lg:px-8">
        @if ($errors->any())
            <div class="mb-6 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation shrink-0"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route($updateRoute, $fiche) }}" novalidate class="flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_320px] lg:gap-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-5">
                {{-- Informations --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="grid gap-4 px-6 py-5 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Date création</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <span class="text-sm text-slate-500">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">Année</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3">
                                <span class="text-sm font-black text-indigo-700">{{ $fiche->annee?->annee ?? $fiche->annee_id }}</span>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">État</label>
                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <span class="text-sm font-bold capitalize text-slate-700">{{ str_replace('_', ' ', $mode) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Titre --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="px-6 py-5">
                        <input type="text" id="titre_fiche" name="titre_fiche" value="{{ old('titre_fiche', $fiche->titre) }}" required {{ $isContested ? 'readonly' : '' }}
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 {{ $isContested ? 'bg-slate-50 cursor-not-allowed opacity-75' : '' }}">
                    </div>
                </div>

                {{-- Objectifs --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <p class="text-sm font-black text-slate-900">Objectifs du contrat</p>
                        @if (!$isContested)
                            <button type="button" id="add-objectif-btn" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700">
                                <i class="fas fa-plus text-[9px]"></i> Ajouter
                            </button>
                        @endif
                    </div>
                    <div class="px-6 py-5">
                        <div id="objectifs-container" class="space-y-4">
                            @foreach ($fiche->objectifs as $idx => $objectif)
                                @php
                                    $isLineContested = ($objectif->statut ?? 'normal') === 'contesté';
                                    $isEditable = !$isContested || ($isContested && $isLineContested);
                                @endphp
                                <div class="objectif-row flex items-start gap-3 p-2 rounded-2xl transition {{ $isLineContested ? 'bg-rose-50 ring-1 ring-rose-100' : '' }}">
                                    <input type="hidden" name="objectif_ids[]" value="{{ $objectif->id }}">
                                    <span class="objectif-num mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-black {{ $isLineContested ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-600' }}">{{ $idx + 1 }}</span>
                                    <div class="flex-1 min-w-0">
                                        <input type="text" name="objectifs[]" value="{{ old('objectifs.'.$idx, $objectif->description) }}" required {{ !$isEditable ? 'readonly' : '' }}
                                               class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 {{ !$isEditable ? 'bg-slate-50 cursor-not-allowed opacity-75' : '' }}">
                                        @if ($isContested && $isLineContested)
                                            <p class="mt-1 text-[10px] font-bold text-rose-500 pl-1"><i class="fas fa-flag mr-1"></i>Modifiez ou supprimez cet objectif contesté.</p>
                                        @endif
                                    </div>
                                    @if (!$isContested || $isLineContested)
                                        <button type="button" class="remove-objectif-btn mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-900 px-5 py-5 text-white">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Agent concerné</p>
                        <p class="mt-1 text-lg font-black leading-tight">{{ $cibleLabel }}</p>
                    </div>
                </div>
                <div class="sticky top-4 flex flex-col gap-3">
                    <button type="submit" name="action"
                            value="{{ ($isContested || $isRefusee) ? 'renvoyer' : 'envoyer' }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-6 py-3.5 text-sm font-black text-white shadow-md transition hover:bg-indigo-700">
                        <i class="fas fa-paper-plane text-xs"></i>
                        @if($isContested) Valider corrections
                        @elseif($isRefusee) Corriger et renvoyer
                        @else Transmettre la fiche
                        @endif
                    </button>
                    @if (!$isContested)
                        <button type="submit" name="action" value="brouillon" class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                            <i class="fas fa-floppy-disk text-xs"></i> Brouillon
                        </button>
                    @endif
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
    if (!container) return;

    var inputCls = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100';
    var numCls   = 'objectif-num mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-black bg-slate-100 text-slate-600';
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

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'objectif-row flex items-start gap-3 p-2 rounded-2xl transition';
            row.innerHTML = '<input type="hidden" name="objectif_ids[]" value="">'
                + '<span class="' + numCls + '">' + (rows().length + 1) + '</span>'
                + '<div class="flex-1 min-w-0"><input type="text" name="objectifs[]" required placeholder="Nouvel objectif..." class="' + inputCls + '"></div>'
                + '<button type="button" class="' + rmCls + '"><i class="fas fa-trash text-xs"></i></button>';
            container.appendChild(row);
            row.querySelector('.remove-objectif-btn').addEventListener('click', function () { row.remove(); refresh(); });
            row.querySelector('input[type="text"]').focus();
            refresh();
        });
    }
    container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { btn.closest('.objectif-row').remove(); refresh(); });
    });
    refresh();
});
</script>
@endpush