@extends('layouts.personnel')

@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

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
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">
                        @if ($agent?->service)
                            {{ $agent->service->nom }} · Personnel
                        @elseif ($agent?->agence)
                            {{ $agent->agence->nom }} · Personnel
                        @else
                            Mon Espace · Personnel
                        @endif
                    </p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-emerald-100/80">
                        {{ $agent?->fonction ?? $user->role }} · Synthèse du {{ now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Sélecteur d'année --}}
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-200">Année</span>
                <form method="GET" action="{{ route('personnel.dashboard') }}" id="year-form">
                    <select name="annee" onchange="document.getElementById('year-form').submit()"
                            class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm outline-none transition hover:bg-white/20">
                        @foreach ($anneesDisponibles as $yr)
                            <option value="{{ $yr }}" @selected($yr === $annee) class="text-slate-900 bg-white">{{ $yr }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('personnel.mon-espace') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-folder-open text-xs"></i> Mon dossier
                </a>
            </div>
        </div>

        {{-- Mini KPIs dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Évaluations',   'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star'],
                ['label' => 'Validées',       'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-check'],
                ['label' => 'Fiches obj.',    'value' => $fichesStats['total'],       'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Avancement moy.','value' => $tauxAvancement.'%',         'icon' => 'fas fa-gauge-high'],
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
    <div class="w-full flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        @if (! $agent)
            <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-12 text-center shadow-sm">
                <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-700">Aucun dossier agent associé à votre compte.</p>
                <p class="mt-1 text-xs text-slate-500">Contactez l'administrateur pour lier votre compte à un dossier agent.</p>
            </div>
        @else

        {{-- ── Graphiques ── --}}
        @if ($evaluationsStats['total'] > 0 || $fichesStats['total'] > 0)
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mes évaluations {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mes objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>
        </div>
        @endif

        {{-- ── Informations personnelles ── --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 mb-3">Informations personnelles</p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm text-slate-700">
                @if ($agent->prenom || $agent->nom)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Nom complet</p>
                        <p class="mt-1 font-semibold">{{ trim($agent->prenom.' '.$agent->nom) }}</p>
                    </div>
                @endif
                @if ($agent->fonction)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Fonction</p>
                        <p class="mt-1 font-semibold">{{ $agent->fonction }}</p>
                    </div>
                @endif
                @if ($agent->service)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Service</p>
                        <p class="mt-1 font-semibold">{{ $agent->service->nom }}</p>
                    </div>
                @elseif ($agent->agence)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Agence</p>
                        <p class="mt-1 font-semibold">{{ $agent->agence->nom }}</p>
                    </div>
                @endif
                @if ($agent->email)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Email</p>
                        <p class="mt-1 font-semibold">{{ $agent->email }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Évaluations récentes ── --}}
        <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Mes dernières évaluations · {{ $annee }}</h2>
                </div>
                <a href="{{ route('personnel.mon-espace') }}?tab=evaluations"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-600 transition hover:border-cyan-300 hover:text-cyan-700">
                    Voir tout <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            @if ($evaluationsRecentes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-clipboard text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune évaluation pour {{ $annee }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach ($evaluationsRecentes as $eval)
                        @php
                            $statClass = match ($eval->statut) {
                                'valide' => 'bg-emerald-100 text-emerald-700',
                                'soumis' => 'bg-amber-100 text-amber-700',
                                default  => 'bg-slate-100 text-slate-600',
                            };
                            $statLabel = match ($eval->statut) {
                                'valide' => 'Validée', 'soumis' => 'Soumise', default => 'Brouillon',
                            };
                            $note = $eval->note_finale !== null ? number_format((float)$eval->note_finale, 2, ',', ' ').'/10' : null;
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">
                                    Période {{ $eval->date_debut->format('m/Y') }} – {{ $eval->date_fin->format('m/Y') }}
                                </p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    Par {{ $eval->evaluateur?->name ?? '—' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($note)
                                    <span class="text-sm font-black text-emerald-700">{{ $note }}</span>
                                @endif
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">{{ $statLabel }}</span>
                                @if ($eval->statut !== 'brouillon')
                                <a href="{{ route('personnel.evaluations.show', $eval) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700">
                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── Fiches d'objectifs récentes ── --}}
        <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Objectifs</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Mes dernières fiches · {{ $annee }}</h2>
                </div>
                <a href="{{ route('personnel.mon-espace') }}?tab=objectifs"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
                    Voir tout <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            @if ($fichesRecentes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune fiche d'objectifs pour {{ $annee }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach ($fichesRecentes as $fiche)
                        @php
                            $statClass = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'bg-emerald-100 text-emerald-700',
                                'refusee'  => 'bg-rose-100 text-rose-700',
                                default    => 'bg-amber-100 text-amber-700',
                            };
                            $statLabel = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'Acceptée', 'refusee' => 'Refusée', default => 'En attente',
                            };
                            $av = (int)($fiche->avancement_percentage ?? 0);
                            $avColor = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">{{ $fiche->titre }}</p>
                                <div class="mt-1.5 flex items-center gap-3">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $avColor }}" style="width:{{ $av }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">{{ $av }}% · {{ $fiche->objectifs_count }} obj.</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">{{ $statLabel }}</span>
                                <a href="{{ route('personnel.fiches.show', $fiche) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @endif {{-- end $agent check --}}

    </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var eD = {!! json_encode($evalsDonut) !!};
    var fD = {!! json_encode($fichesDonut) !!};
    if (eD.series.every(v => v === 0) && fD.series.every(v => v === 0)) return;
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
    script.onload = function () {
        function donut(data) {
            return { chart:{type:'donut',height:200,fontFamily:'Inter,sans-serif'}, labels:data.labels, series:data.series, colors:data.colors,
                plotOptions:{pie:{donut:{size:'65%',labels:{show:true,total:{show:true,label:'Total',fontSize:'12px',fontWeight:700,color:'#475569',
                    formatter:function(w){return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0);}}}}}},
                legend:{position:'bottom',fontSize:'11px',fontWeight:600}, dataLabels:{enabled:false}, stroke:{width:2} };
        }
        if (document.querySelector('#chart-evals-donut'))  new ApexCharts(document.querySelector('#chart-evals-donut'),  donut(eD)).render();
        if (document.querySelector('#chart-fiches-donut')) new ApexCharts(document.querySelector('#chart-fiches-donut'), donut(fD)).render();
    };
    document.head.appendChild(script);
})();
</script>
@endpush
