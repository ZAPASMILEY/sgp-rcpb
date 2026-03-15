@extends('layouts.pca')

@section('title', 'Parametres PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-5 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-5xl flex-col gap-5">
            <header class="admin-panel px-6 py-5 lg:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA / Parametres</p>
                <div class="mt-2 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Parametres</h1>
                        <p class="mt-1 text-sm text-slate-600">Theme et securite du compte PCA.</p>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
                <section class="admin-panel px-6 py-5 lg:px-7">
                    <h2 class="text-lg font-semibold text-slate-900">Theme</h2>
                    <p class="mt-1 text-sm text-slate-600">Choisissez le style visuel de l'interface.</p>

                    <form method="POST" action="{{ route('pca.settings.theme.update') }}" class="mt-4 space-y-3">
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

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </section>

                <section class="admin-panel px-6 py-5 lg:px-7">
                    <h2 class="text-lg font-semibold text-slate-900">Mot de passe</h2>
                    <p class="mt-1 text-sm text-slate-600">Mettez a jour l'acces de votre compte PCA.</p>

                    <form method="POST" action="{{ route('pca.settings.password.update') }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PUT')

                        <div class="space-y-2">
                            <label for="current_password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Actuel</label>
                            <input id="current_password" name="current_password" type="password" class="ent-input" required autocomplete="current-password">
                            @error('current_password')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nouveau</label>
                                <input id="password" name="password" type="password" class="ent-input" required autocomplete="new-password">
                            </div>

                            <div class="space-y-2">
                                <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Confirmation</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="ent-input" required autocomplete="new-password">
                            </div>
                        </div>
                        @error('password')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="ent-btn ent-btn-primary">Mettre a jour</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
@endsection
