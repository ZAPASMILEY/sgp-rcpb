@extends('layouts.app')

@section('title', 'Statistiques | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $distribution = [
            'Entités' => $entitesCount,
            'Directions' => $directionsCount,
            'Services' => $servicesCount,
            'Caisses' => $caissesCount,
            'Agences' => $agencesCount,
            'Guichets' => $guichetsCount,
            'Agents' => $agentsCount,
        ];

        $kpis = [
            ['label' => 'Caisses',   'count' => $caissesCount,  'icon' => 'fas fa-university',       'gradient' => 'from-emerald-400 to-teal-500'],
            ['label' => 'Agences',   'count' => $agencesCount,  'icon' => 'fas fa-building',          'gradient' => 'from-violet-500 to-purple-600'],
            ['label' => 'Guichets',  'count' => $guichetsCount, 'icon' => 'fas fa-window-maximize',   'gradient' => 'from-blue-500 to-indigo-600'],
            ['label' => 'Personnel', 'count' => $agentsCount,   'icon' => 'fas fa-users',             'gradient' => 'from-amber-400 to-orange-500'],
        ];
    @endphp

    <div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">

            {{-- Header --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Statistiques globales</h1>
                        <p class="mt-1 text-sm text-slate-400">Vue consolidée des structures pour {{ $selectedYear }}.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.statistiques.index') }}" class="flex items-end gap-3">
                        <div>
                            <label for="annee" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Année</label>
                            <select id="annee" name="annee" class="mt-1 block w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                @foreach ($availableYears as $year)
                                    <option value="{{ $year }}" @selected($year === $selectedYear)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                            <i class="fas fa-filter text-xs"></i> Filtrer
                        </button>
                    </form>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($kpis as $kpi)
                    <div class="rounded-2xl bg-gradient-to-br {{ $kpi['gradient'] }} p-5 text-white shadow-sm">
                        <div class="flex items-start justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <i class="{{ $kpi['icon'] }} text-sm"></i>
                            </span>
                            <span class="text-3xl font-black">{{ $kpi['count'] }}</span>
                        </div>
                        <p class="mt-3 text-sm font-bold">{{ $kpi['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Charts row --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.5fr_1fr]">
                {{-- Bar chart: Répartition des volumes --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Répartition des volumes</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Structures — {{ $selectedYear }}</p>
                    <div id="chart-distribution" class="mt-4"></div>
                </div>

                {{-- Donut: Personnel par sexe --}}
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Personnel par sexe</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Ensemble du réseau</p>
                    <div id="chart-sexe" class="mt-4"></div>
                </div>
            </div>

        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var font = 'Inter, sans-serif';

    // 1. Bar — Répartition des volumes
    new ApexCharts(document.querySelector('#chart-distribution'), {
        chart: { type: 'bar', height: 320, fontFamily: font, toolbar: { show: false } },
        series: [{ name: 'Nombre', data: @json(array_values($distribution)) }],
        xaxis: {
            categories: @json(array_keys($distribution)),
            labels: { style: { fontSize: '10px', fontWeight: 700, colors: '#94a3b8' }, rotate: -45, rotateAlways: true },
        },
        yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } } },
        colors: ['#10b981'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', distributed: true } },
        dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 900, colors: ['#fff'] }, offsetY: -2 },
        legend: { show: false },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
        tooltip: { y: { formatter: function (val) { return val; } } },
    }).render();

    // 2. Donut — Personnel par sexe
    new ApexCharts(document.querySelector('#chart-sexe'), {
        chart: { type: 'donut', height: 300, fontFamily: font },
        series: @json(array_values($agentsBySexe)),
        labels: @json(array_keys($agentsBySexe)),
        colors: ['#3b82f6', '#f472b6'],
        legend: { position: 'bottom', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' } },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 900, color: '#334155' } }
                }
            }
        },
        dataLabels: { enabled: false },
        stroke: { width: 2, colors: ['#fff'] },
    }).render();
});
</script>
@endpush
@endsection
