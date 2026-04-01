@extends('layouts.app')

@section('content')
@php $isModal = request()->has('modal'); @endphp

<div class="{{ $isModal ? '' : 'min-h-screen bg-[#f8fafc] p-4 lg:p-12' }}">
    <div class="max-w-5xl mx-auto">
        
        {{-- Header stylisé --}}
        <div class="mb-10 text-center lg:text-left">
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">Configuration Direction</h1>
            <p class="text-slate-400 font-medium mt-2">Enregistrement d'une nouvelle unité administrative pour la Faîtière.</p>
        </div>

        <form method="POST" action="{{ route('admin.entites.directions.store') }}" target="_top" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            @csrf

            {{-- COLONNE GAUCHE : Identité --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-10 w-10 bg-cyan-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-cyan-100">
                            <i class="fas fa-building text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Détails de la Structure</h3>
                    </div>

                    <div class="space-y-6">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1 transition-colors group-focus-within:text-cyan-500">Nom de la Direction</label>
                            <input name="nom" type="text" required class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 mt-2 text-slate-700 font-bold focus:bg-white focus:border-cyan-500 focus:ring-0 transition-all placeholder-slate-300" placeholder="Ex: Direction de l'Audit Interne">
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div class="group">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Prénom Directeur</label>
                                <input name="directeur_prenom" type="text" required class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 mt-2 font-bold focus:bg-white focus:border-cyan-500 focus:ring-0 transition-all" placeholder="Prénom">
                            </div>
                            <div class="group">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Nom Directeur</label>
                                <input name="directeur_nom" type="text" required class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 mt-2 font-bold focus:bg-white focus:border-cyan-500 focus:ring-0 transition-all" placeholder="NOM">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contact --}}
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-10 w-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100">
                            <i class="fas fa-at text-sm"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Coordonnées & Accès</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Email Pro</label>
                            <input name="directeur_email" type="email" required class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 mt-2 font-bold focus:bg-white focus:border-cyan-500 focus:ring-0 transition-all" placeholder="directeur@rcpb.org">
                        </div>
                        <div class="group">
                            <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Téléphone Secrétariat</label>
                            <input name="secretariat_telephone" type="text" class="w-full bg-slate-50 border-2 border-transparent rounded-2xl p-4 mt-2 font-bold focus:bg-white focus:border-cyan-500 focus:ring-0 transition-all" placeholder="+226 XX XX XX XX">
                        </div>
                    </div>
                </div>
            </div>

            {{-- COLONNE DROITE : Résumé & Validation --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-slate-900 rounded-[32px] p-8 text-white shadow-2xl shadow-slate-200 sticky top-8">
                    <h3 class="text-xl font-black italic mb-6">Résumé</h3>
                    
                    <div class="space-y-6 border-b border-white/10 pb-6">
                        <div class="flex justify-between items-center text-xs">
                            <span class="opacity-50 font-bold uppercase">Type</span>
                            <span class="font-black text-cyan-400">FAÎTIÈRE</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="opacity-50 font-bold uppercase">Statut</span>
                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded-lg font-black">ACTIF</span>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-[10px] font-bold text-white/40 leading-relaxed uppercase tracking-widest">
                            En validant ce formulaire, un accès sécurisé sera créé pour le nouveau directeur.
                        </p>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full mt-10 py-5 bg-cyan-500 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-xl shadow-cyan-500/20 hover:bg-cyan-400 hover:-translate-y-1 transition-all duration-300">
                        Créer la Direction
                    </button>
                    
                    <a href="{{ route('admin.entites.index') }}" class="block w-full mt-4 py-4 text-center text-[10px] font-black uppercase text-white/30 hover:text-white transition-colors">
                        Annuler l'opération
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection