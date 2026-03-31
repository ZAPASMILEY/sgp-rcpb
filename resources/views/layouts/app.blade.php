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
        @livewireStyles

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

        [x-cloak] {
            display: none !important;
        }

        .admin-layout {
            min-height: 100vh;
        }

        .admin-sidebar {
            background:
                radial-gradient(circle at top, rgba(52, 211, 153, 0.2), transparent 28%),
                linear-gradient(180deg, #052e2b 0%, #064e3b 52%, #022c22 100%);
        }

        .admin-sidebar__brand {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.04));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .admin-sidebar__brand::after {
            content: '';
            position: absolute;
            inset: auto -20% -55% auto;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 68%);
            pointer-events: none;
        }

        .admin-sidebar__link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            border-radius: 1rem;
            padding: 0.85rem 0.95rem;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1;
            text-decoration: none;
            transition: transform 0.18s ease, background 0.18s ease, color 0.18s ease;
        }

        .admin-sidebar__link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateX(2px);
        }

        .admin-sidebar__link.is-active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95));
            color: #ffffff;
            box-shadow: 0 16px 30px rgba(6, 78, 59, 0.35);
        }

        .admin-sidebar__link--danger:hover {
            background: rgba(239, 68, 68, 0.18);
        }

        .admin-sidebar__submenu {
            margin-left: 1rem;
            padding-left: 1rem;
            border-left: 1px solid rgba(255, 255, 255, 0.12);
        }

        .admin-sidebar__submenu-link {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border-radius: 0.85rem;
            padding: 0.7rem 0.85rem;
            color: rgba(255, 255, 255, 0.74);
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease;
        }

        .admin-sidebar__submenu-link:hover,
        .admin-sidebar__submenu-link.is-active {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .admin-workspace {
            min-height: 100vh;
            padding: 1rem;
            width: 100%;
            max-width: none;
        }

        .admin-topbar-shell {
            position: sticky;
            top: 0;
            z-index: 30;
            margin-bottom: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.82);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .admin-topbar-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(90deg, rgba(16, 185, 129, 0.08), transparent 24%, transparent 76%, rgba(16, 185, 129, 0.06));
            pointer-events: none;
        }

        .admin-topbar {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
        }

        .admin-topbar__context {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            min-width: 0;
        }

        .admin-topbar__back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.9rem;
            height: 2.9rem;
            border: 1px solid rgba(16, 185, 129, 0.18);
            border-radius: 1rem;
            background: linear-gradient(180deg, #ecfdf5, #d1fae5);
            color: #047857;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .admin-topbar__eyebrow {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .admin-topbar__page {
            display: block;
            margin-top: 0.2rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-topbar__meta {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .admin-topbar__pill {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.72rem 0.95rem;
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 999px;
            background: rgba(248, 250, 252, 0.92);
            color: #334155;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .admin-topbar__profile {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.42rem 0.5rem 0.42rem 0.42rem;
            border: 1px solid rgba(15, 23, 42, 0.07);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
        }

        .admin-topbar__avatar {
            width: 2.7rem;
            height: 2.7rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #10b981, #047857);
            color: #ffffff;
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 10px 22px rgba(16, 185, 129, 0.26);
            overflow: hidden;
        }

        .admin-content {
            padding: 0.25rem 0 1rem;
            width: 100%;
        }

        .admin-content > .container-fluid {
            padding: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        .app-wrapper,
        .app-main {
            width: 100%;
            max-width: none;
        }

        @media (max-width: 1024px) {
            .admin-workspace {
                padding: 0.9rem;
            }

            .admin-topbar {
                padding: 0.9rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .admin-topbar__meta {
                width: 100%;
                justify-content: space-between;
            }

            .admin-topbar__page {
                white-space: normal;
            }
        }
    </style>
    
    @stack('head')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary {{ request()->routeIs('admin.*') && !request()->routeIs('login') ? 'admin-theme' : '' }}">
    @livewireScripts
    @php
        $isEmbeddedFrame = request()->header('Sec-Fetch-Dest') === 'iframe';
        $showAdminSidebar = request()->routeIs('admin.*') && !request()->routeIs('login') && !$isEmbeddedFrame;
        $adminUserInitial = auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'A';
        $displayYear = (int) request()->query('annee', now()->year);
        $previousUrl = url()->previous();
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);
        $currentHost = request()->getHost();
        $safeBackUrl = $previousUrl && (!$previousHost || $previousHost === $currentHost)
            ? $previousUrl
            : route('admin.dashboard');
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
        $currentAdminSection = collect($adminTopMenu)->first(fn ($item) => request()->routeIs($item['pattern']));
        $currentAdminLabel = $currentAdminSection['label'] ?? 'Administration';
        $opensCaisseMenu = request()->routeIs('admin.caisses.*') || request()->routeIs('admin.agences.*') || request()->routeIs('admin.guichets.*') || request()->routeIs('admin.agents.*');
    @endphp

    <div class="app-wrapper admin-layout d-flex" @if($showAdminSidebar) x-data="{ sidebarOpen: true, mobileSidebarOpen: false, caisseMenuOpen: {{ $opensCaisseMenu ? 'true' : 'false' }} }" @endif>
        @if ($showAdminSidebar)
            <button
                @click="if (window.innerWidth < 768) { mobileSidebarOpen = true } else { sidebarOpen = !sidebarOpen }"
                class="fixed left-4 top-4 z-50 rounded-xl border border-white/70 bg-white px-3 py-2 text-slate-700 shadow-lg shadow-slate-200/60 hover:bg-slate-50 focus:outline-none md:hidden"
                type="button"
            >
                <i class="fas fa-bars"></i>
            </button>

            <div
                x-cloak
                x-show="mobileSidebarOpen"
                x-transition.opacity
                @click="mobileSidebarOpen = false"
                class="fixed inset-0 z-40 bg-slate-950/35 md:hidden"
            ></div>

            <aside
                id="admin-sidebar"
                class="admin-sidebar fixed inset-y-0 left-0 z-50 flex w-72 min-h-screen flex-col px-4 py-5 shadow-2xl shadow-emerald-950/20 transition-transform duration-200 md:relative md:inset-auto md:z-auto md:flex-shrink-0"
                x-cloak
                x-transition:enter="transition-transform duration-200"
                x-transition:enter-start="-translate-x-full md:translate-x-0"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                :class="(window.innerWidth < 768 ? mobileSidebarOpen : sidebarOpen) ? 'translate-x-0 md:flex' : '-translate-x-full md:hidden'"
            >
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    :class="sidebarOpen ? 'left-[16.75rem]' : 'left-4'"
                    class="fixed top-4 z-[60] hidden rounded-xl border border-white/80 bg-white px-3 py-2 text-emerald-700 shadow-lg shadow-slate-200/70 hover:bg-emerald-50 focus:outline-none transition-all duration-200 md:block"
                    type="button"
                >
                    <i :class="sidebarOpen ? 'fas fa-angle-left' : 'fas fa-angle-right'"></i>
                </button>

                <button
                    @click="mobileSidebarOpen = false"
                    class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white md:hidden"
                    type="button"
                >
                    <i class="fas fa-times"></i>
                </button>

                <div class="admin-sidebar__brand mb-8 rounded-[1.6rem] p-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-lg font-black text-emerald-700 shadow-lg shadow-emerald-950/10">
                            SG
                        </span>
                        <div>
                            <p class="text-[0.68rem] font-bold uppercase tracking-[0.28em] text-emerald-100/80">Portail Admin</p>
                            <p class="mt-1 text-xl font-extrabold tracking-tight text-white">SGP-RCPB</p>
                        </div>
                    </div>
                    <p class="mt-4 max-w-[15rem] text-sm leading-6 text-emerald-50/80">
                        Gestion centrale des entites, directions, services et comptes utilisateurs.
                    </p>
                </div>

                <div class="mb-3 px-2">
                    <p class="text-[0.68rem] font-bold uppercase tracking-[0.28em] text-emerald-100/65">Navigation</p>
                </div>

                <nav class="flex flex-col gap-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                        <i class="fas fa-home text-base"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.entites.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.entites.*') ? 'is-active' : '' }}">
                        <i class="fas fa-university text-base"></i>
                        <span>Faitiere</span>
                    </a>
                    <a href="{{ route('admin.directions.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.directions.*') ? 'is-active' : '' }}">
                        <i class="fas fa-sitemap text-base"></i>
                        <span>Delegations</span>
                    </a>

                    <div class="pt-1">
                        <button @click="caisseMenuOpen = !caisseMenuOpen" class="admin-sidebar__link w-full focus:outline-none">
                            <i class="fas fa-cash-register text-base"></i>
                            <span>Reseau caisses</span>
                            <i :class="caisseMenuOpen ? 'fa-chevron-up' : 'fa-chevron-down'" class="fas ml-auto text-xs"></i>
                        </button>
                        <div x-show="caisseMenuOpen" x-transition class="admin-sidebar__submenu mt-2 flex flex-col gap-1.5">
                            <a href="{{ route('admin.caisses.index') }}" class="admin-sidebar__submenu-link {{ request()->routeIs('admin.caisses.*') ? 'is-active' : '' }}">
                                <i class="fas fa-wallet"></i><span>Caisses</span>
                            </a>
                            <a href="{{ route('admin.agences.index') }}" class="admin-sidebar__submenu-link {{ request()->routeIs('admin.agences.*') ? 'is-active' : '' }}">
                                <i class="fas fa-building"></i><span>Agences</span>
                            </a>
                            <a href="{{ route('admin.guichets.index') }}" class="admin-sidebar__submenu-link {{ request()->routeIs('admin.guichets.*') ? 'is-active' : '' }}">
                                <i class="fas fa-store"></i><span>Guichets</span>
                            </a>
                            <a href="{{ route('admin.agents.index') }}" class="admin-sidebar__submenu-link {{ request()->routeIs('admin.agents.*') ? 'is-active' : '' }}">
                                <i class="fas fa-user"></i><span>Agents</span>
                            </a>
                        </div>
                    </div>

                    <div class="my-4 h-px bg-white/10"></div>

                    <a href="{{ route('admin.statistiques.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.statistiques.*') ? 'is-active' : '' }}">
                        <i class="fas fa-chart-line text-base"></i>
                        <span>Statistiques</span>
                    </a>
                    <a href="{{ route('admin.evaluations.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.evaluations.*') ? 'is-active' : '' }}">
                        <i class="fas fa-clipboard-check text-base"></i>
                        <span>Evaluations</span>
                    </a>
                    <a href="{{ route('admin.settings.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                        <i class="fas fa-cog text-base"></i>
                        <span>Parametres</span>
                    </a>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.utilisateurs.*') ? 'is-active' : '' }}">
                        <i class="fas fa-users-cog text-base"></i>
                        <span>Utilisateurs</span>
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="admin-sidebar__link admin-sidebar__link--danger w-full">
                            <i class="fas fa-sign-out-alt text-base"></i>
                            <span>Deconnexion</span>
                        </button>
                    </form>
                </nav>
            </aside>

            <div class="admin-workspace flex-1 transition-all duration-200">

            <main class="app-main">
                <!-- Header/Topbar modernisé -->
                <div class="admin-topbar-shell">
                    <!-- Flèche de retour universelle -->
                    <div class="admin-topbar">
                        <div class="admin-topbar__context">
                            <a href="{{ $safeBackUrl }}" class="admin-topbar__back" title="Retour">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <div class="min-w-0">
                                <span class="admin-topbar__eyebrow">Tableau administratif</span>
                                <span class="admin-topbar__page">{{ $currentAdminLabel }}</span>
                            </div>
                        </div>
                        <div class="admin-topbar__meta">
                            <div class="admin-topbar__pill">
                                <i class="fas fa-calendar-alt text-emerald-600"></i>
                                <span>{{ now()->format('d/m/Y') }}</span>
                            </div>
                        <!-- Profil utilisateur -->
                        @php $user = auth()->user(); $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A'; @endphp
                        <div class="admin-topbar__profile">
                            <div class="admin-topbar__avatar">
                                @if($user && $user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="Photo de profil" class="h-full w-full object-cover" />
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                            <div class="pr-2">
                                <p class="text-sm font-semibold text-slate-800">{{ $user ? $user->name : 'Admin' }}</p>
                                <p class="text-xs text-slate-500">{{ $user && $user->role ? $user->role : 'Administrateur' }}</p>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                <!-- Fin Header/Topbar -->
                <div class="app-content admin-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
            </main>
            </div>
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
