@extends('layouts.dga')
@section('title', $user->name.' | Subordonnés DGA | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">Espace DGA · Subordonnés</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $user->name }}</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">{{ str_replace('_', ' ', $user->role) }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('dga.subordonnes.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Mes subordonnés
                </a>
            </div>
        </div>
    </div>
    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @php($currentSubordonneId = $user->id)
        @include('dga.subordonnes._dossier_tabs')

    </div>
    </div>
</div>
@endsection
