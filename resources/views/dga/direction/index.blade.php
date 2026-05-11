@extends('layouts.dga')
@section('title', 'Ma Direction | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

{{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
<header class="admin-panel px-6 py-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DGA</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $direction->nom }}</h1>
            @if($direction->secretaire)
                <p class="mt-1 text-sm text-slate-500">
                    <i class="fas fa-user-tie mr-1 text-slate-400"></i>
                    Secrétaire : <span class="font-semibold">{{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}</span>
                </p>
            @endif
        </div>
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 shadow-sm">
            <i class="fas fa-sitemap text-xl"></i>
        </div>
    </div>
</header>

@if(session('status'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
        {{ session('status') }}
    </div>
@endif

{{-- ── KPIs ─────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    @php
        $kpis = [
            ['label' => 'Services',   'value' => count($services),  'icon' => 'fas fa-layer-group',   'bg' => '#ede9fe', 'color' => '#7c3aed'],
            ['label' => 'Agents',     'value' => $totalAgents,      'icon' => 'fas fa-users',          'bg' => '#dbeafe', 'color' => '#1d4ed8'],
            ['label' => 'Évaluations','value' => $totalEvals,       'icon' => 'fas fa-clipboard-check','bg' => '#d1fae5', 'color' => '#065f46'],
            ['label' => 'Note moy.',  'value' => $noteMoyenne !== null ? number_format($noteMoyenne,2).'⁄10' : '—', 'icon' => 'fas fa-star', 'bg' => '#fef3c7', 'color' => '#b45309'],
        ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="admin-panel flex items-center gap-4 px-5 py-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl"
             style="background:{{ $kpi['bg'] }}; color:{{ $kpi['color'] }}">
            <i class="{{ $kpi['icon'] }}"></i>
        </div>
        <div>
            <p class="text-xl font-black text-slate-900">{{ $kpi['value'] }}</p>
            <p class="text-xs font-semibold text-slate-400">{{ $kpi['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Services ─────────────────────────────────────────────────────────── --}}
<section class="admin-panel overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                <i class="fas fa-layer-group text-sm"></i>
            </span>
            <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Mes Services</h2>
        </div>
        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">
            {{ count($services) }} service{{ count($services) > 1 ? 's' : '' }}
        </span>
    </div>

    @if(count($services) === 0)
        <div class="px-6 py-10 text-center text-sm text-slate-400">Aucun service dans cette direction.</div>
    @else
        <div class="divide-y divide-slate-50">
            @foreach($services as $s)
            @php
                $service   = $s['service'];
                $chef      = $s['chef'];
                $chefUser  = $s['chefUser'];
                $mention   = match(true) {
                    $s['noteAvg'] === null      => null,
                    $s['noteAvg'] >= 8.5        => ['label'=>'Excellent','bg'=>'#d1fae5','color'=>'#065f46'],
                    $s['noteAvg'] >= 7          => ['label'=>'Bien',     'bg'=>'#dbeafe','color'=>'#1d4ed8'],
                    $s['noteAvg'] >= 5          => ['label'=>'Passable', 'bg'=>'#fef3c7','color'=>'#b45309'],
                    default                     => ['label'=>'Insuffisant','bg'=>'#fee2e2','color'=>'#991b1b'],
                };
            @endphp
            <div class="px-6 py-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    {{-- Infos service --}}
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-600 text-white font-black text-lg shadow">
                            {{ strtoupper(substr($service->nom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">{{ $service->nom }}</p>
                            @if($chef)
                                <p class="mt-0.5 text-sm text-slate-500">
                                    <i class="fas fa-user mr-1 text-slate-400"></i>
                                    {{ $chef->prenom }} {{ $chef->nom }}
                                    <span class="ml-1 text-xs text-slate-400">— {{ $chef->fonction }}</span>
                                </p>
                            @else
                                <p class="mt-0.5 text-sm italic text-slate-400">Chef non affecté</p>
                            @endif
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                    <i class="fas fa-users text-[9px]"></i> {{ $s['nbAgents'] }} agent{{ $s['nbAgents'] > 1 ? 's' : '' }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                    <i class="fas fa-clipboard-check text-[9px]"></i> {{ $s['nbEvals'] }} éval.
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                    <i class="fas fa-bullseye text-[9px]"></i> {{ $s['nbObjectifs'] }} obj.
                                </span>
                                @if($mention)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                          style="background:{{ $mention['bg'] }}; color:{{ $mention['color'] }}">
                                        {{ number_format($s['noteAvg'],2) }}/10 · {{ $mention['label'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    @if($chefUser)
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('dga.sub-evaluations.create', ['user' => $chefUser->id]) }}"
                           class="ent-btn ent-btn-primary py-1.5 px-4 text-xs">
                            <i class="fas fa-pen-to-square mr-1.5"></i>Évaluer
                        </a>
                        <a href="{{ route('dga.sub-objectifs.create', ['user' => $chefUser->id]) }}"
                           class="ent-btn ent-btn-soft py-1.5 px-4 text-xs">
                            <i class="fas fa-bullseye mr-1.5"></i>Objectifs
                        </a>
                        <a href="{{ route('dga.subordonnes.show', $chefUser) }}"
                           class="ent-btn py-1.5 px-4 text-xs border border-slate-200 text-slate-500 hover:bg-slate-50">
                            <i class="fas fa-folder-open mr-1.5"></i>Dossier
                        </a>
                    </div>
                    @else
                        <span class="text-xs text-slate-400 italic">Compte non activé</span>
                    @endif
                </div>

                {{-- Agents du service --}}
                @if($service->agents->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2 pl-16">
                    @foreach($service->agents->where('id','!=',$chef?->id) as $agent)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600">
                        <i class="fas fa-user text-slate-300 text-[10px]"></i>
                        {{ $agent->prenom }} {{ $agent->nom }}
                        <span class="text-slate-400">· {{ $agent->fonction }}</span>
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ── Collaborateurs directs (sans service) ─────────────────────────────── --}}
@if($agentsDirects->isNotEmpty())
<section class="admin-panel overflow-hidden">
    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4">
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
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 font-black">
                    {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                    <p class="text-xs text-slate-400">{{ $agent->fonction }}</p>
                </div>
            </div>
            @php $agentUser = \App\Models\User::where('agent_id', $agent->id)->first(); @endphp
            @if($agentUser)
                <a href="{{ route('dga.subordonnes.show', $agentUser) }}"
                   class="ent-btn py-1 px-3 text-xs border border-slate-200 text-slate-500 hover:bg-slate-50">
                    <i class="fas fa-folder-open mr-1"></i>Dossier
                </a>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif

</div>
</div>
@endsection
