@extends('layouts.pca')

@section('title', 'Tableau de bord PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $pcaTotal = $objectifsEntiteCount + $objectifsDirecteursCount + $evaluationsEntiteCount + $evaluationsDirecteursCount;
        $objectifTotal = max(1, $objectifsEntiteCount + $objectifsDirecteursCount);
        $evaluationTotal = $evaluationsEntiteCount + $evaluationsDirecteursCount;
        $progressRate = min(100, (int) round(($evaluationTotal / $objectifTotal) * 100));
        $maxPca = max(1, $objectifsEntiteCount, $objectifsDirecteursCount, $evaluationsEntiteCount, $evaluationsDirecteursCount);
        $pcaBars = [
            'Obj entite' => max(16, (int) round(($objectifsEntiteCount / $maxPca) * 100)),
            'Obj directeurs' => max(16, (int) round(($objectifsDirecteursCount / $maxPca) * 100)),
            'Eval entite' => max(16, (int) round(($evaluationsEntiteCount / $maxPca) * 100)),
            'Eval directeurs' => max(16, (int) round(($evaluationsDirecteursCount / $maxPca) * 100)),
        ];
    @endphp

    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl clone-wrap">

            <section class="clone-hero">
                <div>
                    <p class="clone-eyebrow">Bonjour PCA</p>
                    <h1 class="clone-title">Tableau de bord de {{ $entite->nom }}</h1>
                </div>
                <div class="clone-top-actions">
                    <input type="search" class="clone-search" placeholder="Rechercher une direction">
                    <span class="clone-avatar">P</span>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="clone-cards">
                <article class="clone-card">
                    <p class="clone-card__label">Obj entite</p>
                    <p class="clone-card__value">{{ $objectifsEntiteCount }}</p>
                    <p class="clone-card__sub">Objectifs de l'entité</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Obj directeurs</p>
                    <p class="clone-card__value">{{ $objectifsDirecteursCount }}</p>
                    <p class="clone-card__sub">Objectifs des directeurs</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Eval entite</p>
                    <p class="clone-card__value">{{ $evaluationsEntiteCount }}</p>
                    <p class="clone-card__sub">Évaluations de l'entité</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Eval directeurs</p>
                    <p class="clone-card__value">{{ $evaluationsDirecteursCount }}</p>
                    <p class="clone-card__sub">Évaluations des directeurs</p>
                </article>
            </section>

            <section class="clone-grid">
                <article class="clone-spot" style="--neo-progress: {{ $progressRate }};">
                    <p class="clone-spot__title">Taux de suivi</p>
                    <div class="clone-spot__gauge">{{ $progressRate }}%</div>
                    <p class="clone-spot__value">{{ number_format($pcaTotal, 0, ',', ' ') }}</p>
                    <p class="clone-spot__sub">Elements suivis</p>
                </article>

                <article class="clone-panel">
                    <div class="clone-panel__head">
                        <div>
                            <p class="clone-card__label">Indicateurs</p>
                            <h2 class="clone-panel__title">Aperçu d'activité PCA</h2>
                        </div>
                        <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft">Voir</a>
                    </div>
                    <div class="clone-bars">
                        @foreach ($pcaBars as $label => $height)
                            @php($bucket = max(10, min(100, (int) (ceil($height / 10) * 10))))
                            <span class="neo-bar--{{ $bucket }}" title="{{ $label }}"></span>
                        @endforeach
                    </div>
                    <div class="clone-legend">
                        @foreach ($pcaBars as $label => $height)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="clone-history">
                <p class="clone-card__label">Historique</p>
                <div class="clone-history__row">
                    <span>Objectifs</span>
                    <span>{{ $objectifTotal }}</span>
                    <span>Évaluations</span>
                    <span>{{ $evaluationTotal }}</span>
                    <span class="clone-badge">Validé</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Nouvel objectif" class="ent-btn ent-btn-primary">Nouvel objectif</a>
                    <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Nouvelle evaluation" class="ent-btn ent-btn-soft">Nouvelle évaluation</a>
                    <a href="{{ route('pca.settings.edit') }}" class="ent-btn ent-btn-soft">Paramètres</a>
                </div>
            </section>

            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Informations de l'entité</h2>
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

            @if ($objectifsPendingCount > 0)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    <strong>{{ $objectifsPendingCount }}</strong> objectif(s) en cours de réalisation pour votre entité ou ses directeurs.
                    <a href="{{ route('pca.objectifs.index') }}" class="ml-2 underline">Voir les objectifs</a>
                </div>
            @endif

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

