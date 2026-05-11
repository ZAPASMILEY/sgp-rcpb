@extends('layouts.dga')
@section('title', 'Tableau de bord DGA | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ══════════════════════════ HERO ══════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Réseau RCPB · Directeur Général Adjoint</p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ auth()->user()->name }}</h1>
                    <p class="mt-1 text-sm text-emerald-100/80">Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>

            {{-- Sélecteur d'année --}}
            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-200">Année</span>
                <form method="GET" action="{{ route('dga.dashboard') }}" id="year-form">
                    <select name="annee" onchange="document.getElementById('year-form').submit()"
                            class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm outline-none transition hover:bg-white/20">
                        @foreach ($anneesDisponibles as $yr)
                            <option value="{{ $yr }}" @selected($yr === $annee) class="text-slate-900 bg-white">{{ $yr }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('dga.mon-espace') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-folder-open text-xs"></i> Mon espace
                </a>
            </div>
        </div>

        {{-- Mini KPIs dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Délégations',         'value' => $reseauStats['delegations'], 'icon' => 'fas fa-map-marker-alt'],
                ['label' => 'Caisses',             'value' => $reseauStats['caisses'],     'icon' => 'fas fa-building-columns'],
                ['label' => 'Évaluations reçues',  'value' => $evalsRecStats['total'],     'icon' => 'fas fa-star'],
                ['label' => 'Note moy. réseau',    'value' => $noteReseau ? number_format($noteReseau, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="{{ $m['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-200">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- ══════════════════════ KPI CARDS ═══════════════════════════════════ --}}
        @php
        $kpis = [
            ['label' => 'Fiches reçues',     'value' => $fichesRecStats['total'],       'icon' => 'fas fa-clipboard-list', 'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
            ['label' => 'Acceptées',          'value' => $fichesRecStats['acceptees'],  'icon' => 'fas fa-circle-check',   'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'En attente',         'value' => $fichesRecStats['en_attente'], 'icon' => 'fas fa-clock',          'color' => 'bg-amber-500',   'light' => 'bg-amber-50 border-amber-100'],
            ['label' => 'Avancement moy.',    'value' => $tauxAvancement.'%',           'icon' => 'fas fa-gauge-high',     'color' => 'bg-sky-600',     'light' => 'bg-sky-50 border-sky-100'],
            ['label' => 'Évals. reçues',      'value' => $evalsRecStats['total'],       'icon' => 'fas fa-star',           'color' => 'bg-indigo-600',  'light' => 'bg-indigo-50 border-indigo-100'],
            ['label' => 'Validées',           'value' => $evalsRecStats['valide'],      'icon' => 'fas fa-check',          'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
            ['label' => 'Évals. données',     'value' => $subStats['total'],            'icon' => 'fas fa-pen-to-square',  'color' => 'bg-rose-500',    'light' => 'bg-rose-50 border-rose-100'],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                            <i class="{{ $kpi['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ══════════════════════ CHARTS + CONTEXTE ═══════════════════════════ --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">

            {{-- Donut : évaluations reçues --}}
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations reçues {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>

            {{-- Donut : fiches d'objectifs --}}
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches d'objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches reçues</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>

            {{-- Évaluations données + réseau --}}
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Pilotage équipe</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Évaluations données</h2>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => 'Total',      'value' => $subStats['total'],     'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-700'],
                        ['label' => 'Validées',   'value' => $subStats['valide'],    'tone' => 'bg-emerald-50 border-emerald-100', 'text' => 'text-emerald-700'],
                        ['label' => 'Soumises',   'value' => $subStats['soumis'],    'tone' => 'bg-amber-50 border-amber-100',     'text' => 'text-amber-700'],
                        ['label' => 'Brouillons', 'value' => $subStats['brouillon'], 'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-500'],
                    ] as $s)
                    <div class="rounded-xl border {{ $s['tone'] }} px-3 py-2.5">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s['label'] }}</p>
                        <p class="mt-1 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Agences</p>
                        <p class="mt-1 text-xl font-black text-slate-700">{{ $reseauStats['agences'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Guichets</p>
                        <p class="mt-1 text-xl font-black text-slate-700">{{ $reseauStats['guichets'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════ ÉVALUATIONS RÉCENTES DONNÉES ════════════════ --}}
        <div class="mt-5 rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">DGA · Activité</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Évaluations données récentes</h2>
                </div>
                {{-- Filtre rapide --}}
                <form method="GET" action="{{ route('dga.dashboard') }}" class="flex items-center gap-2">
                    <input type="hidden" name="annee" value="{{ $annee }}">
                    <select name="statut" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 outline-none">
                        <option value="" {{ $filters['statut'] === '' ? 'selected' : '' }}>Tous statuts</option>
                        <option value="brouillon" {{ $filters['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                        <option value="soumis"    {{ $filters['statut'] === 'soumis'    ? 'selected' : '' }}>Soumises</option>
                        <option value="valide"    {{ $filters['statut'] === 'valide'    ? 'selected' : '' }}>Validées</option>
                    </select>
                </form>
            </div>

            @if ($evaluations->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-star text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune évaluation créée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Évalué</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Statut</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Note</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Date</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($evaluations as $eval)
                                @php
                                    $sc = match($eval->statut) {
                                        'valide'    => 'bg-emerald-100 text-emerald-700',
                                        'soumis'    => 'bg-amber-100 text-amber-700',
                                        'brouillon' => 'bg-slate-100 text-slate-500',
                                        'refuse'    => 'bg-rose-100 text-rose-700',
                                        default     => 'bg-slate-100 text-slate-500',
                                    };
                                    $sl = match($eval->statut) {
                                        'valide' => 'Validée', 'soumis' => 'Soumise',
                                        'brouillon' => 'Brouillon', 'refuse' => 'Refusée',
                                        default => ucfirst($eval->statut),
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800">
                                        {{ $eval->identification?->nom_prenom ?? '—' }}
                                        @if ($eval->identification?->emploi)
                                            <span class="block text-[10px] font-normal text-slate-400">{{ $eval->identification->emploi }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                    </td>
                                    <td class="px-5 py-3 font-black text-slate-700">
                                        {{ $eval->note_finale ? number_format($eval->note_finale, 2, ',', ' ').'/10' : '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">
                                        {{ $eval->date_debut?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('dga.sub-evaluations.show', $eval) }}"
                                           class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                            <i class="fas fa-eye text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $evaluations->links() }}
                </div>
            @endif
        </div>

        {{-- Alerte fiches en attente --}}
        @if ($fichesRecStats['en_attente'] > 0)
            <div class="mt-5 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div class="flex-1">
                    <p class="font-black text-amber-800">
                        {{ $fichesRecStats['en_attente'] }} fiche(s) d'objectifs en attente de votre validation
                    </p>
                </div>
                <a href="{{ route('dga.mon-espace') }}"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">
                    Mon espace
                </a>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
window._dashData = {
    evalsDonut:  {!! json_encode($evalsDonut) !!},
    fichesDonut: {!! json_encode($fichesDonut) !!},
};
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var d = window._dashData;
    function donutOptions(data) {
        return {
            chart: { type: 'donut', height: 200, fontFamily: 'Inter, sans-serif' },
            labels: data.labels, series: data.series, colors: data.colors,
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true,
                total: { show: true, label: 'Total', fontSize: '12px', fontWeight: 700,
                    color: '#475569', formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0); } }
            } } } },
            legend: { position: 'bottom', fontSize: '11px', fontWeight: 600 },
            dataLabels: { enabled: false }, stroke: { width: 2 },
        };
    }
    if (document.querySelector('#chart-evals-donut'))  new ApexCharts(document.querySelector('#chart-evals-donut'),  donutOptions(d.evalsDonut)).render();
    if (document.querySelector('#chart-fiches-donut')) new ApexCharts(document.querySelector('#chart-fiches-donut'), donutOptions(d.fichesDonut)).render();
});
</script>
@endpush
