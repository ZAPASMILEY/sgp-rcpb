@extends('layouts.pca')

@section('title', 'Tableau de bord PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ═══════════════════════════════════════════════════════════════════════
         HERO BANNER — Identité institutionnelle RCPB
    ═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        {{-- Motif décoratif --}}
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            {{-- Identité --}}
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white text-2xl font-black shadow-inner ring-2 ring-white/20 backdrop-blur-sm">
                    R
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Réseau des Caisses Populaires du Burkina</p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $entite->nom }}</h1>
                    <p class="mt-1 text-sm text-emerald-100/80">
                        Pilotage PCA · Synthèse du {{ now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Sélecteur d'année --}}
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-200">Année</span>
                <form method="GET" action="{{ route('pca.dashboard') }}" id="year-form">
                    <select name="annee" onchange="document.getElementById('year-form').submit()"
                            class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm outline-none
                                   transition hover:bg-white/20 focus:ring-2 focus:ring-white/30">
                        @foreach ($anneesDisponibles as $yr)
                            <option value="{{ $yr }}" @selected($yr === $annee) class="text-slate-900 bg-white">{{ $yr }}</option>
                        @endforeach
                    </select>
                </form>
                @if($evaluationsEnabled)
                    <a href="{{ route('pca.evaluations.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                        <i class="fas fa-plus text-xs"></i> Évaluer DG
                    </a>
                @else
                    <span title="Fonctionnalité désactivée par l'administrateur"
                          class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-black text-white/40 opacity-60 select-none">
                        <i class="fas fa-plus text-xs"></i> Évaluer DG
                    </span>
                @endif
            </div>
        </div>

        {{-- Méta indicateurs rapides dans le hero — portée DG uniquement --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Fiches objectifs DG', 'value' => $totalFiches,                                                               'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Évaluations DG',       'value' => $evalsTotal,                                                               'icon' => 'fas fa-star'],
                ['label' => 'Note moy. DG',         'value' => $noteMoyenne > 0 ? number_format($noteMoyenne, 2, ',', ' ').'/10' : '—',   'icon' => 'fas fa-chart-bar'],
                ['label' => 'Avancement DG',        'value' => $tauxAvancement.'%',                                                       'icon' => 'fas fa-gauge-high'],
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

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 1 — KPI CARDS (7 indicateurs)
        ═══════════════════════════════════════════════════════════════════════ --}}
        @php
        $kpis = [
            ['label' => 'Fiches d\'objectifs',   'value' => $totalFiches,      'icon' => 'fas fa-clipboard-list', 'color' => 'bg-slate-700',    'light' => 'bg-slate-50 border-slate-200'],
            ['label' => 'Acceptées',              'value' => $fichesAcceptees,  'icon' => 'fas fa-circle-check',  'color' => 'bg-emerald-600',  'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'En attente',             'value' => $fichesEnAttente,  'icon' => 'fas fa-clock',         'color' => 'bg-amber-500',    'light' => 'bg-amber-50 border-amber-100'],
            ['label' => 'Taux d\'avancement',     'value' => $tauxAvancement.'%','icon' => 'fas fa-gauge-high',   'color' => 'bg-sky-600',      'light' => 'bg-sky-50 border-sky-100'],
            ['label' => 'Évaluations',            'value' => $evalsTotal,       'icon' => 'fas fa-star',          'color' => 'bg-indigo-600',   'light' => 'bg-indigo-50 border-indigo-100'],
            ['label' => 'Validées',               'value' => $evalsValidees,    'icon' => 'fas fa-check',         'color' => 'bg-teal-600',     'light' => 'bg-teal-50 border-teal-100'],
            ['label' => 'À traiter',              'value' => $evalsSoumises + $evalsBrouillon, 'icon' => 'fas fa-triangle-exclamation', 'color' => 'bg-rose-500', 'light' => 'bg-rose-50 border-rose-100'],
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

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 2 — CHARTS + PROFIL DG
        ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">

            {{-- Donut : santé évaluations --}}
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Santé des évaluations {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>

            {{-- Donut : fiches d'objectifs --}}
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches d'objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>

            {{-- Profil DG --}}
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Profil institutionnel</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Directeur(trice) Général(e)</h2>

                <div class="mt-4 flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-xl font-black text-white shadow-lg shadow-emerald-200/60">
                        {{ $dgInitiale }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-base font-black text-slate-900">{{ $dgNom ?: 'Non renseigné' }}</p>
                        <span class="mt-1 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-black text-emerald-700">
                            Administration centrale
                        </span>
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <dt class="font-black uppercase tracking-wider text-slate-400">Email</dt>
                        <dd class="mt-1 truncate font-semibold text-slate-700">{{ $entite->dg?->email ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <dt class="font-black uppercase tracking-wider text-slate-400">Ville</dt>
                        <dd class="mt-1 font-semibold text-slate-700">{{ $entite->ville ?: '—' }}</dd>
                    </div>
                </dl>

                {{-- Cabinet --}}
                @if ($personnelCabinet->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($personnelCabinet as $p)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $p['color'] }} text-sm">
                                    <i class="{{ $p['icon'] }}"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $p['role'] }}</p>
                                    <p class="truncate text-sm font-bold text-slate-800">
                                        {{ trim($p['agent']->prenom . ' ' . $p['agent']->nom) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 3 — FICHES DG RÉCENTES
        ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_340px]">

            {{-- Récapitulatif DG --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Directeur Général · {{ $annee }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Suivi des objectifs &amp; évaluations</h2>
                </div>
                <div class="p-6">
                    @if (!$dgUser)
                        <div class="py-8 text-center">
                            <i class="fas fa-user-slash text-2xl text-slate-200"></i>
                            <p class="mt-2 text-sm text-slate-400">Aucun Directeur Général assigné.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Fiches DG',    'value' => $totalFiches,     'color' => 'bg-emerald-50 border-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'fas fa-clipboard-list'],
                                ['label' => 'Acceptées',    'value' => $fichesAcceptees, 'color' => 'bg-teal-50 border-teal-100',        'text' => 'text-teal-700',    'icon' => 'fas fa-circle-check'],
                                ['label' => 'En attente',   'value' => $fichesEnAttente, 'color' => 'bg-amber-50 border-amber-100',      'text' => 'text-amber-700',   'icon' => 'fas fa-clock'],
                                ['label' => 'Avancement',   'value' => $tauxAvancement.'%', 'color' => 'bg-sky-50 border-sky-100',      'text' => 'text-sky-700',     'icon' => 'fas fa-gauge-high'],
                            ] as $s)
                            <div class="rounded-2xl border {{ $s['color'] }} px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $s['icon'] }} text-xs {{ $s['text'] }}"></i>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p>
                                </div>
                                <p class="mt-2 text-2xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Évaluations',  'value' => $evalsTotal,     'color' => 'bg-indigo-50 border-indigo-100',  'text' => 'text-indigo-700',  'icon' => 'fas fa-star'],
                                ['label' => 'Validées',      'value' => $evalsValidees,  'color' => 'bg-emerald-50 border-emerald-100','text' => 'text-emerald-700', 'icon' => 'fas fa-check'],
                                ['label' => 'Soumises',      'value' => $evalsSoumises,  'color' => 'bg-amber-50 border-amber-100',    'text' => 'text-amber-700',   'icon' => 'fas fa-paper-plane'],
                                ['label' => 'Note moy.',     'value' => $noteMoyenne > 0 ? number_format($noteMoyenne, 2, ',', ' ').'/10' : '—', 'color' => 'bg-violet-50 border-violet-100', 'text' => 'text-violet-700', 'icon' => 'fas fa-chart-bar'],
                            ] as $s)
                            <div class="rounded-2xl border {{ $s['color'] }} px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $s['icon'] }} text-xs {{ $s['text'] }}"></i>
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p>
                                </div>
                                <p class="mt-2 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            @if($objectifsEnabled)
                                <a href="{{ route('pca.objectifs.create') }}"
                                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700">
                                    <i class="fas fa-plus text-[10px]"></i> Nouvelle fiche objectifs
                                </a>
                            @else
                                <span title="Fonctionnalité désactivée par l'administrateur"
                                      class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl bg-slate-300 px-4 py-2 text-xs font-black text-slate-500 opacity-60 select-none">
                                    <i class="fas fa-plus text-[10px]"></i> Nouvelle fiche objectifs
                                </span>
                            @endif
                            @if($evaluationsEnabled)
                                <a href="{{ route('pca.evaluations.create') }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                                    <i class="fas fa-star text-[10px]"></i> Évaluer le DG
                                </a>
                            @else
                                <span title="Fonctionnalité désactivée par l'administrateur"
                                      class="inline-flex cursor-not-allowed items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-black text-slate-400 opacity-60 select-none">
                                    <i class="fas fa-star text-[10px]"></i> Évaluer le DG
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Fiches DG récentes --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">DG · Activité récente</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Fiches d'objectifs {{ $annee }}</h2>
                </div>

                @if ($fichesDGRecentes->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucune fiche pour {{ $annee }}</p>
                        @if($objectifsEnabled)
                            <a href="{{ route('pca.objectifs.create') }}"
                               class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-black text-white transition hover:bg-emerald-700">
                                <i class="fas fa-plus text-[10px]"></i> Créer
                            </a>
                        @else
                            <span title="Fonctionnalité désactivée par l'administrateur"
                                  class="mt-3 inline-flex cursor-not-allowed items-center gap-1.5 rounded-xl bg-slate-300 px-3 py-1.5 text-xs font-black text-slate-500 opacity-60 select-none">
                                <i class="fas fa-plus text-[10px]"></i> Créer
                            </span>
                        @endif
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($fichesDGRecentes as $fiche)
                            @php
                                $sc = match($fiche->statut) {
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',
                                    default      => 'bg-slate-100 text-slate-500',
                                };
                                $sl = match($fiche->statut) {
                                    'acceptee' => 'Acceptée', 'en_attente' => 'En attente', 'refusee' => 'Refusée', default => ucfirst($fiche->statut),
                                };
                                $av = (int) ($fiche->avancement_percentage ?? 0);
                                $avBar = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-200'));
                            @endphp
                            <div class="px-5 py-3 hover:bg-slate-50/60 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ $fiche->titre }}</p>
                                        <div class="mt-0.5 flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $av }}%</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('pca.objectifs.show', $fiche) }}"
                                       class="shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                </div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $avBar }}" style="width:{{ $av }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('pca.objectifs.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                            Voir tous les objectifs →
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════
             SECTION 4 — ALERTE OBJECTIFS EN COURS
        ═══════════════════════════════════════════════════════════════════════ --}}
        @if ($fichesEnAttente > 0)
            <div class="mt-5 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div class="flex-1">
                    <p class="font-black text-amber-800">
                        {{ $fichesEnAttente }} fiche(s) d'objectifs en attente de validation en {{ $annee }}
                    </p>
                    <p class="text-xs text-amber-600">Ces fiches nécessitent votre examen.</p>
                </div>
                <a href="{{ route('pca.objectifs.index') }}"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">
                    Voir les fiches
                </a>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
{{-- Données injectées avant le script pour éviter les faux positifs IDE --}}
<script>
window._pcaDashboard = {
    evalsDonut:  {!! json_encode($evalsDonut) !!},
    fichesDonut: {!! json_encode($fichesDonut) !!},
};
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var evalsData  = window._pcaDashboard.evalsDonut;
    var fichesData = window._pcaDashboard.fichesDonut;

    function totalFormatter(w) {
        return w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0);
    }

    function donutOptions(data) {
        return {
            chart: { type: 'donut', height: 200, fontFamily: 'Inter, sans-serif' },
            labels: data.labels,
            series: data.series,
            colors: data.colors,
            plotOptions: { pie: { donut: { size: '65%', labels: {
                show: true,
                total: { show: true, label: 'Total', fontSize: '12px', fontWeight: 700,
                         color: '#475569', formatter: totalFormatter }
            } } } },
            legend: { position: 'bottom', fontSize: '11px', fontWeight: 600 },
            dataLabels: { enabled: false },
            stroke: { width: 2 },
        };
    }

    // ── Donut : Santé évaluations ──
    if (document.querySelector('#chart-evals-donut')) {
        new ApexCharts(document.querySelector('#chart-evals-donut'), donutOptions(evalsData)).render();
    }

    // ── Donut : Fiches objectifs ──
    if (document.querySelector('#chart-fiches-donut')) {
        new ApexCharts(document.querySelector('#chart-fiches-donut'), donutOptions(fichesData)).render();
    }

});
</script>
@endpush
