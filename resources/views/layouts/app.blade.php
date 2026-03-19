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
    </style>
    
    @stack('head')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    @php
        $showAdminSidebar = request()->routeIs('admin.*') && !request()->routeIs('login');
        $adminUserInitial = auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A';
    @endphp

    <div class="app-wrapper">
        @if ($showAdminSidebar)
            <nav class="app-header navbar navbar-expand bg-body shadow-sm">
                <div class="container-fluid">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                                <i class="fas fa-bars"></i>
                            </a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link" data-bs-toggle="dropdown" href="#">
                                <i class="far fa-bell"></i>
                                <span class="badge badge-warning navbar-badge">3</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                                <span class="dropdown-item dropdown-header">Statut SGP</span>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-file me-2"></i> 3 objectifs en cours
                                </a>
                            </div>
                        </li>
                        
                        <li class="nav-item dropdown user-menu">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="rounded-circle bg-primary text-white d-inline-block text-center" style="width: 30px; height: 30px; line-height: 30px;">
                                    {{ $adminUserInitial }}
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li class="user-header bg-primary text-white p-3 text-center">
                                    <p>{{ auth()->user()->name ?? 'Administrateur' }}</p>
                                </li>
                                <li class="user-footer border-top p-2">
                                    <a href="{{ route('admin.settings.edit') }}" class="btn btn-default btn-flat float-start">Profil</a>
                                    <a href="{{ route('admin.logout') }}" 
                                       class="btn btn-default btn-flat float-end text-danger"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
                <div class="sidebar-brand">
                    <a href="{{ route('admin.dashboard') }}" class="brand-link text-center w-100">
                        <span class="brand-text fw-bold">SGP RCPB</span>
                    </a>
                </div>
                
                <div class="sidebar-wrapper">
                    <nav class="mt-3">
                        <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-pie"></i>
                                    <p>Tableau de bord</p>
                                </a>
                            </li>

                            <li class="nav-header">RÉFÉRENTIEL</li>
                            <li class="nav-item">
                                <a href="{{ route('admin.entites.index') }}" class="nav-link {{ request()->routeIs('admin.entites.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-university"></i>
                                    <p>Faitiere</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.directions.index') }}" class="nav-link {{ request()->routeIs('admin.directions.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sitemap"></i>
                                    <p>Delegation Technique</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.caisses.index') }}" class="nav-link {{ request()->routeIs('admin.caisses.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cash-register"></i>
                                    <p>Agents par caisse</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.agences.index') }}" class="nav-link {{ request()->routeIs('admin.agences.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <p>Agences</p>
                                </a>
                            </li>

                            <li class="nav-header">PILOTAGE</li>
                            <li class="nav-item">
                                <a href="{{ route('admin.statistiques.index') }}" class="nav-link {{ request()->routeIs('admin.statistiques.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <p>Statistiques</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.evaluations.index') }}" class="nav-link {{ request()->routeIs('admin.evaluations.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clipboard-check"></i>
                                    <p>Évaluations</p>
                                </a>
                            </li>

                            <li class="nav-header">ADMINISTRATION</li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.edit') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cog"></i>
                                    <p>Paramètres</p>
                                </a>
                            </li>

                            <li class="nav-item mt-4">
                                <form id="logout-form-sidebar" action="{{ route('admin.logout') }}" method="POST" class="px-3">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100 shadow-sm">
                                        <i class="fas fa-power-off"></i> Quitter
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </nav>
                </div>
            </aside>

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
            var modal = document.getElementById('create-form-modal');
            var frame = document.getElementById('create-form-modal-frame');
            var closeButton = document.getElementById('create-form-modal-close');
            var title = document.getElementById('create-form-modal-title');

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