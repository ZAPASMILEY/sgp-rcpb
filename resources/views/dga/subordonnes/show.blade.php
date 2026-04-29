@extends('layouts.dga')
@section('title', $user->name.' | Subordonnés DGA | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <a href="{{ route('dga.subordonnes.index') }}" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-violet-600">
                        <i class="fas fa-arrow-left"></i> Mes subordonnés
                    </a>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ str_replace('_', ' ', $user->role) }}</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 font-black text-xl shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
        </header>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        @php($currentSubordonneId = $user->id)
        @include('dga.subordonnes._dossier_tabs')

    </div>
</div>
@endsection
