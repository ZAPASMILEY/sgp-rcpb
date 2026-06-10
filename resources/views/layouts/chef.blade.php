{{--
    ──────────────────────────────────────────────────────────────────────────
    layouts/chef.blade.php — Layout de l'espace chef
    ──────────────────────────────────────────────────────────────────────────

    Structure identique à layouts/personnel.blade.php mais adaptée aux chefs.

    Sidebar en trois sections :
      1. Mon espace     → tableau de bord (chef.mon-espace)
      2. Mon dossier    → mes évaluations reçues, mes objectifs reçus
      3. Mon équipe     → mes agents, évaluer un agent, assigner objectifs

    ChefEntity est résolu ici pour afficher le nom de la structure dans
    le header de la sidebar. En cas d'échec (structure non configurée),
    on affiche "Mon Équipe" en valeur de repli.
    ──────────────────────────────────────────────────────────────────────────
--}}
@php
    use App\Http\Controllers\Chef\ChefEntity;

    $user    = auth()->user();
    $chefCtx = ChefEntity::resolve($user); // null si structure non trouvée

    // Nom de la structure gérée par le chef (affiché en en-tête sidebar)
    $structureNom  = $chefCtx?->getNom() ?? 'Mon Équipe';
    $structureType = $chefCtx?->getTypeLabel() ?? 'Chef';

    // ── Badges sidebar ──────────────────────────────────────────────────────
    // Deux sources d'indicateurs :
    //   1. Réclamations actives sur les évaluations des agents (attention requise)
    //   2. Notifications non lues pour Mon Dossier / Formations
    $navBadges    = ['chef.mon-espace' => 0, 'chef.equipe' => 0, 'chef.guichets' => 0, 'chef.formations.index' => 0];
    $navBadgeTips = [];

    if ($user) {
        // — Mon Dossier : notifications non lues concernant l'espace personnel uniquement
        $unreadCount = $user->alertesNonLues()->where('lien', 'like', '%mon-espace%')->count();
        $navBadges['chef.mon-espace'] = $unreadCount;
        if ($unreadCount > 0) {
            $firstNotif = $user->alertesNonLues()->where('lien', 'like', '%mon-espace%')->first();
            $navBadgeTips['chef.mon-espace'] = $firstNotif?->titre;
        }

        // — Mes Agents : réclamations actives sur les évaluations créées par ce chef
        if ($chefCtx) {
            $agentIds = $chefCtx->getAgents()->pluck('id');

            if ($agentIds->isNotEmpty()) {
                $agentsEnReclamation = \App\Models\Evaluation::whereIn('evaluable_id', $agentIds)
                    ->where('evaluable_type', \App\Models\Agent::class)
                    ->where('evaluateur_id', $user->id)
                    ->where('statut', 'reclamation')
                    ->where(fn ($q) => $q->whereNull('statut_reclamation')
                        ->orWhere('statut_reclamation', '!=', 'maintenu'))
                    ->distinct('evaluable_id')
                    ->count('evaluable_id');

                $navBadges['chef.equipe'] = $agentsEnReclamation;
                if ($agentsEnReclamation > 0) {
                    $navBadgeTips['chef.equipe'] = $agentsEnReclamation . ' agent(s) avec réclamation active';
                }
            }

            // — Mes Chefs de Guichet (Chef_Agence) : réclamations actives sur évals guichet
            if ($chefCtx->type === 'agence') {
                $guichetIds = \App\Models\Guichet::where('agence_id', $chefCtx->entity->id)
                    ->pluck('id');

                if ($guichetIds->isNotEmpty()) {
                    $guichetsEnReclamation = \App\Models\Evaluation::whereIn('evaluable_id', $guichetIds)
                        ->where('evaluable_type', \App\Models\Guichet::class)
                        ->where('evaluateur_id', $user->id)
                        ->where('statut', 'reclamation')
                        ->where(fn ($q) => $q->whereNull('statut_reclamation')
                            ->orWhere('statut_reclamation', '!=', 'maintenu'))
                        ->distinct('evaluable_id')
                        ->count('evaluable_id');

                    $navBadges['chef.guichets'] = $guichetsEnReclamation;
                    if ($guichetsEnReclamation > 0) {
                        $navBadgeTips['chef.guichets'] = $guichetsEnReclamation . ' guichet(s) avec réclamation active';
                    }
                }
            }
        }
    }

    // Menu de la sidebar — identique au pattern layouts/personnel.blade.php
    // mais avec une section supplémentaire "Mon équipe"
    $menuSections = [
        [
            'title' => 'Mon espace',
            'items' => [
                [
                    'route' => 'chef.dashboard',
                    'icon'  => 'fas fa-house',
                    'label' => 'Tableau de bord',
                ],
            ],
        ],
        [
            'title' => 'Mon dossier',
            'items' => [
                [
                    'route' => 'chef.mon-espace',
                    'icon'  => 'fas fa-folder-open',
                    'label' => 'Mon dossier',
                    'badge' => $navBadges['chef.mon-espace'] ?? 0,
                    'badgeTip' => $navBadgeTips['chef.mon-espace'] ?? null,
                ],
            ],
        ],
        [
            'title' => 'Mon équipe',
            'items' => array_values(array_filter([
                [
                    'route'    => 'chef.equipe',
                    'icon'     => 'fas fa-users',
                    'label'    => 'Mes agents',
                    'disabled' => false,
                    'badge'    => $navBadges['chef.equipe'] ?? 0,
                    'badgeTip' => $navBadgeTips['chef.equipe'] ?? null,
                ],
                // Guichets : affiché uniquement pour le Chef_Agence
                $chefCtx?->type === 'agence' ? [
                    'route'    => 'chef.guichets',
                    'icon'     => 'fas fa-store',
                    'label'    => 'Mes Chefs de Guichet',
                    'disabled' => false,
                    'badge'    => $navBadges['chef.guichets'] ?? 0,
                    'badgeTip' => $navBadgeTips['chef.guichets'] ?? null,
                ] : null,

            ])),
        ],
        [
            'title' => 'Formations',
            'items' => [
                [
                    'route'    => 'chef.formations.index',
                    'icon'     => 'fas fa-graduation-cap',
                    'label'    => 'Mes formations',
                    'disabled' => false,
                    'badge'    => $navBadges['chef.formations.index'] ?? 0,
                    'badgeTip' => $navBadgeTips['chef.formations.index'] ?? null,
                ],
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
    <title>@yield('title', 'Espace Chef — ' . config('app.name', 'SGP-RCPB'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <style>
        /* ── Variables CSS ─────────────────────────────────────────────────── */
        :root {
            --sidebar-width: 260px;
            --sidebar-color:      #008751;
            --sidebar-color-dark: #006837;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ── Sidebar ────────────────────────────────────────────────────────── */
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

        /* ── Liens de navigation ────────────────────────────────────────────── */
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

        /* ── Contenu principal ──────────────────────────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            transition: margin 0.3s ease, width 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        /* ── Mode réduit (collapsed) ────────────────────────────────────────── */
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

        /* ── Bouton de réduction ────────────────────────────────────────────── */
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

        /* ── Responsive mobile ──────────────────────────────────────────────── */
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); width: var(--sidebar-width) !important; }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0 !important; width: 100% !important; }
            .sidebar-collapse-btn { display: none !important; }
        }
    </style>
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

    @include('layouts._alerte_banniere')

    {{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
    <nav class="sidebar shadow" id="sidebar">

        {{-- Bouton de réduction (desktop uniquement) --}}
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Réduire le menu">
            <i class="fas fa-chevron-left"></i>
        </button>

        {{-- En-tête : nom de la structure + type --}}
        <div class="sidebar-header">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 text-slate-200 shadow">
                <i class="fas fa-briefcase text-2xl"></i>
            </div>
            <h5 class="mt-3 text-lg font-black text-white leading-tight">{{ $structureNom }}</h5>
            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/60">{{ $structureType }}</p>
            @if($user)
                <p class="mt-2 text-xs font-medium text-white/80 truncate px-2">{{ $user->name }}</p>
            @endif
        </div>

        {{-- Liens de navigation —— parcourus via la variable $menuSections --}}
        <div class="flex flex-1 flex-col mt-1">
            @foreach($menuSections as $section)
                <div class="sidebar-label">{{ $section['title'] }}</div>
                @foreach($section['items'] as $item)
                    @php
                        $isDisabled = $item['disabled'] ?? false;
                        $query      = $item['query'] ?? null;
                        $isActive   = ! $isDisabled && request()->routeIs($item['route'] . '*');
                        $link       = ! $isDisabled
                            ? route($item['route']) . ($query ? '?' . $query : '')
                            : null;
                    @endphp
                    @php
                        $badge    = $item['badge'] ?? 0;
                        $badgeTip = $item['badgeTip'] ?? null;
                    @endphp
                    @if($isDisabled)
                        @php
                            $feat    = $item['feature'] ?? null;
                            $tipMsg  = $feat === 'evaluations'
                                ? ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')
                                : ($feat === 'objectifs'
                                    ? ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.')
                                    : 'Fonctionnalité désactivée par l\'administrateur.');
                        @endphp
                        <span class="nav-link opacity-70 cursor-not-allowed select-none"
                              title="{{ $tipMsg }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @else
                        <a href="{{ $link }}" class="nav-link {{ $isActive ? 'active' : '' }}">
                            <i class="{{ $item['icon'] }}"></i>
                            <span class="flex-1">{{ $item['label'] }}</span>
                            @if($badge > 0)
                                <span class="ml-1 inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold leading-none text-white shadow-sm"
                                      title="{{ $badgeTip ?? $badge . ' notification(s) non lue(s)' }}">
                                    {{ $badge > 99 ? '99+' : $badge }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endforeach
            @endforeach
        </div>

        {{-- Pied de sidebar : avatar + déconnexion --}}
        <div class="mt-auto border-t border-white/10 p-3">
            <div class="sidebar-user-compact flex items-center gap-3 rounded-xl bg-white/10 px-3 py-3">
                {{-- Initiale du nom de l'utilisateur --}}
                <div class="user-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-black text-slate-700">
                    {{ strtoupper(substr($user?->name ?? 'C', 0, 1)) }}
                </div>
                <div class="sidebar-user-info min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'Chef' }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-white/60">Session active</p>
                </div>
                {{-- Bouton de déconnexion --}}
                <form action="{{ route('chef.logout') }}" method="POST" class="sidebar-user-info">
                    @csrf
                    <button type="submit"
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white/70 transition hover:bg-rose-500 hover:text-white"
                            title="Se déconnecter">
                        <i class="fas fa-power-off text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- ── Contenu principal ────────────────────────────────────────────────── --}}
    <div class="main-content">

        {{-- Topbar : toggle mobile + titre + cloche notifications --}}
        <header class="relative z-[9999] flex h-12 shrink-0 items-center justify-between border-b border-slate-100 bg-white/80 px-4 backdrop-blur-sm">
            {{-- Bouton hamburger (mobile uniquement) --}}
            <button class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50 text-slate-500 shadow-sm lg:hidden"
                    id="btnToggleSidebar" type="button">
                <i class="fas fa-bars"></i>
            </button>
            <span class="hidden text-sm font-black text-slate-400 lg:block">Espace Chef</span>
            @include('layouts._notif_bell', ['bellId' => 'chef'])
        </header>

        {{-- Contenu de la page (injecté par @yield('content')) --}}
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

    @livewireScripts

    <script>
        // ── Toggle sidebar (mobile) ──────────────────────────────────────────
        document.getElementById('btnToggleSidebar')?.addEventListener('click', () => {
            document.getElementById('sidebar')?.classList.toggle('show');
        });

        // ── Persistance de l'état collapsed (desktop) ────────────────────────
        const STORAGE_KEY = 'chef-sidebar-collapsed';
        if (localStorage.getItem(STORAGE_KEY) === '1') {
            document.body.classList.add('sidebar-collapsed');
        }
        document.getElementById('sidebarCollapseBtn')?.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                STORAGE_KEY,
                document.body.classList.contains('sidebar-collapsed') ? '1' : '0'
            );
        });

        // ── Fermeture sidebar au clic extérieur (mobile) ─────────────────────
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const toggle  = document.getElementById('btnToggleSidebar');
            if (
                window.innerWidth <= 992 &&
                sidebar?.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !toggle?.contains(e.target)
            ) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
    <script>
    (function(){
        var tsOpts={searchField:['text'],maxOptions:300,dropdownParent:'body',render:{
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
