@extends('layouts.pca')

@section('title', 'Statistiques PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $distribution = [
            'Objectifs entite' => $objectifsEntiteCount,
            'Objectifs directeurs' => $objectifsDirecteursCount,
            'Evaluations entite' => $evaluationsEntiteCount,
            'Evaluations directeurs' => $evaluationsDirecteursCount,
        ];
        $maxValue = max(1, ...array_values($distribution));
    @endphp

    <div class="admin-shell stats-page min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage / Statistiques</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Statistiques de {{ $entite->nom }}</h1>
                <p class="mt-2 text-sm text-slate-600">Suivi des objectifs et evaluations de l'entite et des directeurs.</p>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Objectifs total</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $objectifsTotal }}</p>
                    <p class="mt-2 text-sm text-slate-600">Entite + directeurs.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Objectifs termines</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $objectifsTermines }}</p>
                    <p class="mt-2 text-sm text-slate-600">Progression complete.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Avancement moyen</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $avancementMoyen }}%</p>
                    <p class="mt-2 text-sm text-slate-600">Moyenne globale de progression.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Evaluations total</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $evaluationsEntiteCount + $evaluationsDirecteursCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Entite + directeurs.</p>
                </article>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Plus forte note du DG</p>
                    <p class="mt-2 text-3xl font-bold text-indigo-700">{{ $meilleureNoteDirecteurGeneral }}%</p>
                    <p class="mt-2 text-sm text-slate-600">Meilleure note finale sur les evaluations de l'entite.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Evaluations soumises</p>
                    <p class="mt-2 text-3xl font-bold text-amber-700">{{ $evaluationsSoumises }}</p>
                    <p class="mt-2 text-sm text-slate-600">En attente de validation.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Evaluations acceptees</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $evaluationsAcceptees }}</p>
                    <p class="mt-2 text-sm text-slate-600">Evaluations validees.</p>
                </article>
                <article class="admin-panel p-4">
                    <p class="text-xs uppercase tracking-[0.15em] text-slate-500">Evaluations rejetees</p>
                    <p class="mt-2 text-3xl font-bold text-rose-700">{{ $evaluationsRejetees }}</p>
                    <p class="mt-2 text-sm text-slate-600">Statuts rejetes/rejetee.</p>
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
            </section>
        </div>
    </div>
@endsection
