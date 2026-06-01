@extends('layouts.directeur')

@section('title', 'Mes Chefs de Service | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">
    <div class="w-full flex flex-col gap-6">

        {{-- Hero --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Subordonnés · {{ $ctx->getTypeLabel() }}</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Mes Chefs de Service</h1>
                    <p class="mt-1 text-sm text-emerald-100/80">{{ $ctx->getNom() }} · {{ $chefsData->count() }} service(s)</p>
                </div>
            </div>
        </div>

        <div class="px-4 lg:px-8 flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- KPIs --}}
        @php
            $totalEvals   = $chefsData->sum('evalCount');
            $totalObjectifs = $chefsData->sum('ficheCount');
            $totalAgents  = $chefsData->sum('agentsCount');
            $avecChef     = $chefsData->filter(fn($i) => $i['chef'] !== null)->count();
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Services',        'value' => $chefsData->count(), 'icon' => 'fas fa-sitemap',        'color' => 'bg-indigo-50 border-indigo-100', 'ic' => 'bg-indigo-600 text-white'],
                ['label' => 'Chefs assignés',  'value' => $avecChef,           'icon' => 'fas fa-user-tie',        'color' => 'bg-emerald-50 border-emerald-100', 'ic' => 'bg-emerald-600 text-white'],
                ['label' => 'Évaluations',     'value' => $totalEvals,         'icon' => 'fas fa-star-half-stroke','color' => 'bg-amber-50 border-amber-100',   'ic' => 'bg-amber-500 text-white'],
                ['label' => 'Agents total',    'value' => $totalAgents,        'icon' => 'fas fa-users',           'color' => 'bg-slate-50 border-slate-100',   'ic' => 'bg-slate-700 text-white'],
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

        {{-- Liste des chefs --}}
        <div class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 lg:px-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Responsables des services</p>
            </div>

            @if ($chefsData->isEmpty())
                <div class="px-6 py-16 text-center lg:px-8">
                    <i class="fas fa-sitemap text-3xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun service rattaché à votre structure</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($chefsData as $item)
                        @php
                            $svc   = $item['service'];
                            $chef  = $item['chef'];      // Agent|null
                            $eval  = $item['latestEval'];

                            // Nom affiché : prenom+nom de l'agent, ou user->name, sinon —
                            $chefNom = $chef
                                ? trim(($chef->prenom ?? '') . ' ' . ($chef->nom ?? ''))
                                : null;
                            if (! $chefNom && $item['chefUser']) {
                                $chefNom = $item['chefUser']->name;
                            }
                            $chefInitiale = $chefNom ? strtoupper(substr($chefNom, 0, 1)) : '?';

                            $note      = $eval ? (float) $eval->note_finale : null;
                            $noteClass = $note !== null ? match(true) {
                                $note >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                $note >= 7   => 'bg-sky-100 text-sky-700',
                                $note >= 5   => 'bg-amber-100 text-amber-700',
                                default      => 'bg-rose-100 text-rose-700',
                            } : null;
                            $noteBar = $note !== null ? max(0, min(100, $note * 10)) : 0;
                            $barClass = $note !== null ? match(true) {
                                $note >= 8.5 => 'bg-emerald-500',
                                $note >= 7   => 'bg-sky-500',
                                $note >= 5   => 'bg-amber-400',
                                default      => 'bg-rose-400',
                            } : 'bg-slate-200';

                            $statutClass = $eval ? match($eval->statut) {
                                'valide'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'soumis'      => 'bg-amber-100 text-amber-700 border-amber-200',
                                'refuse'      => 'bg-rose-100 text-rose-700 border-rose-200',
                                'reclamation' => 'bg-orange-100 text-orange-700 border-orange-200',
                                'a_reviser'   => 'bg-purple-100 text-purple-700 border-purple-200',
                                default       => 'bg-slate-100 text-slate-600 border-slate-200',
                            } : null;
                            $statutLabel = $eval ? match($eval->statut) {
                                'valide' => 'Validée', 'soumis' => 'Soumise',
                                'refuse' => 'Refusée', 'reclamation' => 'Réclamation', 'a_reviser' => 'À réviser', 'brouillon' => 'Brouillon', default => ucfirst((string) $eval->statut),
                            } : null;
                        @endphp
                        <div class="flex flex-wrap items-center gap-5 px-6 py-5 transition hover:bg-slate-50/70 lg:px-8">

                            {{-- Avatar --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
                                {{ $chef ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }}
                                text-base font-black shadow-sm">
                                {{ $chefInitiale }}
                            </div>

                            {{-- Identité --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-slate-900 text-base">
                                        {{ $chefNom ?: 'Chef non assigné' }}
                                    </p>
                                    <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-0.5 text-[10px] font-black text-indigo-700">
                                        <i class="fas fa-sitemap text-[8px]"></i> {{ $svc->nom }}
                                    </span>
                                    @if ($chef?->role)
                                        <span class="text-xs text-slate-400">· {{ $chef->role }}</span>
                                    @endif
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-3">
                                    <span class="text-[11px] font-semibold text-slate-400">
                                        <i class="fas fa-users mr-1 text-[9px]"></i>{{ $item['agentsCount'] }} agents
                                    </span>
                                    <span class="text-slate-200">·</span>
                                    <span class="text-[11px] font-semibold text-slate-400">
                                        <i class="fas fa-star mr-1 text-[9px]"></i>{{ $item['evalCount'] }} éval.
                                    </span>
                                    <span class="text-slate-200">·</span>
                                    <span class="text-[11px] font-semibold text-slate-400">
                                        <i class="fas fa-bullseye mr-1 text-[9px]"></i>{{ $item['ficheCount'] }} objectifs
                                    </span>
                                </div>
                            </div>

                            {{-- Dernière note --}}
                            <div class="hidden w-36 shrink-0 sm:block">
                                @if ($note !== null)
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Dernière note</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black {{ $noteClass }}">
                                            {{ number_format($note, 2, ',', ' ') }}/10
                                        </span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width:{{ $noteBar }}%"></div>
                                    </div>
                                    @if ($statutLabel)
                                        <span class="mt-1.5 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-bold {{ $statutClass }}">{{ $statutLabel }}</span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-300">Non évalué</span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                @if($evaluationsEnabled)
                                    <a href="{{ route('directeur.evaluations.create', ['service_id' => $svc->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="Fonctionnalité désactivée"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled)
                                    <a href="{{ route('directeur.subordonnes.service.objectifs.create', $svc) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="Fonctionnalité désactivée"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                                <a href="{{ route('directeur.subordonnes.service', $svc) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-indigo-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
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
