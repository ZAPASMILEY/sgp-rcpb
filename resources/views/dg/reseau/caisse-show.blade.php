@extends('layouts.dg')
@section('title', $caisse->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Caisses</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $caisse->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $caisse->quartier }}
                        @if ($caisse->delegationTechnique)
                            — Délégation : {{ $caisse->delegationTechnique->region }}
                        @endif
                        — {{ $caisse->agences->count() }} agence(s)
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('dg.caisses.pdf', array_merge(['caisse' => $caisse->id], request()->query())) }}"
                       class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                    </a>
                    <a href="{{ route('dg.caisses') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
        </header>

        {{-- Personnel direction --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-700 mb-4">Direction de la caisse</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Directeur de Caisse</p>
                    <p class="mt-2 font-bold text-slate-900">{{ $caisse->directeur_prenom }} {{ $caisse->directeur_nom }}</p>
                    <p class="text-xs text-slate-500">{{ $caisse->directeur_email }}</p>
                    <p class="text-xs text-slate-500">{{ $caisse->directeur_telephone }}</p>
                    <p class="mt-1 text-xs text-slate-400">Depuis {{ $caisse->directeur_date_debut_mois ?? '—' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Secrétaire</p>
                    <p class="mt-2 font-bold text-slate-900">{{ $caisse->secretaire_prenom }} {{ $caisse->secretaire_nom }}</p>
                    <p class="text-xs text-slate-500">{{ $caisse->secretaire_email }}</p>
                    <p class="text-xs text-slate-500">{{ $caisse->secretaire_telephone }}</p>
                    <p class="mt-1 text-xs text-slate-400">Depuis {{ $caisse->secretaire_date_debut_mois ?? '—' }}</p>
                </div>
            </div>
        </section>

        {{-- Agences --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">
                    Agences
                    <span class="ml-2 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-black text-sky-700">{{ $caisse->agences->count() }}</span>
                </h2>
            </div>
            @if ($caisse->agences->isEmpty())
                <p class="px-6 py-8 text-sm text-slate-400">Aucune agence rattachée.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($caisse->agences as $agence)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $agence->nom }}</p>
                                <p class="text-xs text-slate-400">Chef : {{ $agence->chef_nom ?? '—' }}</p>
                            </div>
                            <a href="{{ route('dg.agences.show', $agence) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Séparateur --}}
        <div class="flex items-center gap-3">
            <div class="h-px flex-1 bg-slate-200"></div>
            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Personnel évalué de la caisse</span>
            <div class="h-px flex-1 bg-slate-200"></div>
        </div>

        @include('dg.reseau._personnel_panel', [
            'filterRoute'  => 'dg.caisses.show',
            'filterParams' => ['caisse' => $caisse->id],
        ])

    </div>
</div>
@endsection
