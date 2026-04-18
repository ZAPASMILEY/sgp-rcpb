@extends('layouts.dg')
@section('title', 'Personnel du réseau | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Réseau RCPB</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Personnel évalué</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $evaluations->total() }} fiche(s) — réseau complet RCPB</p>
                </div>
                <a href="{{ route('dg.personnel.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-2xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                    <i class="fas fa-file-pdf"></i>
                    Télécharger PDF
                </a>
            </div>
        </header>

        {{-- KPIs ──────────────────────────────────────────────────────────── --}}
        @php
        $kpis = [
            ['label'=>'Total évaluations', 'value'=>$stats['total'],       'sub'=>'',                                    'icon'=>'fas fa-clipboard-list','bg'=>'bg-slate-700',  'light'=>'bg-white border-slate-200'],
            ['label'=>'Note moyenne',      'value'=>number_format($stats['moyenne'],2,',',' '), 'sub'=>'/10',             'icon'=>'fas fa-chart-bar',     'bg'=>'bg-emerald-600','light'=>'bg-emerald-50 border-emerald-200'],
            ['label'=>'Excellent',         'value'=>$stats['excellent'],   'sub'=>'≥ 8,5',                               'icon'=>'fas fa-star',          'bg'=>'bg-emerald-500','light'=>'bg-emerald-50 border-emerald-100'],
            ['label'=>'Bien',              'value'=>$stats['bien'],        'sub'=>'7 – 8,5',                             'icon'=>'fas fa-thumbs-up',     'bg'=>'bg-sky-500',    'light'=>'bg-sky-50 border-sky-100'],
            ['label'=>'Passable',          'value'=>$stats['passable'],    'sub'=>'5 – 7',                               'icon'=>'fas fa-minus-circle',  'bg'=>'bg-amber-400',  'light'=>'bg-amber-50 border-amber-100'],
            ['label'=>'Insuffisant',       'value'=>$stats['insuffisant'], 'sub'=>'< 5',                                 'icon'=>'fas fa-circle-xmark',  'bg'=>'bg-rose-500',   'light'=>'bg-rose-50 border-rose-100'],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['bg'] }} text-white text-xs">
                            <i class="{{ $kpi['icon'] }}"></i>
                        </span>
                    </div>
                    <div class="mt-3 flex items-baseline gap-1">
                        <span class="text-3xl font-black text-slate-900">{{ $kpi['value'] }}</span>
                        @if ($kpi['sub'])
                            <span class="text-xs text-slate-400">{{ $kpi['sub'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filtres ─────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('dg.personnel') }}" class="admin-panel px-5 py-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Recherche --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche (nom, matricule)</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                        placeholder="Nom complet, matricule…" class="ent-input w-full">
                </div>

                {{-- Emploi --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Emploi / Poste</label>
                    <select name="emploi" class="ent-input w-full">
                        <option value="">Tous les emplois</option>
                        @foreach ($emplois as $e)
                            <option value="{{ $e }}" {{ $filters['emploi'] === $e ? 'selected' : '' }}>{{ $e }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Structure --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Direction / Structure</label>
                    <select name="structure" class="ent-input w-full">
                        <option value="">Toutes les structures</option>
                        @foreach ($structures as $s)
                            <option value="{{ $s }}" {{ $filters['structure'] === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Année --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Année</label>
                    <select name="annee" class="ent-input w-full">
                        <option value="">Toutes les années</option>
                        @foreach ($annees as $annee)
                            <option value="{{ $annee->id }}" {{ $filters['anneeId'] == $annee->id ? 'selected' : '' }}>{{ $annee->annee }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Semestre --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Semestre</label>
                    <select name="semestre" class="ent-input w-full">
                        <option value="">Tous</option>
                        <option value="1" {{ $filters['semestre'] === '1' ? 'selected' : '' }}>Semestre 1</option>
                        <option value="2" {{ $filters['semestre'] === '2' ? 'selected' : '' }}>Semestre 2</option>
                    </select>
                </div>

                {{-- Appréciation --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Appréciation</label>
                    <select name="appreciation" class="ent-input w-full">
                        <option value="">Toutes</option>
                        <option value="excellent"   {{ $filters['appreciation'] === 'excellent'   ? 'selected' : '' }}>⭐ Excellent (≥ 8,5)</option>
                        <option value="bien"        {{ $filters['appreciation'] === 'bien'        ? 'selected' : '' }}>👍 Bien (7 – 8,5)</option>
                        <option value="passable"    {{ $filters['appreciation'] === 'passable'    ? 'selected' : '' }}>➖ Passable (5 – 7)</option>
                        <option value="insuffisant" {{ $filters['appreciation'] === 'insuffisant' ? 'selected' : '' }}>❌ Insuffisant (&lt; 5)</option>
                    </select>
                </div>

                {{-- Statut --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Statut</label>
                    <select name="statut" class="ent-input w-full">
                        <option value="">Tous</option>
                        <option value="soumis" {{ $filters['statut'] === 'soumis' ? 'selected' : '' }}>Soumise</option>
                        <option value="valide" {{ $filters['statut'] === 'valide' ? 'selected' : '' }}>Validée</option>
                        <option value="refuse" {{ $filters['statut'] === 'refuse' ? 'selected' : '' }}>Refusée</option>
                    </select>
                </div>

                {{-- Tri --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Trier par</label>
                    <select name="sort" class="ent-input w-full">
                        <option value="note_desc" {{ $filters['sort'] === 'note_desc' ? 'selected' : '' }}>Note ↓ (meilleure en premier)</option>
                        <option value="note_asc"  {{ $filters['sort'] === 'note_asc'  ? 'selected' : '' }}>Note ↑ (plus basse en premier)</option>
                        <option value="date_desc" {{ $filters['sort'] === 'date_desc' ? 'selected' : '' }}>Date ↓ (plus récente)</option>
                        <option value="date_asc"  {{ $filters['sort'] === 'date_asc'  ? 'selected' : '' }}>Date ↑ (plus ancienne)</option>
                    </select>
                </div>

            </div>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="ent-btn ent-btn-primary">
                    <i class="fas fa-filter mr-2"></i>Appliquer les filtres
                </button>
                @if (array_filter(array_diff_key($filters, ['sort' => ''])))
                    <a href="{{ route('dg.personnel') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-xmark mr-1"></i>Réinitialiser
                    </a>
                @endif
                <span class="ml-auto text-xs text-slate-400">{{ $evaluations->total() }} résultat(s)</span>
            </div>
        </form>

        {{-- Tableau ──────────────────────────────────────────────────────────── --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">
                    Liste du personnel évalué
                </h2>
                <span class="text-xs text-slate-400">Page {{ $evaluations->currentPage() }} / {{ $evaluations->lastPage() }}</span>
            </div>

            @if ($evaluations->isEmpty())
                <div class="px-6 py-20 text-center">
                    <i class="fas fa-users-slash text-5xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucun résultat pour ces critères.</p>
                    <a href="{{ route('dg.personnel') }}" class="mt-3 inline-block text-sm font-semibold text-emerald-600 hover:underline">Réinitialiser les filtres</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Nom complet</th>
                                <th class="px-4 py-3">Emploi / Poste</th>
                                <th class="px-4 py-3">Structure</th>
                                <th class="px-4 py-3">Période</th>
                                <th class="px-4 py-3 text-right">Note /10</th>
                                <th class="px-4 py-3">Appréciation</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3">Évaluateur</th>
                                <th class="px-4 py-3 text-center">Fiche</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($evaluations as $i => $eval)
                                @php
                                    $ident = $eval->identification;
                                    $note  = (float) $eval->note_finale;

                                    $appreciation = $note >= 8.5
                                        ? ['label'=>'Excellent',   'cls'=>'bg-emerald-100 text-emerald-700', 'dot'=>'bg-emerald-500']
                                        : ($note >= 7
                                            ? ['label'=>'Bien',        'cls'=>'bg-sky-100 text-sky-700',         'dot'=>'bg-sky-500']
                                            : ($note >= 5
                                                ? ['label'=>'Passable',    'cls'=>'bg-amber-100 text-amber-700',     'dot'=>'bg-amber-400']
                                                : ['label'=>'Insuffisant', 'cls'=>'bg-rose-100 text-rose-600',       'dot'=>'bg-rose-500']));

                                    $statutCls = match($eval->statut) {
                                        'valide' => 'bg-emerald-100 text-emerald-700',
                                        'soumis' => 'bg-amber-100 text-amber-700',
                                        'refuse' => 'bg-rose-100 text-rose-600',
                                        default  => 'bg-slate-100 text-slate-600',
                                    };
                                    $statutLabel = match($eval->statut) {
                                        'valide' => 'Validée',
                                        'soumis' => 'Soumise',
                                        'refuse' => 'Refusée',
                                        default  => ucfirst($eval->statut),
                                    };

                                    $pct = $note > 0 ? min(100, $note * 10) : 0;

                                    $semLabel = $ident?->semestre ? 'S'.$ident->semestre.' ' : '';
                                    $anneeLabel = $ident?->date_evaluation?->format('Y')
                                        ?? $eval->date_debut?->format('Y') ?? '—';
                                    $periode = $semLabel.$anneeLabel;

                                    $evalRoute = match(true) {
                                        strtolower($eval->evaluable_role ?? '') === 'dg'
                                            => route('dg.evaluations.show', $eval),
                                        in_array($eval->evaluable_role, ['DGA','Assistante_Dg','Conseillers_Dg'], true)
                                            => route('dg.sub-evaluations.show', $eval),
                                        default => null,
                                    };

                                    $rank = ($evaluations->currentPage() - 1) * $evaluations->perPage() + $i + 1;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    {{-- Rang --}}
                                    <td class="px-4 py-3 text-xs font-black text-slate-300">{{ $rank }}</td>

                                    {{-- Nom --}}
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-black text-slate-500">
                                                {{ strtoupper(substr($ident?->nom_prenom ?? '?', 0, 1)) }}
                                            </span>
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $ident?->nom_prenom ?? '—' }}</p>
                                                @if ($ident?->matricule)
                                                    <p class="text-[11px] text-slate-400">Mat. {{ $ident->matricule }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Emploi --}}
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800">{{ $ident?->emploi ?? $eval->evaluable_role ?? '—' }}</p>
                                    </td>

                                    {{-- Structure --}}
                                    <td class="px-4 py-3">
                                        <p class="text-slate-700">{{ $ident?->direction ?? '—' }}</p>
                                        @if ($ident?->direction_service && $ident?->direction_service !== $ident?->direction)
                                            <p class="text-xs text-slate-400">{{ $ident->direction_service }}</p>
                                        @endif
                                    </td>

                                    {{-- Période --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $periode }}</td>

                                    {{-- Note --}}
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-lg font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}</span>
                                        <div class="mt-1 h-1.5 w-20 rounded-full bg-slate-100 ml-auto overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $appreciation['dot'] }} transition-all" style="width: {{ $pct }}%"></div>
                                        </div>
                                    </td>

                                    {{-- Appréciation --}}
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-black {{ $appreciation['cls'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $appreciation['dot'] }}"></span>
                                            {{ $appreciation['label'] }}
                                        </span>
                                    </td>

                                    {{-- Statut --}}
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statutCls }}">{{ $statutLabel }}</span>
                                    </td>

                                    {{-- Évaluateur --}}
                                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $eval->evaluateur?->name ?? '—' }}</td>

                                    {{-- Action --}}
                                    <td class="px-4 py-3 text-center">
                                        @if ($evalRoute)
                                            <a href="{{ $evalRoute }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                                <i class="fas fa-eye mr-1"></i>Voir
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
                    <div class="border-t border-slate-100 px-6 py-4 flex items-center justify-between gap-4">
                        <p class="text-xs text-slate-400">
                            {{ $evaluations->firstItem() }}–{{ $evaluations->lastItem() }} sur {{ $evaluations->total() }} résultats
                        </p>
                        {{ $evaluations->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </section>

    </div>
</div>
@endsection
