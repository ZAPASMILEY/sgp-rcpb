@extends('layouts.app')

@section('title', 'Tableau de bord | SGP-RCPB')
@section('page_title', '')

@section('content')
@php
    $calendarStart = now()->startOfMonth()->startOfWeek(\Illuminate\Support\Carbon::MONDAY);
    $calendarEnd = now()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SUNDAY);
    $calendarDays = [];

    for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
        $calendarDays[] = $date->copy();
    }

    $overviewCards = [
        [
            'label' => 'Directions',
            'value' => $directionsCount,
            'meta' => $faitiereDirectionsCount.' direction(s) a la faitiere',
            'href' => route('admin.entites.directions.index'),
            'icon' => 'fas fa-sitemap',
            'from' => 'from-sky-500', 'to' => 'to-cyan-400',
            'icon_bg' => 'bg-sky-100', 'icon_text' => 'text-sky-600',
            'badge' => 'bg-sky-100 text-sky-700',
            'border' => 'border-sky-200', 'hover' => 'hover:border-sky-400',
        ],
        [
            'label' => 'Delegations techniques',
            'value' => $delegationsCount,
            'meta' => $caissesParDelegation.' caisse(s) rattachees',
            'href' => route('admin.delegations-techniques.index'),
            'icon' => 'fas fa-building-circle-arrow-right',
            'from' => 'from-emerald-500', 'to' => 'to-teal-500',
            'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600',
            'badge' => 'bg-emerald-100 text-emerald-700',
            'border' => 'border-emerald-200', 'hover' => 'hover:border-emerald-400',
        ],
        [
            'label' => 'Services',
            'value' => $servicesCount,
            'meta' => $servicesWithoutDirection.' sans direction',
            'href' => route('admin.services.index'),
            'icon' => 'fas fa-layer-group',
            'from' => 'from-violet-500', 'to' => 'to-purple-500',
            'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600',
            'badge' => 'bg-violet-100 text-violet-700',
            'border' => 'border-violet-200', 'hover' => 'hover:border-violet-400',
        ],
        [
            'label' => 'Agents',
            'value' => $agentsCount,
            'meta' => $agentsWithoutService.' sans service',
            'href' => route('admin.agents.index'),
            'icon' => 'fas fa-users',
            'from' => 'from-amber-500', 'to' => 'to-orange-400',
            'icon_bg' => 'bg-amber-100', 'icon_text' => 'text-amber-600',
            'badge' => 'bg-amber-100 text-amber-700',
            'border' => 'border-amber-200', 'hover' => 'hover:border-amber-400',
        ],
        [
            'label' => 'Secretaires',
            'value' => $secretairesCount,
            'meta' => 'Total des secretaires du reseau',
            'href' => route('admin.entites.secretaires.index'),
            'icon' => 'fas fa-user-tie',
            'from' => 'from-fuchsia-500', 'to' => 'to-pink-500',
            'icon_bg' => 'bg-fuchsia-100', 'icon_text' => 'text-fuchsia-600',
            'badge' => 'bg-fuchsia-100 text-fuchsia-700',
            'border' => 'border-fuchsia-200', 'hover' => 'hover:border-fuchsia-400',
        ],
        [
            'label' => 'Alertes securite',
            'value' => $failedLoginAttemptsToday,
            'meta' => $failedLoginEmailsCount.' email(s) distinct(s)',
            'href' => '#security-log',
            'icon' => 'fas fa-shield-halved',
            'from' => 'from-rose-500', 'to' => 'to-red-500',
            'icon_bg' => 'bg-rose-100', 'icon_text' => 'text-rose-600',
            'badge' => 'bg-rose-100 text-rose-700',
            'border' => 'border-rose-200', 'hover' => 'hover:border-rose-400',
        ],
    ];
@endphp

