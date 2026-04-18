@php
    $user = auth()->user();
    $menuSections = [
        [
            'title' => 'Mon espace',
            'items' => [
                ['route' => 'directeur.mon-espace', 'icon' => 'fas fa-house', 'label' => 'Tableau de bord'],
            ],
        ],
        [
            'title' => 'Mon dossier',
            'items' => [
                ['route' => 'directeur.mon-espace', 'query' => 'tab=evaluations', 'icon' => 'fas fa-star',    'label' => 'Mes évaluations'],
                ['route' => 'directeur.mon-espace', 'query' => 'tab=objectifs',   'icon' => 'fas fa-bullseye', 'label' => 'Mes objectifs'],
            ],
        ],
        [
            'title' => 'Ma direction',
            'items' => [
                ['route' => 'directeur.mon-espace', 'query' => 'tab=dashboard', 'icon' => 'fas fa-users',      'label' => 'Mes chefs de service'],
                ['route' => 'directeur.evaluations.create',                     'icon' => 'fas fa-pen-to-square','label' => 'Nouvelle évaluation'],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace Directeur')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-color:      #2563eb;
            --sidebar-color-dark: #1d4ed8;
        }

        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; overflow-x: hidden; }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            background: linear-gradient(180deg, var(--sidebar-color) 0%, var(--sidebar-color-dark) 100%);
            color: #fff;
            transition: transform 0.3s ease, width 0.3s ease;
            z-index: 1050;
            border-right: 1px solid rgba(255,255,255,0.08);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .sidebar::-webkit-scrollbar { width: 0; }

        .sidebar-header { padding: 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-label  { padding: 1.2rem 1.5rem 0.4rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.4px; font-weight: 800; color: rgba(255,255,255,0.5); }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85) !important;
            padding: 0.6rem 1.2rem;
            display: flex; align-items: center;
            border-radius: 10px;
            margin: 0.15rem 0.8rem;
            transition: all 0.2s;
            font-size: 0.875rem; font-weight: 500;
            text-decoration: none;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 1.5rem; text-align: center; margin-right: 0.75rem; }
        .sidebar .nav-link:hover  { background: rgba(255,255,255,0.12); color: #fff !important; }
        .sidebar .nav-link.active { background: #fff !important; color: var(--sidebar-color) !important; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; width: calc(100% - var(--sidebar-width)); transition: margin 0.3s ease, width 0.3s ease; display: flex; flex-direction: column; }

        body.sidebar-collapsed .sidebar { width: 62px; overflow: visible; }
        body.sidebar-collapsed .sidebar .sidebar-header,
        body.sidebar-collapsed .sidebar .sidebar-label,
        body.sidebar-collapsed .sidebar .nav-link span,
        body.sidebar-collapsed .sidebar .sidebar-user-info { display: none; }
        body.sidebar-collapsed .sidebar .nav-link { justify-content: center; margin: 0.15rem 0.5rem; padding: 0.65rem; }
        body.sidebar-collapsed .sidebar .nav-link i { margin-right: 0; font-size: 1.1rem; }
        body.sidebar-collapsed .main-content { margin-left: 62px; width: calc(100% - 62px); }

        .sidebar-collapse-btn {
            position: absolute; right: -14px; top: 28px; z-index: 1060;
            width: 28px; height: 28px; border-radius: 50%;
            background: #fff; border: 2px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--sidebar-color); font-size: 0.7rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: all 0.2s;
        }
        .sidebar-collapse-btn:hover { background: var(--sidebar-color); color: #fff; border-color: var(--sidebar-color); }
        body.sidebar-collapsed .sidebar-collapse-btn i { transform: rotate(180deg); }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .sidebar-collapse-btn { display: none !important; }
        }
    </style>
</head>
<body class="h-full antialiased">

    <nav class="sidebar shadow" id="sidebar">
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Réduire le menu">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="sidebar-header">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 text-blue-200 shadow text-2xl font-black">
                {{ strtoupper(substr($user?->name ?? 'D', 0, 1)) }}
            </div>
            <h5 class="mt-3 text-base font-black text-white leading-tight">{{ $user?->name }}</h5>
            <p class="mt-1 text-[11px] font-semibold uppercase tracking-widest text-white/60">Directeur de Direction</p>
        </div>

        <div class="flex flex-1 flex-col mt-1">
            @foreach($menuSections as $section)
                <div class="sidebar-label">{{ $section['title'] }}</div>
                @foreach($section['items'] as $item)
                    @php
                        $isActive = request()->routeIs($item['route'].'*');
                        $query    = $item['query'] ?? null;
                        $link     = route($item['route']) . ($query ? '?'.$query : '');
                    @endphp
                    <a href="{{ $link }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                        <i class="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </div>

        <div class="mt-auto border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-blue-700">
                    {{ strtoupper(substr($user?->name ?? 'D', 0, 1)) }}
                </div>
                <div class="sidebar-user-info min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'Directeur' }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Session active</p>
                </div>
                <form action="{{ route('directeur.logout') }}" method="POST" class="sidebar-user-info">
                    @csrf
                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/70 transition hover:bg-rose-500 hover:text-white" title="Se déconnecter">
                        <i class="fas fa-power-off text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header class="flex h-12 shrink-0 items-center justify-between border-b border-slate-100 bg-white/80 px-4 backdrop-blur-sm">
            <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-500 shadow-sm lg:hidden" id="btnToggleSidebar" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <span class="hidden text-sm font-black text-slate-400 lg:block">Espace Directeur</span>
            @include('layouts._notif_bell', ['bellId' => 'directeur'])
        </header>

        <div class="flex-1 w-full overflow-visible">
            @yield('content')
        </div>
    </div>

    <script>
        document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('show');
        });

        const STORAGE_KEY = 'directeur-sidebar-collapsed';
        if (localStorage.getItem(STORAGE_KEY) === '1') document.body.classList.add('sidebar-collapsed');
        document.getElementById('sidebarCollapseBtn')?.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(STORAGE_KEY, document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
        });

        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const toggle  = document.getElementById('btnToggleSidebar');
            if (window.innerWidth <= 992 && sidebar?.classList.contains('show') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
