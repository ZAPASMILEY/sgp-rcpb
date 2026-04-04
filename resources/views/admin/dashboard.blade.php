@extends('layouts.app')

@section('title', 'Tableau de bord | SGP-RCPB')
@section('page_title', '')

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
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
            'meta' => $faitiereDirectionsCount.' a la faitiere / '.$delegationDirectionsCount.' en delegation',
            'href' => route('admin.entites.directions.index'),
            'icon' => 'fas fa-sitemap',
            'valueClass' => 'text-sky-500',
            'iconClass' => 'bg-sky-50 text-sky-500',
            'borderClass' => 'border-slate-100',
        ],
        [
            'label' => 'Delegations techniques',
            'value' => $delegationsCount,
            'meta' => $delegationDirectionsCount.' directions rattachees',
            'href' => route('admin.delegations-techniques.index'),
            'icon' => 'fas fa-building-circle-arrow-right',
            'valueClass' => 'text-emerald-500',
            'iconClass' => 'bg-emerald-50 text-emerald-500',
            'borderClass' => 'border-slate-100',
        ],
        [
            'label' => 'Services',
            'value' => $servicesCount,
            'meta' => $servicesWithoutDirection.' sans direction',
            'href' => route('admin.services.index'),
            'icon' => 'fas fa-layer-group',
            'valueClass' => 'text-cyan-500',
            'iconClass' => 'bg-cyan-50 text-cyan-500',
            'borderClass' => 'border-slate-100',
        ],
        [
            'label' => 'Agents',
            'value' => $agentsCount,
            'meta' => $agentsWithoutService.' sans service',
            'href' => route('admin.agents.index'),
            'icon' => 'fas fa-users',
            'valueClass' => 'text-amber-500',
            'iconClass' => 'bg-amber-50 text-amber-500',
            'borderClass' => 'border-slate-100',
        ],
        [
            'label' => 'Secretaires',
            'value' => $secretairesCount,
            'meta' => 'Total des secretaires du reseau',
            'href' => route('admin.entites.secretaires.index'),
            'icon' => 'fas fa-user-tie',
            'valueClass' => 'text-fuchsia-500',
            'iconClass' => 'bg-fuchsia-50 text-fuchsia-500',
            'borderClass' => 'border-slate-100',
        ],
        [
            'label' => 'Alertes securite',
            'value' => $failedLoginAttemptsToday,
            'meta' => $failedLoginEmailsCount.' email(s) distinct(s)',
            'href' => '#security-log',
            'icon' => 'fas fa-shield-halved',
            'valueClass' => 'text-rose-500',
            'iconClass' => 'bg-rose-50 text-rose-500',
            'borderClass' => 'border-rose-200',
        ],
    ];
@endphp

<div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
    <div class="mx-auto max-w-[1500px] space-y-4">
        <section class="rounded-[26px] border border-white bg-white/90 px-5 py-4 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-base font-black text-emerald-700">Tableau de bord</p>
                    <div class="mt-1 flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Pilotage administratif</h1>
                    </div>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Synthese du {{ now()->translatedFormat('d F Y') }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[520px]">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Caisses</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $caissesCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Agences</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $agencesCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Guichets</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $guichetsCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Alertes</p>
                        <p class="mt-1 text-xl font-black text-rose-500">{{ $failedLoginAttemptsCount }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-3 md:grid-cols-2 2xl:grid-cols-6">
            @foreach ($overviewCards as $card)
                <article class="rounded-[20px] border {{ $card['borderClass'] }} bg-white px-4 py-3 shadow-[0_12px_30px_-24px_rgba(15,23,42,0.3)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight {{ $card['valueClass'] }}">{{ $card['value'] }}</p>
                            <p class="mt-1 line-clamp-1 text-[11px] font-bold text-slate-400">{{ $card['meta'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['iconClass'] }}">
                            <i class="{{ $card['icon'] }} text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <a href="{{ $card['href'] }}" class="inline-flex h-8 items-center rounded-xl bg-slate-50 px-3 text-[10px] font-black uppercase tracking-[0.14em] text-slate-700 transition hover:bg-slate-900 hover:text-white">
                            Ouvrir
                        </a>
                    </div>
                </article>
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
                                        <h3 class="truncate text-base font-black text-slate-900">{{ $delegation->ville }}</h3>
                                        <p class="text-[11px] font-semibold text-slate-400">{{ $delegation->adresse ?: 'Adresse non renseignee' }}</p>
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
                                    @php($leadDirection = $delegation->directions->first())
                                    {{ $leadDirection ? trim(($leadDirection->directeur_prenom ?? '').' '.($leadDirection->directeur_nom ?? '')) : 'Aucun responsable charge' }}
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
                                        <p class="truncate text-[11px] font-semibold text-slate-400">{{ $agent->service?->nom ?? 'Sans service' }}</p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-[18px] bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">Aucun agent recent.</p>
                        @endforelse
                    </div>
                </section>

                <section id="security-log" class="rounded-[26px] border border-rose-200 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-rose-600">Journal de securite</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ $failedLoginAttemptsCount }} tentative(s) enregistree(s)</p>
                        </div>
                        <span class="inline-flex h-8 items-center rounded-full bg-rose-500 px-4 text-[10px] font-black uppercase tracking-[0.14em] text-white">A surveiller</span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($recentLoginFailures as $failure)
                            <article class="rounded-[18px] bg-rose-50 px-4 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-7 w-7 items-center justify-center rounded-full bg-rose-500 text-[10px] text-white">
                                        <i class="fas fa-exclamation"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-rose-700">{{ $failure->email ?: 'Email non renseigne' }}</p>
                                        <p class="truncate text-[11px] font-semibold text-rose-500">
                                            {{ $failure->ip_address ?: 'IP inconnue' }} • {{ optional($failure->attempted_at)->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-[18px] bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">Aucune alerte de connexion recente.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Donut — Répartition du réseau
    new ApexCharts(document.querySelector('#chart-reseau'), {
        chart: { type: 'donut', height: 260, fontFamily: 'Inter, sans-serif' },
        series: @json($reseauChart['series']),
        labels: @json($reseauChart['labels']),
        colors: ['#10b981', '#3b82f6', '#f59e0b'],
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' } },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 900, color: '#334155' }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['#fff'] },
        tooltip: { y: { formatter: function (val) { return val + ' structures'; } } },
    }).render();

    // 2. Bar — Délégations
    new ApexCharts(document.querySelector('#chart-delegations'), {
        chart: { type: 'bar', height: 260, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
        series: [
            { name: 'Caisses', data: @json($delegationsChart['caisses']) },
            { name: 'Agences', data: @json($delegationsChart['agences']) },
        ],
        xaxis: {
            categories: @json($delegationsChart['categories']),
            labels: { style: { fontSize: '10px', fontWeight: 700, colors: '#94a3b8' }, rotate: -45, rotateAlways: @json(count($delegationsChart['categories']) > 5) },
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
        series: [{ name: 'Tentatives', data: @json($alertsChart['series']) }],
        xaxis: {
            categories: @json($alertsChart['categories']),
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