{{-- ── Hero Banner ────────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background: linear-gradient(135deg, #1e293b 0%, #334155 60%, #475569 100%);">
    <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full blur-3xl" style="background:rgba(255,255,255,0.04)"></div>
    <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full blur-2xl" style="background:rgba(148,163,184,0.08)"></div>
    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-lg ring-1 ring-white/20">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-300">Administration Système · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black text-white">Pilotage Administratif</h1>
                <p class="mt-0.5 text-sm text-slate-300/80">{{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>
        @if ($failedLoginAttemptsToday > 0)
        <div class="flex items-center gap-3 rounded-2xl border border-rose-400/30 bg-rose-500/20 px-5 py-3 backdrop-blur-sm">
            <i class="fas fa-triangle-exclamation text-rose-300"></i>
            <p class="text-sm font-bold text-rose-200">
                <span class="font-black text-white">{{ $failedLoginAttemptsToday }}</span> alerte(s) de connexion aujourd'hui
            </p>
        </div>
        @endif
    </div>
    <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
        @foreach ([
            ['label' => 'Agents',      'value' => $agentsCount,     'icon' => 'fas fa-users',                'iconColor' => '#fbbf24'],
            ['label' => 'Directions',  'value' => $directionsCount, 'icon' => 'fas fa-sitemap',              'iconColor' => '#38bdf8'],
            ['label' => 'Délégations', 'value' => $delegationsCount,'icon' => 'fas fa-map-marker-alt',       'iconColor' => '#34d399'],
            ['label' => 'Caisses',     'value' => $caissesCount,    'icon' => 'fas fa-landmark',             'iconColor' => '#a78bfa'],
            ['label' => 'Agences',     'value' => $agencesCount,    'icon' => 'fas fa-building',             'iconColor' => '#22d3ee'],
            ['label' => 'Guichets',    'value' => $guichetsCount,   'icon' => 'fas fa-cash-register',        'iconColor' => '#fb7185'],
        ] as $hs)
        <div class="flex items-center gap-3 rounded-2xl px-4 py-3" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.15);">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base" style="background:rgba(255,255,255,0.15); color:{{ $hs['iconColor'] }}">
                <i class="{{ $hs['icon'] }}"></i>
            </span>
            <div>
                <p class="leading-none font-black text-white" style="font-size:1.6rem">{{ $hs['value'] }}</p>
                <p class="mt-1 font-bold" style="font-size:0.72rem; color:rgba(255,255,255,0.75); text-transform:uppercase; letter-spacing:0.08em">{{ $hs['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="relative z-10 bg-[#f1f5f9] px-4 pb-6 pt-6 lg:px-8">
    <div class="mx-auto max-w-[1500px] space-y-4">

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($overviewCards as $card)
                <a href="{{ $card['href'] }}"
                   class="group relative flex flex-col overflow-hidden rounded-3xl border bg-white shadow-sm transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 {{ $card['border'] }} {{ $card['hover'] }}">

                    {{-- Barre de couleur en haut --}}
                    <div class="h-1.5 w-full bg-gradient-to-r {{ $card['from'] }} {{ $card['to'] }}"></div>

                    <div class="flex flex-1 flex-col gap-4 p-5">

                        {{-- Header : icône + valeur en badge --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['icon_bg'] }} {{ $card['icon_text'] }} transition group-hover:scale-105">
                                <i class="{{ $card['icon'] }} text-base"></i>
                            </div>
                            <span class="rounded-full {{ $card['badge'] }} px-3 py-0.5 text-sm font-black">
                                {{ $card['value'] }}
                            </span>
                        </div>

                        {{-- Label + méta --}}
                        <div class="flex-1">
                            <h3 class="text-base font-black leading-snug text-slate-900 transition-colors group-hover:{{ $card['icon_text'] }}">
                                {{ $card['label'] }}
                            </h3>
                            <p class="mt-1 text-[12px] text-slate-400">{{ $card['meta'] }}</p>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center gap-2 border-t border-slate-100 pt-3">
                            <span class="text-xs font-semibold text-slate-400">Ouvrir</span>
                            <div class="ml-auto flex h-7 w-7 items-center justify-center rounded-full {{ $card['icon_bg'] }} transition group-hover:scale-110">
                                <i class="fas fa-arrow-right text-[10px] {{ $card['icon_text'] }}"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        {{-- Charts Row --}}
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            {{-- Donut: Répartition du réseau --}}
            <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                <h2 class="text-base font-black tracking-tight text-slate-900">Répartition du réseau</h2>
                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Caisses, Agences, Guichets</p>
                <div id="chart-reseau" class="mt-2"></div>
            </div>

            {{-- Bar: Délégations --}}
            <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                <h2 class="text-base font-black tracking-tight text-slate-900">Caisses & Agences par délégation</h2>
                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Répartition territoriale</p>
                <div id="chart-delegations" class="mt-2"></div>
            </div>

            {{-- Area: Alertes 7 jours --}}
            <div class="rounded-[26px] border border-rose-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                <h2 class="text-base font-black tracking-tight text-rose-600">Alertes de sécurité</h2>
                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">7 derniers jours</p>
                <div id="chart-alerts" class="mt-2"></div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(340px,0.9fr)]">
            <div class="space-y-4">
                <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-slate-900">Delegations recentes</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Vue compacte du reseau territorial</p>
                        </div>
                        <a href="{{ route('admin.delegations-techniques.index') }}" class="inline-flex h-8 items-center rounded-full bg-emerald-700 px-4 text-[10px] font-black uppercase tracking-[0.14em] text-white">Voir tout</a>
                    </div>

                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
                        @forelse ($delegations as $delegation)
                            <article class="rounded-[20px] border border-slate-100 bg-slate-50 px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-black text-slate-900">DT {{ $delegation->region }}</h3>
                                        <p class="text-[11px] font-semibold text-slate-400">{{ $delegation->ville }}</p>
                                    </div>
                                    <a href="{{ route('admin.delegations-techniques.index') }}" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-600 hover:text-white" title="Ouvrir la delegation {{ $delegation->ville }}">
                                        <i class="fas fa-building"></i>
                                    </a>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                                    <div class="rounded-2xl bg-white px-3 py-3 shadow-sm">
                                        <p class="text-lg font-black text-slate-900">{{ $delegation->caisses_count }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Caisses</p>
                                    </div>
                                    <div class="rounded-2xl bg-white px-3 py-3 shadow-sm">
                                        <p class="text-lg font-black text-slate-900">{{ $delegation->agences_count }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Agences</p>
                                    </div>
                                </div>
                                <div class="mt-3 text-[11px] font-semibold text-slate-500">
                                    {{ $delegation->directeur ? trim($delegation->directeur->prenom.' '.$delegation->directeur->nom) : 'Aucun responsable charge' }}
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[20px] border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center text-sm text-slate-400 lg:col-span-2 2xl:col-span-3">
                                Aucune delegation technique recente.
                            </div>
                        @endforelse
                    </div>
                </section>

                <div class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-lg font-black tracking-tight text-slate-900">Derniers services</h2>
                                <a href="{{ route('admin.services.index') }}" class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Liste</a>
                            </div>
                            <div class="space-y-3">
                                @forelse ($recentServices as $service)
                                    <article class="flex items-center justify-between gap-3 rounded-[18px] bg-slate-50 px-4 py-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-900">{{ $service->nom }}</p>
                                            <p class="truncate text-[11px] font-semibold text-slate-400">{{ $service->direction?->nom ?? 'Sans direction' }}</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-600 shadow-sm">{{ $service->direction?->delegationTechnique?->ville ?? 'Siege' }}</span>
                                    </article>
                                @empty
                                    <p class="rounded-[18px] bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">Aucun service recent.</p>
                                @endforelse
                            </div>
                        </section>

                        <section class="rounded-[26px] border border-slate-100 bg-white p-4 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                            <div class="mb-3 flex items-center justify-between">
                                <div>
                                    <h2 class="text-sm font-black tracking-tight text-slate-900">Calendrier</h2>
                                    <p class="mt-0.5 text-[9px] font-black uppercase tracking-[0.14em] text-slate-400">{{ now()->translatedFormat('M Y') }}</p>
                                </div>
                                <div class="flex gap-1.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fas fa-chevron-left text-[9px]"></i>
                                    </span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fas fa-chevron-right text-[9px]"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-7 gap-1 text-center">
                                @foreach (['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $weekday)
                                    <span class="py-0.5 text-[9px] font-black uppercase text-slate-300">{{ $weekday }}</span>
                                @endforeach
                            </div>

                            <div class="mt-1.5 grid grid-cols-7 gap-1">
                                @foreach ($calendarDays as $day)
                                    <div class="flex h-7 items-center justify-center rounded-lg text-[10px] font-black transition-all {{ $day->isToday() ? 'bg-emerald-600 text-white shadow-md shadow-emerald-100' : ($day->month === now()->month ? 'bg-slate-50 text-slate-600' : 'bg-slate-50/60 text-slate-300') }}">
                                        {{ $day->day }}
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black tracking-tight text-slate-900">Derniers agents</h2>
                        <a href="{{ route('admin.agents.index') }}" class="inline-flex h-8 items-center rounded-full bg-emerald-700 px-4 text-[10px] font-black uppercase tracking-[0.14em] text-white">Voir tous</a>
                    </div>
                    <div class="space-y-3">
                        @forelse ($recentAgents as $agent)
                            <article class="rounded-[18px] bg-slate-50 px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-slate-500 shadow-sm">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-slate-900">{{ trim(($agent->prenom ?? '').' '.($agent->nom ?? '')) ?: 'Agent non renseigne' }}</p>
                                        @php
                                            $structure = $agent->guichet?->nom
                                                ?? $agent->agence?->nom
                                                ?? $agent->caisse?->nom
                                                ?? $agent->delegationTechnique
                                                    ? ($agent->delegationTechnique->region . ' – ' . $agent->delegationTechnique->ville)
                                                    : ($agent->service?->nom
                                                        ?? $agent->direction?->nom
                                                        ?? null);
                                        @endphp
                                        <p class="truncate text-[11px] font-semibold text-slate-400">{{ $structure ?? 'Non affecté' }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-[18px] bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">Aucun agent recent.</p>
                        @endforelse
                    </div>
                </section>

                <section id="security-log" class="rounded-[26px] border border-rose-200 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-black tracking-tight text-rose-600">Journal de sécurité</h2>
                            <p class="mt-0.5 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $failedLoginAttemptsCount }} tentative(s) enregistrée(s)</p>
                        </div>
                        @if($failedLoginAttemptsCount > 0)
                            <span class="inline-flex h-7 items-center gap-1.5 rounded-full bg-rose-500 px-3 text-[10px] font-black uppercase tracking-[0.1em] text-white">
                                <i class="fas fa-triangle-exclamation text-[9px]"></i> Alerte
                            </span>
                        @else
                            <span class="inline-flex h-7 items-center gap-1.5 rounded-full bg-emerald-100 px-3 text-[10px] font-black uppercase tracking-[0.1em] text-emerald-700">
                                <i class="fas fa-check text-[9px]"></i> OK
                            </span>
                        @endif
                    </div>

                    {{-- Liste scrollable limitée en hauteur --}}
                    <div class="overflow-y-auto rounded-2xl" style="max-height: 260px;">
                        @forelse ($recentLoginFailures as $failure)
                            <div class="flex items-center gap-3 border-b border-rose-50 px-3 py-2 last:border-0 hover:bg-rose-50/50 transition">
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-rose-500 text-[9px] text-white">
                                    <i class="fas fa-exclamation"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-black text-rose-700">{{ $failure->email ?: 'Email non renseigné' }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400">{{ $failure->ip_address ?: 'IP inconnue' }}</p>
                                </div>
                                <span class="shrink-0 text-[10px] font-semibold text-slate-400">
                                    {{ optional($failure->attempted_at)->format('d/m H:i') }}
                                </span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <i class="fas fa-shield-check text-2xl text-emerald-300 mb-2"></i>
                                <p class="text-sm font-semibold text-slate-400">Aucune alerte récente</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
window._adminCharts = {
    reseau:      {!! json_encode($reseauChart) !!},
    delegations: {!! json_encode($delegationsChart) !!},
    alerts:      {!! json_encode($alertsChart) !!},
    moreThan5:   {!! json_encode(count($delegationsChart['categories']) > 5) !!},
};
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var c = window._adminCharts;

    // 1. Donut — Répartition du réseau
    new ApexCharts(document.querySelector('#chart-reseau'), {
        chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
        series: c.reseau.series,
        labels: c.reseau.labels,
        colors: ['#10b981', '#3b82f6', '#f59e0b'],
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' } },
        plotOptions: { pie: { donut: { size: '60%',
            labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 900, color: '#334155' } }
        }}},
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['#fff'] },
        tooltip: { y: { formatter: function (val) { return val + ' structures'; } } },
    }).render();

    // 2. Bar — Délégations
    new ApexCharts(document.querySelector('#chart-delegations'), {
        chart: { type: 'bar', height: 260, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
        series: [
            { name: 'Caisses', data: c.delegations.caisses },
            { name: 'Agences', data: c.delegations.agences },
        ],
        xaxis: {
            categories: c.delegations.categories,
            labels: { style: { fontSize: '10px', fontWeight: 700, colors: '#94a3b8' }, rotate: -45, rotateAlways: c.moreThan5 },
        },
        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } } },
        colors: ['#10b981', '#6366f1'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' } },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        tooltip: { y: { formatter: function (val) { return val + ' structures'; } } },
    }).render();

    // 3. Area — Alertes 7 jours
    new ApexCharts(document.querySelector('#chart-alerts'), {
        chart: { type: 'area', height: 260, fontFamily: 'Inter, sans-serif', toolbar: { show: false }, sparkline: { enabled: false } },
        series: [{ name: 'Tentatives', data: c.alerts.series }],
        xaxis: {
            categories: c.alerts.categories,
            labels: { style: { fontSize: '10px', fontWeight: 700, colors: '#94a3b8' } },
        },
        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } }, min: 0 },
        colors: ['#f43f5e'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, type: 'vertical', opacityFrom: 0.4, opacityTo: 0.05 } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        grid: { borderColor: '#fff1f2', strokeDashArray: 4 },
        tooltip: { y: { formatter: function (val) { return val + ' tentative(s)'; } } },
    }).render();
});
</script>
@endpush
@endsection
