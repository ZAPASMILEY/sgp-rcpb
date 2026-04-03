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
            <div class="space-y-4">
                <label for="delegation_technique_id" class="text-xs font-bold text-slate-400 ml-1">Délégation Technique</label>
                <select id="delegation_technique_id" name="delegation_technique_id" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">Sélectionner une délégation</option>
                    @foreach ($delegations as $delegation)
                        <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id') === (string) $delegation->id)>
                            {{ $delegation->region }} / {{ $delegation->ville }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500">Le superviseur sera filtré selon cette délégation.</p>
            </div>
            <div class="space-y-4">
                <label for="nom" class="text-xs font-bold text-slate-400 ml-1">Nom de la caisse</label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300" placeholder="Ex: Caisse de Ouagadougou Centre">
            </div>
            <div class="bg-cyan-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="h-6 w-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-[10px] font-black">i</div>
                <p class="text-[10px] font-black text-cyan-700 uppercase tracking-tight">Renseignez les coordonnées du directeur de caisse, le secrétariat et le superviseur technique.</p>
            </div>
            <div class="space-y-4">
                <label for="directeur_nom" class="text-xs font-bold text-slate-400 ml-1">Nom complet du directeur de caisse</label>
                <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Nom et prénom du directeur de caisse">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="directeur_email" class="text-xs font-bold text-slate-400 ml-1">Email</label>
                    <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="directeur.caisse@rcpb.org">
                </div>
                <div class="space-y-2">
                    <label for="directeur_telephone" class="text-xs font-bold text-slate-400 ml-1">Téléphone</label>
                    <input id="directeur_telephone" name="directeur_telephone" type="text" value="{{ old('directeur_telephone') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="+226 70 00 00 00">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="secretariat_telephone" class="text-xs font-bold text-slate-400 ml-1">Numéro du secrétariat</label>
                    <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="+226 25 00 00 00">
                </div>
                <div class="space-y-2">
                    <label for="superviseur_direction_id" class="text-xs font-bold text-slate-400 ml-1">Directeur technique superviseur</label>
                    <select id="superviseur_direction_id" name="superviseur_direction_id" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500" @disabled($directions->isEmpty() || $delegations->isEmpty())>
                        <option value="">Choisissez d'abord une délégation</option>
                        @foreach ($directions as $direction)
                            <option value="{{ $direction->id }}" data-delegation-id="{{ $direction->delegation_technique_id }}" @selected((string) old('superviseur_direction_id') === (string) $direction->id)>
                                {{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}
                                @if ($direction->delegationTechnique)
                                    - {{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" @disabled($directions->isEmpty() || $delegations->isEmpty()) class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all disabled:cursor-not-allowed disabled:opacity-60">
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
