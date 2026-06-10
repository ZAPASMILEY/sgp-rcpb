@extends('layouts.directeur')

@section('title', 'Mes Chefs de Service | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $indexUrl  = route('directeur.subordonnes.chefs');
    $serviceId = $filters['serviceId'] ?? null;
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">
    <div class="w-full flex flex-col gap-6">

        {{-- ── Hero ───────────────────────────────────────────────────────────────── --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-blue-600 px-6 py-8 lg:px-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="relative flex flex-col gap-5">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-200">Subordonnés · {{ $ctx->getTypeLabel() }}</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Mes Chefs de Service</h1>
                    <p class="mt-1 text-sm text-indigo-100/80">{{ $ctx->getNom() }} · {{ $chefsData->count() }} service(s)</p>
                </div>

                {{-- Service filter cards --}}
                @if ($chefsData->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($chefsData as $item)
                            @php
                                $svc       = $item['service'];
                                $isActive  = $serviceId === $svc->id;
                                $targetUrl = $isActive
                                    ? $indexUrl.'?tab='.$filters['tab']
                                    : $indexUrl.'?tab='.$filters['tab'].'&service_id='.$svc->id;
                                $chef      = $item['chef'];
                                $chefNom   = $chef ? trim(($chef->prenom ?? '').' '.($chef->nom ?? '')) : null;
                                if (! $chefNom && $item['chefUser']) { $chefNom = $item['chefUser']->name; }
                                $initiale  = $chefNom ? strtoupper(mb_substr($chefNom, 0, 1)) : '?';
                            @endphp
                            <a href="{{ $targetUrl }}"
                               class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-bold transition
                                   {{ $isActive
                                       ? 'border-white/40 bg-white text-indigo-800 shadow-md'
                                       : 'border-white/20 bg-white/10 text-white hover:bg-white/20' }}">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-black
                                    {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-white/20 text-white' }}">
                                    {{ $initiale }}
                                </span>
                                <span>{{ $chefNom ?: 'Chef non assigné' }}</span>
                                <span class="text-[10px] font-semibold opacity-70">· {{ $svc->nom }}</span>
                                @if ($isActive)
                                    <i class="fas fa-times text-[10px] opacity-60 ml-0.5"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="px-4 lg:px-8 flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        @if ($chefsData->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <i class="fas fa-sitemap text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucun service rattaché à votre structure</p>
            </div>
        @else

        {{-- ── KPIs ────────────────────────────────────────────────────────────── --}}
        @php
            $totalEvals     = $chefsData->sum('evalCount');
            $totalObjectifs = $chefsData->sum('ficheCount');
            $totalAgents    = $chefsData->sum('agentsCount');
            $avecChef       = $chefsData->filter(fn($i) => $i['chef'] !== null)->count();
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Services',       'value' => $chefsData->count(), 'icon' => 'fas fa-sitemap',        'color' => 'bg-indigo-50 border-indigo-100',   'ic' => 'bg-indigo-600 text-white'],
                ['label' => 'Chefs assignés', 'value' => $avecChef,           'icon' => 'fas fa-user-tie',        'color' => 'bg-emerald-50 border-emerald-100', 'ic' => 'bg-emerald-600 text-white'],
                ['label' => 'Évaluations',    'value' => $totalEvals,         'icon' => 'fas fa-star-half-stroke','color' => 'bg-amber-50 border-amber-100',     'ic' => 'bg-amber-500 text-white'],
                ['label' => 'Agents total',   'value' => $totalAgents,        'icon' => 'fas fa-users',           'color' => 'bg-slate-50 border-slate-100',     'ic' => 'bg-slate-700 text-white'],
            ] as $kpi)
            <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['color'] }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['ic'] }} text-xs">
                        <i class="{{ $kpi['icon'] }}"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- ── Panel principal ─────────────────────────────────────────────────── --}}
        <div class="rounded-[20px] border border-slate-100 bg-white shadow-sm overflow-hidden">

            {{-- En-tête : tabs + boutons action --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 lg:px-8">

                {{-- Tabs --}}
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    @foreach ([
                        ['key' => 'objectifs',   'icon' => 'fas fa-bullseye',         'label' => 'Objectifs',   'count' => $fichesStats['total']],
                        ['key' => 'evaluations', 'icon' => 'fas fa-star-half-stroke', 'label' => 'Évaluations', 'count' => $evaluationsStats['total']],
                    ] as $t)
                        @php $tabUrl = $indexUrl.'?tab='.$t['key'].($serviceId ? '&service_id='.$serviceId : ''); @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                               {{ $filters['tab'] === $t['key'] ? 'border border-slate-200 bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="{{ $t['icon'] }} text-xs"></i>
                            {{ $t['label'] }}
                            <span class="inline-flex items-center justify-center rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-black text-slate-600 min-w-[1.2rem]">{{ $t['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Boutons action --}}
                @php $selectedItem = $serviceId ? $chefsData->first(fn($i) => $i['service']->id === $serviceId) : null; @endphp
                <div class="flex items-center gap-2">
                    @if ($filters['tab'] === 'objectifs')
                        @if ($objectifsEnabled && $serviceId && !$ficheBlocksNewForService && $selectedItem)
                            <a href="{{ route('directeur.subordonnes.service.objectifs.create', $selectedItem['service']) }}"
                               class="ent-btn ent-btn-primary text-xs">
                                <i class="fas fa-plus mr-1"></i> Nouvelle fiche
                            </a>
                        @elseif ($objectifsEnabled)
                            <span class="ent-btn ent-btn-primary text-xs cursor-not-allowed opacity-60 select-none pointer-events-none"
                                  title="{{ !$serviceId ? 'Sélectionnez un chef de service pour assigner des objectifs.' : 'Une fiche d\'objectifs est déjà assignée à ce chef.' }}">
                                <i class="fas fa-lock mr-1"></i> Nouvelle fiche
                            </span>
                        @endif
                    @else
                        @if ($evaluationsEnabled && $serviceId && $ficheAccepteeForService && !$evaluationEnCoursForService)
                            <a href="{{ route('directeur.evaluations.create', ['service_id' => $serviceId]) }}"
                               class="ent-btn ent-btn-primary text-xs">
                                <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                            </a>
                        @elseif ($evaluationsEnabled)
                            <span class="ent-btn ent-btn-primary text-xs cursor-not-allowed opacity-60 select-none pointer-events-none"
                                  title="{{ !$serviceId ? 'Sélectionnez un chef de service pour créer une évaluation.' : ($evaluationEnCoursForService ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : 'Aucune fiche d\'objectifs acceptée pour ce chef.') }}">
                                <i class="fas fa-lock mr-1"></i> Nouvelle évaluation
                            </span>
                        @endif
                    @endif

                    {{-- Lien dossier individuel --}}
                    @if ($selectedItem)
                        <a href="{{ route('directeur.subordonnes.service', $selectedItem['service']) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-indigo-700">
                            <i class="fas fa-folder-open text-[10px]"></i> Dossier
                        </a>
                    @endif
                </div>
            </div>

            {{-- Contenu du tab --}}
            <div class="px-6 py-6 lg:px-8">
                @if ($filters['tab'] === 'objectifs')
                    @include('directeur.subordonnes._tab_objectifs')
                @else
                    @include('directeur.subordonnes._tab_evaluations')
                @endif
            </div>
        </div>

        @endif

        </div>
    </div>
</div>
@endsection
