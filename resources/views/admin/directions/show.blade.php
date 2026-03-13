@extends('layouts.app')

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <header class="admin-panel p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Direction</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $direction->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Entite: {{ $direction->entite?->nom }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.directions.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                        <a href="{{ route('admin.directions.edit', $direction) }}" class="ent-btn ent-btn-primary">Modifier</a>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-6 md:grid-cols-2">
                <article class="admin-panel p-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Directeur</p>
                    <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $direction->directeur_nom }}</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $direction->directeur_email }}</p>
                </article>

                <article class="admin-panel p-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Secretariat</p>
                    <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $direction->secretariat_telephone }}</h2>
                </article>
            </section>
        </div>
    </main>
@endsection
