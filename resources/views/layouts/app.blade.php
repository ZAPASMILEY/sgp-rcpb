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
        @endphp

        @if ($showAdminSidebar)
            <div id="admin-layout" class="admin-layout min-h-screen {{ $adminThemeClass }}">
                <aside id="admin-sidebar" class="admin-sidebar">
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
                            Dashboard
                        </a>
                        <a href="{{ route('admin.entites.index') }}" class="admin-tab {{ request()->routeIs('admin.entites.*') ? 'is-active' : '' }}">
                            Entites
                        </a>
                        <a href="{{ route('admin.directions.index') }}" class="admin-tab {{ request()->routeIs('admin.directions.*') ? 'is-active' : '' }}">
                            Directions
                        </a>
                        <a href="{{ route('admin.services.index') }}" class="admin-tab {{ request()->routeIs('admin.services.*') ? 'is-active' : '' }}">
                            Services
                        </a>
                        <a href="{{ route('admin.agents.index') }}" class="admin-tab {{ request()->routeIs('admin.agents.*') ? 'is-active' : '' }}">
                            Agents
                        </a>
                        <a href="{{ route('admin.objectifs.index') }}" class="admin-tab {{ request()->routeIs('admin.objectifs.*') ? 'is-active' : '' }}">
                            Objectifs
                        </a>
                        <a href="{{ route('admin.evaluations.index') }}" class="admin-tab {{ request()->routeIs('admin.evaluations.*') ? 'is-active' : '' }}">
                            Evaluations
                        </a>
                        <a href="{{ route('admin.settings.edit') }}" class="admin-tab {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                            Parametres
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
                var storageKey = 'admin-sidebar-collapsed';

                function syncExpandButton() {
                    if (!layout || !expandButton) {
                        return;
                    }

                    if (layout.classList.contains(collapsedClass)) {
                        expandButton.classList.remove('hidden');
                    } else {
                        expandButton.classList.add('hidden');
                    }
                }

                if (layout) {
                    var savedState = window.localStorage.getItem(storageKey);
                    if (savedState === '1') {
                        layout.classList.add(collapsedClass);
                    }
                    syncExpandButton();
                }

                if (collapseButton && layout) {
                    collapseButton.addEventListener('click', function () {
                        layout.classList.add(collapsedClass);
                        window.localStorage.setItem(storageKey, '1');
                        syncExpandButton();
                    });
                }

                if (expandButton && layout) {
                    expandButton.addEventListener('click', function () {
                        layout.classList.remove(collapsedClass);
                        window.localStorage.setItem(storageKey, '0');
                        syncExpandButton();
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
