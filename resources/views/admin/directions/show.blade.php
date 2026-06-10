@extends('layouts.app')

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $isFaitiere = is_null($direction->delegation_technique_id ?? null);
    $directeur  = $direction->directeur;
    $secretaire = $direction->secretaire;
@endphp
<main class="admin-shell min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
<div class="w-full flex flex-col gap-5">

    {{-- ── HEADER ───────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">
                {{ $isFaitiere ? 'Faîtière · Direction' : 'Délégation · Direction' }}
            </p>
            <h1 class="mt-0.5 text-xl font-black text-slate-900">{{ $direction->nom }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft text-xs py-1.5 px-3">Retour</a>
            <a href="{{ route('admin.directions.edit', $direction) }}" class="ent-btn ent-btn-primary text-xs py-1.5 px-3">
                <i class="fas fa-pen mr-1"></i> Modifier
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- ── RESPONSABLES ─────────────────────────────────────────────────────── --}}
    <div class="grid gap-3 sm:grid-cols-2">

        {{-- Directeur --}}
        <div class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
            <div class="relative shrink-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-base font-black text-white shadow shadow-emerald-100">
                    @if($directeur)
                        {{ strtoupper(substr($directeur->prenom ?? '', 0, 1)) }}{{ strtoupper(substr($directeur->nom ?? '', 0, 1)) }}
                    @else
                        <i class="fas fa-user-tie text-sm"></i>
                    @endif
                </div>
                @if($directeur)
                <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-emerald-500 ring-1 ring-white">
                    <i class="fas fa-check text-[6px] text-white"></i>
                </span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600">
                    {{ $isFaitiere ? 'Directeur de Direction' : 'Directeur Technique' }}
                </p>
                @if($directeur)
                    <p class="mt-0.5 text-sm font-black text-slate-900 truncate">{{ trim($directeur->prenom . ' ' . $directeur->nom) }}</p>
                    @if($directeur->email)
                        <p class="mt-0.5 text-xs text-emerald-600 truncate">{{ $directeur->email }}</p>
                    @endif
                    @if($directeur->numero_telephone)
                        <p class="text-xs text-slate-400">{{ $directeur->numero_telephone }}</p>
                    @endif
                @else
                    <p class="mt-0.5 text-sm italic text-slate-400">Non renseigné</p>
                @endif
            </div>
        </div>

        {{-- Secrétaire --}}
        <div class="flex items-center gap-4 rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm">
            <div class="relative shrink-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-slate-500 to-slate-700 text-base font-black text-white shadow shadow-slate-100">
                    @if($secretaire)
                        {{ strtoupper(substr($secretaire->prenom ?? '', 0, 1)) }}{{ strtoupper(substr($secretaire->nom ?? '', 0, 1)) }}
                    @else
                        <i class="fas fa-user-clock text-sm"></i>
                    @endif
                </div>
                @if($secretaire)
                <span class="absolute -bottom-0.5 -right-0.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-slate-500 ring-1 ring-white">
                    <i class="fas fa-check text-[6px] text-white"></i>
                </span>
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-black uppercase tracking-wider text-sky-600">Secrétaire de Direction</p>
                @if($secretaire)
                    <p class="mt-0.5 text-sm font-black text-slate-900 truncate">{{ trim($secretaire->prenom . ' ' . $secretaire->nom) }}</p>
                    @if($secretaire->email)
                        <p class="mt-0.5 text-xs text-sky-500 truncate">{{ $secretaire->email }}</p>
                    @endif
                    @if($secretaire->numero_telephone)
                        <p class="text-xs text-slate-400">{{ $secretaire->numero_telephone }}</p>
                    @endif
                @else
                    <p class="mt-0.5 text-sm italic text-slate-400">Non renseignée</p>
                @endif
            </div>
        </div>

    </div>

    {{-- ── STATS ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                <i class="fas fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-emerald-600">{{ $agents->total() }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $selectedService ? 'Filtrés' : 'Total agents' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                <i class="fas fa-sitemap text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-sky-600">{{ $services->count() }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Services</p>
            </div>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                <i class="fas fa-layer-group text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-violet-600">{{ $direction->agents()->whereNotNull('service_id')->count() }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dans les services</p>
            </div>
        </div>
    </div>

    {{-- ── TABLEAU AGENTS ───────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Personnel de la Direction</h2>
                <p class="text-xs text-slate-400 mt-0.5">Agents rattachés via un service</p>
            </div>
            <form method="GET" action="{{ route('admin.directions.show', $direction) }}" class="flex items-center gap-2">
                <select name="service_id" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm focus:border-emerald-400 focus:outline-none">
                    <option value="">— Tous les services —</option>
                    @foreach ($services as $svc)
                        <option value="{{ $svc->id }}" @selected($selectedService == $svc->id)>{{ $svc->nom }}</option>
                    @endforeach
                </select>
                @if ($selectedService)
                    <a href="{{ route('admin.directions.show', $direction) }}"
                       class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-rose-500 transition">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </form>
        </div>

        @if ($agents->isEmpty())
            <div class="flex flex-col items-center py-12 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <p class="text-sm italic text-slate-400">
                    {{ $selectedService ? 'Aucun agent dans ce service.' : 'Aucun agent rattaché.' }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400 w-8">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Nom & Prénom</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Fonction</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Service</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Email</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($agents as $agent)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-xs text-slate-300">
                                {{ ($agents->currentPage() - 1) * $agents->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 text-xs font-black">
                                        {{ strtoupper(substr($agent->prenom ?? '', 0, 1) . substr($agent->nom ?? '', 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800 text-sm">{{ trim($agent->prenom . ' ' . $agent->nom) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $agent->poste ?: '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($agent->service)
                                    <span class="inline-flex items-center rounded-lg bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700">
                                        {{ $agent->service->nom }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $agent->email ?: '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.agents.show', $agent) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500 shadow-sm transition hover:border-emerald-200 hover:text-emerald-600">
                                    <i class="far fa-eye text-[9px]"></i> Voir
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($agents->hasPages())
            <div class="flex items-center justify-between border-t border-slate-100 px-6 py-3">
                <p class="text-xs text-slate-400">
                    {{ $agents->firstItem() }}–{{ $agents->lastItem() }} sur {{ $agents->total() }} agents
                </p>
                <div class="flex items-center gap-1">
                    @if($agents->onFirstPage())
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-300 text-xs cursor-default"><i class="fas fa-chevron-left text-[9px]"></i></span>
                    @else
                        <a href="{{ $agents->previousPageUrl() }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 text-xs hover:bg-slate-50 transition"><i class="fas fa-chevron-left text-[9px]"></i></a>
                    @endif
                    @php $current = $agents->currentPage(); $last = $agents->lastPage(); $from = max(1, $current - 2); $to = min($last, $current + 2); @endphp
                    @if($from > 1)
                        <a href="{{ $agents->url(1) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 text-xs hover:bg-slate-50 transition">1</a>
                        @if($from > 2)<span class="text-slate-300 text-xs px-0.5">…</span>@endif
                    @endif
                    @for($p = $from; $p <= $to; $p++)
                        @if($p === $current)
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-600 text-white text-xs font-black">{{ $p }}</span>
                        @else
                            <a href="{{ $agents->url($p) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 text-xs hover:bg-slate-50 transition">{{ $p }}</a>
                        @endif
                    @endfor
                    @if($to < $last)
                        @if($to < $last - 1)<span class="text-slate-300 text-xs px-0.5">…</span>@endif
                        <a href="{{ $agents->url($last) }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 text-xs hover:bg-slate-50 transition">{{ $last }}</a>
                    @endif
                    @if($agents->hasMorePages())
                        <a href="{{ $agents->nextPageUrl() }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 text-xs hover:bg-slate-50 transition"><i class="fas fa-chevron-right text-[9px]"></i></a>
                    @else
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-300 text-xs cursor-default"><i class="fas fa-chevron-right text-[9px]"></i></span>
                    @endif
                </div>
            </div>
            @endif
        @endif

    </div>

</div>
</main>
@endsection
