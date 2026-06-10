@extends('layouts.pca')
@section('title', 'Paramètres | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-800 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative">
            <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">PCA · Compte</p>
            <h1 class="mt-1 text-2xl font-black leading-tight text-white">Paramètres</h1>
            <p class="mt-0.5 text-sm text-slate-300/80">Gérez vos préférences et la sécurité de votre compte.</p>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="mx-auto max-w-2xl flex flex-col gap-5">

            @if (session('status'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                    <i class="fas fa-circle-check shrink-0"></i> {{ session('status') }}
                </div>
            @endif

            {{-- ── Thème ──────────────────────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Apparence</p>
                    <p class="mt-0.5 text-sm font-black text-slate-800">Thème de l'interface</p>
                </div>
                <form method="POST" action="{{ route('pca.settings.theme.update') }}" class="px-6 py-5">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach (['reference' => ['label' => 'Référence', 'desc' => 'Thème officiel par défaut', 'icon' => 'fas fa-star'], 'classic' => ['label' => 'Classique', 'desc' => 'Interface sobre et épurée', 'icon' => 'fas fa-circle']] as $val => $opt)
                        <label class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 p-4 transition
                            {{ $theme === $val ? 'border-slate-700 bg-slate-50' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                            <input type="radio" name="theme_preference" value="{{ $val }}"
                                   {{ $theme === $val ? 'checked' : '' }}
                                   class="sr-only">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                {{ $theme === $val ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-500' }}">
                                <i class="{{ $opt['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black {{ $theme === $val ? 'text-slate-900' : 'text-slate-700' }}">{{ $opt['label'] }}</p>
                                <p class="text-xs text-slate-500">{{ $opt['desc'] }}</p>
                            </div>
                            @if ($theme === $val)
                                <i class="fas fa-circle-check ml-auto text-slate-700"></i>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-700">
                            <i class="fas fa-floppy-disk text-xs"></i> Enregistrer le thème
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Mot de passe ────────────────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Sécurité</p>
                    <p class="mt-0.5 text-sm font-black text-slate-800">Changer le mot de passe</p>
                </div>
                <form method="POST" action="{{ route('pca.settings.password.update') }}" class="px-6 py-5">
                    @csrf @method('PUT')
                    <div class="flex flex-col gap-4">
                        <div>
                            <label for="current_password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1.5">Mot de passe actuel</label>
                            <input id="current_password" type="password" name="current_password" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:bg-white @error('current_password') border-rose-400 @enderror">
                            @error('current_password')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1.5">Nouveau mot de passe</label>
                            <input id="password" type="password" name="password" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:bg-white @error('password') border-rose-400 @enderror">
                            @error('password')
                                <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1.5">Confirmer le nouveau mot de passe</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-slate-400 focus:bg-white">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-700">
                            <i class="fas fa-lock text-xs"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Supprimer le compte ─────────────────────────────────────────── --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-rose-100">
                <div class="border-b border-rose-100 bg-rose-50/60 px-6 py-4">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-rose-400">Zone dangereuse</p>
                    <p class="mt-0.5 text-sm font-black text-rose-800">Supprimer le compte</p>
                </div>
                <form method="POST" action="{{ route('pca.settings.account.destroy') }}"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')"
                      class="px-6 py-5">
                    @csrf @method('DELETE')
                    <p class="text-sm text-slate-600 mb-4">Cette action est <strong>irréversible</strong>. Toutes vos données seront supprimées définitivement.</p>
                    <div>
                        <label for="delete_password" class="block text-xs font-black uppercase tracking-[0.12em] text-slate-500 mb-1.5">Confirmez avec votre mot de passe</label>
                        <input id="delete_password" type="password" name="delete_password" required
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-rose-400 focus:bg-white @error('delete_password') border-rose-400 @enderror">
                        @error('delete_password')
                            <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                            <i class="fas fa-trash text-xs"></i> Supprimer mon compte
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
