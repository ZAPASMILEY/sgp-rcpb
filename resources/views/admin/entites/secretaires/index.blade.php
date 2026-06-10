@extends('layouts.app')

@section('title', 'Secrétaires | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">

    {{-- Breadcrumb --}}
    <div class="mb-4">
        <a href="{{ route('admin.entites.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour Faîtière</span>
        </a>
    </div>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Secrétaires</h1>
                <p class="text-sm text-slate-400 mt-1">Toutes les secrétaires du réseau RCPB</p>
            </div>
            <div class="px-5 py-3 rounded-2xl bg-white border border-slate-200 shadow-sm text-sm font-black uppercase tracking-widest text-slate-500">
                {{ $secretaires->count() }} secrétaire(s)
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" action="{{ route('admin.entites.secretaires.index') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex flex-col lg:flex-row gap-3">

                {{-- Recherche --}}
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Rechercher par nom, email, téléphone…"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-300"
                    >
                </div>

                {{-- Type --}}
                <div class="w-full lg:w-72">
                    <select name="type" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-300">
                        <option value="">Tous les types</option>
                        @foreach($fonctionsSecretaires as $f)
                            <option value="{{ $f }}" @selected($type === $f)>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tri --}}
                <div class="w-full lg:w-56">
                    <select name="sort" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-300">
                        <option value="nom"     @selected($sortBy === 'nom')>Trier par Nom</option>
                        <option value="prenom"  @selected($sortBy === 'prenom')>Trier par Prénom</option>
                        <option value="fonction" @selected($sortBy === 'fonction')>Trier par Type</option>
                    </select>
                </div>

                {{-- Ordre --}}
                <div class="w-full lg:w-40">
                    <select name="dir" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-300">
                        <option value="asc"  @selected($sortDir === 'asc')>A → Z</option>
                        <option value="desc" @selected($sortDir === 'desc')>Z → A</option>
                    </select>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 text-white text-sm font-bold hover:bg-cyan-700 transition whitespace-nowrap">
                    <i class="fas fa-filter mr-1"></i> Filtrer
                </button>

                @if($search || $type)
                    <a href="{{ route('admin.entites.secretaires.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-sm font-bold hover:bg-slate-50 transition whitespace-nowrap">
                        <i class="fas fa-times mr-1"></i> Effacer
                    </a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm text-slate-700">
                    <thead class="sticky top-0 z-10 bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">#</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Secrétaire</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Type</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Structure</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Email</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Téléphone</th>
                            <th class="px-5 py-3.5 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Compte</th>
                            <th class="px-5 py-3.5 text-right text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($secretaires as $agent)
                            @php
                                $structure = match(true) {
                                    $agent->entite_id            !== null && $agent->direction_id === null => $agent->entite?->nom ?? '—',
                                    $agent->direction_id         !== null => $agent->direction?->nom ?? '—',
                                    $agent->delegation_technique_id !== null => ($agent->delegationTechnique ? 'DT '.$agent->delegationTechnique->region.' - '.$agent->delegationTechnique->ville : '—'),
                                    $agent->caisse_id            !== null => $agent->caisse?->nom ?? '—',
                                    $agent->agence_id            !== null => $agent->agence?->nom ?? '—',
                                    default => '—',
                                };

                                $typeBadge = match($agent->role) {
                                    'Secrétaire Assistante'    => ['bg-purple-100 text-purple-700', 'Assistante DG'],
                                    'Secrétaire de Direction'  => ['bg-blue-100 text-blue-700',   'Direction'],
                                    'Secrétaire Technique'     => ['bg-emerald-100 text-emerald-700', 'DT'],
                                    'Secrétaire de Caisse'     => ['bg-amber-100 text-amber-700', 'Caisse'],
                                    "Secrétaire d'Agence"      => ['bg-rose-100 text-rose-700',   'Agence'],
                                    default                    => ['bg-slate-100 text-slate-600',  $agent->role],
                                };
                                $showUrl = route('admin.agents.show', $agent);
                                $editUrl = route('admin.agents.edit', $agent);
                            @endphp
                            <tr class="hover:bg-cyan-50/50 transition cursor-pointer"
                                onclick="window.location='{{ $showUrl }}'">
                                <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">{{ $loop->iteration }}</td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ $showUrl }}" class="font-semibold text-slate-800 hover:text-cyan-700 transition" onclick="event.stopPropagation()">
                                        {{ $agent->prenom }} {{ $agent->nom }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $typeBadge[0] }}">
                                        {{ $typeBadge[1] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $structure }}</td>
                                <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $agent->email ?: '—' }}</td>
                                <td class="px-5 py-3.5">
                                    @if($agent->numero_telephone)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700">
                                            <i class="fas fa-phone text-[10px] text-cyan-500"></i>
                                            {{ $agent->numero_telephone }}
                                        </span>
                                    @else
                                        <a href="{{ $editUrl }}" onclick="event.stopPropagation()"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-amber-500 hover:text-amber-700 transition"
                                           title="Ajouter le numéro de téléphone">
                                            <i class="fas fa-plus-circle text-[10px]"></i> Ajouter
                                        </a>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    @if($agent->user)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700">
                                            <i class="fas fa-check-circle text-[10px]"></i> Actif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-400">
                                            <i class="fas fa-user-slash text-[10px]"></i> Sans compte
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ $showUrl }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-cyan-50 hover:text-cyan-600 transition"
                                           title="Voir le profil">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ $editUrl }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-600 transition"
                                           title="Modifier">
                                            <i class="fas fa-pencil text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                    <i class="fas fa-search text-2xl mb-2 block"></i>
                                    Aucune secrétaire trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $secretaires->count() }} résultat(s)</div>
        </div>

    </div>
</div>
@endsection
