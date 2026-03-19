@extends('layouts.app')

@section('title', 'Faitiere | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .faitiere-shell {
            display: grid;
            gap: 1.5rem;
        }

        .faitiere-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.75rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.20), transparent 35%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 32%),
                linear-gradient(135deg, #f8fafc 0%, #ffffff 52%, #ecfeff 100%);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        .faitiere-kpi {
            border-radius: 1.4rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: #fff;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.06);
        }

        .faitiere-list-card {
            border-radius: 1.5rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .faitiere-badge {
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

        .faitiere-list {
            display: grid;
            gap: 0.85rem;
        }

        .faitiere-list__item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #f8fafc;
            padding: 0.95rem 1rem;
        }

        .faitiere-list__meta {
            color: #64748b;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .faitiere-list__item {
                flex-direction: column;
            }
        }
    </style>
@endpush

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-7xl faitiere-shell">
            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (! $entite)
                <section class="admin-panel ent-window p-6 sm:p-8 text-center">
                    <div class="ent-window__bar" aria-hidden="true">
                        <span class="ent-window__dot ent-window__dot--danger"></span>
                        <span class="ent-window__dot ent-window__dot--warn"></span>
                        <span class="ent-window__dot ent-window__dot--ok"></span>
                        <span class="ent-window__label">Configuration initiale</span>
                    </div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration / Faitiere</p>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">Aucune faitiere configuree</h1>
                    <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-600">Le siege est unique. Configurez la faitiere pour rattacher ensuite les directions, services, secretaires et agents.</p>
                    <div class="mt-6 flex justify-center">
                        <a href="{{ route('admin.entites.create') }}" data-open-create-modal data-modal-title="Configurer la faitiere" class="ent-btn ent-btn-primary">
                            Configurer la faitiere
                        </a>
                    </div>
                </section>
            @else
                <section class="faitiere-hero p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <div class="flex flex-wrap gap-2">
                                <span class="faitiere-badge">Siege unique du reseau</span>
                                <span class="faitiere-badge">{{ $entite->ville }}</span>
                                <span class="faitiere-badge">{{ $entite->region }}</span>
                            </div>
                            <p class="mt-4 text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration / Faitiere</p>
                            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-slate-950">Faitiere</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">La faitiere centralise les directions, les services, les secretaires et les agents du siege. Cette page sert de tableau de bord principal du reseau.</p>
                            <div class="mt-5 flex flex-wrap gap-3 text-sm text-slate-600">
                                <span>DG: {{ $entite->directrice_generale_prenom }} {{ $entite->directrice_generale_nom }}</span>
                                <span>Assistante DG: {{ $entite->assistante_dg_prenom }} {{ $entite->assistante_dg_nom }}</span>
                                <span>DGA: {{ $entite->dga_prenom }} {{ $entite->dga_nom }}</span>
                                <span>PCA: {{ $entite->pca_prenom }} {{ $entite->pca_nom }}</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3 lg:justify-end">
                            <a href="{{ route('admin.entites.edit', $entite) }}" class="ent-btn ent-btn-primary">Modifier la faitiere</a>
                            <a href="{{ route('admin.entites.show', $entite) }}" class="ent-btn ent-btn-soft">Voir la fiche</a>
                            <form method="POST" action="{{ route('admin.entites.reset') }}" onsubmit="return confirm('Cette action va vider completement la faitiere et ses donnees liees. Continuer ?');">
                                @csrf
                                <button type="submit" class="ent-btn ent-btn-danger">Reinitialiser la liste</button>
                            </form>
                        </div>
                    </div>
                </section>

                <section class="faitiere-list-card p-5">
                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                            <p class="mt-2 font-semibold text-slate-900">Directions</p>
                            <div class="mt-3 flex gap-2">
                                <a href="#onglet-faitiere-directions" class="ent-btn ent-btn-soft">Voir</a>
                                <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter une direction" class="ent-btn ent-btn-primary">Ajouter</a>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                            <p class="mt-2 font-semibold text-slate-900">Services</p>
                            <div class="mt-3 flex gap-2">
                                <a href="#onglet-faitiere-services" class="ent-btn ent-btn-soft">Voir</a>
                                <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary">Ajouter</a>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                            <p class="mt-2 font-semibold text-slate-900">Secretaires</p>
                            <div class="mt-3 flex gap-2">
                                <a href="#onglet-faitiere-secretaires" class="ent-btn ent-btn-soft">Voir</a>
                                <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                            </div>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Onglet</p>
                            <p class="mt-2 font-semibold text-slate-900">Agents</p>
                            <div class="mt-3 flex gap-2">
                                <a href="#onglet-faitiere-agents" class="ent-btn ent-btn-soft">Voir</a>
                                <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="faitiere-kpi p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Directions</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $stats['directions'] }}</p>
                        <p class="mt-2 text-sm text-slate-600">Directions rattachees au siege.</p>
                    </article>
                    <article class="faitiere-kpi p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Services</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $stats['services'] }}</p>
                        <p class="mt-2 text-sm text-slate-600">Services actifs sous les directions du siege.</p>
                    </article>
                    <article class="faitiere-kpi p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secretaires</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $stats['secretaires'] }}</p>
                        <p class="mt-2 text-sm text-slate-600">Agents identifies avec une fonction de secretaire.</p>
                    </article>
                    <article class="faitiere-kpi p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Agents</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-950">{{ $stats['agents'] }}</p>
                        <p class="mt-2 text-sm text-slate-600">Effectif total rattache au siege.</p>
                    </article>
                </section>

                <section class="grid gap-4 lg:grid-cols-4">
                    <article id="onglet-faitiere-directions" class="faitiere-list-card p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Direction generale</p>
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $entite->directrice_generale_prenom }} {{ $entite->directrice_generale_nom }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $entite->directrice_generale_email }}</p>
                    </article>
                    <article id="onglet-faitiere-services" class="faitiere-list-card p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Assistante du DG</p>
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $entite->assistante_dg_prenom }} {{ $entite->assistante_dg_nom }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $entite->assistante_dg_email }}</p>
                    </article>
                    <article id="onglet-faitiere-secretaires" class="faitiere-list-card p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Direction generale adjointe</p>
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $entite->dga_prenom }} {{ $entite->dga_nom }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $entite->dga_email }}</p>
                    </article>
                    <article class="faitiere-list-card p-6">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">PCA</p>
                        <h2 class="mt-3 text-xl font-semibold text-slate-950">{{ $entite->pca_prenom }} {{ $entite->pca_nom }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $entite->pca_email }}</p>
                        <p class="mt-3 text-sm text-slate-500">Secretariat: {{ $entite->secretariat_telephone }}</p>
                    </article>
                </section>

                <section class="grid gap-4 xl:grid-cols-2">
                    <article class="faitiere-list-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Directions</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-950">Directions du siege</h2>
                            </div>
                            <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter une direction" class="ent-btn ent-btn-soft">Ajouter</a>
                        </div>
                        <div class="faitiere-list mt-5">
                            @forelse ($directions as $direction)
                                <div class="faitiere-list__item">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $direction->nom }}</p>
                                        <p class="faitiere-list__meta mt-1">Directeur: {{ $direction->directeur_nom }}</p>
                                    </div>
                                    <div class="text-right text-sm text-slate-500">
                                        <p>{{ $direction->services_count }} service(s)</p>
                                        <p>{{ $direction->secretariat_telephone }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucune direction rattachee a la faitiere.</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="faitiere-list-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Services</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-950">Services du siege</h2>
                            </div>
                            <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-soft">Ajouter</a>
                        </div>
                        <div class="faitiere-list mt-5">
                            @forelse ($services as $service)
                                <div class="faitiere-list__item">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $service->nom }}</p>
                                        <p class="faitiere-list__meta mt-1">Direction: {{ $service->direction?->nom ?? '-' }}</p>
                                    </div>
                                    <div class="text-right text-sm text-slate-500">
                                        <p>Chef de service</p>
                                        <p>{{ $service->chef_prenom }} {{ $service->chef_nom }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucun service rattache a la faitiere.</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="faitiere-list-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Secretaires</p>
                                <h2 class="mt-2 text-xl font-semibold text-slate-950">Secretaires identifies</h2>
                            </div>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-soft">Ajouter</a>
                        </div>
                        <div class="faitiere-list mt-5">
                            @forelse ($secretaires as $secretaire)
                                <div class="faitiere-list__item">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $secretaire->prenom }} {{ $secretaire->nom }}</p>
                                        <p class="faitiere-list__meta mt-1">{{ $secretaire->fonction }}</p>
                                    </div>
                                    <div class="text-right text-sm text-slate-500">
                                        <p>{{ $secretaire->service?->nom ?? '-' }}</p>
                                        <p>{{ $secretaire->numero_telephone }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucun secretaire identifie dans les agents de la faitiere.</p>
                            @endforelse
                        </div>
                    </article>

                    <article id="onglet-faitiere-agents" class="faitiere-list-card p-6">
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
                        <div class="faitiere-list mt-5">
                            @forelse ($agents as $agent)
                                <div class="faitiere-list__item">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                        <p class="faitiere-list__meta mt-1">{{ $agent->fonction }}</p>
                                    </div>
                                    <div class="text-right text-sm text-slate-500">
                                        <p>{{ $agent->service?->nom ?? '-' }}</p>
                                        <p>{{ $agent->service?->direction?->nom ?? '-' }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Aucun agent rattache a la faitiere.</p>
                            @endforelse
                        </div>
                    </article>
                </section>
            @endif
        </div>
    </div>
@endsection
