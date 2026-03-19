@extends('layouts.app')

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <header class="admin-panel p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Direction</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $direction->nom }}</h1>
                        @if ($direction->delegationTechnique)
                            <p class="mt-2 text-sm text-slate-600">{{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}</p>
                        @endif
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
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Directeur Technique</p>
                    <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</h2>
                    <p class="mt-2 text-sm text-slate-600">{{ $direction->directeur_email }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $direction->directeur_numero }}</p>
                    @if ($direction->delegationTechnique)
                        <p class="mt-1 text-sm text-slate-500">Delegation: {{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}</p>
                    @endif
                </article>

                <article class="admin-panel p-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Secretaire de direction</p>
                    @if ($direction->secretaire_prenom || $direction->secretaire_nom)
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $direction->secretaire_prenom }} {{ $direction->secretaire_nom }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $direction->secretaire_email }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $direction->secretaire_telephone }}</p>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Aucune secretaire renseignee.</p>
                    @endif
                    @if ($direction->delegationTechnique)
                        <p class="mt-3 text-sm text-slate-500">Secretariat delegation: {{ $direction->delegationTechnique->secretariat_telephone }}</p>
                    @endif
                </article>
            </section>
        </div>
    </main>
@endsection

