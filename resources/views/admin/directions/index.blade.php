@extends('layouts.app')

@section('title', 'Délégation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="max-w-[1600px] mx-auto space-y-8">

        {{-- HEADER : Titre & Actions --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Délégations Techniques</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Référentiel</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Pilotage technique</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.delegations-techniques.directeurs.index') }}" class="h-12 px-6 bg-white border border-slate-200 text-slate-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
                    <i class="fas fa-user-tie text-slate-400"></i> Voir les Directeurs
                </a>
                <a href="{{ route('admin.directions.create') }}" data-open-modal data-title="Ajouter un Directeur Technique" class="h-12 px-6 bg-cyan-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-2 hover:bg-cyan-600 transition-all shadow-lg shadow-cyan-100">
                    <i class="fas fa-plus"></i> Nouveau Directeur
                </a>
            </div>
        </div>

        {{-- SECTION KPI (Style SaaS) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
                $stats_kpi = [
                    ['label' => 'Délégations', 'val' => $delegations->count(), 'icon' => 'fas fa-map-marked-alt', 'grad' => 'from-cyan-400 to-blue-500', 'meta' => 'Sur 3 autorisées'],
                    ['label' => 'Directeurs T.', 'val' => $directionsCount, 'icon' => 'fas fa-user-shield', 'grad' => 'from-emerald-400 to-teal-500', 'meta' => 'Personnel cadre'],
                    ['label' => 'Services', 'val' => $servicesCount, 'icon' => 'fas fa-layer-group', 'grad' => 'from-orange-400 to-amber-500', 'meta' => 'Unités techniques'],
                ];
            @endphp

            @foreach($stats_kpi as $k)
            <div class="relative overflow-hidden rounded-[32px] p-8 text-white shadow-xl shadow-slate-200/50 bg-gradient-to-br {{ $k['grad'] }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-4xl font-black tracking-tighter">{{ $k['val'] }}</p>
                        <p class="text-xs font-bold opacity-80 mt-1 uppercase tracking-widest">{{ $k['label'] }}</p>
                    </div>
                    <div class="bg-white/20 h-12 w-12 rounded-2xl flex items-center justify-center backdrop-blur-md">
                        <i class="{{ $k['icon'] }} text-xl"></i>
                    </div>
                </div>
                <p class="mt-6 text-[10px] font-bold opacity-70 uppercase tracking-widest">{{ $k['meta'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-12 gap-8">
            
            {{-- COLONNE GAUCHE (8/12) : Liste des Délégations --}}
            <div class="col-span-12 lg:col-span-8 space-y-8">
                
                <div class="bg-white rounded-[35px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-bold text-slate-800">Structures de Coordination</h3>
                        <span class="px-3 py-1 bg-slate-50 text-slate-400 text-[10px] font-black rounded-full uppercase tracking-widest border border-slate-100">
                            {{ $delegations->count() }} / 3 Active
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse ($delegations as $delegation)
                        <div class="group flex flex-col sm:flex-row sm:items-center justify-between p-6 rounded-[24px] border border-slate-50 bg-slate-50/30 hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all duration-300">
                            <div class="flex items-center gap-5">
                                <div class="h-14 w-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-cyan-500 text-xl font-black group-hover:bg-cyan-500 group-hover:text-white transition-all">
                                    {{ substr($delegation->ville, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-800 text-lg tracking-tight">{{ $delegation->region }} <span class="text-cyan-500 mx-1">/</span> {{ $delegation->ville }}</p>
                                    <p class="text-xs text-slate-400 font-medium mt-1">Secrétariat : <span class="text-slate-600">{{ $delegation->secretariat_telephone }}</span></p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2 mt-4 sm:mt-0">
                                <a href="{{ route('admin.delegations-techniques.edit', $delegation) }}" class="h-10 w-10 bg-white border border-slate-100 text-slate-400 rounded-xl flex items-center justify-center hover:text-cyan-500 transition-colors shadow-sm">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <div class="h-10 w-px bg-slate-100 mx-1"></div>
                                <a href="{{ route('admin.delegations-techniques.directeurs.index', ['delegation_id' => $delegation->id]) }}" class="px-4 py-2 bg-white text-slate-600 text-[10px] font-black uppercase rounded-xl border border-slate-100 hover:bg-slate-50 shadow-sm">Directions</a>
                                <a href="{{ route('admin.delegations-techniques.services.index', ['delegation_id' => $delegation->id]) }}" class="px-4 py-2 bg-white text-slate-600 text-[10px] font-black uppercase rounded-xl border border-slate-100 hover:bg-slate-50 shadow-sm">Services</a>
                            </div>
                        </div>
                        @empty
                        <div class="py-12 text-center">
                            <i class="fas fa-map-marker-slash text-4xl text-slate-200 mb-4"></i>
                            <p class="text-slate-400 font-medium italic">Aucune délégation configurée pour le moment.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- SERVICES RÉCENTS (Horizontal Scroll ou Grid) --}}
                <div class="bg-white rounded-[35px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-xs font-black text-slate-300 uppercase tracking-[0.2em] mb-6">Derniers Services Techniques</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($recentServices->take(4) as $service)
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-50 group hover:bg-white hover:border-cyan-100 transition-all">
                            <div class="h-10 w-10 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center group-hover:bg-cyan-500 group-hover:text-white transition-all">
                                <i class="fas fa-cog text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-slate-700 truncate">{{ $service->nom }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase truncate">{{ $service->direction?->nom ?? 'Standard' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- COLONNE DROITE (4/12) --}}
            <div class="col-span-12 xl:col-span-4 space-y-8">
                
                {{-- FORMULAIRE AJOUT RAPIDE --}}
                @if ($delegations->count() < 3)
                <div class="bg-slate-900 rounded-[35px] p-8 text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center text-emerald-400">
                            <i class="fas fa-plus-circle text-xl"></i>
                        </div>
                        <h3 class="text-lg font-black italic">Nouvelle Délégation</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.delegations-techniques.store') }}" class="space-y-5">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest opacity-50">Région</label>
                            <input name="region" type="text" placeholder="Ex: Centre" class="w-full bg-white/5 border-none rounded-2xl p-4 text-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest opacity-50">Ville</label>
                            <input name="ville" type="text" placeholder="Ex: Ouagadougou" class="w-full bg-white/5 border-none rounded-2xl p-4 text-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest opacity-50">Secrétariat</label>
                            <input name="secretariat_telephone" type="text" placeholder="+226 25 XX XX XX" class="w-full bg-white/5 border-none rounded-2xl p-4 text-white text-sm focus:ring-2 focus:ring-emerald-500" required>
                        </div>
                        <button type="submit" class="w-full py-4 bg-emerald-500 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-500/20">
                            Activer la Délégation
                        </button>
                    </form>
                </div>
                @else
                <div class="bg-emerald-500 rounded-[35px] p-8 text-white text-center">
                    <div class="h-16 w-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-double text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-black italic">Configuration Complète</h3>
                    <p class="text-xs opacity-80 mt-2">Le quota de 3 délégations techniques a été atteint.</p>
                </div>
                @endif

                {{-- AGENTS RÉCENTS --}}
                <div class="bg-white rounded-[35px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xs font-black text-slate-300 uppercase tracking-[0.2em]">Agents Techniques</h3>
                        <span class="h-6 w-6 rounded-lg bg-slate-50 flex items-center justify-center text-[10px] font-black text-slate-400 border border-slate-100">{{ $agentsCount }}</span>
                    </div>

                    <div class="space-y-5">
                        @foreach ($recentAgents->take(5) as $agent)
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 text-xs font-black">
                                {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                <p class="text-[10px] text-slate-400 font-medium uppercase">{{ $agent->service?->nom ?? 'Agent de terrain' }}</p>
                            </div>
                            <div class="h-2 w-2 rounded-full bg-emerald-400"></div>
                        </div>
                        @endforeach
                    </div>
                    
                    <a href="{{ route('admin.delegations-techniques.agents.index') }}" class="mt-8 w-full block py-3 text-center text-[10px] font-black uppercase text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl hover:bg-slate-50 hover:border-slate-200 transition-all">
                        Gérer le personnel
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection