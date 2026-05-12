@extends('layouts.dga')
@section('title', 'Notes du Réseau | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Espace DGA</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Notes du Réseau</h1>
                <p class="mt-1 text-sm text-emerald-100/80">
                    Subordonnés directs · Services DGA · Délégations · Caisses · Agences
                </p>
            </div>
            @if($noteMoyenne !== null)
                <div class="shrink-0 rounded-2xl bg-white/15 px-6 py-4 text-center ring-1 ring-white/20">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-200">Note moy. réseau</p>
                    <p class="mt-0.5 text-4xl font-black text-white">{{ number_format($noteMoyenne, 2) }}</p>
                    <p class="text-[10px] text-emerald-100/60">/ 10</p>
                </div>
            @else
                <div class="shrink-0 rounded-2xl bg-white/10 px-6 py-4 text-center ring-1 ring-white/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-300">Note moy. réseau</p>
                    <p class="mt-0.5 text-3xl font-black text-white/30">—</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-screen-xl px-4 pt-6 lg:px-8 space-y-5">

        {{-- KPI row --}}
        <div class="flex gap-2">
            @php
                $kpis = [
                    ['label' => 'Total',      'value' => $stats['total'],     'bg' => 'bg-white',      'num' => 'text-slate-900',   'lbl' => 'text-slate-400'],
                    ['label' => 'Validées',   'value' => $stats['valide'],    'bg' => 'bg-emerald-50', 'num' => 'text-emerald-700', 'lbl' => 'text-emerald-500'],
                    ['label' => 'Soumises',   'value' => $stats['soumis'],    'bg' => 'bg-amber-50',   'num' => 'text-amber-700',   'lbl' => 'text-amber-500'],
                    ['label' => 'Brouillons', 'value' => $stats['brouillon'], 'bg' => 'bg-slate-50',   'num' => 'text-slate-600',   'lbl' => 'text-slate-400'],
                    ['label' => 'Refusées',   'value' => $stats['refuse'],    'bg' => 'bg-red-50',     'num' => 'text-red-600',     'lbl' => 'text-red-400'],
                ];
            @endphp
            @foreach($kpis as $k)
            <div class="flex-1 rounded-xl {{ $k['bg'] }} px-3 py-2.5 shadow-sm">
                <p class="text-[9px] font-black uppercase tracking-widest {{ $k['lbl'] }}">{{ $k['label'] }}</p>
                <p class="mt-0.5 text-xl font-black {{ $k['num'] }}">{{ $k['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('dga.notes-reseau.index') }}"
              class="rounded-2xl bg-white px-5 py-4 shadow-sm flex flex-wrap items-end gap-3">

            <div class="flex-1 min-w-52 space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Nom / Emploi</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher..."
                           class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Délégation</label>
                <select name="delegation_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none">
                    <option value="">Toutes les DT</option>
                    @foreach($filterDelegations as $dt)
                        <option value="{{ $dt->id }}" @selected($filters['delegId'] == $dt->id)>DT {{ $dt->region }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Caisse</label>
                <select name="caisse_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none">
                    <option value="">Toutes</option>
                    @foreach($filterCaisses as $c)
                        <option value="{{ $c->id }}" @selected($filters['caisseId'] == $c->id)>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Statut</label>
                <select name="statut" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none">
                    <option value="">Tous</option>
                    <option value="valide"    @selected($filters['statut']==='valide')>Validée</option>
                    <option value="soumis"    @selected($filters['statut']==='soumis')>Soumise</option>
                    <option value="brouillon" @selected($filters['statut']==='brouillon')>Brouillon</option>
                    <option value="refuse"    @selected($filters['statut']==='refuse')>Refusée</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Année</label>
                <select name="annee_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-emerald-400 focus:outline-none">
                    <option value="">Toutes</option>
                    @foreach($annees as $annee)
                        <option value="{{ $annee->id }}" @selected($filters['anneeId'] == $annee->id)>{{ $annee->annee }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
                <i class="fas fa-search mr-1.5 text-xs"></i>Filtrer
            </button>
            @if($filters['search'] || $filters['statut'] || $filters['delegId'] || $filters['caisseId'] || $filters['anneeId'])
                <a href="{{ route('dga.notes-reseau.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition">
                    <i class="fas fa-times mr-1 text-xs"></i>Effacer
                </a>
            @endif
        </form>

        {{-- Tableau des évaluations --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Évaluations</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    {{ $evaluations->total() }} résultat{{ $evaluations->total() > 1 ? 's' : '' }}
                </span>
            </div>

            @if($evaluations->isEmpty())
                <div class="px-5 py-16 text-center">
                    <i class="fas fa-chart-bar text-4xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucune évaluation trouvée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Agent évalué</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Poste / Structure</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Évaluateur</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Période</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Statut</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Note</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($evaluations as $eval)
                            @php
                                $ident = $eval->identification;
                                $nom   = trim((string)($ident?->nom_prenom ?? '')) ?: ($eval->evaluable?->name ?? '—');
                                $poste = $ident?->emploi ?? str_replace('_', ' ', $eval->evaluable?->role ?? '');
                                $dir   = $ident?->direction ?? '';
                                $note  = $eval->note_finale !== null ? (float)$eval->note_finale : null;
                                $badgeClass = match($eval->statut) {
                                    'valide'    => 'bg-emerald-100 text-emerald-700',
                                    'soumis'    => 'bg-amber-100 text-amber-700',
                                    'brouillon' => 'bg-slate-100 text-slate-500',
                                    'refuse'    => 'bg-red-100 text-red-600',
                                    default     => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 text-xs font-black">
                                            {{ strtoupper(substr($nom, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $nom }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-700">{{ $poste ?: '—' }}</p>
                                    @if($dir)<p class="text-xs text-slate-400">{{ $dir }}</p>@endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $eval->evaluateur?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                    @if($eval->date_debut)
                                        {{ \Carbon\Carbon::parse($eval->date_debut)->translatedFormat('M Y') }}
                                        @if($eval->date_fin && $eval->date_fin != $eval->date_debut)
                                            → {{ \Carbon\Carbon::parse($eval->date_fin)->translatedFormat('M Y') }}
                                        @endif
                                    @else —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $badgeClass }}">
                                        {{ match($eval->statut) {
                                            'valide'=>'Validée','soumis'=>'Soumise',
                                            'brouillon'=>'Brouillon','refuse'=>'Refusée',
                                            default=>$eval->statut
                                        } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($note !== null)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-black {{ $noteColor($note) }}">
                                            {{ number_format($note, 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('dga.notes-reseau.show', $eval) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 transition">
                                        <i class="fas fa-eye text-[10px]"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($evaluations->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $evaluations->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>
@endsection
