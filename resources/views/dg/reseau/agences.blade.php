@extends('layouts.dg')
@section('title', 'Agences | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] px-4 pb-12 pt-6 lg:px-10">
    <div class="mx-auto max-w-7xl flex flex-col gap-8">

        {{-- Header Premium avec dégradé --}}
        <header class="relative overflow-hidden rounded-[32px] bg-slate-950 p-8 shadow-2xl lg:p-10">
            {{-- Effets de lumière en fond --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-emerald-500/20 blur-[80px]"></div>
            <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-sky-500/10 blur-[60px]"></div>

            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                        Réseau RCPB / Pilotage
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white lg:text-4xl">Agences</h1>
                    <p class="mt-2 text-sm font-medium text-slate-400">
                        Supervision de <span class="text-white font-bold underline decoration-emerald-500 decoration-2 underline-offset-4">{{ $agences->total() }} points de vente</span> actifs.
                    </p>
                </div>
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/5 border border-white/10 text-emerald-400 shadow-inner backdrop-blur-md">
                    <i class="fas fa-landmark text-2xl"></i>
                </div>
            </div>
        </header>

        {{-- Filtres Stylisés --}}
        <form method="GET" class="rounded-[28px] bg-white border border-slate-200/60 p-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Recherche rapide</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Nom d'agence, chef..." 
                            class="w-full rounded-2xl border-none bg-slate-50 pl-11 pr-4 py-3.5 text-sm font-bold text-slate-700 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-500 transition-all">
                    </div>
                </div>
                
                @if ($caisses->isNotEmpty())
                <div class="min-w-[200px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 ml-1">Caisse de rattachement</label>
                    <select name="caisse" class="w-full rounded-2xl border-none bg-slate-50 px-4 py-3.5 text-sm font-bold text-slate-700 ring-1 ring-slate-200 focus:ring-2 focus:ring-emerald-500">
                        <option value="">Toutes les caisses</option>
                        @foreach ($caisses as $c)
                            <option value="{{ $c->id }}" {{ $caisseId == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="flex gap-2">
                    <button type="submit" class="inline-flex h-[52px] items-center gap-2 rounded-2xl bg-slate-900 px-6 text-xs font-black uppercase tracking-widest text-white transition hover:bg-emerald-600 shadow-lg shadow-slate-200 hover:shadow-emerald-200">
                        <i class="fas fa-sliders-h"></i> Filtrer
                    </button>
                    @if ($search || $caisseId)
                        <a href="{{ route('dg.agences') }}" class="inline-flex h-[52px] items-center rounded-2xl bg-slate-100 px-6 text-xs font-black uppercase tracking-widest text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table avec design "Card List" --}}
        <section class="overflow-hidden rounded-[32px] border border-slate-200/60 bg-white shadow-xl shadow-slate-200/40">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-8 py-5">
                <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Répertoire des Agences</h2>
                <span class="rounded-lg bg-emerald-100 px-3 py-1 text-[10px] font-black text-emerald-700">LIVE DATA</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Agence</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Superviseur</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-center">Ressources</th>
                            <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($agences as $agence)
                            <tr class="group transition-all hover:bg-emerald-50/30">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900 text-emerald-400 font-black shadow-lg transition-transform group-hover:scale-110 group-hover:bg-emerald-600 group-hover:text-white">
                                            {{ substr($agence->nom, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-base font-black text-slate-900 group-hover:text-emerald-700 transition-colors">{{ $agence->nom }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Point de vente RCPB</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="inline-flex w-fit items-center rounded-md bg-blue-50 px-2 py-1 text-[10px] font-bold text-blue-600 ring-1 ring-blue-100 mb-1 italic">
                                            {{ $agence->caisse?->nom ?? '—' }}
                                        </span>
                                        @if($agence->chef)
                                            <p class="text-sm font-bold text-slate-700">{{ $agence->chef->prenom }} {{ $agence->chef->nom }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center gap-3">
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-black text-slate-900">{{ $agence->agents_count }}</span>
                                            <span class="text-[9px] font-bold uppercase text-slate-400">Agents</span>
                                        </div>
                                        <div class="h-8 w-[1px] bg-slate-100"></div>
                                        <div class="flex flex-col items-center">
                                            <span class="text-xs font-black text-violet-600">{{ $agence->guichets_count }}</span>
                                            <span class="text-[9px] font-bold uppercase text-slate-400">Guichets</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('dg.agences.show', $agence) }}" 
                                       class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-white transition hover:bg-emerald-600 shadow-md hover:shadow-emerald-100">
                                        Fiche complète <i class="fas fa-arrow-right text-[8px]"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if ($agences->hasPages())
                <div class="bg-slate-50/50 px-8 py-6 border-t border-slate-100">
                    {{ $agences->withQueryString()->links() }}
                </div>
            @endif
        </section>

    </div>
</div>
@endsection