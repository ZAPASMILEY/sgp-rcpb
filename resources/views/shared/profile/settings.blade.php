@extends($layout)

@section('title', 'Paramètres du compte | SGP-RCPB')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="space-y-6">

        {{-- Entête de page avec Dégradé Émeraude --}}
        <div class="bg-gradient-to-r from-[#008751] to-[#006837] rounded-2xl p-6 shadow-md text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/15 backdrop-blur-sm text-white shadow-inner">
                        <i class="fas fa-user-gear text-2xl"></i>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-emerald-200/90 block">Mon Espace Personnel</span>
                        <h1 class="text-2xl font-black tracking-tight mt-0.5">Paramètres & Sécurité</h1>
                    </div>
                </div>
                <div class="hidden sm:block text-right">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white/10 backdrop-blur-sm border border-white/10">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Compte Sécurisé
                    </span>
                </div>
            </div>
        </div>

        {{-- Alerte Succès Stylisée --}}
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 flex items-center gap-3 shadow-sm border-l-4 border-l-[#008751]">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[#008751]">
                    <i class="fas fa-check text-xs"></i>
                </div>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Section 1 : Informations profil --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                <div class="p-1.5 rounded-lg bg-emerald-50 text-[#008751]">
                    <i class="fas fa-id-card text-sm"></i>
                </div>
                <h2 class="text-xs font-black uppercase tracking-wider text-slate-700">Informations utilisateur</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-[#008751] mb-1.5">Nom & Prénom</label>
                    <div class="w-full text-sm font-bold text-slate-800 bg-slate-50/80 border border-slate-200 rounded-xl px-4 py-3 shadow-inner flex items-center gap-2 select-all">
                        <i class="fas fa-user text-slate-400 text-xs"></i>
                        <span>{{ $user->prenom }} {{ $user->nom }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-[#008751] mb-1.5">Identifiant de connexion</label>
                    <div class="w-full text-sm font-bold text-slate-800 bg-slate-50/80 border border-slate-200 rounded-xl px-4 py-3 shadow-inner flex items-center gap-2 select-all">
                        <i class="fas fa-envelope text-slate-400 text-xs"></i>
                        <span>{{ $user->email ?? $user->username }}</span>
                    </div>
                </div>
                @if($user->poste)
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-black uppercase tracking-wider text-[#008751] mb-1.5">Poste occupé</label>
                    <div class="w-full text-sm font-bold text-slate-700 bg-[#f0fdf4]/50 border border-emerald-100 rounded-xl px-4 py-3 flex items-center gap-2">
                        <i class="fas fa-briefcase text-[#008751]/70 text-xs"></i>
                        <span>{{ $user->poste }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Section 2 : Formulaire de changement de mot de passe --}}
<form method="POST" action="{{ route('global.profile.password') }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @csrf
            @method('PUT')

            <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center gap-2.5">
                <div class="p-1.5 rounded-lg bg-amber-50 text-amber-600">
                    <i class="fas fa-key text-sm"></i>
                </div>
                <h2 class="text-xs font-black uppercase tracking-wider text-slate-700">Modifier mon mot de passe</h2>
            </div>

            <div class="p-6 space-y-5">
                {{-- Mot de passe actuel --}}
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1.5">
                        Mot de passe actuel <span class="text-rose-500 font-bold">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="fas fa-unlock-keyhole text-xs"></i>
                        </span>
                        <input type="password" name="current_password" required
                               placeholder="Saisissez votre mot de passe actuel"
                               class="w-full rounded-xl border @error('current_password') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @else border-slate-200 focus:border-[#008751] focus:ring-emerald-100 @enderror bg-slate-50 pl-10 pr-4 py-3 text-sm font-semibold outline-none focus:bg-white focus:ring-4 transition shadow-inner">
                    </div>
                    @error('current_password')
                        <p class="mt-2 text-xs font-bold text-rose-600 flex items-center gap-1">
                            <i class="fas fa-circle-exclamation text-sm"></i> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Nouveaux champs --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1.5">
                            Nouveau mot de passe <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-lock text-xs"></i>
                            </span>
                            <input type="password" name="password" required
                                   placeholder="8 caractères minimum"
                                   class="w-full rounded-xl border @error('password') border-rose-400 focus:border-rose-400 focus:ring-rose-100 @else border-slate-200 focus:border-[#008751] focus:ring-emerald-100 @enderror bg-slate-50 pl-10 pr-4 py-3 text-sm font-semibold outline-none focus:bg-white focus:ring-4 transition shadow-inner">
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-bold text-rose-600 flex items-center gap-1">
                                <i class="fas fa-circle-exclamation text-sm"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1.5">
                            Confirmer le nouveau mot de passe <span class="text-rose-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-shield text-xs"></i>
                            </span>
                            <input type="password" name="password_confirmation" required
                                   placeholder="Répétez le mot de passe"
                                   class="w-full rounded-xl border border-slate-200 focus:border-[#008751] focus:ring-emerald-100 bg-slate-50 pl-10 pr-4 py-3 text-sm font-semibold outline-none focus:bg-white focus:ring-4 transition shadow-inner">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Zone de validation colorée --}}
            <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#008751] to-[#006837] px-5 py-3 text-sm font-black text-white shadow-md transition hover:opacity-90 active:transform active:scale-95 cursor-pointer">
                    <i class="fas fa-save text-xs"></i> Sauvegarder les modifications
                </button>
            </div>
        </form>

    </div>
</div>
@endsection