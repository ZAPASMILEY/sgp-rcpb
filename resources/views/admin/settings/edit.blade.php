@extends('layouts.app')

@section('title', 'Reglages | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .ios-settings-shell {
            min-height: 100vh;
            background: linear-gradient(180deg, #f4f5f9 0%, #efeff4 100%);
        }

        .ios-phone {
            max-width: 460px;
            margin-inline: auto;
        }

        .ios-title {
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #111827;
        }

        .ios-search {
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(10px);
        }

        .ios-group {
            border-radius: 1.15rem;
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 26px rgba(15, 23, 42, 0.05);
        }

        .ios-row {
            display: flex;
            align-items: center;
            width: 100%;
            gap: 0.85rem;
            padding: 0.92rem 1rem;
            color: #111827;
            background: #fff;
            border-bottom: 1px solid #f0f1f5;
            text-align: left;
            transition: background-color 120ms ease;
        }

        .ios-row:last-child {
            border-bottom: 0;
        }

        .ios-row:hover {
            background: #f9fafb;
        }

        .ios-row:active {
            background: #f3f4f6;
        }

        .ios-icon {
            width: 1.95rem;
            height: 1.95rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            color: #fff;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .ios-chevron {
            color: #c1c6d0;
            font-size: 1.35rem;
            line-height: 1;
            margin-left: auto;
        }

        .ios-group-label {
            padding-left: 0.45rem;
            font-size: 0.69rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 700;
        }

        .ios-radio {
            width: 1.2rem;
            height: 1.2rem;
            border-radius: 999px;
            border: 2px solid #d1d5db;
            margin-left: auto;
            position: relative;
            flex: 0 0 auto;
        }

        .ios-radio::after {
            content: "";
            position: absolute;
            inset: 2px;
            border-radius: inherit;
            background: #16a34a;
            transform: scale(0);
            transition: transform 120ms ease;
        }

        .theme-input:checked + .ios-row .ios-radio {
            border-color: #16a34a;
        }

        .theme-input:checked + .ios-row .ios-radio::after {
            transform: scale(1);
        }

        .ios-row-danger {
            color: #dc2626;
            font-weight: 600;
        }

        #password-modal .create-form-modal__panel,
        #delete-modal .create-form-modal__panel {
            width: min(92vw, 28rem);
            max-width: 28rem;
            border-radius: 1rem;
        }

        #password-modal .create-form-modal__header,
        #delete-modal .create-form-modal__header {
            padding: 0.8rem 0.95rem;
        }

        #password-modal .create-form-modal__title,
        #delete-modal .create-form-modal__title {
            font-size: 0.98rem;
        }
    </style>
@endpush

