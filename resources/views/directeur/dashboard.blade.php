@extends('layouts.directeur')

@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ══════════════════════════ HERO ══════════════════════════════════════ --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
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
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">
                        {{ $direction->nom ?? 'Direction' }} · Pilotage
                    </p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-white/60">
                        {{ match($user->role) {
                            'Directeur_Direction' => 'Directeur de Direction',
                            'Directeur_Technique' => 'Directeur Technique',
                            'Directeur_Caisse'    => 'Directeur de Caisse',
                            default               => $user->role,
                        } }} · Synthèse du {{ now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            {{-- Sélecteur d'année --}}
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-white/70">Année</span>
                <form method="GET" action="{{ route('directeur.dashboard') }}" id="year-form">
                    <select name="annee" onchange="document.getElementById('year-form').submit()"
                            class="rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm outline-none transition hover:bg-white/20 focus:ring-2 focus:ring-white/30">
                        @foreach ($anneesDisponibles as $yr)
                            <option value="{{ $yr }}" @selected($yr === $annee) class="text-slate-900 bg-white">{{ $yr }}</option>
                        @endforeach
                    </select>
                </form>
                @if($evaluationsEnabled)
                    <a href="{{ route('directeur.evaluations.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                        <i class="fas fa-plus text-xs"></i> Évaluer
                    </a>
                @else
                    <span title="Fonctionnalité désactivée par l'administrateur"
                          class="ent-btn-disabled-dark">
                        <i class="fas fa-plus text-xs"></i> Évaluer
                    </span>
                @endif
            </div>
        </div>

        {{-- Mini KPIs dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Fiches reçues',      'value' => $fichesRecStats['total'],    'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Évaluations reçues', 'value' => $evalsRecStats['total'],     'icon' => 'fas fa-star'],
                ['label' => 'Avancement',          'value' => $tauxAvancement.'%',         'icon' => 'fas fa-gauge-high'],
                ['label' => 'Note moy. équipe',   'value' => $noteMoyenneEquipe > 0 ? number_format($noteMoyenneEquipe, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="{{ $m['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
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

        {{-- ── Alerte agents sans évaluation ──────────────────────────────── --}}
        @if ($openAnnee && $agentsSansEval > 0)
            <div class="mb-4 flex items-center gap-4 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-800">
                        {{ $agentsSansEval }} agent{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation validée — Année {{ $openAnnee->annee }}
                    </p>
                    <p class="mt-0.5 text-xs text-orange-600">
                        Ces agents n'ont pas encore de note validée pour l'exercice en cours. Pensez à finaliser leurs évaluations avant la clôture.
                    </p>
                </div>
                <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-2 text-xl font-black text-white shadow-sm">
                    {{ $agentsSansEval }}
                </span>
            </div>
        @endif

        {{-- ══════════════════════ COUVERTURE ÉVALUATION ══════════════════════ --}}
        @if($openAnnee && $totalAgents > 0)
        @php $tauxCouv = $totalAgents > 0 ? round($agentsEvalues / $totalAgents * 100) : 0; @endphp
        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $agentsSansEval === 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                        <i class="fas fa-users-viewfinder"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Couverture · {{ $openAnnee->annee }}</p>
                        <p class="text-sm font-black text-slate-900">Évaluation des agents</p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <p class="text-2xl font-black text-emerald-600">{{ $agentsEvalues }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Évalués</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black {{ $agentsSansEval > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ $agentsSansEval }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Restants</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-black text-slate-700">{{ $totalAgents }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Total</p>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-1.5">
                    <span>Progression</span>
                    <span class="{{ $tauxCouv === 100 ? 'text-emerald-600' : ($tauxCouv >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $tauxCouv }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $tauxCouv === 100 ? 'bg-emerald-500' : ($tauxCouv >= 50 ? 'bg-amber-400' : 'bg-rose-500') }}"
                         style="width:{{ $tauxCouv }}%"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════ KPI CARDS ═══════════════════════════════════ --}}
        @php
        $kpis = [
            ['label' => 'Fiches reçues',    'value' => $fichesRecStats['total'],      'meta' => 'Objectifs assignés au directeur',       'icon' => 'fas fa-clipboard-list', 'valueClass' => 'text-slate-700',   'iconClass' => 'bg-slate-100 text-slate-600',    'href' => route('directeur.dashboard')],
            ['label' => 'Acceptées',        'value' => $fichesRecStats['acceptees'],  'meta' => 'Fiches objectifs acceptées',            'icon' => 'fas fa-circle-check',   'valueClass' => 'text-emerald-600', 'iconClass' => 'bg-emerald-50 text-emerald-600', 'href' => route('directeur.dashboard')],
            ['label' => 'En attente',       'value' => $fichesRecStats['en_attente'], 'meta' => 'Fiches en cours de traitement',         'icon' => 'fas fa-clock',          'valueClass' => 'text-amber-500',   'iconClass' => 'bg-amber-50 text-amber-500',     'href' => route('directeur.dashboard')],
            ['label' => 'Avancement moy.',  'value' => $tauxAvancement.'%',           'meta' => 'Taux moyen de réalisation',             'icon' => 'fas fa-gauge-high',     'valueClass' => 'text-sky-500',     'iconClass' => 'bg-sky-50 text-sky-500',         'href' => route('directeur.dashboard')],
            ['label' => 'Évals. reçues',    'value' => $evalsRecStats['total'],       'meta' => 'Évaluations reçues du DGA/DG',         'icon' => 'fas fa-star',           'valueClass' => 'text-indigo-500',  'iconClass' => 'bg-indigo-50 text-indigo-500',   'href' => route('directeur.dashboard')],
            ['label' => 'Validées',         'value' => $evalsRecStats['valide'],      'meta' => 'Évaluations acceptées et validées',     'icon' => 'fas fa-check',          'valueClass' => 'text-teal-600',    'iconClass' => 'bg-teal-50 text-teal-600',       'href' => route('directeur.dashboard')],
            ['label' => 'Évals. données',   'value' => $evalsGivStats['total'],       'meta' => 'Évaluations données aux chefs',         'icon' => 'fas fa-pen-to-square',  'valueClass' => 'text-rose-500',    'iconClass' => 'bg-rose-50 text-rose-500',       'href' => route('directeur.evaluations.create')],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                <article class="rounded-[20px] border border-slate-100 bg-white px-4 py-3 shadow-[0_12px_30px_-24px_rgba(15,23,42,0.3)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 leading-tight">{{ $kpi['label'] }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight {{ $kpi['valueClass'] }}">{{ $kpi['value'] }}</p>
                            <p class="mt-1 line-clamp-1 text-[11px] font-bold text-slate-400">{{ $kpi['meta'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $kpi['iconClass'] }}">
                            <i class="{{ $kpi['icon'] }} text-base"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <a href="{{ $kpi['href'] }}" class="inline-flex h-8 items-center rounded-xl bg-slate-50 px-3 text-[10px] font-black uppercase tracking-[0.14em] text-slate-700 transition hover:bg-slate-900 hover:text-white">
                            Ouvrir
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- ══════════════════════ CHARTS + CONTEXT ════════════════════════════ --}}
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

            {{-- Évaluations données à l'équipe --}}
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Pilotage équipe {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Évaluations données</h2>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => 'Total',      'value' => $evalsGivStats['total'],     'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-700'],
                        ['label' => 'Validées',   'value' => $evalsGivStats['valide'],    'tone' => 'bg-emerald-50 border-emerald-100', 'text' => 'text-emerald-700'],
                        ['label' => 'Soumises',   'value' => $evalsGivStats['soumis'],    'tone' => 'bg-amber-50 border-amber-100',     'text' => 'text-amber-700'],
                        ['label' => 'Brouillons', 'value' => $evalsGivStats['brouillon'], 'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-500'],
                    ] as $s)
                    <div class="rounded-xl border {{ $s['tone'] }} px-3 py-2.5">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s['label'] }}</p>
                        <p class="mt-1 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 flex gap-2">
                    @if($evaluationsEnabled)
                        <a href="{{ route('directeur.evaluations.create') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-plus text-[10px]"></i> Évaluer
                        </a>
                    @else
                        <span title="Fonctionnalité désactivée par l'administrateur"
                              class="ent-btn-disabled-light">
                            <i class="fas fa-plus text-[10px]"></i> Évaluer
                        </span>
                    @endif
                    <a href="{{ route('directeur.mon-espace') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                        <i class="fas fa-folder-open text-[10px]"></i> Mon espace
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════════ FICHES RÉCENTES + ÉQUIPE ════════════════════ --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_340px]">

            {{-- Récapitulatif des services --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mon équipe · {{ $annee }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Suivi des services</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($servicesOverview as $s)
                        @php
                            $es = $s['eval'];
                            $sc = match($es?->statut) {
                                'valide' => 'bg-emerald-100 text-emerald-700',
                                'soumis' => 'bg-amber-100 text-amber-700',
                                default  => 'bg-slate-100 text-slate-500',
                            };
                            $sl = match($es?->statut) {
                                'valide' => 'Validée', 'soumis' => 'Soumise', default => 'Non évalué',
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-3 px-6 py-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 text-sm">
                                    <i class="fas fa-sitemap"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ $s['service']->nom }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $s['agents_count'] }} agent(s)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                @if ($es?->note_finale)
                                    <span class="text-sm font-black text-slate-700">{{ number_format($es->note_finale, 1) }}/10</span>
                                @endif
                                @if($evaluationsEnabled)
                                    <a href="{{ route('directeur.evaluations.create') }}"
                                       class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                @else
                                    <span title="Fonctionnalité désactivée"
                                          class="inline-flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 text-slate-400 select-none">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <i class="fas fa-sitemap text-2xl text-slate-200"></i>
                            <p class="mt-2 text-sm text-slate-400">Aucun service rattaché.</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 px-6 py-3">
                    <a href="{{ route('directeur.mon-espace') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                        Voir tout mon espace →
                    </a>
                </div>
            </div>

            {{-- Fiches récentes reçues --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Activité récente</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Fiches d'objectifs {{ $annee }}</h2>
                </div>

                @if ($fichesRecentes->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucune fiche pour {{ $annee }}</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($fichesRecentes as $fiche)
                            @php
                                $sc = match($fiche->statut) {
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',
                                    default      => 'bg-slate-100 text-slate-500',
                                };
                                $sl = match($fiche->statut) {
                                    'acceptee' => 'Acceptée', 'en_attente' => 'En attente',
                                    'refusee'  => 'Refusée',  default      => ucfirst((string) $fiche->statut),
                                };
                                $av    = (int) ($fiche->avancement_percentage ?? 0);
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
                                    <a href="{{ route('directeur.objectifs.show', $fiche) }}"
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
                        <a href="{{ route('directeur.mon-espace') }}?tab=objectifs" class="text-xs font-bold text-emerald-600 hover:underline">
                            Voir toutes les fiches →
                        </a>
                    </div>
                @endif
            </div>
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
                    <p class="text-xs text-amber-600">Ces fiches nécessitent votre acceptation ou refus.</p>
                </div>
                <a href="{{ route('directeur.mon-espace') }}?tab=objectifs"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">
                    Voir les fiches
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
