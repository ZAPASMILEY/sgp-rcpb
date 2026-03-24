<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SGP RCPB') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #050e0a; min-height: 100vh; overflow-x: hidden; color: #f0fdf4; }

        /* Effets de fond */
        .hero-glow {
            position: absolute; top: -10%; left: 50%; transform: translateX(-50%);
            width: 800px; height: 500px;
            background: radial-gradient(ellipse at center, rgba(16,185,129,0.18) 0%, rgba(5,150,105,0.08) 45%, transparent 70%);
            pointer-events: none;
        }
        .grid-bg {
            position: absolute; inset: 0;
            background-image: linear-gradient(rgba(16,185,129,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(16,185,129,0.04) 1px, transparent 1px);
            background-size: 60px 60px; pointer-events: none;
        }

        .gradient-text {
            background: linear-gradient(135deg, #34d399 0%, #10b981 40%, #6ee7b7 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        .feature-card {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(52,211,153,0.12);
            border-radius: 24px; padding: 2rem; transition: all 0.3s ease; backdrop-filter: blur(4px);
        }
        .feature-card:hover {
            background: rgba(16,185,129,0.06); border-color: rgba(52,211,153,0.3); transform: translateY(-5px);
        }

        .btn-sgp {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white; font-weight: 800; border-radius: 50px;
            padding: 1rem 2.5rem; transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-sgp:hover {
            transform: scale(1.05); box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
            filter: brightness(1.1);
        }

        .badge {
            background: rgba(16,185,129,0.1); border: 1px solid rgba(52,211,153,0.25);
            border-radius: 50px; padding: 0.5rem 1.2rem; font-size: 0.75rem;
            color: #6ee7b7; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
        }

        .divider-glow {
            height: 1px; width: 100%;
            background: linear-gradient(90deg, transparent, rgba(52,211,153,0.3), transparent);
        }
    </style>
</head>
<body>

    <div class="relative min-h-screen flex flex-col">
        <div class="hero-glow"></div>
        <div class="grid-bg"></div>

        <nav class="relative z-50 flex items-center justify-between px-6 py-8 sm:px-16">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-[#059669] to-[#34d399] rounded-xl flex items-center justify-center shadow-lg">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5">
                        <path d="M12 2L3 7V17L12 22L21 17V7L12 2Z" />
                    </svg>
                </div>
                <span class="text-xl font-black tracking-tighter">SGP <span class="text-[#34d399]">RCPB</span></span>
            </div>

            <a href="{{ route('login') }}" class="hidden sm:flex font-bold text-sm hover:text-[#34d399] transition-colors mr-8">Se connecter</a>
            <a href="{{ route('login') }}" class="btn-sgp text-sm py-3 px-8">Accès direct</a>
        </nav>

        <main class="relative z-10 flex flex-col items-center justify-center flex-1 px-6 text-center py-20">
            <span class="badge mb-8 flex items-center gap-2">
                <span class="w-2 h-2 bg-[#34d399] rounded-full animate-pulse"></span>
                Système de gestion de la performance
            </span>

            <h1 class="text-5xl sm:text-7xl font-black leading-none tracking-tight mb-8">
                Pilotez la performance<br>
                <span class="gradient-text">du réseau RCPB</span>
            </h1>

            <p class="text-gray-400 text-lg max-w-2xl mb-12 leading-relaxed">
                Suivez les objectifs, gérez les évaluations et coordonnez les entités du réseau en toute simplicité depuis un tableau de bord centralisé.
            </p>

            <a href="{{ auth()->check() ? url('/admin/dashboard') : route('login') }}" class="btn-sgp text-lg">
                Accéder à mon espace &nbsp; →
            </a>
        </main>

        <div class="px-16"><div class="divider-glow"></div></div>

        <section class="relative z-10 px-6 py-24 sm:px-16 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="feature-card">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 border border-emerald-500/20">
                        📊
                    </div>
                    <h3 class="font-bold mb-3">Suivi des Objectifs</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Définissez et suivez l'avancement des objectifs par entité, direction et service.</p>
                </div>

                <div class="feature-card">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 border border-emerald-500/20">
                        ✅
                    </div>
                    <h3 class="font-bold mb-3">Evaluations</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Créez, soumettez et validez les évaluations du personnel avec export PDF intégré.</p>
                </div>

                <div class="feature-card">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 border border-emerald-500/20">
                        👥
                    </div>
                    <h3 class="font-bold mb-3">Personnel</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Gérez agents, directeurs et chefs de service de toutes les entités du réseau.</p>
                </div>

                <div class="feature-card">
                    <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 border border-emerald-500/20">
                        🏛️
                    </div>
                    <h3 class="font-bold mb-3">Tableau PCA</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Espace dédié aux PCA pour piloter la performance de leur entité en temps réel.</p>
                </div>

            </div>
        </section>

        <footer class="relative z-10 py-10 border-t border-white/5 text-center">
            <p class="text-gray-600 text-xs font-medium">
                © {{ date('Y') }} SGP RCPB — Réseau des Caisses Populaires du Burkina
            </p>
        </footer>
    </div>

</body>
</html>