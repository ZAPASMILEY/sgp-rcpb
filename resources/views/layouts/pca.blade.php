<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

        @php
            $hasViteBuild = file_exists(public_path('build/manifest.json'));
            $hasViteHot = file_exists(public_path('hot'));
            $requestHost = request()->getHost();
            $isLocalHost = in_array($requestHost, ['127.0.0.1', 'localhost'], true);
        @endphp


        @if ($hasViteBuild || ($hasViteHot && $isLocalHost))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="{{ asset('css/admin-fallback.css') }}">
        @endif

        @livewireStyles

        <style>
            .create-form-modal {
                position: fixed;
                inset: 0;
                z-index: 2200;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(2px);
            }

            .create-form-modal.is-open {
                display: flex;
            }

            .create-form-modal__panel {
                width: min(980px, 96vw);
                height: min(86vh, 860px);
                border-radius: 18px;
                border: 1px solid rgba(148, 163, 184, 0.45);
                background: #ffffff;
                overflow: hidden;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
                display: grid;
                grid-template-rows: auto 1fr;
            }

            .create-form-modal__header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                border-bottom: 1px solid rgba(226, 232, 240, 0.9);
                background: #f8fafc;
            }

            .create-form-modal__title {
                margin: 0;
                font-size: 0.85rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                font-weight: 700;
                color: #475569;
            }

            .create-form-modal__close {
                border: 0;
                background: #e2e8f0;
                color: #334155;
                width: 2rem;
                height: 2rem;
                border-radius: 999px;
                font-size: 1.2rem;
                line-height: 1;
                cursor: pointer;
            }

            .create-form-modal__frame {
                width: 100%;
                height: 100%;
                border: 0;
                background: #ffffff;
            }
        </style>

        @stack('head')
    </head>
    <body>
        @livewireScripts
        @php
            $themePreference = auth()->check() ? (auth()->user()->theme_preference ?? 'reference') : 'reference';
            $adminThemeClass = $themePreference === 'reference' ? 'admin-layout--reference' : '';
            $displayYear = (int) request()->query('annee', now()->year);
            $pcaTopbarLabel = match (true) {
                request()->routeIs('pca.dashboard') => 'Tableau de bord',
                request()->routeIs('pca.statistiques.*') => 'Pilotage / Statistiques',
                request()->routeIs('pca.objectifs.*') => 'Pilotage / Objectifs',
                request()->routeIs('pca.evaluations.*') => 'Pilotage / Évaluations',
                request()->routeIs('pca.settings.*') => 'Administration / Paramètres',
                default => 'PCA',
            };
            $userInitial = auth()->check() && filled(auth()->user()->name)
                ? strtoupper(substr(auth()->user()->name, 0, 1))
                : 'P';
        @endphp

        <div id="admin-layout" class="admin-layout min-h-screen {{ $adminThemeClass }}">
            <aside id="admin-sidebar" class="admin-sidebar">
                <div class="admin-sidebar__logo-card">
                    <div class="admin-sidebar__logo-mark">R</div>
                    <div>
                        <p class="admin-sidebar__logo-title">RCPB</p>
                        <p class="admin-sidebar__logo-subtitle">Réseau des Caisses</p>
                    </div>
                </div>

                <div class="admin-sidebar__brand">
                    <div class="admin-sidebar__brand-row">
                        <div>
                            <p class="admin-sidebar__subtitle">Espace PCA</p>
                            <p class="admin-sidebar__title">SGP RCPB</p>
                        </div>
                        <button id="sidebar-toggle" type="button" class="admin-sidebar__toggle" aria-label="Replier le menu" title="Replier le menu">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                </div>

                <nav class="admin-sidebar__nav" aria-label="Navigation PCA">
                    <a href="{{ route('pca.dashboard') }}" class="admin-tab {{ request()->routeIs('pca.dashboard') ? 'is-active' : '' }}">
                        <span class="admin-tab__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 1 18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6Z"/></svg>
                        </span>
                        <span class="admin-tab__label">Tableau de bord</span>
                    </a>
                    <a href="{{ route('pca.objectifs.index') }}" class="admin-tab {{ request()->routeIs('pca.objectifs.*') ? 'is-active' : '' }}">
                        <span class="admin-tab__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Zm2.25 4.5h6m-6 3h6m-6 3h3"/></svg>
                        </span>
                        <span class="admin-tab__label">Objectifs</span>
                    </a>
                    <a href="{{ route('pca.statistiques.index') }}" class="admin-tab {{ request()->routeIs('pca.statistiques.*') ? 'is-active' : '' }}">
                        <span class="admin-tab__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V10.5m5.25 9V6.75m5.25 12.75v-6m5.25 6V4.5"/></svg>
                        </span>
                        <span class="admin-tab__label">Statistiques</span>
                    </a>
                    <a href="{{ route('pca.evaluations.index') }}" class="admin-tab {{ request()->routeIs('pca.evaluations.*') ? 'is-active' : '' }}">
                        <span class="admin-tab__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 12 3 3 6-6M4.5 5.25h15v13.5h-15z"/></svg>
                        </span>
                        <span class="admin-tab__label">Évaluations</span>
                    </a>
                    <a href="{{ route('pca.settings.edit') }}" class="admin-tab {{ request()->routeIs('pca.settings.*') ? 'is-active' : '' }}">
                        <span class="admin-tab__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 3.75h3l.6 2.07a7.6 7.6 0 0 1 1.77.72l1.95-1.05 2.12 2.12-1.05 1.95c.29.56.53 1.15.72 1.77l2.07.6v3l-2.07.6a7.6 7.6 0 0 1-.72 1.77l1.05 1.95-2.12 2.12-1.95-1.05a7.6 7.6 0 0 1-1.77.72l-.6 2.07h-3l-.6-2.07a7.6 7.6 0 0 1-1.77-.72l-1.95 1.05-2.12-2.12 1.05-1.95a7.6 7.6 0 0 1-.72-1.77l-2.07-.6v-3l2.07-.6c.19-.62.43-1.21.72-1.77L3.4 7.6 5.52 5.48l1.95 1.05c.56-.29 1.15-.53 1.77-.72l.6-2.06Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        </span>
                        <span class="admin-tab__label">Paramètres</span>
                    </a>
                </nav>

                <form method="POST" action="{{ route('pca.logout') }}" class="admin-sidebar__logout">
                    @csrf
                    <button type="submit" class="ent-btn ent-btn-soft w-full">Déconnexion</button>
                </form>
            </aside>

            <main class="admin-content">
                <button id="sidebar-expand" type="button" class="admin-sidebar__expand hidden" aria-label="Ouvrir le menu" title="Ouvrir le menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <header class="admin-topbar">
                    <p class="admin-topbar__title">{{ $pcaTopbarLabel }}</p>
                    <div class="admin-topbar__actions" id="admin-topbar-actions">
                        <span class="hidden rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 sm:inline-flex">Année {{ $displayYear }}</span>
                        <button id="topbar-quick-toggle" type="button" class="admin-topbar__icon" aria-label="Actions rapides" aria-expanded="false" aria-controls="topbar-quick-panel">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Zm8.25 3.75-.94-.27a7.9 7.9 0 0 0-.67-1.6l.53-.82-1.76-1.76-.82.53c-.51-.28-1.04-.5-1.6-.67l-.27-.94h-2.5l-.27.94c-.56.17-1.09.39-1.6.67l-.82-.53-1.76 1.76.53.82c-.28.51-.5 1.04-.67 1.6l-.94.27v2.5l.94.27c.17.56.39 1.09.67 1.6l-.53.82 1.76 1.76.82-.53c.51.28 1.04.5 1.6.67l.27.94h2.5l.27-.94c.56-.17 1.09-.39 1.6-.67l.82.53 1.76-1.76-.53-.82c.28-.51.5-1.04.67-1.6l.94-.27v-2.5Z"/></svg>
                        </button>
                        <button id="topbar-profile-toggle" type="button" class="admin-topbar__avatar admin-topbar__avatar--button" aria-label="Profil" aria-expanded="false" aria-controls="topbar-profile-panel">{{ $userInitial }}</button>

                        <div id="topbar-quick-panel" class="admin-topbar__panel admin-topbar__panel--quick hidden" role="menu" aria-label="Actions rapides">
                            <p class="admin-topbar__panel-caption">Actions rapides</p>
                            <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Nouvelle évaluation" class="admin-topbar__quick-link">Nouvelle évaluation</a>
                            <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Nouvel objectif" class="admin-topbar__quick-link">Nouvel objectif</a>
                            <a href="{{ route('pca.settings.edit') }}" class="admin-topbar__quick-link">Ouvrir paramètres</a>
                        </div>

                        <div id="topbar-profile-panel" class="admin-topbar__panel admin-topbar__panel--profile hidden" role="menu" aria-label="Profil">
                            <p class="admin-topbar__panel-caption">Compte PCA</p>
                            <a href="{{ route('pca.settings.edit') }}" class="admin-topbar__quick-link">Mon profil et sécurité</a>
                            <form method="POST" action="{{ route('pca.logout') }}">
                                @csrf
                                <button type="submit" class="admin-topbar__quick-link admin-topbar__quick-link--danger">Se déconnecter</button>
                            </form>
                        </div>
                    </div>
                </header>

                @yield('content')
            </main>
        </div>

        <div id="create-form-modal" class="create-form-modal" aria-hidden="true">
            <div class="create-form-modal__panel" role="dialog" aria-modal="true" aria-labelledby="create-form-modal-title">
                <div class="create-form-modal__header">
                    <p id="create-form-modal-title" class="create-form-modal__title">Formulaire d'ajout</p>
                    <button id="create-form-modal-close" type="button" class="create-form-modal__close" aria-label="Fermer">&times;</button>
                </div>
                <iframe id="create-form-modal-frame" class="create-form-modal__frame" loading="lazy"></iframe>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var layout = document.getElementById('admin-layout');
                var collapseButton = document.getElementById('sidebar-toggle');
                var expandButton = document.getElementById('sidebar-expand');
                var collapsedClass = 'admin-layout--collapsed';
                var closedClass = 'admin-layout--closed';
                var storageKey = 'pca-sidebar-state';

                function syncExpandButton() {
                    if (!layout || !expandButton) { return; }
                    if (layout.classList.contains(collapsedClass) || layout.classList.contains(closedClass)) {
                        expandButton.classList.remove('hidden');
                    } else {
                        expandButton.classList.add('hidden');
                    }
                }

                if (layout) {
                    var savedState = window.localStorage.getItem(storageKey);
                    var isMobileViewport = window.matchMedia('(max-width: 1024px)').matches;

                    if (!savedState) {
                        savedState = isMobileViewport ? 'closed' : 'open';
                    }

                    if (savedState === 'collapsed') {
                        layout.classList.add(collapsedClass);
                        layout.classList.remove(closedClass);
                    } else if (savedState === 'closed') {
                        layout.classList.add(closedClass);
                        layout.classList.remove(collapsedClass);
                    } else {
                        layout.classList.remove(collapsedClass, closedClass);
                    }
                    syncExpandButton();
                }

                if (collapseButton && layout) {
                    collapseButton.addEventListener('click', function () {
                        layout.classList.add(closedClass);
                        layout.classList.remove(collapsedClass);
                        window.localStorage.setItem(storageKey, 'closed');
                        syncExpandButton();
                    });
                }

                if (expandButton && layout) {
                    expandButton.addEventListener('click', function () {
                        layout.classList.remove(closedClass, collapsedClass);
                        window.localStorage.setItem(storageKey, 'open');
                        syncExpandButton();
                    });
                }

                var topbarContainer = document.getElementById('admin-topbar-actions');
                var quickToggle = document.getElementById('topbar-quick-toggle');
                var profileToggle = document.getElementById('topbar-profile-toggle');
                var quickPanel = document.getElementById('topbar-quick-panel');
                var profilePanel = document.getElementById('topbar-profile-panel');

                function closeAllTopbarPanels() {
                    [quickPanel, profilePanel].forEach(function (panel) {
                        if (panel) { panel.classList.add('hidden'); }
                    });
                    [quickToggle, profileToggle].forEach(function (toggle) {
                        if (toggle) { toggle.setAttribute('aria-expanded', 'false'); }
                    });
                }

                function toggleTopbarPanel(toggle, panel) {
                    if (!toggle || !panel) { return; }
                    var isClosed = panel.classList.contains('hidden');
                    closeAllTopbarPanels();
                    if (isClosed) {
                        panel.classList.remove('hidden');
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                }

                if (topbarContainer) {
                    if (quickToggle && quickPanel) {
                        quickToggle.addEventListener('click', function () {
                            toggleTopbarPanel(quickToggle, quickPanel);
                        });
                    }
                    if (profileToggle && profilePanel) {
                        profileToggle.addEventListener('click', function () {
                            toggleTopbarPanel(profileToggle, profilePanel);
                        });
                    }
                    document.addEventListener('click', function (event) {
                        if (!topbarContainer.contains(event.target)) {
                            closeAllTopbarPanels();
                        }
                    });
                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') { closeAllTopbarPanels(); }
                    });
                }

                document.querySelectorAll('[data-auto-dismiss]').forEach(function (element) {
                    var delay = Number(element.getAttribute('data-auto-dismiss')) || 4000;
                    window.setTimeout(function () {
                        element.style.transition = 'opacity 220ms ease, transform 220ms ease';
                        element.style.opacity = '0';
                        element.style.transform = 'translateY(-6px)';
                        window.setTimeout(function () { element.remove(); }, 240);
                    }, delay);
                });

                var createModal = document.getElementById('create-form-modal');
                var createFrame = document.getElementById('create-form-modal-frame');
                var createCloseButton = document.getElementById('create-form-modal-close');
                var createTitle = document.getElementById('create-form-modal-title');

                if (createModal && createFrame && createCloseButton && createTitle) {
                    function closeCreateModal() {
                        createModal.classList.remove('is-open');
                        createModal.setAttribute('aria-hidden', 'true');
                        createFrame.removeAttribute('src');
                    }

                    function openCreateModal(url, label) {
                        createFrame.src = url;
                        createTitle.textContent = label || "Formulaire d'ajout";
                        createModal.classList.add('is-open');
                        createModal.setAttribute('aria-hidden', 'false');
                    }

                    document.addEventListener('click', function (event) {
                        var trigger = event.target.closest('[data-open-create-modal]');

                        if (trigger) {
                            event.preventDefault();
                            openCreateModal(trigger.getAttribute('href'), trigger.getAttribute('data-modal-title'));
                            return;
                        }

                        if (event.target === createModal || event.target === createCloseButton) {
                            closeCreateModal();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape' && createModal.classList.contains('is-open')) {
                            closeCreateModal();
                        }
                    });

                    createFrame.addEventListener('load', function () {
                        if (!createModal.classList.contains('is-open')) {
                            return;
                        }

                        try {
                            var currentPath = createFrame.contentWindow.location.pathname || '';
                            var isCreatePage = currentPath.indexOf('/creer') !== -1 || currentPath.indexOf('/create') !== -1;

                            if (!isCreatePage) {
                                closeCreateModal();
                                window.location.reload();
                            }
                        } catch (error) {
                            // Keep modal open if frame location cannot be inspected.
                        }
                    });
                }
            });
        </script>

        @stack('scripts')
    </body>
</html>
