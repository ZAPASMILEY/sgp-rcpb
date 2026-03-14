@extends('layouts.app')

@section('title', 'Parametres | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Parametres</h1>
                <p class="mt-2 text-sm text-slate-600">Personnalisez le theme et securisez votre compte administrateur.</p>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-semibold text-slate-900">Theme de l'application</h2>
                <p class="mt-1 text-sm text-slate-600">Choisissez le style visuel de votre interface admin.</p>

                <form method="POST" action="{{ route('admin.settings.theme.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="theme_preference" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Theme</label>
                        <select id="theme_preference" name="theme_preference" class="ent-select" required>
                            <option value="reference" @selected(old('theme_preference', $theme) === 'reference')>Reference (moderne)</option>
                            <option value="classic" @selected(old('theme_preference', $theme) === 'classic')>Classique (vert)</option>
                        </select>
                        @error('theme_preference')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="ent-btn ent-btn-primary">Enregistrer le theme</button>
                    </div>
                </form>
            </section>

            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-semibold text-slate-900">Mot de passe</h2>
                <p class="mt-1 text-sm text-slate-600">Mettez a jour le mot de passe de votre compte administrateur.</p>

                <form method="POST" action="{{ route('admin.settings.password.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="current_password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mot de passe actuel</label>
                        <input id="current_password" name="current_password" type="password" class="ent-input" required autocomplete="current-password">
                        @error('current_password')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nouveau mot de passe</label>
                        <input id="password" name="password" type="password" class="ent-input" required autocomplete="new-password">
                        @error('password')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Confirmer le nouveau mot de passe</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="ent-input" required autocomplete="new-password">
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="ent-btn ent-btn-primary">Mettre a jour le mot de passe</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
