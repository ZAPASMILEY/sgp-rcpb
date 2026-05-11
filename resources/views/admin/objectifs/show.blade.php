@extends('layouts.app')
@section('title', 'Objectif | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $assignable = $objectif->assignable;
    $progressValue = (int) $objectif->avancement_percentage;
    $deadline = \Carbon\Carbon::parse($objectif->date_echeance);
    $today = today();
    $isExpired = $deadline->isBefore($today);
    $remainingLabel = $isExpired
        ? 'Échu depuis '.$deadline->diffInDays($today).' jour'.($deadline->diffInDays($today) > 1 ? 's' : '')
        : ($deadline->isSameDay($today)
            ? "Échéance aujourd'hui"
            : 'Il reste '.$today->diffInDays($deadline).' jour'.($today->diffInDays($deadline) > 1 ? 's' : ''));
    $typeLabel = $assignable instanceof \App\Models\Entite ? 'Entité' : (
        $assignable instanceof \App\Models\Direction ? 'Direction' : (
            $assignable instanceof \App\Models\Service ? 'Service' : (
                $assignable instanceof \App\Models\Agent ? 'Agent' : '-'
            )
        )
    );
    $cibleLabel = $assignable instanceof \App\Models\Agent
        ? trim($assignable->prenom.' '.$assignable->nom)
        : ($assignable?->nom ?? '-');
    $progressColor = $progressValue > 50 ? 'bg-emerald-500' : ($progressValue > 0 ? 'bg-amber-400' : 'bg-slate-200');
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-300">
                    <a href="{{ route('admin.objectifs.index') }}" class="hover:text-white transition">Objectifs</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white">Détail</span>
                </div>
                <h1 class="mt-2 text-2xl font-black text-white leading-tight">Détail de l'objectif</h1>
                <p class="mt-1 text-sm text-slate-300/80">{{ $cibleLabel }} · {{ $typeLabel }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('admin.objectifs.edit', $objectif) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-pen text-[10px]"></i> Modifier
                </a>
                <a href="{{ route('admin.objectifs.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i>{{ session('status') }}
            </div>
        @endif

        {{-- KPI cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ $objectif->date }}</p>
            </div>
            <div class="rounded-[20px] border {{ $isExpired ? 'border-rose-200 bg-rose-50' : 'border-slate-100 bg-white' }} px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Échéance</p>
                <p class="mt-2 text-lg font-black {{ $isExpired ? 'text-rose-600' : 'text-slate-900' }}">{{ $objectif->date_echeance }}</p>
                <p class="mt-0.5 text-[10px] font-bold {{ $isExpired ? 'text-rose-500' : 'text-emerald-600' }}">{{ $remainingLabel }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Type cible</p>
                <p class="mt-2 text-base font-black text-slate-900">{{ $typeLabel }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Cible</p>
                <p class="mt-2 text-base font-black text-slate-900 leading-snug">{{ $cibleLabel }}</p>
            </div>
        </div>

        {{-- Avancement --}}
        <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement global</p>
                    <p class="mt-1 text-4xl font-black text-slate-900">{{ $progressValue }}<span class="text-xl font-bold text-slate-400">%</span></p>
                </div>
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl
                    {{ $progressValue >= 100 ? 'bg-emerald-100 text-emerald-600' : ($progressValue >= 50 ? 'bg-sky-100 text-sky-600' : 'bg-amber-100 text-amber-600') }}">
                    <i class="fas {{ $progressValue >= 100 ? 'fa-circle-check' : 'fa-chart-line' }} text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-3 rounded-full transition-all {{ $progressColor }}" style="width: {{ min($progressValue, 100) }}%"></div>
            </div>
            <div class="mt-1.5 flex justify-between text-[10px] font-bold text-slate-400">
                <span>0%</span><span>50%</span><span>100%</span>
            </div>
            @if ($isExpired)
                <p class="mt-3 flex items-center gap-1.5 text-xs font-bold text-rose-600">
                    <i class="fas fa-lock text-[10px]"></i> L'échéance est dépassée. L'évolution de cet objectif est verrouillée.
                </p>
            @endif
        </div>

        {{-- Commentaire --}}
        <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Commentaire / objectif</p>
            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-slate-800">{{ $objectif->commentaire }}</p>
        </div>

        {{-- Footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
            <a href="{{ route('admin.objectifs.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.objectifs.edit', $objectif) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-700">
                <i class="fas fa-pen text-xs"></i> Modifier
            </a>
        </div>

        </div>
    </div>
</div>
@endsection
