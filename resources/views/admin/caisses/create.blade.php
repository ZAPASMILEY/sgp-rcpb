@extends('layouts.app')

@section('title', 'Nouvelle caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-3xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in zoom-in duration-300">
        <a href="{{ route('admin.caisses.index') }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>
        <div class="bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-[#22d3ee] font-black shadow-sm">
                <i class="fas fa-piggy-bank"></i>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouvelle caisse</h1>
        </div>
        <form method="POST" action="{{ route('admin.caisses.store') }}" class="p-8 lg:p-12 space-y-8">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <label for="delegation_technique_id" class="text-xs font-bold text-slate-400 ml-1">Délégation Technique <span class="text-rose-500">*</span></label>
                    <select id="delegation_technique_id" name="delegation_technique_id" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                        <option value="">Sélectionner une délégation</option>
                        @foreach ($delegations as $delegation)
                            <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id') === (string) $delegation->id)>
                                {{ $delegation->region }} / {{ $delegation->ville }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-4">
                    <label for="ville_id" class="text-xs font-bold text-slate-400 ml-1">Ville <span class="text-rose-500">*</span></label>
                    <select id="ville_id" name="ville_id" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                        <option value="">— Choisir d'abord la délégation —</option>
                        @foreach ($villes as $ville)
                            <option value="{{ $ville->id }}" data-delegation="{{ $ville->delegation_technique_id }}" @selected((string) old('ville_id') === (string) $ville->id)>
                                {{ $ville->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="space-y-4">
                <label for="nom" class="text-xs font-bold text-slate-400 ml-1">Nom de la caisse</label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300" placeholder="Ex: Caisse de Ouagadougou Centre">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="annee_ouverture" class="text-xs font-bold text-slate-400 ml-1">Année d'ouverture <span class="text-rose-500">*</span></label>
                    <input id="annee_ouverture" name="annee_ouverture" type="text" value="{{ old('annee_ouverture') }}" required maxlength="4" class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Ex: 2010">
                </div>
                <div class="space-y-2">
                    <label for="quartier" class="text-xs font-bold text-slate-400 ml-1">Quartier</label>
                    <input id="quartier" name="quartier" type="text" value="{{ old('quartier') }}" class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Ex: Secteur 5">
                </div>
            </div>
            <div class="space-y-2">
                <label for="secretariat_telephone" class="text-xs font-bold text-slate-400 ml-1">Numéro d'accueil <span class="text-rose-500">*</span></label>
                <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="+226 25 00 00 00">
            </div>
            <div class="space-y-2">
                <label for="directeur_agent_id" class="text-xs font-bold text-slate-400 ml-1">Directeur de caisse</label>
                @if($directeurs->isEmpty())
                    @if(($totalDirecteurs ?? 0) === 0)
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec le rôle <strong>Directeur de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                            <span>Tous les agents <strong>Directeur de Caisse</strong> ({{ $totalDirecteurs }}) sont déjà affectés à une caisse. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
                        </div>
                    @endif
                @endif
                <select id="directeur_agent_id" name="directeur_agent_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">— Aucun directeur pour l'instant —</option>
                    @foreach ($directeurs as $agent)
                        <option value="{{ $agent->id }}" @selected(old('directeur_agent_id') == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label for="secretaire_agent_id" class="text-xs font-bold text-slate-400 ml-1">Secrétaire de caisse</label>
                @if($secretaires->isEmpty())
                    @if(($totalSecretaires ?? 0) === 0)
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec le rôle <strong>Secrétaire de Caisse</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                            <span>Toutes les agents <strong>Secrétaire de Caisse</strong> ({{ $totalSecretaires }}) sont déjà affectées à une caisse. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
                        </div>
                    @endif
                @endif
                <select id="secretaire_agent_id" name="secretaire_agent_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">— Aucune secrétaire pour l'instant —</option>
                    @foreach ($secretaires as $agent)
                        <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id') == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label for="directeur_superieur_display" class="text-xs font-bold text-slate-400 ml-1">Directeur supérieur</label>
                <input id="directeur_superieur_display" type="text" readonly
                    class="w-full bg-slate-200 border-none rounded-[20px] p-4 text-slate-500 font-bold cursor-not-allowed"
                    placeholder="— Sélectionner d'abord une délégation —">
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" @disabled($delegations->isEmpty()) class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all disabled:cursor-not-allowed disabled:opacity-60">
                    Enregistrer la caisse
                </button>
                <a href="{{ route('admin.caisses.index') }}" class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-in {
        animation: zoomIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endsection

@push('head')
    <style>
        .ts-wrapper .ts-control {
            background: rgb(241 245 249);
            border: none;
            border-radius: 20px;
            padding: 1rem;
            font-weight: 700;
            color: #334155;
            box-shadow: none;
        }
        .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #22d3ee; }
        .ts-dropdown { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .ts-dropdown .option { padding: 10px 16px; font-weight: 600; font-size: 14px; }
        .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #ecfeff; color: #0e7490; }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tom Select pour les responsables
            ['directeur_agent_id', 'secretaire_agent_id'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    new TomSelect(el, {
                        placeholder: 'Rechercher un agent...',
                        allowEmptyOption: true,
                        maxOptions: 50,
                        dropdownParent: 'body',
                    });
                }
            });

            // Mapping délégation → directeur technique
            var dtMap = {
                @foreach ($delegations as $d)
                    {{ $d->id }}: "{{ $d->directeur ? $d->directeur->prenom.' '.$d->directeur->nom : '' }}",
                @endforeach
            };
            var delSel   = document.getElementById('delegation_technique_id');
            var dtDisplay = document.getElementById('directeur_superieur_display');
            if (delSel && dtDisplay) {
                function updateDT() {
                    var nom = dtMap[delSel.value] || '';
                    dtDisplay.value = nom !== '' ? nom : '';
                    dtDisplay.placeholder = nom !== '' ? '' : '— Aucun directeur technique pour cette délégation —';
                }
                delSel.addEventListener('change', updateDT);
                updateDT();
            }

            // Filtrage villes par délégation
            var villeSel = document.getElementById('ville_id');
            if (delSel && villeSel) {
                var allOpts = Array.from(villeSel.querySelectorAll('option[data-delegation]'));
                function filterVilles() {
                    var delId = delSel.value;
                    var hasMatch = false;
                    allOpts.forEach(function (opt) {
                        var show = delId !== '' && opt.dataset.delegation === delId;
                        opt.style.display = show ? '' : 'none';
                        if (show) hasMatch = true;
                    });
                    villeSel.options[0].textContent = delId === ''
                        ? '— Choisir d\'abord la délégation —'
                        : (hasMatch ? '— Choisir une ville —' : '— Aucune ville disponible —');
                    if (villeSel.value !== '' && villeSel.selectedOptions[0] && villeSel.selectedOptions[0].style.display === 'none') {
                        villeSel.value = '';
                    }
                }
                delSel.addEventListener('change', filterVilles);
                filterVilles();
            }
        });
    </script>
@endpush
