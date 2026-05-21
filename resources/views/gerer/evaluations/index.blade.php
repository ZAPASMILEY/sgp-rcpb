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

        {{-- Filtres --}}
        <form method="GET" action="{{ route('gerer.evaluations.index') }}"
              class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">

            <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Matricule, nom, prénom…"
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:border-sky-400 focus:ring-0">
                </div>
            </div>

            <div class="flex flex-col gap-1 min-w-[150px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Année</label>
                <select name="annee_id" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a->id }}" @selected($annee?->id == $a->id)>{{ $a->annee }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1 min-w-[140px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Délégation</label>
                <select name="delegation_id" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                    <option value="">Toutes</option>
                    @foreach($delegations as $d)
                        <option value="{{ $d->id }}" @selected(request('delegation_id') == $d->id)>{{ $d->region }} – {{ $d->ville }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1 min-w-[130px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Caisse</label>
                <select name="caisse_id" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                    <option value="">Toutes</option>
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>{{ $c->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col gap-1 min-w-[130px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Statut</label>
                <select name="statut" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                    <option value="">Tous</option>
                    <option value="brouillon"  @selected(request('statut') === 'brouillon')>Brouillon</option>
                    <option value="soumise"    @selected(request('statut') === 'soumise')>Soumise</option>
                    <option value="validee"    @selected(request('statut') === 'validee')>Validée</option>
                    <option value="rejetee"    @selected(request('statut') === 'rejetee')>Rejetée</option>
                </select>
            </div>

            <button type="submit" class="rounded-lg px-5 py-2 text-sm font-bold text-white transition"
                    style="background:#0284c7" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                <i class="fas fa-filter mr-1"></i> Filtrer
            </button>
            <a href="{{ route('gerer.evaluations.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Réinitialiser
            </a>
        </form>

        {{-- Tableau --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            @if($evaluations->isEmpty())
                <div class="px-8 py-16 text-center">
                    <i class="fas fa-clipboard text-slate-200 text-5xl mb-4 block"></i>
                    <p class="text-sm font-semibold text-slate-400">Aucune évaluation trouvée.</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Agent</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Structure</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Semestre</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Évaluateur</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Note</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Statut</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($evaluations as $eval)
                        @php
                            $structure = $eval->agent?->caisse?->nom
                                ?? $eval->agent?->direction?->nom
                                ?? $eval->agent?->delegationTechnique?->region.' – '.$eval->agent?->delegationTechnique?->ville
                                ?? '—';
                            $statutColors = [
                                'brouillon' => 'bg-slate-100 text-slate-500',
                                'soumise'   => 'bg-amber-50 text-amber-700',
                                'validee'   => 'bg-emerald-50 text-emerald-700',
                                'rejetee'   => 'bg-red-50 text-red-700',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $eval->agent?->prenom }} {{ $eval->agent?->nom }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $eval->agent?->matricule }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 max-w-[160px] truncate" title="{{ $structure }}">{{ $structure }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $eval->semestre?->label ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $eval->evaluateur?->prenom }} {{ $eval->evaluateur?->nom }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-slate-700">
                                @if($eval->note_finale !== null)
                                    {{ number_format($eval->note_finale, 2) }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $statutColors[$eval->statut] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($eval->statut) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $eval->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ $evaluations->total() }} évaluation{{ $evaluations->total() > 1 ? 's' : '' }}</span>
                {{ $evaluations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
