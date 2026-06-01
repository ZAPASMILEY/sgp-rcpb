@extends('layouts.dg')

@section('title', 'Mes Conseillers | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-300">Espace DG · Collaborateurs</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Mes Conseillers</h1>
                <p class="mt-0.5 text-sm text-emerald-100/80">{{ $conseillers->count() }} conseiller(s) rattaché(s)</p>
            </div>
        </div>
    </div>
    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if ($conseillers->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucun conseiller configuré.</p>
                <p class="mt-1 text-xs text-slate-400">Contactez l'administrateur pour créer les comptes.</p>
            </div>
        @else
            <div class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Liste des conseillers</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($conseillers as $conseiller)
                        <a href="{{ route('dg.conseillers.show', $conseiller) }}"
                           class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-slate-50/60 px-5 py-4 shadow-sm transition hover:border-cyan-300 hover:shadow-md">
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
            </div>
        @endif

    </div>
    </div>
</div>
@endsection
