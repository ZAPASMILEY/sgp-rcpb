@extends('layouts.chef')

@section('title', 'Mon Équipe | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">
    <div class="w-full flex flex-col gap-6">

        {{-- Hero --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-600 px-6 py-8 lg:px-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-10 left-1/3 h-40 w-40 rounded-full bg-indigo-300/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-blue-200">Mon Équipe · {{ $ctx->getTypeLabel() }}</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white">{{ $ctx->getNom() }}</h1>
                    @if ($ctx->getParentNom())
                        <p class="mt-1 text-sm text-blue-100/80">{{ $ctx->getParentNom() }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-2 sm:mt-0">
                    @if($evaluationsEnabled)
                        <a href="{{ route('chef.evaluations.create') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                            <i class="fas fa-star-half-stroke text-xs"></i> Évaluer
                        </a>
                    @else
                        <span class="ent-btn-disabled-dark"><i class="fas fa-star-half-stroke text-xs"></i> Évaluer</span>
                    @endif
                    @if($objectifsEnabled)
                        <a href="{{ route('chef.objectifs.create') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-blue-700 shadow transition hover:bg-blue-50">
                            <i class="fas fa-list-check text-xs"></i> Assigner objectifs
                        </a>
                    @else
                        <span class="ent-btn-disabled-dark"><i class="fas fa-list-check text-xs"></i> Assigner objectifs</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-4 lg:px-8 flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-3 gap-3">
            @foreach ([
                ['label' => 'Agents dans l\'équipe', 'value' => $stats['total_agents'],       'icon' => 'fas fa-users',           'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
                ['label' => 'Déjà évalués',          'value' => $stats['agents_evalues'],      'icon' => 'fas fa-clipboard-check', 'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
                ['label' => 'Évaluations créées',    'value' => $stats['evaluations_creees'],  'icon' => 'fas fa-star-half-stroke','color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-100'],
            ] as $kpi)
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

        {{-- Liste des agents --}}
        <div class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 lg:px-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                    Agents de {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                </p>
            </div>

            @if ($agentsOverview->isEmpty())
                <div class="px-6 py-16 text-center lg:px-8">
                    <i class="fas fa-users text-3xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">
                        Aucun agent dans votre {{ $ctx->getTypeLabel() }} pour l'instant.
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Contactez l'administrateur pour affecter des agents à votre structure.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($agentsOverview as $row)
                        @php
                            $ag     = $row['agent'];
                            $eval   = $row['latest_eval'];
                            $statut = $row['eval_statut'];
                            $note   = $row['eval_note'];

                            $evalClass = match ($statut) {
                                'valide'    => 'bg-emerald-100 text-emerald-700',
                                'soumis'    => 'bg-amber-100 text-amber-700',
                                'refuse'    => 'bg-rose-100 text-rose-700',
                                'brouillon' => 'bg-slate-100 text-slate-600',
                                default     => null,
                            };
                            $evalLabel = match ($statut) {
                                'valide'    => 'Acceptée',
                                'soumis'    => 'Soumise',
                                'refuse'    => 'Refusée',
                                'brouillon' => 'Brouillon',
                                default     => null,
                            };
                            $noteClass = $note !== null ? match(true) {
                                (float)$note >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                (float)$note >= 7   => 'bg-sky-100 text-sky-700',
                                (float)$note >= 5   => 'bg-amber-100 text-amber-700',
                                default             => 'bg-rose-100 text-rose-700',
                            } : null;
                            $noteBar = $note !== null ? max(0, min(100, (float)$note * 10)) : 0;
                            $barClass = $note !== null ? match(true) {
                                (float)$note >= 8.5 => 'bg-emerald-500',
                                (float)$note >= 7   => 'bg-sky-500',
                                (float)$note >= 5   => 'bg-amber-400',
                                default             => 'bg-rose-400',
                            } : 'bg-slate-200';
                        @endphp
                        <div class="flex flex-wrap items-center gap-5 px-6 py-5 transition hover:bg-slate-50/70 lg:px-8">

                            {{-- Avatar --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 text-base font-black shadow-sm">
                                {{ strtoupper(substr(trim($ag->prenom . ' ' . $ag->nom), 0, 1)) }}
                            </div>

                            {{-- Identité --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-slate-900 text-base">
                                        {{ trim($ag->prenom . ' ' . $ag->nom) }}
                                    </p>
                                    @if ($ag->fonction)
                                        <span class="text-xs text-slate-400">· {{ $ag->fonction }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if ($ag->numero_telephone)
                                        <span class="text-[11px] text-slate-400">
                                            <i class="fas fa-phone mr-1 text-[9px]"></i>{{ $ag->numero_telephone }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Dernière note --}}
                            <div class="hidden w-36 shrink-0 sm:block">
                                @if ($note !== null)
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Dernière note</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black {{ $noteClass }}">
                                            {{ number_format((float)$note, 2, ',', ' ') }}/10
                                        </span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width:{{ $noteBar }}%"></div>
                                    </div>
                                    @if ($evalLabel)
                                        <span class="mt-1.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $evalClass }}">
                                            {{ $evalLabel }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-300">Non évalué</span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                @if($evaluationsEnabled)
                                    <a href="{{ route('chef.evaluations.create', ['agent_id' => $ag->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="Fonctionnalité désactivée"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled)
                                    <a href="{{ route('chef.objectifs.create', ['agent_id' => $ag->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="Fonctionnalité désactivée"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                                @if ($eval)
                                    <a href="{{ route('chef.evaluations.show', $eval) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-blue-700">
                                        <i class="fas fa-eye text-[10px]"></i> Voir éval.
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    </div>
</div>
@endsection
