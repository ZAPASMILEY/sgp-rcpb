@extends('layouts.dga')
@section('title', 'Structures | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-teal-700 via-teal-600 to-emerald-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-emerald-300/40 blur-2xl"></div>
        </div>
        <div class="relative">
            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-teal-200">Espace DGA</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Structures du Réseau</h1>
            <p class="mt-1 text-sm text-teal-100/80">
                Direction DGA · Délégations Techniques · Caisses · Agences
            </p>
        </div>
    </div>

    <div class="mx-auto max-w-screen-xl px-4 pt-0 lg:px-8">

        {{-- ─── Barre d'onglets ────────────────────────────────────────────── --}}
        <div class="flex items-end overflow-x-auto bg-white shadow-sm border-b border-slate-200 rounded-b-none">

            {{-- Onglet Services DGA --}}
            @if($direction)
            <a href="{{ route('dga.structures.index', ['tab' => 'services-dga']) }}"
               class="flex shrink-0 items-center gap-2 px-5 py-4 text-sm font-bold border-b-2 transition-colors whitespace-nowrap
                      @if($tab === 'services-dga') border-violet-600 text-violet-700 bg-violet-50 @else border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 @endif">
                <i class="fas fa-sitemap text-xs"></i>
                <span>Services DGA</span>
            </a>
            @endif

            {{-- Onglet par DT --}}
            @foreach($delegations as $dt)
            <a href="{{ route('dga.structures.index', ['tab' => $dt->id]) }}"
               class="flex shrink-0 items-center gap-2 px-5 py-4 text-sm font-bold border-b-2 transition-colors whitespace-nowrap
                      @if((string)$tab === (string)$dt->id) border-emerald-600 text-emerald-700 bg-emerald-50 @else border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50 @endif">
                <i class="fas fa-map-marker-alt text-xs"></i>
                <span>DT {{ $dt->region }}</span>
            </a>
            @endforeach
        </div>

        <div class="space-y-5 pt-5">

        {{-- ═══════════════ ONGLET SERVICES DGA ═══════════════ --}}
        @if($tab === 'services-dga')

            <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                {{-- En-tête --}}
                <div class="flex items-center gap-3 bg-gradient-to-r from-violet-600 to-purple-600 px-6 py-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white ring-1 ring-white/30">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <p class="font-black text-white text-base">{{ $direction?->nom ?? 'Direction DGA' }}</p>
                        <p class="text-xs text-violet-200">{{ count($servicesDga) }} service(s)</p>
                    </div>
                </div>

                @if(count($servicesDga) === 0)
                    <div class="px-6 py-16 text-center">
                        <i class="fas fa-sitemap text-4xl text-slate-200"></i>
                        <p class="mt-4 text-sm font-semibold text-slate-400">Aucun service dans cette direction.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach($servicesDga as $item)
                        @php $note = $item['note']['moyenne']; $svc = $item['service']; @endphp
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-700 font-black text-sm">
                                    {{ strtoupper(substr($svc->nom, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">{{ $svc->nom }}</p>
                                    <div class="mt-0.5 flex items-center gap-3 text-xs text-slate-400">
                                        <span><i class="fas fa-users mr-1"></i>{{ $item['nbAgents'] }} agent(s)</span>
                                        @if($svc->chef)
                                            <span><i class="fas fa-user-tie mr-1"></i>{{ $svc->chef->prenom }} {{ $svc->chef->nom }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ $item['note']['total'] }} éval. validées</span>
                                @if($note !== null)
                                    <span class="inline-flex items-center rounded-full px-3 py-1.5 text-sm font-black {{ $noteColor($note) }}">
                                        {{ number_format($note, 2) }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-400">—</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        {{-- ═══════════════ ONGLET DT ═══════════════ --}}
        @elseif($dtData !== null)
        @php $dt = $dtData['dt']; $dtNote = $dtData['note']['moyenne']; @endphp

            {{-- En-tête DT --}}
            <div class="rounded-2xl overflow-hidden shadow-sm">
                <div class="flex items-center justify-between bg-gradient-to-r from-emerald-700 to-teal-600 px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white font-black text-xl ring-1 ring-white/30">
                            {{ strtoupper(substr($dt->region, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-black text-white text-lg">DT {{ $dt->region }}</p>
                            <div class="mt-0.5 flex flex-wrap items-center gap-3 text-xs text-emerald-100">
                                @if($dt->ville)<span><i class="fas fa-map-pin mr-1"></i>{{ $dt->ville }}</span>@endif
                                @if($dt->directeur)<span><i class="fas fa-user-tie mr-1"></i>{{ $dt->directeur->prenom }} {{ $dt->directeur->nom }}</span>@endif
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($dtNote !== null)
                            <p class="text-3xl font-black text-white">{{ number_format($dtNote, 2) }}</p>
                            <p class="text-[10px] text-emerald-200 font-semibold">{{ $dtData['note']['total'] }} éval. validées</p>
                        @else
                            <p class="text-2xl font-black text-white/40">—</p>
                            <p class="text-[10px] text-emerald-200/60">Aucune évaluation</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Services de la DT --}}
            @if($dtData['services']->count() > 0)
            <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 bg-slate-50">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-100 text-teal-700 text-sm">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <p class="font-bold text-slate-700 text-sm">Services de la délégation</p>
                    <span class="ml-auto rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $dtData['services']->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($dtData['services'] as $item)
                    @php $note = $item['note']['moyenne']; $svc = $item['service']; @endphp
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700 font-black text-xs">
                                {{ strtoupper(substr($svc->nom, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 text-sm">{{ $svc->nom }}</p>
                                <div class="mt-0.5 flex items-center gap-3 text-xs text-slate-400">
                                    <span>{{ $item['nbAgents'] }} agent(s)</span>
                                    @if($svc->chef)<span>{{ $svc->chef->prenom }} {{ $svc->chef->nom }}</span>@endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400">{{ $item['note']['total'] }} éval.</span>
                            @if($note !== null)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-black {{ $noteColor($note) }}">{{ number_format($note, 2) }}</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-400">—</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Agents directs --}}
            @if($dtData['services']->count() === 0)
                <div class="rounded-2xl bg-white px-6 py-14 text-center shadow-sm">
                    <i class="fas fa-cogs text-4xl text-slate-200"></i>
                    <p class="mt-4 text-sm font-semibold text-slate-400">Aucun service dans cette délégation.</p>
                </div>
            @endif

        {{-- Onglet introuvable --}}
        @else
            <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm">
                <i class="fas fa-exclamation-circle text-4xl text-slate-200"></i>
                <p class="mt-4 text-sm font-semibold text-slate-400">Structure introuvable.</p>
            </div>
        @endif

        </div>{{-- end space-y-5 --}}
    </div>
</div>
@endsection
