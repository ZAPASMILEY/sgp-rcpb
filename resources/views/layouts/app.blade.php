<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="{{ asset('css/admin-fallback.css') }}">
        @endif

        @stack('head')
    </head>
    <body>
        @php
            $showAdminSidebar = request()->routeIs('admin.*') && !request()->routeIs('login');
            $themePreference = auth()->check() ? (auth()->user()->theme_preference ?? 'reference') : 'reference';
            $adminThemeClass = $themePreference === 'reference' ? 'admin-layout--reference' : '';
            $adminTopbarLabel = match (true) {
                request()->routeIs('admin.dashboard') => 'Tableau de bord',
                request()->routeIs('admin.entites.*') => 'Referentiel / Entites',
                request()->routeIs('admin.directions.*') => 'Referentiel / Directions',
                request()->routeIs('admin.services.*') => 'Referentiel / Services',
                request()->routeIs('admin.agents.*') => 'Referentiel / Agents',
                request()->routeIs('admin.objectifs.*') => 'Pilotage / Objectifs',
                request()->routeIs('admin.evaluations.*') => 'Pilotage / Evaluations',
                request()->routeIs('admin.settings.*') => 'Administration / Parametres',
                default => 'Administration',
            };
            $adminUserInitial = auth()->check() && filled(auth()->user()->name)
                ? strtoupper(substr(auth()->user()->name, 0, 1))
                : 'A';
        @endphp

        @if ($showAdminSidebar)
            <div id="admin-layout" class="admin-layout min-h-screen {{ $adminThemeClass }}">
                <aside id="admin-sidebar" class="admin-sidebar">
                    <div class="admin-sidebar__logo-card">
                        <div class="admin-sidebar__logo-mark">R</div>
                        <div>
                            <p class="admin-sidebar__logo-title">RCPB</p>
                            <p class="admin-sidebar__logo-subtitle">Reseau des Caisses</p>
                        </div>
                    </div>

                    <div class="admin-sidebar__brand">
                        <div class="admin-sidebar__brand-row">
                            <div>
                                <p class="admin-sidebar__subtitle">Administration</p>
                                <p class="admin-sidebar__title">SGP RCPB</p>
                            </div>
                            <button id="sidebar-toggle" type="button" class="admin-sidebar__toggle" aria-label="Replier le menu" title="Replier le menu">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <nav class="admin-sidebar__nav" aria-label="Navigation principale">
                        <a href="{{ route('admin.dashboard') }}" class="admin-tab {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 1 18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6Z"/></svg>
                            </span>
                            <span class="admin-tab__label">Dashboard</span>
                        </a>
                        <a href="{{ route('admin.entites.index') }}" class="admin-tab {{ request()->routeIs('admin.entites.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21V7.5l7.5-4.5 7.5 4.5V21M9 21v-6h6v6"/></svg>
                            </span>
                            <span class="admin-tab__label">Entites</span>
                        </a>
                        <a href="{{ route('admin.directions.index') }}" class="admin-tab {{ request()->routeIs('admin.directions.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 4.5-9 4.5-9-4.5L12 3Zm0 9v9m-6-6 6 3 6-3"/></svg>
                            </span>
                            <span class="admin-tab__label">Directions</span>
                        </a>
                        <a href="{{ route('admin.services.index') }}" class="admin-tab {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"/></svg>
                            </span>
                            <span class="admin-tab__label">Services</span>
                        </a>
                        <a href="{{ route('admin.agents.index') }}" class="admin-tab {{ request()->routeIs('admin.agents.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Zm-7.5 7.5a7.5 7.5 0 1 1 15 0"/></svg>
                            </span>
                            <span class="admin-tab__label">Agents</span>
                        </a>
                        <a href="{{ route('admin.objectifs.index') }}" class="admin-tab {{ request()->routeIs('admin.objectifs.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Zm2.25 4.5h6m-6 3h6m-6 3h3"/></svg>
                            </span>
                            <span class="admin-tab__label">Objectifs</span>
                        </a>
                        <a href="{{ route('admin.evaluations.index') }}" class="admin-tab {{ request()->routeIs('admin.evaluations.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 12 3 3 6-6M4.5 5.25h15v13.5h-15z"/></svg>
                            </span>
                            <span class="admin-tab__label">Evaluations</span>
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="admin-tab {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                            <span class="admin-tab__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 3.75h3l.6 2.07a7.6 7.6 0 0 1 1.77.72l1.95-1.05 2.12 2.12-1.05 1.95c.29.56.53 1.15.72 1.77l2.07.6v3l-2.07.6a7.6 7.6 0 0 1-.72 1.77l1.05 1.95-2.12 2.12-1.95-1.05a7.6 7.6 0 0 1-1.77.72l-.6 2.07h-3l-.6-2.07a7.6 7.6 0 0 1-1.77-.72l-1.95 1.05-2.12-2.12 1.05-1.95a7.6 7.6 0 0 1-.72-1.77l-2.07-.6v-3l2.07-.6c.19-.62.43-1.21.72-1.77L3.4 7.6 5.52 5.48l1.95 1.05c.56-.29 1.15-.53 1.77-.72l.6-2.06Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            </span>
                            <span class="admin-tab__label">Parametres</span>
                        </a>
                    </nav>

                    <form method="POST" action="{{ route('admin.logout') }}" class="admin-sidebar__logout">
                        @csrf
                        <button type="submit" class="ent-btn ent-btn-soft w-full">Deconnexion</button>
                    </form>
                </aside>

                <main class="admin-content">
                    <button id="sidebar-expand" type="button" class="admin-sidebar__expand hidden" aria-label="Ouvrir le menu" title="Ouvrir le menu">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                    <header class="admin-topbar">
                        <p class="admin-topbar__title">{{ $adminTopbarLabel }}</p>
                        <div class="admin-topbar__actions" id="admin-topbar-actions">
                            <button id="topbar-notifications-toggle" type="button" class="admin-topbar__icon" aria-label="Notifications" aria-expanded="false" aria-controls="topbar-notifications-panel">
                                <span class="admin-topbar__badge" aria-hidden="true"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75h-4.5m8.25-1.5-1.06-1.77a3 3 0 0 1-.44-1.54V10.5a4.5 4.5 0 1 0-9 0v3.44c0 .55-.15 1.1-.44 1.54L6 17.25h12Z"/></svg>
                            </button>
                            <button id="topbar-quick-toggle" type="button" class="admin-topbar__icon" aria-label="Reglages rapides" aria-expanded="false" aria-controls="topbar-quick-panel">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Zm8.25 3.75-.94-.27a7.9 7.9 0 0 0-.67-1.6l.53-.82-1.76-1.76-.82.53c-.51-.28-1.04-.5-1.6-.67l-.27-.94h-2.5l-.27.94c-.56.17-1.09.39-1.6.67l-.82-.53-1.76 1.76.53.82c-.28.51-.5 1.04-.67 1.6l-.94.27v2.5l.94.27c.17.56.39 1.09.67 1.6l-.53.82 1.76 1.76.82-.53c.51.28 1.04.5 1.6.67l.27.94h2.5l.27-.94c.56-.17 1.09-.39 1.6-.67l.82.53 1.76-1.76-.53-.82c.28-.51.5-1.04.67-1.6l.94-.27v-2.5Z"/></svg>
                            </button>
                            <button id="topbar-profile-toggle" type="button" class="admin-topbar__avatar admin-topbar__avatar--button" aria-label="Profil" aria-expanded="false" aria-controls="topbar-profile-panel">{{ $adminUserInitial }}</button>

                            <div id="topbar-notifications-panel" class="admin-topbar__panel hidden" role="dialog" aria-label="Notifications">
                                <div class="admin-topbar__panel-head">
                                    <p>Notifications</p>
                                    <button type="button" id="topbar-mark-read" class="admin-topbar__panel-action">Tout marquer lu</button>
                                </div>
                                <div class="admin-topbar__panel-list">
                                    <p class="admin-topbar__item"><span class="admin-topbar__dot"></span>3 objectifs arrivent a echeance cette semaine.</p>
                                    <p class="admin-topbar__item"><span class="admin-topbar__dot"></span>2 evaluations sont en attente de validation.</p>
                                    <p class="admin-topbar__item"><span class="admin-topbar__dot"></span>Nouveau compte agent cree aujourd'hui.</p>
                                </div>
                            </div>

                            <div id="topbar-quick-panel" class="admin-topbar__panel admin-topbar__panel--quick hidden" role="menu" aria-label="Actions rapides">
                                <p class="admin-topbar__panel-caption">Actions rapides</p>
                                <a href="{{ route('admin.evaluations.create') }}" class="admin-topbar__quick-link">Nouvelle evaluation</a>
                                <a href="{{ route('admin.objectifs.create') }}" class="admin-topbar__quick-link">Nouvel objectif</a>
                                <a href="{{ route('admin.agents.create') }}" class="admin-topbar__quick-link">Nouvel agent</a>
                                <a href="{{ route('admin.settings.edit') }}" class="admin-topbar__quick-link">Ouvrir parametres</a>
                            </div>

                            <div id="topbar-profile-panel" class="admin-topbar__panel admin-topbar__panel--profile hidden" role="menu" aria-label="Profil">
                                <p class="admin-topbar__panel-caption">Compte</p>
                                <a href="{{ route('admin.settings.edit') }}" class="admin-topbar__quick-link">Mon profil et securite</a>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="admin-topbar__quick-link admin-topbar__quick-link--danger">Se deconnecter</button>
                                </form>
                            </div>
                        </div>
                    </header>
                    @yield('content')
                </main>
            </div>
        @else
            @yield('content')
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var layout = document.getElementById('admin-layout');
                var collapseButton = document.getElementById('sidebar-toggle');
                var expandButton = document.getElementById('sidebar-expand');
                var collapsedClass = 'admin-layout--collapsed';
                var closedClass = 'admin-layout--closed';
                var storageKey = 'admin-sidebar-state';

                function syncExpandButton() {
                    if (!layout || !expandButton) {
                        return;
                    }

                    if (layout.classList.contains(collapsedClass) || layout.classList.contains(closedClass)) {
                        expandButton.classList.remove('hidden');
                    } else {
                        expandButton.classList.add('hidden');
                    }
                }

                if (layout) {
                    var savedState = window.localStorage.getItem(storageKey) || 'open';
                    if (savedState === 'collapsed') {
                        layout.classList.add(collapsedClass);
                        layout.classList.remove(closedClass);
                    } else if (savedState === 'closed') {
                        layout.classList.add(closedClass);
                        layout.classList.remove(collapsedClass);
                    } else {
                        layout.classList.remove(collapsedClass);
                        layout.classList.remove(closedClass);
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
                        layout.classList.remove(closedClass);
                        layout.classList.remove(collapsedClass);
                        window.localStorage.setItem(storageKey, 'open');

                        syncExpandButton();
                    });
                }

                var topbarContainer = document.getElementById('admin-topbar-actions');
                var notificationsToggle = document.getElementById('topbar-notifications-toggle');
                var quickToggle = document.getElementById('topbar-quick-toggle');
                var profileToggle = document.getElementById('topbar-profile-toggle');
                var notificationsPanel = document.getElementById('topbar-notifications-panel');
                var quickPanel = document.getElementById('topbar-quick-panel');
                var profilePanel = document.getElementById('topbar-profile-panel');
                var markReadButton = document.getElementById('topbar-mark-read');

                function closeAllTopbarPanels() {
                    [notificationsPanel, quickPanel, profilePanel].forEach(function (panel) {
                        if (panel) {
                            panel.classList.add('hidden');
                        }
                    });

                    [notificationsToggle, quickToggle, profileToggle].forEach(function (toggle) {
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                function toggleTopbarPanel(toggle, panel) {
                    if (!toggle || !panel) {
                        return;
                    }

                    var isClosed = panel.classList.contains('hidden');
                    closeAllTopbarPanels();

                    if (isClosed) {
                        panel.classList.remove('hidden');
                        toggle.setAttribute('aria-expanded', 'true');
                    }
                }

                if (topbarContainer) {
                    if (notificationsToggle && notificationsPanel) {
                        notificationsToggle.addEventListener('click', function () {
                            toggleTopbarPanel(notificationsToggle, notificationsPanel);
                        });
                    }

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

                    if (markReadButton && notificationsToggle) {
                        markReadButton.addEventListener('click', function () {
                            var badge = notificationsToggle.querySelector('.admin-topbar__badge');
                            if (badge) {
                                badge.style.display = 'none';
                            }
                        });
                    }

                    document.addEventListener('click', function (event) {
                        if (!topbarContainer.contains(event.target)) {
                            closeAllTopbarPanels();
                        }
                    });

                    document.addEventListener('keydown', function (event) {
                        if (event.key === 'Escape') {
                            closeAllTopbarPanels();
                        }
                    });
                }

                document.querySelectorAll('[data-auto-dismiss]').forEach(function (element) {
                    var delay = Number(element.getAttribute('data-auto-dismiss')) || 4000;

                    window.setTimeout(function () {
                        element.style.transition = 'opacity 220ms ease, transform 220ms ease';
                        element.style.opacity = '0';
                        element.style.transform = 'translateY(-6px)';

                        window.setTimeout(function () {
                            element.remove();
                        }, 240);
                    }, delay);
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
