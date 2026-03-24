@extends('layouts.app')

@section('title', 'Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@push('head')
<style>
    .app-content-header { display: none !important; }
    .app-content { background: #ffffff !important; padding: 0 !important; }
    .app-content > .container-fluid { padding: 0 !important; max-width: 100% !important; }
    .app-main { background: #ffffff !important; }

    .dt-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 18% 14%, rgba(34, 197, 94, 0.08), transparent 24%),
            radial-gradient(circle at 82% 16%, rgba(34, 197, 94, 0.06), transparent 18%),
            linear-gradient(180deg, #ffffff 0%, #f0f9f4 48%, #f8fcfa 100%);
        color: #374151;
    }

    .dt-stage {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 1.5rem 0 2rem;
        position: relative;
    }

    .dt-panel {
        background: linear-gradient(180deg, rgba(240, 253, 250, 0.88), rgba(229, 250, 245, 0.86));
        border: 1px solid rgba(34, 197, 94, 0.20);
        border-radius: 1.1rem;
        box-shadow:
            inset 0 1px 0 rgba(34, 197, 94, 0.04),
            0 24px 40px rgba(34, 197, 94, 0.08);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }

    .dt-panel-soft {
        background: linear-gradient(180deg, rgba(240, 253, 250, 0.80), rgba(229, 250, 245, 0.78));
        border: 1px solid rgba(34, 197, 94, 0.16);
        border-radius: 0.95rem;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    .dt-muted {
        color: #6b7280;
        font-size: 0.7rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .dt-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.48rem 0.8rem;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.12);
        border: 1px solid rgba(34, 197, 94, 0.20);
        color: #16a34a;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .dt-heading {
        font-size: clamp(2rem, 3vw, 3rem);
        line-height: 1;
        font-weight: 900;
        color: #1f2937;
        text-shadow: 0 8px 24px rgba(34, 197, 94, 0.08);
    }

    .dt-copy {
        max-width: 43rem;
        color: #4b5563;
        line-height: 1.65;
    }

    .dt-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        border: 0;
        border-radius: 0.8rem;
        padding: 0.75rem 1rem;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
        transition: 0.15s ease;
        cursor: pointer;
    }

    .dt-btn-primary {
        background: linear-gradient(180deg, #22c55e, #16a34a);
        color: #ffffff;
        box-shadow: 0 10px 24px rgba(34, 197, 94, 0.30);
    }

    .dt-btn-soft {
        background: rgba(34, 197, 94, 0.10);
        color: #16a34a;
        border: 1px solid rgba(34, 197, 94, 0.20);
    }

    .dt-btn-danger {
        background: rgba(239, 68, 68, 0.10);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.20);
    }

    .dt-stat {
        font-size: 2.4rem;
        line-height: 1;
        font-weight: 900;
        color: #1f2937;
    }

    .dt-note {
        color: #22c55e;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .dt-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        color: #16a34a;
        font-size: 0.78rem;
        font-weight: 700;
    }

    .dt-status::before {
        content: '';
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 999px;
        background: #22c55e;
    }

    .dt-hero {
        min-height: 220px;
    }

    .dt-grid {
        display: grid;
        grid-template-columns: 1.35fr 1fr 1fr;
        gap: 0.85rem;
        align-items: stretch;
    }

    .dt-stack {
        display: grid;
        gap: 0.85rem;
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
        border: 1px solid rgba(255, 255, 255, 0.08);
        background: rgba(255, 255, 255, 0.05);
        padding: 1rem;
    }

    .dt-list__meta {
        color: #cbd5e1;
        font-size: 0.9rem;
    }

    .dt-inline-form summary {
        list-style: none;
        display: inline-flex;
    }

    .dt-inline-form summary::-webkit-details-marker {
        display: none;
    }

    .dt-table-wrap {
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(15, 23, 42, 0.18);
    }

    .dt-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .dt-table thead th {
        background: rgba(255,255,255,0.07);
        color: #94a3b8;
        font-size: 0.68rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        padding: 0.85rem 1rem;
    }

    .dt-table tbody td {
        padding: 0.95rem 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        color: #e2e8f0;
        font-size: 0.92rem;
        background: rgba(2, 6, 23, 0.10);
    }

    .dt-table tbody tr:hover td {
        background: rgba(255,255,255,0.04);
    }

    .dt-icon-btn {
        width: 2.1rem;
        height: 2.1rem;
        padding: 0;
    }

    @media (max-width: 1100px) {
        .dt-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .dt-stage {
            width: min(100% - 1rem, 1180px);
        }

        .dt-list__item {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="dt-page">
    <div class="dt-stage space-y-5">
        @if (session('status'))
            <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <section class="dt-panel dt-hero p-6 lg:p-8">
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap gap-2">
                        <span class="dt-badge">{{ $delegations->count() }} delegations configurees</span>
                        <span class="dt-badge">{{ $directionsCount }} Directeur(s) Technique(s)</span>
                        <span class="dt-badge">{{ $servicesCount }} Services</span>
                    </div>
                    <p class="dt-muted mt-4">Administration / Referentiel</p>
                    <h1 class="dt-heading mt-3">Delegation Technique</h1>
                    <p class="dt-copy mt-3 text-sm">
                        La delegation technique regroupe les directeurs techniques, les services, les secretaires et les agents de terrain. Cette page sert de cockpit central pour configurer les delegations et piloter la structure technique.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 xl:justify-end">
                    <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter un Directeur Technique" class="dt-btn dt-btn-primary">
                        Ajouter un D.T.
                    </a>
                </div>
            </div>
        </section>

        <section class="dt-grid">
            <article class="dt-panel p-5 lg:p-6">
                <p class="dt-muted">Delegations</p>
                <h2 class="mt-2 text-2xl font-bold text-white">Structures configurees</h2>
                <p class="dt-stat mt-3">{{ $delegations->count() }}</p>
                <p class="mt-3 text-sm text-slate-300">Maximum autorise: 3 delegations techniques actives.</p>
                <p class="dt-note mt-2">Couverture: {{ min($delegations->count(), 3) }} / 3</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @php $hasDelegationFormErrors = $errors->has('region') || $errors->has('ville') || $errors->has('secretariat_telephone'); @endphp
                    @if ($delegations->count() < 3)
                        <details class="dt-inline-form" @if ($hasDelegationFormErrors) open @endif>
                            <summary class="dt-btn dt-btn-primary">Ajouter une delegation</summary>
                            <form method="POST" action="{{ route('admin.delegations-techniques.store') }}" class="mt-4 grid min-w-[300px] gap-3 sm:min-w-[420px]">
                                @csrf
                                <div class="space-y-2">
                                    <label for="region" class="text-sm font-semibold text-slate-300">Region</label>
                                    <input id="region" name="region" type="text" value="{{ old('region') }}" required class="ent-input" placeholder="Ex: Centre">
                                </div>
                                <div class="space-y-2">
                                    <label for="ville" class="text-sm font-semibold text-slate-300">Ville</label>
                                    <input id="ville" name="ville" type="text" value="{{ old('ville') }}" required class="ent-input" placeholder="Ex: Ouagadougou">
                                </div>
                                <div class="space-y-2">
                                    <label for="secretariat_telephone" class="text-sm font-semibold text-slate-300">Numero du secretariat</label>
                                    <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                                </div>
                                <button type="submit" class="dt-btn dt-btn-primary justify-center">Configurer la delegation</button>
                            </form>
                        </details>
                    @else
                        <span class="dt-status">Configuration complete</span>
                    @endif
                </div>
            </article>

            <div class="dt-stack">
                <article class="dt-panel p-4">
                    <p class="dt-muted">Services</p>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div>
                            <p class="dt-stat">{{ $servicesCount }}</p>
                            <p class="mt-2 text-sm text-slate-300">Rattaches aux delegations techniques</p>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <a href="{{ route('admin.delegations-techniques.services.index') }}" class="dt-btn dt-btn-soft">Index</a>
                            <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="dt-btn dt-btn-primary">Ajouter</a>
                        </div>
                    </div>
                </article>
                <article class="dt-panel p-4">
                    <p class="dt-muted">Agents</p>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div>
                            <p class="dt-stat">{{ $agentsCount }}</p>
                            <p class="mt-2 text-sm text-slate-300">Personnel technique rattache</p>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <a href="{{ route('admin.delegations-techniques.agents.index') }}" class="dt-btn dt-btn-soft">Index</a>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="dt-btn dt-btn-primary">Ajouter</a>
                        </div>
                    </div>
                </article>
            </div>

            <div class="dt-stack">
                <article class="dt-panel p-4">
                    <p class="dt-muted">Secretariat</p>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div>
                            <p class="dt-stat">{{ $secretariatsCount }}</p>
                            <p class="mt-2 text-sm text-slate-300">Points de secretariat suivis</p>
                        </div>
                        <div class="flex gap-2 flex-wrap justify-end">
                            <a href="{{ route('admin.delegations-techniques.secretaires.index') }}" class="dt-btn dt-btn-soft">Index</a>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="dt-btn dt-btn-primary">Ajouter</a>
                        </div>
                    </div>
                </article>
                <article class="dt-panel-soft p-4 flex items-center justify-between gap-4">
                    <div>
                        <p class="dt-muted">Etat</p>
                        <p class="mt-2 text-sm text-white">Reseau technique actif et pret pour le pilotage</p>
                    </div>
                    <span class="dt-status">En ligne</span>
                </article>
            </div>
        </section>

        <section class="dt-panel p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div>
                    <p class="dt-muted">Liste Detaillee</p>
                    <h2 class="text-xl font-bold text-white mt-1">Delegations techniques configurees</h2>
                </div>
                <span class="dt-badge">{{ $delegations->count() }} / 3</span>
            </div>

            <div class="dt-list">
                @forelse ($delegations as $delegation)
                    <div class="dt-list__item">
                        <div>
                            <p class="font-semibold text-slate-900 text-lg">{{ $delegation->region }} / {{ $delegation->ville }}</p>
                            <p class="dt-list__meta mt-2">Secretariat: {{ $delegation->secretariat_telephone }}</p>
                            <p class="mt-1 text-sm text-slate-400">{{ $delegation->directions_count }} directeur(s) rattache(s)</p>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center justify-end gap-2 flex-wrap">
                                <a href="{{ route('admin.delegations-techniques.services.index', ['delegation_id' => $delegation->id]) }}" class="dt-btn dt-btn-soft">Services</a>
                                <a href="{{ route('admin.delegations-techniques.edit', $delegation) }}" class="dt-btn dt-btn-soft">Modifier</a>
                                <form method="POST" action="{{ route('admin.delegations-techniques.destroy', $delegation) }}" onsubmit="return confirm('Supprimer cette delegation technique ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dt-btn dt-btn-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Aucune delegation configuree.</p>
                @endforelse
            </div>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <section class="dt-panel p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="dt-muted">Flux Recent</p>
                        <h2 class="text-xl font-bold text-white mt-1">Services recents</h2>
                    </div>
                    <a href="{{ route('admin.delegations-techniques.services.index') }}" class="dt-btn dt-btn-soft">Voir tout</a>
                </div>
                <div class="dt-list">
                    @forelse ($recentServices as $service)
                        <div class="dt-panel-soft p-4 flex items-center justify-between gap-4">
                            <div>
                                <p class="font-semibold text-white">{{ $service->nom }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $service->direction?->nom ?? 'Sans direction' }}</p>
                            </div>
                            <a href="{{ route('admin.delegations-techniques.services.index') }}" class="dt-btn dt-btn-soft dt-icon-btn" title="Ouvrir">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Aucun service recent.</p>
                    @endforelse
                </div>
            </section>

            <section class="dt-panel p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <p class="dt-muted">Equipe</p>
                        <h2 class="text-xl font-bold text-white mt-1">Secretaires et agents recents</h2>
                    </div>
                    <a href="{{ route('admin.delegations-techniques.agents.index') }}" class="dt-btn dt-btn-soft">Voir tout</a>
                </div>
                <div class="space-y-4">
                    <div class="dt-list">
                        @forelse ($secretaires as $secretaire)
                            <div class="dt-panel-soft p-4 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $secretaire->prenom }} {{ $secretaire->nom }}</p>
                                    <p class="mt-1 text-sm text-slate-300">{{ $secretaire->service?->nom ?? 'Sans service' }}</p>
                                </div>
                                <span class="dt-badge">Secretaire</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Aucun secretaire recent.</p>
                        @endforelse
                    </div>

                    <div class="dt-list">
                        @forelse ($recentAgents as $agent)
                            <div class="dt-panel-soft p-4 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-white">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                    <p class="mt-1 text-sm text-slate-300">{{ $agent->service?->nom ?? 'Sans service' }}</p>
                                </div>
                                <span class="dt-badge">Agent</span>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Aucun agent recent.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </section>
    </div>
</div>
@endsection
