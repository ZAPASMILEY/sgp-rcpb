<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'SGP-RCPB'))</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    ['route' => 'admin.alertes.index', 'icon' => 'fas fa-bell', 'label' => 'Alertes'],
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

        /*
         * =============================================================
         *  THEME RCPB (Identité) — UNIQUEMENT vert (#2e7d32) & jaune (#d4a017)
         *  Aucune autre couleur (cyan, bleu, rose, violet, orange, fuchsia, pink, sky, indigo, red) ne doit apparaitre.
         * =============================================================
         */

        /* ---- Palette de reference ---- */
        body.theme-rcpb {
            --rcpb-green:       #2e7d32;
            --rcpb-green-dark:  #1b5e20;
            --rcpb-green-light: #43a047;
            --rcpb-green-bg:    #dcedc8;
            --rcpb-green-bg50:  rgba(220,237,200,0.5);
            --rcpb-green-bg30:  rgba(220,237,200,0.3);
            --rcpb-yellow:      #d4a017;
            --rcpb-yellow-dark: #b8860b;
            --rcpb-yellow-bg:   #fff9db;
            --rcpb-yellow-bg50: rgba(255,249,219,0.5);
            background-color: #f0f7e6 !important;
        }

        /* ==== PAGE BACKGROUNDS ==== */
        body.theme-rcpb .bg-\[\#f1f5f9\],
        body.theme-rcpb .bg-slate-50 {
            background-color: #f0f7e6 !important;
        }
        body.theme-rcpb .bg-\[linear-gradient\(180deg\,\#f6f9ff_0\%\,\#fbfdff_100\%\)\] {
            background: linear-gradient(180deg, #f0f7e6 0%, #f5fbe8 100%) !important;
        }

        /* ==== PANELS — subtle green tint ==== */
        body.theme-rcpb .rounded-2xl.bg-white.shadow-sm,
        body.theme-rcpb .rounded-\[26px\].bg-white\/90 {
            border: 1px solid rgba(46,125,50,0.12);
        }

        /* ==== TEXT — every non-green/yellow colour → green or yellow ==== */
        body.theme-rcpb [class*="text-cyan-"],
        body.theme-rcpb [class*="text-sky-"],
        body.theme-rcpb [class*="text-blue-"],
        body.theme-rcpb [class*="text-indigo-"],
        body.theme-rcpb [class*="text-violet-"],
        body.theme-rcpb [class*="text-purple-"],
        body.theme-rcpb [class*="text-fuchsia-"] {
            color: var(--rcpb-green) !important;
        }
        body.theme-rcpb [class*="text-rose-"],
        body.theme-rcpb [class*="text-pink-"],
        body.theme-rcpb [class*="text-red-"],
        body.theme-rcpb [class*="text-orange-"] {
            color: var(--rcpb-yellow-dark) !important;
        }
        body.theme-rcpb [class*="text-amber-"] {
            color: var(--rcpb-yellow-dark) !important;
        }

        /* hover text */
        body.theme-rcpb [class*="hover\:text-cyan-"]:hover,
        body.theme-rcpb [class*="hover\:text-sky-"]:hover,
        body.theme-rcpb [class*="hover\:text-blue-"]:hover,
        body.theme-rcpb [class*="hover\:text-indigo-"]:hover,
        body.theme-rcpb [class*="hover\:text-violet-"]:hover,
        body.theme-rcpb [class*="hover\:text-purple-"]:hover,
        body.theme-rcpb [class*="hover\:text-fuchsia-"]:hover {
            color: var(--rcpb-green-dark) !important;
        }
        body.theme-rcpb [class*="hover\:text-rose-"]:hover,
        body.theme-rcpb [class*="hover\:text-pink-"]:hover,
        body.theme-rcpb [class*="hover\:text-red-"]:hover,
        body.theme-rcpb [class*="hover\:text-orange-"]:hover,
        body.theme-rcpb [class*="hover\:text-amber-"]:hover {
            color: var(--rcpb-yellow-dark) !important;
        }

        /* ==== BACKGROUNDS — solid colours ==== */
        body.theme-rcpb [class*="bg-cyan-50"],
        body.theme-rcpb [class*="bg-sky-50"],
        body.theme-rcpb [class*="bg-blue-50"],
        body.theme-rcpb [class*="bg-indigo-50"],
        body.theme-rcpb [class*="bg-violet-50"],
        body.theme-rcpb [class*="bg-purple-50"],
        body.theme-rcpb [class*="bg-fuchsia-50"] {
            background-color: var(--rcpb-green-bg50) !important;
        }
        body.theme-rcpb [class*="bg-rose-50"],
        body.theme-rcpb [class*="bg-pink-50"],
        body.theme-rcpb [class*="bg-red-50"],
        body.theme-rcpb [class*="bg-orange-50"],
        body.theme-rcpb [class*="bg-amber-50"] {
            background-color: var(--rcpb-yellow-bg50) !important;
        }

        /* bg-{color}-100 */
        body.theme-rcpb [class*="bg-cyan-100"],
        body.theme-rcpb [class*="bg-sky-100"],
        body.theme-rcpb [class*="bg-blue-100"],
        body.theme-rcpb [class*="bg-indigo-100"],
        body.theme-rcpb [class*="bg-violet-100"],
        body.theme-rcpb [class*="bg-fuchsia-100"] {
            background-color: var(--rcpb-green-bg30) !important;
        }
        body.theme-rcpb [class*="bg-rose-100"],
        body.theme-rcpb [class*="bg-pink-100"],
        body.theme-rcpb [class*="bg-red-100"],
        body.theme-rcpb [class*="bg-orange-100"],
        body.theme-rcpb [class*="bg-amber-100"] {
            background-color: rgba(255,249,219,0.4) !important;
        }

        /* bg-{color}-400/500/600/700 (solid buttons, badges) → green */
        body.theme-rcpb [class*="bg-cyan-4"],
        body.theme-rcpb [class*="bg-cyan-5"],
        body.theme-rcpb [class*="bg-cyan-6"],
        body.theme-rcpb [class*="bg-cyan-7"],
        body.theme-rcpb [class*="bg-sky-4"],
        body.theme-rcpb [class*="bg-sky-5"],
        body.theme-rcpb [class*="bg-sky-6"],
        body.theme-rcpb [class*="bg-blue-4"],
        body.theme-rcpb [class*="bg-blue-5"],
        body.theme-rcpb [class*="bg-blue-6"],
        body.theme-rcpb [class*="bg-blue-7"],
        body.theme-rcpb [class*="bg-indigo-5"],
        body.theme-rcpb [class*="bg-indigo-6"],
        body.theme-rcpb [class*="bg-violet-5"],
        body.theme-rcpb [class*="bg-violet-6"],
        body.theme-rcpb [class*="bg-purple-5"],
        body.theme-rcpb [class*="bg-purple-6"],
        body.theme-rcpb [class*="bg-fuchsia-5"],
        body.theme-rcpb [class*="bg-fuchsia-6"] {
            background-color: var(--rcpb-green) !important;
        }
        /* bg-{color}-400/500/600 (warm) → yellow */
        body.theme-rcpb [class*="bg-rose-4"],
        body.theme-rcpb [class*="bg-rose-5"],
        body.theme-rcpb [class*="bg-rose-6"],
        body.theme-rcpb [class*="bg-pink-4"],
        body.theme-rcpb [class*="bg-pink-5"],
        body.theme-rcpb [class*="bg-pink-6"],
        body.theme-rcpb [class*="bg-red-4"],
        body.theme-rcpb [class*="bg-red-5"],
        body.theme-rcpb [class*="bg-red-6"],
        body.theme-rcpb [class*="bg-orange-4"],
        body.theme-rcpb [class*="bg-orange-5"],
        body.theme-rcpb [class*="bg-orange-6"],
        body.theme-rcpb [class*="bg-amber-4"],
        body.theme-rcpb [class*="bg-amber-5"],
        body.theme-rcpb [class*="bg-amber-6"] {
            background-color: var(--rcpb-yellow) !important;
        }

        /* bg-slate-800/900 (dark buttons) → green */
        body.theme-rcpb .bg-slate-800 {
            background-color: var(--rcpb-green) !important;
        }
        body.theme-rcpb .bg-slate-900 {
            background-color: var(--rcpb-green-dark) !important;
        }

        /* hover backgrounds */
        body.theme-rcpb [class*="hover\:bg-cyan-"]:hover,
        body.theme-rcpb [class*="hover\:bg-sky-"]:hover,
        body.theme-rcpb [class*="hover\:bg-blue-"]:hover,
        body.theme-rcpb [class*="hover\:bg-indigo-"]:hover,
        body.theme-rcpb [class*="hover\:bg-violet-"]:hover,
        body.theme-rcpb [class*="hover\:bg-purple-"]:hover,
        body.theme-rcpb [class*="hover\:bg-fuchsia-"]:hover {
            background-color: var(--rcpb-green-dark) !important;
        }
        body.theme-rcpb [class*="hover\:bg-rose-"]:hover,
        body.theme-rcpb [class*="hover\:bg-pink-"]:hover,
        body.theme-rcpb [class*="hover\:bg-red-"]:hover,
        body.theme-rcpb [class*="hover\:bg-orange-"]:hover,
        body.theme-rcpb [class*="hover\:bg-amber-"]:hover {
            background-color: var(--rcpb-yellow-dark) !important;
        }
        body.theme-rcpb .hover\:bg-slate-700:hover,
        body.theme-rcpb .hover\:bg-slate-900:hover {
            background-color: var(--rcpb-green-dark) !important;
        }

        /* ==== BORDERS ==== */
        body.theme-rcpb [class*="border-cyan-"],
        body.theme-rcpb [class*="border-sky-"],
        body.theme-rcpb [class*="border-blue-"],
        body.theme-rcpb [class*="border-indigo-"],
        body.theme-rcpb [class*="border-violet-"],
        body.theme-rcpb [class*="border-purple-"],
        body.theme-rcpb [class*="border-fuchsia-"] {
            border-color: rgba(46,125,50,0.3) !important;
        }
        body.theme-rcpb [class*="border-rose-"],
        body.theme-rcpb [class*="border-pink-"],
        body.theme-rcpb [class*="border-red-"],
        body.theme-rcpb [class*="border-orange-"],
        body.theme-rcpb [class*="border-amber-"] {
            border-color: rgba(212,160,23,0.3) !important;
        }

        /* ==== GRADIENTS — all to green or yellow ==== */
        body.theme-rcpb [class*="from-cyan-"],
        body.theme-rcpb [class*="from-sky-"],
        body.theme-rcpb [class*="from-blue-"],
        body.theme-rcpb [class*="from-indigo-"],
        body.theme-rcpb [class*="from-violet-"],
        body.theme-rcpb [class*="from-purple-"] {
            --tw-gradient-from: var(--rcpb-green) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
        body.theme-rcpb [class*="to-blue-"],
        body.theme-rcpb [class*="to-indigo-"],
        body.theme-rcpb [class*="to-sky-"],
        body.theme-rcpb [class*="to-purple-"] {
            --tw-gradient-to: var(--rcpb-green-dark) !important;
        }
        body.theme-rcpb [class*="from-rose-"],
        body.theme-rcpb [class*="from-pink-"],
        body.theme-rcpb [class*="from-fuchsia-"],
        body.theme-rcpb [class*="from-orange-"],
        body.theme-rcpb [class*="from-red-"],
        body.theme-rcpb [class*="from-amber-"] {
            --tw-gradient-from: var(--rcpb-yellow) !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
        body.theme-rcpb [class*="to-pink-"],
        body.theme-rcpb [class*="to-rose-"],
        body.theme-rcpb [class*="to-fuchsia-"],
        body.theme-rcpb [class*="to-orange-"],
        body.theme-rcpb [class*="to-red-"] {
            --tw-gradient-to: var(--rcpb-yellow-dark) !important;
        }
        /* bg-gradient-to-br settings cards */
        body.theme-rcpb .bg-gradient-to-br.from-slate-700 {
            --tw-gradient-from: #33691e !important;
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to) !important;
        }
        body.theme-rcpb .to-slate-900 {
            --tw-gradient-to: var(--rcpb-green-dark) !important;
        }

        /* ==== FOCUS RINGS & BORDERS — all green ==== */
        body.theme-rcpb [class*="focus\:border-cyan-"]:focus,
        body.theme-rcpb [class*="focus\:border-sky-"]:focus,
        body.theme-rcpb [class*="focus\:border-blue-"]:focus,
        body.theme-rcpb [class*="focus\:border-indigo-"]:focus,
        body.theme-rcpb [class*="focus\:border-violet-"]:focus,
        body.theme-rcpb [class*="focus\:border-purple-"]:focus,
        body.theme-rcpb [class*="focus\:border-fuchsia-"]:focus,
        body.theme-rcpb [class*="focus\:border-rose-"]:focus,
        body.theme-rcpb [class*="focus\:border-pink-"]:focus,
        body.theme-rcpb [class*="focus\:border-red-"]:focus,
        body.theme-rcpb [class*="focus\:border-orange-"]:focus,
        body.theme-rcpb [class*="focus\:border-amber-"]:focus,
        body.theme-rcpb [class*="focus\:border-emerald-"]:focus {
            border-color: var(--rcpb-green-light) !important;
        }
        body.theme-rcpb [class*="focus\:ring-cyan-"]:focus,
        body.theme-rcpb [class*="focus\:ring-sky-"]:focus,
        body.theme-rcpb [class*="focus\:ring-blue-"]:focus,
        body.theme-rcpb [class*="focus\:ring-indigo-"]:focus,
        body.theme-rcpb [class*="focus\:ring-violet-"]:focus,
        body.theme-rcpb [class*="focus\:ring-purple-"]:focus,
        body.theme-rcpb [class*="focus\:ring-fuchsia-"]:focus,
        body.theme-rcpb [class*="focus\:ring-rose-"]:focus,
        body.theme-rcpb [class*="focus\:ring-pink-"]:focus,
        body.theme-rcpb [class*="focus\:ring-red-"]:focus,
        body.theme-rcpb [class*="focus\:ring-orange-"]:focus,
        body.theme-rcpb [class*="focus\:ring-amber-"]:focus,
        body.theme-rcpb [class*="focus\:ring-emerald-"]:focus {
            --tw-ring-color: rgba(46,125,50,0.4) !important;
        }

        /* ==== SHADOWS ==== */
        body.theme-rcpb [class*="shadow-cyan-"],
        body.theme-rcpb [class*="shadow-sky-"],
        body.theme-rcpb [class*="shadow-blue-"],
        body.theme-rcpb [class*="shadow-indigo-"],
        body.theme-rcpb [class*="shadow-violet-"],
        body.theme-rcpb [class*="shadow-purple-"],
        body.theme-rcpb [class*="shadow-fuchsia-"] {
            --tw-shadow-color: rgba(46,125,50,0.2) !important;
        }
        body.theme-rcpb [class*="shadow-rose-"],
        body.theme-rcpb [class*="shadow-pink-"],
        body.theme-rcpb [class*="shadow-red-"],
        body.theme-rcpb [class*="shadow-orange-"],
        body.theme-rcpb [class*="shadow-amber-"] {
            --tw-shadow-color: rgba(212,160,23,0.2) !important;
        }
        body.theme-rcpb .shadow-emerald-100\/60 {
            box-shadow: 0 20px 60px rgba(46,125,50,0.15) !important;
        }

        /* ==== FILE INPUTS ==== */
        body.theme-rcpb [class*="file\:bg-cyan-"],
        body.theme-rcpb [class*="file\:bg-sky-"],
        body.theme-rcpb [class*="file\:bg-blue-"],
        body.theme-rcpb [class*="file\:bg-amber-"] {
            --tw-file-bg: var(--rcpb-green-bg) !important;
        }
        body.theme-rcpb [class*="file\:text-cyan-"] {
            --tw-file-text: var(--rcpb-green) !important;
        }
        body.theme-rcpb input[type="file"]::file-selector-button {
            background-color: var(--rcpb-green-bg) !important;
            color: var(--rcpb-green-dark) !important;
        }
        body.theme-rcpb input[type="file"]:hover::file-selector-button {
            background-color: var(--rcpb-green-bg50) !important;
        }

        /* ==== PEER-CHECKED (theme radio in settings) ==== */
        body.theme-rcpb .peer:checked ~ [class*="peer-checked\:border-cyan-"] {
            border-color: var(--rcpb-green) !important;
        }
        body.theme-rcpb .peer:checked ~ [class*="peer-checked\:bg-cyan-"] {
            background-color: var(--rcpb-green-bg30) !important;
        }

        /* ==== DIGITAL CLOCK ==== */
        body.theme-rcpb #digital-clock {
            color: var(--rcpb-green) !important;
        }

        /* ==== HOVER on blue-50 (JS toggles in show pages) ==== */
        body.theme-rcpb .bg-blue-50 {
            background-color: var(--rcpb-green-bg50) !important;
        }

        /* ==== Dots/bullets (h-2 w-2 bg-{color}) ==== */
        body.theme-rcpb [class*="bg-cyan-"].rounded-full,
        body.theme-rcpb [class*="bg-sky-"].rounded-full,
        body.theme-rcpb [class*="bg-blue-"].rounded-full,
        body.theme-rcpb [class*="bg-pink-"].rounded-full {
            background-color: var(--rcpb-green) !important;
        }
        body.theme-rcpb [class*="bg-rose-"].rounded-full {
            background-color: var(--rcpb-yellow) !important;
        }

        /* ==== Emerald is already green — keep consistent ==== */
        body.theme-rcpb .bg-emerald-700,
        body.theme-rcpb .bg-emerald-600 {
            background-color: var(--rcpb-green) !important;
        }
    </style>
</head>

@php
    $themePreference = auth()->check() ? (auth()->user()->theme_preference ?? 'reference') : 'reference';
@endphp
<body class="h-full antialiased {{ $isModalMode ? 'bg-slate-50' : '' }} {{ $themePreference === 'classic' ? 'theme-rcpb' : '' }}">
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
                    <div class="relative" id="notif-bell-wrapper">
                        <button onclick="document.getElementById('notif-dropdown').classList.toggle('hidden')" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-400 transition hover:text-slate-600">
                            <i class="fas fa-bell"></i>
                            @if($alertesNonLuesCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-black text-white">{{ $alertesNonLuesCount > 99 ? '99+' : $alertesNonLuesCount }}</span>
                            @endif
                        </button>
                        <div id="notif-dropdown" class="absolute right-0 top-full z-50 mt-2 hidden w-80 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <p class="text-sm font-black text-slate-800">Notifications</p>
                                @if($alertesNonLuesCount > 0)
                                    <form method="POST" action="{{ route('admin.alertes.lire-tout') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-emerald-600 hover:underline">Tout marquer lu</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($alertesNonLues as $notif)
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
                            @if($alertesNonLuesCount > 0)
                                <a href="{{ route('admin.alertes.index') }}" class="flex items-center justify-center border-t border-slate-100 py-3 text-[11px] font-bold text-emerald-600 transition hover:bg-slate-50">
                                    Voir toutes les alertes <i class="fas fa-arrow-right ml-1.5 text-[9px]"></i>
                                </a>
                            @endif
                        </div>
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

        // Close notification dropdown on click outside
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('notif-bell-wrapper');
            const dropdown = document.getElementById('notif-dropdown');
            if (wrapper && dropdown && !wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
