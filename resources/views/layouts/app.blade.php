<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')

    @php
        $isModalMode = request()->header('Sec-Fetch-Dest') === 'iframe' || request()->boolean('modal');
        $showSidebar = !$isModalMode && !request()->routeIs('login');

        $menuSections = [
            [
                'title' => '1. Principal',
                'items' => [
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-grid-2', 'label' => 'Tableau de bord'],
                    ['route' => 'admin.entites.index', 'icon' => 'fas fa-university', 'label' => 'Faitiere'],
                    ['route' => 'admin.delegations-techniques.index', 'icon' => 'fas fa-building-circle-arrow-right', 'label' => 'Delegations'],
                ],
            ],
            [
                'title' => '2. Reseau',
                'items' => [
                    ['route' => 'admin.caisses.index', 'icon' => 'fas fa-wallet', 'label' => 'Caisses'],
                    ['route' => 'admin.agences.index', 'icon' => 'fas fa-building-columns', 'label' => 'Agences'],
                    ['route' => 'admin.guichets.index', 'icon' => 'fas fa-store', 'label' => 'Guichets'],
                    ['route' => 'admin.services.index', 'icon' => 'fas fa-layer-group', 'label' => 'Services'],
                ],
            ],
            [
                'title' => '3. Ressources',
                'items' => [
                    ['route' => 'admin.agents.index', 'icon' => 'fas fa-users', 'label' => 'Agents'],
                    ['route' => 'admin.statistiques.index', 'icon' => 'fas fa-chart-column', 'label' => 'Statistiques'],
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-bell', 'label' => 'Alertes', 'href' => route('admin.dashboard').'#security-log'],
                    ['route' => 'admin.settings.edit', 'icon' => 'fas fa-cog', 'label' => 'Parametres'],
                ],
            ],
        ];
    @endphp

    <style>
        :root {
            --app-bg: #f8fafc;
            --sidebar-width: 260px;
            --accent-color: #15803d;
            --sidebar-green: #008751;
            --sidebar-green-dark: #006837;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
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

        .sidebar::-webkit-scrollbar { width: 0; }

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

        /* Collapsed sidebar */
        body.sidebar-collapsed .sidebar {
            width: 62px;
            overflow: visible;
        }
        body.sidebar-collapsed .sidebar .sidebar-header,
        body.sidebar-collapsed .sidebar .sidebar-label,
        body.sidebar-collapsed .sidebar .nav-link span,
        body.sidebar-collapsed .sidebar .nav-link .badge-alert,
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

        /* Toggle arrow */
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

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
            .sidebar-collapse-btn { display: none !important; }
        }
    </style>
</head>

<body class="h-full antialiased {{ $isModalMode ? 'bg-slate-50' : '' }}">
    @if($showSidebar)
        <nav class="sidebar shadow" id="sidebar">
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Reduire le menu">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="sidebar-header">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white text-emerald-700 shadow">
                    <i class="fas fa-landmark text-2xl"></i>
                </div>
                <h5 class="mt-3 text-xl font-black text-white">SGP-RCPB</h5>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/70">Gestion du reseau cooperatif</p>
            </div>

            <div class="flex flex-1 flex-col mt-1">
                @foreach($menuSections as $section)
                    <div class="sidebar-label">{{ $section['title'] }}</div>
                    @foreach($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route'].'*');
                            $link = $item['href'] ?? route($item['route']);
                        @endphp
                        <a href="{{ $link }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if($item['label'] === 'Alertes')
                                <span class="ml-auto inline-flex min-w-[22px] items-center justify-center rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-black text-white">!</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </div>

            <div class="mt-auto border-t border-white/10 p-3">
                <div class="sidebar-user-compact flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
                    <div class="user-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-emerald-700">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="sidebar-user-info min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Session active</p>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="sidebar-user-info">
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
                    <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm lg:hidden" id="btnToggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h2 class="hidden text-lg font-extrabold text-slate-800 lg:block">@yield('page_title')</h2>
                </div>

                <div class="flex items-center gap-3">
                    <div id="digital-clock" class="mr-4 hidden text-sm font-black text-rose-500 md:block"></div>
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-300">
                        <i class="fas fa-bell"></i>
                        <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full border-2 border-white bg-rose-500"></span>
                    </div>
                </div>
            </header>

            <div class="flex-1 w-full overflow-visible">
                @yield('content')
            </div>
        </div>
    @else
        <div class="{{ $isModalMode ? '' : 'p-6' }}">@yield('content')</div>
    @endif

    <div id="create-form-modal" class="create-form-modal items-center justify-center p-4">
        <div class="flex h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-[32px] bg-white shadow-2xl animate-in fade-in zoom-in duration-300">
            <div class="flex items-center justify-between border-b border-slate-50 bg-slate-50/50 px-8 py-6">
                <h3 id="modal-title" class="w-full text-center text-sm font-black uppercase tracking-widest text-slate-800">Nouveau Formulaire</h3>
                <button onclick="closeModal()" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition hover:bg-rose-50 hover:text-rose-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <iframe id="modal-frame" class="flex-1 w-full border-0" src=""></iframe>
        </div>
    </div>

    @livewireScripts
    <script>
        function updateClock() {
            const el = document.getElementById('digital-clock');
            if (el) el.innerText = new Date().toLocaleTimeString('fr-FR');
        }

        setInterval(updateClock, 1000);
        updateClock();

        const toggleBtn = document.getElementById('btnToggleSidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.getElementById('sidebar')?.classList.toggle('show');
            });
        }

        // Sidebar collapse toggle
        if (localStorage.getItem('sidebar-collapsed') === '1') document.body.classList.add('sidebar-collapsed');
        const collapseBtn = document.getElementById('sidebarCollapseBtn');
        if (collapseBtn) {
            collapseBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            });
        }

        function openModal(url, title) {
            const modal = document.getElementById('create-form-modal');
            const frame = document.getElementById('modal-frame');
            document.getElementById('modal-title').innerText = title || 'Nouveau';
            const targetUrl = new URL(url, window.location.origin);
            targetUrl.searchParams.set('modal', '1');
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
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
