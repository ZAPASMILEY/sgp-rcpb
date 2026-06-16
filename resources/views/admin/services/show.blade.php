@extends('layouts.app')

@section('title', $service->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="space-y-6 pb-8">

    {{-- Toast --}}
    @if(session('status'))
        <div id="status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fas fa-check"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('status-msg')?.remove(), 3500);</script>
    @endif

    {{-- En-tête gradient --}}
    <div class="rounded-2xl overflow-hidden shadow-lg">
        <div style="background: linear-gradient(135deg, #0891b2 0%, #2563eb 60%, #7c3aed 100%)" class="px-8 py-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                {{-- Titre service --}}
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm text-white text-2xl shadow-inner">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">{{ $service->nom }}</h1>
                        <p class="text-xs text-white/70 mt-1 font-semibold uppercase tracking-wider">
                            Service
                            @if($service->direction) · {{ $service->direction->nom }}
                            @elseif($service->caisse ?? null) · {{ $service->caisse->nom }}
                            @elseif($service->delegationTechnique ?? null) · {{ $service->delegationTechnique->region }}
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="flex items-center gap-2">
                    <a href="{{ url()->previous() }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 backdrop-blur-sm border border-white/30 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-white/30">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                    <a href="{{ route('admin.services.edit', $service) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold shadow-sm transition hover:bg-slate-100"
                       style="color:#0891b2">
                        <i class="fas fa-pen text-xs"></i> Modifier
                    </a>
                </div>
            </div>
        </div>

        {{-- Bande de stats --}}
        <div class="bg-white border-t border-slate-100 grid grid-cols-3 divide-x divide-slate-100">
            <div class="px-6 py-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600">
                    <i class="fas fa-users text-sm"></i>
                </div>
                <div>
                    <p class="text-xl font-black text-slate-900">{{ $service->agents->count() }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Agents</p>
                </div>
            </div>
            <div class="px-6 py-4 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <i class="fas fa-sitemap text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900 truncate max-w-[160px]">
                        @if($service->direction) {{ $service->direction->nom }}
                        @elseif($service->caisse ?? null) {{ $service->caisse->nom }}
                        @elseif($service->delegationTechnique ?? null) {{ $service->delegationTechnique->region }}
                        @else — @endif
                    </p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rattachement</p>
                </div>
            </div>
            <div class="px-6 py-4 flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600 text-xs font-black">
                    @if($service->chef)
                        {{ strtoupper(substr($service->chef->prenom,0,1)) }}{{ strtoupper(substr($service->chef->nom,0,1)) }}
                    @else
                        <i class="fas fa-user-slash text-sm"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-black text-slate-900 truncate max-w-[160px]">
                        {{ $service->chef ? $service->chef->prenom.' '.$service->chef->nom : '—' }}
                    </p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Chef de service</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Agents --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden border border-slate-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100"
             style="background: linear-gradient(to right, #ecfeff, #eff6ff)">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600">
                    <i class="fas fa-users text-sm"></i>
                </div>
                <h2 class="text-sm font-black uppercase tracking-[0.14em] text-cyan-800">
                    Agents affectés
                    <span class="ml-2 inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-black text-white shadow-sm"
                          style="background: linear-gradient(135deg, #0891b2, #2563eb)">
                        {{ $service->agents->count() }}
                    </span>
                </h2>
            </div>
            <a href="{{ route('admin.services.attach-agent.create', $service) }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black text-white shadow-sm transition hover:opacity-90"
               style="background: linear-gradient(to right, #0891b2, #2563eb)">
                <i class="fas fa-user-plus text-[10px]"></i> Affecter un agent
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-100" style="background: linear-gradient(to right, #f8fafc, #f0f9ff)">
                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Agent</th>
                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Matricule</th>
                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400">Fonction</th>
                        <th class="px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($service->agents as $agent)
                        <tr class="hover:bg-cyan-50/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm"
                                         style="background: linear-gradient(135deg, #0891b2, #2563eb)">
                                        {{ strtoupper(substr($agent->prenom,0,1)) }}{{ strtoupper(substr($agent->nom,0,1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $agent->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-lg px-2.5 py-1 text-xs font-bold ring-1"
                                      style="background:#ecfeff; color:#0e7490; ring-color:#cffafe">
                                    {{ $agent->matricule ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">
                                    <i class="fas fa-briefcase text-[8px]"></i>
                                    {{ $agent->poste ?: $agent->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.agents.show', $agent) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-cyan-100 bg-cyan-50 text-cyan-500 transition hover:bg-cyan-100"
                                       title="Voir">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.agents.edit', $agent) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-amber-100 bg-amber-50 text-amber-500 transition hover:bg-amber-100"
                                       title="Modifier">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.services.agents.destroy', [$service, $agent]) }}"
                                          onsubmit="return confirm('Retirer {{ addslashes($agent->prenom.' '.$agent->nom) }} de ce service ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-100 bg-rose-50 text-rose-500 transition hover:bg-rose-100"
                                            title="Retirer du service">
                                            <i class="fas fa-user-minus text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-300 text-2xl mx-auto mb-4">
                                    <i class="fas fa-user-slash"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-400">Aucun agent affecté à ce service.</p>
                                <a href="{{ route('admin.services.attach-agent.create', $service) }}"
                                   class="mt-4 inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-black text-white shadow-sm transition hover:opacity-90"
                                   style="background: linear-gradient(to right, #0891b2, #2563eb)">
                                    <i class="fas fa-user-plus text-[10px]"></i> Affecter un premier agent
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
