@extends('layouts.app')

@section('title', 'Parametres | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Parametres')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">

        {{-- Status toast --}}
        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Parametres du Systeme</h1>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-400">
                        <i class="fas fa-cog text-xs"></i>
                        <span class="font-bold uppercase tracking-wider text-slate-500">Administration</span>
                        &bull;
                        <span>Configuration generale</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- KPI-style summary cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-palette text-sm"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm font-bold">Apparence</p>
                <p class="mt-1 text-xs text-white/70">Theme {{ $theme === 'classic' ? 'RCPB' : 'Moderne' }} actif</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-rose-400 to-pink-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $maxLoginAttempts ?? 3 }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Securite</p>
                <p class="mt-1 text-xs text-white/70">Tentatives avant blocage</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-user-shield text-sm"></i>
                    </span>
                </div>
                <p class="mt-3 text-sm font-bold">Mon Compte</p>
                <p class="mt-1 text-xs text-white/70">{{ auth()->user()->name ?? 'Admin' }}</p>
            </div>
        </div>

        {{-- Two-column layout --}}
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">

            {{-- Left column --}}
            <div class="space-y-6">

                {{-- Apparence --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-500">
                            <i class="fas fa-palette text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Personnalisation</h3>
                            <p class="text-xs text-slate-400">Choisissez l'ambiance visuelle du portail.</p>
                        </div>
                    </div>

                    <form id="theme-form" method="POST" action="{{ route('admin.settings.theme.update') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <label class="relative cursor-pointer group">
                            <input class="peer sr-only" type="radio" name="theme_preference" value="reference" @checked(old('theme_preference', $theme) === 'reference') onchange="this.form.submit()">
                            <div class="rounded-2xl border-2 border-slate-100 bg-slate-50 p-5 transition-all peer-checked:border-cyan-500 peer-checked:bg-cyan-50/30 group-hover:border-slate-200">
                                <div class="mb-3 flex gap-1">
                                    <div class="h-6 w-6 rounded-lg bg-slate-800"></div>
                                    <div class="h-6 w-12 rounded-lg border border-slate-200 bg-white"></div>
                                </div>
                                <p class="text-sm font-black text-slate-800">Interface Moderne</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">Style SaaS (Bleu/Ardoise)</p>
                            </div>
                        </label>

                        <label class="relative cursor-pointer group">
                            <input class="peer sr-only" type="radio" name="theme_preference" value="classic" @checked(old('theme_preference', $theme) === 'classic') onchange="this.form.submit()">
                            <div class="rounded-2xl border-2 border-slate-100 bg-slate-50 p-5 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 group-hover:border-slate-200">
                                <div class="mb-3 flex gap-1">
                                    <div class="h-6 w-6 rounded-lg bg-emerald-600"></div>
                                    <div class="h-6 w-12 rounded-lg border border-slate-200 bg-white"></div>
                                </div>
                                <p class="text-sm font-black text-slate-800">Identite RCPB</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">Palette verte officielle</p>
                            </div>
                        </label>
                    </form>
                </div>

                {{-- Securite --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
                            <i class="fas fa-shield-alt text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Politique de Securite</h3>
                            <p class="text-xs text-slate-400">Controlez les tentatives d'acces non autorisees.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.security.update') }}" method="POST" class="space-y-5">
                        @csrf @method('PUT')

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tentatives avant blocage</label>
                                <input type="number" name="max_login_attempts" value="{{ old('max_login_attempts', $maxLoginAttempts ?? 3) }}" min="1" max="10" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Duree de suspension</label>
                                <select name="lockout_time" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                    <option value="15" @selected(old('lockout_time', $lockoutTime ?? 30) == 15)>15 Minutes</option>
                                    <option value="30" @selected(old('lockout_time', $lockoutTime ?? 30) == 30)>30 Minutes</option>
                                    <option value="60" @selected(old('lockout_time', $lockoutTime ?? 30) == 60)>1 Heure</option>
                                    <option value="1440" @selected(old('lockout_time', $lockoutTime ?? 30) == 1440)>24 Heures</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 rounded-2xl border border-rose-100 bg-rose-50/50 p-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-500 text-white shadow">
                                <i class="fas fa-exclamation-triangle text-[10px]"></i>
                            </span>
                            <div>
                                <p class="text-xs font-black text-rose-700">Protection Anti-BruteForce</p>
                                <p class="mt-0.5 text-[11px] text-rose-600/80">Apres 3 mots de passe incorrects, le compte sera suspendu temporairement.</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-slate-700">
                                <i class="fas fa-check text-xs"></i> Mis à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-6">

                {{-- Mon Profil --}}
                <div class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
                            <i class="fas fa-user-shield text-sm"></i>
                        </span>
                        <h3 class="text-sm font-black uppercase tracking-wider">Mon Profil Securise</h3>
                    </div>

                    <div class="space-y-3">
                        <button id="open-password-modal" class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400">
                                    <i class="fas fa-key text-sm"></i>
                                </span>
                                <div class="text-left">
                                    <p class="text-xs font-black uppercase tracking-wider">Mot de passe</p>
                                    <p class="text-[10px] text-white/40">Changer votre mot de passe</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-white/20"></i>
                        </button>

                        <div class="flex items-center justify-between rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-4 opacity-50 grayscale">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400">
                                    <i class="fas fa-shield-alt text-sm"></i>
                                </span>
                                <p class="text-xs font-black uppercase tracking-wider text-white/50">Double Facteur</p>
                            </div>
                            <span class="rounded bg-white/5 px-2 py-0.5 text-[8px] font-black uppercase">Desactive</span>
                        </div>

                        <button id="open-manage-pwd-modal" class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                                    <i class="fas fa-users-cog text-sm"></i>
                                </span>
                                <div class="text-left">
                                    <p class="text-xs font-black uppercase tracking-wider">Gerer les mots de passe</p>
                                    <p class="text-[10px] text-white/40">Modifier le mot de passe du personnel</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-white/20"></i>
                        </button>
                    </div>
                </div>

                {{-- Zone de danger --}}
                <div class="rounded-2xl border border-rose-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fas fa-radiation text-rose-500 animate-pulse text-sm"></i>
                        <h3 class="text-xs font-black uppercase tracking-wider text-rose-500">Zone de Danger</h3>
                    </div>
                    <p class="mb-5 text-xs text-slate-400 leading-relaxed">
                        La suppression de votre acces administrateur est une action irreversible. Toutes vos donnees seront purgees.
                    </p>
                    <button id="open-delete-modal" class="w-full rounded-xl border border-rose-100 bg-rose-50 py-3 text-xs font-black uppercase tracking-wider text-rose-600 transition hover:bg-rose-600 hover:text-white">
                        Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>

        {{-- Bottom info bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white px-5 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                    <i class="fas fa-user text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-700">{{ auth()->user()->name ?? 'Administrateur' }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-xs text-slate-400">
                <span><i class="fas fa-clock mr-1"></i> Derniere connexion: {{ now()->translatedFormat('d M Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- MODALE : CHANGER MOT DE PASSE --}}
<div id="password-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('password-modal').classList.add('hidden')"></div>
    <div class="relative w-full max-w-md rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('password-modal').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-500">Securite</p>
            <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Changer le mot de passe</h2>
        </div>

        <form method="POST" action="{{ route('admin.settings.password.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Mot de passe actuel <span class="text-rose-500">*</span></label>
                <input name="current_password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nouveau <span class="text-rose-500">*</span></label>
                    <input name="password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Confirmation <span class="text-rose-500">*</span></label>
                    <input name="password_confirmation" type="password" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                </div>
            </div>
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-slate-800 px-8 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-check"></i> Mettre a jour
                </button>
                <button type="button" onclick="document.getElementById('password-modal').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODALE : SUPPRESSION COMPTE --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('delete-modal').classList.add('hidden')"></div>
    <div class="relative w-full max-w-md rounded-[28px] border border-white/70 bg-white p-8 text-center shadow-2xl">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 shadow-sm">
            <i class="fas fa-user-times text-2xl"></i>
        </div>
        <h3 class="text-xl font-black tracking-tight text-slate-800">Supprimer l'acces ?</h3>
        <p class="mt-2 text-sm text-slate-400">Confirmez votre identite pour proceder a la suppression definitive.</p>

        <form method="POST" action="{{ route('admin.settings.account.destroy') }}" class="mt-6 space-y-4">
            @csrf @method('DELETE')
            <input name="delete_password" type="password" placeholder="Saisir votre mot de passe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-center text-sm text-slate-700 shadow-sm focus:border-rose-400 focus:ring-rose-400">
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="flex-1 rounded-2xl border border-slate-200 bg-white py-3 text-xs font-black uppercase tracking-wider text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
                <button type="submit" class="flex-1 rounded-2xl bg-rose-500 py-3 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-rose-600">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODALE : GERER LES MOTS DE PASSE DU PERSONNEL --}}
<div id="manage-pwd-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('manage-pwd-modal').classList.add('hidden'); resetManagePwdModal();"></div>
    <div class="relative w-full max-w-lg rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('manage-pwd-modal').classList.add('hidden'); resetManagePwdModal();" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-amber-500">Administration</p>
            <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Gerer les mots de passe</h2>
            <p class="mt-1 text-xs text-slate-400">Recherchez un membre du personnel et modifiez son mot de passe.</p>
        </div>

        {{-- Search input --}}
        <div class="relative mb-4">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <i class="fas fa-search text-xs text-slate-300"></i>
            </div>
            <input id="manage-pwd-search" type="text" placeholder="Rechercher par nom, email ou role..." autocomplete="off" class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
            <div id="manage-pwd-spinner" class="hidden absolute inset-y-0 right-0 flex items-center pr-4">
                <i class="fas fa-circle-notch fa-spin text-xs text-slate-300"></i>
            </div>
        </div>

        {{-- Search results list --}}
        <div id="manage-pwd-results" class="mb-4 max-h-48 space-y-1 overflow-y-auto"></div>

        {{-- Selected user form (hidden by default) --}}
        <div id="manage-pwd-form-wrap" class="hidden">
            <div id="manage-pwd-selected" class="mb-4 flex items-center gap-3 rounded-2xl border border-amber-100 bg-amber-50/50 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-sm font-black text-white" id="manage-pwd-avatar"></span>
                <div>
                    <p class="text-sm font-black text-slate-800" id="manage-pwd-name"></p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400" id="manage-pwd-role"></p>
                </div>
                <button type="button" onclick="resetManagePwdModal();" class="ml-auto flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.settings.users.password.update') }}" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="user_id" id="manage-pwd-user-id">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nouveau mot de passe <span class="text-rose-500">*</span></label>
                        <input name="password" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Confirmation <span class="text-rose-500">*</span></label>
                        <input name="password_confirmation" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
                    </div>
                </div>
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-amber-500 px-8 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-amber-600">
                        <i class="fas fa-check"></i> Modifier
                    </button>
                    <button type="button" onclick="document.getElementById('manage-pwd-modal').classList.add('hidden'); resetManagePwdModal();" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Annuler
                    </button>
                </div>
            </form>

            {{-- Formulaire de changement de rôle --}}
            <form method="POST" action="{{ route('admin.settings.users.role.update') }}" class="mt-6 space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="user_id" id="manage-role-user-id">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Rôle <span class="text-amber-500">*</span></label>
                    <select name="role" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
                        <option value="agent">Agent</option>
                        <option value="chef">Chef</option>
                        <option value="directeur">Directeur</option>
                        <option value="dg">DG</option>
                        <option value="directeur_adjoint">Directeur Adjoint</option>
                        <option value="assistant">Assistant</option>
                        <option value="secretaire">Secrétaire</option>
                        <option value="admin">Administrateur</option>
                        <option value="pca">PCA</option>
                        <option value="rh">RH</option>
                    </select>
                </div>
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-amber-500 px-8 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-amber-600">
                        <i class="fas fa-check"></i> Attribuer le rôle
                    </button>
                </div>
            </form>
        </div>

        {{-- Empty state --}}
        <div id="manage-pwd-empty" class="py-6 text-center">
            <i class="fas fa-user-lock mb-2 text-2xl text-slate-200"></i>
            <p class="text-xs text-slate-400">Tapez au moins 2 caracteres pour rechercher</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const setupModal = (modalId, openBtnId) => {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        openBtn?.addEventListener('click', () => modal?.classList.remove('hidden'));
    };
    setupModal('password-modal', 'open-password-modal');
    setupModal('delete-modal', 'open-delete-modal');
    setupModal('manage-pwd-modal', 'open-manage-pwd-modal');

    // --- Gerer les mots de passe: recherche AJAX ---
    const searchInput = document.getElementById('manage-pwd-search');
    const resultsDiv = document.getElementById('manage-pwd-results');
    const formWrap = document.getElementById('manage-pwd-form-wrap');
    const emptyState = document.getElementById('manage-pwd-empty');
    const spinner = document.getElementById('manage-pwd-spinner');
    let searchTimeout = null;

    searchInput?.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(searchTimeout);

        if (q.length < 2) {
            resultsDiv.innerHTML = '';
            formWrap.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        spinner?.classList.remove('hidden');
        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.settings.users.search') }}?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(users => {
                spinner?.classList.add('hidden');
                emptyState.classList.add('hidden');
                formWrap.classList.add('hidden');

                if (users.length === 0) {
                    resultsDiv.innerHTML = '<p class="py-4 text-center text-xs text-slate-400"><i class="fas fa-search mr-1"></i>Aucun resultat</p>';
                    return;
                }

                resultsDiv.innerHTML = users.map(u => {
                    const initials = u.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();

                    const roleBadge = {
                        admin: 'bg-rose-100 text-rose-600',
                        pca: 'bg-amber-100 text-amber-700',
                        directeur: 'bg-blue-100 text-blue-600',
                        directeur_adjoint: 'bg-indigo-100 text-indigo-600',
                        chef: 'bg-cyan-100 text-cyan-600',
                        agent: 'bg-slate-100 text-slate-600',
                        secretaire: 'bg-purple-100 text-purple-600',
                        assistant: 'bg-teal-100 text-teal-600',
                        dg: 'bg-cyan-100 text-cyan-600'
                    }[u.role] || 'bg-slate-100 text-slate-600';

                    return `<button type="button" onclick="selectUserForPwd(${u.id}, '${u.name.replace(/'/g, "\\'")}', '${u.role}', '${initials}')" class="flex w-full items-center gap-3 rounded-xl border border-slate-100 bg-white p-3 text-left transition hover:border-amber-200 hover:bg-amber-50/50">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-[10px] font-black text-white">${initials}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800">${u.name}</p>
                            <p class="truncate text-[10px] text-slate-400">${u.email}</p>
                        </div>
                        <span class="rounded-lg px-2 py-0.5 text-[9px] font-black uppercase ${roleBadge}">${u.role === 'dg' ? 'DG' : u.role.charAt(0).toUpperCase() + u.role.slice(1).replace('_', ' ')}</span>
                    </button>`;
                }).join('');
            })
            .catch(() => {
                spinner?.classList.add('hidden');
                resultsDiv.innerHTML = '<p class="py-4 text-center text-xs text-rose-400"><i class="fas fa-exclamation-triangle mr-1"></i>Erreur de recherche</p>';
            });
        }, 300);
    });
});

function selectUserForPwd(id, name, role, initials) {
    document.getElementById('manage-pwd-user-id').value = id;
    document.getElementById('manage-role-user-id').value = id;
    document.getElementById('manage-pwd-name').textContent = name;
    document.getElementById('manage-pwd-role').textContent = role;
    document.getElementById('manage-pwd-avatar').textContent = initials;
    document.getElementById('manage-pwd-results').innerHTML = '';
    document.getElementById('manage-pwd-search').value = '';
    document.getElementById('manage-pwd-form-wrap').classList.remove('hidden');
    document.getElementById('manage-pwd-empty').classList.add('hidden');
}

function resetManagePwdModal() {
    document.getElementById('manage-pwd-search').value = '';
    document.getElementById('manage-pwd-results').innerHTML = '';
    document.getElementById('manage-pwd-form-wrap').classList.add('hidden');
    document.getElementById('manage-pwd-empty').classList.remove('hidden');
    document.getElementById('manage-pwd-user-id').value = '';
    document.getElementById('manage-role-user-id').value = '';
}
</script>
@endpush
