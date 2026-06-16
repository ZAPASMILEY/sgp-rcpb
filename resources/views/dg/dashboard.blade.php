@extends('layouts.dg')

@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

@section('content')

<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- ── Hero Banner ────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10 shadow-md" style="background: linear-gradient(135deg, #003d20 0%, #005c30 50%, #008751 100%)">

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            {{-- Identity --}}
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl text-2xl font-black text-white shadow-lg ring-1 ring-white/20" style="background:rgba(255,255,255,0.15)">
                    {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-white/60">{{ auth()->user()?->agent?->role_genree ?? 'Directeur Général' }} · Pilotage Réseau</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">{{ auth()->user()->name }}</h1>
                    <p class="mt-0.5 text-sm text-white/70">Vue consolidée de toutes les évaluations du réseau RCPB</p>
                </div>
            </div>

            {{-- Filters in hero --}}
            @php
                $statutLabel = match($filters['statut']) {
                    'soumis' => 'Soumises', 'valide' => 'Validées', 'refuse' => 'Refusées', default => 'Tous statuts'
                };
                $anneeLabel = $annees->firstWhere('id', $filters['anneeId'])?->annee ?? 'Toutes années';
            @endphp
            <form method="GET" action="{{ route('dg.dashboard') }}" id="dg-filter-form" class="flex flex-wrap items-center gap-3">
                {{-- Champ recherche --}}
                <div class="flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 ring-1 ring-white/25 backdrop-blur-sm">
                    <i class="fas fa-search text-white/50 text-xs"></i>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Rechercher…"
                        class="w-36 bg-transparent text-sm font-semibold text-white placeholder-white/40 outline-none">
                </div>

                {{-- Hidden inputs soumis au form --}}
                <input type="hidden" name="statut" id="dg-input-statut" value="{{ $filters['statut'] }}">
                <input type="hidden" name="annee"  id="dg-input-annee"  value="{{ $filters['anneeId'] }}">

                {{-- Dropdown Statut --}}
                <div class="relative" id="dg-dd-statut">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur-sm cursor-pointer hover:bg-white/25 transition"
                        onclick="dgDdToggle('dg-dd-statut-menu')">
                        <span id="dg-label-statut">{{ $statutLabel }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dg-dd-statut-menu" class="dg-dd-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[11rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        @foreach (['' => 'Tous statuts', 'soumis' => 'Soumises', 'valide' => 'Validées', 'refuse' => 'Refusées'] as $val => $lbl)
                            <a href="#"
                               onclick="dgDdSelect('dg-input-statut','{{ $val }}','dg-label-statut','{{ $lbl }}');return false;"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50
                                      {{ $filters['statut'] === $val ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $lbl }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Dropdown Année --}}
                @if ($annees->isNotEmpty())
                <div class="relative" id="dg-dd-annee">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur-sm cursor-pointer hover:bg-white/25 transition"
                        onclick="dgDdToggle('dg-dd-annee-menu')">
                        <span id="dg-label-annee">{{ $anneeLabel }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dg-dd-annee-menu" class="dg-dd-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[9rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        <a href="#"
                           onclick="dgDdSelect('dg-input-annee','','dg-label-annee','Toutes années');return false;"
                           class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50
                                  {{ ! $filters['anneeId'] ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                            Toutes années
                        </a>
                        @foreach ($annees as $annee)
                            <a href="#"
                               onclick="dgDdSelect('dg-input-annee','{{ $annee->id }}','dg-label-annee','{{ $annee->annee }}');return false;"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50
                                      {{ $filters['anneeId'] == $annee->id ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $annee->annee }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Bouton Filtrer --}}
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:opacity-90" style="background:#008751">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>

                @if ($filters['search'] || $filters['statut'] || $filters['anneeId'])
                    <a href="{{ route('dg.dashboard') }}"
                       class="inline-flex items-center gap-1 rounded-xl bg-white/15 px-3 py-2.5 text-xs font-bold text-white/70 ring-1 ring-white/25 backdrop-blur-sm transition hover:bg-white/25">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </form>

            <script>
            function dgDdToggle(menuId) {
                const menu = document.getElementById(menuId);
                document.querySelectorAll('.dg-dd-menu').forEach(m => { if (m.id !== menuId) m.classList.add('hidden'); });
                menu.classList.toggle('hidden');
            }
            function dgDdSelect(inputId, value, labelId, label) {
                document.getElementById(inputId).value = value;
                document.getElementById(labelId).textContent = label;
                document.querySelectorAll('.dg-dd-menu').forEach(m => m.classList.add('hidden'));
                document.getElementById('dg-filter-form').submit();
            }
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#dg-dd-statut') && !e.target.closest('#dg-dd-annee')) {
                    document.querySelectorAll('.dg-dd-menu').forEach(m => m.classList.add('hidden'));
                }
            });
            </script>
        </div>

        {{-- Meta-stats inside hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
                $heroStats = [
                    ['label' => 'Total évaluations', 'value' => $stats['total'],   'icon' => 'fas fa-clipboard-list'],
                    ['label' => 'Validées',           'value' => $stats['valide'],  'icon' => 'fas fa-circle-check'],
                    ['label' => 'En attente',         'value' => $stats['soumis'],  'icon' => 'fas fa-hourglass-half'],
                    ['label' => 'Note excellente',    'value' => $stats['excellent'],'icon'=> 'fas fa-star'],
                ];
            @endphp
            @foreach ($heroStats as $hs)
            <div class="flex items-center gap-3 rounded-2xl bg-white/15 px-4 py-3 ring-1 ring-white/25 backdrop-blur-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white text-sm" style="background:rgba(255,255,255,0.15)">
                    <i class="{{ $hs['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-xl font-black text-white">{{ $hs['value'] }}</p>
                    <p class="text-[10px] font-semibold text-white/60">{{ $hs['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- ── Alerte agents sans évaluation (cliquable) ───────────────────── --}}
        @if ($openAnnee && $agentsSansEval > 0)
            <a href="{{ $filters['sansEval'] ? route('dg.dashboard') : route('dg.dashboard', ['sans_eval' => 1]) }}"
               class="flex items-center gap-4 rounded-2xl border px-5 py-4 transition hover:shadow-md
                      {{ $filters['sansEval'] ? 'border-orange-400 bg-orange-100 ring-2 ring-orange-300' : 'border-orange-200 bg-orange-50 hover:border-orange-300' }}">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-800">
                        {{ $agentsSansEval }} agent{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation validée — Année {{ $openAnnee->annee }}
                        <span class="ml-2 text-xs font-semibold text-orange-500">{{ $filters['sansEval'] ? '(cliquer pour masquer)' : '→ cliquer pour voir la liste' }}</span>
                    </p>
                    <p class="mt-0.5 text-xs text-orange-600">
                        Ces agents n'ont pas encore de note validée pour l'exercice en cours.
                    </p>
                </div>
                <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-2 text-xl font-black text-white shadow-sm">
                    {{ $agentsSansEval }}
                </span>
            </a>

            {{-- Liste agents sans évaluation --}}
            @if ($filters['sansEval'] && $listeSansEval->isNotEmpty())
            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-orange-200 overflow-hidden">
                <div class="border-b border-orange-100 bg-orange-50 px-5 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-black text-orange-800 shrink-0">
                        <i class="fas fa-user-xmark mr-2 text-orange-500"></i>
                        Agents sans évaluation validée — {{ $openAnnee->annee }}
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 sm:w-64">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                            <input id="se-search" type="text" placeholder="Rechercher…"
                                   class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-7 pr-3 text-xs font-semibold text-slate-700 outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-100">
                        </div>
                        <span id="se-count" class="shrink-0 rounded-full bg-orange-200 px-2.5 py-0.5 text-xs font-black text-orange-800">{{ $listeSansEval->count() }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table id="se-table" class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th data-col="0" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Nom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="1" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Prénom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="2" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Matricule <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="3" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Fonction <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th class="px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Téléphone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($listeSansEval as $ag)
                            <tr class="se-row hover:bg-orange-50/40 transition-colors">
                                <td class="px-5 py-2.5 font-semibold text-slate-800">{{ $ag->nom }}</td>
                                <td class="px-5 py-2.5 text-slate-700">{{ $ag->prenom }}</td>
                                <td class="px-5 py-2.5 text-slate-500 font-mono text-xs">{{ $ag->matricule ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-slate-500 text-xs">{{ $ag->role }}</td>
                                <td class="px-5 py-2.5">
                                    @if ($ag->numero_telephone)
                                        <a href="tel:{{ $ag->numero_telephone }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition">
                                            <i class="fas fa-phone text-[10px]"></i>{{ $ag->numero_telephone }}
                                        </a>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endif

        {{-- KPI cards (cliquables) ─────────────────────────────────────────── --}}
        @php
        $kpis = [
            ['label' => 'Total',          'value' => $stats['total'],       'icon' => 'fas fa-clipboard-list',       'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200',    'href' => route('dg.dashboard')],
            ['label' => 'Soumises',       'value' => $stats['soumis'],      'icon' => 'fas fa-paper-plane',          'color' => 'bg-amber-500',   'light' => 'bg-amber-50 border-amber-200',    'href' => route('dg.dashboard', ['statut' => 'soumis'])],
            ['label' => 'Validées',       'value' => $stats['valide'],      'icon' => 'fas fa-circle-check',         'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-200','href' => route('dg.dashboard', ['statut' => 'valide'])],
            ['label' => 'Excellent ≥8,5', 'value' => $stats['excellent'],   'icon' => 'fas fa-star',                 'color' => 'bg-emerald-500', 'light' => 'bg-emerald-50 border-emerald-100','href' => route('dg.dashboard', ['note' => 'excellent'])],
            ['label' => 'Bien 7–8,5',     'value' => $stats['bien'],        'icon' => 'fas fa-thumbs-up',            'color' => 'bg-sky-500',     'light' => 'bg-sky-50 border-sky-200',        'href' => route('dg.dashboard', ['note' => 'bien'])],
            ['label' => 'Passable 5–7',   'value' => $stats['passable'],    'icon' => 'fas fa-minus-circle',         'color' => 'bg-amber-400',   'light' => 'bg-amber-50 border-amber-100',    'href' => route('dg.dashboard', ['note' => 'passable'])],
            ['label' => 'Insuffisant <5', 'value' => $stats['insuffisant'], 'icon' => 'fas fa-triangle-exclamation', 'color' => 'bg-rose-500',    'light' => 'bg-rose-50 border-rose-200',      'href' => route('dg.dashboard', ['note' => 'insuffisant'])],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                @php $isActive = ($filters['statut'] !== '' && str_contains($kpi['href'], 'statut='.$filters['statut']))
                              || ($filters['note']   !== '' && str_contains($kpi['href'], 'note='.$filters['note']))
                              || ($kpi['href'] === route('dg.dashboard') && !$filters['statut'] && !$filters['note'] && !$filters['sansEval']); @endphp
                <a href="{{ $kpi['href'] }}"
                   class="flex flex-col rounded-[20px] border px-4 py-4 shadow-sm transition hover:shadow-md hover:-translate-y-0.5 {{ $kpi['light'] }} {{ $isActive ? 'ring-2 ring-offset-1 ring-slate-400' : '' }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                            <i class="{{ $kpi['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                </a>
            @endforeach
        </div>

        {{-- Charts + Performers row ────────────────────────────────────────── --}}
        <div class="grid gap-4 lg:grid-cols-3">

            {{-- Donut évaluations --}}
            <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 mb-4">Distribution des statuts</p>
                <div id="dg-eval-donut" class="h-52"></div>
            </div>

            {{-- Donut mentions --}}
            <div class="rounded-[24px] bg-white px-6 py-5 shadow-sm ring-1 ring-slate-100">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 mb-4">Distribution des mentions</p>
                <div id="dg-mention-donut" class="h-52"></div>
            </div>

            {{-- Top / Bottom performers --}}
            <div class="flex flex-col gap-3">
                @if ($topEval)
                <div class="flex items-center gap-4 rounded-[24px] bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-xl">
                        <i class="fas fa-trophy"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Meilleure note</p>
                        <p class="truncate text-sm font-bold text-slate-900">{{ $topEval->identification?->nom_prenom ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $topEval->identification?->emploi ?? $topEval->evaluable_role }}</p>
                    </div>
                    <span class="text-2xl font-black text-emerald-600">{{ number_format((float)$topEval->note_finale, 2, ',', ' ') }}</span>
                </div>
                @endif
                @if ($bottomEval)
                <div class="flex items-center gap-4 rounded-[24px] bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-500 text-xl">
                        <i class="fas fa-arrow-trend-down"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Note la plus basse</p>
                        <p class="truncate text-sm font-bold text-slate-900">{{ $bottomEval->identification?->nom_prenom ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $bottomEval->identification?->emploi ?? $bottomEval->evaluable_role }}</p>
                    </div>
                    <span class="text-2xl font-black text-rose-500">{{ number_format((float)$bottomEval->note_finale, 2, ',', ' ') }}</span>
                </div>
                @endif
                @if (!$topEval && !$bottomEval)
                <div class="flex flex-1 items-center justify-center rounded-[24px] bg-white px-5 py-8 shadow-sm ring-1 ring-slate-100 text-center">
                    <div>
                        <i class="fas fa-chart-bar text-3xl text-slate-200"></i>
                        <p class="mt-2 text-xs text-slate-400">Aucune évaluation validée</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Alert pending --}}
        @if ($stats['soumis'] > 0)
        <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3">
            <i class="fas fa-hourglass-half text-amber-500"></i>
            <p class="text-sm font-semibold text-amber-700">
                <span class="font-black">{{ $stats['soumis'] }}</span> évaluation(s) soumise(s) en attente de validation.
            </p>
        </div>
        @endif

        {{-- Tableau des évaluations (masqué si liste sans-eval active) ──────── --}}
        @if (!$filters['sansEval'])
        <section class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Réseau RCPB</p>
                    <h2 class="mt-0.5 text-sm font-black text-slate-800">
                        Évaluations du réseau
                        <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $evaluations->total() }}</span>
                    </h2>
                </div>
            </div>

            @if ($evaluations->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune évaluation trouvée.</p>
                </div>
            @else
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
                            @foreach ($evaluations as $eval)
                                @php
                                    $note    = (float) $eval->note_finale;
                                    $mention = $note >= 8.5 ? ['label' => 'Excellent',   'cls' => 'bg-emerald-100 text-emerald-700']
                                             : ($note >= 7  ? ['label' => 'Bien',         'cls' => 'bg-sky-100 text-sky-700']
                                             : ($note >= 5  ? ['label' => 'Passable',     'cls' => 'bg-amber-100 text-amber-700']
                                                            : ['label' => 'Insuffisant',  'cls' => 'bg-rose-100 text-rose-600']));
                                    $statutCls = match($eval->statut) {
                                        'valide'      => 'bg-emerald-100 text-emerald-700',
                                        'soumis'      => 'bg-amber-100 text-amber-700',
                                        'refuse'      => 'bg-rose-100 text-rose-600',
                                        'reclamation' => 'bg-orange-100 text-orange-700',
                                        default       => 'bg-slate-100 text-slate-600',
                                    };
                                    $statutLabel = match($eval->statut) {
                                        'valide'      => 'Validée',
                                        'soumis'      => 'Soumise',
                                        'refuse'      => 'Refusée',
                                        'reclamation' => 'Réclamation',
                                        default       => ucfirst($eval->statut),
                                    };
                                    $pct = $note > 0 ? min(100, $note * 10) : 0;
                                    $barColor = $note >= 8.5 ? 'bg-emerald-500' : ($note >= 7 ? 'bg-sky-500' : ($note >= 5 ? 'bg-amber-400' : 'bg-rose-500'));
                                    $nom = $eval->identification?->nom_prenom ?? '—';
                                    $emploi = $eval->identification?->emploi ?? $eval->evaluable_role ?? '—';
                                    $periode = $eval->date_debut?->format('m/Y').' – '.$eval->date_fin?->format('m/Y');
                                    $evalRoute = match(true) {
                                        strtolower($eval->evaluable_role ?? '') === 'dg'
                                            => route('dg.evaluations.show', $eval),
                                        in_array($eval->evaluable_role, ['DGA','Assistante_Dg','Conseillers_Dg'], true)
                                            => route('dg.sub-evaluations.show', $eval),
                                        default => null,
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $emploi }}</td>
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $periode }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if ($note > 0)
                                            <span class="text-base font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}</span>
                                            <div class="mt-1 h-1.5 w-20 rounded-full bg-slate-100 ml-auto">
                                                <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($note > 0)
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $mention['cls'] }}">{{ $mention['label'] }}</span>
                                        @else
                                            <span class="text-xs text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statutCls }}">{{ $statutLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $eval->evaluateur?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($evalRoute)
                                            <a href="{{ $evalRoute }}"
                                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                                                <i class="fas fa-eye text-[10px]"></i> Voir
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($evaluations->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $evaluations->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </section>
        @endif {{-- fin @if (!$filters['sansEval']) --}}

        </div>
    </div>
</div>

{{-- Page background slideshow --}}
<script>
(function () {
    const bgs = [
        document.getElementById('dg-bg-1'),
        document.getElementById('dg-bg-2'),
        document.getElementById('dg-bg-3'),
    ].filter(Boolean);
    if (!bgs.length) return;
    let current = 0;
    setInterval(function () {
        bgs[current].style.opacity = '0';
        current = (current + 1) % bgs.length;
        bgs[current].style.opacity = '1';
    }, 6000);
})();
</script>

{{-- Chart data --}}
<script>
window._dgEvalChart = {!! json_encode([
    'labels' => ['Validées', 'Soumises', 'Refusées'],
    'series' => [$stats['valide'], $stats['soumis'], 0],
    'colors' => ['#10b981', '#f59e0b', '#ef4444'],
]) !!};
window._dgMentionChart = {!! json_encode([
    'labels' => ['Excellent', 'Bien', 'Passable', 'Insuffisant'],
    'series' => [$stats['excellent'], $stats['bien'], $stats['passable'], $stats['insuffisant']],
    'colors' => ['#10b981', '#0ea5e9', '#f59e0b', '#ef4444'],
]) !!};
</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
(function () {
    function donutOpts(data, id) {
        var isEmpty = data.series.every(function(v){ return v === 0; });
        return {
            series: isEmpty ? [1] : data.series,
            labels: isEmpty ? ['Aucune donnée'] : data.labels,
            colors: isEmpty ? ['#e2e8f0'] : data.colors,
            chart: { type: 'donut', height: 200, fontFamily: 'inherit', toolbar: { show: false } },
            legend: { position: 'bottom', fontSize: '10px', fontWeight: 700,
                offsetY: 4, markers: { radius: 4, width: 8, height: 8 } },
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

    var evalData    = window._dgEvalChart;
    var mentionData = window._dgMentionChart;

    new ApexCharts(document.getElementById('dg-eval-donut'), donutOpts(evalData, 'eval')).render();
    new ApexCharts(document.getElementById('dg-mention-donut'), donutOpts(mentionData, 'mention')).render();
})();
</script>

<script>
(function () {
    const searchInput = document.getElementById('se-search');
    const countBadge  = document.getElementById('se-count');
    const table       = document.getElementById('se-table');
    if (!searchInput || !table) return;

    const tbody = table.querySelector('tbody');
    let sortCol = -1, sortAsc = true;

    // ── Recherche ──────────────────────────────────────────────────────────
    searchInput.addEventListener('input', function () {
        filterAndCount();
    });

    function filterAndCount() {
        const q    = searchInput.value.trim().toLowerCase();
        const rows = tbody.querySelectorAll('tr.se-row');
        let visible = 0;
        rows.forEach(function (tr) {
            const text = tr.textContent.toLowerCase();
            const show = q === '' || text.includes(q);
            tr.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        if (countBadge) countBadge.textContent = visible;
    }

    // ── Tri ────────────────────────────────────────────────────────────────
    table.querySelectorAll('th.se-th').forEach(function (th) {
        th.addEventListener('click', function () {
            const col = parseInt(th.dataset.col);
            if (sortCol === col) {
                sortAsc = !sortAsc;
            } else {
                sortCol = col;
                sortAsc = true;
            }

            // Mettre à jour les icônes
            table.querySelectorAll('th.se-th').forEach(function (h) {
                const icon = h.querySelector('i');
                if (!icon) return;
                icon.className = h === th
                    ? (sortAsc ? 'fas fa-sort-up ml-1 text-orange-500' : 'fas fa-sort-down ml-1 text-orange-500')
                    : 'fas fa-sort ml-1 opacity-40';
            });

            const rows = Array.from(tbody.querySelectorAll('tr.se-row'));
            rows.sort(function (a, b) {
                const va = a.cells[col]?.textContent.trim().toLowerCase() ?? '';
                const vb = b.cells[col]?.textContent.trim().toLowerCase() ?? '';
                return sortAsc ? va.localeCompare(vb, 'fr') : vb.localeCompare(va, 'fr');
            });
            rows.forEach(function (tr) { tbody.appendChild(tr); });
        });
    });
})();
</script>
@endsection
