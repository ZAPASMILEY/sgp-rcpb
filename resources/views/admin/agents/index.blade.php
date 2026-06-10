@extends('layouts.app')

@section('title', 'Agents | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
<div class="w-full flex flex-col gap-5">

    {{-- ── FLASH ────────────────────────────────────────────────────────────── --}}
    @if (session('status'))
        <div id="flash-msg" class="fixed right-6 top-6 z-50 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-5 py-3.5 shadow-2xl shadow-emerald-100/60 transition-all duration-500">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i class="fas fa-check text-xs"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            <button onclick="this.closest('#flash-msg').remove()" class="ml-2 text-slate-300 hover:text-slate-500 transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <script>setTimeout(() => document.getElementById('flash-msg')?.remove(), 3000);</script>
    @endif

    {{-- ── HEADER ───────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Administration</p>
            <h1 class="mt-0.5 text-xl font-black text-slate-900">Agents</h1>
        </div>
        <div class="flex items-center gap-2">
            @if($sansCompte > 0)
                <form method="POST" action="{{ route('admin.agents.sync-accounts') }}"
                      onsubmit="return confirm('Créer les comptes manquants pour {{ $sansCompte }} agent(s) ? Mot de passe par défaut : 11111111')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 hover:bg-amber-100 transition">
                        <i class="fas fa-user-plus text-[10px]"></i>
                        {{ $sansCompte }} comptes manquants
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.agents.create') }}"
               class="ent-btn ent-btn-primary text-xs py-1.5 px-4">
                <i class="fas fa-plus mr-1.5"></i> Ajouter un agent
            </a>
        </div>
    </div>

    {{-- ── STATS ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-700 to-slate-900 p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white">
                <i class="fas fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-white">{{ $totalAgents }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Total agents</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow shadow-emerald-100">
                <i class="fas fa-map-marker-alt text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-emerald-600">{{ $totalAffectes }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Affectés</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow shadow-amber-100">
                <i class="fas fa-user-slash text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-amber-600">{{ $sansCompte }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sans compte</p>
            </div>
        </div>
    </div>

    {{-- ── FILTRES ───────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.agents.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[180px]">
                <label class="mb-1 block text-[10px] font-black uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Nom, prénom, email…"
                           class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs font-semibold text-slate-700 placeholder-slate-300 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                </div>
            </div>
            <div class="min-w-[180px]">
                <label class="mb-1 block text-[10px] font-black uppercase tracking-wider text-slate-400">Rôle</label>
                <select name="role" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                    <option value="">Tous les rôles</option>
                    @foreach ($roles as $val => $label)
                        <option value="{{ $val }}" @selected($roleActive === $val)>
                            {{ $label }}@if(($countsByRole[$val] ?? 0) > 0) ({{ $countsByRole[$val] }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[160px]">
                <label class="mb-1 block text-[10px] font-black uppercase tracking-wider text-slate-400">Affectation</label>
                <select name="affectation" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 transition">
                    <option value="">Tous les agents</option>
                    <option value="affecte"     @selected($affectation === 'affecte')>Affectés ({{ $totalAffectes }})</option>
                    <option value="non_affecte" @selected($affectation === 'non_affecte')>Non affectés ({{ $totalAgents - $totalAffectes }})</option>
                </select>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                <i class="fas fa-filter text-[10px]"></i> Filtrer
            </button>
            @if ($roleActive || $search || $affectation)
                <a href="{{ route('admin.agents.index') }}"
                   class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-rose-500 transition">
                    <i class="fas fa-times text-xs"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- ── BADGES RÔLE RAPIDE ───────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-1.5">
        <a href="{{ route('admin.agents.index', array_filter(['search' => $search])) }}"
           class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition
                  {{ !$roleActive ? 'bg-slate-800 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' }}">
            Tous
            <span class="rounded-full {{ !$roleActive ? 'bg-white/20' : 'bg-slate-100' }} px-1.5 py-0.5 text-[10px] font-black">{{ $totalAgents }}</span>
        </a>
        @foreach ($roles as $val => $label)
            @php $cnt = $countsByRole[$val] ?? 0; @endphp
            @if ($cnt > 0)
                <a href="{{ route('admin.agents.index', array_filter(['role' => $val, 'search' => $search])) }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold transition
                          {{ $roleActive === $val ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $label }}
                    <span class="rounded-full {{ $roleActive === $val ? 'bg-white/20' : 'bg-slate-100' }} px-1.5 py-0.5 text-[10px] font-black">{{ $cnt }}</span>
                </a>
            @endif
        @endforeach
    </div>

    {{-- ── TABLEAU ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Liste des agents</h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ $agents->count() }} agent{{ $agents->count() > 1 ? 's' : '' }} affiché{{ $agents->count() > 1 ? 's' : '' }}
                    @if($roleActive || $search) · <span class="text-blue-500 font-semibold">filtre actif</span>@endif
                </p>
            </div>
        </div>

        @if($agents->isEmpty())
            <div class="flex flex-col items-center py-12 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
                @if ($roleActive)
                    <p class="text-sm italic text-slate-400">Aucun agent avec le rôle <strong>{{ $roles[$roleActive] ?? $roleActive }}</strong>.</p>
                    <a href="{{ route('admin.agents.index') }}" class="mt-2 text-xs text-blue-500 hover:underline">Voir tous les agents</a>
                @elseif ($search)
                    <p class="text-sm italic text-slate-400">Aucun résultat pour "<strong>{{ $search }}</strong>".</p>
                    <a href="{{ route('admin.agents.index') }}" class="mt-2 text-xs text-blue-500 hover:underline">Réinitialiser</a>
                @else
                    <p class="text-sm italic text-slate-400">Aucun agent enregistré.</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:520px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gradient-to-r from-slate-700 to-slate-800">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300 w-8">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Nom complet</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Rôle</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Contact</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Affectation</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Prise de fonction</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Compte</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($agents as $agent)
                        @php
                            // Affectation
                            $lieu = null; $libelle = null;
                            $affBadge = 'bg-violet-100 text-violet-700';
                            if ($agent->service_id && $agent->service)                          { $lieu = 'Service';      $libelle = $agent->service->nom;                                          $affBadge = 'bg-violet-100 text-violet-700'; }
                            elseif ($agent->guichet_id && $agent->guichet)                      { $lieu = 'Guichet';      $libelle = $agent->guichet->nom ?? 'Guichet';                              $affBadge = 'bg-pink-100 text-pink-700'; }
                            elseif ($agent->agence_id && $agent->agence)                        { $lieu = 'Agence';       $libelle = $agent->agence->nom;                                            $affBadge = 'bg-cyan-100 text-cyan-700'; }
                            elseif ($agent->caisse_id && $agent->caisse)                        { $lieu = 'Caisse';       $libelle = $agent->caisse->nom;                                            $affBadge = 'bg-emerald-100 text-emerald-700'; }
                            elseif ($agent->delegation_technique_id && $agent->delegationTechnique){ $lieu = 'DT';        $libelle = $agent->delegationTechnique->region.' / '.$agent->delegationTechnique->ville; $affBadge = 'bg-orange-100 text-orange-700'; }
                            elseif ($agent->direction_id && $agent->direction)                  { $lieu = 'Direction';    $libelle = $agent->direction->nom;                                         $affBadge = 'bg-blue-100 text-blue-700'; }
                            elseif ($agent->entite_id && $agent->entite)                        { $lieu = 'Dir. Générale';$libelle = $agent->entite->nom;                                            $affBadge = 'bg-green-100 text-green-700'; }
                            elseif ($agent->pcaedEntite)   { $lieu = 'PCA';          $libelle = $agent->pcaedEntite->nom;        $affBadge = 'bg-purple-100 text-purple-700'; }
                            elseif ($agent->assistantedEntite){ $lieu = 'Assistante'; $libelle = $agent->assistantedEntite->nom;  $affBadge = 'bg-fuchsia-100 text-fuchsia-700'; }
                            elseif ($agent->ledService)    { $lieu = 'Chef Service'; $libelle = $agent->ledService->nom;          $affBadge = 'bg-teal-100 text-teal-700'; }
                            elseif ($agent->ledGuichet)    { $lieu = 'Chef Guichet'; $libelle = $agent->ledGuichet->nom ?? 'Guichet'; $affBadge = 'bg-pink-100 text-pink-700'; }
                            elseif ($agent->ledAgence)     { $lieu = 'Chef Agence';  $libelle = $agent->ledAgence->nom;           $affBadge = 'bg-cyan-100 text-cyan-700'; }
                            elseif ($agent->secretariedCaisse)  { $lieu = 'Sec. Caisse';     $libelle = $agent->secretariedCaisse->nom;              $affBadge = 'bg-emerald-100 text-emerald-700'; }
                            elseif ($agent->directedCaisse)     { $lieu = 'Dir. Caisse';      $libelle = $agent->directedCaisse->nom;                 $affBadge = 'bg-emerald-100 text-emerald-700'; }
                            elseif ($agent->secretariedDelegation){ $lieu = 'Sec. DT';        $libelle = $agent->secretariedDelegation->region.' / '.$agent->secretariedDelegation->ville; $affBadge = 'bg-orange-100 text-orange-700'; }
                            elseif ($agent->directedDelegation)  { $lieu = 'Dir. DT';         $libelle = $agent->directedDelegation->region.' / '.$agent->directedDelegation->ville;  $affBadge = 'bg-orange-100 text-orange-700'; }
                            elseif ($agent->secretariedDirection){ $lieu = 'Sec. Direction';  $libelle = $agent->secretariedDirection->nom;           $affBadge = 'bg-blue-100 text-blue-700'; }
                            elseif ($agent->directedDirection)   { $lieu = 'Dir. Direction';  $libelle = $agent->directedDirection->nom;               $affBadge = 'bg-blue-100 text-blue-700'; }

                            // Couleur avatar selon rôle
                            $avatarGrad = match(true) {
                                in_array($agent->role, ['PCA'])                                                   => 'from-purple-500 to-indigo-600',
                                in_array($agent->role, ['Directeur Général'])                                     => 'from-indigo-500 to-blue-600',
                                in_array($agent->role, ['DGA'])                                                   => 'from-blue-500 to-cyan-600',
                                in_array($agent->role, ['Directeur de Direction'])                                => 'from-sky-500 to-blue-600',
                                in_array($agent->role, ['Directeur Technique'])                                   => 'from-cyan-500 to-teal-600',
                                in_array($agent->role, ['Directeur de Caisse'])                                   => 'from-teal-500 to-emerald-600',
                                in_array($agent->role, ["Chef d'Agence", 'Chef de Guichet', 'Chef de Service'])   => 'from-emerald-500 to-green-600',
                                in_array($agent->role, ['Assistante DG', 'Conseiller DG'])                        => 'from-fuchsia-500 to-pink-600',
                                str_contains($agent->role ?? '', 'Secrétaire')                                    => 'from-rose-400 to-pink-500',
                                default                                                                           => 'from-slate-400 to-slate-600',
                            };

                            // Couleur badge rôle
                            $roleBadge = match(true) {
                                in_array($agent->role, ['PCA'])                                                   => 'bg-purple-100 text-purple-700',
                                in_array($agent->role, ['Directeur Général'])                                     => 'bg-indigo-100 text-indigo-700',
                                in_array($agent->role, ['DGA'])                                                   => 'bg-blue-100 text-blue-700',
                                in_array($agent->role, ['Directeur de Direction'])                                => 'bg-sky-100 text-sky-700',
                                in_array($agent->role, ['Directeur Technique'])                                   => 'bg-cyan-100 text-cyan-700',
                                in_array($agent->role, ['Directeur de Caisse'])                                   => 'bg-teal-100 text-teal-700',
                                in_array($agent->role, ["Chef d'Agence", 'Chef de Guichet', 'Chef de Service'])   => 'bg-emerald-100 text-emerald-700',
                                in_array($agent->role, ['Assistante DG', 'Conseiller DG'])                        => 'bg-fuchsia-100 text-fuchsia-700',
                                str_contains($agent->role ?? '', 'Secrétaire')                                    => 'bg-rose-100 text-rose-700',
                                default                                                                           => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- # --}}
                            <td class="px-4 py-3.5 text-xs text-slate-300 font-medium">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Nom complet --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if ($agent->photo_path)
                                        <img src="{{ Storage::url($agent->photo_path) }}"
                                             alt="{{ $agent->prenom }}"
                                             class="h-9 w-9 shrink-0 rounded-xl object-cover ring-2 ring-white shadow-sm">
                                    @else
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $avatarGrad }} text-xs font-black text-white shadow-sm">
                                            {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm leading-tight">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                        @if ($agent->sexe)
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ ucfirst($agent->sexe) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Rôle --}}
                            <td class="px-4 py-3.5">
                                @if ($agent->role)
                                    <span class="inline-flex items-center rounded-lg {{ $roleBadge }} px-2.5 py-1 text-[10px] font-bold">
                                        {{ $roles[$agent->role] ?? $agent->role }}
                                    </span>
                                @else
                                    <span class="text-xs italic text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3.5">
                                <p class="text-xs text-slate-600 font-medium">{{ $agent->email ?: '—' }}</p>
                                @if ($agent->numero_telephone)
                                    <p class="text-[10px] text-slate-400 mt-0.5">{{ $agent->numero_telephone }}</p>
                                @endif
                            </td>

                            {{-- Affectation --}}
                            <td class="px-4 py-3.5">
                                @if ($lieu)
                                    <span class="inline-flex items-center rounded-lg {{ $affBadge }} px-2 py-0.5 text-[10px] font-bold">
                                        {{ $lieu }}
                                    </span>
                                    <p class="mt-0.5 text-[11px] text-slate-500 font-medium leading-tight max-w-[150px] truncate">{{ $libelle }}</p>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600">
                                        <i class="fas fa-exclamation text-[8px]"></i> Non affecté
                                    </span>
                                @endif
                            </td>

                            {{-- Prise de fonction --}}
                            <td class="px-4 py-3.5">
                                @if ($agent->date_debut_fonction)
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ $agent->date_debut_fonction->format('d/m/Y') }}
                                    </span>
                                @else
                                    <a href="{{ route('admin.agents.edit', $agent) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600 hover:bg-rose-100 transition">
                                        <i class="fas fa-exclamation text-[8px]"></i> À renseigner
                                    </a>
                                @endif
                            </td>

                            {{-- Compte --}}
                            <td class="px-4 py-3.5">
                                @if ($agent->user)
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Compte actif
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.agents.activate-account', $agent) }}">
                                        @csrf
                                        <button type="button"
                                                onclick="if(confirm('Créer le compte de {{ e($agent->prenom) }} {{ e($agent->nom) }} ?\nMot de passe par défaut : 11111111')) this.form.submit();"
                                                class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 transition">
                                            <i class="fas fa-user-plus text-[8px]"></i> Créer
                                        </button>
                                    </form>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.agents.show', $agent) }}"
                                       class="inline-flex h-[26px] w-[26px] items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm hover:border-emerald-200 hover:text-emerald-600 transition"
                                       title="Voir la fiche">
                                        <i class="fas fa-eye text-[9px]"></i>
                                    </a>
                                    <a href="{{ route('admin.agents.edit', $agent) }}"
                                       class="inline-flex h-[26px] w-[26px] items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm hover:border-blue-200 hover:text-blue-600 transition"
                                       title="Modifier">
                                        <i class="fas fa-pen text-[9px]"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}"
                                          onsubmit="return confirm('Supprimer {{ e($agent->prenom) }} {{ e($agent->nom) }} ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-[26px] w-[26px] items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 transition"
                                                title="Supprimer">
                                            <i class="fas fa-trash text-[9px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">
                {{ $agents->count() }} agent{{ $agents->count() > 1 ? 's' : '' }} affiché{{ $agents->count() > 1 ? 's' : '' }}
                @if($roleActive || $search) · <span class="text-blue-500 font-semibold">filtre actif</span>@endif
            </div>
        @endif

    </div>

</div>
</main>
@endsection
