@extends('layouts.chef')
@section('title', 'Mes Chefs de Guichet | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-sky-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-200">{{ $ctx->getNom() }} · Mon Agence</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Mes Chefs de Guichet</h1>
                <p class="mt-0.5 text-sm text-blue-100/80">Guichets rattachés à votre agence.</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-cash-register"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Guichets</p>
                        <p class="text-base font-black text-white">{{ $stats['total'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-circle-check"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Avec chef</p>
                        <p class="text-base font-black text-white">{{ $stats['avec_chef'] }}</p>
                    </div>
                </div>
                @if($stats['sans_chef'] > 0)
                <div class="flex items-center gap-2 rounded-2xl border border-white/15 bg-amber-500/30 px-4 py-2.5 backdrop-blur-sm">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15 text-white text-xs"><i class="fas fa-triangle-exclamation"></i></span>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-wider text-white/70">Sans chef</p>
                        <p class="text-base font-black text-white">{{ $stats['sans_chef'] }}</p>
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

        <form method="GET" action="{{ route('chef.guichets') }}"
              class="flex flex-wrap items-end gap-3 rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
            <div class="flex-1 min-w-[200px]">
                <label class="mb-1 block text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Recherche</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Nom du guichet…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-700 outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                @if($filters['search'])
                <a href="{{ route('chef.guichets') }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-500 transition hover:text-slate-700">
                    <i class="fas fa-xmark text-xs"></i> Réinitialiser
                </a>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-[20px] border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center gap-3">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <i class="fas fa-cash-register text-sm"></i>
                </span>
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Guichets</h2>
                <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $stats['total'] }}</span>
            </div>

            @if($guichets->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun guichet trouvé.</p>
                    @if($filters['search'])
                        <p class="mt-1 text-xs text-slate-300">Essayez de modifier votre recherche.</p>
                    @else
                        <p class="mt-1 text-xs text-slate-300">Aucun guichet n'est rattaché à votre agence.</p>
                    @endif
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach($guichets as $row)
                        @php
                            $guichet  = $row['guichet'];
                            $chef     = $row['chef'];
                            $chefUser = $row['chefUser'];
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 transition-colors hover:bg-slate-50/60">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600 shadow-sm">
                                <i class="fas fa-cash-register text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-bold text-slate-900">{{ $guichet->nom ?? 'Guichet #'.$guichet->id }}</p>
                                @if($chef)
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <div class="flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-sky-600 text-white text-[9px] font-black">
                                                {{ strtoupper(substr($chef->prenom ?? $chef->nom ?? '?', 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-semibold text-slate-700">{{ trim(($chef->prenom ?? '').' '.($chef->nom ?? '')) }}</span>
                                        </div>
                                        @if($chefUser)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black text-emerald-700">
                                                <i class="fas fa-circle-check text-[8px]"></i> Compte actif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-black text-amber-700">
                                                <i class="fas fa-circle-exclamation text-[8px]"></i> Sans compte
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 flex flex-wrap gap-3 text-[10px] font-semibold text-slate-400">
                                        <span><i class="fas fa-star-half-stroke mr-1"></i>{{ $row['nbEvals'] }} éval.</span>
                                        <span><i class="fas fa-bullseye mr-1"></i>{{ $row['nbObjectifs'] }} objectifs</span>
                                        @if($row['noteAvg'] !== null)
                                            <span class="font-black text-emerald-600"><i class="fas fa-award mr-1"></i>{{ $row['noteAvg'] }}/20</span>
                                        @endif
                                    </div>
                                @else
                                    <p class="mt-0.5 text-xs text-slate-400 italic">Aucun chef désigné</p>
                                @endif
                            </div>
                            @if($chefUser)
                                <a href="{{ route('chef.agent.show', $chefUser->agent) }}"
                                   class="shrink-0 inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    </div>
</div>
@endsection
