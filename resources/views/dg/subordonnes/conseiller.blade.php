@extends('layouts.dg')

@section('title', $subordonne->name.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Espace DG / <a href="{{ route('dg.conseillers') }}" class="hover:underline">Mes Conseillers</a>
                    </p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $subordonne->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Conseiller du DG</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700 font-black text-xl shadow-sm">
                    {{ strtoupper(substr($subordonne->name, 0, 1)) }}
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @include('dg.subordonnes._dossier_tabs')

    </div>
</div>
@endsection
