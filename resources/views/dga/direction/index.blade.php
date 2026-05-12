@extends('layouts.dga')
@section('title', 'Ma Direction | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-purple-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-violet-200">Espace DGA</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-white">{{ $direction->nom }}</h1>
                @if($direction->secretaire)
                    <p class="mt-1 text-sm text-violet-100/80">
                        <i class="fas fa-user-tie mr-1.5 text-violet-300"></i>
                        Secrétaire : <span class="font-semibold">{{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}</span>
                    </p>
                @endif
            </div>
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white ring-1 ring-white/20">
                <i class="fas fa-sitemap"></i>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-screen-xl px-4 pt-6 lg:px-8 space-y-5">

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @php
                $kpis = [
                    ['label'=>'Services',    'value'=>count($services), 'icon'=>'fas fa-layer-group',    'bg'=>'bg-violet-50',  'text'=>'text-violet-700',  'num'=>'text-violet-900'],
                    ['label'=>'Agents',      'value'=>$totalAgents,     'icon'=>'fas fa-users',           'bg'=>'bg-blue-50',    'text'=>'text-blue-600',    'num'=>'text-blue-900'],
                    ['label'=>'Évaluations', 'value'=>$totalEvals,      'icon'=>'fas fa-clipboard-check', 'bg'=>'bg-emerald-50', 'text'=>'text-emerald-600', 'num'=>'text-emerald-900'],
                    ['label'=>'Objectifs',   'value'=>$totalObjs,       'icon'=>'fas fa-bullseye',        'bg'=>'bg-amber-50',   'text'=>'text-amber-600',   'num'=>'text-amber-900'],
                ];
            @endphp
            @foreach($kpis as $kpi)
            <div class="flex items-center gap-4 rounded-2xl bg-white px-5 py-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $kpi['bg'] }} {{ $kpi['text'] }}">
                    <i class="{{ $kpi['icon'] }}"></i>
                </div>
                <div>
                    <p class="text-xl font-black {{ $kpi['num'] }}">{{ $kpi['value'] }}</p>
                    <p class="text-xs font-semibold text-slate-400">{{ $kpi['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-slate-200 bg-white rounded-t-2xl shadow-sm overflow-hidden">
            {{-- Services --}}
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'services', 'search' => '', 'statut' => '']) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-bold border-b-2 transition-colors
                      @if($tab === 'services') border-violet-600 text-violet-700 bg-violet-50 @else border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 @endif">
                <i class="fas fa-layer-group text-xs"></i>
                Services
                <span class="rounded-full px-2 py-0.5 text-[10px] font-black
                             @if($tab === 'services') bg-violet-600 text-white @else bg-slate-100 text-slate-500 @endif">
                    {{ count($services) }}
                </span>
            </a>
            {{-- Évaluations --}}
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'evaluations', 'search' => '', 'statut' => '']) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-bold border-b-2 transition-colors
                      @if($tab === 'evaluations') border-violet-600 text-violet-700 bg-violet-50 @else border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 @endif">
                <i class="fas fa-clipboard-check text-xs"></i>
                Évaluations
                <span class="rounded-full px-2 py-0.5 text-[10px] font-black
                             @if($tab === 'evaluations') bg-violet-600 text-white @else bg-slate-100 text-slate-500 @endif">
                    {{ $totalEvals }}
                </span>
            </a>
            {{-- Objectifs --}}
            <a href="{{ request()->fullUrlWithQuery(['tab' => 'objectifs', 'search' => '', 'statut' => '']) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-bold border-b-2 transition-colors
                      @if($tab === 'objectifs') border-violet-600 text-violet-700 bg-violet-50 @else border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 @endif">
                <i class="fas fa-bullseye text-xs"></i>
                Objectifs
                <span class="rounded-full px-2 py-0.5 text-[10px] font-black
                             @if($tab === 'objectifs') bg-violet-600 text-white @else bg-slate-100 text-slate-500 @endif">
                    {{ $totalObjs }}
                </span>
            </a>
        </div>

        {{-- ══════════════ ONGLET SERVICES ══════════════ --}}
        @if($tab === 'services')

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($services as $s)
            @php
                $service  = $s['service'];
                $chef     = $s['chef'];
                $chefUser = $s['chefUser'];
                $note     = $s['noteAvg'];
                $noteClass = $note === null ? 'bg-slate-100 text-slate-400'
                    : ($note >= 8.5 ? 'bg-emerald-100 text-emerald-700'
                    : ($note >= 7   ? 'bg-blue-100 text-blue-700'
                    : ($note >= 5   ? 'bg-amber-100 text-amber-700'
                    :                 'bg-red-100 text-red-600')));
            @endphp
            <div class="flex flex-col rounded-2xl bg-white shadow-sm overflow-hidden">
                {{-- Header carte --}}
                <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white font-black text-base shadow">
                        {{ strtoupper(substr($service->nom, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-slate-900 truncate text-sm">{{ $service->nom }}</p>
                        @if($chef)
                            <p class="text-xs text-slate-500 truncate">
                                <i class="fas fa-user mr-1 text-slate-400"></i>{{ $chef->prenom }} {{ $chef->nom }}
                            </p>
                        @else
                            <p class="text-xs italic text-slate-400">Chef non affecté</p>
                        @endif
                    </div>
                    @if($note !== null)
                        <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-sm font-black {{ $noteClass }}">
                            {{ number_format($note, 2) }}
                        </span>
                    @endif
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100 text-center">
                    <div class="py-3">
                        <p class="text-base font-black text-slate-900">{{ $s['nbAgents'] }}</p>
                        <p class="text-[10px] font-semibold text-slate-400">Agent{{ $s['nbAgents'] > 1 ? 's' : '' }}</p>
                    </div>
                    <div class="py-3">
                        <p class="text-base font-black text-slate-900">{{ $s['nbEvals'] }}</p>
                        <p class="text-[10px] font-semibold text-slate-400">Éval.</p>
                    </div>
                    <div class="py-3">
                        <p class="text-base font-black text-slate-900">{{ $s['nbObjectifs'] }}</p>
                        <p class="text-[10px] font-semibold text-slate-400">Obj.</p>
                    </div>
                </div>

                {{-- Agents du service --}}
                @if($service->agents->isNotEmpty())
                <div class="px-4 py-3 flex flex-wrap gap-1.5">
                    @foreach($service->agents->where('id','!=',$chef?->id)->take(4) as $agent)
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600">
                            {{ $agent->prenom }} {{ $agent->nom }}
                        </span>
                    @endforeach
                    @if($service->agents->where('id','!=',$chef?->id)->count() > 4)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                            +{{ $service->agents->where('id','!=',$chef?->id)->count() - 4 }} autres
                        </span>
                    @endif
                </div>
                @endif

                {{-- Actions --}}
                @if($chefUser)
                <div class="mt-auto flex gap-2 border-t border-slate-100 px-4 py-3">
                    <a href="{{ route('dga.sub-evaluations.create', ['subordonne_id' => $chefUser->id]) }}"
                       class="flex-1 rounded-xl bg-violet-600 py-2 text-center text-xs font-bold text-white hover:bg-violet-700 transition">
                        <i class="fas fa-pen-to-square mr-1"></i>Évaluer
                    </a>
                    <a href="{{ route('dga.sub-objectifs.create', ['user' => $chefUser->id]) }}"
                       class="flex-1 rounded-xl border border-slate-200 py-2 text-center text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                        <i class="fas fa-bullseye mr-1"></i>Objectifs
                    </a>
                </div>
                @else
                <div class="mt-auto border-t border-slate-100 px-4 py-3">
                    <p class="text-center text-xs italic text-slate-400">Compte non activé</p>
                </div>
                @endif
            </div>
            @empty
                <div class="sm:col-span-2 xl:col-span-3 rounded-2xl bg-white px-5 py-16 text-center shadow-sm">
                    <i class="fas fa-layer-group text-4xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucun service dans cette direction.</p>
                </div>
            @endforelse
        </div>

        {{-- Collaborateurs directs --}}
        @if($agentsDirects->isNotEmpty())
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <i class="fas fa-user-tie text-sm"></i>
                </span>
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Collaborateurs directs</h2>
                <span class="ml-auto rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                    {{ $agentsDirects->count() }}
                </span>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($agentsDirects as $agent)
                @php $agentUser = \App\Models\User::where('agent_id', $agent->id)->first(); @endphp
                <div class="flex items-center justify-between px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 font-black text-sm">
                            {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">{{ $agent->prenom }} {{ $agent->nom }}</p>
                            <p class="text-xs text-slate-400">{{ $agent->fonction }}</p>
                        </div>
                    </div>
                    @if($agentUser)
                        <a href="{{ route('dga.sub-evaluations.create', ['subordonne_id' => $agentUser->id]) }}"
                           class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500 hover:bg-slate-50 transition">
                            <i class="fas fa-pen-to-square mr-1"></i>Évaluer
                        </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ══════════════ ONGLET ÉVALUATIONS ══════════════ --}}
        @elseif($tab === 'evaluations')

        {{-- Filtres --}}
        <form method="GET" action="{{ request()->url() }}" class="flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <input type="hidden" name="tab" value="evaluations">
            <div class="flex-1 min-w-48 space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, emploi..."
                           class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Statut</label>
                <select name="statut" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                    <option value="">Tous</option>
                    <option value="valide"    @selected($filters['statut']==='valide')>Validée</option>
                    <option value="soumis"    @selected($filters['statut']==='soumis')>Soumise</option>
                    <option value="brouillon" @selected($filters['statut']==='brouillon')>Brouillon</option>
                    <option value="refuse"    @selected($filters['statut']==='refuse')>Refusée</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition">
                <i class="fas fa-search mr-1.5 text-xs"></i>Filtrer
            </button>
            @if($filters['search'] || $filters['statut'])
                <a href="{{ request()->fullUrlWithQuery(['tab'=>'evaluations','search'=>'','statut'=>'']) }}"
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition">
                    <i class="fas fa-times mr-1 text-xs"></i>Effacer
                </a>
            @endif
        </form>

        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Toutes les évaluations</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    {{ $evaluations?->total() ?? 0 }}
                </span>
            </div>

            @if(!$evaluations || $evaluations->isEmpty())
                <div class="px-5 py-16 text-center">
                    <i class="fas fa-clipboard-check text-4xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucune évaluation.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Chef de service</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Service</th>
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
                                $svcNom = $eval->evaluable?->agent?->ledService?->nom ?? ($ident?->direction_service ?? '—');
                                $note  = $eval->note_finale !== null ? (float)$eval->note_finale : null;
                                $badgeClass = match($eval->statut) {
                                    'valide'    => 'bg-emerald-100 text-emerald-700',
                                    'soumis'    => 'bg-amber-100 text-amber-700',
                                    'brouillon' => 'bg-slate-100 text-slate-500',
                                    'refuse'    => 'bg-red-100 text-red-600',
                                    default     => 'bg-slate-100 text-slate-500',
                                };
                                $noteClass = $note === null ? '' : ($note >= 8.5 ? 'bg-emerald-100 text-emerald-700' : ($note >= 7 ? 'bg-blue-100 text-blue-700' : ($note >= 5 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-600')));
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 text-xs font-black">
                                            {{ strtoupper(substr($nom, 0, 1)) }}
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $nom }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $svcNom }}</td>
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
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-black {{ $noteClass }}">
                                            {{ number_format($note, 2) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('dga.sub-evaluations.show', $eval) }}"
                                       class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-violet-50 hover:border-violet-200 hover:text-violet-700 transition">
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

        {{-- ══════════════ ONGLET OBJECTIFS ══════════════ --}}
        @elseif($tab === 'objectifs')

        {{-- Filtres --}}
        <form method="GET" action="{{ request()->url() }}" class="flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm">
            <input type="hidden" name="tab" value="objectifs">
            <div class="flex-1 min-w-48 space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Titre</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Titre de la fiche..."
                           class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Statut</label>
                <select name="statut" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 py-2.5 px-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                    <option value="">Tous</option>
                    <option value="acceptee"   @selected($filters['statut']==='acceptee')>Acceptée</option>
                    <option value="en_attente" @selected($filters['statut']==='en_attente')>En attente</option>
                    <option value="refusee"    @selected($filters['statut']==='refusee')>Refusée</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-violet-700 transition">
                <i class="fas fa-search mr-1.5 text-xs"></i>Filtrer
            </button>
            @if($filters['search'] || $filters['statut'])
                <a href="{{ request()->fullUrlWithQuery(['tab'=>'objectifs','search'=>'','statut'=>'']) }}"
                   class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500 hover:bg-slate-50 transition">
                    <i class="fas fa-times mr-1 text-xs"></i>Effacer
                </a>
            @endif
        </form>

        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Fiches d'objectifs</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                    {{ $objectifs?->total() ?? 0 }}
                </span>
            </div>

            @if(!$objectifs || $objectifs->isEmpty())
                <div class="px-5 py-16 text-center">
                    <i class="fas fa-bullseye text-4xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucune fiche d'objectifs.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-left">
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Titre</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Assigné à</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Statut</th>
                                <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Avancement</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($objectifs as $fiche)
                            @php
                                $statutClass = match($fiche->statut ?? 'en_attente') {
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                    'refusee'    => 'bg-red-100 text-red-600',
                                    default      => 'bg-amber-100 text-amber-700',
                                };
                                $statutLabel = match($fiche->statut ?? 'en_attente') {
                                    'acceptee' => 'Acceptée',
                                    'refusee'  => 'Refusée',
                                    default    => 'En attente',
                                };
                                $pct = (int)($fiche->avancement_percentage ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $fiche->titre }}</p>
                                    <p class="text-xs text-slate-400">{{ $fiche->objectifs_count }} objectif{{ $fiche->objectifs_count > 1 ? 's' : '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $fiche->assignable?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                    {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->translatedFormat('d M Y') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statutClass }}">
                                        {{ $statutLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100">
                                            <div class="h-1.5 rounded-full {{ $pct >= 75 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-400' : 'bg-red-400') }}"
                                                 style="width:{{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500 w-8 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('dga.sub-objectifs.show', $fiche) }}"
                                       class="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-violet-50 hover:border-violet-200 hover:text-violet-700 transition">
                                        <i class="fas fa-eye text-[10px]"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($objectifs->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4">
                        {{ $objectifs->links() }}
                    </div>
                @endif
            @endif
        </div>

        @endif

    </div>
</div>
@endsection
