@extends('layouts.dg')
@section('title', $guichet->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Guichets</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $guichet->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Agence : {{ $guichet->agence?->nom ?? '—' }}
                        — Caisse : {{ $guichet->agence?->superviseurCaisse?->nom ?? '—' }}
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('dg.guichets.pdf', array_merge(['guichet' => $guichet->id], request()->query())) }}"
                       class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                    </a>
                    <a href="{{ route('dg.guichets') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </header>

        {{-- Info guichet --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-700 mb-4">Responsable</h2>
            <div class="max-w-sm rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef de guichet</p>
                <p class="mt-2 font-bold text-slate-900">{{ $guichet->chef_nom ?? '—' }}</p>
                @if ($guichet->chef_email)
                    <p class="text-xs text-slate-500">{{ $guichet->chef_email }}</p>
                @endif
                @if ($guichet->chef_telephone)
                    <p class="text-xs text-slate-500">{{ $guichet->chef_telephone }}</p>
                @endif
            </div>
            @if ($guichet->agence)
                <p class="mt-4 text-xs text-slate-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    Les évaluations affichées ci-dessous concernent les agents de l'agence
                    <strong>{{ $guichet->agence->nom }}</strong> à laquelle ce guichet est rattaché.
                </p>
            @endif
        </section>

        {{-- Séparateur --}}
        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Personnel évalué — Agence {{ $guichet->agence?->nom ?? '' }}</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        @include('dg.reseau._personnel_panel', [
            'filterRoute'  => 'dg.guichets.show',
            'filterParams' => ['guichet' => $guichet->id],
        ])

    </div>
</div>
@endsection