@section('content')
    <div class="ios-settings-shell px-4 py-6 sm:px-6">
        <div class="ios-phone space-y-5">
            <header class="space-y-3">
                <p class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500">Administration / Parametres</p>
                <h1 class="ios-title">Reglages</h1>

                <div class="ios-search flex items-center gap-2 px-4 py-2.5">
                    <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path></svg>
                    <span class="text-sm text-slate-400">Recherche</span>
                </div>

                @if (session('status'))
                    <div id="settings-status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif
            </header>

            <section class="space-y-2">
                <p class="ios-group-label">Apparence</p>
                <div class="ios-group">
                    <form id="theme-form" method="POST" action="{{ route('admin.settings.theme.update') }}">
                        @csrf
                        @method('PUT')

                        <label class="block cursor-pointer">
                            <input class="theme-input sr-only" type="radio" name="theme_preference" value="reference" @checked(old('theme_preference', $theme) === 'reference')>
                            <span class="ios-row">
                                <span class="ios-icon bg-slate-700">A</span>
                                <span>
                                    <span class="block text-base font-medium">Mode reference</span>
                                    <span class="block text-xs text-slate-500">Interface moderne neutre</span>
                                </span>
                                <span class="ios-radio"></span>
                            </span>
                        </label>

                        <label class="block cursor-pointer">
                            <input class="theme-input sr-only" type="radio" name="theme_preference" value="classic" @checked(old('theme_preference', $theme) === 'classic')>
                            <span class="ios-row">
                                <span class="ios-icon bg-emerald-600">C</span>
                                <span>
                                    <span class="block text-base font-medium">Mode classique</span>
                                    <span class="block text-xs text-slate-500">Palette verte officielle RCPB</span>
                                </span>
                                <span class="ios-radio"></span>
                            </span>
                        </label>

                        <button type="submit" class="sr-only" aria-hidden="true" tabindex="-1">Appliquer</button>
                    </form>
                </div>
                <p class="px-1 text-xs text-slate-500">Changement de theme optimise en un seul clic.</p>
                @error('theme_preference')
                    <p class="px-1 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </section>

            <section class="space-y-2">
                <p class="ios-group-label">Securite</p>
                <div class="ios-group">
                    <button id="open-password-modal" type="button" class="ios-row">
                        <span class="ios-icon bg-blue-500">M</span>
                        <span class="text-base font-medium">Changer le mot de passe</span>
                        <span class="ios-chevron">&#8250;</span>
                    </button>

                    <button id="open-delete-modal" type="button" class="ios-row ios-row-danger">
                        <span class="ios-icon bg-rose-500">S</span>
                        <span class="text-base">Supprimer le compte</span>
                        <span class="ios-chevron">&#8250;</span>
                    </button>
                </div>
                <p class="px-1 text-xs text-slate-500">Le message de confirmation disparait apres 5 secondes.</p>
            </section>

            <div id="password-modal" class="create-form-modal" aria-hidden="true" data-open-on-load="{{ ($errors->has('current_password') || $errors->has('password')) ? '1' : '0' }}">
                <div class="create-form-modal__panel" role="dialog" aria-modal="true" aria-labelledby="password-modal-title">
                    <div class="create-form-modal__header">
                        <p id="password-modal-title" class="create-form-modal__title">Changer le mot de passe</p>
                        <button id="close-password-modal" type="button" class="create-form-modal__close" aria-label="Fermer">&times;</button>
                    </div>

                    <div class="overflow-auto p-4 sm:p-5">
                        <form method="POST" action="{{ route('admin.settings.password.update') }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div class="space-y-2">
                                <label for="current_password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mot de passe actuel</label>
                                <input id="current_password" name="current_password" type="password" class="ent-input" required autocomplete="current-password">
                                @error('current_password')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <label for="password" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nouveau mot de passe</label>
                                    <input id="password" name="password" type="password" class="ent-input" required autocomplete="new-password">
                                </div>
                                <div class="space-y-2">
                                    <label for="password_confirmation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Confirmation</label>
                                    <input id="password_confirmation" name="password_confirmation" type="password" class="ent-input" required autocomplete="new-password">
                                </div>
                            </div>
                            @error('password')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <div class="flex justify-end">
                                <button type="submit" class="ent-btn ent-btn-primary">Mettre a jour</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="delete-modal" class="create-form-modal" aria-hidden="true" data-open-on-load="{{ $errors->has('delete_password') ? '1' : '0' }}">
                <div class="create-form-modal__panel" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
                    <div class="create-form-modal__header">
                        <p id="delete-modal-title" class="create-form-modal__title">Suppression du compte</p>
                        <button id="close-delete-modal" type="button" class="create-form-modal__close" aria-label="Fermer">&times;</button>
                    </div>

                    <div class="overflow-auto p-4 sm:p-5">
                        <p class="text-sm text-rose-700">Action irreversible. Entrez votre mot de passe pour confirmer.</p>

                        <form method="POST" action="{{ route('admin.settings.account.destroy') }}" class="mt-4 space-y-3" onsubmit="return confirm('Confirmer la suppression definitive du compte ?');">
                            @csrf
                            @method('DELETE')

                            <div class="space-y-2">
                                <label for="delete_password_modal" class="text-xs font-semibold uppercase tracking-[0.15em] text-rose-700">Mot de passe de confirmation</label>
                                <input id="delete_password_modal" name="delete_password" type="password" class="ent-input" required autocomplete="current-password">
                                @error('delete_password')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
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
            function wireModal(modalId, openId, closeId) {
                var modal = document.getElementById(modalId);
                var openButton = document.getElementById(openId);
                var closeButton = document.getElementById(closeId);

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

                if (modal.getAttribute('data-open-on-load') === '1') {
                    openModal();
                }
            }

            var themeForm = document.getElementById('theme-form');
            if (themeForm) {
                var themeInputs = themeForm.querySelectorAll('input[name="theme_preference"]');
                var isSubmitting = false;

                themeInputs.forEach(function (input) {
                    input.addEventListener('change', function () {
                        if (isSubmitting) {
                            return;
                        }

                        isSubmitting = true;
                        themeForm.submit();
                    });
                });
            }

            var statusBox = document.getElementById('settings-status');
            if (statusBox) {
                window.setTimeout(function () {
                    statusBox.style.transition = 'opacity 220ms ease';
                    statusBox.style.opacity = '0';
                    window.setTimeout(function () {
                        statusBox.remove();
                    }, 250);
                }, 5000);
            }

            wireModal('password-modal', 'open-password-modal', 'close-password-modal');
            wireModal('delete-modal', 'open-delete-modal', 'close-delete-modal');
        });
    </script>
@endpush