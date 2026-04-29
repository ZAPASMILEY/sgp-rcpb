@extends('layouts.dga')
@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DGA / Pilotage</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Tableau de bord</h1>
                    <p class="mt-1 text-sm text-slate-500">Vue consolidée — réseau RCPB & subordonnés.</p>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- KPI Réseau ─────────────────────────────────────────────────────── --}}
        <div>
            <p class="mb-2 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 px-1">Réseau RCPB</p>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
                @php
                $reseauKpis = [
                    ['label' => 'Délégations', 'value' => $reseauStats['delegations'], 'icon' => 'fas fa-map-marker-alt', 'color' => 'bg-violet-600',  'light' => 'bg-violet-50 border-violet-200'],
                    ['label' => 'Caisses',     'value' => $reseauStats['caisses'],     'icon' => 'fas fa-landmark',        'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-200'],
                    ['label' => 'Agences',     'value' => $reseauStats['agences'],     'icon' => 'fas fa-building',        'color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-200'],
                    ['label' => 'Guichets',    'value' => $reseauStats['guichets'],    'icon' => 'fas fa-cash-register',   'color' => 'bg-amber-600',   'light' => 'bg-amber-50 border-amber-200'],
                ];
                @endphp
                @foreach ($reseauKpis as $kpi)
                    <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                                <i class="{{ $kpi['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                    </div>
                @endforeach

                {{-- Note moyenne réseau --}}
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm
                    @if($noteReseau !== null)
                        @php $nr = (float) $noteReseau; @endphp
                        {{ $nr >= 8.5 ? 'bg-emerald-50 border-emerald-200' : ($nr >= 7 ? 'bg-blue-50 border-blue-200' : ($nr >= 5 ? 'bg-amber-50 border-amber-200' : 'bg-rose-50 border-rose-200')) }}
                    @else
                        bg-slate-50 border-slate-200
                    @endif
                ">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">Note réseau</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-600 text-white text-xs">
                            <i class="fas fa-chart-bar"></i>
                        </span>
                    </div>
                    @if($noteReseau !== null)
                        @php $nr = (float) $noteReseau; @endphp
                        <p class="mt-3 text-3xl font-black
                            {{ $nr >= 8.5 ? 'text-emerald-600' : ($nr >= 7 ? 'text-blue-600' : ($nr >= 5 ? 'text-amber-600' : 'text-rose-600')) }}">
                            {{ number_format($nr, 2) }}
                        </p>
                        <p class="text-[10px] text-slate-400">moy. validées /10</p>
                    @else
                        <p class="mt-3 text-xl font-bold text-slate-300">—</p>
                        <p class="text-[10px] text-slate-300">Aucune évaluation</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- KPI Subordonnés ─────────────────────────────────────────────────── --}}
        <div>
            <p class="mb-2 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400 px-1">Mes évaluations (subordonnés)</p>
            @php
            $subKpis = [
                ['label' => 'Total',      'value' => $subStats['total'],     'icon' => 'fas fa-clipboard-list', 'color' => 'bg-slate-700',  'light' => 'bg-slate-50 border-slate-200'],
                ['label' => 'Brouillons', 'value' => $subStats['brouillon'], 'icon' => 'fas fa-pencil',         'color' => 'bg-slate-400',  'light' => 'bg-slate-50 border-slate-200'],
                ['label' => 'Soumises',   'value' => $subStats['soumis'],    'icon' => 'fas fa-paper-plane',    'color' => 'bg-amber-500',  'light' => 'bg-amber-50 border-amber-200'],
                ['label' => 'Validées',   'value' => $subStats['valide'],    'icon' => 'fas fa-circle-check',   'color' => 'bg-emerald-600','light' => 'bg-emerald-50 border-emerald-200'],
            ];
            @endphp
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($subKpis as $kpi)
                    <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
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
        </div>

        {{-- Top / Bottom performers (parmi les subordonnés du DGA) ────────── --}}
        @if ($topEval || $bottomEval)
        <div class="grid gap-4 sm:grid-cols-2">
            @if ($topEval)
            <div class="admin-panel flex items-center gap-4 px-5 py-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-xl">
                    <i class="fas fa-trophy"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Meilleure note (subordonnés)</p>
                    <p class="truncate text-sm font-bold text-slate-900">{{ $topEval->identification?->nom_prenom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $topEval->identification?->emploi ?? $topEval->evaluable_role }}</p>
                </div>
                <span class="ml-auto text-2xl font-black text-emerald-600">{{ number_format((float)$topEval->note_finale, 2, ',', ' ') }}</span>
            </div>
            @endif
            @if ($bottomEval)
            <div class="admin-panel flex items-center gap-4 px-5 py-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-500 text-xl">
                    <i class="fas fa-arrow-trend-down"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Note la plus basse (subordonnés)</p>
                    <p class="truncate text-sm font-bold text-slate-900">{{ $bottomEval->identification?->nom_prenom ?? '—' }}</p>
                    <p class="text-xs text-slate-500">{{ $bottomEval->identification?->emploi ?? $bottomEval->evaluable_role }}</p>
                </div>
                <span class="ml-auto text-2xl font-black text-rose-500">{{ number_format((float)$bottomEval->note_finale, 2, ',', ' ') }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Filtres ────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('dga.dashboard') }}" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Nom, emploi…"
                        class="ent-input w-full">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Statut</label>
                    <select name="statut" class="ent-input">
                        <option value="">Tous</option>
                        <option value="brouillon" {{ $filters['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                        <option value="soumis"    {{ $filters['statut'] === 'soumis'    ? 'selected' : '' }}>Soumises</option>
                        <option value="valide"    {{ $filters['statut'] === 'valide'    ? 'selected' : '' }}>Validées</option>
                        <option value="refuse"    {{ $filters['statut'] === 'refuse'    ? 'selected' : '' }}>Refusées</option>
                    </select>
                </div>
                @if ($annees->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Année</label>
                    <select name="annee" class="ent-input">
                        <option value="">Toutes</option>
                        @foreach ($annees as $annee)
                            <option value="{{ $annee->id }}" {{ $filters['anneeId'] == $annee->id ? 'selected' : '' }}>
                                {{ $annee->annee }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="ent-btn ent-btn-primary">
                    <i class="fas fa-filter mr-2"></i>Filtrer
                </button>
                @if ($filters['search'] || $filters['statut'] || $filters['anneeId'])
                    <a href="{{ route('dga.dashboard') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Évaluations données par le DGA ────────────────────────────────── --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">
                    Évaluations de mes subordonnés
                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $evaluations->total() }}</span>
                </h2>
                <a href="{{ route('dga.subordonnes.index') }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                    <i class="fas fa-users mr-1"></i>Mes subordonnés
                </a>
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
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($evaluations as $eval)
                                @php
                                    $note    = (float) $eval->note_finale;
                                    $mention = $note >= 8.5 ? ['label' => 'Excellent',  'cls' => 'bg-emerald-100 text-emerald-700']
                                             : ($note >= 7  ? ['label' => 'Bien',        'cls' => 'bg-sky-100 text-sky-700']
                                             : ($note >= 5  ? ['label' => 'Passable',    'cls' => 'bg-amber-100 text-amber-700']
                                                            : ['label' => 'Insuffisant', 'cls' => 'bg-rose-100 text-rose-600']));
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
                                    $pct = $note > 0 ? min(100, $note * 10) : 0;
                                    $barColor = $note >= 8.5 ? 'bg-emerald-500' : ($note >= 7 ? 'bg-sky-500' : ($note >= 5 ? 'bg-amber-400' : 'bg-rose-500'));
                                    $nom = $eval->identification?->nom_prenom ?? '—';
                                    $emploi = $eval->identification?->emploi ?? $eval->evaluable_role ?? '—';
                                    $periode = $eval->date_debut?->format('m/Y').' – '.$eval->date_fin?->format('m/Y');
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $emploi }}</td>
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $periode }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @if($note > 0)
                                            <span class="text-base font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}</span>
                                            <div class="mt-1 h-1.5 w-20 rounded-full bg-slate-100 ml-auto">
                                                <div class="h-1.5 rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($note > 0)
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $mention['cls'] }}">
                                                {{ $mention['label'] }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statutCls }}">
                                            {{ $statutLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dga.sub-evaluations.show', $eval) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
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

    </div>
</div>
@endsection
