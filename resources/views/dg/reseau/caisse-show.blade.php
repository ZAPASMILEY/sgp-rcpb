@extends('layouts.dg')
@section('title', $caisse->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="mx-auto max-w-5xl flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Caisses</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $caisse->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $caisse->quartier }} — {{ $caisse->delegationTechnique?->region }}</p>
                </div>
                <a href="{{ route('dg.caisses') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        {{-- Personnel --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-700 mb-4">Personnel</h2>
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
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Agences <span class="ml-2 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-black text-sky-700">{{ $caisse->agences->count() }}</span></h2>
            </div>
            @if ($caisse->agences->isEmpty())
                <p class="px-6 py-8 text-sm text-slate-400">Aucune agence rattachée.</p>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($caisse->agences as $agence)
                        <div class="flex items-center justify-between px-6 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $agence->nom }}</p>
                                <p class="text-xs text-slate-400">Chef : {{ $agence->chef_nom ?? '—' }} — {{ $agence->agents->count() }} agent(s) · {{ $agence->guichets->count() }} guichet(s)</p>
                            </div>
                            <a href="{{ route('dg.agences.show', $agence) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs"><i class="fas fa-eye mr-1"></i>Voir</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
