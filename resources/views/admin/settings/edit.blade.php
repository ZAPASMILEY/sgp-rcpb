@extends('layouts.app')

@section('title', 'Parametres | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Parametres')

@section('content')
@php $activeTab = request()->query('tab', 'general'); @endphp
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

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

            {{-- Tab Navigation --}}
            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                @foreach([
                    'general'    => ['icon' => 'fa-sliders-h',      'label' => 'Général'],
                    'comptes'    => ['icon' => 'fa-users-cog',       'label' => 'Comptes & Rôles'],
                    'roles'      => ['icon' => 'fa-user-tag',        'label' => 'Rôles & Permissions'],
                    'droits'     => ['icon' => 'fa-user-lock',       'label' => 'Droits Individuels'],
                    'catalogue'  => ['icon' => 'fa-list-alt',        'label' => 'Catalogue Permissions'],
                ] as $tab => $meta)
                    <a href="{{ route('admin.settings.edit', array_merge(request()->except('tab'), ['tab' => $tab])) }}"
                       class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                              {{ $activeTab === $tab
                                  ? 'bg-slate-800 text-white shadow-sm'
                                  : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700' }}">
                        <i class="fas {{ $meta['icon'] }} text-[10px]"></i>
                        {{ $meta['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             TAB: GÉNÉRAL
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'general')

        {{-- KPI cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-500 p-5 text-white shadow-sm">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                    <i class="fas fa-palette text-sm"></i>
                </span>
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
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                    <i class="fas fa-user-shield text-sm"></i>
                </span>
                <p class="mt-3 text-sm font-bold">Mon Compte</p>
                <p class="mt-1 text-xs text-white/70">{{ auth()->user()->name ?? 'Admin' }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
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
                        @csrf @method('PUT')
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
                                <p class="mt-0.5 text-[11px] text-rose-600/80">Apres {{ $maxLoginAttempts ?? 3 }} mots de passe incorrects, le compte sera suspendu temporairement.</p>
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
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400"><i class="fas fa-key text-sm"></i></span>
                                <div class="text-left">
                                    <p class="text-xs font-black uppercase tracking-wider">Mot de passe</p>
                                    <p class="text-[10px] text-white/40">Changer votre mot de passe</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-white/20"></i>
                        </button>
                        <button id="open-manage-pwd-modal" class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400"><i class="fas fa-users-cog text-sm"></i></span>
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

        @endif {{-- /general --}}

        {{-- ══════════════════════════════════════════════════════════════
             TAB: COMPTES & RÔLES
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'comptes')
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                    <i class="fas fa-users-cog text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Comptes utilisateurs</h3>
                    <p class="text-xs text-slate-400">Modifiez le rôle ou le mot de passe de chaque compte.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Utilisateur</th>
                            <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Email</th>
                            <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Rôle actuel</th>
                            <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($allUsers as $u)
                        <tr class="group hover:bg-slate-50/50">
                            <td class="py-3 font-bold text-slate-700">{{ $u->name }}</td>
                            <td class="py-3 text-slate-500">{{ $u->email }}</td>
                            <td class="py-3">
                                <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-600">
                                    {{ $allRoles[$u->role] ?? $u->role }}
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            data-action="edit-role"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-role="{{ $u->role }}"
                                            class="btn-edit-role inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-amber-600 transition hover:bg-amber-100">
                                        <i class="fas fa-user-tag"></i> Rôle
                                    </button>
                                    <button type="button"
                                            data-action="reset-pwd"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            class="btn-reset-pwd inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-slate-600 transition hover:bg-slate-200">
                                        <i class="fas fa-key"></i> Mot de passe
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal changer rôle --}}
        <div id="modal-edit-role" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close="modal-edit-role"></div>
            <div class="relative w-full max-w-md rounded-[28px] bg-white p-7 shadow-2xl">
                <button type="button" data-close="modal-edit-role" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 hover:bg-rose-100 hover:text-rose-500">
                    <i class="fas fa-times"></i>
                </button>
                <p class="text-xs font-black uppercase tracking-widest text-amber-500">Modifier le rôle</p>
                <h2 class="mt-1 text-xl font-black text-slate-900" id="edit-role-name"></h2>
                <form method="POST" action="{{ route('admin.settings.users.role.update') }}" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="user_id" id="edit-role-user-id">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nouveau rôle <span class="text-rose-500">*</span></label>
                        <select name="role" id="edit-role-select" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
                            @foreach($allRoles as $slug => $label)
                                <option value="{{ $slug }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-amber-500 px-8 text-sm font-black uppercase tracking-wider text-white transition hover:bg-amber-600">
                            <i class="fas fa-check"></i> Attribuer
                        </button>
                        <button type="button" data-close="modal-edit-role" class="inline-flex h-11 items-center rounded-2xl border border-slate-200 px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal réinitialiser mot de passe --}}
        <div id="modal-reset-pwd" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close="modal-reset-pwd"></div>
            <div class="relative w-full max-w-md rounded-[28px] bg-white p-7 shadow-2xl">
                <button type="button" data-close="modal-reset-pwd" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 hover:bg-rose-100 hover:text-rose-500">
                    <i class="fas fa-times"></i>
                </button>
                <p class="text-xs font-black uppercase tracking-widest text-cyan-500">Réinitialiser</p>
                <h2 class="mt-1 text-xl font-black text-slate-900" id="reset-pwd-name"></h2>
                <form method="POST" action="{{ route('admin.settings.users.password.update') }}" class="mt-6 space-y-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="user_id" id="reset-pwd-user-id">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nouveau mot de passe <span class="text-rose-500">*</span></label>
                            <input name="password" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Confirmation <span class="text-rose-500">*</span></label>
                            <input name="password_confirmation" type="password" required minlength="8" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="submit" class="inline-flex h-11 items-center gap-2 rounded-2xl bg-slate-800 px-8 text-sm font-black uppercase tracking-wider text-white transition hover:bg-slate-700">
                            <i class="fas fa-check"></i> Modifier
                        </button>
                        <button type="button" data-close="modal-reset-pwd" class="inline-flex h-11 items-center rounded-2xl border border-slate-200 px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif {{-- /comptes --}}

        {{-- ══════════════════════════════════════════════════════════════
             TAB: RÔLES & PERMISSIONS (permissions par défaut)
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'roles')
        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">

            {{-- Sélecteur de rôle --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                        <i class="fas fa-user-tag text-sm"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Choisir un rôle</h3>
                        <p class="text-xs text-slate-400">Sélectionnez un rôle pour gérer ses permissions par défaut.</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    @foreach($allRoles as $slug => $label)
                        <a href="{{ route('admin.settings.edit', ['tab' => 'roles', 'role' => $slug]) }}"
                           class="flex items-center justify-between rounded-xl px-4 py-3 text-sm font-bold transition
                                  {{ $selectedRoleSlug === $slug
                                      ? 'bg-indigo-600 text-white shadow-sm'
                                      : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
                            <span>{{ $label }}</span>
                            @if($selectedRoleSlug === $slug)
                                <i class="fas fa-chevron-right text-xs"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Permissions du rôle sélectionné --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                @if($selectedRole)
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">
                                Permissions — {{ $allRoles[$selectedRoleSlug] }}
                            </h3>
                            <p class="text-xs text-slate-400">Cochez les permissions par défaut pour ce rôle.</p>
                        </div>
                        <span class="rounded-xl bg-indigo-100 px-3 py-1 text-xs font-black text-indigo-700">
                            {{ $rolePermissions->count() }} / {{ $permissions->count() }}
                        </span>
                    </div>

                    @if($permissions->isEmpty())
                        <p class="py-8 text-center text-sm text-slate-400">
                            Aucune permission dans le catalogue. <a href="{{ route('admin.settings.edit', ['tab' => 'catalogue']) }}" class="text-indigo-500 underline">Créer des permissions d'abord.</a>
                        </p>
                    @else
                        <form method="POST" action="{{ route('admin.settings.roles.permissions.sync', $selectedRoleSlug) }}" class="space-y-4">
                            @csrf
                            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                @foreach($permissions as $perm)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                               @checked($rolePermissions->contains($perm->id))>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-700">{{ $perm->slug }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $perm->name }}</p>
                                            @if($perm->description)
                                                <p class="mt-0.5 text-[10px] text-slate-400">{{ $perm->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-indigo-700">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="{{ route('admin.settings.edit', ['tab' => 'roles']) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50">
                                    Tout décocher
                                </a>
                            </div>
                        </form>
                    @endif

                @else
                    <div class="flex h-full min-h-[200px] items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-arrow-left mb-3 text-3xl text-slate-200"></i>
                            <p class="text-sm font-bold text-slate-400">Sélectionnez un rôle pour voir ses permissions.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif {{-- /roles --}}

        {{-- ══════════════════════════════════════════════════════════════
             TAB: DROITS INDIVIDUELS
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'droits')
        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">

            {{-- Sélecteur d'utilisateur --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                        <i class="fas fa-user-lock text-sm"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Choisir un utilisateur</h3>
                        <p class="text-xs text-slate-400">Permissions directes (exceptions au rôle).</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('admin.settings.edit') }}" class="mb-4 flex gap-2">
                    <input type="hidden" name="tab" value="droits">
                    <select name="user_id" class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">— Sélectionner —</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}" @selected($selectedUser?->id == $u->id)>
                                {{ $u->name }} ({{ $allRoles[$u->role] ?? $u->role }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>

                @if($selectedUser)
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3">
                        <p class="text-sm font-black text-slate-800">{{ $selectedUser->name }}</p>
                        <p class="text-[11px] text-slate-500">{{ $selectedUser->email }}</p>
                        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-emerald-600">
                            {{ $allRoles[$selectedUser->role] ?? $selectedUser->role }}
                        </p>
                    </div>
                @endif
            </div>

            {{-- Permissions individuelles --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                @if($selectedUser)
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">
                                Permissions directes — {{ $selectedUser->name }}
                            </h3>
                            <p class="text-xs text-slate-400">Ces permissions s'ajoutent à celles du rôle.</p>
                        </div>
                        <span class="rounded-xl bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                            {{ $userPermissions->count() }} attribuées
                        </span>
                    </div>

                    @if($permissions->isEmpty())
                        <p class="py-8 text-center text-sm text-slate-400">
                            Aucune permission dans le catalogue. <a href="{{ route('admin.settings.edit', ['tab' => 'catalogue']) }}" class="text-emerald-500 underline">Créer des permissions d'abord.</a>
                        </p>
                    @else
                        <form method="POST" action="{{ route('admin.settings.users.permissions.sync', $selectedUser) }}" class="space-y-4">
                            @csrf
                            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                @foreach($permissions as $perm)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                               @checked($userPermissions->contains($perm->id))>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-700">{{ $perm->slug }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $perm->name }}</p>
                                            @if($perm->description)
                                                <p class="mt-0.5 text-[10px] text-slate-400">{{ $perm->description }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-emerald-700">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="flex h-full min-h-[200px] items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-arrow-left mb-3 text-3xl text-slate-200"></i>
                            <p class="text-sm font-bold text-slate-400">Sélectionnez un utilisateur pour gérer ses permissions directes.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif {{-- /droits --}}

        {{-- ══════════════════════════════════════════════════════════════
             TAB: CATALOGUE PERMISSIONS
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'catalogue')
        <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">

            {{-- Liste des permissions --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-500">
                            <i class="fas fa-list-alt text-sm"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Catalogue des permissions</h3>
                            <p class="text-xs text-slate-400">{{ $permissions->count() }} permissions enregistrées.</p>
                        </div>
                    </div>
                </div>

                @if($permissions->isEmpty())
                    <div class="rounded-[20px] border border-dashed border-slate-200 py-12 text-center">
                        <i class="fas fa-key mb-3 text-3xl text-slate-200"></i>
                        <p class="text-sm font-bold text-slate-400">Aucune permission. Créez-en une depuis le formulaire.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100">
                                    <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Libellé</th>
                                    <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Code technique</th>
                                    <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Description</th>
                                    <th class="pb-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($permissions as $perm)
                                    <tr class="group hover:bg-slate-50/50">
                                        <td class="py-3 font-bold text-slate-700">{{ $perm->slug }}</td>
                                        <td class="py-3 font-mono text-xs text-slate-500">{{ $perm->name }}</td>
                                        <td class="py-3 text-xs text-slate-400">{{ $perm->description ?: '—' }}</td>
                                        <td class="py-3 text-right">
                                            <form method="POST" action="{{ route('admin.settings.permissions.destroy', $perm) }}"
                                                  onsubmit="return confirm('Supprimer « {{ $perm->slug }} » ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-100 bg-rose-50 px-3 py-1.5 text-[10px] font-black uppercase text-rose-600 transition hover:bg-rose-600 hover:text-white">
                                                    <i class="fas fa-trash-alt"></i> Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Formulaire ajout --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-500">
                        <i class="fas fa-plus text-sm"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Nouvelle permission</h3>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.permissions.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">
                            Libellé <span class="text-rose-500">*</span>
                        </label>
                        <input name="slug" type="text" required maxlength="150"
                               value="{{ old('slug') }}"
                               placeholder="Ex : Valider une évaluation"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400 @error('slug') border-rose-400 @enderror">
                        @error('slug')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">
                            Code technique <span class="text-rose-500">*</span>
                        </label>
                        <input name="name" type="text" required maxlength="100"
                               value="{{ old('name') }}"
                               placeholder="valider-evaluation"
                               class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 font-mono text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400 @error('name') border-rose-400 @enderror">
                        <p class="mt-1 text-[10px] text-slate-400">Minuscules, chiffres et tirets uniquement.</p>
                        @error('name')
                            <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-wider text-slate-400">Description</label>
                        <textarea name="description" rows="2" maxlength="255"
                                  placeholder="Description optionnelle…"
                                  class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400">{{ old('description') }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-violet-600 py-3 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-violet-700">
                        <i class="fas fa-plus"></i> Créer la permission
                    </button>
                </form>
            </div>
        </div>
        @endif {{-- /catalogue --}}

    </div>
</div>

{{-- ── Modales communes (onglet Général) ──────────────────────────────── --}}

{{-- MODALE : CHANGER MON MOT DE PASSE --}}
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

{{-- MODALE : SUPPRIMER MON COMPTE --}}
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('delete-modal').classList.add('hidden')"></div>
    <div class="relative w-full max-w-md rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>
        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-rose-500">Zone de danger</p>
            <h2 class="mt-2 text-xl font-black tracking-tight text-slate-900">Supprimer mon compte</h2>
            <p class="mt-1 text-xs text-slate-400">Cette action est irreversible. Confirmez avec votre mot de passe.</p>
        </div>
        <form method="POST" action="{{ route('admin.settings.account.destroy') }}" class="space-y-4">
            @csrf @method('DELETE')
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Mot de passe de confirmation <span class="text-rose-500">*</span></label>
                <input name="delete_password" type="password" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-rose-400 focus:ring-rose-400">
                @error('delete_password')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-rose-600 px-8 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-rose-700">
                    <i class="fas fa-trash-alt"></i> Supprimer
                </button>
                <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODALE : GERER LES MOTS DE PASSE (accès rapide depuis onglet Général) --}}
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
        <div class="relative mb-4">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <i class="fas fa-search text-xs text-slate-300"></i>
            </div>
            <input id="manage-pwd-search" type="text" placeholder="Rechercher par nom, email ou role..." autocomplete="off" class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400">
            <div id="manage-pwd-spinner" class="hidden absolute inset-y-0 right-0 flex items-center pr-4">
                <i class="fas fa-circle-notch fa-spin text-xs text-slate-300"></i>
            </div>
        </div>
        <div id="manage-pwd-results" class="mb-4 max-h-48 space-y-1 overflow-y-auto"></div>
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
                </div>
            </form>
        </div>
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
    // Modales de l'onglet Général
    const setupModal = (modalId, openBtnId) => {
        const modal = document.getElementById(modalId);
        const openBtn = document.getElementById(openBtnId);
        if (openBtn && modal) {
            openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        }
    };
    setupModal('password-modal', 'open-password-modal');
    setupModal('delete-modal', 'open-delete-modal');
    setupModal('manage-pwd-modal', 'open-manage-pwd-modal');

    // Recherche AJAX dans manage-pwd-modal
    const searchInput = document.getElementById('manage-pwd-search');
    const resultsDiv  = document.getElementById('manage-pwd-results');
    const formWrap    = document.getElementById('manage-pwd-form-wrap');
    const emptyState  = document.getElementById('manage-pwd-empty');
    const spinner     = document.getElementById('manage-pwd-spinner');
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
                    return `<button type="button" onclick="selectUserForPwd(${u.id}, '${u.name.replace(/'/g,"\\'")}', '${u.role}', '${initials}')" class="flex w-full items-center gap-3 rounded-xl border border-slate-100 bg-white p-3 text-left transition hover:border-amber-200 hover:bg-amber-50/50">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-800 text-[10px] font-black text-white">${initials}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-slate-800">${u.name}</p>
                            <p class="truncate text-[10px] text-slate-400">${u.email}</p>
                        </div>
                        <span class="rounded-lg bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase text-slate-600">${u.role}</span>
                    </button>`;
                }).join('');
            })
            .catch(() => {
                spinner?.classList.add('hidden');
                resultsDiv.innerHTML = '<p class="py-4 text-center text-xs text-rose-400">Erreur de recherche</p>';
            });
        }, 300);
    });
});

function selectUserForPwd(id, name, role, initials) {
    document.getElementById('manage-pwd-user-id').value  = id;
    document.getElementById('manage-pwd-name').textContent    = name;
    document.getElementById('manage-pwd-role').textContent    = role;
    document.getElementById('manage-pwd-avatar').textContent  = initials;
    document.getElementById('manage-pwd-results').innerHTML   = '';
    document.getElementById('manage-pwd-search').value        = '';
    document.getElementById('manage-pwd-form-wrap').classList.remove('hidden');
    document.getElementById('manage-pwd-empty').classList.add('hidden');
}

function resetManagePwdModal() {
    document.getElementById('manage-pwd-search').value        = '';
    document.getElementById('manage-pwd-results').innerHTML   = '';
    document.getElementById('manage-pwd-form-wrap').classList.add('hidden');
    document.getElementById('manage-pwd-empty').classList.remove('hidden');
    document.getElementById('manage-pwd-user-id').value       = '';
}

    // Onglet Comptes: boutons rôle + mot de passe via délégation
    document.addEventListener('click', function (e) {
        const btnRole = e.target.closest('[data-action="edit-role"]');
        if (btnRole) {
            const { id, name, role } = btnRole.dataset;
            document.getElementById('edit-role-user-id').value = id;
            document.getElementById('edit-role-name').textContent = name;
            const sel = document.getElementById('edit-role-select');
            if (sel) {
                for (let i = 0; i < sel.options.length; i++) {
                    if (sel.options[i].value === role) { sel.selectedIndex = i; break; }
                }
            }
            const modal = document.getElementById('modal-edit-role');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        const btnPwd = e.target.closest('[data-action="reset-pwd"]');
        if (btnPwd) {
            const { id, name } = btnPwd.dataset;
            document.getElementById('reset-pwd-user-id').value = id;
            document.getElementById('reset-pwd-name').textContent = name;
            const modal = document.getElementById('modal-reset-pwd');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
        }

        // Fermeture modales comptes
        if (e.target.closest('[data-close="modal-edit-role"]')) {
            const m = document.getElementById('modal-edit-role');
            m.classList.add('hidden'); m.style.display = '';
        }
        if (e.target.closest('[data-close="modal-reset-pwd"]')) {
            const m = document.getElementById('modal-reset-pwd');
            m.classList.add('hidden'); m.style.display = '';
        }
    });

// Auto-générer le code technique depuis le libellé
const slugInput = document.querySelector('input[name="slug"]');
const nameInput = document.querySelector('input[name="name"]');
if (slugInput && nameInput) {
    slugInput.addEventListener('input', function() {
        if (!nameInput.dataset.manual) {
            nameInput.value = this.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }
    });
    nameInput.addEventListener('input', function() {
        this.dataset.manual = '1';
    });
}
</script>
@endpush
