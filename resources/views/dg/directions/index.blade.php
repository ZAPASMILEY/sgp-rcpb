@extends('layouts.dg')

@section('title', 'Mes Directeurs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- ── Hero Banner ────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 px-6 py-10 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-emerald-400/10 blur-2xl"></div>
        <div class="pointer-events-none absolute right-1/4 top-0 h-32 w-32 rounded-full bg-teal-300/5 blur-2xl"></div>

        <div class="relative flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-slate-400">Espace DG · Pilotage</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-white">Mes Directeurs</h1>
                <p class="mt-1 text-sm text-slate-400">Assignez des objectifs et évaluez les directeurs de la faîtière.</p>
            </div>
            <div class="flex items-center gap-3 mt-3 sm:mt-0">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                    <i class="fas fa-sitemap text-2xl text-white"></i>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-black text-white">{{ $directions->count() }}</p>
                    <p class="text-xs font-bold text-slate-400">direction(s)</p>
                </div>
            </div>
        </div>

        {{-- Quick stats strip --}}
        <div class="relative mt-6 flex flex-wrap gap-3">
            <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 ring-1 ring-white/15">
                <i class="fas fa-user-tie text-xs text-emerald-300"></i>
                <span class="text-xs font-bold text-white">{{ $directions->filter(fn($d) => $d->directeur)->count() }} directeur(s) affecté(s)</span>
            </div>
            <div class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 ring-1 ring-white/15">
                <i class="fas fa-layer-group text-xs text-sky-300"></i>
                <span class="text-xs font-bold text-white">{{ $directions->sum(fn($d) => $d->services->count()) }} service(s) au total</span>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span class="text-sm font-semibold text-emerald-700">{{ session('status') }}</span>
            </div>
        @endif

        @if ($directions->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center shadow-sm">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-300">
                    <i class="fas fa-sitemap text-3xl"></i>
                </div>
                <p class="mt-4 text-base font-black text-slate-700">Aucune direction enregistrée</p>
                <p class="mt-1 text-sm text-slate-400">Les directions apparaîtront ici une fois créées par l'administrateur.</p>
            </div>
        @else
            @php
            $palettes = [
                ['from' => 'from-emerald-500', 'to' => 'to-teal-500',   'text' => 'text-emerald-600',  'border' => 'border-emerald-200', 'hover' => 'hover:border-emerald-400', 'badge' => 'bg-emerald-100 text-emerald-700', 'icon_bg' => 'bg-emerald-100', 'icon_text' => 'text-emerald-600'],
                ['from' => 'from-blue-500',    'to' => 'to-indigo-500',  'text' => 'text-blue-600',    'border' => 'border-blue-200',   'hover' => 'hover:border-blue-400',   'badge' => 'bg-blue-100 text-blue-700',   'icon_bg' => 'bg-blue-100',   'icon_text' => 'text-blue-600'],
                ['from' => 'from-violet-500',  'to' => 'to-purple-500',  'text' => 'text-violet-600',  'border' => 'border-violet-200', 'hover' => 'hover:border-violet-400', 'badge' => 'bg-violet-100 text-violet-700', 'icon_bg' => 'bg-violet-100', 'icon_text' => 'text-violet-600'],
                ['from' => 'from-amber-500',   'to' => 'to-orange-500',  'text' => 'text-amber-600',   'border' => 'border-amber-200',  'hover' => 'hover:border-amber-400',  'badge' => 'bg-amber-100 text-amber-700',  'icon_bg' => 'bg-amber-100',  'icon_text' => 'text-amber-600'],
                ['from' => 'from-rose-500',    'to' => 'to-pink-500',    'text' => 'text-rose-600',    'border' => 'border-rose-200',   'hover' => 'hover:border-rose-400',   'badge' => 'bg-rose-100 text-rose-700',   'icon_bg' => 'bg-rose-100',   'icon_text' => 'text-rose-600'],
                ['from' => 'from-sky-500',     'to' => 'to-cyan-500',    'text' => 'text-sky-600',     'border' => 'border-sky-200',    'hover' => 'hover:border-sky-400',    'badge' => 'bg-sky-100 text-sky-700',     'icon_bg' => 'bg-sky-100',    'icon_text' => 'text-sky-600'],
                ['from' => 'from-teal-500',    'to' => 'to-emerald-600', 'text' => 'text-teal-600',    'border' => 'border-teal-200',   'hover' => 'hover:border-teal-400',   'badge' => 'bg-teal-100 text-teal-700',   'icon_bg' => 'bg-teal-100',   'icon_text' => 'text-teal-600'],
            ];
            @endphp

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($directions as $i => $direction)
                    @php
                        $p = $palettes[$i % count($palettes)];
                        $directeur = $direction->directeur;
                        $directeurNom = $directeur ? trim($directeur->prenom.' '.$directeur->nom) : null;
                        $initiales = $directeurNom ? collect(explode(' ', $directeurNom))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('') : '—';
                        $nbServices = $direction->services->count();
                    @endphp
                    <a href="{{ route('dg.directions.show', $direction) }}"
                       class="group relative flex flex-col overflow-hidden rounded-2xl border bg-white shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 {{ $p['border'] }} {{ $p['hover'] }}">

                        {{-- Barre de couleur --}}
                        <div class="h-1 w-full bg-gradient-to-r {{ $p['from'] }} {{ $p['to'] }}"></div>

                        <div class="flex flex-1 flex-col gap-2 p-3">

                            {{-- Header --}}
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $p['icon_bg'] }} {{ $p['icon_text'] }} transition group-hover:scale-105">
                                    <i class="fas fa-building text-xs"></i>
                                </div>
                                <span class="rounded-full {{ $p['badge'] }} px-2 py-0.5 text-[10px] font-black uppercase tracking-wider">
                                    {{ $nbServices }} svc
                                </span>
                            </div>

                            {{-- Nom direction --}}
                            <h3 class="text-sm font-black leading-snug text-slate-900 transition-colors group-hover:{{ $p['text'] }}">
                                {{ $direction->nom }}
                            </h3>

                            {{-- Directeur --}}
                            @if ($directeurNom)
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gradient-to-br {{ $p['from'] }} {{ $p['to'] }} text-[9px] font-black text-white">
                                        {{ $initiales }}
                                    </div>
                                    <p class="truncate text-xs font-semibold text-slate-600">{{ $directeurNom }}</p>
                                </div>
                            @else
                                <p class="text-xs italic text-slate-400">Non affecté</p>
                            @endif

                            {{-- Footer --}}
                            <div class="flex items-center justify-end border-t border-slate-100 pt-2 mt-auto">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full {{ $p['icon_bg'] }} transition group-hover:scale-110">
                                    <i class="fas fa-arrow-right text-[9px] {{ $p['icon_text'] }}"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
