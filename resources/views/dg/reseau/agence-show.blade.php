@extends('layouts.dg')
@section('title', $agence->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="mx-auto max-w-5xl flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Agences</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $agence->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Caisse : {{ $agence->superviseurCaisse?->nom ?? '—' }} — Délégation : {{ $agence->delegationTechnique?->region ?? '—' }}</p>
                </div>
                <a href="{{ route('dg.agences') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        {{-- Personnel direction --}}
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
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Guichets <span class="ml-2 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-black text-violet-700">{{ $agence->guichets->count() }}</span></h2>
            </div>
            @if ($agence->guichets->isEmpty())
                <p class="px-6 py-8 text-sm text-slate-400">Aucun guichet enregistré.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($agence->guichets as $guichet)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $guichet->nom }}</p>
                                <p class="text-xs text-slate-400">Chef : {{ $guichet->chef_nom ?? '—' }} · {{ $guichet->chef_telephone ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Agents --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Agents <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-black text-slate-600">{{ $agence->agents->count() }}</span></h2>
            </div>
            @if ($agence->agents->isEmpty())
                <p class="px-6 py-8 text-sm text-slate-400">Aucun agent enregistré.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($agence->agents as $agent)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                <p class="text-xs text-slate-400">{{ $agent->fonction ?? '—' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
