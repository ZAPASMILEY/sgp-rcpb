@extends('layouts.app')

@section('title', 'Modifier caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window h-full w-full p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre de modification</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modification</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier la caisse</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour les coordonnees du directeur, du secretariat et du superviseur technique.</p>
                    </div>
                    <a href="{{ route('admin.caisses.index') }}" target="_top" class="ent-btn ent-btn-soft">Index caisses</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.caisses.update', $caisse) }}" target="_top" class="mt-6 grid gap-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation Technique</label>
                        <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                            <option value="">Selectionner une delegation</option>
                            @php
                                $selectedDelegationId = (string) old('delegation_technique_id', $caisse->superviseur?->delegation_technique_id);
                            @endphp
                            @foreach ($delegations as $delegation)
                                <option value="{{ $delegation->id }}" @selected($selectedDelegationId === (string) $delegation->id)>
                                    {{ $delegation->region }} / {{ $delegation->ville }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Le superviseur est filtre selon cette delegation.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom de la caisse</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom', $caisse->nom) }}" required class="ent-input" placeholder="Ex: Caisse de Ouagadougou Centre">
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="annee_ouverture" class="text-sm font-semibold text-slate-700">Année d'ouverture <span class="text-rose-500">*</span></label>
                            <input id="annee_ouverture" name="annee_ouverture" type="text" value="{{ old('annee_ouverture', $caisse->annee_ouverture) }}" required maxlength="4" class="ent-input" placeholder="Ex: 2010">
                        </div>
                        <div class="space-y-2">
                            <label for="quartier" class="text-sm font-semibold text-slate-700">Quartier</label>
                            <input id="quartier" name="quartier" type="text" value="{{ old('quartier', $caisse->quartier) }}" class="ent-input" placeholder="Ex: Secteur 5">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                        <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone', $caisse->secretariat_telephone) }}" class="ent-input" placeholder="+226 25 00 00 00">
                    </div>

                    <div class="space-y-2">
                        <label for="directeur_agent_id" class="text-sm font-semibold text-slate-700">Directeur de caisse</label>
                        @if($directeurs->isEmpty())
                            <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Directeur de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select id="directeur_agent_id" name="directeur_agent_id" class="ent-select">
                            <option value="">— Aucun directeur pour l'instant —</option>
                            @foreach ($directeurs as $agent)
                                <option value="{{ $agent->id }}" @selected(old('directeur_agent_id', $caisse->directeur_agent_id) == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="secretaire_agent_id" class="text-sm font-semibold text-slate-700">Secrétaire de caisse</label>
                        @if($secretaires->isEmpty())
                            <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Secrétaire de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select id="secretaire_agent_id" name="secretaire_agent_id" class="ent-select">
                            <option value="">— Aucune secrétaire pour l'instant —</option>
                            @foreach ($secretaires as $agent)
                                <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id', $caisse->secretaire_agent_id) == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="direction_id" class="text-sm font-semibold text-slate-700">Direction superviseur</label>
                        <select id="direction_id" name="direction_id" class="ent-select">
                            <option value="">— Aucune direction superviseur —</option>
                            @foreach ($directions as $direction)
                                <option value="{{ $direction->id }}" @selected(old('direction_id', $caisse->direction_id) == $direction->id)>
                                    {{ $direction->nom }}{{ $direction->directeur ? ' — '.$direction->directeur->nom.' '.$direction->directeur->prenom : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

