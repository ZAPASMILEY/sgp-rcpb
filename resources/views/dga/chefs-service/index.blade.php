@extends('layouts.dga')
@section('title', 'Chefs de Service | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">Espace DGA · Ma Direction</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Chefs de Service</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">{{ $direction?->nom ?? 'Direction Générale Adjointe' }}</p>
            </div>
            {{-- Mini KPIs Hero --}}
            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-users"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Total</p>
                        <p class="text-base font-black text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-circle-check"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Avec compte</p>
                        <p class="text-base font-black text-white">{{ $stats['actifs'] }}</p>
                    </div>
                </div>
                @if($stats['sans_compte'] > 0)
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-amber-500/30 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Sans compte</p>
                        <p class="text-base font-black text-white">{{ $stats['sans_compte'] }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif

        {{-- Filtres --}}
        <form method="GET" action="{{ route('dga.chefs-service.index') }}"
              class="flex flex-wrap items-end gap-3 rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
            <div class="flex-1 min-w-[200px]">
                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Recherche</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Nom, prénom, matricule…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-violet-700">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                @if($filters['search'])
                <a href="{{ route('dga.chefs-service.index') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-500 transition hover:text-slate-700">
                    <i class="fas fa-xmark text-xs"></i> Réinitialiser
                </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-[20px] border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <i class="fas fa-user-tie text-sm"></i>
                </span>
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste</h2>
                <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                    {{ $chefsService->total() }}
                </span>
            </div>

            @if($chefsService->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun chef de service trouvé.</p>
                    @if($filters['search'])
                        <p class="mt-1 text-xs text-slate-300">Essayez de modifier vos filtres.</p>
                    @endif
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach($chefsService as $chef)
                        <div class="flex items-center gap-4 px-6 py-4 transition-colors hover:bg-slate-50/60">
                            {{-- Avatar --}}
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl
                                        {{ $chef->user ? 'bg-gradient-to-br from-violet-500 to-purple-600' : 'bg-slate-100' }}
                                        font-black text-base shadow
                                        {{ $chef->user ? 'text-white' : 'text-slate-400' }}">
                                {{ strtoupper(substr($chef->prenom ?? $chef->nom ?? '?', 0, 1)) }}
                            </div>
                            {{-- Infos --}}
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-bold text-slate-900">
                                    {{ trim(($chef->prenom ?? '').' '.($chef->nom ?? '')) ?: '—' }}
                                </p>
                                <div class="mt-0.5 flex flex-wrap items-center gap-2">
                                    @if($chef->matricule)
                                        <span class="text-[10px] font-semibold text-slate-400">{{ $chef->matricule }}</span>
                                    @endif
                                    @if($chef->service)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                            <i class="fas fa-building text-[8px]"></i>{{ $chef->service->nom }}
                                        </span>
                                    @endif
                                    @if($chef->direction)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold text-violet-700">
                                            <i class="fas fa-sitemap text-[8px]"></i>{{ $chef->direction->nom }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            {{-- Compte --}}
                            <div class="shrink-0 text-right">
                                @if($chef->user)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black text-emerald-700">
                                        <i class="fas fa-circle-check text-[8px]"></i> Compte actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black text-amber-700">
                                        <i class="fas fa-circle-exclamation text-[8px]"></i> Sans compte
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($chefsService->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $chefsService->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
    </div>
</div>
@endsection
