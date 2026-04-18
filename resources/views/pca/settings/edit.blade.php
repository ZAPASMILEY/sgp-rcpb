@extends('layouts.pca')

@section('title', 'Parametres PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-5 sm:px-6 lg:px-10">
        <div class="mb-4">
            <button onclick="history.back()" class="ent-btn ent-btn-soft"><i class="fas fa-arrow-left mr-2"></i>Retour</button>
        </div>
        <div class="w-full flex flex-col gap-5">
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
                <div id="pca-settings-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-settings-status-message')?.remove(), 3000);</script>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <!-- Carte Apparence -->
                <div class="rounded-2xl bg-gradient-to-br from-cyan-50 to-blue-100 p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-500">
                            <i class="fas fa-palette text-lg"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Personnalisation</h3>
                            <p class="text-xs text-slate-400">Choisissez l'ambiance visuelle du portail.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('pca.settings.theme.update') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf
                        @method('PUT')
                        <label class="relative cursor-pointer group">
                            <input class="peer sr-only" type="radio" name="theme_preference" value="reference" @checked(old('theme_preference', $theme) === 'reference') onchange="this.form.submit()">
                            <div class="rounded-2xl border-2 border-slate-100 bg-white p-5 transition-all peer-checked:border-cyan-500 peer-checked:bg-cyan-50/30 group-hover:border-slate-200">
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
                            <div class="rounded-2xl border-2 border-slate-100 bg-white p-5 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50/30 group-hover:border-slate-200">
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

                <!-- Carte Mot de passe & Zone de danger -->
                <div class="space-y-6">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-500">
                                <i class="fas fa-key text-lg"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">Mot de passe</h3>
                                <p class="text-xs text-slate-400">Mettez à jour l'accès de votre compte PCA.</p>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button id="open-password-modal" type="button" class="ent-btn ent-btn-primary">Changer le mot de passe</button>
                        </div>
                        <div id="password-modal" class="create-form-modal" aria-hidden="true" data-open-on-load="{{ ($errors->has('current_password') || $errors->has('password')) ? '1' : '0' }}">
                            <div class="create-form-modal__panel" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
                                <div class="create-form-modal__header">
                                    <p id="password-modal-title" class="create-form-modal__title">Changer le mot de passe</p>
                                    <button id="close-password-modal" type="button" class="create-form-modal__close" aria-label="Fermer">&times;</button>
                                </div>
                                <div class="overflow-auto p-4 sm:p-6">
                                    <form method="POST" action="{{ route('pca.settings.password.update') }}" class="space-y-3">
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
                                            <button type="submit" class="ent-btn ent-btn-primary">Mettre à jour</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-6 shadow-sm">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fas fa-radiation text-rose-500 animate-pulse text-lg"></i>
                            <h3 class="text-xs font-black uppercase tracking-wider text-rose-500">Zone Dangereuse</h3>
                        </div>
                        <p class="mb-5 text-xs text-rose-700 leading-relaxed">Supprimer votre compte est irréversible. Vos évaluations créées seront aussi supprimées.</p>
                        <form method="POST" action="{{ route('pca.settings.account.destroy') }}" class="space-y-3" onsubmit="return confirm('Confirmer la suppression définitive du compte ?');">
                            @csrf
                            @method('DELETE')
                            <div class="space-y-2">
                                <label for="delete_password" class="text-xs font-semibold uppercase tracking-[0.15em] text-rose-700">Mot de passe de confirmation</label>
                                <input id="delete_password" name="delete_password" type="password" class="ent-input" required autocomplete="current-password">
                                @error('delete_password')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="ent-btn ent-btn-danger">Supprimer le compte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('password-modal');
            var openButton = document.getElementById('open-password-modal');
            var closeButton = document.getElementById('close-password-modal');

            if (!modal || !openButton || !closeButton) {
                return;
            }

            function openModal() {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }

            openButton.addEventListener('click', openModal);
            closeButton.addEventListener('click', closeModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            var shouldOpenModal = modal.getAttribute('data-open-on-load') === '1';
            if (shouldOpenModal) {
                openModal();
            }
        });
    </script>
@endpush
