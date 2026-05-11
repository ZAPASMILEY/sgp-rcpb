@extends('layouts.pca')

@section('title', 'Statistiques PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-200">Espace PCA · Statistiques DG</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Statistiques</h1>
                <p class="mt-0.5 text-sm text-emerald-100/80">Suivi des objectifs et évaluations du Directeur Général · {{ $selectedYear }}</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <form method="GET" action="{{ route('pca.statistiques.index') }}" class="flex items-center gap-2">
                    <select name="annee" onchange="this.form.submit()"
                            class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm outline-none transition hover:bg-white/20">
                        @foreach ($availableYears as $year)
                            <option value="{{ $year }}" @selected($year === $selectedYear) class="text-slate-900 bg-white">{{ $year }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('pca.dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Tableau de bord
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        {{-- KPIs objectifs --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches DG</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $fichesDGCount }}</p>
                <p class="mt-1 text-xs text-slate-400">Total {{ $selectedYear }}</p>
            </div>
            <div class="rounded-[20px] border border-emerald-100 bg-emerald-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">Acceptées</p>
                <p class="mt-2 text-3xl font-black text-emerald-700">{{ $fichesAcceptees }}</p>
                <p class="mt-1 text-xs text-emerald-500">Fiches validées</p>
            </div>
            <div class="rounded-[20px] border border-amber-100 bg-amber-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-600">En attente</p>
                <p class="mt-2 text-3xl font-black text-amber-700">{{ $fichesEnAttente }}</p>
                <p class="mt-1 text-xs text-amber-500">À traiter</p>
            </div>
            <div class="rounded-[20px] border border-sky-100 bg-sky-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-sky-600">Avancement moy.</p>
                <p class="mt-2 text-3xl font-black text-sky-700">{{ $avancementMoyen }}<span class="text-lg font-bold text-sky-400">%</span></p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-sky-100">
                    <div class="h-full rounded-full bg-sky-400" style="width:{{ $avancementMoyen }}%"></div>
                </div>
            </div>
        </div>

        {{-- KPIs évaluations --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations DG</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $evaluationsDGCount }}</p>
                <p class="mt-1 text-xs text-slate-400">Total {{ $selectedYear }}</p>
            </div>
            <div class="rounded-[20px] border border-indigo-100 bg-indigo-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-indigo-600">Meilleure note</p>
                <p class="mt-2 text-3xl font-black text-indigo-700">{{ $meilleureNoteDG }}<span class="text-lg font-bold text-indigo-400">/10</span></p>
                <p class="mt-1 text-xs text-indigo-500">Note maximale validée</p>
            </div>
            <div class="rounded-[20px] border border-emerald-100 bg-emerald-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">Validées</p>
                <p class="mt-2 text-3xl font-black text-emerald-700">{{ $evaluationsAcceptees }}</p>
                <p class="mt-1 text-xs text-emerald-500">Évaluations acceptées</p>
            </div>
            <div class="rounded-[20px] border border-rose-100 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-rose-600">Rejetées</p>
                <p class="mt-2 text-3xl font-black text-rose-700">{{ $evaluationsRejetees }}</p>
                <p class="mt-1 text-xs text-rose-500">Évaluations rejetées</p>
            </div>
        </div>

        {{-- Répartition + Évaluations par statut --}}
        <div class="grid gap-4 lg:grid-cols-2">

            {{-- Distribution fiches / évaluations DG --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Vue DG · {{ $selectedYear }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Répartition des volumes</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                    @php $maxVal = max(1, ...array_values($distribution)); @endphp
                    @foreach ($distribution as $label => $value)
                        @php $pct = (int) round(($value / $maxVal) * 100); @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                                <span class="text-sm font-black text-slate-900">{{ $value }}</span>
                            </div>
                            <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Évaluations par statut --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations DG · {{ $selectedYear }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Répartition par statut</h2>
                </div>
                <div class="divide-y divide-slate-50 px-6 py-2">
                    @php
                        $statutStyles = [
                            'Brouillon' => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'dot' => 'bg-slate-400'],
                            'Soumis'    => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
                            'Valide'    => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                        ];
                    @endphp
                    @foreach ($evaluationsByStatut as $statut => $total)
                        @php $st = $statutStyles[$statut] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'dot' => 'bg-slate-400']; @endphp
                        <div class="flex items-center justify-between py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full {{ $st['dot'] }}"></span>
                                <span class="text-sm font-semibold text-slate-700">{{ $statut }}</span>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-black {{ $st['bg'] }} {{ $st['text'] }}">
                                {{ $total }}
                            </span>
                        </div>
                    @endforeach
                    @if ($evaluationsRejetees > 0)
                        <div class="flex items-center justify-between py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                <span class="text-sm font-semibold text-slate-700">Rejeté</span>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-sm font-black text-rose-700">
                                {{ $evaluationsRejetees }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alerte fiches en attente --}}
        @if ($fichesEnAttente > 0)
            <div class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div class="flex-1">
                    <p class="font-black text-amber-800">{{ $fichesEnAttente }} fiche(s) en attente de validation</p>
                    <p class="text-xs text-amber-600">Ces fiches d'objectifs du DG nécessitent votre examen.</p>
                </div>
                <a href="{{ route('pca.objectifs.index') }}"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">
                    Voir les fiches
                </a>
            </div>
        @endif

        </div>
    </div>
</div>
@endsection
