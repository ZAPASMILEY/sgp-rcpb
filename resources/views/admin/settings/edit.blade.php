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
                    'general'          => ['icon' => 'fa-sliders-h',      'label' => 'Général'],
                    'fonctionnalites'   => ['icon' => 'fa-toggle-on',      'label' => 'Fonctionnalités'],
                    'comptes'          => ['icon' => 'fa-users-cog',       'label' => 'Comptes & Rôles'],
                    'roles'            => ['icon' => 'fa-user-tag',        'label' => 'Rôles & Permissions'],
                    'droits'           => ['icon' => 'fa-user-lock',       'label' => 'Droits Individuels'],
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
                <a href="{{ route('admin.settings.edit', array_merge(request()->except('tab'), ['tab' => 'danger'])) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                          {{ $activeTab === 'danger'
                              ? 'bg-rose-700 text-white shadow-sm'
                              : 'bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700' }}">
                    <i class="fas fa-skull text-[10px]"></i>
                    Zone de danger
                </a>
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

                        {{-- Thème Moderne / Référence --}}
                        <label class="relative cursor-pointer group">
                            <input class="peer sr-only" type="radio" name="theme_preference" value="reference"
                                   @checked(old('theme_preference', $theme) === 'reference') onchange="this.form.submit()">
                            <div class="rounded-2xl border-2 bg-white p-4 shadow-sm transition-all
                                        border-slate-200 peer-checked:border-sky-500 peer-checked:shadow-sky-100/80 peer-checked:shadow-md
                                        group-hover:border-sky-300">
                                {{-- Mini-aperçu sidebar sombre --}}
                                <div class="mb-3 flex gap-2 overflow-hidden rounded-xl border border-slate-100" style="height:64px">
                                    <div class="flex w-10 flex-col gap-1 bg-slate-800 p-1.5">
                                        <div class="h-1.5 w-full rounded bg-white/30"></div>
                                        <div class="h-1.5 w-4/5 rounded bg-white/20"></div>
                                        <div class="h-1.5 w-full rounded bg-sky-400"></div>
                                        <div class="h-1.5 w-4/5 rounded bg-white/20"></div>
                                    </div>
                                    <div class="flex-1 p-1.5">
                                        <div class="mb-1 h-2 w-3/4 rounded bg-slate-100"></div>
                                        <div class="h-1.5 w-1/2 rounded bg-slate-100"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Interface Moderne</p>
                                        <p class="text-[11px] text-slate-400">Ardoise sombre — accent bleu ciel</p>
                                    </div>
                                    @if($theme === 'reference')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-sky-600">
                                            <i class="fas fa-circle text-[6px]"></i>ACTIF
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </label>

                        {{-- Thème Classique RCPB --}}
                        <label class="relative cursor-pointer group">
                            <input class="peer sr-only" type="radio" name="theme_preference" value="classic"
                                   @checked(old('theme_preference', $theme) === 'classic') onchange="this.form.submit()">
                            <div class="rounded-2xl border-2 bg-white p-4 shadow-sm transition-all
                                        border-slate-200 peer-checked:border-emerald-500 peer-checked:shadow-emerald-100/80 peer-checked:shadow-md
                                        group-hover:border-emerald-300">
                                {{-- Mini-aperçu sidebar verte --}}
                                <div class="mb-3 flex gap-2 overflow-hidden rounded-xl border border-slate-100" style="height:64px; background:#f0f9f4">
                                    <div class="flex w-10 flex-col gap-1 p-1.5" style="background:#008751">
                                        <div class="h-1.5 w-full rounded bg-white/30"></div>
                                        <div class="h-1.5 w-4/5 rounded bg-white/20"></div>
                                        <div class="h-1.5 w-full rounded bg-white"></div>
                                        <div class="h-1.5 w-4/5 rounded bg-white/20"></div>
                                    </div>
                                    <div class="flex-1 p-1.5" style="background:#f0f9f4">
                                        <div class="mb-1 h-2 w-3/4 rounded bg-emerald-100"></div>
                                        <div class="h-1.5 w-1/2 rounded bg-emerald-100"></div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-black text-slate-800">Identité RCPB</p>
                                        <p class="text-[11px] text-slate-400">Vert officiel RCPB — fond clair</p>
                                    </div>
                                    @if($theme === 'classic')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-700">
                                            <i class="fas fa-circle text-[6px]"></i>ACTIF
                                        </span>
                                    @endif
                                </div>
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

                        @if ($errors->hasAny(['max_login_attempts', 'lockout_time']))
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                                @foreach (['max_login_attempts', 'lockout_time'] as $field)
                                    @error($field)<p>{{ $message }}</p>@enderror
                                @endforeach
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tentatives avant blocage</label>
                                <input type="number" name="max_login_attempts"
                                       value="{{ old('max_login_attempts', $maxLoginAttempts ?? 3) }}"
                                       min="1" max="10"
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400
                                              @error('max_login_attempts') border-rose-400 @enderror">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Duree de suspension</label>
                                <select name="lockout_time" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                    <option value="15" @selected(old('lockout_time', $lockoutTime) == 15)>15 Minutes</option>
                                    <option value="30" @selected(old('lockout_time', $lockoutTime) == 30)>30 Minutes</option>
                                    <option value="60" @selected(old('lockout_time', $lockoutTime) == 60)>1 Heure</option>
                                    <option value="1440" @selected(old('lockout_time', $lockoutTime) == 1440)>24 Heures</option>
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
                                    <p class="text-[10px] text-white/60">Changer votre mot de passe</p>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-xs text-white/20"></i>
                        </button>
                        <button id="open-manage-pwd-modal" class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400"><i class="fas fa-users-cog text-sm"></i></span>
                                <div class="text-left">
                                    <p class="text-xs font-black uppercase tracking-wider">Gerer les mots de passe</p>
                                    <p class="text-[10px] text-white/60">Modifier le mot de passe du personnel</p>
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
             TAB: FONCTIONNALITÉS
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'fonctionnalites')
        <div class="space-y-4">

            {{-- Section header --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                        <i class="fas fa-toggle-on text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900">Contrôle des fonctionnalités</h2>
                        <p class="mt-0.5 text-sm text-slate-500">Activez ou désactivez les modules de création d'évaluations et d'assignation d'objectifs pour l'ensemble des utilisateurs.</p>
                    </div>
                </div>
            </div>

            {{-- Toggle cards --}}
            <div class="grid gap-4 sm:grid-cols-2">

                {{-- Évaluations --}}
                @php
                    $evalOn  = $featuresEnabled['evaluations'] ?? true;
                    $evalMsg = $featuresMessages['evaluations'] ?? '';
                @endphp
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $evalOn ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-900">Évaluations</p>
                            <p class="text-xs text-slate-500">Création et soumission de fiches d'évaluation</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black
                                     {{ $evalOn ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $evalOn ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $evalOn ? 'Activées' : 'Désactivées' }}
                        </span>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            @if($evalOn)
                                Les utilisateurs peuvent actuellement <strong>créer et soumettre</strong> des évaluations. Désactivez pour bloquer toute nouvelle création.
                            @else
                                La création d'évaluations est <strong>bloquée</strong>. Les fiches existantes restent accessibles en lecture.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('admin.settings.feature.toggle', 'evaluations') }}">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('{{ $evalOn ? 'Désactiver les évaluations pour tous les utilisateurs ?' : 'Activer les évaluations ?' }}')"
                                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-black transition
                                           {{ $evalOn
                                               ? 'border-2 border-rose-200 bg-white text-rose-600 hover:bg-rose-50'
                                               : 'bg-emerald-600 text-white shadow-md shadow-emerald-200 hover:bg-emerald-700' }}">
                                <i class="fas {{ $evalOn ? 'fa-toggle-off' : 'fa-toggle-on' }} text-base"></i>
                                {{ $evalOn ? 'Désactiver les évaluations' : 'Activer les évaluations' }}
                            </button>
                        </form>
                        {{-- Message de désactivation --}}
                        <form method="POST" action="{{ route('admin.settings.feature.message', 'evaluations') }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Message affiché aux utilisateurs quand désactivé
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="message" value="{{ $evalMsg }}" maxlength="300"
                                       placeholder="Ex : Campagne d'évaluation fermée jusqu'au 30 juin."
                                       class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-indigo-300 focus:outline-none">
                                <button type="submit"
                                        class="shrink-0 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-200">
                                    Enregistrer
                                </button>
                            </div>
                            @if($evalMsg)
                                <p class="text-xs text-slate-400"><i class="fas fa-eye mr-1"></i>Actuellement affiché : « {{ $evalMsg }} »</p>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Objectifs --}}
                @php
                    $objOn  = $featuresEnabled['objectifs'] ?? true;
                    $objMsg = $featuresMessages['objectifs'] ?? '';
                @endphp
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $objOn ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-900">Assignation d'objectifs</p>
                            <p class="text-xs text-slate-500">Création et assignation de fiches d'objectifs</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black
                                     {{ $objOn ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $objOn ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            {{ $objOn ? 'Activées' : 'Désactivées' }}
                        </span>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-slate-600 leading-relaxed">
                            @if($objOn)
                                Les responsables peuvent actuellement <strong>assigner des objectifs</strong> à leurs subordonnés. Désactivez pour bloquer toute nouvelle assignation.
                            @else
                                L'assignation d'objectifs est <strong>bloquée</strong>. Les fiches existantes restent accessibles en lecture.
                            @endif
                        </p>
                        <form method="POST" action="{{ route('admin.settings.feature.toggle', 'objectifs') }}">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('{{ $objOn ? 'Désactiver l\'assignation d\'objectifs pour tous les utilisateurs ?' : 'Activer l\'assignation d\'objectifs ?' }}')"
                                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-black transition
                                           {{ $objOn
                                               ? 'border-2 border-rose-200 bg-white text-rose-600 hover:bg-rose-50'
                                               : 'bg-emerald-600 text-white shadow-md shadow-emerald-200 hover:bg-emerald-700' }}">
                                <i class="fas {{ $objOn ? 'fa-toggle-off' : 'fa-toggle-on' }} text-base"></i>
                                {{ $objOn ? 'Désactiver les objectifs' : 'Activer les objectifs' }}
                            </button>
                        </form>
                        {{-- Message de désactivation --}}
                        <form method="POST" action="{{ route('admin.settings.feature.message', 'objectifs') }}" class="space-y-2">
                            @csrf @method('PATCH')
                            <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400">
                                Message affiché aux utilisateurs quand désactivé
                            </label>
                            <div class="flex gap-2">
                                <input type="text" name="message" value="{{ $objMsg }}" maxlength="300"
                                       placeholder="Ex : Assignation d'objectifs fermée jusqu'à nouvel ordre."
                                       class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 placeholder-slate-300 focus:border-emerald-300 focus:outline-none">
                                <button type="submit"
                                        class="shrink-0 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-200">
                                    Enregistrer
                                </button>
                            </div>
                            @if($objMsg)
                                <p class="text-xs text-slate-400"><i class="fas fa-eye mr-1"></i>Actuellement affiché : « {{ $objMsg }} »</p>
                            @endif
                        </form>
                    </div>
                </div>

            </div>{{-- /grid --}}

            {{-- Info note --}}
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                <i class="fas fa-info-circle mt-0.5 text-amber-500"></i>
                <div class="text-sm text-amber-800">
                    <p class="font-bold">Ces paramètres prennent effet immédiatement.</p>
                    <p class="mt-0.5">Lorsqu'une fonctionnalité est désactivée, les formulaires de création sont inaccessibles pour tous les rôles. Les données existantes (fiches, évaluations) ne sont pas supprimées.</p>
                </div>
            </div>

        </div>
        @endif {{-- /fonctionnalites --}}

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
                        @foreach($pagedUsers as $u)
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
            @if($pagedUsers->hasPages())
            <div class="mt-4 flex items-center justify-between px-1">
                <p class="text-xs text-slate-400">
                    {{ $pagedUsers->firstItem() }}–{{ $pagedUsers->lastItem() }} sur {{ $pagedUsers->total() }} utilisateurs
                </p>
                <div class="flex items-center gap-1">
                    {{-- Précédent --}}
                    @if($pagedUsers->onFirstPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-300 text-xs cursor-default">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ $pagedUsers->appends(request()->except('page'))->previousPageUrl() }}&tab=comptes"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    {{-- Pages --}}
                    @php
                        $current  = $pagedUsers->currentPage();
                        $last     = $pagedUsers->lastPage();
                        $from     = max(1, $current - 2);
                        $to       = min($last, $current + 2);
                    @endphp
                    @if($from > 1)
                        <a href="{{ $pagedUsers->appends(request()->except('page'))->url(1) }}&tab=comptes"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">1</a>
                        @if($from > 2)
                            <span class="inline-flex h-8 items-center px-1 text-slate-300 text-xs">…</span>
                        @endif
                    @endif
                    @for($page = $from; $page <= $to; $page++)
                        @if($page == $current)
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-xs font-black">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $pagedUsers->appends(request()->except('page'))->url($page) }}&tab=comptes"
                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">
                                {{ $page }}
                            </a>
                        @endif
                    @endfor
                    @if($to < $last)
                        @if($to < $last - 1)
                            <span class="inline-flex h-8 items-center px-1 text-slate-300 text-xs">…</span>
                        @endif
                        <a href="{{ $pagedUsers->appends(request()->except('page'))->url($last) }}&tab=comptes"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">{{ $last }}</a>
                    @endif

                    {{-- Suivant --}}
                    @if($pagedUsers->hasMorePages())
                        <a href="{{ $pagedUsers->appends(request()->except('page'))->nextPageUrl() }}&tab=comptes"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 text-xs hover:bg-slate-50 transition">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-300 text-xs cursor-default">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
            @endif
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
        {{-- ── Comptes RH ───────────────────────────────────────────────── --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <i class="fas fa-user-tie text-sm"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Comptes Responsable RH</h3>
                        <p class="text-xs text-slate-400">Créez et visualisez les comptes avec le rôle RH.</p>
                    </div>
                </div>
                <button type="button" id="open-create-rh-modal"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-plus text-[10px]"></i> Créer compte RH
                </button>
            </div>

            @if($rhUsers->isEmpty())
                <div class="flex items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-4 text-sm text-slate-400">
                    <i class="fas fa-info-circle"></i>
                    Aucun compte RH créé pour le moment.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Nom</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Email</th>
                                <th class="pb-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Rôle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($rhUsers as $rhu)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 font-bold text-slate-700">{{ $rhu->name }}</td>
                                <td class="py-3 text-slate-500">{{ $rhu->email }}</td>
                                <td class="py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2.5 py-0.5 text-[10px] font-black text-teal-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-teal-400"></span>
                                        Responsable RH
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Modal créer compte RH --}}
        <div id="modal-create-rh" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-close="modal-create-rh"></div>
            <div class="relative w-full max-w-md rounded-[28px] bg-white shadow-2xl">

                {{-- En-tête modal --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-7 py-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-800 text-white">
                            <i class="fas fa-user-tie text-sm"></i>
                        </span>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Nouveau compte</p>
                            <h2 class="text-lg font-black text-slate-900">Compte Responsable RH</h2>
                        </div>
                    </div>
                    <button type="button" data-close="modal-create-rh"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Corps --}}
                <div class="px-7 py-5">
                    <div class="mb-4 flex items-start gap-2.5 rounded-2xl border border-teal-100 bg-teal-50 px-4 py-3 text-xs text-teal-700">
                        <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                        <span>Ce compte aura accès au module RH uniquement. Le mot de passe devra être changé à la première connexion.</span>
                    </div>

                    @if($errors->has('name') || $errors->has('email') || $errors->has('password'))
                        <div class="mb-4 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-600">
                            @foreach($errors->only(['name','email','password']) as $err)
                                <p><i class="fas fa-exclamation-circle mr-1"></i>{{ $err }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.rh.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Nom complet <span class="text-rose-500">*</span></label>
                            <input name="name" type="text" required autocomplete="off"
                                   value="{{ old('name') }}"
                                   placeholder="Ex : Koné Marie"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:font-normal placeholder:text-slate-300 focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Adresse email <span class="text-rose-500">*</span></label>
                            <input name="email" type="email" required autocomplete="off"
                                   value="{{ old('email') }}"
                                   placeholder="rh@rcpb.bf"
                                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:font-normal placeholder:text-slate-300 focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Mot de passe <span class="text-rose-500">*</span></label>
                                <input name="password" type="password" required minlength="8"
                                       placeholder="Min. 8 caractères"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition placeholder:text-xs placeholder:text-slate-300 focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-500">Confirmation <span class="text-rose-500">*</span></label>
                                <input name="password_confirmation" type="password" required minlength="8"
                                       placeholder="Répéter"
                                       class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition placeholder:text-xs placeholder:text-slate-300 focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-100">
                            </div>
                        </div>

                        {{-- Boutons --}}
                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-600 py-3 text-sm font-black text-white shadow-md shadow-teal-200 transition hover:bg-teal-700">
                                <i class="fas fa-user-plus text-xs"></i> Créer le compte
                            </button>
                            <button type="button" data-close="modal-create-rh"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Accès & Droits du rôle RH ──────────────────────────────────── --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-teal-100 text-teal-600">
                    <i class="fas fa-user-lock text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Accès & Droits du rôle RH</h3>
                    <p class="text-xs text-slate-400">Ce que le Responsable RH peut voir et faire dans le système.</p>
                </div>
            </div>

            {{-- ── Modules accessibles ──────────────────────────────────── --}}
            <p class="mb-3 text-[11px] font-black uppercase tracking-widest text-slate-400">Modules accessibles</p>
            <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @php
                $modulesRh = [
                    ['icon' => 'fa-gauge-high',      'label' => 'Tableau de bord',          'detail' => 'Vue d\'ensemble',          'level' => 'full'],
                    ['icon' => 'fa-graduation-cap',  'label' => 'Formations',               'detail' => 'CRUD complet',             'level' => 'full'],
                    ['icon' => 'fa-chart-bar',        'label' => 'Statistiques',             'detail' => 'Consultation',             'level' => 'read'],
                    ['icon' => 'fa-table-columns',   'label' => 'Tableaux export',          'detail' => 'Consultation + Export',    'level' => 'read'],
                    ['icon' => 'fa-chart-line',      'label' => 'Analyse comparative',      'detail' => 'Consultation',             'level' => 'read'],
                    ['icon' => 'fa-clipboard-check', 'label' => 'Évaluations',              'detail' => 'Lecture seule',            'level' => 'read'],
                    ['icon' => 'fa-comment-dots',    'label' => 'Réclamations',             'detail' => 'Répondre uniquement',      'level' => 'partial'],
                    ['icon' => 'fa-sitemap',         'label' => 'Structures réseau',        'detail' => 'Lecture seule + PDF',      'level' => 'read'],
                ];
                $levelCfg = [
                    'full'    => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'badge' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500', 'text' => 'Complet', 'icon_color' => 'text-emerald-600'],
                    'read'    => ['bg' => 'bg-sky-50',     'border' => 'border-sky-200',     'badge' => 'bg-sky-100 text-sky-700',         'dot' => 'bg-sky-500',     'text' => 'Lecture',  'icon_color' => 'text-sky-600'],
                    'partial' => ['bg' => 'bg-amber-50',   'border' => 'border-amber-200',   'badge' => 'bg-amber-100 text-amber-700',     'dot' => 'bg-amber-500',   'text' => 'Partiel',  'icon_color' => 'text-amber-600'],
                ];
                @endphp

                @foreach ($modulesRh as $mod)
                    @php $cfg = $levelCfg[$mod['level']]; @endphp
                    <div class="flex flex-col gap-2 rounded-xl border {{ $cfg['border'] }} {{ $cfg['bg'] }} p-3.5">
                        <div class="flex items-center justify-between">
                            <i class="fas {{ $mod['icon'] }} {{ $cfg['icon_color'] }} text-base"></i>
                            <span class="inline-flex items-center gap-1 rounded-full {{ $cfg['badge'] }} px-2 py-0.5 text-[10px] font-black">
                                <span class="h-1.5 w-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                {{ $cfg['text'] }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700">{{ $mod['label'] }}</p>
                            <p class="text-[11px] text-slate-500">{{ $mod['detail'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Modules interdits --}}
            <div class="mb-6 flex flex-wrap gap-2">
                <p class="w-full text-[11px] font-black uppercase tracking-widest text-slate-400">Modules non accessibles</p>
                @foreach([
                    'Administration système', 'Gestion des agents', 'Gestion des utilisateurs',
                    'Fiches d\'objectifs (assignation)', 'Évaluations (créer/valider)', 'Années & semestres',
                ] as $blocked)
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs text-slate-400">
                        <i class="fas fa-ban text-[10px] text-rose-300"></i> {{ $blocked }}
                    </span>
                @endforeach
            </div>

            {{-- ── Permissions Spatie du rôle RH ───────────────────────── --}}
            <p class="mb-3 text-[11px] font-black uppercase tracking-widest text-slate-400">Permissions système attribuées</p>
            @if ($rhSpatiePerms->isEmpty())
                <div class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Aucune permission Spatie trouvée pour le rôle RH — vérifiez que le seeder a été exécuté.
                </div>
            @else
                @php
                $permLabels = [
                    'agents.voir'              => ['label' => 'Consulter tous les agents',                       'icon' => 'fa-users',           'color' => 'sky'],
                    'agents.modifier'          => ['label' => 'Modifier les informations d\'un agent',           'icon' => 'fa-user-pen',        'color' => 'sky'],
                    'structures.voir'          => ['label' => 'Consulter les structures organisationnelles',     'icon' => 'fa-sitemap',         'color' => 'amber'],
                    'evaluations.voir-propres' => ['label' => 'Consulter ses propres évaluations',               'icon' => 'fa-clipboard-check', 'color' => 'indigo'],
                    'evaluations.exporter-pdf' => ['label' => 'Télécharger les évaluations en PDF',              'icon' => 'fa-file-pdf',        'color' => 'indigo'],
                    'objectifs.voir-propres'   => ['label' => 'Consulter ses propres fiches d\'objectifs',       'icon' => 'fa-bullseye',        'color' => 'emerald'],
                    'objectifs.avancement'     => ['label' => 'Mettre à jour l\'avancement des objectifs',       'icon' => 'fa-circle-half-stroke','color' => 'emerald'],
                    'formations.assigner'      => ['label' => 'Créer, modifier et supprimer des formations',     'icon' => 'fa-graduation-cap',  'color' => 'teal'],
                    'statistiques.voir'        => ['label' => 'Consulter les statistiques du personnel',         'icon' => 'fa-chart-bar',       'color' => 'teal'],
                    'tableaux.voir'            => ['label' => 'Consulter et exporter les tableaux personnalisés','icon' => 'fa-table-columns',   'color' => 'teal'],
                ];
                $colorMap = [
                    'sky'    => 'bg-sky-50 text-sky-700 border-sky-200',
                    'amber'  => 'bg-amber-50 text-amber-700 border-amber-200',
                    'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'emerald'=> 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'teal'   => 'bg-teal-50 text-teal-700 border-teal-200',
                    'slate'  => 'bg-slate-50 text-slate-600 border-slate-200',
                ];
                @endphp
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($rhSpatiePerms->sortBy('name') as $perm)
                        @php
                            $meta  = $permLabels[$perm->name] ?? ['label' => $perm->name, 'icon' => 'fa-key', 'color' => 'slate'];
                            $cls   = $colorMap[$meta['color']] ?? $colorMap['slate'];
                        @endphp
                        <div class="flex items-center gap-2.5 rounded-xl border {{ $cls }} px-3.5 py-2.5">
                            <i class="fas {{ $meta['icon'] }} w-4 text-center text-sm"></i>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold">{{ $meta['label'] }}</p>
                                <p class="text-[10px] font-mono opacity-60">{{ $perm->name }}</p>
                            </div>
                            <i class="fas fa-check ml-auto text-xs opacity-60"></i>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Légende --}}
            <div class="mt-5 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-4 text-[11px] text-slate-400">
                <span class="font-black uppercase tracking-widest">Légende :</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> Complet — lecture + écriture + suppression</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Lecture — consultation uniquement</span>
                <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-amber-500"></span> Partiel — action limitée</span>
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
                @if($selectedRoleSlug && isset($allRoles[$selectedRoleSlug]))
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">
                                Permissions — {{ $allRoles[$selectedRoleSlug] }}
                            </h3>
                            <p class="text-xs text-slate-400">Cochez les droits accordés par défaut à ce rôle.</p>
                        </div>
                        <span class="rounded-xl bg-indigo-100 px-3 py-1 text-xs font-black text-indigo-700">
                            {{ $rolePermissions->count() }} / {{ $permissions->count() }} accordées
                        </span>
                    </div>

                    @if($permissions->isEmpty())
                        <p class="py-8 text-center text-sm text-slate-400">Aucune permission enregistrée.</p>
                    @else
                        <form method="POST" action="{{ route('admin.settings.roles.permissions.sync', $selectedRoleSlug) }}" class="space-y-5">
                            @csrf
                            <div class="max-h-[520px] overflow-y-auto pr-1 space-y-4">
                                @foreach($permissionGroups as $prefix => $group)
                                    @if(count($group['items']) > 0)
                                        @php
                                            $colorMap = [
                                                'indigo' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-100', 'check' => 'text-indigo-600'],
                                                'emerald'=> ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'check' => 'text-emerald-600'],
                                                'sky'    => ['bg' => 'bg-sky-50',     'text' => 'text-sky-700',     'border' => 'border-sky-100',     'check' => 'text-sky-600'],
                                                'amber'  => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-100',   'check' => 'text-amber-600'],
                                                'rose'   => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'border' => 'border-rose-100',    'check' => 'text-rose-600'],
                                            ];
                                            $c = $colorMap[$group['color']] ?? $colorMap['indigo'];
                                        @endphp
                                        <div class="rounded-2xl border {{ $c['border'] }} overflow-hidden">
                                            <div class="{{ $c['bg'] }} px-4 py-2.5 flex items-center gap-2">
                                                <i class="{{ $group['icon'] }} text-xs {{ $c['text'] }}"></i>
                                                <span class="text-xs font-black uppercase tracking-wider {{ $c['text'] }}">{{ $group['label'] }}</span>
                                            </div>
                                            <div class="divide-y divide-slate-50">
                                                @foreach($group['items'] as $perm)
                                                    <label class="flex cursor-pointer items-start gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 {{ $c['check'] }} focus:ring-indigo-500"
                                                               @checked($rolePermissions->contains($perm->id))>
                                                        <div>
                                                            <p class="text-sm font-semibold text-slate-800">
                                                                {{ $group['labels'][$perm->name] ?? $perm->name }}
                                                            </p>
                                                            <p class="text-xs font-mono text-slate-400">{{ $perm->name }}</p>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="flex items-center gap-3 border-t border-slate-100 pt-4">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-indigo-700">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="{{ route('admin.settings.edit', ['tab' => 'roles', 'role' => $selectedRoleSlug]) }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-5 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-50">
                                    <i class="fas fa-times"></i> Annuler
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
        </div>{{-- /grid permissions --}}
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
                        <p class="py-8 text-center text-sm text-slate-400">Aucune permission enregistrée.</p>
                    @else
                        <form method="POST" action="{{ route('admin.settings.users.permissions.sync', $selectedUser) }}" class="space-y-4">
                            @csrf
                            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                                @foreach($permissions as $perm)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                               @checked($userPermissions->contains($perm->id))>
                                        <span class="font-mono text-sm text-slate-700">{{ $perm->name }}</span>
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
             TAB: ZONE DE DANGER
        ══════════════════════════════════════════════════════════════ --}}
        @if($activeTab === 'danger')
        <div class="space-y-6" id="danger">

            {{-- Erreurs --}}
            @if($errors->has('confirm_password'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first('confirm_password') }}
            </div>
            @endif

            {{-- Avertissement --}}
            <div class="flex items-start gap-4 rounded-2xl border-2 border-rose-200 bg-rose-50 px-5 py-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <p class="font-black text-rose-900">Actions irréversibles</p>
                    <p class="mt-0.5 text-sm text-rose-700">Ces opérations suppriment définitivement toutes les données. Elles ne peuvent pas être annulées. Confirmez avec votre mot de passe admin.</p>
                </div>
            </div>

            {{-- Purge évaluations --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="font-black text-slate-900">Archiver toutes les évaluations</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Déplace toutes les fiches d'évaluation dans l'archive. Elles disparaissent des vues normales
                            mais restent <strong>consultables et restaurables</strong> depuis la page d'archives.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span class="text-xs font-semibold text-rose-600">
                                <i class="fas fa-database mr-1"></i>
                                {{ \App\Models\Evaluation::count() }} évaluation(s) active(s)
                            </span>
                            @php $archivedEvals = \App\Models\Evaluation::onlyTrashed()->count(); @endphp
                            @if($archivedEvals)
                            <a href="{{ route('admin.archives.evaluations') }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:underline">
                                <i class="fas fa-archive"></i>
                                {{ $archivedEvals }} archivée(s) — Voir l'archive
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('admin.archives.evaluations') }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                            <i class="fas fa-archive text-xs"></i> Archives
                        </a>
                        <button type="button"
                                onclick="document.getElementById('modal-purge-evaluations').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                            <i class="fas fa-box-archive text-xs"></i> Tout archiver
                        </button>
                    </div>
                </div>
            </div>

            {{-- Purge objectifs --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="font-black text-slate-900">Archiver tous les objectifs</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Déplace toutes les fiches d'objectifs et objectifs classiques dans l'archive.
                            Ils restent <strong>consultables et restaurables</strong> depuis la page d'archives.
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span class="text-xs font-semibold text-rose-600">
                                <i class="fas fa-database mr-1"></i>
                                {{ \App\Models\FicheObjectif::count() }} fiche(s) · {{ \App\Models\Objectif::count() }} objectif(s) actif(s)
                            </span>
                            @php $archivedFiches = \App\Models\FicheObjectif::onlyTrashed()->count(); @endphp
                            @if($archivedFiches)
                            <a href="{{ route('admin.archives.objectifs') }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:underline">
                                <i class="fas fa-archive"></i>
                                {{ $archivedFiches }} archivée(s) — Voir l'archive
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('admin.archives.objectifs') }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                            <i class="fas fa-archive text-xs"></i> Archives
                        </a>
                        <button type="button"
                                onclick="document.getElementById('modal-purge-objectifs').classList.remove('hidden')"
                                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                            <i class="fas fa-box-archive text-xs"></i> Tout archiver
                        </button>
                    </div>
                </div>
            </div>

        </div>
        @endif {{-- /danger --}}


    </div>
</div>

{{-- ── MODALE : PURGE ÉVALUATIONS ──────────────────────────────────────── --}}
<div id="modal-purge-evaluations" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="relative w-full max-w-md rounded-[28px] bg-white p-6 shadow-2xl">
        <div class="mb-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 mb-4">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-rose-500">Zone de danger</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Archiver toutes les évaluations</h2>
            <p class="mt-2 text-sm text-slate-500">
                Toutes les évaluations seront <strong>archivées</strong> (pas supprimées).
                Elles resteront visibles et restaurables depuis la page
                <a href="{{ route('admin.archives.evaluations') }}" class="font-semibold text-indigo-600 hover:underline">Archives évaluations</a>.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.settings.purge.evaluations') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Confirmez avec votre mot de passe <span class="text-rose-500">*</span></label>
                <input name="confirm_password" type="password" required
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-rose-400 focus:ring-rose-400"
                       placeholder="Votre mot de passe admin">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                    <i class="fas fa-box-archive text-xs"></i> Confirmer l'archivage
                </button>
                <button type="button" onclick="document.getElementById('modal-purge-evaluations').classList.add('hidden')"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODALE : PURGE OBJECTIFS ─────────────────────────────────────────── --}}
