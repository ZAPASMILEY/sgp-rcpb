@extends('layouts.app')

@section('title', $agence->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full space-y-6">

        {{-- Fil d'Ariane + Retour --}}
        <div class="flex items-center justify-between">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                Réseau /
                @if ($agence->delegationTechnique)
                    <span class="text-slate-500">{{ $agence->delegationTechnique->region }}</span> /
                @endif
                @if ($agence->caisse)
                    <span class="text-slate-500">{{ $agence->caisse->nom }}</span> /
                @endif
                Agence
            </p>
            <a href="{{ route('admin.agences.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check shrink-0"></i> {{ session('status') }}
            </div>
        @endif

        {{-- En-tête --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $agence->nom }}</h1>
                <p class="mt-1 text-xs text-slate-500">Fiche de consultation de l'agence et de son personnel rattaché.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.agences.edit', $agence) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-sky-700">
                    <i class="fas fa-pen text-xs"></i> Modifier
                </a>
            </div>
        </div>

        {{-- Infos structure --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Délégation Technique</p>
                <p class="mt-2 text-sm font-bold text-slate-800">
                    {{ $agence->delegationTechnique?->region ?? '—' }}
                    @if ($agence->delegationTechnique?->ville)
                        — {{ $agence->delegationTechnique->ville }}
                    @endif
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Caisse superviseur</p>
                <p class="mt-2 text-sm font-bold text-slate-800">{{ $agence->caisse?->nom ?? '—' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Téléphone accueil</p>
                <p class="mt-2 text-sm font-bold text-slate-800">{{ $agence->telephone_accueil ?? '—' }}</p>
            </div>
        </div>

        {{-- KPI --}}
        @php
            $agence->loadMissing(['guichets', 'agents']);
            $nbGuichets = $agence->guichets->count();
            $nbAgents   = $agence->agents->count();
        @endphp
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <h3 class="text-2xl font-bold text-sky-600">{{ $nbGuichets }}</h3>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">GUICHETS</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm col-span-1 sm:col-span-3">
                <h3 class="text-2xl font-bold text-emerald-600">{{ $nbAgents }}</h3>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">AGENTS RATTACHÉS</p>
            </div>
        </div>

        {{-- Responsables --}}
        <div class="grid gap-4 md:grid-cols-2">

            {{-- Chef d'Agence --}}
            <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                    <i class="fas fa-user-tie text-xl"></i>
                </div>
                <div class="space-y-0.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">CHEF D'AGENCE</p>
                    @if ($agence->chef)
                        <h2 class="text-base font-bold text-slate-900">
                            {{ $agence->chef->prenom }} {{ $agence->chef->nom }}
                        </h2>
                        <p class="text-xs font-medium text-slate-500">{{ $agence->chef->poste ?? "Chef d'Agence" }}</p>
                        @if ($agence->chef->matricule)
                            <p class="text-[11px] text-slate-400">Mat. {{ $agence->chef->matricule }}</p>
                        @endif
                    @else
                        <p class="text-sm italic text-slate-400">Non assigné</p>
                    @endif
                </div>
            </div>

            {{-- Secrétaire --}}
            <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-600">
                    <i class="fas fa-user-pen text-xl"></i>
                </div>
                <div class="space-y-0.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">SECRÉTAIRE D'AGENCE</p>
                    @php $secretaire = $agence->secretaire; @endphp
                    @if ($secretaire)
                        <h2 class="text-base font-bold text-slate-900">
                            {{ $secretaire->prenom }} {{ $secretaire->nom }}
                        </h2>
                        <p class="text-xs font-medium text-slate-500">{{ $secretaire->poste ?? "Secrétaire d'Agence" }}</p>
                        @if ($secretaire->matricule)
                            <p class="text-[11px] text-slate-400">Mat. {{ $secretaire->matricule }}</p>
                        @endif
                    @else
                        <p class="text-sm italic text-slate-400">Non assigné</p>
                    @endif
                </div>
            </div>

        </div>

        {{-- Guichets --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">GUICHETS DE L'AGENCE</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Guichets rattachés à cette agence.</p>
                </div>
            </div>

            @if ($nbGuichets > 0)
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">GUICHET</th>
                                <th class="px-5 py-3">CHEF DE GUICHET</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($agence->guichets as $i => $guichet)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3 text-xs font-medium text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $guichet->nom }}</td>
                                    <td class="px-5 py-3">
                                        @if ($guichet->chef ?? null)
                                            <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                                                {{ $guichet->chef->prenom }} {{ $guichet->chef->nom }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">Non assigné</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-sm text-slate-400 flex items-center gap-3">
                    <i class="fas fa-cash-register text-slate-300"></i>
                    <span>Aucun guichet rattaché à cette agence.</span>
                </div>
            @endif
        </div>

        {{-- Agents rattachés --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">AGENTS RATTACHÉS</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Personnel directement affecté à cette agence.</p>
                </div>
                <a href="{{ route('admin.agences.agents.index', $agence) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-sky-600">
                    <i class="fas fa-users text-sky-600 text-sm"></i>
                    <span>Gérer les agents</span>
                </a>
            </div>

            @if ($nbAgents > 0)
                <div class="overflow-hidden rounded-xl border border-slate-100">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3">#</th>
                                <th class="px-5 py-3">NOM & PRÉNOM</th>
                                <th class="px-5 py-3">MATRICULE</th>
                                <th class="px-5 py-3">FONCTION</th>
                                <th class="px-5 py-3">SEXE</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($agence->agents as $i => $agent)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-3 text-xs font-medium text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sky-50 text-[10px] font-bold text-sky-600">
                                                {{ strtoupper(substr($agent->prenom ?? '?', 0, 1)) }}
                                            </div>
                                            <span class="font-semibold text-slate-800">{{ $agent->prenom }} {{ $agent->nom }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $agent->matricule ?? '—' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-600">{{ $agent->poste ?? $agent->role ?? '—' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $agent->sexe ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-xl border border-slate-100 bg-slate-50 p-5 text-sm text-slate-400 flex items-center gap-3">
                    <i class="fas fa-users text-slate-300"></i>
                    <span>Aucun agent rattaché directement à cette agence.</span>
                </div>
            @endif
        </div>

    </div>
</main>
@endsection
