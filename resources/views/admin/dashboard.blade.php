@extends('layouts.app')

@section('title', 'Administration | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $modulesTotal = $entitesCount + $directionsCount + $servicesCount + $agentsCount + $objectifsCount + $evaluationsCount;
        $progressRate = $objectifsCount > 0 ? min(100, (int) round(($evaluationsCount / $objectifsCount) * 100)) : 0;
        $maxModule = max(1, $entitesCount, $directionsCount, $servicesCount, $agentsCount, $objectifsCount, $evaluationsCount);
        $bars = [
            'Faitiere' => max(14, (int) round(($entitesCount / $maxModule) * 100)),
            'Directions' => max(14, (int) round(($directionsCount / $maxModule) * 100)),
            'Services' => max(14, (int) round(($servicesCount / $maxModule) * 100)),
            'Agents' => max(14, (int) round(($agentsCount / $maxModule) * 100)),
            'Objectifs' => max(14, (int) round(($objectifsCount / $maxModule) * 100)),
            'Evaluations' => max(14, (int) round(($evaluationsCount / $maxModule) * 100)),
        ];
    @endphp

    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl clone-wrap">
            <section class="clone-hero">
                <div>
                    <p class="clone-eyebrow">Bonjour Administrateur</p>
                    <h1 class="clone-title">Mise à jour hebdomadaire de pilotage</h1>
                </div>
                <div class="clone-top-actions">
                    <input type="search" class="clone-search" placeholder="Rechercher un module">
                    <span class="clone-avatar">A</span>
                </div>
            </section>

            <section class="clone-cards">
                <article class="clone-card">
                    <p class="clone-card__label">Faitiere</p>
                    <p class="clone-card__value">{{ $entitesCount }}</p>
                    <p class="clone-card__sub">Siege configure</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Directions</p>
                    <p class="clone-card__value">{{ $directionsCount }}</p>
                    <p class="clone-card__sub">Directions actives</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Services</p>
                    <p class="clone-card__value">{{ $servicesCount }}</p>
                    <p class="clone-card__sub">Services opérationnels</p>
                </article>
                <article class="clone-card">
                    <p class="clone-card__label">Agents</p>
                    <p class="clone-card__value">{{ $agentsCount }}</p>
                    <p class="clone-card__sub">Agents enregistrés</p>
                </article>
            </section>

            <section class="clone-grid">
                <article class="clone-spot" style="--neo-progress: {{ $progressRate }};">
                    <p class="clone-spot__title">Taux de suivi</p>
                    <div class="clone-spot__gauge">{{ $progressRate }}%</div>
                    <p class="clone-spot__value">{{ number_format($modulesTotal, 0, ',', ' ') }}</p>
                    <p class="clone-spot__sub">Elements suivis</p>
                </article>

                <article class="clone-panel">
                    <div class="clone-panel__head">
                        <div>
                            <p class="clone-card__label">Indicateurs</p>
                            <h2 class="clone-panel__title">Aperçu des performances</h2>
                        </div>
                        <a href="{{ route('admin.objectifs.index') }}" class="ent-btn ent-btn-soft">Voir</a>
                    </div>
                    <div class="clone-bars">
                        @foreach ($bars as $label => $height)
                            @php($bucket = max(10, min(100, (int) (ceil($height / 10) * 10))))
                            <span class="neo-bar--{{ $bucket }}" title="{{ $label }}"></span>
                        @endforeach
                    </div>
                    <div class="clone-legend">
                        @foreach ($bars as $label => $height)
                            <span>{{ $label }}</span>
                        @endforeach
                    </div>
                </article>
            </section>

            <section class="clone-history">
                <p class="clone-card__label">Historique</p>
                <div class="clone-history__row">
                    <span>Objectifs</span>
                    <span>{{ $objectifsCount }}</span>
                    <span>Evaluations</span>
                    <span>{{ $evaluationsCount }}</span>
                    <span class="clone-badge">Valide</span>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-primary">Voir la faitiere</a>
                    <a href="{{ route('admin.objectifs.create') }}" class="ent-btn ent-btn-soft">Nouvel objectif</a>
                    <a href="{{ route('admin.evaluations.create') }}" class="ent-btn ent-btn-soft">Nouvelle evaluation</a>
                </div>
            </section>
        </div>
    </div>
@endsection
