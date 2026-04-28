@extends('layouts.app')

@section('title', 'Agents | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="flash-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('flash-msg')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Agents</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $totalAgents }} agent(s) enregistré(s) au total.
                        @if ($fonctionActive)
                            <span class="font-semibold text-blue-600">— Filtre : {{ $fonctions[$fonctionActive] ?? $fonctionActive }}</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.agents.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter un agent
                </a>
            </div>
        </div>

        {{-- Filtres --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.agents.index') }}" class="flex flex-wrap items-end gap-3">

                {{-- Recherche textuelle --}}
                <div class="flex-1 min-w-[180px] space-y-1">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Nom, prénom, email, fonction…"
                            class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400"
                        >
                    </div>
                </div>

                {{-- Filtre par fonction --}}
                <div class="min-w-[200px] space-y-1">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Fonction</label>
                    <select name="fonction" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400">
                        <option value="">Toutes les fonctions</option>
                        @foreach ($fonctions as $val => $label)
                            <option value="{{ $val }}" @selected($fonctionActive === $val)>
                                {{ $label }}
                                @if (($countsByFonction[$val] ?? 0) > 0)
                                    ({{ $countsByFonction[$val] }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtre affectation --}}
                <div class="min-w-[180px] space-y-1">
                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Affectation</label>
                    <select name="affectation" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400">
                        <option value="">Tous les agents</option>
                        <option value="affecte" @selected($affectation === 'affecte')>Affectés ({{ $totalAffectes }})</option>
                        <option value="non_affecte" @selected($affectation === 'non_affecte')>Non affectés ({{ $totalAgents - $totalAffectes }})</option>
                    </select>
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>

                @if ($fonctionActive || $search || $affectation)
                    <a href="{{ route('admin.agents.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        <i class="fas fa-times text-xs"></i> Réinitialiser
                    </a>
                @endif
            </form>
        </div>

        {{-- Badges de filtre rapide par fonction --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.agents.index', array_filter(['search' => $search])) }}"
               class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition
                      {{ !$fonctionActive ? 'bg-slate-800 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' }}">
                Tous
                <span class="rounded-full {{ !$fonctionActive ? 'bg-white/20' : 'bg-slate-100' }} px-1.5 py-0.5 text-[10px]">
                    {{ $totalAgents }}
                </span>
            </a>
            @foreach ($fonctions as $val => $label)
                @php $cnt = $countsByFonction[$val] ?? 0; @endphp
                @if ($cnt > 0)
                    <a href="{{ route('admin.agents.index', array_filter(['fonction' => $val, 'search' => $search])) }}"
                       class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition
                              {{ $fonctionActive === $val ? 'bg-blue-600 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' }}">
                        {{ $label }}
                        <span class="rounded-full {{ $fonctionActive === $val ? 'bg-white/20' : 'bg-slate-100' }} px-1.5 py-0.5 text-[10px]">
                            {{ $cnt }}
                        </span>
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Tableau --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 w-12">#</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Photo</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Nom complet</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Fonction</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Contact</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Affectation</th>
                            <th class="px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Compte</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($agents as $agent)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-400 text-xs">{{ $loop->iteration }}</td>

                                {{-- Photo --}}
                                <td class="px-4 py-3">
                                    @if ($agent->photo_path)
                                        <img src="{{ Storage::url($agent->photo_path) }}"
                                             alt="{{ $agent->prenom }}"
                                             class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200">
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-200 to-slate-300 text-xs font-black uppercase text-slate-500">
                                            {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Nom --}}
                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-800">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                    @if ($agent->sexe)
                                        <p class="text-[11px] text-slate-400">{{ ucfirst($agent->sexe) }}</p>
                                    @endif
                                </td>

                                {{-- Fonction --}}
                                <td class="px-4 py-3">
                                    @if ($agent->fonction)
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            {{ $agent->fonction }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">—</span>
                                    @endif
                                </td>

                                {{-- Contact --}}
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-600">{{ $agent->email }}</p>
                                    @if ($agent->numero_telephone)
                                        <p class="text-[11px] text-slate-400">{{ $agent->numero_telephone }}</p>
                                    @endif
                                </td>

                                {{-- Affectation --}}
                                <td class="px-4 py-3">
                                    @php
                                        $lieu = null; $libelle = null;
                                        // 1. FK directes (agent membre d'une structure)
                                        if ($agent->service_id && $agent->service) {
                                            $lieu = 'Service'; $libelle = $agent->service->nom;
                                        } elseif ($agent->guichet_id && $agent->guichet) {
                                            $lieu = 'Guichet'; $libelle = $agent->guichet->nom ?? 'Guichet';
                                        } elseif ($agent->agence_id && $agent->agence) {
                                            $lieu = 'Agence'; $libelle = $agent->agence->nom;
                                        } elseif ($agent->caisse_id && $agent->caisse) {
                                            $lieu = 'Caisse'; $libelle = $agent->caisse->nom;
                                        } elseif ($agent->delegation_technique_id && $agent->delegationTechnique) {
                                            $lieu = 'Délégation'; $libelle = $agent->delegationTechnique->region.' / '.$agent->delegationTechnique->ville;
                                        } elseif ($agent->direction_id && $agent->direction) {
                                            $lieu = 'Direction'; $libelle = $agent->direction->nom;
                                        } elseif ($agent->entite_id && $agent->entite) {
                                            $lieu = 'Dir. Générale'; $libelle = $agent->entite->nom;
                                        }
                                        // 2. FK inverses (agent EST responsable d'une structure)
                                        elseif ($agent->ledService) {
                                            $lieu = 'Chef Service'; $libelle = $agent->ledService->nom;
                                        } elseif ($agent->ledGuichet) {
                                            $lieu = 'Chef Guichet'; $libelle = $agent->ledGuichet->nom ?? 'Guichet';
                                        } elseif ($agent->ledAgence) {
                                            $lieu = "Chef Agence"; $libelle = $agent->ledAgence->nom;
                                        } elseif ($agent->secretariedCaisse) {
                                            $lieu = 'Sec. Caisse'; $libelle = $agent->secretariedCaisse->nom;
                                        } elseif ($agent->directedCaisse) {
                                            $lieu = 'Dir. Caisse'; $libelle = $agent->directedCaisse->nom;
                                        } elseif ($agent->secretariedDelegation) {
                                            $d = $agent->secretariedDelegation;
                                            $lieu = 'Sec. Délégation'; $libelle = $d->region.' / '.$d->ville;
                                        } elseif ($agent->directedDelegation) {
                                            $d = $agent->directedDelegation;
                                            $lieu = 'Dir. Délégation'; $libelle = $d->region.' / '.$d->ville;
                                        } elseif ($agent->secretariedDirection) {
                                            $lieu = 'Sec. Direction'; $libelle = $agent->secretariedDirection->nom;
                                        } elseif ($agent->directedDirection) {
                                            $lieu = 'Dir. Direction'; $libelle = $agent->directedDirection->nom;
                                        }
                                    @endphp
                                    @if ($lieu)
                                        <div>
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">{{ $lieu }}</span>
                                            <p class="mt-0.5 text-xs text-slate-600 font-semibold truncate max-w-[160px]">{{ $libelle }}</p>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-600">
                                            <i class="fas fa-circle-exclamation text-[8px]"></i> Non affecté
                                        </span>
                                    @endif
                                </td>

                                {{-- Compte --}}
                                <td class="px-4 py-3">
                                    @if ($agent->user)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <i class="fas fa-circle text-[6px]"></i> Compte actif
                                        </span>
                                    @else
                                        <a href="{{ route('admin.users.create') }}?agent_id={{ $agent->id }}"
                                           class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition">
                                            <i class="fas fa-plus text-[8px]"></i> Créer compte
                                        </a>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.agents.show', $agent) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-600 transition"
                                           title="Voir la fiche">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition"
                                           title="Modifier">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}"
                                              onsubmit="return confirm('Supprimer {{ addslashes($agent->prenom.' '.$agent->nom) }} ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                                    title="Supprimer">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <i class="fas fa-users text-4xl"></i>
                                        @if ($fonctionActive)
                                            <p class="text-sm font-semibold">Aucun agent avec la fonction <strong>{{ $fonctions[$fonctionActive] ?? $fonctionActive }}</strong>.</p>
                                            <a href="{{ route('admin.agents.index') }}" class="text-xs text-blue-500 hover:underline">Voir tous les agents</a>
                                        @elseif ($search)
                                            <p class="text-sm font-semibold">Aucun résultat pour "<strong>{{ $search }}</strong>".</p>
                                            <a href="{{ route('admin.agents.index') }}" class="text-xs text-blue-500 hover:underline">Réinitialiser</a>
                                        @else
                                            <p class="text-sm font-semibold">Aucun agent enregistré.</p>
                                            <a href="{{ route('admin.agents.create') }}" class="text-xs font-bold text-blue-500 hover:underline">
                                                Ajouter le premier agent
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($agents->isNotEmpty())
                <div class="border-t border-slate-100 bg-slate-50 px-4 py-2.5 text-right text-xs text-slate-400">
                    {{ $agents->count() }} agent(s) affiché(s)
                    @if ($fonctionActive || $search) sur {{ $totalAgents }} au total @endif
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
