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

    @php
        $isModalMode = request()->header('Sec-Fetch-Dest') === 'iframe' || request()->boolean('modal');
        $showSidebar = !$isModalMode && !request()->routeIs('login');

        $menuSections = [
            [
                'title' => 'Principal',
                'items' => [
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-grid-2', 'label' => 'Tableau de bord'],
                    ['route' => 'admin.entites.index', 'icon' => 'fas fa-university', 'label' => 'Faitiere'],
                    ['route' => 'admin.directions.index', 'icon' => 'fas fa-sitemap', 'label' => 'Directions'],
                    ['route' => 'admin.delegations-techniques.directeurs.index', 'icon' => 'fas fa-building-circle-arrow-right', 'label' => 'Delegations'],
                ],
            ],
            [
                'title' => 'Reseau',
                'items' => [
                    ['route' => 'admin.caisses.index', 'icon' => 'fas fa-wallet', 'label' => 'Caisses'],
                    ['route' => 'admin.agences.index', 'icon' => 'fas fa-building-columns', 'label' => 'Agences'],
                    ['route' => 'admin.guichets.index', 'icon' => 'fas fa-store', 'label' => 'Guichets'],
                    ['route' => 'admin.services.index', 'icon' => 'fas fa-layer-group', 'label' => 'Services'],
                ],
            ],
            [
                'title' => 'Ressources',
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
            --sidebar-width: 290px;
            --accent-color: #15803d;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        #admin-sidebar {
            width: var(--sidebar-width);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            left: 1rem;
            top: 1rem;
            bottom: 1rem;
            z-index: 50;
            display: flex;
            flex-direction: column;
        }

        #admin-sidebar nav {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none;
        }

        #admin-sidebar nav::-webkit-scrollbar {
            display: none;
        }

        main {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: var(--sidebar-width);
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .sidebar-closed #admin-sidebar {
            transform: translateX(calc(-100% + 58px));
        }

        .sidebar-closed main {
            margin-left: 58px;
        }

        header {
            position: relative;
            z-index: 100;
            background: transparent;
        }

        .sidebar-link-active {
            background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
            color: var(--accent-color) !important;
            box-shadow: 0 10px 24px -18px rgba(21, 128, 61, 0.55);
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
    </style>
</head>

<body class="h-full antialiased {{ $isModalMode ? 'bg-slate-50' : '' }}">
    @if($showSidebar)
        <div class="flex min-h-screen p-4">
            <aside id="admin-sidebar" class="relative flex flex-col overflow-hidden rounded-[34px] border border-emerald-900/10 bg-gradient-to-b from-[#2f944d] via-[#2d8b49] to-[#246b38] text-white shadow-2xl shadow-emerald-950/20">
                <button id="sidebar-toggle" class="absolute -right-3 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-700 text-white shadow-lg shadow-emerald-900/20 transition-all hover:bg-emerald-800">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="shrink-0 border-b border-white/10 px-6 py-8">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-white/20 bg-white/95 text-emerald-700 shadow-lg">
                            <i class="fas fa-landmark text-2xl"></i>
                        </div>
                        <h2 class="mt-4 text-2xl font-black tracking-tight">SGP-RCPB</h2>
                        <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-50/80">Gestion du reseau cooperatif</p>
                    </div>
                </div>

                <nav class="space-y-6 px-4 py-6">
                    @foreach($menuSections as $section)
                        <div>
                            <p class="mb-3 px-3 text-[10px] font-black uppercase tracking-[0.24em] text-emerald-100/60">{{ $section['title'] }}</p>
                            <div class="space-y-1.5">
                                @foreach($section['items'] as $item)
                                    @php
                                        $isActive = request()->routeIs($item['route'].'*');
                                        $link = $item['href'] ?? route($item['route']);
                                    @endphp
                                    <a href="{{ $link }}" class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition-all {{ $isActive ? 'sidebar-link-active' : 'text-emerald-50/80 hover:bg-white/10 hover:text-white' }}">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-white/10 text-emerald-50/90 group-hover:bg-white/15' }}">
                                            <i class="{{ $item['icon'] }}"></i>
                                        </span>
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        @if($item['label'] === 'Alertes')
                                            <span class="inline-flex min-w-[24px] items-center justify-center rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white">!</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <div class="mt-auto shrink-0 border-t border-white/10 p-4">
                    <div class="rounded-[24px] bg-white/10 p-4 text-white backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-[11px] font-black text-emerald-700">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black">{{ auth()->user()->name ?? 'Administrateur' }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-50/70">Session active</p>
                            </div>
                            <form action="{{ route('admin.logout') }}" method="POST" class="ml-auto">
                                @csrf
                                <button type="submit" class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/15 text-rose-100 transition hover:bg-rose-500 hover:text-white">
                                    <i class="fas fa-power-off"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex min-w-0 flex-col">
                <header class="flex h-8 shrink-0 items-center justify-between px-4 pt-0 lg:px-8">
                    <div class="flex items-center gap-4">
                        <h2 class="hidden text-lg font-extrabold text-slate-800 lg:block">@yield('page_title')</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div id="digital-clock" class="mr-4 hidden text-sm font-black text-slate-400 md:block"></div>
                        <div class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-300">
                            <i class="fas fa-bell"></i>
                            <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full border-2 border-white bg-rose-500"></span>
                        </div>
                    </div>
                </header>

                <div class="flex-1 w-full overflow-visible">
                    @yield('content')
                </div>
            </main>
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

        const toggleBtn = document.getElementById('sidebar-toggle');
        if (localStorage.getItem('sidebar-state') === 'closed') document.body.classList.add('sidebar-closed');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-closed');
                localStorage.setItem('sidebar-state', document.body.classList.contains('sidebar-closed') ? 'closed' : 'open');
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
