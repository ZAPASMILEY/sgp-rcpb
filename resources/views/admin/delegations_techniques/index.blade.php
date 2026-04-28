@extends('layouts.app')

@section('title', 'Délégations Techniques | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Délégations Techniques')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">
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

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $kpis = [
                ['label' => 'Caisses', 'count' => $stats['caisses'], 'gradient' => 'from-emerald-400 to-teal-500', 'icon' => 'fas fa-building-columns', 'form' => 'caisse-form'],
                ['label' => 'Services', 'count' => $stats['services'], 'gradient' => 'from-sky-500 to-blue-600', 'icon' => 'fas fa-briefcase', 'form' => 'service-form'],
                ['label' => 'Agents', 'count' => $stats['agents'], 'gradient' => 'from-amber-400 to-orange-500', 'icon' => 'fas fa-users', 'form' => 'agent-form'],
            ];
        @endphp

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Délégations Techniques</h1>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-400">
                        <span class="font-bold uppercase tracking-wider text-slate-500">Pilotage régional</span>
                        <i class="fas fa-map text-xs"></i>
                        {{ $stats['delegations'] }} région(s) configurée(s)
                    </p>
                </div>
                @if ($delegations->count() < 3)
                    <button type="button" onclick="document.getElementById('creation-form').classList.toggle('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-plus text-xs text-cyan-300"></i> Ajouter une Délégation
                    </button>
                @endif
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-3 gap-4">
            @foreach ($kpis as $kpi)
                <div class="rounded-2xl bg-gradient-to-br {{ $kpi['gradient'] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="text-3xl font-black">{{ $kpi['count'] }}</span>
                        <button type="button" onclick="document.getElementById('{{ $kpi['form'] }}').classList.toggle('hidden')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white/90 text-slate-700 transition hover:scale-105 cursor-pointer" title="Ajouter">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                    <p class="mt-2 text-sm font-bold uppercase tracking-wider">{{ $kpi['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Creation form (hidden by default) --}}
        @if ($delegations->count() < 3)
            <section id="creation-form" class="hidden rounded-2xl bg-white p-6 shadow-sm lg:p-8">
                <div class="mb-6">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-500">Nouvelle délégation</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Créer une Délégation Technique</h2>
                </div>

                <form method="POST" action="{{ route('admin.delegations-techniques.store') }}" class="space-y-8">
                    @csrf

                    {{-- Info delegation --}}
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                            <i class="fas fa-map-marker-alt text-cyan-500"></i>
                            Informations de la Délégation
                        </h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Région <span class="text-rose-500">*</span></label>
                                <input type="text" name="region" value="{{ old('region') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400" placeholder="Ex: Centre-Ouest">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville <span class="text-rose-500">*</span></label>
                                <input type="text" name="ville" value="{{ old('ville') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400" placeholder="Ex: Koudougou">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                                <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400" placeholder="+226 XX XX XX XX">
                            </div>
                        </div>
                    </div>

                    {{-- Directeur regional --}}
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                            <i class="fas fa-user-tie text-sky-500"></i>
                            Directeur Régional
                        </h3>
                        @if($directeurs->isEmpty())
                            <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Directeur Technique</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select name="directeur_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                            <option value="">— Aucun directeur pour l'instant —</option>
                            @foreach ($directeurs as $agent)
                                <option value="{{ $agent->id }}" @selected(old('directeur_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Secretaire --}}
                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                            <i class="fas fa-user-pen text-fuchsia-500"></i>
                            Secrétaire de la Direction Régionale
                        </h3>
                        @if($secretaires->isEmpty())
                            <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Secrétaire Technique</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select name="secretaire_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                            <option value="">— Aucune secrétaire pour l'instant —</option>
                            @foreach ($secretaires as $agent)
                                <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-cyan-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-700">
                            <i class="fas fa-check"></i> Enregistrer
                        </button>
                        <button type="button" onclick="document.getElementById('creation-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </section>
        @endif

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

                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                            <i class="fas fa-map-marker-alt text-emerald-500"></i>
                            Informations de la Caisse
                        </h3>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                                <select name="delegation_technique_id" id="caisse-delegation-select" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($delegations as $d)
                                        <option value="{{ $d->id }}" {{ (int) old('delegation_technique_id') === $d->id ? 'selected' : '' }}>{{ $d->region }} — {{ $d->ville }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville <span class="text-rose-500">*</span></label>
                                <select name="ville_id" id="caisse-ville-select" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                    <option value="">-- Choisir une délégation d'abord --</option>
                                    @foreach ($delegations as $d)
                                        @foreach ($d->villes as $v)
                                            <option value="{{ $v->id }}" data-delegation="{{ $d->id }}" class="hidden" {{ (int) old('ville_id') === $v->id ? 'selected' : '' }}>{{ $v->nom }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom de la caisse <span class="text-rose-500">*</span></label>
                                <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Caisse Populaire de Koudougou">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Année d'ouverture <span class="text-rose-500">*</span></label>
                                <input type="text" name="annee_ouverture" value="{{ old('annee_ouverture') }}" required maxlength="4" pattern="\d{4}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="2020">
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
                        @if($directeurs_caisse->isEmpty())
                            <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Directeur de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select name="directeur_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">— Aucun directeur pour l'instant —</option>
                            @foreach ($directeurs_caisse as $agent)
                                <option value="{{ $agent->id }}" @selected(old('directeur_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                            <i class="fas fa-user-pen text-fuchsia-500"></i>
                            Secrétaire du Directeur
                        </h3>
                        @if($secretaires_caisse->isEmpty())
                            <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Secrétaire de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select name="secretaire_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">— Aucune secrétaire pour l'instant —</option>
                            @foreach ($secretaires_caisse as $agent)
                                <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-emerald-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                            <i class="fas fa-check"></i>
                            Enregistrer
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

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                            <select name="delegation_technique_id" id="agent-delegation-select" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                                <option value="">-- Choisir --</option>
                                @foreach ($delegations as $d)
                                    <option value="{{ $d->id }}" {{ (int) old('delegation_technique_id') === $d->id ? 'selected' : '' }}>{{ $d->region }} — {{ $d->ville }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Service</label>
                            <select name="service_id" id="agent-service-select" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                                <option value="">-- Aucun --</option>
                                @foreach ($services as $srv)
                                    <option value="{{ $srv->id }}" data-delegation="{{ $srv->delegation_technique_id }}" {{ (int) old('service_id') === $srv->id ? 'selected' : '' }}>{{ $srv->nom }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <i class="fas fa-check"></i>
                            Enregistrer
                        </button>
                        <button type="button" onclick="document.getElementById('agent-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Service creation modal --}}
        <div id="service-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('service-form').classList.add('hidden')"></div>
            <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
                <button type="button" onclick="document.getElementById('service-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                    <i class="fas fa-times"></i>
                </button>

                <div class="mb-6">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-blue-500">Nouveau service</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter un Service</h2>
                </div>

                <form method="POST" action="{{ route('admin.delegations-techniques.services.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                            <i class="fas fa-map-marker-alt text-blue-500"></i>
                            Informations du Service
                        </h3>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                                <select name="delegation_technique_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($delegations as $d)
                                        <option value="{{ $d->id }}" {{ (int) old('delegation_technique_id') === $d->id ? 'selected' : '' }}>{{ $d->region }} — {{ $d->ville }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom du service <span class="text-rose-500">*</span></label>
                                <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400" placeholder="Ex: Service Comptabilité">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                            <i class="fas fa-user-tie text-blue-500"></i>
                            Chef de Service
                        </h3>
                        <select name="chef_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400">
                            <option value="">— Aucun chef pour l'instant —</option>
                            @foreach ($chefs_service as $agent)
                                <option value="{{ $agent->id }}" @selected(old('chef_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-blue-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-blue-700">
                            <i class="fas fa-check"></i>
                            Enregistrer
                        </button>
                        <button type="button" onclick="document.getElementById('service-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Two-column: Delegation list + Directors structure --}}
        @if ($delegations->isNotEmpty())
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                {{-- Left: Delegation list --}}
                <div class="rounded-2xl bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700">
                            <i class="fas fa-list-ul text-cyan-500"></i>
                            Délégations
                            <span class="ml-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-cyan-100 text-xs font-black text-cyan-700">{{ $delegations->count() }}</span>
                        </div>
                    </div>

                    <div class="p-5">
                        @foreach ($delegations as $delegation)
                            <div class="flex items-center justify-between border-b border-slate-50 py-3 -mx-2 px-2 rounded-lg transition hover:bg-slate-50">
                                <a href="{{ route('admin.delegations-techniques.show', $delegation) }}" class="flex items-center gap-3 flex-1 min-w-0">
                                    <span class="h-2 w-2 rounded-full bg-cyan-500"></span>
                                    <div>
                                        <span class="text-sm font-semibold text-slate-700">{{ $delegation->region }} — {{ $delegation->ville }}</span>
                                        <p class="text-xs text-slate-400">
                                            <i class="fas fa-phone mr-1"></i>{{ $delegation->secretariat_telephone ?: 'Tél. non renseigné' }}
                                            &bull; {{ $delegation->caisses_count ?? 0 }} caisse(s)
                                            &bull; <span class="font-bold text-slate-300">Note : —</span>
                                        </p>
                                    </div>
                                </a>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.delegations-techniques.show', $delegation) }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600" title="Voir">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.delegations-techniques.edit', $delegation) }}" class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-cyan-50 hover:text-cyan-600" title="Modifier">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.delegations-techniques.destroy', $delegation) }}" onsubmit="return confirm('Supprimer cette délégation ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" title="Supprimer">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Directors structure --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-800">Directeurs Régionaux</h2>
                    <div class="mt-6 flex flex-wrap justify-center gap-6">
                        @php
                            $dirColors = [
                                ['bg' => 'bg-rose-500', 'badge' => 'text-rose-300'],
                                ['bg' => 'bg-cyan-500', 'badge' => 'text-cyan-300'],
                                ['bg' => 'bg-fuchsia-500', 'badge' => 'text-fuchsia-300'],
                            ];
                        @endphp
                        @foreach ($delegations as $index => $delegation)
                            @php
                                $color = $dirColors[$index % 3];
                                $nomComplet = trim(($delegation->directeur_prenom ?? '').' '.($delegation->directeur_nom ?? ''));
                                $sexe = $delegation->directeur_sexe ?? null;
                            @endphp
                            <div class="flex flex-col items-center text-center" style="max-width: 110px;">
                                @if (!empty($delegation->directeur_photo_path))
                                    <img src="{{ asset('storage/'.$delegation->directeur_photo_path) }}" alt="Directeur {{ $delegation->region }}" class="h-16 w-16 rounded-full object-cover shadow-md ring-2 ring-white">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full {{ $color['bg'] }} text-sm font-black text-white shadow-md ring-2 ring-white">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                @endif
                                <p class="mt-3 text-sm font-black text-slate-800 leading-tight">
                                    {{ $sexe === 'Feminin' ? 'Mme.' : 'M.' }}
                                    {{ Str::afterLast($nomComplet, ' ') ?: 'N/D' }}
                                </p>
                                <p class="mt-1 text-[11px] leading-tight text-slate-400">DR {{ $delegation->region }}</p>
                                @if ($delegation->directeur_email)
                                    <p class="mt-0.5 text-[10px] leading-tight text-slate-300 truncate w-full">{{ $delegation->directeur_email }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bottom info bar --}}
            <div class="rounded-2xl bg-white shadow-sm">
                <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-500">
                            <i class="fas fa-map text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Couverture</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $stats['delegations'] }} région(s) active(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                            <i class="fas fa-building-columns text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Caisses populaires</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $stats['caisses'] }} caisse(s) rattachée(s)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-500">
                            <i class="fas fa-bullseye text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mission</p>
                            <p class="mt-0.5 text-sm font-semibold italic text-slate-600">"Pilotage régional efficace"</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-8 py-16 text-center shadow-sm">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-4xl text-slate-300">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-slate-800">Aucune Délégation Technique</h2>
                <p class="mx-auto mt-3 max-w-md text-sm text-slate-500">Aucune délégation n'est encore configurée. Cliquez sur le bouton ci-dessus pour en créer une.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Agent: filter services by delegation
    const delegSelect = document.getElementById('agent-delegation-select');
    const serviceSelect = document.getElementById('agent-service-select');
    if (delegSelect && serviceSelect) {
        const allOptions = Array.from(serviceSelect.querySelectorAll('option[data-delegation]'));

        function filterServices() {
            const delegId = delegSelect.value;
            const current = serviceSelect.value;
            serviceSelect.querySelectorAll('option[data-delegation]').forEach(o => o.remove());

            allOptions.forEach(function (opt) {
                if (!delegId || opt.dataset.delegation === delegId) {
                    serviceSelect.appendChild(opt);
                }
            });

            serviceSelect.value = serviceSelect.querySelector('option[value="' + current + '"]') ? current : '';
        }

        delegSelect.addEventListener('change', filterServices);
        filterServices();
    }

    // Caisse: filter villes by delegation
    const caisseDelegSelect = document.getElementById('caisse-delegation-select');
    const villeSelect = document.getElementById('caisse-ville-select');
    if (caisseDelegSelect && villeSelect) {
        const allVilleOptions = Array.from(villeSelect.querySelectorAll('option[data-delegation]'));

        function filterVilles() {
            const delegId = caisseDelegSelect.value;
            const current = villeSelect.value;
            villeSelect.querySelectorAll('option[data-delegation]').forEach(o => o.remove());

            const placeholder = villeSelect.querySelector('option[value=""]');
            placeholder.textContent = delegId ? '-- Choisir --' : '-- Choisir une délégation d\'abord --';

            allVilleOptions.forEach(function (opt) {
                if (delegId && opt.dataset.delegation === delegId) {
                    opt.classList.remove('hidden');
                    villeSelect.appendChild(opt);
                }
            });

            villeSelect.value = villeSelect.querySelector('option[value="' + current + '"]') ? current : '';
        }

        caisseDelegSelect.addEventListener('change', filterVilles);
        filterVilles();
    }
});
</script>
@endpush
