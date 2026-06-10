@extends('layouts.app')

@section('title', 'Détails caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full space-y-6">
            
            {{-- Fil d'Ariane et Bouton Retour --}}
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">FAITIÈRE / CAISSE</p>
                <a href="{{ route('admin.caisses.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <span>Retour</span>
                </a>
            </div>

            {{-- Entête principale de la caisse --}}
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $caisse->nom }}</h1>
                    <p class="text-xs text-slate-500 mt-1">Fiche de consultation de la caisse et de son personnel rattaché.</p>
                </div>
                <div>
                    <a href="{{ route('admin.caisses.edit', $caisse) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-700">
                        <span>Modifier</span>
                    </a>
                </div>
            </div>

            {{-- Section Profils Compacts (Style Direction de l'Audit) --}}
            <div class="grid gap-6 md:grid-cols-2">
                
                {{-- Fiche Directeur de Caisse --}}
                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-user-tie text-xl"></i>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">DIRECTEUR DE CAISSE</p>
                        <h2 class="text-base font-bold text-slate-950">
                            {{ $caisse->directeur ? trim($caisse->directeur->prenom . ' ' . $caisse->directeur->nom) : '—' }}
                        </h2>
                        <p class="text-xs text-slate-500 font-medium">Directeur de Caisse</p>
                        @if($caisse->directeur?->email)
                        <div class="flex items-center gap-2 pt-1 text-xs text-cyan-600">
                            <i class="fas fa-envelope text-[10px]"></i>
                            <span class="text-[11px] font-medium">{{ $caisse->directeur->email }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Fiche Secrétaire du Directeur --}}
                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-50 text-slate-600">
                        <i class="fas fa-users-cog text-xl"></i>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">SECRÉTAIRE DE DIRECTION</p>
                        <h2 class="text-base font-bold text-slate-950">
                            {{ $caisse->secretaire ? trim($caisse->secretaire->prenom . ' ' . $caisse->secretaire->nom) : '—' }}
                        </h2>
                        <p class="text-xs text-slate-500 font-medium">Secrétaire de Caisse</p>
                        @if($caisse->secretaire?->email)
                        <div class="flex items-center gap-2 pt-1 text-xs text-cyan-600">
                            <i class="fas fa-envelope text-[10px]"></i>
                            <span class="text-[11px] font-medium">{{ $caisse->secretaire->email }}</span>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Petits Blocs de Statistiques / Compteurs --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <h3 class="text-2xl font-bold text-emerald-600">{{ $caisse->services->count() }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">SERVICES</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <h3 class="text-2xl font-bold text-sky-600">{{ $caisse->agences->count() }}</h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">AGENCES</p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm col-span-2 sm:col-span-2">
                    <h3 class="text-2xl font-bold text-cyan-600">
                        {{ $caisse->effectif_reel }} agents
                    </h3>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">TOTAL AGENTS</p>
                </div>
            </div>

            {{-- Tableau Principal : Personnel et Services de la Caisse --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">

                {{-- Entête du tableau avec bouton Affecter un service --}}
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">PERSONNEL ET SERVICES DE LA CAISSE</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Agents rattachés directement à la caisse ou via un service.</p>
                    </div>
                    <div>
                        {{-- Bouton Affecter un service --}}
                        <a href="{{ route('admin.caisses.affecter-service', $caisse) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-cyan-600">
                            <i class="fas fa-plus-circle text-cyan-600 text-sm"></i>
                            <span>Affecter un service</span>
                        </a>
                    </div>
                </div>

                @if($caisse->services->count() > 0)
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <table class="w-full text-left text-sm text-slate-600 border-collapse">
                            <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">#</th>
                                    <th class="px-6 py-4">NOM & PRÉNOM</th>
                                    <th class="px-6 py-4">FONCTION</th>
                                    <th class="px-6 py-4">SERVICE</th>
                                    <th class="px-6 py-4">EMAIL</th>
                                    <th class="px-6 py-4 text-right">ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php $counter = 1; @endphp
                                @foreach ($caisse->services as $service)
                                    {{-- Ligne pour le Chef de Service --}}
                                    @if($service->chef)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 text-xs font-medium text-slate-400">{{ $counter++ }}</td>
                                            <td class="px-6 py-4 font-semibold text-slate-900">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-cyan-50 text-[10px] font-bold text-cyan-600">
                                                        {{ strtoupper(substr($service->chef->nom, 0, 1).substr($service->chef->prenom, 0, 1)) }}
                                                    </div>
                                                    <span>{{ $service->chef->prenom }} {{ $service->chef->nom }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="inline-flex items-center rounded-full bg-cyan-50 px-2.5 py-0.5 text-xs font-medium text-cyan-700">
                                                    Chef de Service
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 font-medium text-slate-500 text-xs uppercase tracking-wide">{{ $service->nom }}</td>
                                            <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $service->chef->email ?: '-' }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                                <a href="{{ route('admin.services.show', $service) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 shadow-sm hover:bg-slate-50 hover:text-cyan-600">
                                                    <i class="fas fa-eye text-[10px]"></i>
                                                    <span>VOIR</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-100 bg-amber-50/50 p-5 text-sm text-amber-800 flex items-center gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500"></i>
                        <span>Aucun service ni agent n'est actuellement rattaché à cette caisse.</span>
                    </div>
                @endif
            </div>

            {{-- Tableau des Agences --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">

                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-wide text-slate-900">AGENCES DE LA CAISSE</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Agences supervisées par cette caisse.</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.caisses.affecter-agence', $caisse) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-sky-600">
                            <i class="fas fa-plus-circle text-sky-600 text-sm"></i>
                            <span>Affecter une agence</span>
                        </a>
                    </div>
                </div>

                @if($caisse->agences->count() > 0)
                    <div class="overflow-hidden rounded-xl border border-slate-100">
                        <table class="w-full text-left text-sm text-slate-600 border-collapse">
                            <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4">#</th>
                                    <th class="px-6 py-4">AGENCE</th>
                                    <th class="px-6 py-4">CHEF D'AGENCE</th>
                                    <th class="px-6 py-4 text-right">ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($caisse->agences as $index => $agence)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 text-xs font-medium text-slate-400">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 font-semibold text-slate-900">
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-50 text-[10px] font-bold text-sky-600">
                                                    <i class="fas fa-building text-[9px]"></i>
                                                </div>
                                                <span>{{ $agence->nom }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($agence->chef)
                                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                                                    {{ $agence->chef->prenom }} {{ $agence->chef->nom }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">Non assigné</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <a href="{{ route('admin.agences.show', $agence) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 shadow-sm hover:bg-slate-50 hover:text-sky-600">
                                                <i class="fas fa-eye text-[10px]"></i>
                                                <span>VOIR</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-5 text-sm text-slate-500 flex items-center gap-3">
                        <i class="fas fa-building text-slate-300"></i>
                        <span>Aucune agence n'est rattachée à cette caisse.</span>
                    </div>
                @endif
            </div>

        </div>
    </main>
@endsection