@extends('layouts.app')

@section('title', $delegation->region.' — '.$delegation->ville.' | '.config('app.name', 'SGP-RCPB'))
@section('page_title', $delegation->region.' — '.$delegation->ville)

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        {{-- Status toast --}}
        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        @php
            $tabs = [
                'caisses' => ['label' => 'Caisses', 'icon' => 'fas fa-building-columns'],
                'agents'  => ['label' => 'Agents',  'icon' => 'fas fa-users'],
            ];
        @endphp

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ $delegation->region }} — {{ $delegation->ville }}</h1>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-400">
                        <i class="fas fa-map-pin text-xs"></i>
                        <span class="font-bold uppercase tracking-wider text-slate-500">Délégation Technique</span>
                        &bull;
                        <i class="fas fa-phone text-xs"></i>
                        {{ $delegation->secretariat_telephone ?: 'Tél. non renseigné' }}
                    </p>
                    @if ($delegation->villes->count())
                        <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-400">
                            <i class="fas fa-city text-[10px] text-emerald-400"></i>
                            <span class="text-slate-500">{{ $delegation->villes->pluck('nom')->join(' · ') }}</span>
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.delegations-techniques.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                    <a href="{{ route('admin.delegations-techniques.edit', $delegation) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-pen text-xs text-cyan-300"></i> Modifier
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-building-columns text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $delegation->caisses_count }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Caisses</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-users text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $delegation->agents_count }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Agents</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div>
            <div class="rounded-2xl bg-white shadow-sm">
                <div class="flex items-center gap-1 border-b border-slate-100 px-5 pt-4 pb-0">
                    @foreach ($tabs as $key => $tab)
                        <button type="button" data-deleg-tab-trigger="{{ $key }}" class="deleg-tab-trigger inline-flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 text-sm font-bold text-slate-400 transition hover:text-slate-600">
                            <i class="{{ $tab['icon'] }} text-xs"></i>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="p-5">
                    {{-- Caisses panel --}}
                    <div data-deleg-tab-panel="caisses">
                        <div class="mb-4">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="text" id="search-caisses" placeholder="Rechercher une caisse..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-cyan-400">
                            </div>
                        </div>
                        <div id="list-caisses">
                        @forelse ($caisses as $caisse)
                            <a href="{{ route('admin.caisses.show', $caisse) }}" class="flex items-center justify-between border-b border-slate-50 py-3 -mx-2 px-2 rounded-lg transition hover:bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                    <div>
                                        <span class="text-sm font-semibold text-slate-700">{{ $caisse->nom }}</span>
                                        <p class="text-xs text-slate-400">
                                            {{ $caisse->ville?->nom ?? '' }}{{ $caisse->quartier ? ($caisse->ville ? ', ' : '') . $caisse->quartier : '' }}
                                            @if ($caisse->annee_ouverture)
                                                &bull; Ouverture {{ $caisse->annee_ouverture }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400">
                                        {{ $caisse->directeur_sexe === 'Feminin' ? 'Mme.' : 'M.' }}
                                        {{ trim(($caisse->directeur_prenom ?? '').' '.($caisse->directeur_nom ?? '')) ?: 'N/D' }}
                                    </span>
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </div>
                            </a>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-400">Aucune caisse enregistrée.</p>
                        @endforelse
                        </div>
                        <p id="no-result-caisses" class="hidden py-6 text-center text-sm text-slate-400">Aucun résultat.</p>

                        <div class="mt-4 flex items-center gap-3">
                            <button type="button" onclick="document.getElementById('caisse-form').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                                <i class="fas fa-plus text-[10px]"></i> Ajouter une caisse
                            </button>
                        </div>
                    </div>

                    {{-- Agents panel --}}
                    <div data-deleg-tab-panel="agents" class="hidden">
                        <div class="mb-4">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                <input type="text" id="search-agents" placeholder="Rechercher un agent..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-sm text-slate-700 placeholder-slate-400 focus:border-cyan-400 focus:bg-white focus:ring-cyan-400">
                            </div>
                        </div>
                        <div id="list-agents">
                        @forelse ($agents as $agent)
                            <a href="{{ route('admin.agents.show', $agent) }}" class="flex items-center justify-between border-b border-slate-50 py-3 -mx-2 px-2 rounded-lg transition hover:bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    <div>
                                        <span class="text-sm font-semibold text-slate-700">{{ trim(($agent->prenom ?? '').' '.($agent->nom ?? '')) ?: 'Agent' }}</span>
                                        @if ($agent->fonction)
                                            <p class="text-xs text-slate-400">{{ $agent->fonction }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($agent->email)
                                        <span class="text-xs text-slate-400">{{ $agent->email }}</span>
                                    @endif
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </div>
                            </a>
                        @empty
                            <p class="py-6 text-center text-sm text-slate-400">Aucun agent enregistré.</p>
                        @endforelse
                        </div>
                        <p id="no-result-agents" class="hidden py-6 text-center text-sm text-slate-400">Aucun résultat.</p>

                        <div class="mt-4 flex items-center gap-3">
                            <button type="button" onclick="document.getElementById('agent-form').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600">
                                <i class="fas fa-plus text-[10px]"></i> Ajouter un agent
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom info bar --}}
        <div class="rounded-2xl bg-white shadow-sm">
            <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                <div class="flex items-center gap-4 p-5">
                    @if (!empty($delegation->directeur_photo_path))
                        <img src="{{ asset('storage/'.$delegation->directeur_photo_path) }}" alt="Directeur" class="h-10 w-10 rounded-full object-cover shadow-sm ring-2 ring-white">
                    @else
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-500 text-xs font-black text-white shadow-sm ring-2 ring-white">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    @endif
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $delegation->directeur_sexe === 'Feminin' ? 'Directrice Régionale' : 'Directeur Régional' }}</p>
                        <p class="mt-0.5 text-sm font-bold text-slate-800">{{ trim(($delegation->directeur_prenom ?? '').' '.($delegation->directeur_nom ?? '')) ?: 'Non renseigné' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                        <i class="fas fa-building-columns text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Caisses populaires</p>
                        <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $delegation->caisses_count }} caisse(s) rattachée(s)</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-500">
                        <i class="fas fa-clock text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dernière mise à jour</p>
                        <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $delegation->updated_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Caisse creation modal --}}
