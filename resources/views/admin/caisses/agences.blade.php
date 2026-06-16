@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.caisses.show', $caisse) }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i>
        <span>Retour à la caisse</span>
    </a>
</div>

<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-3xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in zoom-in duration-300">

        <a href="{{ route('admin.caisses.show', $caisse) }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>

        <div class="bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-[#22d3ee] font-black shadow-sm">
                <i class="fas fa-building"></i>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouvelle Agence</h1>
                <p class="text-white/80 text-xs font-bold uppercase mt-1 tracking-wider">Rattachement direct : {{ $caisse->nom }}</p>
            </div>
        </div>

        {{-- Formulaire de création lié à AgenceController::store --}}
        <form method="POST" action="{{ route('admin.agences.store') }}" class="p-8 lg:p-12 space-y-8">
            @csrf

            {{-- Inputs masqués : pré-lier la caisse et sa DT --}}
            <input type="hidden" name="caisse_id" value="{{ $caisse->id }}">
            <input type="hidden" name="delegation_technique_id" value="{{ $caisse->delegation_technique_id }}">

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Nom de l'agence --}}
            <div class="space-y-4">
                <label for="nom" class="text-xs font-bold text-slate-400 ml-1">Nom de l'agence <span class="text-rose-500">*</span></label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required
                    class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300"
                    placeholder="Ex: Agence Ouaga Nord">
            </div>

            {{-- Téléphone d'accueil --}}
            <div class="space-y-4">
                <label for="telephone_accueil" class="text-xs font-bold text-slate-400 ml-1">Téléphone d'accueil <span class="text-rose-500">*</span></label>
                <input id="telephone_accueil" name="telephone_accueil" type="text" value="{{ old('telephone_accueil') }}" required
                    class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300"
                    placeholder="+226 25 00 00 00">
            </div>

            {{-- Chef d'agence --}}
            <div class="space-y-4">
                <label for="chef_agent_id" class="text-xs font-bold text-slate-400 ml-1">Chef d'agence</label>
                @if($chefs->isEmpty())
                    @if(($totalChefs ?? 0) === 0)
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Chef d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                            <span>Tous les agents <strong>Chef d'Agence</strong> ({{ $totalChefs }}) sont déjà affectés à une agence. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
                        </div>
                    @endif
                @endif
                <select id="chef_agent_id" name="chef_agent_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">— Aucun chef pour l'instant —</option>
                    @foreach ($chefs as $agent)
                        <option value="{{ $agent->id }}" @selected(old('chef_agent_id') == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Secrétaire d'agence --}}
            <div class="space-y-4">
                <label for="secretaire_agent_id" class="text-xs font-bold text-slate-400 ml-1">Secrétaire d'agence</label>
                @if($secretaires->isEmpty())
                    @if(($totalSecretaires ?? 0) === 0)
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Secrétaire d'Agence</strong> n'est enregistrée. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                            <span>Toutes les agents <strong>Secrétaire d'Agence</strong> ({{ $totalSecretaires }}) sont déjà affectées à une agence. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
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

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all">
                    Créer et affecter à la caisse
                </button>
                <a href="{{ route('admin.caisses.show', $caisse) }}" class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['chef_agent_id', 'secretaire_agent_id'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) new TomSelect(el, { placeholder: 'Rechercher un agent...', allowEmptyOption: true, maxOptions: 50 });
        });
    });
</script>
@endpush

@push('head')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css" rel="stylesheet">
<style>
    .ts-wrapper .ts-control { background: rgb(241 245 249); border: none; border-radius: 20px; padding: 1rem; font-weight: 700; color: #334155; box-shadow: none; }
    .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #22d3ee; }
    .ts-dropdown { border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .ts-dropdown .option { padding: 10px 16px; font-weight: 600; font-size: 14px; }
    .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #ecfeff; color: #0e7490; }
</style>
@endpush
