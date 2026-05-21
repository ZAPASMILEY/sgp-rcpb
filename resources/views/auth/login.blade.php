<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — {{ config('app.name', 'SGP RCPB') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; background: #fff; }

        /* ── Panneau gauche (image) ── */
        .left-panel {
            position: relative;
            width: 58%;
            min-height: 100vh;
            overflow: hidden;
        }
        @media (max-width: 768px) { .left-panel { display: none; } }

        .slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: opacity 1.6s ease;
        }

        .left-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.10) 0%, rgba(0,0,0,0.45) 100%);
            z-index: 1;
        }

        .left-content {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 3rem;
        }

        .left-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.30);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            width: fit-content;
            margin-bottom: 16px;
        }

        .left-title {
            font-size: 2.4rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            margin: 0 0 10px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }

        .left-sub {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.80);
            margin: 0 0 2.5rem;
        }

        /* Dots navigation */
        .dots {
            display: flex;
            gap: 8px;
        }
        .dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.40);
            transition: all 0.4s;
            cursor: pointer;
        }
        .dot.active {
            width: 24px;
            border-radius: 4px;
            background: #fff;
        }

        /* ── Panneau droit (formulaire) ── */
        .right-panel {
            width: 42%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 3.5rem;
            background: #fff;
        }
        @media (max-width: 768px) {
            .right-panel { width: 100%; padding: 2rem 1.5rem; }
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: #008751;
            box-shadow: 0 0 0 3px rgba(0,135,81,0.12);
            background: #fff;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #003d20 0%, #008751 100%);
            color: #fff;
            font-weight: 800;
            font-size: 14px;
            letter-spacing: 0.04em;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            box-shadow: 0 4px 20px rgba(0,135,81,0.30);
        }
        .btn-login:hover { opacity: 0.92; transform: translateY(-1px); }
    </style>
</head>
<body>

    {{-- ── Panneau gauche : slideshow ── --}}
    <div class="left-panel">
        <div class="slide" id="slide-1" style="background-image: url('/images/DG1.jpg');"></div>
        <div class="slide" id="slide-2" style="background-image: url('/images/DG2.jpg'); opacity:0;"></div>
        <div class="slide" id="slide-3" style="background-image: url('/images/DG3.jpg'); opacity:0;"></div>
        <div class="left-overlay"></div>
        <div class="left-content">
            <div class="left-badge">
                <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;display:inline-block;"></span>
                Réseau des Caisses Populaires du Burkina
            </div>
            <h1 class="left-title">Pilotez la<br>performance<br>du réseau RCPB</h1>
            <p class="left-sub">Évaluations, objectifs et suivi centralisés<br>pour toutes les entités du réseau.</p>
            <div class="dots">
                <div class="dot active" id="dot-0"></div>
                <div class="dot" id="dot-1"></div>
                <div class="dot" id="dot-2"></div>
            </div>
        </div>
    </div>

    {{-- ── Panneau droit : formulaire ── --}}
    <div class="right-panel">
        <div style="width:100%; max-width:360px;">

            <div style="text-align:center; margin-bottom:2rem;">
                <img src="/img/logo-fcpb.png" alt="RCPB"
                    style="height:72px;width:72px;border-radius:50%;border:3px solid #e8f5ee;box-shadow:0 4px 16px rgba(0,135,81,0.15);margin-bottom:1rem;">
                <h2 style="font-size:1.6rem;font-weight:900;color:#003d20;margin:0 0 4px;">Connexion</h2>
                <p style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.12em;margin:0;">
                    Système de gestion de la performance
                </p>
            </div>

            @if(session('error'))
                <div style="margin-bottom:1rem;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:13px;font-weight:600;color:#dc2626;">
                    <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>{{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="form-input" placeholder="votre@email.com">
                    @error('email')
                        <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p>
                    @enderror
                </div>
                <div style="margin-bottom:1.75rem;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#374151;margin-bottom:6px;">Mot de passe</label>
                    <input type="password" name="password" required
                        class="form-input" placeholder="••••••••">
                    @error('password')
                        <p style="margin-top:4px;font-size:12px;color:#ef4444;">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Se connecter
                </button>
            </form>

            <p style="margin-top:2.5rem;text-align:center;font-size:11px;color:#cbd5e1;">
                © {{ date('Y') }} SGP RCPB — Tous droits réservés
            </p>
        </div>
    </div>

    <script>
    (function () {
        const slides = [
            document.getElementById('slide-1'),
            document.getElementById('slide-2'),
            document.getElementById('slide-3'),
        ];
        const dots = [
            document.getElementById('dot-0'),
            document.getElementById('dot-1'),
            document.getElementById('dot-2'),
        ];
        let current = 0;
        setInterval(function () {
            slides[current].style.opacity = '0';
            dots[current].classList.remove('active');
            current = (current + 1) % slides.length;
            slides[current].style.opacity = '1';
            dots[current].classList.add('active');
        }, 6000);
    })();
    </script>

</body>
</html>
