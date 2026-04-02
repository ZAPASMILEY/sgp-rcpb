@extends('layouts.app')

@section('title', 'Faitiere | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Faitiere')

@section('content')
<div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(96,165,250,0.18),_transparent_30%),linear-gradient(180deg,_#f8fbff_0%,_#eef4ff_100%)] px-4 pb-8 pt-2 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-7">
        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-[24px] border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 5000);</script>
        @endif

        @if ($entite)
            @php
                $kpis = [
                    ['key' => 'directions', 'label' => 'Directions', 'count' => $stats['directions'], 'icon' => 'fas fa-sitemap', 'gradient' => 'from-sky-500 via-blue-500 to-blue-600', 'soft' => 'bg-blue-50/25', 'list' => route('admin.entites.directions.index'), 'create' => route('admin.entites.directions.create')],
                    ['key' => 'services', 'label' => 'Services', 'count' => $stats['services'], 'icon' => 'fas fa-briefcase', 'gradient' => 'from-emerald-400 via-teal-400 to-cyan-500', 'soft' => 'bg-emerald-50/25', 'list' => route('admin.services.index'), 'create' => route('admin.services.create')],
                    ['key' => 'secretaires', 'label' => 'Secretaires', 'count' => $stats['secretaires'], 'icon' => 'fas fa-wallet', 'gradient' => 'from-fuchsia-400 via-pink-500 to-pink-600', 'soft' => 'bg-fuchsia-50/25', 'list' => route('admin.entites.secretaires.index'), 'create' => route('admin.entites.secretaires.index')],
                    ['key' => 'agents', 'label' => 'Agents', 'count' => $stats['agents'], 'icon' => 'fas fa-users', 'gradient' => 'from-amber-400 via-orange-400 to-orange-500', 'soft' => 'bg-orange-50/25', 'list' => route('admin.agents.index'), 'create' => route('admin.agents.create')],
                ];

                $tabs = [
                    'directions' => ['label' => 'Directions', 'icon' => 'fas fa-location-dot'],
                    'services' => ['label' => 'Services', 'icon' => 'fas fa-briefcase'],
                    'secretaires' => ['label' => 'Secretaires', 'icon' => 'fas fa-user'],
                    'agents' => ['label' => 'Agents', 'icon' => 'fas fa-users'],
                ];

                $dirigeants = [
                    [
                        'label' => 'PCA',
                        'nom' => trim(($entite->pca_prenom ?? '').' '.($entite->pca_nom ?? '')) ?: 'Non renseigne',
                        'icon' => 'fas fa-landmark',
                        'photo' => $entite->pca_photo_path,
                        'tint' => 'from-rose-50 to-white',
                        'ring' => 'text-rose-500',
                    ],
                    [
                        'label' => 'DG',
                        'nom' => trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? '')) ?: 'Non renseigne',
                        'icon' => 'fas fa-user-tie',
                        'photo' => $entite->directrice_generale_photo_path,
                        'tint' => 'from-cyan-50 to-white',
                        'ring' => 'text-cyan-500',
                    ],
                    [
                        'label' => 'DGA',
                        'nom' => trim(($entite->dga_prenom ?? '').' '.($entite->dga_nom ?? '')) ?: 'Non renseigne',
                        'icon' => 'fas fa-user-gear',
                        'photo' => $entite->dga_photo_path,
                        'tint' => 'from-fuchsia-50 to-white',
                        'ring' => 'text-fuchsia-500',
                    ],
                ];
            @endphp

            <section class="overflow-hidden rounded-[34px] border border-white/70 bg-white/80 p-6 shadow-[0_30px_80px_-35px_rgba(148,163,184,0.55)] backdrop-blur xl:p-7">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <h1 class="text-3xl font-black tracking-tight text-slate-900 lg:text-5xl">Gestion de la Faitiere</h1>
                            <div class="flex flex-wrap items-center gap-4 text-sm font-semibold text-slate-500">
                                <span class="uppercase tracking-[0.25em] text-slate-700">Siege principal</span>
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-location-dot text-slate-400"></i>
                                    {{ $entite->ville ?: 'Ouagadougou' }}, {{ $entite->region ?: 'Centre' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.entites.edit', $entite) }}" class="inline-flex h-12 items-center gap-3 rounded-2xl bg-slate-900 px-6 text-[11px] font-black uppercase tracking-[0.18em] text-white shadow-lg shadow-slate-300 transition hover:-translate-y-0.5 hover:bg-slate-800">
                                <i class="fas fa-pen text-cyan-300"></i>
                                Editer le profil
                            </a>
                            <form method="POST" action="{{ route('admin.entites.reset') }}" class="inline">
                                @csrf
                                <button type="submit" class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-50 text-rose-500 shadow-lg shadow-rose-100/60 transition hover:-translate-y-0.5 hover:bg-rose-500 hover:text-white" title="Reinitialiser">
                                    <i class="fas fa-rotate-right"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach ($kpis as $kpi)
                            <article class="rounded-[28px] bg-gradient-to-br {{ $kpi['gradient'] }} p-[1px] shadow-[0_18px_50px_-24px_rgba(15,23,42,0.55)]">
                                <div class="h-full rounded-[27px] {{ $kpi['soft'] }} p-5 text-white backdrop-blur">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-5xl font-black leading-none drop-shadow-sm">{{ $kpi['count'] }}</p>
                                            <p class="mt-3 text-lg font-extrabold uppercase tracking-[0.12em]">{{ $kpi['label'] }}</p>
                                        </div>
                                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/25 text-lg shadow-inner">
                                            <i class="{{ $kpi['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <div class="mt-6 flex items-center gap-3">
                                        <a href="{{ $kpi['list'] }}" class="inline-flex min-w-0 flex-1 items-center justify-center rounded-2xl bg-white/20 px-4 py-3 text-xs font-black uppercase tracking-[0.18em] text-white transition hover:bg-white/30">
                                            Consulter
                                        </a>
                                        <a href="{{ $kpi['create'] }}" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/90 text-slate-700 shadow-lg transition hover:scale-105">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="space-y-7">
                <div class="overflow-hidden rounded-[34px] border border-white/70 bg-white/85 shadow-[0_24px_70px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100/80 px-4 py-4 lg:px-6">
                        @foreach ($tabs as $key => $tab)
                            <button type="button" data-entite-tab-trigger="{{ $key }}" class="entite-tab-trigger inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold tracking-[0.08em] text-slate-500 transition hover:bg-sky-50 hover:text-sky-600">
                                <i class="{{ $tab['icon'] }} text-xs"></i>
                                <span>{{ $tab['label'] }}</span>
                            </button>
                        @endforeach

                        <div class="ml-auto">
                            <button type="button" class="flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm">
                                <i class="fas fa-sliders"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 lg:p-6">
                        <div data-entite-tab-panel="directions" class="space-y-5">
                            <div class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_45px_-35px_rgba(15,23,42,0.55)]">
                                <div class="mb-5 flex items-center gap-3 text-sm font-black uppercase tracking-[0.14em] text-slate-700">
                                    <i class="fas fa-list-ul text-cyan-500"></i>
                                    Liste des directions & contacts du siege
                                </div>

                                <div class="space-y-4">
                                    @forelse ($directions as $direction)
                                        <article class="flex flex-col gap-4 rounded-[24px] border border-slate-100 bg-slate-50/80 p-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div class="flex items-center gap-4">
                                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[linear-gradient(135deg,_#dbeafe,_#ffffff)] text-sky-600 shadow-inner">
                                                    <i class="fas fa-user-tie text-xl"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-lg font-black text-slate-800">{{ $direction->nom }}</h3>
                                                    <p class="text-base text-slate-500">
                                                        {{ trim(($direction->directeur_prenom ?? '').' '.($direction->directeur_nom ?? '')) ?: 'Responsable non renseigne' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('admin.entites.directions.index') }}" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                                                    Voir liste
                                                </a>
                                                <a href="{{ route('admin.entites.directions.create') }}" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-sky-200 transition hover:-translate-y-0.5">
                                                    Ajouter
                                                </a>
                                            </div>
                                        </article>
                                    @empty
                                        <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
                                            Aucune direction n'est encore enregistree.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div data-entite-tab-panel="services" class="hidden">
                            <div class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_45px_-35px_rgba(15,23,42,0.55)]">
                                @include('admin.entites.partials.services')
                            </div>
                        </div>

                        <div data-entite-tab-panel="secretaires" class="hidden">
                            <div class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_45px_-35px_rgba(15,23,42,0.55)]">
                                @include('admin.entites.partials.secretaires', ['allDirections' => $allDirections])
                            </div>
                        </div>

                        <div data-entite-tab-panel="agents" class="hidden">
                            <div class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_45px_-35px_rgba(15,23,42,0.55)]">
                                <div class="mb-5 flex items-center justify-between gap-4">
                                    <div>
                                        <h2 class="text-xl font-black text-slate-800">Agents de la faitiere</h2>
                                        <p class="mt-1 text-sm text-slate-400">Consultez la liste rapide des agents rattaches au siege.</p>
                                    </div>
                                    <a href="{{ route('admin.agents.create') }}" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-amber-400 to-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5">
                                        Ajouter un agent
                                    </a>
                                </div>

                                @if ($agents->isEmpty())
                                    <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-400">
                                        Aucun agent trouve.
                                    </div>
                                @else
                                    <div class="grid gap-4 md:grid-cols-2">
                                        @foreach ($agents as $agent)
                                            <article class="rounded-[24px] border border-slate-100 bg-slate-50/80 p-4">
                                                <div class="flex items-center gap-4">
                                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-500 shadow-sm">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <h3 class="font-black text-slate-800">{{ trim(($agent->prenom ?? '').' '.($agent->nom ?? '')) ?: 'Agent non renseigne' }}</h3>
                                                        <p class="text-sm text-slate-500">{{ $agent->fonction ?: 'Fonction non renseignee' }}</p>
                                                        <p class="text-xs text-slate-400">{{ $agent->service?->nom ?? 'Sans service' }}</p>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[34px] border border-white/70 bg-white/85 p-6 shadow-[0_24px_70px_-35px_rgba(148,163,184,0.6)] backdrop-blur lg:p-8">
                    <div class="grid gap-6 lg:grid-cols-[1.3fr_1fr] lg:items-start">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-500">Organisation de haut niveau</p>
                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 lg:text-4xl">Structure de la Faitiere</h2>
                            <p class="mt-4 max-w-3xl text-base leading-8 text-slate-500">
                                La faitiere assure le pilotage strategique de l'ensemble du reseau RCPB. Elle centralise les decisions administratives et la gestion du personnel cadre.
                            </p>
                        </div>
                        <div class="hidden justify-end lg:flex">
                            <div class="h-24 w-32 rounded-[28px] bg-[radial-gradient(circle_at_top_left,_rgba(191,219,254,0.8),_rgba(255,255,255,0.5)_70%)]"></div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        @foreach ($dirigeants as $dirigeant)
                            <article class="rounded-[28px] border border-slate-100 bg-gradient-to-br {{ $dirigeant['tint'] }} p-6 shadow-sm">
                                @if (!empty($dirigeant['photo']))
                                    <img src="{{ asset('storage/'.$dirigeant['photo']) }}" alt="{{ $dirigeant['label'] }}" class="mx-auto h-20 w-20 rounded-full object-cover shadow-md">
                                @else
                                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white text-2xl shadow-md {{ $dirigeant['ring'] }}">
                                        <i class="{{ $dirigeant['icon'] }}"></i>
                                    </div>
                                @endif
                                <div class="mt-5 text-center">
                                    <p class="text-sm font-medium text-slate-500">{{ $dirigeant['label'] }}</p>
                                    <h3 class="mt-2 text-xl font-black text-slate-800">{{ $dirigeant['nom'] }}</h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-[34px] border border-white/70 bg-white/85 p-6 shadow-[0_24px_70px_-35px_rgba(148,163,184,0.6)] backdrop-blur lg:p-8">
                    <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr_0.9fr] lg:items-start">
                        <div class="flex items-center gap-4">
                            <span class="h-14 w-1.5 rounded-full bg-cyan-500"></span>
                            <div>
                                <h3 class="text-3xl font-black italic tracking-tight text-slate-900">Infos Siege</h3>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 rounded-[24px] bg-slate-50/80 p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Contact siege</p>
                                <p class="mt-1 text-lg font-bold text-slate-800">+226 25 30 XX XX</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 rounded-[24px] bg-slate-50/80 p-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Derniere maj</p>
                                <p class="mt-1 text-lg font-bold text-slate-800">{{ $entite->updated_at->format('d M Y a H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[28px] bg-slate-900 px-6 py-6 text-center text-sm italic text-cyan-50 shadow-inner">
                        "Assurer la perennite et la solidarite du reseau a travers une gouvernance rigoureuse."
                    </div>
                </div>
            </section>
        @else
            <div class="rounded-[36px] border border-dashed border-slate-200 bg-white/90 px-8 py-20 text-center shadow-[0_30px_80px_-35px_rgba(148,163,184,0.55)]">
                <div class="mx-auto mb-8 flex h-28 w-28 items-center justify-center rounded-full bg-slate-100 text-5xl text-slate-300 shadow-inner">
                    <i class="fas fa-building"></i>
                </div>
                <h2 class="text-4xl font-black tracking-tight text-slate-800">Faitiere non configuree</h2>
                <p class="mx-auto mt-4 max-w-2xl text-slate-500">L'entite principale du reseau n'est pas encore configuree. Creez-la pour afficher ce tableau de bord.</p>
                <a href="{{ route('admin.entites.create') }}" class="mt-8 inline-flex items-center rounded-2xl bg-cyan-600 px-8 py-4 text-sm font-black uppercase tracking-[0.18em] text-white shadow-xl shadow-cyan-200 transition hover:bg-slate-900">
                    Initialiser la structure
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('[data-entite-tab-trigger]');
    const panels = document.querySelectorAll('[data-entite-tab-panel]');

    function activateTab(tabName) {
        triggers.forEach((trigger) => {
            const isActive = trigger.getAttribute('data-entite-tab-trigger') === tabName;

            trigger.classList.toggle('bg-sky-50', isActive);
            trigger.classList.toggle('text-sky-600', isActive);
            trigger.classList.toggle('shadow-sm', isActive);
            trigger.classList.toggle('border', isActive);
            trigger.classList.toggle('border-sky-100', isActive);
            trigger.classList.toggle('text-slate-500', !isActive);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.getAttribute('data-entite-tab-panel') !== tabName);
        });
    }

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            activateTab(this.getAttribute('data-entite-tab-trigger'));
        });
    });

    activateTab('directions');
});
</script>
@endpush
