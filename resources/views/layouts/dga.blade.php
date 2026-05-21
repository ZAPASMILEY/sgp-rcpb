@php
    $user = auth()->user();
    $menuSections = [
        [
            'title' => 'Mon espace',
            'items' => [
                ['route' => 'dga.dashboard', 'icon' => 'fas fa-house', 'label' => 'Tableau de bord'],
            ],
        ],
        [
            'title' => 'Mon dossier',
            'items' => [
                ['route' => 'dga.mon-espace', 'icon' => 'fas fa-folder-open', 'label' => 'Mon espace'],
            ],
        ],
        [
            'title' => 'Ma Direction',
            'items' => [
                ['route' => 'dga.direction', 'icon' => 'fas fa-sitemap', 'label' => 'Ma Direction'],
            ],
        ],
        [
            'title' => 'Mes subordonnés',
            'items' => [
                ['route' => 'dga.subordonnes.index',   'icon' => 'fas fa-users',         'label' => 'Directeurs Techniques'],
                ['route' => 'dga.notes-reseau.index',  'icon' => 'fas fa-chart-bar',     'label' => 'Notes du Réseau'],
                ['route' => 'dga.structures.index',    'icon' => 'fas fa-network-wired', 'label' => 'Structures'],
            ],
        ],
        [
            'title' => 'Réseau RCPB',
            'items' => [
                ['route' => 'dga.reseau.delegations', 'icon' => 'fas fa-map-marker-alt', 'label' => 'Délégations'],
                ['route' => 'dga.reseau.caisses',     'icon' => 'fas fa-landmark',        'label' => 'Caisses'],
                ['route' => 'dga.reseau.agences',     'icon' => 'fas fa-building',         'label' => 'Agences'],
                ['route' => 'dga.reseau.guichets',    'icon' => 'fas fa-cash-register',    'label' => 'Guichets'],
            ],
        ],
        [
            'title' => 'Formations',
            'items' => [
                ['route' => 'dga.formations.index', 'icon' => 'fas fa-graduation-cap', 'label' => 'Mes formations'],
            ],
        ],
    ];

    // ── Sections conditionnelles selon permissions ───────────────────────────
    $menuSections = array_merge($menuSections, \App\Helpers\PermissionMenu::extraSections());
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace DGA')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-color:      #008751;
            --sidebar-color-dark: #006837;
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
        body.sidebar-collapsed .sidebar .sidebar-user-compact { justify-content: center; }
        body.sidebar-collapsed .sidebar .sidebar-user-compact .user-avatar { margin: 0 auto; }

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <style>
    .ts-wrapper.single .ts-control{background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:.55rem 1rem;font-size:.875rem;color:#1e293b;box-shadow:none;cursor:pointer;}
    .ts-wrapper.single.focus .ts-control{border-color:#34d399;background:#fff;box-shadow:0 0 0 3px rgba(52,211,153,.15);}
    .ts-wrapper .ts-control input{color:#1e293b;font-size:.875rem;}
    .ts-dropdown{border:1px solid #e2e8f0;border-radius:.75rem;box-shadow:0 10px 30px rgba(0,0,0,.1);overflow:hidden;font-size:.875rem;}
    .ts-dropdown .option{padding:.5rem 1rem;color:#334155;}
    .ts-dropdown .option:hover,.ts-dropdown .option.active{background:#f0fdf4;color:#065f46;}
    .ts-dropdown .option.selected{background:#d1fae5;color:#065f46;font-weight:700;}
    .ts-dropdown-content{max-height:220px;}
    </style>
</head>
<body class="h-full antialiased">

    <nav class="sidebar shadow" id="sidebar">
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Réduire le menu">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="sidebar-header">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 text-violet-200 shadow text-2xl font-black">
                {{ strtoupper(substr($user?->name ?? 'D', 0, 1)) }}
            </div>
            <h5 class="mt-3 text-base font-black text-white leading-tight">{{ $user?->name }}</h5>
            <p class="mt-1 text-[11px] font-semibold uppercase tracking-widest text-white/60">Directeur Général Adjoint</p>
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
            <div class="sidebar-user-compact flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
                <div class="user-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-violet-700">
                    {{ strtoupper(substr($user?->name ?? 'D', 0, 1)) }}
                </div>
                <div class="sidebar-user-info min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'DGA' }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Session active</p>
                </div>
                <form action="{{ route('dga.logout') }}" method="POST" class="sidebar-user-info">
                    @csrf
                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/70 transition hover:bg-rose-500 hover:text-white" title="Se déconnecter">
                        <i class="fas fa-power-off text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <header class="relative z-[9999] flex h-12 shrink-0 items-center justify-between border-b border-slate-100 bg-white/80 px-4 backdrop-blur-sm">
            <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-500 shadow-sm lg:hidden" id="btnToggleSidebar" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <span class="hidden text-sm font-black text-slate-400 lg:block">Espace DGA</span>
            @include('layouts._notif_bell', ['bellId' => 'dga'])
        </header>

        <div class="flex-1 w-full overflow-visible">
            @if(session('feature_disabled'))
                <div class="mx-4 mt-4 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700 shadow-sm">
                    <i class="fas fa-ban shrink-0"></i>
                    <span>{{ session('feature_disabled') }}</span>
                </div>
            @endif
            @if(session('periode_fermee'))
                <div class="mx-4 mt-4 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 shadow-sm">
                    <i class="fas fa-calendar-times shrink-0"></i>
                    <span>{{ session('periode_fermee') }}</span>
                </div>
            @endif
            @yield('content')
        </div>
    </div>

    <script>
        document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('show');
        });

        const STORAGE_KEY = 'dga-sidebar-collapsed';
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
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
    (function(){
        var tsOpts={searchField:['text'],maxOptions:300,render:{
            no_results:function(){return'<div style="padding:.6rem 1rem;color:#94a3b8;font-size:.8rem">Aucun résultat</div>';}
        }};
        function initSelects(){
            document.querySelectorAll('select:not([data-no-ts]):not([multiple])').forEach(function(el){
                if(el.tomselect)return;
                new TomSelect(el,tsOpts);
            });
        }
        if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initSelects);}
        else{initSelects();}
    })();
    </script>
</body>
</html>
