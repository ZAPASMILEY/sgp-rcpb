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

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">

    @if ($hasViteBuild || ($hasViteHot && $isLocalHost))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('css/admin-fallback.css') }}">
    @endif

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

        .admin-topbar {
            background: #ffffff;
            border-bottom: 1px solid rgba(34, 197, 94, 0.15);
        }

        .admin-topbar__brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #1f2937;
            text-decoration: none;
        }

        .admin-topbar__mark {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #ffffff;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .admin-topbar__label {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .admin-topbar__title {
            font-size: 0.92rem;
            font-weight: 800;
            color: #1f2937;
        }

        .admin-topbar__subtitle {
            font-size: 0.68rem;
            color: #6b7280;
        }

        .admin-topbar__nav {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .admin-topbar__nav::-webkit-scrollbar {
            display: none;
        }

        .admin-topbar__link {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.58rem 0.85rem;
            border-radius: 0.7rem;
            color: #6b7280;
            text-decoration: none;
            white-space: nowrap;
            font-size: 0.82rem;
            font-weight: 600;
            transition: 0.15s ease;
        }

        .admin-topbar__link:hover {
            color: #16a34a;
            background: rgba(34, 197, 94, 0.10);
        }

        .admin-topbar__link.active {
            background: #22c55e;
            color: #ffffff;
        }

        .admin-topbar__meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-topbar__datetime {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 2.2rem;
            padding: 0.35rem 0.8rem;
            border-radius: 0.9rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
            background: rgba(34, 197, 94, 0.08);
            color: #166534;
            font-size: 0.76rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-topbar__datetime i {
            font-size: 0.86rem;
            color: #16a34a;
        }

        .admin-topbar__datetime-text {
            display: inline-flex;
            align-items: baseline;
            gap: 0.45rem;
            flex-wrap: wrap;
        }

        .admin-topbar__year,
        .admin-topbar__clock {
            line-height: 1;
        }

        .admin-topbar__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0 0.7rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.10);
            color: #16a34a;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .admin-topbar__icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.8rem;
            background: rgba(34, 197, 94, 0.10);
            color: #16a34a;
            text-decoration: none;
        }

        .admin-topbar__dot {
            position: absolute;
            top: 0.15rem;
            right: 0.15rem;
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 999px;
            background: #ef4444;
            border: 2px solid #ffffff;
        }

        .admin-topbar__user {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #2563eb;
            color: #ffffff;
            font-weight: 800;
        }

        .admin-topbar__logout {
            border: 0;
            border-radius: 0.8rem;
            background: #dc2626;
            color: #ffffff;
            padding: 0.6rem 0.95rem;
            font-size: 0.8rem;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .admin-topbar__meta {
                width: 100%;
                justify-content: flex-end;
            }

            .admin-topbar__datetime {
                padding-inline: 0.6rem;
                font-size: 0.72rem;
            }
        }

        .app-header,
        .app-main {
            margin-left: 0 !important;
        }

        body.admin-theme {
            background:
                radial-gradient(circle at 18% 12%, rgba(34, 197, 94, 0.08), transparent 24%),
                radial-gradient(circle at 82% 20%, rgba(34, 197, 94, 0.06), transparent 18%),
                linear-gradient(180deg, #ffffff 0%, #f0f9f4 48%, #f8fcfa 100%);
            color: #374151;
        }

        body.admin-theme .app-main,
        body.admin-theme .app-content,
        body.admin-theme .app-content-header,
        body.admin-theme .content-wrapper {
            background: transparent !important;
        }

        body.admin-theme .app-content-header {
            border-bottom: 1px solid rgba(34, 197, 94, 0.15);
            box-shadow: none !important;
        }

        body.admin-theme .app-content-header h3 {
            color: #1f2937;
        }

        body.admin-theme .admin-shell {
            position: relative;
        }

        body.admin-theme .admin-shell::before {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(34, 197, 94, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(34, 197, 94, 0.04) 1px, transparent 1px);
            background-size: 34px 34px;
            content: '';
            pointer-events: none;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.6), transparent 90%);
            opacity: 0.28;
        }

        body.admin-theme .admin-panel,
        body.admin-theme .metric-card,
        body.admin-theme .ent-card,
        body.admin-theme .ent-kpi,
        body.admin-theme .ent-filters,
        body.admin-theme .ent-table-wrap {
            background: linear-gradient(180deg, rgba(240, 253, 250, 0.90), rgba(229, 250, 245, 0.88)) !important;
            border: 1px solid rgba(34, 197, 94, 0.20) !important;
            color: #374151 !important;
            box-shadow:
                inset 0 1px 0 rgba(34, 197, 94, 0.03),
                0 24px 40px rgba(34, 197, 94, 0.08) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        body.admin-theme [class$='-hero'],
        body.admin-theme [class*='-hero '],
        body.admin-theme [class$='-list-card'],
        body.admin-theme [class*='-list-card '] {
            background: linear-gradient(180deg, rgba(240, 253, 250, 0.88), rgba(229, 250, 245, 0.86)) !important;
            border: 1px solid rgba(34, 197, 94, 0.20) !important;
            box-shadow:
                inset 0 1px 0 rgba(34, 197, 94, 0.03),
                0 24px 40px rgba(34, 197, 94, 0.08) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }

        body.admin-theme .ent-window__bar {
            border-bottom: 1px solid rgba(34, 197, 94, 0.15) !important;
            background: linear-gradient(180deg, rgba(34, 197, 94, 0.04), rgba(34, 197, 94, 0.02)) !important;
        }

        body.admin-theme .ent-window__label,
        body.admin-theme .ent-kpi-label,
        body.admin-theme .ft-muted,
        body.admin-theme .status-pill,
        body.admin-theme .text-slate-500,
        body.admin-theme .text-slate-600,
        body.admin-theme .text-slate-700 {
            color: #6b7280 !important;
        }

        body.admin-theme .text-slate-950,
        body.admin-theme .text-slate-900,
        body.admin-theme .ent-identity,
        body.admin-theme h1,
        body.admin-theme h2,
        body.admin-theme h3,
        body.admin-theme h4 {
            color: #1f2937 !important;
        }

        body.admin-theme .ent-subtext,
        body.admin-theme .text-slate-400,
        body.admin-theme .text-slate-300 {
            color: #4b5563 !important;
        }

        body.admin-theme .ent-input,
        body.admin-theme .ent-select,
        body.admin-theme .ent-textarea,
        body.admin-theme input,
        body.admin-theme select,
        body.admin-theme textarea {
            background: rgba(255, 255, 255, 0.95) !important;
            border-color: rgba(34, 197, 94, 0.20) !important;
            color: #374151 !important;
            box-shadow: none;
        }

        body.admin-theme .ent-input::placeholder,
        body.admin-theme .ent-textarea::placeholder,
        body.admin-theme input::placeholder,
        body.admin-theme textarea::placeholder {
            color: #9ca3af !important;
        }

        body.admin-theme .ent-input:focus,
        body.admin-theme .ent-select:focus,
        body.admin-theme .ent-textarea:focus,
        body.admin-theme input:focus,
        body.admin-theme select:focus,
        body.admin-theme textarea:focus {
            border-color: rgba(34, 197, 94, 0.6) !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.10) !important;
        }

        body.admin-theme .ent-btn {
            border-radius: 0.8rem;
            letter-spacing: 0.08em;
        }

        body.admin-theme .ent-btn-primary,
        body.admin-theme .bg-slate-950 {
            background: linear-gradient(180deg, #22c55e, #16a34a) !important;
            color: #ffffff !important;
            border: 1px solid rgba(34, 197, 94, 0.34) !important;
            box-shadow: 0 10px 24px rgba(34, 197, 94, 0.22);
        }

        body.admin-theme .ent-btn-primary:hover,
        body.admin-theme .hover\:bg-slate-800:hover {
            background: #16a34a !important;
        }

        body.admin-theme .ent-btn-soft {
            background: rgba(34, 197, 94, 0.08) !important;
            color: #16a34a !important;
            border: 1px solid rgba(34, 197, 94, 0.20) !important;
        }

        body.admin-theme .ent-btn-soft:hover {
            background: rgba(34, 197, 94, 0.15) !important;
            color: #15803d !important;
            border-color: rgba(34, 197, 94, 0.30) !important;
        }

        body.admin-theme .ent-btn-danger {
            background: rgba(239, 68, 68, 0.10) !important;
            color: #dc2626 !important;
            border: 1px solid rgba(239, 68, 68, 0.20) !important;
        }

        body.admin-theme .ent-table-wrap {
            overflow: hidden;
        }

        body.admin-theme .ent-table thead th {
            background: rgba(34, 197, 94, 0.08) !important;
            color: #4b5563 !important;
            border-bottom: 1px solid rgba(34, 197, 94, 0.12) !important;
        }

        body.admin-theme .ent-table tbody td {
            background: rgba(34, 197, 94, 0.02) !important;
            color: #374151 !important;
            border-bottom: 1px solid rgba(34, 197, 94, 0.08) !important;
        }

        body.admin-theme .ent-table tbody tr:hover td {
            background: rgba(34, 197, 94, 0.06) !important;
        }

        body.admin-theme .border-slate-100,
        body.admin-theme .border-slate-200,
        body.admin-theme .border-slate-300 {
            border-color: rgba(34, 197, 94, 0.16) !important;
        }

        body.admin-theme .bg-white,
        body.admin-theme .bg-slate-50,
        body.admin-theme .bg-slate-100 {
            background: rgba(240, 253, 250, 0.95) !important;
            color: #374151 !important;
        }

        body.admin-theme .bg-red-50,
        body.admin-theme .bg-emerald-50,
        body.admin-theme .bg-amber-50 {
            background: rgba(240, 253, 250, 0.95) !important;
        }

        body.admin-theme .text-red-700,
        body.admin-theme .text-emerald-700,
        body.admin-theme .text-amber-700 {
            color: #16a34a !important;
        }

        body.admin-theme .border-red-200,
        body.admin-theme .border-emerald-200,
        body.admin-theme .border-amber-200 {
            border-color: rgba(34, 197, 94, 0.20) !important;
        }

        body.admin-theme .create-form-modal__panel {
            background: linear-gradient(180deg, rgba(240, 253, 250, 0.98), rgba(229, 250, 245, 0.96));
            border-color: rgba(34, 197, 94, 0.20);
        }

        body.admin-theme .create-form-modal__header {
            background: rgba(34, 197, 94, 0.04);
            border-bottom-color: rgba(34, 197, 94, 0.12);
        }

        body.admin-theme .create-form-modal__title {
            color: #4b5563;
        }

        body.admin-theme .create-form-modal__close {
            background: rgba(34, 197, 94, 0.10);
            color: #6b7280;
        }

        body.admin-theme .create-form-modal__frame {
            background: #ffffff;
        }
    </style>
    
    @stack('head')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary {{ request()->routeIs('admin.*') && !request()->routeIs('login') ? 'admin-theme' : '' }}">
    @php
        $showAdminSidebar = request()->routeIs('admin.*') && !request()->routeIs('login');
        $adminUserInitial = auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A';
        $displayYear = (int) request()->query('annee', now()->year);
        $adminTopMenu = [
            ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Tableau de bord', 'icon' => 'fas fa-chart-pie'],
            ['route' => 'admin.entites.index', 'pattern' => 'admin.entites.*', 'label' => 'Faitiere', 'icon' => 'fas fa-university'],
            ['route' => 'admin.directions.index', 'pattern' => 'admin.directions.*', 'label' => 'Delegation Technique', 'icon' => 'fas fa-sitemap'],
            ['route' => 'admin.caisses.index', 'pattern' => 'admin.caisses.*', 'label' => 'Caisses', 'icon' => 'fas fa-cash-register'],
            ['route' => 'admin.agences.index', 'pattern' => 'admin.agences.*', 'label' => 'Agences', 'icon' => 'fas fa-building'],
            ['route' => 'admin.guichets.index', 'pattern' => 'admin.guichets.*', 'label' => 'Guichets', 'icon' => 'fas fa-store'],
            ['route' => 'admin.statistiques.index', 'pattern' => 'admin.statistiques.*', 'label' => 'Statistiques', 'icon' => 'fas fa-chart-line'],
            ['route' => 'admin.evaluations.index', 'pattern' => 'admin.evaluations.*', 'label' => 'Evaluations', 'icon' => 'fas fa-clipboard-check'],
            ['route' => 'admin.settings.edit', 'pattern' => 'admin.settings.*', 'label' => 'Parametres', 'icon' => 'fas fa-cog'],
        ];
    @endphp

    <div class="app-wrapper">
        @if ($showAdminSidebar)
            <nav class="app-header navbar navbar-expand admin-topbar shadow-sm">
                <div class="container-fluid d-flex flex-column flex-xl-row gap-3 py-3 py-xl-2">
                    <div class="d-flex align-items-center gap-3 w-100 w-xl-auto">
                        <a href="{{ route('admin.dashboard') }}" class="admin-topbar__brand">
                            <span class="admin-topbar__mark">S</span>
                            <span class="admin-topbar__label">
                                <span class="admin-topbar__title">SGP RCPB</span>
                                <span class="admin-topbar__subtitle">Système de gestion centralisé</span>
                            </span>
                        </a>
                    </div>

                    <div class="admin-topbar__nav flex-grow-1">
                        @foreach ($adminTopMenu as $item)
                            <a href="{{ route($item['route']) }}" class="admin-topbar__link {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                                <i class="{{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="admin-topbar__meta ms-xl-auto">
                        <span class="admin-topbar__datetime" aria-live="polite">
                            <i class="far fa-clock" aria-hidden="true"></i>
                            <span class="admin-topbar__datetime-text">
                                <span class="admin-topbar__year">Année {{ $displayYear }}</span>
                                <span class="admin-topbar__clock" id="admin-current-time">--:--:--</span>
                            </span>
                        </span>
                        <a href="{{ route('admin.settings.edit') }}" class="admin-topbar__icon" aria-label="Paramètres">
                            <i class="fas fa-bell"></i>
                            <span class="admin-topbar__dot"></span>
                        </a>
                        <div class="dropdown user-menu">
                            <a href="#" class="admin-topbar__user" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $adminUserInitial }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li class="user-header bg-primary text-white p-3 text-center">
                                    <p class="mb-0">{{ auth()->user()->name ?? 'Administrateur' }}</p>
                                </li>
                                <li class="user-footer border-top p-2 d-flex justify-content-between align-items-center gap-2">
                                    <a href="{{ route('admin.settings.edit') }}" class="btn btn-default btn-flat">Profil</a>
                                    <form action="{{ route('admin.logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="admin-topbar__logout">Déconnexion</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="app-main">
                <div class="app-content-header shadow-sm bg-white mb-4 py-3">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-6">
                                <h3 class="mb-0">@yield('title')</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="app-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
            </main>
        @else
            @yield('content')
        @endif
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

    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var currentTime = document.getElementById('admin-current-time');
            var modal = document.getElementById('create-form-modal');
            var frame = document.getElementById('create-form-modal-frame');
            var closeButton = document.getElementById('create-form-modal-close');
            var title = document.getElementById('create-form-modal-title');

            if (currentTime) {
                var formatter = new Intl.DateTimeFormat('fr-FR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                });

                function updateClock() {
                    currentTime.textContent = formatter.format(new Date());
                }

                updateClock();
                window.setInterval(updateClock, 1000);
            }

            if (!modal || !frame || !closeButton) {
                return;
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                frame.removeAttribute('src');
            }

            function openModal(url, label) {
                frame.src = url;
                title.textContent = label || "Formulaire d'ajout";
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }

            document.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-open-create-modal]');

                if (trigger) {
                    event.preventDefault();
                    openModal(trigger.getAttribute('href'), trigger.getAttribute('data-modal-title'));
                    return;
                }

                if (event.target === modal || event.target === closeButton) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    closeModal();
                }
            });

            frame.addEventListener('load', function () {
                if (!modal.classList.contains('is-open')) {
                    return;
                }

                try {
                    var currentPath = frame.contentWindow.location.pathname || '';
                    var isCreatePage = currentPath.indexOf('/creer') !== -1 || currentPath.indexOf('/create') !== -1;

                    if (!isCreatePage) {
                        closeModal();
                        window.location.reload();
                    }
                } catch (error) {
                    // Keep modal open if the frame location cannot be inspected.
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>