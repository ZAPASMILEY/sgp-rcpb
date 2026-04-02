@extends('layouts.app')

@section('title', 'Statistiques | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $distribution = [
            'Entites' => $entitesCount,
            'Directions' => $directionsCount,
            'Services' => $servicesCount,
            'Caisses' => $caissesCount,
            'Agences' => $agencesCount,
            'Guichets' => $guichetsCount,
            'Agents' => $agentsCount,
            'Objectifs' => $objectifsCount,
            'Evaluations' => $evaluationsCount,
        ];
        $maxValue = max(1, ...array_values($distribution));
    @endphp

    <div class="admin-shell stats-page min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage / Statistiques</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Statistiques globales</h1>
                        <p class="mt-2 text-sm text-slate-600">Vue consolidee des structures, objectifs et evaluations pour {{ $selectedYear }}.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.statistiques.index') }}" class="grid gap-2 sm:grid-cols-[minmax(0,180px)_auto] sm:items-end">
                        <div>
                            <label for="annee" class="text-sm font-semibold text-slate-700">Annee</label>
                            <select id="annee" name="annee" class="mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $selectedYear)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700">Filtrer</button>
                    </form>
                </div>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Caisses</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $caissesCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Creees sur l'annee selectionnee.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Agences</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $agencesCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Creees sur l'annee selectionnee.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Guichets</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $guichetsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Crees sur l'annee selectionnee.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Personnel</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $agentsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Selon la date de debut de fonction.</p>
                </article>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Objectifs</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $objectifsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Total d'objectifs crees.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Termines</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $objectifsTermines }}</p>
                    <p class="mt-2 text-sm text-slate-600">Objectifs a 100% ou plus.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">En cours</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ $objectifsEnCours }}</p>
                    <p class="mt-2 text-sm text-slate-600">Objectifs non acheves.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Avancement moyen</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $avancementMoyen }}%</p>
                    <p class="mt-2 text-sm text-slate-600">Moyenne de progression des objectifs.</p>
                </article>
            </section>

            <section class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
                <article class="admin-panel p-5">
                    <h2 class="text-base font-semibold text-slate-900">Repartition des volumes</h2>
                    <div class="clone-bars mt-4">
                        @foreach ($distribution as $label => $value)
                            @php($height = max(10, min(100, (int) ceil(($value / $maxValue) * 100 / 10) * 10)))
                            <span class="neo-bar--{{ $height }}" title="{{ $label }}: {{ $value }}"></span>
                        @endforeach
                    </div>
                    <div class="clone-legend mt-3">
                        @foreach ($distribution as $label => $value)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </article>

                <article class="admin-panel p-5">
                    <h2 class="text-base font-semibold text-slate-900">Evaluations par statut</h2>
                    <ul class="neo-list">
                        @foreach ($evaluationsByStatut as $statut => $total)
                            <li>
                                <span class="text-sm font-medium text-slate-700">{{ $statut }}</span>
                                <span class="neo-pill neo-pill--draft">{{ $total }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>

                <article class="admin-panel p-5">
                    <h2 class="text-base font-semibold text-slate-900">Personnel par sexe</h2>
                    <ul class="neo-list">
                        @foreach ($agentsBySexe as $label => $total)
                            <li>
                                <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                                <span class="neo-pill neo-pill--draft">{{ $total }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            </section>
        </div>
    </div>
@endsection
