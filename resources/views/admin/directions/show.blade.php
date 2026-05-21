@extends('layouts.app')

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $isFaitiere   = is_null($direction->delegation_technique_id ?? null);
    $directeur    = $direction->directeur;
    $secretaire   = $direction->secretaire;
@endphp
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full flex flex-col gap-6">

        {{-- ── HEADER ─────────────────────────────────────────────────────── --}}
        <header class="admin-panel p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">
                        {{ $isFaitiere ? 'Faîtière / Direction' : 'Délégation Technique / Direction' }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $direction->nom }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        @if ($direction->delegationTechnique)
                            {{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}
                        @else
                            Direction rattachée à la Faîtière
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ $isFaitiere ? route('admin.entites.directions.index') : route('admin.delegations-techniques.index') }}"
                       class="ent-btn ent-btn-soft">Retour</a>
                    <a href="{{ route('admin.directions.edit', $direction) }}"
                       class="ent-btn ent-btn-primary">Modifier</a>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- ── RESPONSABLES ────────────────────────────────────────────────── --}}
        <section class="grid gap-4 md:grid-cols-2">
            {{-- Directeur --}}
            <article class="admin-panel p-6 flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <i class="fas fa-user-tie text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                        {{ $isFaitiere ? 'Directeur de Direction' : 'Directeur Technique' }}
                    </p>
                    @if ($directeur)
                        <p class="mt-1 text-lg font-bold text-slate-900">
                            {{ trim($directeur->prenom . ' ' . $directeur->nom) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $directeur->role }}</p>
                        @if ($directeur->email)
                            <p class="text-xs text-emerald-600 mt-1">
                                <i class="far fa-envelope mr-1 opacity-60"></i>{{ $directeur->email }}
                            </p>
                        @endif
                        @if ($directeur->numero_telephone)
                            <p class="text-xs text-slate-500 mt-0.5">
                                <i class="fas fa-phone mr-1 opacity-60"></i>{{ $directeur->numero_telephone }}
                            </p>
                        @endif
                    @else
                        <p class="mt-2 text-sm italic text-slate-400">Aucun directeur renseigné.</p>
                    @endif
                </div>
            </article>

            {{-- Secrétaire --}}
            <article class="admin-panel p-6 flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-500">
                    <i class="fas fa-user-clock text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Secrétaire de Direction</p>
                    @if ($secretaire)
                        <p class="mt-1 text-lg font-bold text-slate-900">
                            {{ trim($secretaire->prenom . ' ' . $secretaire->nom) }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $secretaire->role }}</p>
                        @if ($secretaire->email)
                            <p class="text-xs text-emerald-600 mt-1">
                                <i class="far fa-envelope mr-1 opacity-60"></i>{{ $secretaire->email }}
                            </p>
                        @endif
                    @else
                        <p class="mt-2 text-sm italic text-slate-400">Aucune secrétaire renseignée.</p>
                    @endif
                </div>
            </article>
        </section>

        {{-- ── STATS ───────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="admin-panel flex flex-col gap-1 p-5">
                <span class="text-3xl font-black text-emerald-600">{{ $agents->count() }}</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    {{ $selectedService ? 'Agents filtrés' : 'Total agents' }}
                </span>
            </div>
            <div class="admin-panel flex flex-col gap-1 p-5">
                <span class="text-3xl font-black text-slate-800">{{ $services->count() }}</span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Services</span>
            </div>
            <div class="admin-panel flex flex-col gap-1 p-5">
                <span class="text-3xl font-black text-slate-800">
                    {{ $direction->agents()->where('direction_id', $direction->id)->count() }}
                </span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">En Direction directe</span>
            </div>
            <div class="admin-panel flex flex-col gap-1 p-5">
                <span class="text-3xl font-black text-slate-800">
                    {{ $direction->agents()->whereNotNull('service_id')->count() }}
                </span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Dans les services</span>
            </div>
        </div>

        {{-- ── AGENTS ──────────────────────────────────────────────────────── --}}
        <section class="admin-panel p-6">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-black uppercase tracking-wider text-slate-800">
                        Personnel de la Direction
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Agents rattachés directement ou via un service</p>
                </div>

                {{-- ── Filtre par service ───────────────────────────────────── --}}
                <form method="GET" action="{{ route('admin.directions.show', $direction) }}"
                      class="flex items-center gap-2">
                    <select name="service_id"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">— Tous les services —</option>
                        @foreach ($services as $svc)
                            <option value="{{ $svc->id }}" @selected($selectedService == $svc->id)>
                                {{ $svc->nom }}
                            </option>
                        @endforeach
                    </select>
                    @if ($selectedService)
                        <a href="{{ route('admin.directions.show', $direction) }}"
                           class="flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 transition">
                            <i class="fas fa-times text-[10px]"></i> Réinitialiser
                        </a>
                    @endif
                </form>
            </div>

            @if ($agents->isEmpty())
                <div class="flex flex-col items-center py-14 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-200">
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-400 italic">
                        @if ($selectedService)
                            Aucun agent dans ce service.
                        @else
                            Aucun agent rattaché à cette direction.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">#</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Nom & Prénom</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Rôle</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Service</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Email</th>
                                <th class="pb-3 text-right text-[11px] font-black uppercase tracking-wider text-slate-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach ($agents as $agent)
                            <tr class="group hover:bg-slate-50/60 transition">
                                <td class="py-3 text-xs text-slate-300 w-6">{{ $loop->iteration }}</td>
                                <td class="py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 text-xs font-black">
                                            {{ strtoupper(substr($agent->prenom ?? '', 0, 1) . substr($agent->nom ?? '', 0, 1)) }}
                                        </div>
                                        <span class="font-bold text-slate-800">
                                            {{ trim($agent->prenom . ' ' . $agent->nom) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 text-slate-600">{{ $agent->role ?: '—' }}</td>
                                <td class="py-3">
                                    @if ($agent->service)
                                        <span class="inline-block rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-600">
                                            {{ $agent->service->nom }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300 italic">Direction directe</span>
                                    @endif
                                </td>
                                <td class="py-3 text-xs text-slate-500">{{ $agent->email ?: '—' }}</td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.agents.show', $agent) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-100 bg-white px-3 py-1.5 text-[10px] font-black uppercase text-slate-500 shadow-sm transition hover:border-emerald-200 hover:text-emerald-600">
                                        <i class="far fa-eye text-[9px]"></i> Voir
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

    </div>
</main>
@endsection