<div id="caisse-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('caisse-form').classList.add('hidden')"></div>
    <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Nouvelle caisse</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter une Caisse</h2>
        </div>

        <form method="POST" action="{{ route('admin.delegations-techniques.caisses.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="delegation_technique_id" value="{{ $delegation->id }}">

            <div>
                <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                    <i class="fas fa-map-marker-alt text-emerald-500"></i>
                    Informations de la Caisse
                </h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom de la caisse <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Caisse Populaire de Koudougou">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Année d'ouverture <span class="text-rose-500">*</span></label>
                        <input type="text" name="annee_ouverture" value="{{ old('annee_ouverture') }}" required maxlength="4" pattern="\d{4}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="2020">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville <span class="text-rose-500">*</span></label>
                        <select name="ville_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Choisir --</option>
                            @foreach ($delegation->villes as $ville)
                                <option value="{{ $ville->id }}" {{ (int) old('ville_id') === $ville->id ? 'selected' : '' }}>{{ $ville->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Quartier</label>
                        <input type="text" name="quartier" value="{{ old('quartier') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Secteur 5">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                        <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="+226 XX XX XX XX">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                    <i class="fas fa-user-tie text-sky-500"></i>
                    Directeur de Caisse
                </h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                        <input type="text" name="directeur_prenom" value="{{ old('directeur_prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                        <input type="text" name="directeur_nom" value="{{ old('directeur_nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                        <select name="directeur_sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" {{ old('directeur_sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                            <option value="Feminin" {{ old('directeur_sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="directeur_email" value="{{ old('directeur_email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                        <input type="text" name="directeur_telephone" value="{{ old('directeur_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois) <span class="text-rose-500">*</span></label>
                        <input type="month" name="directeur_date_debut_mois" value="{{ old('directeur_date_debut_mois') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                    <i class="fas fa-user-pen text-fuchsia-500"></i>
                    Secrétaire du Directeur
                </h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                        <input type="text" name="secretaire_prenom" value="{{ old('secretaire_prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                        <input type="text" name="secretaire_nom" value="{{ old('secretaire_nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                        <select name="secretaire_sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" {{ old('secretaire_sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                            <option value="Feminin" {{ old('secretaire_sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="secretaire_email" value="{{ old('secretaire_email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                        <input type="text" name="secretaire_telephone" value="{{ old('secretaire_telephone') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois) <span class="text-rose-500">*</span></label>
                        <input type="month" name="secretaire_date_debut_mois" value="{{ old('secretaire_date_debut_mois') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-emerald-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fas fa-check"></i> Enregistrer
                </button>
                <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Agent creation modal --}}
<div id="agent-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('agent-form').classList.add('hidden')"></div>
    <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('agent-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-orange-500">Nouvel agent</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter un Agent</h2>
        </div>

        <form method="POST" action="{{ route('admin.delegations-techniques.agents.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="delegation_technique_id" value="{{ $delegation->id }}">

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                    <select name="sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                        <option value="">-- Choisir --</option>
                        <option value="Masculin" {{ old('sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                        <option value="Feminin" {{ old('sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Fonction <span class="text-rose-500">*</span></label>
                    <input type="text" name="fonction" value="{{ old('fonction') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400" placeholder="Ex: Caissier, Comptable...">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                    <input type="text" name="numero_telephone" value="{{ old('numero_telephone') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Date début fonction</label>
                    <input type="month" name="date_debut_fonction" value="{{ old('date_debut_fonction') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-orange-500 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-orange-600">
                    <i class="fas fa-check"></i> Enregistrer
                </button>
                <button type="button" onclick="document.getElementById('agent-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('[data-deleg-tab-trigger]');
    const panels   = document.querySelectorAll('[data-deleg-tab-panel]');

    function activate(key) {
        triggers.forEach(function (btn) {
            const active = btn.dataset.delegTabTrigger === key;
            btn.classList.toggle('bg-blue-50', active);
            btn.classList.toggle('text-blue-600', active);
            btn.classList.toggle('border-b-2', active);
            btn.classList.toggle('border-blue-500', active);
            btn.classList.toggle('text-slate-400', !active);
        });
        panels.forEach(function (p) {
            p.classList.toggle('hidden', p.dataset.delegTabPanel !== key);
        });
    }

    triggers.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activate(this.dataset.delegTabTrigger);
        });
    });

    activate('caisses');

    // Search filtering
    function setupSearch(inputId, listId, noResultId) {
        var input = document.getElementById(inputId);
        var list = document.getElementById(listId);
        var noResult = document.getElementById(noResultId);
        if (!input || !list) return;

        input.addEventListener('input', function () {
            var term = this.value.toLowerCase().trim();
            var items = list.querySelectorAll(':scope > a, :scope > div');
            var visible = 0;
            items.forEach(function (item) {
                var text = item.textContent.toLowerCase();
                var match = !term || text.indexOf(term) !== -1;
                item.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (noResult) noResult.classList.toggle('hidden', visible > 0);
        });
    }

    setupSearch('search-caisses', 'list-caisses', 'no-result-caisses');
    setupSearch('search-agents', 'list-agents', 'no-result-agents');
});
</script>
@endpush
