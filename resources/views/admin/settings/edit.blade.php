@extends('layouts.app')

@section('title', 'Réglages | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="max-w-[1400px] mx-auto space-y-8">

        {{-- Barre de titre et fil d'ariane --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Réglages Système</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Administration</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Paramètres</span>
                </div>
            </div>
            
            @if (session('status'))
                <div id="settings-status" class="flex items-center gap-3 px-6 py-3 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 text-xs font-black uppercase tracking-tight shadow-sm animate-in fade-in slide-in-from-right-4">
                    <i class="fas fa-check-circle text-lg"></i> {{ session('status') }}
                </div>
            @endif
        </div>

        <div class="grid grid-cols-12 gap-8">
            
            {{-- COLONNE PRINCIPALE (8/12) --}}
            <div class="col-span-12 lg:col-span-8 space-y-8">
                
                {{-- SECTION : APPARENCE --}}
                <section class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-12 w-12 bg-cyan-50 text-cyan-500 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-palette text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Personnalisation</h3>
                            <p class="text-xs text-slate-400 font-medium">Choisissez l'ambiance visuelle de votre portail.</p>
                        </div>
                    </div>

                    <form id="theme-form" method="POST" action="{{ route('admin.settings.theme.update') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf
                        @method('PUT')

                        {{-- Mode Référence --}}
                        <label class="relative cursor-pointer group">
                            <input class="sr-only peer" type="radio" name="theme_preference" value="reference" @checked(old('theme_preference', $theme) === 'reference') onchange="this.form.submit()">
                            <div class="p-6 rounded-[28px] border-2 border-slate-50 bg-slate-50 transition-all peer-checked:border-cyan-500 peer-checked:bg-cyan-50/30 group-hover:border-slate-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex gap-1">
                                        <div class="h-8 w-8 bg-slate-800 rounded-lg"></div>
                                        <div class="h-8 w-16 bg-white border border-slate-200 rounded-lg"></div>
                                    </div>
                                    <div class="h-6 w-6 rounded-full border-2 border-slate-200 flex items-center justify-center peer-checked:border-cyan-500 bg-white shadow-sm">
                                        <div class="h-3 w-3 rounded-full bg-cyan-500 scale-0 transition-transform peer-checked:scale-100"></div>
                                    </div>
                                </div>
                                <p class="font-black text-slate-800">Interface Moderne</p>
                                <p class="text-[11px] text-slate-400 mt-1 font-medium italic">Style neutre type SaaS (Bleu/Ardoise)</p>
                            </div>
                        </label>

                        {{-- Mode Classique --}}
                        <label class="relative cursor-pointer group">
                            <input class="sr-only peer" type="radio" name="theme_preference" value="classic" @checked(old('theme_preference', $theme) === 'classic') onchange="this.form.submit()">
                            <div class="p-6 rounded-[28px] border-2 border-slate-50 bg-slate-50 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 group-hover:border-slate-200">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex gap-1">
                                        <div class="h-8 w-8 bg-emerald-600 rounded-lg"></div>
                                        <div class="h-8 w-16 bg-white border border-slate-200 rounded-lg"></div>
                                    </div>
                                    <div class="h-6 w-6 rounded-full border-2 border-slate-200 flex items-center justify-center peer-checked:border-emerald-500 bg-white shadow-sm">
                                        <div class="h-3 w-3 rounded-full bg-emerald-500 scale-0 transition-transform peer-checked:scale-100"></div>
                                    </div>
                                </div>
                                <p class="font-black text-slate-800">Identité RCPB</p>
                                <p class="text-[11px] text-slate-400 mt-1 font-medium italic">Palette verte officielle des caisses.</p>
                            </div>
                        </label>
                    </form>
                </section>

                {{-- SECTION : SÉCURITÉ & BLOCAGE --}}
                <section class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-12 w-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-shield-alt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Politique de Sécurité</h3>
                            <p class="text-xs text-slate-400 font-medium">Contrôlez les tentatives d'accès non autorisées.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.security.update') }}" method="POST" class="space-y-8">
                        @csrf @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Seuil de blocage --}}
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Tentatives avant blocage</label>
                                <div class="flex items-center gap-4 bg-slate-50 rounded-[20px] p-2 border-2 border-transparent focus-within:border-cyan-500 focus-within:bg-white transition-all">
                                    <div class="h-10 w-10 bg-white rounded-xl flex items-center justify-center shadow-sm text-slate-400">
                                        <i class="fas fa-fingerprint"></i>
                                    </div>
                                    <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $maxLoginAttempts ?? 3) }}" min="1" max="10" 
                                           class="flex-1 bg-transparent border-none font-black text-slate-700 focus:ring-0 text-xl">
                                    <span class="pr-6 text-xs font-black text-slate-300 uppercase tracking-widest">Essais</span>
                                </div>
                            </div>

                            {{-- Durée de blocage --}}
                            <div class="space-y-3">
                                <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Durée de la suspension</label>
                                <div class="flex items-center gap-4 bg-slate-50 rounded-[20px] p-2 border-2 border-transparent focus-within:border-cyan-500 focus-within:bg-white transition-all">
                                    <div class="h-10 w-10 bg-white rounded-xl flex items-center justify-center shadow-sm text-slate-400">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <select name="lockout_time" class="flex-1 bg-transparent border-none font-black text-slate-700 focus:ring-0 appearance-none">
                                        <option value="15" @selected(old('lockout_time', $lockoutTime ?? 30) == 15)>15 Minutes</option>
                                        <option value="30" @selected(old('lockout_time', $lockoutTime ?? 30) == 30)>30 Minutes</option>
                                        <option value="60" @selected(old('lockout_time', $lockoutTime ?? 30) == 60)>1 Heure</option>
                                        <option value="1440" @selected(old('lockout_time', $lockoutTime ?? 30) == 1440)>24 Heures</option>
                                    </select>
                                    <i class="fas fa-chevron-down pr-4 text-[10px] text-slate-300"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Alerte visuelle --}}
                        <div class="flex items-start gap-5 p-6 bg-rose-50/50 rounded-[28px] border border-rose-100">
                            <div class="h-10 w-10 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-200">
                                <i class="fas fa-exclamation-triangle text-xs"></i>
                            </div>
                            <div class="space-y-1">
                                <p class="text-sm font-black text-rose-700">Protection Anti-BruteForce</p>
                                <p class="text-[11px] text-rose-600/80 font-medium leading-relaxed uppercase tracking-tight">
                                    Après <span class="font-black underline">3 mots de passe incorrects</span>, le compte de l'agent sera suspendu temporairement et une alerte de sécurité sera envoyée au tableau de bord.
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="px-10 py-4 bg-slate-900 text-white rounded-[20px] text-xs font-black uppercase tracking-[0.15em] hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">
                                Mettre à jour la politique
                            </button>
                        </div>
                    </form>
                </section>
            </div>

            {{-- COLONNE DROITE (4/12) --}}
            <div class="col-span-12 lg:col-span-4 space-y-8">
                
                {{-- COMPTE PERSONNEL --}}
                <div class="bg-slate-900 rounded-[32px] p-8 text-white shadow-2xl shadow-slate-300 relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 h-24 w-24 bg-white/5 rounded-full blur-2xl"></div>
                    
                    <h3 class="text-lg font-black italic mb-8 relative z-10">Mon Profil Securisé</h3>
                    
                    <div class="space-y-4 relative z-10">
                        <button id="open-password-modal" class="w-full flex items-center justify-between p-5 bg-white/5 rounded-2xl border border-white/10 hover:bg-white/15 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 bg-cyan-500/20 text-cyan-400 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs font-black uppercase tracking-widest text-white">Mot de passe</p>
                                    <p class="text-[10px] opacity-40">Dernier changement: 3 mois</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs opacity-20 group-hover:opacity-100 transition-opacity"></i>
                        </button>

                        <div class="p-5 bg-emerald-500/5 rounded-2xl border border-emerald-500/20 flex items-center justify-between grayscale opacity-50">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <p class="text-xs font-black uppercase tracking-widest text-white/50">Double Facteur</p>
                            </div>
                            <span class="text-[8px] font-black uppercase bg-white/5 px-2 py-1 rounded">Désactivé</span>
                        </div>
                    </div>
                </div>

                {{-- DANGER ZONE --}}
                <div class="bg-white rounded-[32px] p-8 shadow-sm border border-rose-50">
                    <div class="flex items-center gap-3 mb-6">
                        <i class="fas fa-radiation text-rose-500 animate-pulse"></i>
                        <h3 class="text-[10px] font-black text-rose-500 uppercase tracking-[0.25em]">Zone de Danger</h3>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed font-medium mb-6">
                        La suppression de votre accès administrateur est une action irréversible. Toutes vos données seront purgées.
                    </p>
                    
                    <button id="open-delete-modal" class="w-full py-4 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] hover:bg-rose-600 hover:text-white transition-all border border-rose-100">
                        Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODALE : CHANGER MOT DE PASSE --}}
