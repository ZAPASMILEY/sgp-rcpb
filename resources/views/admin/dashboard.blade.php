@extends('layouts.app')

@section('title', 'Tableau de bord | SGP-RCPB')

{{-- On vide le titre du layout pour supprimer "Aperçu" et gagner de la place --}}
@section('page_title', '')

@section('content')
@php
    $calendarStart = now()->startOfMonth()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
    $calendarEnd = now()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SUNDAY);
    $calendarDays = [];
    for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) { $calendarDays[] = $date->copy(); }

    $calendarHighlights = collect([now()->day, min(now()->endOfMonth()->day, now()->copy()->addDays(6)->day)])->unique();

    $adminKpis = [
        ['label' => 'Directions', 'value' => $faitiereDirectionsCount, 'meta' => 'Directions Faîtières', 'href' => route('admin.directions.index'), 'tone' => 'from-[#22d3ee] to-[#3b82f6]', 'icon' => 'fas fa-sitemap'],
        ['label' => 'Services', 'value' => $servicesCount, 'meta' => $servicesWithoutDirection.' sans direction', 'href' => route('admin.services.index'), 'tone' => 'from-[#34d399] to-[#10b981]', 'icon' => 'fas fa-layer-group'],
        ['label' => 'Agents', 'value' => $agentsCount, 'meta' => $agentsWithoutService.' sans service', 'href' => route('admin.agents.index'), 'tone' => 'from-[#fb923c] to-[#f59e0b]', 'icon' => 'fas fa-users'],
        ['label' => 'Alertes', 'value' => $failedLoginAttemptsToday, 'meta' => 'Tentatives échouées', 'href' => '#', 'tone' => 'from-[#f87171] to-[#ef4444]', 'icon' => 'fas fa-exclamation-triangle'],
    ];
@endphp

{{-- -mt-20 fait remonter tout le bloc au niveau du header --}}
<div class="px-4 pb-8 lg:px-8 -mt-20 relative z-10">
    <div class="mx-auto max-w-[1600px] space-y-6">
        
        {{-- Header interne du Dashboard --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-4">
            <div class="flex-1">
                <h1 class="text-3xl font-black text-slate-800 tracking-tighter">Tableau de bord Administration</h1>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Gestion centrale SGP-RCPB • {{ now()->translatedFormat('l d F Y') }}</p>
            </div>
            
            <div class="flex items-center gap-3 bg-white p-2 rounded-[24px] shadow-sm border border-slate-100 flex-1 max-w-xl">
                <div class="flex-1 flex items-center px-4 gap-3">
                    <i class="fas fa-search text-slate-300"></i>
                    <input type="text" class="w-full py-2 bg-transparent border-none focus:ring-0 text-sm text-slate-600 placeholder-slate-300" placeholder="Rechercher une entité...">
                </div>
                <button class="bg-cyan-500 text-white h-10 w-10 rounded-xl shadow-lg shadow-cyan-100 flex items-center justify-center transition hover:scale-105">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @foreach ($adminKpis as $kpi)
                <div class="relative overflow-hidden rounded-[32px] p-6 text-white shadow-xl shadow-slate-200/50 bg-gradient-to-br {{ $kpi['tone'] }}">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-4xl font-black">{{ $kpi['value'] }}</p>
                            <p class="text-[10px] font-black opacity-80 mt-1 uppercase tracking-widest">{{ $kpi['label'] }}</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-2xl backdrop-blur-md">
                            <i class="{{ $kpi['icon'] }} text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-between">
                        <span class="text-[10px] font-black opacity-70 uppercase tracking-tight">{{ $kpi['meta'] }}</span>
                        <i class="fas fa-arrow-right text-[10px] opacity-40"></i>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-12 gap-8">
            {{-- Liste Directions --}}
            <div class="col-span-12 xl:col-span-8 space-y-6">
                <div class="bg-white rounded-[35px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-lg font-black text-slate-800 tracking-tight italic">Dernières Directions</h3>
                        <a href="{{ route('admin.directions.index') }}" class="text-xs font-black text-cyan-500 uppercase tracking-widest">Voir tout</a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-slate-300 text-[10px] font-black uppercase tracking-[0.2em] border-b border-slate-50">
                                    <th class="pb-4">Direction</th>
                                    <th class="pb-4">Ville</th>
                                    <th class="pb-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm">
                                @foreach ($recentDirections->take(5) as $direction)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td class="py-4">
                                        <p class="font-black text-slate-700">{{ $direction->nom }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">{{ $direction->directeur_nom ?: 'Sans directeur' }}</p>
                                    </td>
                                    <td class="py-4 text-xs font-black text-slate-400 uppercase tracking-widest">
                                        {{ $direction->delegationTechnique?->ville ?? 'Faîtière' }}
                                    </td>
                                    <td class="py-4">
                                        <span class="bg-emerald-50 text-emerald-500 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">Actif</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sidebar Dashboard --}}
            <div class="col-span-12 xl:col-span-4 space-y-8">
                {{-- Calendrier Épuré --}}
                <div class="bg-white rounded-[35px] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-black text-slate-800 mb-6 italic">{{ now()->translatedFormat('F Y') }}</h3>
                    <div class="grid grid-cols-7 gap-1 text-center mb-4">
                        @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $weekday)
                            <span class="text-[10px] font-black text-slate-300 uppercase">{{ $weekday }}</span>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-2">
                        @foreach ($calendarDays as $day)
                            <div class="h-8 flex items-center justify-center text-[11px] font-black rounded-xl
                                {{ $day->isToday() ? 'bg-cyan-500 text-white shadow-lg shadow-cyan-100' : ($day->month === now()->month ? 'text-slate-600 hover:bg-slate-50' : 'text-slate-200') }}">
                                {{ $day->day }}
                            </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Sécurité --}}
                <div class="bg-slate-900 rounded-[35px] p-8 text-white shadow-xl shadow-slate-200">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-10 w-10 rounded-xl bg-rose-500 flex items-center justify-center shadow-lg shadow-rose-500/20">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </div>
                        <h3 class="text-base font-black italic">Journal de Sécurité</h3>
                    </div>
                    <p class="text-3xl font-black tracking-tighter">{{ $failedLoginAttemptsCount }}</p>
                    <p class="text-[10px] font-bold opacity-40 uppercase tracking-[0.2em] mt-1">Alertes suspectes</p>
                    <button class="w-full mt-6 py-3 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/20 transition-all">Analyser les Logs</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection