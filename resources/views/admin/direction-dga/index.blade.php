@extends('layouts.app')
@section('title', 'Direction DGA | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction DGA')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full space-y-6">

    @if (session('status'))
        <div id="status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"><i class="fas fa-check"></i></div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('status-msg')?.remove(), 3000);</script>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-500"><i class="fas fa-exclamation-triangle text-sm"></i></div>
            <p class="text-sm font-semibold text-rose-700">{{ session('error') }}</p>
        </div>
    @endif

    @if (! $direction)
        <div class="rounded-2xl bg-white p-10 shadow-sm text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-100">
                <i class="fas fa-sitemap text-2xl text-purple-400"></i>
            </div>
            <p class="font-bold text-slate-600">Direction DGA introuvable.</p>
            <p class="mt-1 text-xs text-slate-400">La direction « Direction Générale Adjointe » n'existe pas encore ou aucun DGA n'y est affecté.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('admin.directions.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-plus text-xs text-slate-400"></i> Créer la direction
                </a>
                <a href="{{ route('admin.direction-dga.configurer') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fas fa-user-tie text-xs"></i> Configurer le DGA
                </a>
            </div>
        </div>
    @else

    {{-- ── En-tête direction ─────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Faîtière / Direction</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $direction->nom }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.direction-dga.configurer') }}" class="ent-btn ent-btn-soft text-xs py-1.5 px-4">
                    <i class="fas fa-user-tie mr-1.5"></i>Configurer DGA
                </a>
                <a href="{{ route('admin.directions.edit', $direction) }}" class="ent-btn ent-btn-primary text-xs py-1.5 px-4">
                    <i class="fas fa-pen mr-1.5"></i>Modifier
                </a>
            </div>
        </div>

        {{-- DGA + Secrétaire --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            {{-- DGA --}}
            <div class="flex items-center gap-4 rounded-2xl border border-purple-100 bg-purple-50/60 p-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 font-black text-lg">
                    {{ $direction->directeur ? strtoupper(substr($direction->directeur->prenom, 0, 1)) : '?' }}
                </div>
                <div class="min-w-0">
                    @if($direction->directeur)
                        <p class="font-black text-slate-900 truncate">{{ $direction->directeur->prenom }} {{ $direction->directeur->nom }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $direction->directeur->email }}</p>
                    @else
                        <p class="text-sm italic text-slate-400">DGA non affecté</p>
                    @endif
                    <span class="mt-1 inline-flex rounded-full bg-purple-500 px-2 py-0.5 text-[10px] font-bold text-white">DGA</span>
                </div>
            </div>

            {{-- Secrétaire --}}
            <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-600 font-black text-lg">
                    {{ $direction->secretaire ? strtoupper(substr($direction->secretaire->prenom, 0, 1)) : '?' }}
                </div>
                <div class="min-w-0 flex-1">
                    @if($direction->secretaire)
                        <p class="font-black text-slate-900 truncate">{{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $direction->secretaire->email }}</p>
                        <span class="mt-1 inline-flex rounded-full bg-slate-400 px-2 py-0.5 text-[10px] font-bold text-white">Secrétaire</span>
                    @else
                        <p class="text-sm italic text-slate-400">Secrétaire non affecté(e)</p>
                    @endif
                </div>
                <a href="{{ route('admin.direction-dga.secretaire.edit') }}"
                   class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition">
                    <i class="fas fa-pen text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Services ──────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-black text-slate-900">Services</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ count($services) }} service{{ count($services) > 1 ? 's' : '' }} rattaché{{ count($services) > 1 ? 's' : '' }} à la Direction DGA</p>
            </div>
            <span class="rounded-full bg-purple-100 px-3 py-1 text-xs font-bold text-purple-700">{{ count($services) }}</span>
        </div>

        @if(count($services) === 0)
            <div class="px-5 py-10 text-center text-sm text-slate-400">Aucun service configuré.</div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($services as $s)
                @php
                    $service   = $s['service'];
                    $chef      = $s['chef'];
                    $chefUser  = $s['chefUser'];
                    // Agents du service hors chef
                    $membres   = $service->agents->where('id', '!=', $chef?->id)->values();
                @endphp
                <div class="px-5 py-4">
                    {{-- Ligne chef + action --}}
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white font-black text-lg shadow">
                            {{ strtoupper(substr($service->nom, 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-900">{{ $service->nom }}</p>
                            @if($chef)
                                <p class="mt-0.5 text-sm text-slate-500">
                                    <i class="fas fa-user-tie mr-1 text-purple-400"></i>
                                    <span class="font-semibold text-slate-700">{{ $chef->prenom }} {{ $chef->nom }}</span>
                                    <span class="text-xs text-slate-400 ml-1">— {{ $chef->role }}</span>
                                </p>
                            @else
                                <p class="mt-0.5 text-sm italic text-slate-400">Chef non affecté</p>
                            @endif
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                    <i class="fas fa-users text-[9px]"></i> {{ $s['nbAgents'] }} agent{{ $s['nbAgents'] > 1 ? 's' : '' }}
                                </span>
                                @if($chefUser)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                                        {{ $chefUser->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        Compte {{ $chefUser->is_active ? 'actif' : 'inactif' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-700 px-2.5 py-0.5 text-[11px] font-semibold">
                                        <i class="fas fa-exclamation-triangle text-[9px]"></i> Sans compte
                                    </span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin.direction-dga.services.chef.edit', $service) }}"
                           class="shrink-0 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-purple-50 hover:border-purple-200 hover:text-purple-700 transition">
                            <i class="fas fa-pen text-[10px]"></i>
                            {{ $chef ? 'Changer le chef' : 'Affecter un chef' }}
                        </a>
                    </div>

                    {{-- Agents du service --}}
                    @if($membres->isNotEmpty())
                        <div class="mt-3 ml-15 pl-1 border-l-2 border-purple-100 space-y-1" style="margin-left:3.75rem">
                            @foreach($membres as $agent)
                            @php $agentUser = \App\Models\User::where('agent_id', $agent->id)->first(); @endphp
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-black">
                                    {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $agent->role }}</p>
                                </div>
                                @if($agentUser)
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        {{ $agentUser->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        {{ $agentUser->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                @else
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-400 px-2 py-0.5 text-[10px] font-semibold">
                                        Sans compte
                                    </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @elseif($s['nbAgents'] <= 1)
                        <p class="mt-2 text-xs italic text-slate-300" style="margin-left:3.75rem">Aucun autre agent dans ce service.</p>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Collaborateurs directs ─────────────────────────────────────────── --}}
    @if($agentsDirects->isNotEmpty())
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-black text-slate-900">Collaborateurs directs</h2>
            <p class="text-xs text-slate-400 mt-0.5">Agents rattachés directement à la direction (hors services)</p>
        </div>
        <ul class="divide-y divide-slate-50">
            @foreach($agentsDirects as $agent)
            <li class="flex items-center gap-4 px-5 py-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 font-black text-sm">
                    {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $agent->prenom }} {{ $agent->nom }}</p>
                    <p class="text-xs text-slate-400">{{ $agent->role }}</p>
                </div>
                @if($agent->user)
                    <span class="inline-flex items-center gap-1 rounded-full {{ $agent->user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }} px-2.5 py-0.5 text-[11px] font-semibold">
                        <i class="fas fa-circle text-[6px]"></i> Compte {{ $agent->user->is_active ? 'actif' : 'inactif' }}
                    </span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @endif {{-- end if $direction --}}

</div>
</div>
@endsection
