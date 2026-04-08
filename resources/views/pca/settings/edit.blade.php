@extends('layouts.pca')

@section('title', 'Parametres PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-5 sm:px-6 lg:px-10">
        <div class="mb-4">
            <button onclick="history.back()" class="ent-btn ent-btn-soft"><i class="fas fa-arrow-left mr-2"></i>Retour</button>
        </div>
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
                <div id="pca-settings-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-settings-status-message')?.remove(), 3000);</script>
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

                    <div class="mt-4 flex justify-end">
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
                                        <button type="submit" class="ent-btn ent-btn-primary">Mettre a jour</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                        <h3 class="text-sm font-semibold uppercase tracking-[0.12em] text-rose-700">Zone dangereuse</h3>
                        <p class="mt-1 text-sm text-rose-700">Supprimer votre compte est irreversible. Vos evaluations creees seront aussi supprimees.</p>

                        <form method="POST" action="{{ route('pca.settings.account.destroy') }}" class="mt-3 space-y-3" onsubmit="return confirm('Confirmer la suppression definitive du compte ?');">
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
                </section>
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
