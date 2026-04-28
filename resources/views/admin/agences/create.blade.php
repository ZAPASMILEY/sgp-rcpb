@extends('layouts.app')

@section('title', 'Nouvelle agence | '.config('app.name', 'SGP-RCPB'))

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
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle agence</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez le chef d'agence, sa secretaire, la delegation et le directeur de caisse superviseur.</p>
                    </div>
                    <a href="{{ route('admin.agences.index') }}" target="_top" class="ent-btn ent-btn-soft">Index agences</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($delegations->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Configurez d'abord au moins une delegation technique.
                    </div>
                @endif

                @if ($caisses->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Ajoutez d'abord une caisse pour pouvoir choisir un superviseur.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agences.store') }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom de l'agence</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Agence Ouaga Centre">
                    </div>

                    <div class="space-y-2">
                        <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation Technique</label>
                        <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                            <option value="">Selectionner une delegation</option>
                            @foreach ($delegations as $delegation)
                                <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id') === (string) $delegation->id)>
                                    {{ $delegation->region }} / {{ $delegation->ville }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Le directeur de caisse superviseur sera filtre selon cette delegation.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="chef_agent_id" class="text-sm font-semibold text-slate-700">Chef d'agence</label>
                        @if($chefs->isEmpty())
                            <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Chef d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select id="chef_agent_id" name="chef_agent_id" class="ent-select">
                            <option value="">— Aucun chef pour l'instant —</option>
                            @foreach ($chefs as $agent)
                                <option value="{{ $agent->id }}" @selected(old('chef_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="secretaire_agent_id" class="text-sm font-semibold text-slate-700">Secretaire d'agence</label>
                        @if($secretaires->isEmpty())
                            <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Secrétaire d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select id="secretaire_agent_id" name="secretaire_agent_id" class="ent-select">
                            <option value="">— Aucune secretaire pour l'instant —</option>
                            @foreach ($secretaires as $agent)
                                <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id') == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="caisse_id" class="text-sm font-semibold text-slate-700">Directeur de caisse superviseur</label>
                        <select id="caisse_id" name="caisse_id" required class="ent-select" @disabled($caisses->isEmpty() || $delegations->isEmpty())>
                            <option value="">Choisissez d'abord une delegation</option>
                            @foreach ($caisses as $caisse)
                                <option value="{{ $caisse->id }}"
                                    data-delegation-id="{{ $caisse->delegation_technique_id }}"
                                    @selected((string) old('caisse_id') === (string) $caisse->id)>
                                    {{ $caisse->nom }}{{ $caisse->directeur ? ' — '.$caisse->directeur->nom.' '.$caisse->directeur->prenom : '' }}
                                    @if ($caisse->delegationTechnique)
                                        - {{ $caisse->delegationTechnique->region }} / {{ $caisse->delegationTechnique->ville }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" @disabled($caisses->isEmpty() || $delegations->isEmpty()) class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm disabled:cursor-not-allowed disabled:opacity-60">
                        Enregistrer l'agence
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var delegationSelect = document.getElementById('delegation_technique_id');
            var superviseurSelect = document.getElementById('caisse_id');

            if (!delegationSelect || !superviseurSelect) {
                return;
            }

            function filterSuperviseursByDelegation() {
                var delegationId = delegationSelect.value;
                var hasMatch = false;

                Array.from(superviseurSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.textContent = delegationId ? 'Selectionner un superviseur' : "Choisissez d'abord une delegation";
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    var isMatch = option.getAttribute('data-delegation-id') === delegationId;
                    option.hidden = !isMatch;
                    option.disabled = !isMatch;

                    if (!isMatch && option.selected) {
                        option.selected = false;
                    }

                    if (isMatch) {
                        hasMatch = true;
                    }
                });

                if (!delegationId || !hasMatch) {
                    superviseurSelect.value = '';
                }

                superviseurSelect.disabled = !delegationId || !hasMatch;
            }

            delegationSelect.addEventListener('change', filterSuperviseursByDelegation);
            filterSuperviseursByDelegation();
        });
    </script>
@endpush
