@extends('layouts.app')

@section('title', 'Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f0f3f8] p-4 lg:p-10 font-sans selection:bg-teal-100">
    <div class="max-w-6xl mx-auto">
        
        @if (session('status'))
            <div id="status-message" class="mb-8 p-5 bg-white border-l-4 border-teal-500 rounded-2xl shadow-xl shadow-teal-100/50 flex items-center gap-4 animate-bounce">
                <span class="text-2xl">✅</span>
                <p class="font-black text-slate-700 uppercase tracking-tighter text-sm">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 5000);</script>
        @endif

        @if ($entite)
            <div class="bg-white/90 backdrop-blur-xl rounded-[4rem] p-10 lg:p-16 shadow-[0_40px_80px_rgba(150,170,220,0.18)] border-t-[1.2rem] border-l-[0.8rem] border-white relative overflow-hidden group mb-16">
                
                <div class="absolute -right-16 -bottom-10 text-[18rem] opacity-[0.03] pointer-events-none group-hover:rotate-6 transition-transform duration-700">🏢</div>

                <div class="relative z-10">
                    <div class="flex flex-wrap gap-3 mb-10">
                        <span class="px-5 py-2 bg-[#f0f3f8] text-[9px] font-black uppercase text-teal-600 rounded-full shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] border border-white tracking-[0.2em]">Siège Unique</span>
                        <span class="px-5 py-2 bg-[#f0f3f8] text-[9px] font-black uppercase text-slate-500 rounded-full shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] border border-white tracking-[0.2em]">{{ $entite->ville }}</span>
                        <span class="px-5 py-2 bg-[#f0f3f8] text-[9px] font-black uppercase text-slate-400 rounded-full shadow-[inset_0_2px_4px_rgba(0,0,0,0.05)] border border-white tracking-[0.2em]">{{ $entite->region }}</span>
                    </div>

                    <div class="mb-12">
                        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 mb-2 italic">Administration Centrale</p>
                        <h1 class="text-6xl font-black text-slate-900 tracking-tighter leading-none mb-4">La Faîtière</h1>
                        <p class="text-lg font-medium text-slate-400 italic">Pilotage global et centralisation des directions et agents</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                        @php
                            $dirigeants = [
                                ['label' => 'DG', 'nom' => $entite->directrice_generale_prenom . ' ' . $entite->directrice_generale_nom],
                                ['label' => 'DGA', 'nom' => $entite->dga_prenom . ' ' . $entite->dga_nom],
                                ['label' => 'PCA', 'nom' => $entite->pca_prenom . ' ' . $entite->pca_nom]
                            ];
                        @endphp
                        @foreach($dirigeants as $d)
                            <div class="p-5 bg-white rounded-3xl shadow-[0_10px_25px_rgba(0,0,0,0.03)] border border-slate-50 flex flex-col group/item hover:-translate-y-1 transition-all">
                                <span class="text-[9px] font-black text-teal-500 uppercase tracking-widest mb-1">{{ $d['label'] }}</span>
                                <span class="text-sm font-black text-slate-800 tracking-tight">{{ $d['nom'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap gap-4 pt-8 border-t border-slate-50">
                        <a href="{{ route('admin.entites.edit', $entite) }}" class="px-10 py-4 bg-teal-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-teal-100 hover:bg-teal-700 hover:-translate-y-1 transition-all">Modifier</a>
                        
                        <a href="{{ route('admin.entites.show', $entite) }}" class="px-10 py-4 bg-white rounded-2xl text-[10px] font-black uppercase tracking-widest border border-slate-100 shadow-lg shadow-black/5 hover:bg-slate-50 transition-all text-slate-600">Fiche complète</a>

                        <form method="POST" action="{{ route('admin.entites.reset') }}" class="inline">
                            @csrf
                            <button class="px-10 py-4 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase tracking-widest border border-rose-100 hover:bg-rose-100 transition-all shadow-lg shadow-rose-50">Réinitialiser</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-20">
                @php
                    $kpiList = [
                        ['label' => 'Directions', 'val' => $stats['directions'], 'desc' => 'Unités de pilotage', 'route_add' => 'admin.entites.directions.create', 'route_index' => 'admin.entites.directions.index'],
                        ['label' => 'Services', 'val' => $stats['services'], 'desc' => 'Supports techniques', 'route_add' => 'admin.services.create', 'route_index' => 'admin.services.index'],
                        ['label' => 'Agents', 'val' => $stats['agents'], 'desc' => 'Effectif actif', 'route_add' => 'admin.agents.create', 'route_index' => 'admin.agents.index'],
                    ];
                @endphp

                @foreach($kpiList as $kpi)
                <div class="flex flex-col items-center">
                    <div class="w-64 h-64 bg-[#f0f3f8] rounded-full shadow-[20px_20px_60px_#d9dde1,-20px_-20px_60px_#ffffff] border-[10px] border-[#f0f3f8] flex flex-col items-center justify-center relative group hover:shadow-xl transition-all duration-500">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-2">{{ $kpi['label'] }}</span>
                        <span class="text-6xl font-black text-slate-900 tracking-tighter group-hover:scale-110 transition-transform">{{ $kpi['val'] }}</span>
                        <span class="text-[9px] font-bold text-slate-400 italic mt-2">{{ $kpi['desc'] }}</span>
                    </div>

                    <div class="flex gap-2 mt-8 w-full max-w-[240px]">
                        <a href="{{ route($kpi['route_add']) }}" class="flex-1 py-3 bg-teal-600 text-white rounded-xl text-[8px] font-black uppercase tracking-widest text-center shadow-lg shadow-teal-100 hover:scale-105 transition-all">+ Ajouter</a>
                        <a href="{{ route($kpi['route_index']) }}" class="flex-1 py-3 bg-white text-slate-500 rounded-xl text-[8px] font-black uppercase tracking-widest text-center border border-slate-100 shadow-md hover:bg-slate-50 transition-all">Voir tout</a>
                    </div>
                </div>
                @endforeach
            </div>

        @else
            <div class="bg-white/70 backdrop-blur-xl rounded-[4rem] p-20 text-center border border-white shadow-2xl">
                <div class="text-8xl mb-8 opacity-20">🏢</div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tighter uppercase mb-6 italic">Aucune faîtière enregistrée</h2>
                <a href="{{ route('admin.entites.create') }}" class="px-12 py-5 bg-teal-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-2xl shadow-teal-200 hover:bg-teal-700 transition-all">Créer la faîtière maintenant</a>
            </div>
        @endif
    </div>
</div>
@endsection