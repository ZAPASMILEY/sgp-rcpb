{{--
    Partial : liste du personnel évalué avec filtres et stats.
    Variables attendues :
      $evaluations  — LengthAwarePaginator
      $stats        — array [total, excellent, bien, passable, insuffisant, moyenne]
      $filters      — array [search, appreciation, statut, sort]
      $filterRoute  — nom de la route (string)
      $filterParams — paramètres supplémentaires pour la route (array)
--}}

{{-- KPIs ──────────────────────────────────────────────────────────────────── --}}
@php
$kpis = [
    ['label'=>'Évaluations', 'value'=>$stats['total'],                                       'icon'=>'fas fa-clipboard-list','bg'=>'bg-slate-700',   'light'=>'bg-white border-slate-200'],
    ['label'=>'Moyenne /10', 'value'=>number_format($stats['moyenne'],2,',',' '),            'icon'=>'fas fa-chart-bar',     'bg'=>'bg-emerald-600', 'light'=>'bg-emerald-50 border-emerald-200'],
    ['label'=>'Excellent',   'value'=>$stats['excellent'],   'sub'=>'≥ 8,5',                 'icon'=>'fas fa-star',          'bg'=>'bg-emerald-500', 'light'=>'bg-emerald-50 border-emerald-100'],
    ['label'=>'Bien',        'value'=>$stats['bien'],        'sub'=>'7 – 8,5',               'icon'=>'fas fa-thumbs-up',     'bg'=>'bg-sky-500',     'light'=>'bg-sky-50 border-sky-100'],
    ['label'=>'Passable',    'value'=>$stats['passable'],    'sub'=>'5 – 7',                 'icon'=>'fas fa-minus-circle',  'bg'=>'bg-amber-400',   'light'=>'bg-amber-50 border-amber-100'],
    ['label'=>'Insuffisant', 'value'=>$stats['insuffisant'], 'sub'=>'< 5',                   'icon'=>'fas fa-circle-xmark',  'bg'=>'bg-rose-500',    'light'=>'bg-rose-50 border-rose-100'],
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
                <span class="text-2xl font-black text-slate-900">{{ $kpi['value'] }}</span>
                @isset($kpi['sub'])
                    <span class="text-xs text-slate-400">{{ $kpi['sub'] }}</span>
                @endisset
            </div>
        </div>
    @endforeach
</div>

{{-- Filtres ─────────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route($filterRoute, $filterParams) }}"
      class="admin-panel px-5 py-5">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche (nom, matricule)</label>
            <input type="text" name="search" value="{{ $filters['search'] }}"
                   placeholder="Nom, matricule, emploi…" class="ent-input w-full">
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Appréciation</label>
            <select name="appreciation" class="ent-input w-full">
                <option value="">Toutes</option>
                <option value="excellent"   @selected($filters['appreciation']==='excellent')>Excellent (≥ 8,5)</option>
                <option value="bien"        @selected($filters['appreciation']==='bien')>Bien (7 – 8,5)</option>
                <option value="passable"    @selected($filters['appreciation']==='passable')>Passable (5 – 7)</option>
                <option value="insuffisant" @selected($filters['appreciation']==='insuffisant')>Insuffisant (< 5)</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Statut</label>
            <select name="statut" class="ent-input w-full">
                <option value="">Tous</option>
                <option value="soumis" @selected($filters['statut']==='soumis')>Soumise</option>
                <option value="valide" @selected($filters['statut']==='valide')>Validée</option>
                <option value="refuse" @selected($filters['statut']==='refuse')>Refusée</option>
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Trier par</label>
            <select name="sort" class="ent-input w-full">
                <option value="note_desc" @selected($filters['sort']==='note_desc')>Note ↓ (meilleure)</option>
                <option value="note_asc"  @selected($filters['sort']==='note_asc')>Note ↑ (plus basse)</option>
                <option value="date_desc" @selected($filters['sort']==='date_desc')>Date ↓ (récente)</option>
                <option value="date_asc"  @selected($filters['sort']==='date_asc')>Date ↑ (ancienne)</option>
            </select>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="submit" class="ent-btn ent-btn-primary">
            <i class="fas fa-filter mr-2"></i>Filtrer
        </button>
        @if ($filters['search'] || $filters['appreciation'] || $filters['statut'])
            <a href="{{ route($filterRoute, $filterParams) }}" class="ent-btn ent-btn-soft">
                <i class="fas fa-xmark mr-1"></i>Réinitialiser
            </a>
        @endif
        <span class="ml-auto text-xs text-slate-400">{{ $evaluations->total() }} résultat(s)</span>
    </div>
</form>

{{-- Tableau ─────────────────────────────────────────────────────────────────── --}}
<section class="admin-panel overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
        <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Personnel évalué</h2>
        @if ($evaluations->hasPages())
            <span class="text-xs text-slate-400">Page {{ $evaluations->currentPage() }} / {{ $evaluations->lastPage() }}</span>
        @endif
    </div>

    @if ($evaluations->isEmpty())
        <div class="px-6 py-16 text-center">
            <i class="fas fa-users-slash text-4xl text-slate-200"></i>
            <p class="mt-4 text-sm font-semibold text-slate-400">Aucune évaluation pour ces critères.</p>
            @if ($filters['search'] || $filters['appreciation'] || $filters['statut'])
                <a href="{{ route($filterRoute, $filterParams) }}"
                   class="mt-3 inline-block text-sm font-semibold text-emerald-600 hover:underline">
                    Réinitialiser les filtres
                </a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nom complet</th>
                        <th class="px-4 py-3">Emploi / Poste</th>
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

                            $app = $note >= 8.5
                                ? ['label'=>'Excellent',   'cls'=>'bg-emerald-100 text-emerald-700', 'dot'=>'bg-emerald-500']
                                : ($note >= 7
                                    ? ['label'=>'Bien',        'cls'=>'bg-sky-100 text-sky-700',         'dot'=>'bg-sky-500']
                                    : ($note >= 5
                                        ? ['label'=>'Passable',    'cls'=>'bg-amber-100 text-amber-700',     'dot'=>'bg-amber-400']
                                        : ['label'=>'Insuffisant', 'cls'=>'bg-rose-100 text-rose-600',       'dot'=>'bg-rose-500']));

                            $statCls = match($eval->statut) {
                                'valide' => 'bg-emerald-100 text-emerald-700',
                                'soumis' => 'bg-amber-100 text-amber-700',
                                'refuse' => 'bg-rose-100 text-rose-600',
                                default  => 'bg-slate-100 text-slate-600',
                            };
                            $statLabel = match($eval->statut) {
                                'valide' => 'Validée',
                                'soumis' => 'Soumise',
                                'refuse' => 'Refusée',
                                default  => ucfirst($eval->statut),
                            };

                            $pct        = $note > 0 ? min(100, $note * 10) : 0;
                            $semLabel   = $ident?->semestre ? 'S'.$ident->semestre.' ' : '';
                            $anneeLabel = $ident?->date_evaluation?->format('Y') ?? $eval->date_debut?->format('Y') ?? '—';
                            $periode    = $semLabel.$anneeLabel;
                            $rank       = ($evaluations->currentPage() - 1) * $evaluations->perPage() + $i + 1;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-3 text-xs font-black text-slate-300">{{ $rank }}</td>
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
                            <td class="px-4 py-3 text-slate-700">{{ $ident?->emploi ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $periode }}</td>
                            <td class="px-4 py-3 text-right">
                                <span class="text-lg font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}</span>
                                <div class="mt-1 h-1.5 w-16 rounded-full bg-slate-100 ml-auto overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $app['dot'] }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-black {{ $app['cls'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $app['dot'] }}"></span>
                                    {{ $app['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $statCls }}">{{ $statLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $eval->evaluateur?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('dg.personnel') }}?search={{ urlencode($ident?->nom_prenom ?? '') }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition hover:bg-emerald-100 hover:text-emerald-600"
                                   title="Voir dans Personnel">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
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
