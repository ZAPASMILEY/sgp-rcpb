@extends('layouts.app')

@section('title', 'Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .dt-shell {
            display: grid;
            gap: 1.5rem;
        }

        .dt-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, 0.18), transparent 35%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.15), transparent 32%),
                linear-gradient(135deg, #f8fafc 0%, #ffffff 52%, #eef2ff 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        .dt-kpi {
            border-radius: 1.4rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: #fff;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
        }

        .dt-list-card {
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .dt-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.82);
            padding: 0.45rem 0.8rem;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #334155;
        }

        .dt-list {
            display: grid;
            gap: 0.85rem;
        }

        .dt-list__item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #f8fafc;
            padding: 0.95rem 1rem;
        }

        .dt-list__meta {
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .dt-list__item {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-7xl dt-shell">
            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Hero --}}
            <section class="dt-hero p-6 sm:p-8 lg:p-10">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap gap-2">
                            <span class="dt-badge">3 delegations techniques configurables</span>
                            <span class="dt-badge">{{ $directionsCount }} Directeur(s) Technique(s)</span>
                        </div>
                        <p class="mt-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration / Referentiel</p>
                        <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">Delegation Technique</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">La delegation technique regroupe l'ensemble des directeurs techniques, des services, des secretaires et des agents. Cette page sert de tableau de bord de la structure technique.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 lg:justify-end">
                        <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter un Directeur Technique" class="ent-btn ent-btn-primary">
                            Ajouter un D.T.
                        </a>
                    </div>
                </div>
            </section>

            <section class="dt-list-card p-5">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                        <p class="mt-2 font-semibold text-slate-900">Directeurs Techniques</p>
                        <p class="mt-1 text-sm text-slate-600">Total: {{ $directionsCount }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.delegations-techniques.directeurs.index') }}" class="ent-btn ent-btn-soft">Index</a>
                            <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter un Directeur Technique" class="ent-btn ent-btn-primary">Ajouter</a>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                        <p class="mt-2 font-semibold text-slate-900">Services</p>
                        <p class="mt-1 text-sm text-slate-600">Total: {{ $servicesCount }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.delegations-techniques.services.index') }}" class="ent-btn ent-btn-soft">Index</a>
                            <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary">Ajouter</a>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                        <p class="mt-2 font-semibold text-slate-900">Secretaires</p>
                        <p class="mt-1 text-sm text-slate-600">Total: {{ $secretariatsCount }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.delegations-techniques.secretaires.index') }}" class="ent-btn ent-btn-soft">Index</a>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                        <p class="mt-2 font-semibold text-slate-900">Agents</p>
                        <p class="mt-1 text-sm text-slate-600">Total: {{ $agentsCount }}</p>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.delegations-techniques.agents.index') }}" class="ent-btn ent-btn-soft">Index</a>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dt-list-card p-6">
                <div class="grid gap-5 lg:grid-cols-2">
                    <article>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Configuration</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-950">Delegations techniques configurees</h2>
                            </div>
                            <span class="dt-badge">{{ $delegations->count() }} / 3</span>
                        </div>
                        <div class="dt-list mt-5">
                            @forelse ($delegations as $delegation)
                                <div class="dt-list__item">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $delegation->region }} / {{ $delegation->ville }}</p>
                                        <p class="dt-list__meta mt-1">Secretariat: {{ $delegation->secretariat_telephone }}</p>
                                    </div>
                                    <div class="text-right text-sm text-slate-500">
                                        <p>{{ $delegation->directions_count }} directeur(s)</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucune delegation configuree.</p>
                            @endforelse
                        </div>
                    </article>

                    <article>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Ajouter une delegation</p>
                        <h2 class="mt-2 text-xl font-semibold text-slate-950">Region, ville et numero de secretariat</h2>

                        @if ($delegations->count() >= 3)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                Les 3 delegations techniques sont deja configurees.
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.delegations-techniques.store') }}" class="mt-4 grid gap-3">
                                @csrf
                                <div class="space-y-2">
                                    <label for="region" class="text-sm font-semibold text-slate-700">Region</label>
                                    <input id="region" name="region" type="text" value="{{ old('region') }}" required class="ent-input" placeholder="Ex: Centre">
                                </div>
                                <div class="space-y-2">
                                    <label for="ville" class="text-sm font-semibold text-slate-700">Ville</label>
                                    <input id="ville" name="ville" type="text" value="{{ old('ville') }}" required class="ent-input" placeholder="Ex: Ouagadougou">
                                </div>
                                <div class="space-y-2">
                                    <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                                    <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                                </div>
                                <button type="submit" class="ent-btn ent-btn-primary justify-center">Configurer la delegation</button>
                            </form>
                        @endif
                    </article>
                </div>
            </section>

            {{-- Lists --}}
            <section class="grid gap-4 xl:grid-cols-2">
                {{-- Directeurs Techniques --}}
                <article id="onglet-directeurs-techniques" class="dt-list-card p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Directeurs Techniques</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Liste des directeurs techniques</h2>
                        </div>
                        <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter un Directeur Technique" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                    <div class="dt-list mt-5">
                        @forelse ($directions as $direction)
                            <div class="dt-list__item">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $direction->nom }}</p>
                                    <p class="dt-list__meta mt-1">{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</p>
                                    @if ($direction->delegationTechnique)
                                        <p class="dt-list__meta">{{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-right text-sm text-slate-500">
                                        <p>{{ $direction->services_count }} service(s)</p>
                                        <p>{{ $direction->delegationTechnique?->secretariat_telephone ?? $direction->secretariat_telephone }}</p>
                                    </div>
                                    <a href="{{ route('admin.directions.show', $direction) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0 shrink-0" title="Voir">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" /><circle cx="12" cy="12" r="3" /></svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun directeur technique enregistre.</p>
                        @endforelse
                    </div>
                </article>

                {{-- Services récents --}}
                <article id="onglet-services" class="dt-list-card p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Services</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Services recents</h2>
                        </div>
                        <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                    <div class="dt-list mt-5">
                        @forelse ($recentServices as $service)
                            <div class="dt-list__item">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $service->nom }}</p>
                                    <p class="dt-list__meta mt-1">Direction: {{ $service->direction?->nom ?? '-' }}</p>
                                </div>
                                <div class="text-right text-sm text-slate-500">
                                    <p>Chef: {{ $service->chef_prenom }} {{ $service->chef_nom }}</p>
                                    <p>{{ $service->chef_telephone }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun service enregistre.</p>
                        @endforelse
                    </div>
                </article>

                {{-- Secrétaires --}}
                <article id="onglet-secretaires" class="dt-list-card p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secretaires</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Secretaires identifies</h2>
                        </div>
                        <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-soft">Ajouter</a>
                    </div>
                    <div class="dt-list mt-5">
                        @forelse ($secretaires as $secretaire)
                            <div class="dt-list__item">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $secretaire->prenom }} {{ $secretaire->nom }}</p>
                                    <p class="dt-list__meta mt-1">{{ $secretaire->fonction }}</p>
                                </div>
                                <div class="text-right text-sm text-slate-500">
                                    <p>{{ $secretaire->service?->nom ?? '-' }}</p>
                                    <p>{{ $secretaire->numero_telephone }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun secretaire identifie parmi les agents.</p>
                        @endforelse
                    </div>
                </article>

                {{-- Agents récents --}}
                <article id="onglet-agents" class="dt-list-card p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Agents</p>
                            <h2 class="mt-2 text-xl font-semibold text-slate-950">Effectif recent</h2>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                            <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-soft">Voir tous</a>
                        </div>
                    </div>
                    <div class="dt-list mt-5">
                        @forelse ($recentAgents as $agent)
                            <div class="dt-list__item">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                    <p class="dt-list__meta mt-1">{{ $agent->fonction }}</p>
                                </div>
                                <div class="text-right text-sm text-slate-500">
                                    <p>{{ $agent->service?->nom ?? '-' }}</p>
                                    <p>{{ $agent->service?->direction?->nom ?? '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Aucun agent enregistre.</p>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </div>
@endsection
