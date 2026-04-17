@php
    $menuSections = [
        [
            'title' => 'Mon espace',
            'items' => [
                [
                    'route' => 'personnel.dashboard',
                    'icon'  => 'fas fa-house',
                    'label' => 'Tableau de bord',
                ],
            ],
        ],
        [
            'title' => 'Mon dossier',
            'items' => [
                [
                    'route' => 'personnel.dashboard',
                    'icon'  => 'fas fa-star',
                    'label' => 'Mes evaluations',
                    'query' => 'tab=evaluations',
                ],
                [
                    'route' => 'personnel.dashboard',
                    'icon'  => 'fas fa-bullseye',
                    'label' => 'Mes objectifs',
                    'query' => 'tab=objectifs',
                ],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mon Espace')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full antialiased bg-slate-50">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <nav class="sidebar shadow" id="sidebar"
             style="width:260px; background:linear-gradient(180deg,#334155 0%,#1e293b 100%); color:#fff; display:flex; flex-direction:column;">
            <div class="sidebar-header p-6 text-center border-b border-white/10">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/20 bg-white/10 text-slate-200 shadow">
                    <i class="fas fa-user text-2xl"></i>
                </div>
                <h5 class="mt-3 text-lg font-black text-white">Mon Espace</h5>
                <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-widest text-white/60">Personnel</p>
                @if(auth()->check())
                    <p class="mt-2 text-xs font-medium text-white/80 truncate">{{ auth()->user()->name }}</p>
                @endif
            </div>

            <div class="flex flex-1 flex-col mt-2">
                @foreach($menuSections as $section)
                    <div class="px-6 pt-5 pb-1 text-xs font-bold uppercase tracking-widest text-white/50">{{ $section['title'] }}</div>
                    @foreach($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route'].'*');
                            $query    = $item['query'] ?? null;
                            $link     = route($item['route']) . ($query ? '?'.$query : '');
                        @endphp
                        <a href="{{ $link }}"
                           class="nav-link flex items-center px-6 py-2.5 my-0.5 rounded-lg mx-3 transition
                               {{ $isActive ? 'bg-white text-slate-800 font-bold shadow' : 'text-white/80 hover:bg-white/10' }}">
                            <i class="{{ $item['icon'] }} w-5 text-sm"></i>
                            <span class="ml-2 text-sm">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                @endforeach
            </div>

            <div class="px-6 pb-6 pt-4 border-t border-white/10">
                <form method="POST" action="{{ route('personnel.logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm text-white font-semibold hover:bg-rose-600 hover:text-white transition">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                        <span>Se deconnecter</span>
                    </button>
                </form>
            </div>
        </nav>

        {{-- Main content --}}
        <main class="flex-1 overflow-auto">
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
