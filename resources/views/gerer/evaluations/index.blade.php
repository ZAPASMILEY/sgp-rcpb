@extends($layout)

@section('title', 'Évaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    <div class="relative overflow-hidden px-6 py-8 lg:px-10"
         style="background:linear-gradient(135deg,#0c4a6e 0%,#0369a1 50%,#0284c7 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow ring-1 ring-white/20">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-200">Suivi · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black text-white">Toutes les évaluations</h1>
                <p class="mt-0.5 text-sm text-sky-100/75">Vue consolidée des évaluations du réseau</p>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        {{-- ── Filtres ─────────────────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('gerer.evaluations.index') }}"
              class="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
            <div class="relative flex-1 min-w-44">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <i class="fas fa-search text-[10px]"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Matricule, nom, prénom…"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100">
            </div>
            <select name="annee_id"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100">
                <option value="">Toutes les années</option>
                @foreach($annees as $a)
                    <option value="{{ $a->id }}" @selected($annee?->id == $a->id)>{{ $a->annee }}</option>
                @endforeach
            </select>
            <select name="delegation_id"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100">
                <option value="">Toutes délégations</option>
                @foreach($delegations as $d)
                    <option value="{{ $d->id }}" @selected(request('delegation_id') == $d->id)>{{ $d->region }} – {{ $d->ville }}</option>
                @endforeach
            </select>
            <select name="caisse_id"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100">
                <option value="">Toutes caisses</option>
                @foreach($caisses as $c)
                    <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>{{ $c->nom }}</option>
                @endforeach
            </select>
            <select name="statut"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-2 focus:ring-sky-100">
                <option value="">Tous les statuts</option>
                <option value="brouillon"   @selected(request('statut') === 'brouillon')>Brouillon</option>
                <option value="soumis"      @selected(request('statut') === 'soumis')>Soumise</option>
                <option value="valide"      @selected(request('statut') === 'valide')>Validée</option>
                <option value="reclamation" @selected(request('statut') === 'reclamation')>Réclamation</option>
                <option value="refuse"      @selected(request('statut') === 'refuse')>Refusée</option>
                <option value="a_reviser"   @selected(request('statut') === 'a_reviser')>À réviser</option>
            </select>
            <button type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
                Filtrer
            </button>
            @if(request('search') || request('annee_id') || request('delegation_id') || request('caisse_id') || request('statut'))
            <a href="{{ route('gerer.evaluations.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500 transition hover:text-slate-800">
                <i class="fas fa-times text-[10px]"></i>
            </a>
            @endif
        </form>

        {{-- ── Tableau ──────────────────────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-200 bg-slate-50/80">
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Agent</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Structure</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                            <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($evaluations as $eval)
                        @php
                            $structure  = $eval->identification?->direction_service ?? '—';
                            $hasNote    = $eval->note_finale !== null;
                            $noteVal    = (float) ($eval->note_finale ?? 0);
                            $mention    = $noteVal >= 8.5 ? 'Excellent' : ($noteVal >= 7 ? 'Bien' : ($noteVal >= 5 ? 'Passable' : 'Insuffisant'));
                            $mentionTxtCls = match ($mention) {
                                'Excellent' => 'text-emerald-600',
                                'Bien'      => 'text-sky-600',
                                'Passable'  => 'text-amber-600',
                                default     => 'text-rose-600',
                            };
                            $notePct     = $hasNote ? max(0, min(100, ($noteVal / 10) * 100)) : 0;
                            $noteBarCls  = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                            $notePillCls = $notePct >= 85
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                : ($notePct >= 70 ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                                : ($notePct >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                                : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'));
                            $statusCls = match ($eval->statut) {
                                'valide'      => 'bg-emerald-100 text-emerald-700',
                                'soumis'      => 'bg-amber-100 text-amber-700',
                                'refuse'      => 'bg-rose-100 text-rose-700',
                                'reclamation' => 'bg-orange-100 text-orange-700',
                                'a_reviser'   => 'bg-purple-100 text-purple-700',
                                default       => 'bg-slate-100 text-slate-600',
                            };
                            $dotCls = match ($eval->statut) {
                                'valide'      => 'bg-emerald-500',
                                'soumis'      => 'bg-amber-400',
                                'refuse'      => 'bg-rose-500',
                                'reclamation' => 'bg-orange-500',
                                'a_reviser'   => 'bg-purple-500',
                                default       => 'bg-slate-400',
                            };
                            $statusLabel = match ($eval->statut) {
                                'valide'      => 'Validée',
                                'soumis'      => 'Soumise',
                                'refuse'      => 'Refusée',
                                'reclamation' => 'Réclamation',
                                'a_reviser'   => 'À réviser',
                                'brouillon'   => 'Brouillon',
                                default       => ucfirst((string) $eval->statut),
                            };
                            $sem = $eval->semestre?->label ?? '—';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Agent --}}
                            <td class="px-5 py-3.5">
                                <p class="font-black text-slate-800">{{ $eval->identification?->nom_prenom ?? '—' }}</p>
                                <p class="mt-0.5 text-[11px] font-mono text-slate-400">{{ $eval->identification?->matricule ?? '—' }}</p>
                            </td>
                            {{-- Structure --}}
                            <td class="px-5 py-3.5 max-w-[180px]">
                                <p class="truncate text-xs font-semibold text-slate-600" title="{{ $structure }}">{{ $structure }}</p>
                            </td>
                            {{-- Période --}}
                            <td class="px-5 py-3.5">
                                <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                    <i class="fas fa-calendar-alt text-[9px] text-slate-400"></i>
                                    {{ $sem }}
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400">{{ $eval->evaluateur?->name ?? ($eval->evaluateur?->prenom.' '.$eval->evaluateur?->nom) }}</p>
                            </td>
                            {{-- Note --}}
                            <td class="px-5 py-3.5">
                                @if($hasNote)
                                    <span class="inline-flex items-baseline gap-0.5 rounded-lg px-2.5 py-1 text-sm font-black {{ $notePillCls }}">
                                        {{ number_format($noteVal, 2, ',', ' ') }}<span class="text-[10px] font-bold opacity-60">/10</span>
                                    </span>
                                    <div class="mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $noteBarCls }}" style="width:{{ $notePct }}%"></div>
                                    </div>
                                    <p class="mt-0.5 text-[10px] font-bold {{ $mentionTxtCls }}">{{ $mention }}</p>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                            {{-- Statut --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            {{-- Date --}}
                            <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">{{ $eval->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                            <i class="fas fa-clipboard-list text-xl text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-black text-slate-700">Aucune évaluation trouvée</p>
                                        <p class="mt-1 text-xs text-slate-400">Ajustez vos critères de filtre.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $evaluations->count() }} résultat(s)</div>
        </div>
    </div>
</div>
@endsection
