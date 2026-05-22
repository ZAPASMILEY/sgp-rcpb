<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SGP-RCPB">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <meta name="theme-color" content="#008751">

    <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
                    ['route' => 'admin.dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Tableau de bord'],
                    ['route' => 'admin.entites.index', 'icon' => 'fas fa-university', 'label' => 'Faitiere'],
                    ['route' => 'admin.direction-generale.index', 'icon' => 'fas fa-user-tie', 'label' => 'Direction Générale'],
                    ['route' => 'admin.direction-dga.index', 'icon' => 'fas fa-sitemap', 'label' => 'Direction DGA'],
                    ['route' => 'admin.directions.index', 'icon' => 'fas fa-building-columns','label' => 'Directions'],
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
                    ['route' => 'admin.users.index', 'icon' => 'fas fa-user-shield', 'label' => 'Comptes'],
                    ['route' => 'admin.annees.index', 'icon' => 'fas fa-calendar-days', 'label' => 'Années'],
                    ['route' => 'admin.statistiques.index', 'icon' => 'fas fa-chart-column', 'label' => 'Statistiques'],
                    ['route' => 'admin.alertes.index', 'icon' => 'fas fa-bell', 'label' => 'Alertes'],
                    ['route' => 'admin.audit.index', 'icon' => 'fas fa-shield-halved', 'label' => 'Audit'],
                    ['route' => 'admin.settings.edit', 'icon' => 'fas fa-cog', 'label' => 'Parametres'],
                ],
            ],
        ];
    @endphp

    <style>
        :root {
            --app-bg: #f8fafc;
            --sidebar-width: 260px;
            /* Thème RÉFÉRENCE (moderne / SaaS) — défaut */
            --sidebar-from:  #1e293b;
            --sidebar-to:    #0f172a;
            --sidebar-active-text: #38bdf8;
            --sidebar-btn-color: #38bdf8;
        }

        /* ── Thème CLASSIQUE RCPB (vert officiel) ───────────────────────── */
        body.theme-rcpb {
            --sidebar-from:       #008751;
            --sidebar-to:         #006837;
            --sidebar-active-text:#008751;
            --sidebar-btn-color:  #008751;
            background-color: #f0f9f4;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--app-bg); color: #1e293b; overflow-x: hidden; }

        /* --- SIDEBAR --- */
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; left: 0; top: 0; background: linear-gradient(180deg, var(--sidebar-from) 0%, var(--sidebar-to) 100%); color: #fff; transition: transform 0.3s ease, width 0.3s ease; z-index: 1050; border-right: 1px solid rgba(255,255,255,0.08); overflow: hidden; display: flex; flex-direction: column; }
        .sidebar-header { padding: 1.5rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .sidebar-nav { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }
        .sidebar-label { padding: 1.2rem 1.5rem 0.4rem; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1.4px; font-weight: 800; color: rgba(255,255,255,0.5); white-space: nowrap; }
        .sidebar .nav-link { color: rgba(255,255,255,0.85) !important; padding: 0.6rem 1.2rem; display: flex; align-items: center; border-radius: 10px; margin: 0.15rem 0.8rem; transition: all 0.2s; font-size: 0.875rem; font-weight: 500; text-decoration: none; white-space: nowrap; overflow: hidden; }
        .sidebar .nav-link i { font-size: 1rem; width: 1.5rem; text-align: center; margin-right: 0.75rem; flex-shrink: 0; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.12); color: #fff !important; }
        .sidebar .nav-link.active { background: #fff !important; color: var(--sidebar-active-text) !important; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

        /* Collapse button */
        .sidebar-collapse-btn { position: absolute; right: -12px; top: 80px; width: 24px; height: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--sidebar-btn-color); font-size: 10px; z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,0.12); transition: transform 0.3s ease; }
        .sidebar-collapse-btn:hover { background: var(--sidebar-from); color: #fff; border-color: var(--sidebar-from); }

        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin 0.3s ease; display: flex; flex-direction: column; }

        /* Collapsed state */
        body.sidebar-collapsed .sidebar { width: 62px; }
        body.sidebar-collapsed .sidebar .sidebar-header, body.sidebar-collapsed .sidebar .sidebar-label, body.sidebar-collapsed .sidebar .nav-link span { display: none; }
        body.sidebar-collapsed .sidebar .nav-link { justify-content: center; margin: 0.15rem 0.5rem; padding: 0.6rem; }
        body.sidebar-collapsed .sidebar .nav-link i { margin-right: 0; }
        body.sidebar-collapsed .sidebar-collapse-btn i { transform: rotate(180deg); }
        body.sidebar-collapsed .main-content { margin-left: 62px; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }

        /* --- THEME RCPB CLASSIC --- */
        body.theme-rcpb {
            --rcpb-green: #2e7d32; --rcpb-yellow: #d4a017; background-color: #f0f7e6 !important;
        }
        /* (Tes autres styles theme-rcpb ici...) */
    </style>
</head>

@php
    $themePreference = auth()->check() ? (auth()->user()->theme_preference ?? 'reference') : 'reference';
@endphp

<body class="h-full antialiased {{ $isModalMode ? 'bg-slate-50' : '' }} {{ $themePreference === 'classic' ? 'theme-rcpb' : '' }}">

    @if(!$isModalMode) @include('layouts._alerte_banniere') @endif

    @if($showSidebar)
        <nav class="sidebar shadow" id="sidebar">
            <button class="sidebar-collapse-btn" id="sidebarCollapseBtn">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="sidebar-header">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white text-emerald-700 shadow">
                    <i class="fas fa-landmark text-2xl"></i>
                </div>
                <h5 class="mt-3 text-xl font-black text-white">SGP-RCPB</h5>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/70">Gestion du réseau</p>
            </div>

            <div class="sidebar-nav mt-1 pb-4">
                @foreach($menuSections as $section)
                    <div class="sidebar-label">{{ $section['title'] }}</div>
                    @foreach($section['items'] as $item)
                        @php $isActive = request()->routeIs($item['route'].'*'); @endphp
                        <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if($item['label'] === 'Alertes' && isset($alertesNonLuesCount) && $alertesNonLuesCount > 0)
                                <span class="ml-auto inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-[10px] text-white">!</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </div>

            {{-- Pied sidebar : utilisateur + déconnexion --}}
            <div class="flex-shrink-0 border-t border-white/10 px-3 py-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white/10 text-white">
                        <i class="fas fa-user-shield text-xs"></i>
                    </div>
                    <div class="sidebar-user-info min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-white">{{ auth()->user()?->name ?? 'Admin' }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Administrateur</p>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="sidebar-user-info">
                        @csrf
                        <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/70 transition hover:bg-rose-500 hover:text-white" title="Se déconnecter">
                            <i class="fas fa-power-off text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    @endif

    <div class="main-content">
        <header class="relative z-[9999] flex h-12 shrink-0 items-center justify-between border-b border-slate-100 bg-white/80 px-4 backdrop-blur-sm">
            <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-500 shadow-sm lg:hidden" id="btnToggleSidebar" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <span class="hidden text-sm font-black text-slate-400 lg:block">Administration</span>
            @include('layouts._notif_bell', ['bellId' => 'admin'])
        </header>
        @yield('content')
    </div>

    @livewireScripts
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

    <script>
        // ── Mobile toggle ────────────────────────────────────────────────────
        document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const toggle  = document.getElementById('btnToggleSidebar');
            if (window.innerWidth <= 992 && sidebar?.classList.contains('show') && !sidebar.contains(e.target) && !toggle?.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });

        // ── Desktop collapse ─────────────────────────────────────────────────
        (function () {
            const btn  = document.getElementById('sidebarCollapseBtn');
            if (!btn) return;
            // Restore saved state
            if (localStorage.getItem('sidebarCollapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
            btn.addEventListener('click', () => {
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed',
                    document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            });
        })();

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('PWA SGP-RCPB: Enregistré avec succès'))
                    .catch(err => console.error('PWA SGP-RCPB: Erreur', err));
            });
        }
    </script>
</body>
</html>