@extends('layouts.app')

@section('content')
@php $isModal = request()->has('modal'); @endphp

<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    
    {{-- LA FENÊTRE FLOTTANTE --}}
    <div class="relative w-full max-w-3xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in zoom-in duration-300">
        
        {{-- BOUTON FERMER (FLOTTANT) --}}
        <a href="{{ route('admin.entites.index') }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>

        {{-- HEADER EN DÉGRADÉ --}}
        <div class="bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-[#22d3ee] font-black shadow-sm">
                01
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouvelle Direction Faîtière</h1>
        </div>

        {{-- CORPS DU FORMULAIRE --}}
        <form method="POST" action="{{ route('admin.entites.directions.store') }}" target="_top" class="p-8 lg:p-12 space-y-8">
            @csrf

            {{-- Section : Détails --}}
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-800 ml-1">Détails Direction</h3>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 ml-1">Nom de la direction</label>
                    <input name="nom" type="text" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300" placeholder="Direction de l'Audit Interne">
                </div>
            </div>

            {{-- Section : Responsable --}}
            <div class="space-y-4">
                <h3 class="text-lg font-black text-slate-800 ml-1">Informations Directeur</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 ml-1">Prénom</label>
                        <input name="directeur_prenom" type="text" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Jean-Claude">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 ml-1">Nom</label>
                        <input name="directeur_nom" type="text" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="ZONGO">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 ml-1">Email</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 h-8 w-8 rounded-lg bg-white flex items-center justify-center text-slate-300 text-xs">@</div>
                            <input name="directeur_email" type="email" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 pl-14 font-bold focus:ring-2 focus:ring-cyan-500 text-sm" placeholder="jean-claude.zongo@rcpb.org">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-400 ml-1">Téléphone</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 h-8 w-8 rounded-lg bg-white flex items-center justify-center text-slate-300 text-xs"><i class="fas fa-phone"></i></div>
                            <input name="secretariat_telephone" type="text" class="w-full bg-slate-100 border-none rounded-[20px] p-4 pl-14 font-bold focus:ring-2 focus:ring-cyan-500 text-sm" placeholder="+226 25 30 00 00">
                        </div>
                    </div>
                </div>
            </div>

            {{-- BANDEAU INFO --}}
            <div class="bg-cyan-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="h-6 w-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-[10px] font-black">i</div>
                <p class="text-[10px] font-black text-cyan-700 uppercase tracking-tight">Compte directeur créé automatiquement</p>
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
    /* Animation pour l'entrée de la fenêtre */
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