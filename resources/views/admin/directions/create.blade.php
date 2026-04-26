@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">

    <div class="relative w-full max-w-3xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in zoom-in duration-300">

        <a href="{{ route('admin.entites.index') }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>

        @if ($errors->any())
        <div style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 1rem; margin-bottom: 1.5rem;">
            <strong>Erreur de validation :</strong>
            <ul style="margin-top: 0.5rem; list-style: inside;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-[#22d3ee] font-black shadow-sm">
                01
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouvelle Direction Faîtière</h1>
        </div>

        <form method="POST" action="{{ route('admin.entites.directions.store') }}" target="_top" class="p-8 lg:p-12 space-y-8">
            @csrf

            {{-- Section : Détails --}}
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-800 ml-1">Détails Direction</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 ml-1">Nom de la direction</label>
                    <input name="nom" type="text" value="{{ old('nom') }}" required
                        class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300"
                        placeholder="Direction de l'Audit Interne">
                </div>
            </div>

            {{-- Section : Directeur --}}
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-800 ml-1">Directeur</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 ml-1">Sélectionner un agent existant</label>
                    @if($directeurs->isEmpty())
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Directeur de Direction</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @endif
                    <select name="directeur_agent_id"
                        class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                        <option value="">— Aucun directeur pour l'instant —</option>
                        @foreach($directeurs as $agent)
                            <option value="{{ $agent->id }}" @selected(old('directeur_agent_id') == $agent->id)>
                                {{ $agent->nom }} {{ $agent->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Section : Secrétaire --}}
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-800 ml-1">Secrétaire</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 ml-1">Sélectionner un agent existant</label>
                    @if($secretaires->isEmpty())
                        <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Secrétaire de Direction</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @endif
                    <select name="secretaire_agent_id"
                        class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                        <option value="">— Aucune secrétaire pour l'instant —</option>
                        @foreach($secretaires as $agent)
                            <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id') == $agent->id)>
                                {{ $agent->nom }} {{ $agent->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- BANDEAU INFO --}}
            <div class="bg-cyan-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="h-6 w-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-[10px] font-black">i</div>
                <p class="text-[10px] font-black text-cyan-700 uppercase tracking-tight">Sélectionner des agents existants — créez d'abord les agents si besoin</p>
            </div>

            {{-- BOUTONS D'ACTION --}}
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" id="submit-btn" class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all">
                    Enregistrer la Structure +
                </button>
                <a href="{{ route('admin.entites.index') }}" class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
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

@push('scripts')
<script>
    const form = document.querySelector('form');
    const btn = document.getElementById('submit-btn');
    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Patientez...';
    });
</script>
@endpush
@endsection
