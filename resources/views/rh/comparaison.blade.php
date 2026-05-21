@extends('layouts.rh')

@section('title', 'Comparaison inter-période · RH · ' . config('app.name', 'SGP-RCPB'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

@php $a1 = $annee1?->annee ?? '—'; $a2 = $annee2?->annee ?? '—'; @endphp

<div class="min-h-screen bg-slate-50 pb-16">

    {{-- ── Bande titre ─────────────────────────────────────────────────────── --}}
    <div class="border-b border-slate-200 bg-white px-6 py-5 lg:px-10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- Titre --}}
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow">
                    <i class="fas fa-code-compare text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Espace RH · Pilotage</p>
                    <h1 class="text-lg font-black text-slate-900">Comparaison inter-période</h1>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rh.comparaison.index', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 transition hover:bg-red-100">
                    <i class="fas fa-file-pdf"></i> Télécharger PDF
                </a>
                <a href="{{ route('rh.dashboard') }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        {{-- ── Sélecteurs d'années ──────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('rh.comparaison.index') }}"
              class="mt-4 flex flex-wrap items-end gap-4 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4">

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-blue-600">
                    <span class="mr-1 inline-block h-2.5 w-2.5 rounded-sm bg-blue-600 align-middle"></span>Période A
                </label>
                <select name="annee1" onchange="this.form.submit()"
                        class="rounded-lg border border-blue-300 bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    @foreach ($annees as $a)
                        <option value="{{ $a->id }}" @selected($annee1?->id === $a->id)>{{ $a->annee }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end pb-2">
                <i class="fas fa-arrows-left-right text-slate-400"></i>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-orange-600">
                    <span class="mr-1 inline-block h-2.5 w-2.5 rounded-sm bg-orange-500 align-middle"></span>Période B
                </label>
                <select name="annee2" onchange="this.form.submit()"
                        class="rounded-lg border border-orange-300 bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200">
                    @foreach ($annees as $a)
                        <option value="{{ $a->id }}" @selected($annee2?->id === $a->id)>{{ $a->annee }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2 text-xs text-slate-400 pb-2">
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-blue-600"></span>A · {{ $a1 }}
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-orange-500"></span>B · {{ $a2 }}
                </span>
            </div>
        </form>
    </div>

    <div class="px-4 pt-6 lg:px-8 flex flex-col gap-6">

        {{-- ── Cartes KPI ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
                $kpis = [
                    ['label' => 'Note moyenne',    'v1' => $stats1['moyenne'],         'v2' => $stats2['moyenne'],         'dec' => true,  'unit' => '/10'],
                    ['label' => 'Évals validées',  'v1' => $stats1['validees'],        'v2' => $stats2['validees'],        'dec' => false, 'unit' => ''],
                    ['label' => 'Taux complétion', 'v1' => $stats1['taux_completion'], 'v2' => $stats2['taux_completion'], 'dec' => true,  'unit' => '%'],
                    ['label' => 'Agents évalués',  'v1' => $stats1['agents_evalues'],  'v2' => $stats2['agents_evalues'],  'dec' => false, 'unit' => ''],
                ];
            @endphp
            @foreach ($kpis as $k)
            @php
                $up = $k['v2'] > $k['v1'];
                $dn = $k['v2'] < $k['v1'];
                $f1 = $k['dec'] ? number_format($k['v1'],2,',',' ') : $k['v1'];
                $f2 = $k['dec'] ? number_format($k['v2'],2,',',' ') : $k['v2'];
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $k['label'] }}</p>
                <div class="mt-3 flex items-end justify-between gap-1">
                    <div>
                        <p class="text-[9px] font-bold uppercase text-blue-500">A · {{ $a1 }}</p>
                        <p class="text-lg font-black text-blue-700 leading-none mt-0.5">
                            {{ $f1 }}<span class="text-[10px] font-normal text-slate-400">{{ $k['unit'] }}</span>
                        </p>
                    </div>
                    <i class="fas text-sm mb-0.5 {{ $up ? 'fa-arrow-up text-emerald-500' : ($dn ? 'fa-arrow-down text-rose-500' : 'fa-minus text-slate-300') }}"></i>
                    <div class="text-right">
                        <p class="text-[9px] font-bold uppercase text-orange-500">B · {{ $a2 }}</p>
                        <p class="text-lg font-black leading-none mt-0.5 {{ $up ? 'text-emerald-600' : ($dn ? 'text-rose-600' : 'text-orange-600') }}">
                            {{ $f2 }}<span class="text-[10px] font-normal text-slate-400">{{ $k['unit'] }}</span>
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Tableau comparatif ───────────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-100 px-6 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Détail complet des indicateurs RH</p>
                <h2 class="mt-0.5 text-base font-black text-slate-900">
                    <span class="text-blue-700">{{ $a1 }}</span>
                    <span class="mx-2 font-normal text-slate-300">vs</span>
                    <span class="text-orange-600">{{ $a2 }}</span>
                </h2>
            </div>

            @php
                $sections = [
                    'Évaluations' => [
                        ['label' => 'Total (hors brouillons)',  'key' => 'total',           'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Validées',                 'key' => 'validees',        'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Soumises',                 'key' => 'soumises',        'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Refusées',                 'key' => 'refusees',        'unit' => '',    'inv' => true,  'dec' => false],
                        ['label' => 'Brouillons',               'key' => 'brouillons',      'unit' => '',    'inv' => true,  'dec' => false],
                        ['label' => 'Note moyenne',             'key' => 'moyenne',         'unit' => '/10', 'inv' => false, 'dec' => true],
                        ['label' => 'Meilleure note',           'key' => 'meilleure',       'unit' => '/10', 'inv' => false, 'dec' => true],
                        ['label' => 'Note la plus basse',       'key' => 'pire',            'unit' => '/10', 'inv' => true,  'dec' => true],
                    ],
                    'Distribution des appréciations' => [
                        ['label' => 'Excellent  ≥ 8,5/10',     'key' => 'excellent',       'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Bien  7 – 8,49/10',       'key' => 'bien',            'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Passable  5 – 6,99/10',   'key' => 'passable',        'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Insuffisant  < 5/10',     'key' => 'insuffisant',     'unit' => '',    'inv' => true,  'dec' => false],
                    ],
                    'Performance par genre' => [
                        ['label' => 'Note moy. Hommes',        'key' => 'moy_hommes',      'unit' => '/10', 'inv' => false, 'dec' => true],
                        ['label' => 'Note moy. Femmes',        'key' => 'moy_femmes',      'unit' => '/10', 'inv' => false, 'dec' => true],
                    ],
                    'Fiches objectifs' => [
                        ['label' => 'Total fiches',            'key' => 'fiches',           'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'Acceptées',               'key' => 'fiches_acceptees', 'unit' => '',    'inv' => false, 'dec' => false],
                        ['label' => 'En attente',              'key' => 'fiches_attente',   'unit' => '',    'inv' => true,  'dec' => false],
                        ['label' => 'Refusées',                'key' => 'fiches_refusees',  'unit' => '',    'inv' => true,  'dec' => false],
                    ],
                    'Couverture agents' => [
                        ['label' => 'Agents évalués',          'key' => 'agents_evalues',  'unit' => '',   'inv' => false, 'dec' => false],
                        ['label' => 'Total agents',            'key' => 'total_agents',    'unit' => '',   'inv' => false, 'dec' => false],
                        ['label' => 'Taux de complétion',      'key' => 'taux_completion', 'unit' => '%',  'inv' => false, 'dec' => true],
                    ],
                ];
            @endphp

            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-3 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 w-[45%]">Indicateur</th>
                        <th class="px-4 py-3 text-center w-[18%]">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-blue-600">
                                <span class="h-2 w-2 rounded-full bg-blue-600"></span>{{ $a1 }}
                            </span>
                        </th>
                        <th class="px-2 py-3 text-center text-[10px] font-black text-slate-300 w-[10%]">Δ</th>
                        <th class="px-4 py-3 text-center w-[18%]">
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-orange-600">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span>{{ $a2 }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sections as $sectionTitle => $rows)
                        <tr class="bg-slate-50/80 border-y border-slate-100">
                            <td colspan="4" class="px-6 py-2 text-[10px] font-black uppercase tracking-widest text-slate-500">
                                {{ $sectionTitle }}
                            </td>
                        </tr>
                        @foreach ($rows as $row)
                        @php
                            $v1   = $stats1[$row['key']] ?? 0;
                            $v2   = $stats2[$row['key']] ?? 0;
                            $inv  = $row['inv'];
                            $dec  = $row['dec'];
                            $up   = $v2 > $v1;
                            $dn   = $v2 < $v1;
                            $good = $inv ? $dn : $up;
                            $bad  = $inv ? $up : $dn;
                            $diff = $v2 - $v1;
                            $f1   = $dec ? number_format($v1,2,',',' ') : $v1;
                            $f2   = $dec ? number_format($v2,2,',',' ') : $v2;
                            $df   = ($diff > 0 ? '+' : '') . ($dec ? number_format($diff,2,',',' ') : $diff);
                        @endphp
                        <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $row['label'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-baseline gap-0.5 rounded-lg bg-blue-50 px-3 py-1.5 font-black text-blue-700 text-sm">
                                    {{ $f1 }}@if($row['unit'])<span class="text-[10px] font-normal text-blue-400 ml-0.5">{{ $row['unit'] }}</span>@endif
                                </span>
                            </td>
                            <td class="px-2 py-3 text-center">
                                @if($diff != 0)
                                <span class="text-xs font-black {{ $good ? 'text-emerald-600' : 'text-rose-600' }}">
                                    <i class="fas {{ $up ? 'fa-arrow-up' : 'fa-arrow-down' }} text-[8px]"></i>
                                    {{ $df }}
                                </span>
                                @else
                                <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-baseline gap-0.5 rounded-lg px-3 py-1.5 font-black text-sm
                                    @if($good) bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200
                                    @elseif($bad) bg-rose-50 text-rose-700 ring-1 ring-rose-200
                                    @else bg-orange-50 text-orange-700
                                    @endif">
                                    {{ $f2 }}@if($row['unit'])<span class="text-[10px] font-normal opacity-60 ml-0.5">{{ $row['unit'] }}</span>@endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Graphique + Appréciations ────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-100 px-6 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Visualisation comparative</p>
                <h2 class="mt-0.5 text-base font-black text-slate-900">
                    Appréciations · <span class="text-blue-700">{{ $a1 }}</span> vs <span class="text-orange-600">{{ $a2 }}</span>
                </h2>
            </div>
            <div class="grid gap-6 px-6 py-5 lg:grid-cols-[1fr_320px]">
                <div class="relative h-56 lg:h-auto">
                    <canvas id="appChart"></canvas>
                </div>

                <div class="flex flex-col gap-2">
                    {{-- Excellent --}}
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                        <i class="fas fa-star w-4 text-center text-emerald-500"></i>
                        <span class="flex-1 text-sm font-bold text-emerald-800">Excellent</span>
                        <span class="w-6 text-right text-sm font-black text-blue-700">{{ $stats1['excellent'] }}</span>
                        <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                        <span class="w-6 text-sm font-black text-orange-600">{{ $stats2['excellent'] }}</span>
                        @php $d = $stats2['excellent'] - $stats1['excellent']; @endphp
                        @if($d != 0) <span class="w-8 text-right text-xs font-black {{ $d > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span>
                        @else <span class="w-8"></span> @endif
                    </div>
                    {{-- Bien --}}
                    <div class="flex items-center gap-3 rounded-xl border border-sky-100 bg-sky-50 px-4 py-3">
                        <i class="fas fa-thumbs-up w-4 text-center text-sky-500"></i>
                        <span class="flex-1 text-sm font-bold text-sky-800">Bien</span>
                        <span class="w-6 text-right text-sm font-black text-blue-700">{{ $stats1['bien'] }}</span>
                        <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                        <span class="w-6 text-sm font-black text-orange-600">{{ $stats2['bien'] }}</span>
                        @php $d = $stats2['bien'] - $stats1['bien']; @endphp
                        @if($d != 0) <span class="w-8 text-right text-xs font-black {{ $d > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span>
                        @else <span class="w-8"></span> @endif
                    </div>
                    {{-- Passable --}}
                    <div class="flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
                        <i class="fas fa-circle-half-stroke w-4 text-center text-amber-500"></i>
                        <span class="flex-1 text-sm font-bold text-amber-800">Passable</span>
                        <span class="w-6 text-right text-sm font-black text-blue-700">{{ $stats1['passable'] }}</span>
                        <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                        <span class="w-6 text-sm font-black text-orange-600">{{ $stats2['passable'] }}</span>
                        @php $d = $stats2['passable'] - $stats1['passable']; @endphp
                        @if($d != 0) <span class="w-8 text-right text-xs font-black {{ $d < 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span>
                        @else <span class="w-8"></span> @endif
                    </div>
                    {{-- Insuffisant --}}
                    <div class="flex items-center gap-3 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3">
                        <i class="fas fa-triangle-exclamation w-4 text-center text-rose-500"></i>
                        <span class="flex-1 text-sm font-bold text-rose-800">Insuffisant</span>
                        <span class="w-6 text-right text-sm font-black text-blue-700">{{ $stats1['insuffisant'] }}</span>
                        <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                        <span class="w-6 text-sm font-black text-orange-600">{{ $stats2['insuffisant'] }}</span>
                        @php $d = $stats2['insuffisant'] - $stats1['insuffisant']; @endphp
                        @if($d != 0) <span class="w-8 text-right text-xs font-black {{ $d < 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $d > 0 ? '+' : '' }}{{ $d }}</span>
                        @else <span class="w-8"></span> @endif
                    </div>

                    {{-- Genre --}}
                    <div class="mt-1 grid grid-cols-2 gap-2">
                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2.5">
                            <p class="text-[10px] font-black uppercase tracking-wide text-blue-500">
                                <i class="fas fa-mars mr-1"></i>Note Hommes
                            </p>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-sm font-black text-blue-700">{{ $stats1['moy_hommes'] > 0 ? number_format($stats1['moy_hommes'],2,',',' ') : '—' }}</span>
                                <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                                <span class="text-sm font-black text-orange-600">{{ $stats2['moy_hommes'] > 0 ? number_format($stats2['moy_hommes'],2,',',' ') : '—' }}</span>
                            </div>
                        </div>
                        <div class="rounded-xl border border-pink-100 bg-pink-50 px-3 py-2.5">
                            <p class="text-[10px] font-black uppercase tracking-wide text-pink-500">
                                <i class="fas fa-venus mr-1"></i>Note Femmes
                            </p>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-sm font-black text-blue-700">{{ $stats1['moy_femmes'] > 0 ? number_format($stats1['moy_femmes'],2,',',' ') : '—' }}</span>
                                <i class="fas fa-arrow-right text-[10px] text-slate-300"></i>
                                <span class="text-sm font-black text-orange-600">{{ $stats2['moy_femmes'] > 0 ? number_format($stats2['moy_femmes'],2,',',' ') : '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-xl bg-slate-50 px-4 py-2.5 text-xs text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm bg-blue-600"></span>Période A · {{ $a1 }}</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded-sm bg-orange-500"></span>Période B · {{ $a2 }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Bilans côte à côte ───────────────────────────────────────────────── --}}
        <div class="grid gap-4 lg:grid-cols-2">

            {{-- Période A --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-blue-100">
                <div class="border-b border-blue-100 bg-blue-50 px-6 py-4 flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-xs font-black text-white">A</span>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-blue-400">Période A</p>
                        <h2 class="text-base font-black text-blue-900">Bilan {{ $a1 }}</h2>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Évals total</p>
                            <p class="text-xl font-black text-slate-900">{{ $stats1['total'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Validées</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats1['validees'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Excellent</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats1['excellent'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Insuffisant</p>
                            <p class="text-xl font-black text-rose-600">{{ $stats1['insuffisant'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Fiches total</p>
                            <p class="text-xl font-black text-slate-900">{{ $stats1['fiches'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Acceptées</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats1['fiches_acceptees'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Agents évalués</p>
                            <p class="text-xl font-black text-slate-700">{{ $stats1['agents_evalues'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Agents total</p>
                            <p class="text-xl font-black text-slate-700">{{ $stats1['total_agents'] }}</p>
                        </div>
                    </div>
                    @if($stats1['moyenne'] > 0)
                    <div class="flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <span class="text-sm font-bold text-blue-700">Note moyenne</span>
                        <span class="text-2xl font-black text-blue-800">
                            {{ number_format($stats1['moyenne'],2,',',' ') }}<span class="text-sm font-normal text-blue-400">/10</span>
                        </span>
                    </div>
                    @endif
                    <div>
                        <div class="mb-1.5 flex justify-between text-sm">
                            <span class="font-bold text-slate-600">Complétion</span>
                            <span class="font-black text-blue-700">{{ $stats1['taux_completion'] }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-blue-600 transition-all" style="width:{{ $stats1['taux_completion'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Période B --}}
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-orange-100">
                <div class="border-b border-orange-100 bg-orange-50 px-6 py-4 flex items-center gap-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-orange-500 text-xs font-black text-white">B</span>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-400">Période B</p>
                        <h2 class="text-base font-black text-orange-900">Bilan {{ $a2 }}</h2>
                    </div>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Évals total</p>
                            <p class="text-xl font-black text-slate-900">{{ $stats2['total'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Validées</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats2['validees'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Excellent</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats2['excellent'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Insuffisant</p>
                            <p class="text-xl font-black text-rose-600">{{ $stats2['insuffisant'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Fiches total</p>
                            <p class="text-xl font-black text-slate-900">{{ $stats2['fiches'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Acceptées</p>
                            <p class="text-xl font-black text-emerald-600">{{ $stats2['fiches_acceptees'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Agents évalués</p>
                            <p class="text-xl font-black text-slate-700">{{ $stats2['agents_evalues'] }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Agents total</p>
                            <p class="text-xl font-black text-slate-700">{{ $stats2['total_agents'] }}</p>
                        </div>
                    </div>
                    @if($stats2['moyenne'] > 0)
                    <div class="flex items-center justify-between rounded-xl border border-orange-100 bg-orange-50 px-4 py-3">
                        <span class="text-sm font-bold text-orange-700">Note moyenne</span>
                        <span class="text-2xl font-black text-orange-800">
                            {{ number_format($stats2['moyenne'],2,',',' ') }}<span class="text-sm font-normal text-orange-400">/10</span>
                        </span>
                    </div>
                    @endif
                    <div>
                        <div class="mb-1.5 flex justify-between text-sm">
                            <span class="font-bold text-slate-600">Complétion</span>
                            <span class="font-black text-orange-600">{{ $stats2['taux_completion'] }}%</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-orange-500 transition-all" style="width:{{ $stats2['taux_completion'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var s1 = @json($stats1);
    var s2 = @json($stats2);
    var a1 = '{{ $a1 }}';
    var a2 = '{{ $a2 }}';
    var ctx = document.getElementById('appChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Excellent', 'Bien', 'Passable', 'Insuffisant'],
            datasets: [
                {
                    label: 'Période A · ' + a1,
                    data: [s1.excellent, s1.bien, s1.passable, s1.insuffisant],
                    backgroundColor: 'rgba(37,99,235,.80)',
                    borderColor: 'rgba(37,99,235,1)',
                    borderWidth: 1,
                    borderRadius: 6,
                },
                {
                    label: 'Période B · ' + a2,
                    data: [s2.excellent, s2.bien, s2.passable, s2.insuffisant],
                    backgroundColor: 'rgba(249,115,22,.80)',
                    borderColor: 'rgba(249,115,22,1)',
                    borderWidth: 1,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top', labels: { font: { family: 'Inter', weight: '700', size: 11 }, padding: 16 } },
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: { family: 'Inter', size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { ticks: { font: { family: 'Inter', weight: '700', size: 11 } }, grid: { display: false } },
            },
        },
    });
})();
</script>
@endpush
@endsection
