<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur SGP RCPB</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background-color: #f1f5f9; padding: 40px 16px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #064e3b, #059669); padding: 40px 40px 32px; text-align: center; }
        .logo-box { width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.75); font-size: 13px; margin-top: 6px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 14px; }
        .intro { font-size: 14px; color: #6b7280; line-height: 1.7; margin-bottom: 28px; }
        .creds-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px 28px; margin-bottom: 28px; }
        .creds-box h2 { font-size: 12px; font-weight: 600; color: #059669; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; }
        .cred-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px; }
        .cred-row:last-child { margin-bottom: 0; }
        .cred-label { font-size: 12px; color: #9ca3af; min-width: 100px; padding-top: 2px; }
        .cred-value { font-size: 14px; font-weight: 600; color: #111827; word-break: break-all; }
        .cred-value.password { font-family: 'Courier New', monospace; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 6px; padding: 3px 10px; font-size: 15px; color: #065f46; letter-spacing: 0.05em; }
        .btn-wrap { text-align: center; margin-bottom: 28px; }
        .btn-login { display: inline-block; background: linear-gradient(135deg, #059669, #10b981); color: #ffffff; font-size: 15px; font-weight: 600; padding: 14px 40px; border-radius: 50px; text-decoration: none; letter-spacing: 0.01em; box-shadow: 0 4px 14px rgba(16,185,129,0.4); }
        .warning { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #92400e; line-height: 1.6; margin-bottom: 28px; }
        .warning strong { color: #78350f; }
        .footer { padding: 24px 40px; border-top: 1px solid #f3f4f6; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .role-badge { display: inline-block; background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 600; padding: 3px 12px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <!-- HEADER -->
            <div class="header">
                <div class="logo-box">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V17L12 22L21 17V7L12 2Z" stroke="white" stroke-width="2" stroke-linejoin="round" fill="rgba(255,255,255,0.2)"/>
                        <path d="M12 8V16M8 10.5V13.5M16 10.5V13.5" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1>SGP RCPB</h1>
                <p>Système de Gestion de la Performance</p>
            </div>

            <!-- BODY -->
            <div class="body">
                <p class="greeting">Bonjour, {{ $recipientName }} 👋</p>

                <p class="intro">
                    Votre compte a été créé sur la plateforme <strong>SGP RCPB</strong>.
                    Vous trouverez ci-dessous vos identifiants de connexion.
                    Veuillez les conserver en lieu sûr.
                </p>

                <!-- BADGE ROLE -->
                <p style="margin-bottom:20px;">
                    <span class="role-badge">
                        @php
                            $roleLabels = [
                                'agent'     => 'Agent',
                                'directeur' => 'Directeur',
                                'chef'      => 'Chef de Service',
                                'pca'       => 'PCA',
                                'admin'     => 'Administrateur',
                            ];
                        @endphp
                        {{ $roleLabels[$role] ?? ucfirst($role) }}
                    </span>
                </p>

                <!-- CREDENTIALS BOX -->
                <div class="creds-box">
                    <h2>Vos identifiants</h2>

                    <div class="cred-row">
                        <span class="cred-label">Adresse e-mail</span>
                        <span class="cred-value">{{ $recipientEmail }}</span>
                    </div>

                    <div class="cred-row">
                        <span class="cred-label">Mot de passe</span>
                        <span class="cred-value password">{{ $plainPassword }}</span>
                    </div>
                </div>

                <!-- LOGIN BUTTON -->
                <div class="btn-wrap">
                    <a href="{{ $loginUrl }}" class="btn-login">
                        Accéder à mon espace →
                    </a>
                </div>

                <!-- WARNING -->
                <div class="warning">
                    <strong>⚠ Important :</strong> Pour votre sécurité, nous vous recommandons de changer votre mot de passe dès votre première connexion.
                    Ne partagez jamais vos identifiants avec des tiers.
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <p>
                    Cet e-mail a été envoyé automatiquement par le système SGP RCPB.<br>
                    Si vous pensez avoir reçu cet e-mail par erreur, veuillez contacter votre administrateur.<br>
                    <strong>&copy; {{ date('Y') }} SGP RCPB</strong> — Réseau des Caisses Populaires du Burkina
                </p>
            </div>
        </div>
    </div>
</body>
</html>