<div id="password-modal" class="create-form-modal items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-[35px] overflow-hidden shadow-2xl animate-in zoom-in duration-300">
        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
            <h3 class="font-black text-slate-800 text-xs uppercase tracking-widest italic">Sécurisation du compte</h3>
            <button id="close-password-modal" class="h-10 w-10 rounded-full bg-white shadow-sm flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition-all">&times;</button>
        </div>
        <div class="p-8">
            <form method="POST" action="{{ route('admin.settings.password.update') }}" class="space-y-6">
                @csrf @method('PUT')
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Mot de passe actuel</label>
                    <input name="current_password" type="password" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-slate-800 font-bold focus:ring-2 focus:ring-cyan-500" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Nouveau</label>
                        <input name="password" type="password" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-slate-800 font-bold focus:ring-2 focus:ring-cyan-500" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Confirmation</label>
                        <input name="password_confirmation" type="password" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-slate-800 font-bold focus:ring-2 focus:ring-cyan-500" required>
                    </div>
                </div>
                <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-[20px] text-xs font-black uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-black transition-all">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>

{{-- MODALE : SUPPRESSION COMPTE --}}
<div id="delete-modal" class="create-form-modal items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-[35px] overflow-hidden shadow-2xl animate-in zoom-in duration-300">
        <div class="p-10 text-center">
            <div class="h-20 w-20 bg-rose-50 text-rose-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-rose-100">
                <i class="fas fa-user-times text-3xl"></i>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-2 tracking-tight">Supprimer l'accès ?</h3>
            <p class="text-sm text-slate-400 mb-8 font-medium italic">Confirmez votre identité pour procéder à la suppression définitive.</p>
            
            <form method="POST" action="{{ route('admin.settings.account.destroy') }}" class="space-y-5">
                @csrf @method('DELETE')
                <input name="delete_password" type="password" placeholder="Saisir votre mot de passe" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-center text-slate-800 font-bold focus:ring-2 focus:ring-rose-500" required>
                <div class="flex gap-3">
                    <button type="button" id="close-delete-modal" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl text-[10px] font-black uppercase tracking-widest">Annuler</button>
                    <button type="submit" class="flex-1 py-4 bg-rose-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-100">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Logique des modales
        const setupModal = (modalId, openBtnId, closeBtnId) => {
            const modal = document.getElementById(modalId);
            const openBtn = document.getElementById(openBtnId);
            const closeBtn = document.getElementById(closeBtnId);

            openBtn?.addEventListener('click', () => modal.classList.add('is-open'));
            closeBtn?.addEventListener('click', () => modal.classList.remove('is-open'));
            
            // Fermer si clic à l'extérieur
            modal?.addEventListener('click', (e) => {
                if(e.target === modal) modal.classList.remove('is-open');
            });
        }

        setupModal('password-modal', 'open-password-modal', 'close-password-modal');
        setupModal('delete-modal', 'open-delete-modal', 'close-delete-modal');

        // Disparition automatique du message de succès
        const statusBox = document.getElementById('settings-status');
        if (statusBox) {
            setTimeout(() => {
                statusBox.style.transition = 'all 0.5s ease';
                statusBox.style.opacity = '0';
                statusBox.style.transform = 'translateX(20px)';
                setTimeout(() => statusBox.remove(), 500);
            }, 4000);
        }
    });
</script>
@endpush
