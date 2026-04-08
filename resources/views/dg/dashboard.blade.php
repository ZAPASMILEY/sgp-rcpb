@extends('layouts.dg')

@section('title', 'Tableau de bord DG | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Tableau de bord DG</h1>
                        <p class="mt-2 text-sm text-slate-600">Suivi de vos propres fiches et de celles assignées à vos subordonnés (DGA, secrétaire).</p>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Tableau de bord DG vide pour l'instant -->
        </div>
    </div>
@endsection
