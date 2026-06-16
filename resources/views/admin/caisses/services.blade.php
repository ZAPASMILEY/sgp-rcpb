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
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouveau Service Interne</h1>
                <p class="text-white/80 text-xs font-bold uppercase mt-1 tracking-wider">Rattachement direct : {{ $caisse->nom }}</p>
            </div>
        </div>

        {{-- Formulaire de création lié au ServiceController --}}
        <form method="POST" action="{{ route('admin.services.store') }}" class="p-8 lg:p-12 space-y-8">
            @csrf

            {{-- Input masqué pour forcer le type et l'ID de la caisse actuelle --}}
            <input type="hidden" name="parent_type" value="caisse">
            <input type="hidden" name="caisse_id" value="{{ $caisse->id }}">

            {{-- Nom du service --}}
            <div class="space-y-4">
                <label for="nom" class="text-xs font-bold text-slate-400 ml-1">Nom du service</label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300" placeholder="Ex: Service Caisse Courante">
                @error('nom')
                    <p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sélection du Chef de service --}}
             <div class="space-y-4">
                <label for="chef_agent_id" class="text-xs font-bold text-slate-400 ml-1">Chef de service</label>
                @if($chefs->isEmpty())
                    @if(($totalChefs ?? 0) === 0)
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Chef de Service</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-ban mt-0.5 shrink-0 text-rose-400"></i>
                            <span>Tous les agents <strong>Chef de Service</strong> ({{ $totalChefs }}) sont déjà affectés à un service. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Ajouter un nouvel agent</a></span>
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