<div id="modal-purge-objectifs" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="relative w-full max-w-md rounded-[28px] bg-white p-6 shadow-2xl">
        <div class="mb-5">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 mb-4">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <p class="text-xs font-black uppercase tracking-[0.25em] text-rose-500">Zone de danger</p>
            <h2 class="mt-1 text-xl font-black text-slate-900">Archiver tous les objectifs</h2>
            <p class="mt-2 text-sm text-slate-500">
                Toutes les fiches d'objectifs et objectifs classiques seront <strong>archivés</strong> (pas supprimés).
                Ils resteront visibles et restaurables depuis la page
                <a href="{{ route('admin.archives.objectifs') }}" class="font-semibold text-indigo-600 hover:underline">Archives objectifs</a>.
            </p>
        </div>
        <form method="POST" action="{{ route('admin.settings.purge.objectifs') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Confirmez avec votre mot de passe <span class="text-rose-500">*</span></label>
                <input name="confirm_password" type="password" required
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-rose-400 focus:ring-rose-400"
                       placeholder="Votre mot de passe admin">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                    <i class="fas fa-box-archive text-xs"></i> Confirmer l'archivage
                </button>
                <button type="button" onclick="document.getElementById('modal-purge-objectifs').classList.add('hidden')"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
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
        if (e.target.closest('[data-close="modal-create-rh"]')) {
            const m = document.getElementById('modal-create-rh');
            if (m) { m.classList.add('hidden'); m.style.display = ''; }
        }
    });

    // Bouton ouvrir modal création compte RH
    document.getElementById('open-create-rh-modal')?.addEventListener('click', function () {
        const m = document.getElementById('modal-create-rh');
        if (m) { m.classList.remove('hidden'); m.style.display = 'flex'; }
    });

    // Ré-ouvrir automatiquement si erreurs de validation du formulaire RH
    @if($errors->has('name') || $errors->has('email') || $errors->has('password'))
    (function () {
        const m = document.getElementById('modal-create-rh');
        if (m) { m.classList.remove('hidden'); m.style.display = 'flex'; }
    })();
    @endif

</script>
@endpush
