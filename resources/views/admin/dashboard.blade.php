@extends('layouts.app')

@section('title', 'Administration | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Tableau de bord administrateur</h1>
                        <p class="mt-2 text-sm text-slate-600">Acces rapide aux modules Entites, Directions, Services, Agents et Objectifs.</p>
                    </div>
                </div>
            </header>

            <section class="dashboard-grid grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Entites</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $entitesCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total d'entites enregistrees.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.entites.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Directions</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $directionsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total de directions enregistrees.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.directions.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.directions.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Services</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $servicesCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total de services enregistres.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.services.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.services.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Agents</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $agentsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total d'agents enregistres.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.agents.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Objectifs</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $objectifsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total d'objectifs enregistres.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.objectifs.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.objectifs.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

                <article class="admin-panel dashboard-card p-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Evaluations</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $evaluationsCount }}</p>
                    <p class="mt-2 text-sm text-slate-600">Nombre total d'evaluations enregistrees.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.evaluations.index') }}" class="ent-btn ent-btn-primary">Ouvrir</a>
                        <a href="{{ route('admin.evaluations.create') }}" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                </article>

            </section>
        </div>
    </div>
@endsection
