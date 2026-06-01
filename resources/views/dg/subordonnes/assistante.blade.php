@extends('layouts.dg')

@section('title', 'Mon Assistante | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-300">Espace DG · Collaborateurs</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Mon Assistante</h1>
                @if ($subordonne)
                    <p class="mt-0.5 text-sm text-emerald-100/80">{{ $subordonne->name }}</p>
                @endif
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
