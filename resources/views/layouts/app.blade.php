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
        
        $menuItems = [
            ['route' => 'admin.dashboard', 'icon' => 'fas fa-grid-2', 'label' => 'Dashboard'],
            ['route' => 'admin.entites.index', 'icon' => 'fas fa-university', 'label' => 'Faîtière'],
            ['route' => 'admin.directions.index', 'icon' => 'fas fa-sitemap', 'label' => 'Délégations'],
            ['route' => 'admin.caisses.index', 'icon' => 'fas fa-wallet', 'label' => 'Caisses'],
            ['route' => 'admin.agents.index', 'icon' => 'fas fa-users', 'label' => 'Agents'],
            ['route' => 'admin.settings.edit', 'icon' => 'fas fa-cog', 'label' => 'Paramètres'],
        ];
    @endphp

    <style>
        :root {
            --app-bg: #f8fafc;
            --sidebar-width: 280px;
            --accent-color: #06b6d4;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--app-bg);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* --- SIDEBAR CONFIG --- */
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

        /* Scroll interne du menu */
        #admin-sidebar nav {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: none;
        }
        #admin-sidebar nav::-webkit-scrollbar { display: none; }

        /* --- MAIN CONTENT & TOGGLE --- */
        main {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-left: var(--sidebar-width);
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .sidebar-closed #admin-sidebar { transform: translateX(-120%); }
        .sidebar-closed main { margin-left: 0; }

        /* HEADER : Z-index élevé pour rester au-dessus du contenu qui remonte */
        header {
            position: relative;
            z-index: 100; 
            background: transparent;
        }

        .sidebar-link-active {
            background: #f1fbfd;
            color: var(--accent-color) !important;
            box-shadow: inset 4px 0 0 var(--accent-color);
        }

        .create-form-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
        }
        .create-form-modal.is-open { display: flex; }
    </style>
</head>

<body class="h-full antialiased {{ $isModalMode ? 'bg-slate-50' : '' }}">
    
    @if($showSidebar)
        <div class="flex min-h-screen p-4">
            
            <aside id="admin-sidebar" class="bg-white rounded-[32px] shadow-xl border border-slate-50 flex flex-col">
                <div class="p-8 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-cyan-100">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2 class="font-black text-xl text-slate-800 tracking-tighter">SGP-RCPB</h2>
                    </div>
                </div>

                <nav class="px-4 space-y-2">
                    <p class="px-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-300 mb-4">Navigation</p>
                    @foreach($menuItems as $item)
                        <a href="{{ route($item['route']) }}" 
                           class="flex items-center gap-4 px-4 py-3.5 rounded-2xl text-sm font-bold transition-all group {{ request()->routeIs($item['route'].'*') ? 'sidebar-link-active' : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600' }}">
                            <i class="{{ $item['icon'] }} w-5 text-center"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="p-4 mt-auto shrink-0">
                    <div class="bg-slate-900 rounded-[24px] p-4 text-white">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-cyan-500 flex items-center justify-center font-black text-[10px]">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <form action="{{ route('admin.logout') }}" method="POST" class="ml-auto">
                                @csrf
                                <button type="submit" class="text-rose-400 hover:text-rose-300"><i class="fas fa-power-off"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex flex-col min-w-0">
                <header class="flex items-center justify-between px-4 lg:px-8 h-20 shrink-0">
                    <div class="flex items-center gap-6">
                        <button id="sidebar-toggle" class="h-12 w-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-50 transition-all">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="text-xl font-extrabold text-slate-800 hidden lg:block">@yield('page_title')</h2>
                    </div>

                    <div class="flex items-center gap-3">
                        <div id="digital-clock" class="hidden md:block text-sm font-black text-slate-400 mr-4"></div>
                        <div class="h-12 w-12 bg-white rounded-2xl border border-slate-100 flex items-center justify-center text-slate-300 relative">
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-3 right-3 h-2 w-2 bg-rose-500 rounded-full border-2 border-white"></span>
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
        <div class="bg-white w-full max-w-4xl h-[90vh] rounded-[32px] overflow-hidden shadow-2xl flex flex-col animate-in fade-in zoom-in duration-300">
            <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                <h3 id="modal-title" class="font-black text-slate-800 uppercase tracking-widest text-sm text-center w-full">Nouveau Formulaire</h3>
                <button onclick="closeModal()" class="h-10 w-10 rounded-full bg-white shadow-sm flex items-center justify-center hover:bg-rose-50 hover:text-rose-500 transition">
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
            if(el) el.innerText = new Date().toLocaleTimeString('fr-FR');
        }
        setInterval(updateClock, 1000); updateClock();

        const toggleBtn = document.getElementById('sidebar-toggle');
        if(localStorage.getItem('sidebar-state') === 'closed') document.body.classList.add('sidebar-closed');

        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-closed');
                localStorage.setItem('sidebar-state', document.body.classList.contains('sidebar-closed') ? 'closed' : 'open');
            });
        }

        function openModal(url, title) {
            const modal = document.getElementById('create-form-modal');
            const frame = document.getElementById('modal-frame');
            document.getElementById('modal-title').innerText = title || "Nouveau";
            const targetUrl = new URL(url, window.location.origin);
            targetUrl.searchParams.set('modal', '1');
            frame.src = targetUrl.toString();
            modal.classList.add('is-open');
        }

        function closeModal() {
            document.getElementById('create-form-modal').classList.remove('is-open');
            document.getElementById('modal-frame').src = "";
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-open-modal], [data-open-create-modal]');
            if(btn) { e.preventDefault(); openModal(btn.getAttribute('href'), btn.getAttribute('data-title') || btn.getAttribute('data-modal-title')); }
        });
    </script>
</body>
</html>