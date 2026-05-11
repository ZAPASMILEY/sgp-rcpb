@extends('layouts.rh')

@section('title', 'Tableau de bord RH | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-purple-300/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            {{-- Identité RH --}}
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow-lg ring-1 ring-white/20">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-purple-200">Ressources Humaines · RCPB</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">Vue d'ensemble du réseau</h1>
                    <p class="mt-0.5 text-sm text-purple-100/80">Toutes les évaluations — agents et cadres confondus</p>
                </div>
            </div>

            {{-- Filtres rapides dans le hero (tab évaluations) --}}
            @if($filters['tab'] === 'evaluations')
            <form method="GET" action="{{ route('rh.dashboard') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="evaluations">
                <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 backdrop-blur-sm ring-1 ring-white/20">
                    <i class="fas fa-search text-purple-200 text-xs"></i>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Nom, emploi, évaluateur…"
                        class="w-40 bg-transparent text-sm font-semibold text-white placeholder-purple-300 outline-none">
                </div>
                <select name="statut" onchange="this.form.submit()"
                    class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm ring-1 ring-white/20 outline-none cursor-pointer">
                    <option value="" class="text-slate-900">Tous statuts</option>
                    <option value="soumis"    class="text-slate-900" {{ $filters['statut'] === 'soumis'    ? 'selected' : '' }}>Soumises</option>
                    <option value="valide"    class="text-slate-900" {{ $filters['statut'] === 'valide'    ? 'selected' : '' }}>Validées</option>
                    <option value="refuse"    class="text-slate-900" {{ $filters['statut'] === 'refuse'    ? 'selected' : '' }}>Refusées</option>
                    <option value="brouillon" class="text-slate-900" {{ $filters['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                </select>
                <input type="number" name="annee" value="{{ $filters['annee'] }}" min="2020" max="2035"
                    placeholder="Année"
                    class="w-24 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm ring-1 ring-white/20 outline-none placeholder-purple-300">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2.5 text-sm font-black text-white ring-1 ring-white/30 transition hover:bg-white/30">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                @if($filters['search'] || $filters['statut'] || $filters['annee'])
                    <a href="{{ route('rh.dashboard') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2.5 text-xs font-bold text-purple-200 ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
            @endif
        </div>

        {{-- Mini KPIs réseau dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach([
                ['label' => 'Total évaluations', 'value' => $stats['total'],    'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Validées',           'value' => $stats['valide'],   'icon' => 'fas fa-circle-check'],
                ['label' => 'En attente',         'value' => $stats['soumis'],   'icon' => 'fas fa-hourglass-half'],
                ['label' => 'Agents réseau',      'value' => $stats['agents'],   'icon' => 'fas fa-users'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="{{ $m['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-purple-200">{{ $m['label'] }}</p>
                    <p class="text-xl font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
    <div class="flex flex-col gap-6">

        {{-- ── Tabs navigation ──────────────────────────────────────────────── --}}
        <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1 self-start">
            @foreach([
                ['key' => 'evaluations', 'label' => 'Évaluations',  'icon' => 'fas fa-star-half-stroke'],
                ['key' => 'agents',      'label' => 'Agents',        'icon' => 'fas fa-users'],
                ['key' => 'objectifs',   'label' => 'Objectifs',     'icon' => 'fas fa-bullseye'],
            ] as $t)
            <a href="{{ route('rh.dashboard') }}?tab={{ $t['key'] }}"
               class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                   {{ $filters['tab'] === $t['key']
                       ? 'border border-slate-200 bg-white text-violet-700 shadow-sm'
                       : 'text-slate-500 hover:text-slate-800' }}">
                <i class="{{ $t['icon'] }} text-xs"></i>{{ $t['label'] }}
            </a>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB ÉVALUATIONS                                                   --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if($filters['tab'] === 'evaluations')

            {{-- KPI cards mentions --}}
            @php
            $kpis = [
                ['label' => 'Total',          'value' => $stats['total'],       'icon' => 'fas fa-clipboard-list',        'color' => 'bg-slate-700',    'light' => 'bg-white border-slate-200'],
                ['label' => 'Soumises',       'value' => $stats['soumis'],      'icon' => 'fas fa-paper-plane',           'color' => 'bg-amber-500',    'light' => 'bg-amber-50 border-amber-200'],
                ['label' => 'Validées',       'value' => $stats['valide'],      'icon' => 'fas fa-circle-check',          'color' => 'bg-emerald-600',  'light' => 'bg-emerald-50 border-emerald-200'],
                ['label' => 'Refusées',       'value' => $stats['refuse'],      'icon' => 'fas fa-circle-xmark',          'color' => 'bg-rose-500',     'light' => 'bg-rose-50 border-rose-200'],
                ['label' => 'Excellent ≥8,5', 'value' => $stats['excellent'],   'icon' => 'fas fa-star',                  'color' => 'bg-emerald-500',  'light' => 'bg-emerald-50 border-emerald-100'],
                ['label' => 'Bien 7–8,5',     'value' => $stats['bien'],        'icon' => 'fas fa-thumbs-up',             'color' => 'bg-sky-500',      'light' => 'bg-sky-50 border-sky-200'],
                ['label' => 'Passable 5–7',   'value' => $stats['passable'],    'icon' => 'fas fa-minus-circle',          'color' => 'bg-amber-400',    'light' => 'bg-amber-50 border-amber-100'],
                ['label' => 'Insuffisant <5', 'value' => $stats['insuffisant'], 'icon' => 'fas fa-triangle-exclamation',  'color' => 'bg-rose-500',     'light' => 'bg-rose-50 border-rose-200'],
            ];
            @endphp
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                @foreach($kpis as $kpi)
                    <div class="flex flex-col rounded-[20px] border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                                <i class="{{ $kpi['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Graphiques + Performers --}}
            <div class="grid gap-4 lg:grid-cols-3">

                {{-- Donut statuts --}}
                <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 mb-4">Distribution des statuts</p>
                    <div id="rh-statut-donut" class="h-52"></div>
                </div>

                {{-- Donut mentions --}}
                <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 mb-4">Distribution des mentions</p>
                    <div id="rh-mention-donut" class="h-52"></div>
                </div>

                {{-- Top / Bottom performers --}}
                <div class="flex flex-col gap-3">
                    @if($topEval)
                    <div class="flex items-center gap-4 rounded-[24px] bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-xl">
                            <i class="fas fa-trophy"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Meilleure note</p>
                            <p class="truncate text-sm font-bold text-slate-900">{{ $topEval->identification?->nom_prenom ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $topEval->identification?->emploi ?? $topEval->evaluable_role ?? '—' }}</p>
                        </div>
                        <span class="text-2xl font-black text-emerald-600">{{ number_format((float)$topEval->note_finale, 2, ',', ' ') }}</span>
                    </div>
                    @endif

                    @if($bottomEval)
                    <div class="flex items-center gap-4 rounded-[24px] bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-500 text-xl">
                            <i class="fas fa-arrow-trend-down"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Note la plus basse</p>
                            <p class="truncate text-sm font-bold text-slate-900">{{ $bottomEval->identification?->nom_prenom ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $bottomEval->identification?->emploi ?? $bottomEval->evaluable_role ?? '—' }}</p>
                        </div>
                        <span class="text-2xl font-black text-rose-500">{{ number_format((float)$bottomEval->note_finale, 2, ',', ' ') }}</span>
                    </div>
                    @endif

                    @if(!$topEval && !$bottomEval)
                    <div class="flex flex-1 items-center justify-center rounded-[24px] bg-white px-5 py-8 shadow-sm ring-1 ring-slate-100 text-center">
                        <div>
                            <i class="fas fa-chart-bar text-3xl text-slate-200"></i>
                            <p class="mt-2 text-xs text-slate-400">Aucune évaluation validée</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Alerte soumises --}}
            @if($stats['soumis'] > 0)
            <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3">
                <i class="fas fa-hourglass-half text-amber-500"></i>
                <p class="text-sm font-semibold text-amber-700">
                    <span class="font-black">{{ $stats['soumis'] }}</span> évaluation(s) en attente de validation dans le réseau.
                </p>
            </div>
            @endif

            {{-- Tableau complet des évaluations --}}
            <section class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Réseau RCPB</p>
                        <h2 class="mt-0.5 text-sm font-black text-slate-800">
                            Toutes les évaluations
                            @if($evaluations)
                                <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $evaluations->total() }}</span>
                            @endif
                        </h2>
                    </div>
                    <a href="{{ route('rh.formations.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-violet-700">
                        <i class="fas fa-graduation-cap"></i> Gérer les formations
                    </a>
                </div>

                @if($evaluations && $evaluations->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <i class="fas fa-inbox text-4xl text-slate-200"></i>
                        <p class="mt-3 text-sm font-semibold text-slate-400">Aucune évaluation trouvée.</p>
                    </div>
                @elseif($evaluations)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                                <tr>
                                    <th class="px-4 py-3">Évalué</th>
                                    <th class="px-4 py-3">Emploi / Rôle</th>
                                    <th class="px-4 py-3">Période</th>
                                    <th class="px-4 py-3 text-right">Note /10</th>
                                    <th class="px-4 py-3">Mention</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Évaluateur</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($evaluations as $eval)
                                    @php
                                        $note    = (float) $eval->note_finale;
                                        $mention = $note >= 8.5 ? ['label' => 'Excellent',   'cls' => 'bg-emerald-100 text-emerald-700']
                                                 : ($note >= 7  ? ['label' => 'Bien',         'cls' => 'bg-sky-100 text-sky-700']
                                                 : ($note >= 5  ? ['label' => 'Passable',     'cls' => 'bg-amber-100 text-amber-700']
                                                                : ['label' => 'Insuffisant',  'cls' => 'bg-rose-100 text-rose-600']));
                                        $statutCls = match($eval->statut) {
                                            'valide'    => 'bg-emerald-100 text-emerald-700',
                                            'soumis'    => 'bg-amber-100 text-amber-700',
                                            'refuse'    => 'bg-rose-100 text-rose-600',
                                            'brouillon' => 'bg-slate-100 text-slate-500',
                                            default     => 'bg-slate-100 text-slate-600',
                                        };
                                        $statutLabel = match($eval->statut) {
                                            'valide'    => 'Validée',
                                            'soumis'    => 'Soumise',
                                            'refuse'    => 'Refusée',
                                            'brouillon' => 'Brouillon',
                                            default     => ucfirst($eval->statut),
                                        };
                                        $pct      = $note > 0 ? min(100, $note * 10) : 0;
                                        $barColor = $note >= 8.5 ? 'bg-emerald-500' : ($note >= 7 ? 'bg-sky-500' : ($note >= 5 ? 'bg-amber-400' : 'bg-rose-500'));
                                        $nom      = $eval->identification?->nom_prenom ?? '—';
                                        $emploi   = $eval->identification?->emploi ?? $eval->evaluable_role ?? '—';
                                        $periode  = $eval->date_debut?->format('m/Y').' – '.$eval->date_fin?->format('m/Y');
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $nom }}</td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $emploi }}</td>
                                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap text-xs">{{ $periode }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if($note > 0)
                                                <span class="text-base font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}</span>
                                                <div class="mt-1 h-1.5 w-20 rounded-full bg-slate-100 ml-auto">
                                                    <div class="h-1.5 rounded-full {{ $barColor }}" style="width:{{ $pct }}%"></div>
                                                </div>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($note > 0)
                                                <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $mention['cls'] }}">{{ $mention['label'] }}</span>
                                            @else
                                                <span class="text-xs text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statutCls }}">{{ $statutLabel }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $eval->evaluateur?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('rh.evaluations.show', $eval) }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                                                <i class="fas fa-eye text-[10px]"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($evaluations->hasPages())
                        <div class="border-t border-slate-100 px-6 py-4">
                            {{ $evaluations->withQueryString()->links() }}
                        </div>
                    @endif
                @endif
            </section>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB AGENTS                                                         --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @elseif($filters['tab'] === 'agents')

            <form method="GET" action="{{ route('rh.dashboard') }}"
                  class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <input type="hidden" name="tab" value="agents">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, email, fonction…"
                           class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-100">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Délégation Technique</label>
                    <select name="dt_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-violet-300">
                        <option value="">Toutes</option>
                        @foreach($delegations as $dt)
                            <option value="{{ $dt->id }}" @selected(request('dt_id') == $dt->id)>{{ $dt->region }} — {{ $dt->ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Caisse</label>
                    <select name="caisse_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-violet-300">
                        <option value="">Toutes</option>
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Direction</label>
                    <select name="dir_id" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-violet-300">
                        <option value="">Toutes</option>
                        @foreach($directions as $d)
                            <option value="{{ $d->id }}" @selected(request('dir_id') == $d->id)>{{ $d->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-violet-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-800">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                <a href="{{ route('rh.dashboard') }}?tab=agents" class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                    Effacer
                </a>
            </form>

            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black text-slate-800">
                        Agents du réseau
                        @if($agents)<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $agents->total() }}</span>@endif
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Agent</th>
                                <th class="px-4 py-3">Fonction</th>
                                <th class="px-4 py-3">Structure</th>
                                <th class="px-4 py-3">Rôle système</th>
                                <th class="px-4 py-3">Email</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($agents as $agent)
                                @php
                                    $structure = $agent->caisse?->nom
                                        ?? ($agent->delegationTechnique ? $agent->delegationTechnique->region.' — '.$agent->delegationTechnique->ville : null)
                                        ?? $agent->direction?->nom ?? $agent->agence?->nom ?? $agent->service?->nom ?? '—';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-xs font-black text-violet-700">
                                                {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                                            </div>
                                            <p class="font-bold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $agent->fonction ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $structure }}</td>
                                    <td class="px-4 py-3">
                                        @if($agent->user)
                                            <span class="inline-flex items-center rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">{{ $agent->user->role }}</span>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $agent->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <i class="fas fa-users text-2xl text-slate-200"></i>
                                        <p class="mt-2 text-sm font-semibold text-slate-400">Aucun agent trouvé</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($agents?->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $agents->links() }}</div>
                @endif
            </div>

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB OBJECTIFS                                                      --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @else

            @if($ficheStats)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([
                    ['label' => 'Total',      'value' => $ficheStats['total'],      'tone' => 'border-slate-100 bg-white text-slate-900'],
                    ['label' => 'Acceptées',  'value' => $ficheStats['acceptee'],   'tone' => 'border-emerald-100 bg-emerald-50 text-emerald-900'],
                    ['label' => 'En attente', 'value' => $ficheStats['en_attente'], 'tone' => 'border-amber-100 bg-amber-50 text-amber-900'],
                    ['label' => 'Refusées',   'value' => $ficheStats['refusee'],    'tone' => 'border-rose-100 bg-rose-50 text-rose-900'],
                ] as $c)
                    <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $c['tone'] }}">
                        <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $c['label'] }}</p>
                        <p class="mt-1 text-3xl font-black">{{ $c['value'] }}</p>
                    </div>
                @endforeach
            </div>
            @endif

            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black text-slate-800">
                        Fiches d'objectifs
                        @if($fiches)<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $fiches->total() }}</span>@endif
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Fiche</th>
                                <th class="px-4 py-3">Assigné à</th>
                                <th class="px-4 py-3">Période</th>
                                <th class="px-4 py-3">Objectifs</th>
                                <th class="px-4 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($fiches as $fiche)
                                @php
                                    $assignable = $fiche->assignable;
                                    // Agent → nom + prenom ; User → name
                                    $assignableNom = $assignable instanceof \App\Models\Agent
                                        ? trim(($assignable->prenom ?? '').' '.($assignable->nom ?? ''))
                                        : ($assignable?->name ?? '—');
                                    $statutCls = match($fiche->statut) {
                                        'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                        'refusee'    => 'bg-rose-100 text-rose-600',
                                        default      => 'bg-amber-100 text-amber-700',
                                    };
                                    $statutLabel = match($fiche->statut) {
                                        'acceptee'   => 'Acceptée',
                                        'refusee'    => 'Refusée',
                                        'en_attente' => 'En attente',
                                        default      => 'Brouillon',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $fiche->titre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $assignableNom }}</td>
                                    <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">
                                        {{ $fiche->date_debut?->format('m/Y') ?? '—' }} → {{ $fiche->date_fin?->format('m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-black text-slate-900">{{ $fiche->objectifs_count }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statutCls }}">{{ $statutLabel }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                                        <p class="mt-2 text-sm font-semibold text-slate-400">Aucune fiche trouvée</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($fiches?->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $fiches->links() }}</div>
                @endif
            </div>

        @endif

    </div>
    </div>
</div>

{{-- Graphiques ApexCharts (tab évaluations uniquement) --}}
@if($filters['tab'] === 'evaluations')
<script>
window._rhStatutChart = {!! json_encode([
    'labels' => ['Validées', 'Soumises', 'Refusées', 'Brouillons'],
    'series' => [$stats['valide'], $stats['soumis'], $stats['refuse'], $stats['brouillon']],
    'colors' => ['#10b981', '#f59e0b', '#ef4444', '#94a3b8'],
]) !!};
window._rhMentionChart = {!! json_encode([
    'labels' => ['Excellent', 'Bien', 'Passable', 'Insuffisant'],
    'series' => [$stats['excellent'], $stats['bien'], $stats['passable'], $stats['insuffisant']],
    'colors' => ['#10b981', '#0ea5e9', '#f59e0b', '#ef4444'],
]) !!};
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    function donutOpts(data) {
        var isEmpty = data.series.every(function(v){ return v === 0; });
        return {
            series: isEmpty ? [1] : data.series,
            labels: isEmpty ? ['Aucune donnée'] : data.labels,
            colors: isEmpty ? ['#e2e8f0'] : data.colors,
            chart: { type: 'donut', height: 200, fontFamily: 'inherit', toolbar: { show: false } },
            legend: { position: 'bottom', fontSize: '10px', fontWeight: 700, offsetY: 4, markers: { radius: 4, width: 8, height: 8 } },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '68%',
                labels: { show: !isEmpty, total: { show: true, label: 'Total',
                    fontSize: '10px', fontWeight: 700, color: '#64748b',
                    formatter: function(w){ return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0); }
                }}}}},
            stroke: { width: 0 },
            tooltip: { theme: 'light' },
        };
    }
    new ApexCharts(document.getElementById('rh-statut-donut'),  donutOpts(window._rhStatutChart)).render();
    new ApexCharts(document.getElementById('rh-mention-donut'), donutOpts(window._rhMentionChart)).render();
})();
</script>
@endif

@endsection
