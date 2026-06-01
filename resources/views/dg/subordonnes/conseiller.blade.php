@extends('layouts.dg')

@section('title', $subordonne->name.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-300">
                    Espace DG · <a href="{{ route('dg.conseillers') }}" class="hover:text-white/80">Mes Conseillers</a>
                </p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $subordonne->name }}</h1>
                <p class="mt-0.5 text-sm text-emerald-100/80">Conseiller du DG</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('dg.conseillers') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>
    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @include('dg.subordonnes._dossier_tabs')

    </div>
    </div>
</div>
@endsection
