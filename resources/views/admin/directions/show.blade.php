@extends('layouts.app')

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php $isFaitiereDirection = is_null($direction->delegation_technique_id); @endphp
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <header class="admin-panel p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $isFaitiereDirection ? 'Faitiere / Direction' : 'Delegation Technique / Direction' }}
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $direction->nom }}</h1>
                        @if ($direction->delegationTechnique)
                            <p class="mt-2 text-sm text-slate-600">{{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}</p>
                        @else
                            <p class="mt-2 text-sm text-slate-600">Direction rattachee a la Faitiere</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ $isFaitiereDirection ? route('admin.entites.directions.index') : route('admin.delegations-techniques.index') }}" class="ent-btn ent-btn-soft">Retour</a>
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
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        {{ $isFaitiereDirection ? 'Directeur de direction' : 'Directeur Technique' }}
                    </p>
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
                    @elseif ($direction->secretariat_telephone)
                        <p class="mt-3 text-sm text-slate-500">Secretariat direction: {{ $direction->secretariat_telephone }}</p>
                    @endif
                </article>
            </section>

            <!-- EVALUTIONS -->
            <section class="admin-panel p-6">
                <h2 class="mb-4 text-xl font-semibold text-slate-950">Évaluations</h2>
                @if ($evaluations->count() > 0)
                    <div class="space-y-3">
                        @foreach ($evaluations as $eval)
                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div>
                                <p class="font-medium text-slate-900">Note: <span class="text-lg font-bold text-emerald-600">{{ number_format($eval->note_finale, 2) }}</span></p>
                                <p class="text-xs text-slate-500 mt-1">{{ $eval->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $eval->statut }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">Aucune évaluation enregistrée.</p>
                @endif
            </section>

            <!-- OBJECTIFS -->
            <section class="admin-panel p-6">
                <h2 class="mb-4 text-xl font-semibold text-slate-950">Objectifs</h2>
                @if ($objectifs->count() > 0)
                    <div class="space-y-3">
                        @foreach ($objectifs as $obj)
                        @php $percentage = $obj->avancement_percentage ?? 0; @endphp
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-slate-900">{{ $obj->nom ?? $obj->titre ?? 'Objectif' }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Année: {{ $obj->annee ?? '-' }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-700">
                                    {{ $percentage }}%
                                </span>
                            </div>
                            <div class="mt-3 h-2 w-full rounded-full bg-slate-200">
                                <progress class="h-2 w-full rounded-full" value="{{ $percentage }}" max="100"></progress>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-500">Aucun objectif assigné.</p>
                @endif
            </section>
        </div>
    </main>
@endsection

