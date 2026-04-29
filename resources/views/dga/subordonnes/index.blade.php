@extends('layouts.dga')
@section('title', 'Mes Subordonnés | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DGA</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Mes Subordonnés</h1>
                    <p class="mt-1 text-sm text-slate-500">Directeurs Techniques et secrétaire.</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 shadow-sm">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </header>

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        {{-- Secrétaire du DGA --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <i class="fas fa-user-tie text-sm"></i>
                </span>
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Secrétaire</h2>
            </div>
            @if($secretaire)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600 text-white font-black text-lg shadow">
                                {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ $secretaire->name }}</p>
                                <p class="text-xs text-slate-400">{{ $secretaire->email }}</p>
                                <span class="mt-1 inline-flex items-center rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-700">
                                    {{ str_replace('_', ' ', $secretaire->role) }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('dga.subordonnes.show', $secretaire) }}" class="ent-btn ent-btn-soft py-1.5 px-4 text-xs">
                            <i class="fas fa-folder-open mr-1.5"></i>Dossier
                        </a>
                    </div>
                </div>
            @else
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-slate-400">Aucune secrétaire assignée.</p>
                    <p class="mt-1 text-xs text-slate-300">Configurer via l'administration (entites.dga_secretaire_agent_id).</p>
                </div>
            @endif
        </section>

        {{-- Directeurs Techniques --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-sitemap text-sm"></i>
                </span>
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Directeurs Techniques</h2>
                <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                    {{ $directeursTechniques->count() }}
                </span>
            </div>
            @if($directeursTechniques->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun Directeur Technique enregistré.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($directeursTechniques as $dt)
                        @php
                            $delegation = $dt->agent?->directedDelegation ?? null;
                        @endphp
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-black text-base shadow">
                                    {{ strtoupper(substr($dt->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $dt->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $dt->email }}</p>
                                    @if($delegation)
                                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                            <i class="fas fa-map-marker-alt"></i>
                                            {{ $delegation->region }} / {{ $delegation->ville }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('dga.subordonnes.show', $dt) }}" class="ent-btn ent-btn-soft py-1.5 px-4 text-xs">
                                <i class="fas fa-folder-open mr-1.5"></i>Dossier
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
