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
                        <p class="mt-2 text-sm text-slate-600">Renseignez le chef d'agence, sa secretaire et la caisse d'appartenance.</p>
                    </div>
                    <a href="{{ route('admin.agences.index') }}" target="_top" class="ent-btn ent-btn-soft">Index agences</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($caisses->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Ajoutez d'abord une caisse pour pouvoir choisir un superviseur.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agences.store') }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="nom" class="text-sm font-semibold text-slate-700">Nom de l'agence <span class="text-rose-500">*</span></label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Agence Ouaga Centre">
                        </div>
                        <div class="space-y-2">
                            <label for="telephone_accueil" class="text-sm font-semibold text-slate-700">Téléphone d'accueil <span class="text-rose-500">*</span></label>
                            <input id="telephone_accueil" name="telephone_accueil" type="text" value="{{ old('telephone_accueil') }}" required class="ent-input" placeholder="+226 25 00 00 00">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="chef_agent_id" class="text-sm font-semibold text-slate-700">Chef d'agence</label>
                        @if($chefs->isEmpty())
                            @if(($totalChefs ?? 0) === 0)
                                <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                    <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                    <span>Aucun agent avec le rôle <strong>Chef d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                                </div>
                            @else
                                <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                                    <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                                    <span>Tous les agents <strong>Chef d'Agence</strong> ({{ $totalChefs }}) sont déjà affectés à une agence. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
                                </div>
                            @endif
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
                            @if(($totalSecretaires ?? 0) === 0)
                                <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                    <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                    <span>Aucun agent avec le rôle <strong>Secrétaire d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                                </div>
                            @else
                                <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                                    <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                                    <span>Toutes les agents <strong>Secrétaire d'Agence</strong> ({{ $totalSecretaires }}) sont déjà affectées à une agence. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
                                </div>
                            @endif
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
                        <label for="caisse_id" class="text-sm font-semibold text-slate-700">Caisse d'appartenance <span class="text-rose-500">*</span></label>
                        <select id="caisse_id" name="caisse_id" required class="ent-select" @disabled($caisses->isEmpty())>
                            <option value="">— Sélectionner une caisse —</option>
                            @foreach ($caisses as $caisse)
                                <option value="{{ $caisse->id }}" @selected((string) old('caisse_id') === (string) $caisse->id)>
                                    {{ $caisse->nom }}
                                    @if($caisse->delegationTechnique) — {{ $caisse->delegationTechnique->region }} / {{ $caisse->delegationTechnique->ville }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Directeur de caisse superviseur</label>
                        <input id="directeur_display" type="text" readonly
                               class="ent-input bg-slate-100 cursor-not-allowed text-slate-500"
                               placeholder="Sélectionnez d'abord une caisse"
                               value="{{ old('caisse_id') ? ($caisses->find(old('caisse_id'))?->directeur ? $caisses->find(old('caisse_id'))->directeur->prenom.' '.$caisses->find(old('caisse_id'))->directeur->nom : 'Aucun directeur désigné') : '' }}">
                    </div>

                    <button type="submit" @disabled($caisses->isEmpty()) class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm disabled:cursor-not-allowed disabled:opacity-60">
                        Enregistrer l'agence
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('head')
    <style>
        .ts-wrapper .ts-control { background: rgb(241 245 249); border: none; border-radius: 12px; padding: 0.6rem 1rem; font-weight: 700; color: #334155; box-shadow: none; }
        .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #22d3ee; }
        .ts-dropdown { border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .ts-dropdown .option { padding: 10px 16px; font-weight: 600; font-size: 14px; }
        .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #ecfeff; color: #0e7490; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['chef_agent_id', 'secretaire_agent_id'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) new TomSelect(el, { placeholder: 'Rechercher un agent...', allowEmptyOption: true, maxOptions: 50, dropdownParent: 'body' });
            });

            var caisseDirecteurMap = {
                @foreach($caisses as $c)
                    "{{ $c->id }}": "{{ $c->directeur ? addslashes($c->directeur->prenom.' '.$c->directeur->nom) : '' }}",
                @endforeach
            };

            var caisseSelect   = document.getElementById('caisse_id');
            var directeurInput = document.getElementById('directeur_display');

            if (caisseSelect && directeurInput) {
                new TomSelect(caisseSelect, {
                    placeholder: 'Rechercher une caisse...',
                    allowEmptyOption: true,
                    dropdownParent: 'body',
                    onChange: function (val) {
                        var nom = caisseDirecteurMap[val] || '';
                        directeurInput.value = nom || (val ? 'Aucun directeur désigné' : '');
                        directeurInput.placeholder = val ? 'Aucun directeur désigné' : 'Sélectionnez d\'abord une caisse';
                    },
                });
            }
        });
    </script>
@endpush
