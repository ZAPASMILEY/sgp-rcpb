@extends('layouts.pca')

@section('title', 'Tableau de bord PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">

            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Tableau de bord</h1>
                        <p class="mt-2 text-sm text-slate-600">Entite : <strong>{{ $entite->nom }}</strong> &mdash; {{ $entite->ville }}</p>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Stats cards --}}
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Objectifs entite</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $objectifsEntiteCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Assignes a votre entite.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-primary">Voir</a>
                        <a href="{{ route('pca.objectifs.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Objectifs directeurs</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $objectifsDirecteursCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Assignes aux directeurs de votre entite.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-primary">Voir</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Evaluations entite</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $evaluationsEntiteCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Evaluations de votre entite.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-primary">Voir</a>
                        <a href="{{ route('pca.evaluations.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Evaluations directeurs</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $evaluationsDirecteursCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Evaluations des directeurs de votre entite.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-primary">Voir</a>
                    </div>
                </article>
            </section>

            {{-- Entite info --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Informations de l'entite</h2>
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->nom }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Ville</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->ville ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur(trice) General(e)</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ trim($entite->directrice_generale_prenom.' '.$entite->directrice_generale_nom) ?: '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Email DG</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->directrice_generale_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Secretariat</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->secretariat_telephone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Directions rattachees</dt>
                        <dd class="mt-1 text-slate-900">{{ $directions->count() }}</dd>
                    </div>
                </dl>

                @if ($directions->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-2">Directions</p>
                        <ul class="space-y-1">
                            @foreach ($directions as $direction)
                                <li class="text-sm text-slate-700">
                                    <span class="font-medium">{{ $direction->nom }}</span>
                                    @if ($direction->directeur_nom)
                                        <span class="text-slate-400"> — {{ $direction->directeur_nom }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            {{-- Objectifs en cours --}}
            @if ($objectifsPendingCount > 0)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    <strong>{{ $objectifsPendingCount }}</strong> objectif(s) en cours de realisation pour votre entite ou ses directeurs.
                    <a href="{{ route('pca.objectifs.index') }}" class="ml-2 underline">Voir les objectifs</a>
                </div>
            @endif

            {{-- Recent evals --}}
            @if ($recentEvaluations->isNotEmpty())
                <section class="admin-panel px-6 py-6 lg:px-8">
                    <h2 class="text-base font-semibold text-slate-800 mb-4">Evaluations recentes</h2>
                    <ul class="space-y-2">
                        @foreach ($recentEvaluations as $eval)
                            <li class="flex items-center justify-between gap-4 rounded-xl border border-slate-100 px-4 py-3 text-sm">
                                <div>
                                    <span class="font-medium text-slate-900">
                                        {{ $eval->evaluable instanceof \App\Models\Entite ? $eval->evaluable->nom : ($eval->evaluable->directeur_nom ?? $eval->evaluable->nom ?? '—') }}
                                    </span>
                                    <span class="ml-2 text-slate-500">
                                        {{ $eval->date_debut->format('d/m/Y') }} – {{ $eval->date_fin->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        @if ($eval->statut === 'valide') bg-emerald-100 text-emerald-700
                                        @elseif ($eval->statut === 'soumis') bg-amber-100 text-amber-700
                                        @else bg-slate-100 text-slate-600 @endif">
                                        {{ ucfirst($eval->statut) }}
                                    </span>
                                    <a href="{{ route('pca.evaluations.show', $eval) }}" class="ent-btn ent-btn-soft text-xs">Voir</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

        </div>
    </div>
@endsection
