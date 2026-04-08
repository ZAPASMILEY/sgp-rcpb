

@php
    $menuSections = [
        [
            'title' => 'Mon espace',
            'items' => [
                [
                    'route' => 'dg.mon-espace',
                    'icon' => 'fas fa-user-circle',
                    'label' => 'Mon espace',
                ],
            ],
        ],
        [
            'title' => 'Pilotage',
            'items' => [
                [
                    'route' => 'dg.dashboard',
                    'icon' => 'fas fa-gauge-high',
                    'label' => 'Tableau de bord',
                ],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Espace DG')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased bg-slate-50">
    <div class="flex min-h-screen">
        <nav class="sidebar shadow" id="sidebar" style="width:260px; background:linear-gradient(180deg,#008751 0%,#006837 100%); color:#fff;">
            <div class="sidebar-header p-6 text-center border-b border-white/10">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white text-emerald-700 shadow">
                    <i class="fas fa-user-tie text-2xl"></i>
                </div>
                <h5 class="mt-3 text-xl font-black text-white">Espace DG</h5>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/70">Gestion DG</p>
            </div>
            <!-- Bouton Déconnexion en bas du menu -->
            <div class="mt-auto px-6 pb-8 pt-4">
                <form method="POST" action="{{ route('dg.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-white font-bold hover:bg-red-600 hover:text-white transition">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Se déconnecter</span>
                    </button>
                </form>
            </div>
            <div class="flex flex-1 flex-col mt-1">
                @foreach($menuSections as $section)
                    <div class="sidebar-label px-6 pt-6 pb-2 text-xs font-bold uppercase tracking-widest text-white/60">{{ $section['title'] }}</div>
                    @foreach($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route'].'*');
                            $link = $item['href'] ?? route($item['route']);
                        @endphp
                        <a href="{{ $link }}" class="nav-link flex items-center px-6 py-2 my-1 rounded-lg transition {{ $isActive ? 'bg-white text-emerald-700 font-bold shadow' : 'hover:bg-white/10' }}">
                            <i class="{{ $item['icon'] }} w-6 text-lg"></i>
                            <span class="ml-2">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </nav>
        <main class="main-content flex-1">
            @yield('content')
        </main>
    </div>
</body>
</html>
