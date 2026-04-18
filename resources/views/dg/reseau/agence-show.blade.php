@extends('layouts.dg')
@section('title', $agence->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Agences</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $agence->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Caisse : {{ $agence->superviseurCaisse?->nom ?? '—' }}
                        — Délégation : {{ $agence->delegationTechnique?->region ?? '—' }}
                        — {{ $agence->guichets->count() }} guichet(s)
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('dg.agences.pdf', array_merge(['agence' => $agence->id], request()->query())) }}"
                       class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                    </a>
                    <a href="{{ route('dg.agences') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </header>

        {{-- Direction --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-700 mb-4">Direction</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef d'agence</p>
                    <p class="mt-2 font-bold text-slate-900">{{ $agence->chef_nom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $agence->chef_email }}</p>
                    <p class="text-xs text-slate-500">{{ $agence->chef_telephone }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Secrétaire</p>
                    <p class="mt-2 font-bold text-slate-900">{{ $agence->secretaire_nom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $agence->secretaire_email }}</p>
                    <p class="text-xs text-slate-500">{{ $agence->secretaire_telephone }}</p>
                </div>
            </div>
        </section>

        {{-- Guichets --}}
        @if ($agence->guichets->isNotEmpty())
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">
                    Guichets
                    <span class="ml-2 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-black text-violet-700">{{ $agence->guichets->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($agence->guichets as $guichet)
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <p class="font-bold text-slate-900">{{ $guichet->nom }}</p>
                            <p class="text-xs text-slate-400">Chef : {{ $guichet->chef_nom ?? '—' }} · {{ $guichet->chef_telephone ?? '' }}</p>
                        </div>
                        <a href="{{ route('dg.guichets.show', $guichet) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                            <i class="fas fa-eye mr-1"></i>Voir
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Séparateur --}}
        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Personnel évalué de l'agence</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        @include('dg.reseau._personnel_panel', [
            'filterRoute'  => 'dg.agences.show',
            'filterParams' => ['agence' => $agence->id],
        ])

    </div>
</div>
@endsection
