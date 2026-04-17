@extends('layouts.dg')

@section('title', 'Mes Conseillers | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-5xl flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Collaborateurs</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Mes Conseillers</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $conseillers->count() }} conseiller(s) rattaché(s)</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700 shadow-sm">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </header>

        @if ($conseillers->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucun conseiller configuré.</p>
                <p class="mt-1 text-xs text-slate-400">Contactez l'administrateur pour créer les comptes.</p>
            </div>
        @else
            <section class="admin-panel px-6 py-6">
                <h2 class="text-lg font-black text-slate-900">Liste des conseillers</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($conseillers as $conseiller)
                        <a href="{{ route('dg.conseillers.show', $conseiller) }}"
                           class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-cyan-300 hover:shadow-md">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700 font-black text-lg">
                                {{ strtoupper(substr($conseiller->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-bold text-slate-900">{{ $conseiller->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $conseiller->email }}</p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-xs text-slate-300"></i>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
@endsection
