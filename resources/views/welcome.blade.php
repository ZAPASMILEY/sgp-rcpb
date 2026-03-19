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

        body {
            background-color: #050e0a;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Glowing orb background */
        .hero-glow {
            position: absolute;
            top: -10%;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 500px;
            background: radial-gradient(ellipse at center, rgba(16,185,129,0.18) 0%, rgba(5,150,105,0.08) 45%, transparent 70%);
            pointer-events: none;
        }

        .hero-glow-2 {
            position: absolute;
            top: 30%;
            left: 20%;
            width: 500px;
            height: 400px;
            background: radial-gradient(ellipse at center, rgba(52,211,153,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .grid-bg {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(16,185,129,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(16,185,129,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* Title gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #34d399 0%, #10b981 40%, #6ee7b7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Card hover */
        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(52,211,153,0.12);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }
        .feature-card:hover {
            background: rgba(16,185,129,0.06);
            border-color: rgba(52,211,153,0.3);
            transform: translateY(-2px);
        }

        /* Login button */
        .btn-login {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            font-weight: 600;
            border-radius: 50px;
            padding: 0.65rem 1.75rem;
            font-size: 0.875rem;
            letter-spacing: 0.01em;
            transition: all 0.25s;
            box-shadow: 0 0 20px rgba(16,185,129,0.35);
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            position: relative;
            z-index: 30;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #047857, #059669);
            box-shadow: 0 0 30px rgba(16,185,129,0.5);
            transform: translateY(-1px);
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(52,211,153,0.25);
            border-radius: 50px;
            padding: 0.3rem 1rem;
            font-size: 0.75rem;
            color: #6ee7b7;
            font-weight: 500;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Icon bg */
        .icon-wrap {
            width: 48px;
            height: 48px;
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(52,211,153,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        /* Divider */
        .divider-glow {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(52,211,153,0.3), transparent);
        }
    </style>
</head>
<body>

    <div class="relative min-h-screen flex flex-col">

        <!-- Background effects -->
        <div class="hero-glow"></div>
        <div class="hero-glow-2"></div>
        <div class="grid-bg"></div>

        <!-- NAVBAR -->
        <nav class="relative z-10 flex items-center justify-between px-6 py-5 sm:px-10 lg:px-16">
            <div class="flex items-center gap-3">
                <!-- Logo / icon -->
                <div style="width:40px;height:40px;background:linear-gradient(135deg,#059669,#34d399);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V17L12 22L21 17V7L12 2Z" stroke="white" stroke-width="2" stroke-linejoin="round" fill="rgba(255,255,255,0.15)"/>
                        <path d="M12 8V16M8 10.5V13.5M16 10.5V13.5" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <span style="font-size:1.05rem;font-weight:700;color:#f0fdf4;letter-spacing:-0.01em;">SGP <span style="color:#34d399;">RCPB</span></span>
            </div>

            <a href="{{ url('/admin/login') }}" class="btn-login" onclick="window.location.assign('{{ url('/admin/login') }}')">
                Se connecter
            </a>
        </nav>

        <!-- HERO -->
        <main class="relative z-10 flex flex-col items-center justify-center flex-1 px-6 pt-16 pb-12 sm:px-10 text-center">

            <span class="badge mb-6">
                <span style="width:6px;height:6px;background:#34d399;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                Systeme de gestion de la performance
            </span>

            <h1 style="font-size:clamp(2.2rem,5vw,3.8rem);font-weight:800;line-height:1.1;letter-spacing:-0.03em;color:#f0fdf4;max-width:820px;margin-bottom:1.5rem;">
                Pilotez la performance<br>
                <span class="gradient-text">du reseau RCPB</span>
            </h1>

            <p style="font-size:1.05rem;color:#6b7280;max-width:560px;line-height:1.7;margin-bottom:2.5rem;">
                Suivez les objectifs, gerez les evaluations et coordonnez les entites du reseau en toute simplicite depuis un tableau de bord centralise.
            </p>

            <a href="{{ url('/admin/login') }}" class="btn-login" style="padding:0.85rem 2.5rem;font-size:1rem;" onclick="window.location.assign('{{ url('/admin/login') }}')">
                Acceder a mon espace ->
            </a>

        </main>

        <!-- DIVIDER -->
        <div class="relative z-10 px-6 sm:px-10 lg:px-16">
            <div class="divider-glow"></div>
        </div>

        <!-- FEATURE CARDS -->
        <section class="relative z-10 px-6 py-12 sm:px-10 lg:px-16">
            <div style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;">

                <!-- Card 1 -->
                <div class="feature-card">
                    <div class="icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M3 3H21V21H3z" stroke="none"/>
                            <path d="M9 17H3v-4h6v4zm6 0h-6V7h6v10zm6 0h-6v-7h6v7z" fill="#34d399"/>
                        </svg>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:#f0fdf4;margin-bottom:0.4rem;">Suivi des Objectifs</h3>
                    <p style="font-size:0.82rem;color:#6b7280;line-height:1.6;">Definissez et suivez l'avancement des objectifs par entite, direction et service.</p>
                </div>

                <!-- Card 2 -->
                <div class="feature-card">
                    <div class="icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="3" width="18" height="18" rx="3" stroke="#34d399" stroke-width="1.8" fill="none"/>
                            <path d="M8 12l2.5 2.5L16 9" stroke="#34d399" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:#f0fdf4;margin-bottom:0.4rem;">Gestion des Evaluations</h3>
                    <p style="font-size:0.82rem;color:#6b7280;line-height:1.6;">Creez, soumettez et validez les evaluations du personnel avec export PDF integre.</p>
                </div>

                <!-- Card 3 -->
                <div class="feature-card">
                    <div class="icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <circle cx="9" cy="7" r="3" stroke="#34d399" stroke-width="1.8" fill="none"/>
                            <circle cx="15" cy="7" r="3" stroke="#34d399" stroke-width="1.8" fill="none"/>
                            <path d="M3 19c0-3.3 2.7-6 6-6h6c3.3 0 6 2.7 6 6" stroke="#34d399" stroke-width="1.8" stroke-linecap="round" fill="none"/>
                        </svg>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:#f0fdf4;margin-bottom:0.4rem;">Gestion du Personnel</h3>
                    <p style="font-size:0.82rem;color:#6b7280;line-height:1.6;">Gerez agents, directeurs et chefs de service de toutes les entites du reseau.</p>
                </div>

                <!-- Card 4 -->
                <div class="feature-card">
                    <div class="icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L3 7V17L12 22L21 17V7L12 2Z" stroke="#34d399" stroke-width="1.8" stroke-linejoin="round" fill="none"/>
                            <path d="M12 8V16M8.5 10V14M15.5 10V14" stroke="#34d399" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h3 style="font-size:0.95rem;font-weight:600;color:#f0fdf4;margin-bottom:0.4rem;">Tableau de Bord PCA</h3>
                    <p style="font-size:0.82rem;color:#6b7280;line-height:1.6;">Espace dedie aux PCA pour visualiser et piloter la performance de leur entite.</p>
                </div>

            </div>
        </section>

        <!-- FOOTER -->
        <footer class="relative z-10 px-6 py-5 sm:px-10 text-center">
            <p style="font-size:0.78rem;color:#374151;">
                &copy; {{ date('Y') }} SGP RCPB &mdash; Reseau des Caisses Populaires du Burkina
            </p>
        </footer>

    </div>

</body>
</html>
