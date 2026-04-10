<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

    @php
        $hasViteBuild = file_exists(public_path('build/manifest.json'));
        $hasViteHot = file_exists(public_path('hot'));
        $requestHost = request()->getHost();
        $isLocalHost = in_array($requestHost, ['127.0.0.1', 'localhost'], true);
        $isModalMode = request()->header('Sec-Fetch-Dest') === 'iframe' || request()->boolean('modal');
        $showSidebar = !$isModalMode && !request()->routeIs('login');

        $menuSections = [
            [
                'title' => '1. Pilotage',
                'items' => [
                    ['route' => 'pca.dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Tableau de bord'],
                    ['route' => 'pca.objectifs.index', 'icon' => 'fas fa-bullseye', 'label' => 'Objectifs'],
                    ['route' => 'pca.statistiques.index', 'icon' => 'fas fa-chart-column', 'label' => 'Statistiques'],
                    ['route' => 'pca.evaluations.index', 'icon' => 'fas fa-clipboard-check', 'label' => 'Evaluations'],
                ],
            ],
            [
                'title' => '2. Administration',
                'items' => [
                    ['route' => 'pca.settings.edit', 'icon' => 'fas fa-cog', 'label' => 'Parametres'],
                ],
            ],
        ];

        $displayYear = (int) request()->query('annee', now()->year);
        $pcaTopbarLabel = match (true) {
            request()->routeIs('pca.dashboard') => 'Tableau de bord',
            request()->routeIs('pca.statistiques.*') => 'Pilotage / Statistiques',
            request()->routeIs('pca.objectifs.*') => 'Pilotage / Objectifs',
            request()->routeIs('pca.evaluations.*') => 'Pilotage / Evaluations',
            request()->routeIs('pca.settings.*') => 'Administration / Parametres',
            default => 'PCA',
        };
        $userInitial = auth()->check() && filled(auth()->user()->name)
            ? strtoupper(substr(auth()->user()->name, 0, 1))
            : 'P';
    @endphp

    @if ($hasViteBuild || ($hasViteHot && $isLocalHost))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/admin-fallback.css') }}">
    @endif

    <link rel="stylesheet" href="{{ asset('resources/css/all.min.css') }}">

    @livewireStyles
    @stack('head')

    <style>
        :root {
            --app-bg: #f8fafc;
            --sidebar-width: 260px;
            --sidebar-green: #008751;
            --sidebar-green-dark: #006837;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, var(--sidebar-green) 0%, var(--sidebar-green-dark) 100%);
            color: #fff;
            transition: transform 0.3s ease;
            z-index: 1050;
            border-right: 1px solid rgba(255,255,255,0.08);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar::-webkit-scrollbar {
            width: 0;
        }

        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-label {
            padding: 1.2rem 1.5rem 0.4rem;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            font-weight: 800;
            color: rgba(255,255,255,0.5);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85) !important;
            padding: 0.6rem 1.2rem;
            display: flex;
            align-items: center;
            border-radius: 10px;
            margin: 0.15rem 0.8rem;
            transition: all 0.2s;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
        }

        .sidebar .nav-link i {
            font-size: 1rem;
            width: 1.5rem;
            text-align: center;
            margin-right: 0.75rem;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.12);
            color: #fff !important;
        }

        .sidebar .nav-link.active {
            background: #fff !important;
            color: var(--sidebar-green) !important;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        body.sidebar-collapsed .sidebar {
            width: 62px;
            overflow: visible;
        }

        body.sidebar-collapsed .sidebar .sidebar-header,
        body.sidebar-collapsed .sidebar .sidebar-label,
        body.sidebar-collapsed .sidebar .nav-link span,
        body.sidebar-collapsed .sidebar .sidebar-user-info {
            display: none;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            margin: 0.15rem 0.5rem;
            padding: 0.65rem;
        }

        body.sidebar-collapsed .sidebar .nav-link i {
            margin-right: 0;
            font-size: 1.1rem;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 62px;
        }

        body.sidebar-collapsed .sidebar .sidebar-user-compact {
            justify-content: center;
        }

        body.sidebar-collapsed .sidebar .sidebar-user-compact .user-avatar {
            margin: 0 auto;
        }

        .sidebar-collapse-btn {
            position: absolute;
            right: -14px;
            top: 28px;
            z-index: 1060;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--sidebar-green);
            font-size: 0.7rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }

        .sidebar-collapse-btn:hover {
            background: var(--sidebar-green);
            color: #fff;
            border-color: var(--sidebar-green);
        }

        body.sidebar-collapsed .sidebar-collapse-btn i {
            transform: rotate(180deg);
        }

        header {
            position: relative;
            z-index: 100;
            background: transparent;
        }

        .admin-topbar__icon,
        .admin-topbar__avatar {
            display: inline-flex;
            width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #ffffff;
            color: #475569;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
        }

        .admin-topbar__avatar {
            background: linear-gradient(180deg, var(--sidebar-green) 0%, var(--sidebar-green-dark) 100%);
            color: #ffffff;
            border-color: transparent;
            font-weight: 800;
        }

        .admin-topbar__avatar--button {
            cursor: pointer;
        }

        .admin-topbar__panel {
            position: absolute;
            top: calc(100% + 0.6rem);
            right: 0;
            width: 18rem;
            border-radius: 16px;
            border: 1px solid rgba(203, 213, 225, 0.9);
            background: #ffffff;
            box-shadow: 0 18px 42px rgba(15, 23, 42, 0.18);
            padding: 0.8rem;
            z-index: 80;
        }

        .admin-topbar__panel--quick {
            width: 15rem;
            right: 2.8rem;
        }

        .admin-topbar__panel-caption {
            margin: 0 0 0.45rem;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #64748b;
        }

        .admin-topbar__quick-link {
            display: block;
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #ffffff;
            padding: 0.62rem 0.7rem;
            color: #334155;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: left;
        }

        .admin-topbar__quick-link + .admin-topbar__quick-link,
        .admin-topbar__quick-link + form,
        .admin-topbar__panel-caption + .admin-topbar__quick-link {
            margin-top: 0.45rem;
        }

        .admin-topbar__quick-link--danger {
            color: #be123c;
        }

        .hidden {
            display: none !important;
        }

        .create-form-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
        }

        .create-form-modal.is-open {
            display: flex;
        }

        body.embedded-frame #sidebar,
        body.embedded-frame header {
            display: none !important;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar-collapse-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body class="h-full antialiased pca-theme {{ $isModalMode ? 'embedded-frame bg-slate-50' : '' }}">
    @if($showSidebar)
        <nav class="sidebar shadow" id="sidebar">
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Reduire le menu">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="sidebar-header">
                <img src="{{ asset('images/rcpb-logo.jpeg') }}" alt="Logo RCPB" class="mx-auto h-16 w-16 rounded-full border-2 border-white/20 bg-white object-cover p-1 shadow">
                <h5 class="mt-3 text-xl font-black text-white">PCA</h5>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/70">Pilotage central RCPB</p>
            </div>

            <div class="mt-1 flex flex-1 flex-col">
                @foreach($menuSections as $section)
                    <div class="sidebar-label">{{ $section['title'] }}</div>
                    @foreach($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route'].'*');
                        @endphp
                        <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </div>

            <div class="mt-auto border-t border-white/10 p-3">
                <div class="sidebar-user-compact flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
                    <div class="user-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-emerald-700">
                        {{ $userInitial }}
                    </div>
                    <div class="sidebar-user-info min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ auth()->user()->name ?? 'PCA' }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Session active</p>
                    </div>
                    <form action="{{ route('pca.logout') }}" method="POST" class="sidebar-user-info">
                        @csrf
                        <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/70 transition hover:bg-rose-500 hover:text-white">
                            <i class="fas fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="main-content">
            <header class="flex h-12 shrink-0 items-center justify-between px-6 pt-2 lg:px-8">
                <div class="flex items-center gap-4">
                    <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm lg:hidden" id="btnToggleSidebar" type="button">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="hidden text-lg font-extrabold text-slate-800 lg:block">{{ $pcaTopbarLabel }}</h2>
                </div>

                <div class="flex items-center gap-3" id="admin-topbar-actions">
                    <span class="hidden rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 sm:inline-flex">Annee {{ $displayYear }}</span>
                    <div class="relative" id="pca-notif-bell-wrapper">
                        <button onclick="document.getElementById('pca-notif-dropdown').classList.toggle('hidden')" class="admin-topbar__icon relative" aria-label="Notifications" type="button">
                            <i class="fas fa-bell text-sm"></i>
                            @if(($alertesNonLuesCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[9px] font-black text-white">{{ $alertesNonLuesCount > 99 ? '99+' : $alertesNonLuesCount }}</span>
                            @endif
                        </button>
                        <div id="pca-notif-dropdown" class="absolute right-0 top-full z-50 mt-2 hidden w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-black text-slate-800">Notifications</p>
                                @if(($alertesNonLuesCount ?? 0) > 0)
                                    <form method="POST" action="{{ route('alertes.lire-tout') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-emerald-600 hover:underline">Tout marquer lu</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(($alertesNonLues ?? collect()) as $notif)
                                    <div class="flex items-start gap-3 border-b border-slate-50 px-4 py-3 transition hover:bg-slate-50">
                                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $notif->priorite === 'critique' ? 'bg-red-100 text-red-500' : ($notif->priorite === 'haute' ? 'bg-orange-100 text-orange-500' : 'bg-blue-100 text-blue-500') }}">
                                            <i class="fas {{ $notif->priorite === 'critique' || $notif->priorite === 'haute' ? 'fa-circle-exclamation' : 'fa-bell' }} text-xs"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-slate-800">{{ $notif->titre }}</p>
                                            <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ Str::limit($notif->message ?? '', 50) }}</p>
                                            <p class="mt-1 text-[10px] font-semibold text-slate-300">{{ $notif->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-slate-400">
                                        <i class="fas fa-check-circle mb-2 text-2xl text-emerald-300"></i>
                                        <p>Aucune notification</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <button id="topbar-quick-toggle" type="button" class="admin-topbar__icon" aria-label="Actions rapides" aria-expanded="false" aria-controls="topbar-quick-panel">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                    <button id="topbar-profile-toggle" type="button" class="admin-topbar__avatar admin-topbar__avatar--button" aria-label="Profil" aria-expanded="false" aria-controls="topbar-profile-panel">
                        {{ $userInitial }}
                    </button>

                    <div id="topbar-quick-panel" class="admin-topbar__panel admin-topbar__panel--quick hidden" role="menu" aria-label="Actions rapides">
                        <p class="admin-topbar__panel-caption">Actions rapides</p>
                        <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Nouvelle evaluation" class="admin-topbar__quick-link">Nouvelle evaluation</a>
                        <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Nouvel objectif" class="admin-topbar__quick-link">Nouvel objectif</a>
                        <a href="{{ route('pca.settings.edit') }}" class="admin-topbar__quick-link">Ouvrir parametres</a>
                    </div>

                    <div id="topbar-profile-panel" class="admin-topbar__panel hidden" role="menu" aria-label="Profil">
                        <p class="admin-topbar__panel-caption">Compte PCA</p>
                        <a href="{{ route('pca.settings.edit') }}" class="admin-topbar__quick-link">Mon profil et securite</a>
                        <form method="POST" action="{{ route('pca.logout') }}">
                            @csrf
                            <button type="submit" class="admin-topbar__quick-link admin-topbar__quick-link--danger">Se deconnecter</button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="flex-1 w-full overflow-visible">
                @yield('content')
            </div>
        </div>
    @else
        <div class="{{ $isModalMode ? '' : 'p-6' }}">
            @yield('content')
        </div>
    @endif

    <div id="create-form-modal" class="create-form-modal items-center justify-center p-4">
        <div class="flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-[32px] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-50 bg-slate-50/50 px-8 py-6">
                <h3 id="modal-title" class="w-full text-center text-sm font-black uppercase tracking-widest text-slate-800">Nouveau formulaire</h3>
                <button onclick="closeModal()" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition hover:bg-rose-50 hover:text-rose-500" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <iframe id="modal-frame" class="flex-1 w-full border-0" src=""></iframe>
        </div>
    </div>

    @livewireScripts

    <script>
        const toggleBtn = document.getElementById('btnToggleSidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.getElementById('sidebar')?.classList.toggle('show');
            });
        }

        if (localStorage.getItem('pca-sidebar-collapsed') === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('pca-sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            });
        }

        function openModal(url, title) {
            const modal = document.getElementById('create-form-modal');
            const frame = document.getElementById('modal-frame');
            const targetUrl = new URL(url, window.location.origin);
            targetUrl.searchParams.set('modal', '1');
            document.getElementById('modal-title').innerText = title || 'Nouveau';
            frame.src = targetUrl.toString();
            modal.classList.add('is-open');
        }

        function closeModal() {
            document.getElementById('create-form-modal').classList.remove('is-open');
            document.getElementById('modal-frame').src = '';
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-open-modal], [data-open-create-modal]');
            if (btn) {
                e.preventDefault();
                openModal(btn.getAttribute('href'), btn.getAttribute('data-title') || btn.getAttribute('data-modal-title'));
                return;
            }

            const wrapper = document.getElementById('pca-notif-bell-wrapper');
            const dropdown = document.getElementById('pca-notif-dropdown');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const topbarContainer = document.getElementById('admin-topbar-actions');
            const quickToggle = document.getElementById('topbar-quick-toggle');
            const profileToggle = document.getElementById('topbar-profile-toggle');
            const quickPanel = document.getElementById('topbar-quick-panel');
            const profilePanel = document.getElementById('topbar-profile-panel');

            function closeAllTopbarPanels() {
                [quickPanel, profilePanel].forEach(function (panel) {
                    if (panel) {
                        panel.classList.add('hidden');
                    }
                });
                [quickToggle, profileToggle].forEach(function (toggle) {
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            function toggleTopbarPanel(toggle, panel) {
                if (!toggle || !panel) {
                    return;
                }
                const isClosed = panel.classList.contains('hidden');
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
                    if (event.key === 'Escape') {
                        closeAllTopbarPanels();
                    }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
