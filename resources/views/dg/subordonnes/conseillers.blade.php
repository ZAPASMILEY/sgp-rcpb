@extends('layouts.dg')

@section('title', 'Mes Conseillers | '.config('app.name'))

@section('content')
@php
    $indexUrl     = route('dg.conseillers');
    $conseillerId = $filters['conseillerId'] ?? null;
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- ── Hero ──────────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-5">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-300">Espace DG · Collaborateurs</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Mes Conseillers</h1>
                <p class="mt-0.5 text-sm text-emerald-100/80">{{ $conseillers->count() }} conseiller(s) rattaché(s)</p>
            </div>

            {{-- Conseiller filter cards --}}
            @if ($conseillers->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($conseillers as $c)
                        @php
                            $isActive  = $conseillerId === $c->id;
                            $targetUrl = $isActive
                                ? $indexUrl.'?tab='.$filters['tab']
                                : $indexUrl.'?tab='.$filters['tab'].'&conseiller_id='.$c->id;
                            $initiale  = strtoupper(mb_substr($c->name, 0, 1));
                        @endphp
                        <a href="{{ $targetUrl }}"
                           class="inline-flex items-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-bold transition
                               {{ $isActive
                                   ? 'border-white/40 bg-white text-emerald-800 shadow-md'
                                   : 'border-white/20 bg-white/10 text-white hover:bg-white/20' }}">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-black
                                {{ $isActive ? 'bg-emerald-600 text-white' : 'bg-white/20 text-white' }}">
                                {{ $initiale }}
                            </span>
                            {{ $c->name }}
                            @if ($isActive)
                                <i class="fas fa-times text-[10px] text-emerald-500 ml-0.5"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        @if ($conseillers->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucun conseiller configuré.</p>
                <p class="mt-1 text-xs text-slate-400">Contactez l'administrateur pour créer les comptes.</p>
            </div>
        @else

        {{-- ── Panel principal ─────────────────────────────────────────────────── --}}
        <div class="rounded-[20px] border border-slate-100 bg-white shadow-sm overflow-hidden">

            {{-- En-tête : tabs + boutons action --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4 lg:px-8">

                {{-- Tabs --}}
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    @foreach ([
                        ['key' => 'objectifs',   'icon' => 'fas fa-bullseye',          'label' => 'Objectifs',    'count' => $fichesStats['total']],
                        ['key' => 'evaluations', 'icon' => 'fas fa-star-half-stroke',  'label' => 'Évaluations',  'count' => $evaluationsStats['total']],
                    ] as $t)
                        @php
                            $tabUrl = $indexUrl.'?tab='.$t['key'].($conseillerId ? '&conseiller_id='.$conseillerId : '');
                        @endphp
                        <a href="{{ $tabUrl }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                               {{ $filters['tab'] === $t['key'] ? 'border border-slate-200 bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="{{ $t['icon'] }} text-xs"></i>
                            {{ $t['label'] }}
                            <span class="inline-flex items-center justify-center rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-black text-slate-600 min-w-[1.2rem]">{{ $t['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- Boutons action --}}
                <div class="flex items-center gap-2">
                    @if ($filters['tab'] === 'objectifs')
                        @if ($objectifsEnabled && $conseillerId && !$ficheBlocksNewForConseiller)
                            <a href="{{ route('dg.objectifs.create', ['subordonne_id' => $conseillerId]) }}"
                               class="ent-btn ent-btn-primary text-xs">
                                <i class="fas fa-plus mr-1"></i> Nouvelle fiche
                            </a>
                        @elseif ($objectifsEnabled)
                            <span class="ent-btn ent-btn-primary text-xs cursor-not-allowed opacity-60 select-none pointer-events-none"
                                  title="{{ !$conseillerId ? 'Sélectionnez un conseiller pour assigner des objectifs.' : 'Une fiche d\'objectifs est déjà assignée à ce conseiller.' }}">
                                <i class="fas fa-lock mr-1"></i> Nouvelle fiche
                            </span>
                        @endif
                    @else
                        @if ($evaluationsEnabled && $conseillerId && $ficheAccepteeForConseiller && !$evaluationEnCoursForConseiller)
                            <a href="{{ route('dg.sub-evaluations.create', ['subordonne_id' => $conseillerId]) }}"
                               class="ent-btn ent-btn-primary text-xs">
                                <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                            </a>
                        @elseif ($evaluationsEnabled)
                            <span class="ent-btn ent-btn-primary text-xs cursor-not-allowed opacity-60 select-none pointer-events-none"
                                  title="{{ !$conseillerId ? 'Sélectionnez un conseiller pour créer une évaluation.' : ($evaluationEnCoursForConseiller ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : 'Aucune fiche d\'objectifs acceptée pour ce conseiller.') }}">
                                <i class="fas fa-lock mr-1"></i> Nouvelle évaluation
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Contenu du tab --}}
            <div class="px-6 py-6 lg:px-8">
                @if ($filters['tab'] === 'objectifs')
                    @include('dg.subordonnes._tab_objectifs')
                @else
                    @include('dg.subordonnes._tab_evaluations')
                @endif
            </div>
        </div>

        @endif

    </div>
    </div>
</div>
@endsection
