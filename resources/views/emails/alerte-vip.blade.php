<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Direction — SGP-RCPB</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background-color: #0f172a; padding: 40px 16px; }
        .wrapper { max-width: 620px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.3); }

        /* Gold accent bar */
        .gold-bar { height: 5px; background: linear-gradient(90deg, #b8860b, #daa520, #ffd700, #daa520, #b8860b); }

        .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 44px 44px 36px; text-align: center; position: relative; }
        .header::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 80px; height: 3px; background: linear-gradient(90deg, transparent, #daa520, transparent); }
        .header .shield { width: 64px; height: 64px; background: linear-gradient(135deg, #b8860b, #daa520); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(218,165,32,0.3); }
        .header h1 { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .header .subtitle { color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.12em; margin-top: 6px; }

        /* Role badge */
        .role-section { text-align: center; padding: 28px 44px 0; }
        .role-badge { display: inline-block; background: linear-gradient(135deg, #0f172a, #1e293b); color: #ffd700; font-size: 11px; font-weight: 700; padding: 6px 20px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.1em; border: 1px solid #334155; }

        .body { padding: 28px 44px 36px; }
        .greeting { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 14px; }
        .intro { font-size: 14px; color: #64748b; line-height: 1.8; margin-bottom: 28px; }
        .intro strong { color: #0f172a; }

        /* Alert detail box - dark premium style */
        .alert-box { background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 16px; padding: 28px 32px; margin-bottom: 28px; border: 1px solid #334155; }
        .alert-box .label-row { display: flex; align-items: center; gap: 8px; margin-bottom: 14px; }
        .alert-box .priority-dot { width: 10px; height: 10px; border-radius: 50%; }
        .alert-box .priority-dot.critique { background: #ef4444; box-shadow: 0 0 8px rgba(239,68,68,0.5); }
        .alert-box .priority-dot.haute { background: #f97316; box-shadow: 0 0 8px rgba(249,115,22,0.5); }
        .alert-box .priority-dot.moyenne { background: #eab308; box-shadow: 0 0 8px rgba(234,179,8,0.5); }
        .alert-box .priority-dot.basse { background: #64748b; }
        .alert-box .alert-label { font-size: 11px; font-weight: 700; color: #daa520; text-transform: uppercase; letter-spacing: 0.1em; }
        .alert-box .alert-title { font-size: 17px; font-weight: 800; color: #ffffff; margin-bottom: 10px; line-height: 1.4; }
        .alert-box .alert-message { font-size: 14px; color: #94a3b8; line-height: 1.7; }

        /* Meta grid */
        .meta-grid { display: flex; gap: 16px; margin-bottom: 28px; }
        .meta-card { flex: 1; background: #f8fafc; border-radius: 12px; padding: 16px 18px; border: 1px solid #e2e8f0; }
        .meta-card .mc-label { font-size: 10px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
        .meta-card .mc-value { font-size: 13px; font-weight: 700; color: #0f172a; }

        /* Confidentiality notice */
        .confidential { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px 20px; margin-bottom: 28px; display: flex; align-items: flex-start; gap: 12px; }
        .confidential .conf-icon { width: 20px; min-width: 20px; height: 20px; background: #fbbf24; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #78350f; font-size: 11px; font-weight: 900; }
        .confidential p { font-size: 12px; color: #92400e; line-height: 1.6; }
        .confidential p strong { color: #78350f; }

        .gold-bar-bottom { height: 3px; background: linear-gradient(90deg, #b8860b, #daa520, #ffd700, #daa520, #b8860b); }
        .footer { background: #0f172a; padding: 24px 44px; text-align: center; }
        .footer p { font-size: 11px; color: #475569; line-height: 1.7; }
        .footer .brand { color: #daa520; font-weight: 700; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="gold-bar"></div>

            <!-- HEADER -->
            <div class="header">
                <div class="shield">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V12C3 17.25 6.75 22.13 12 23C17.25 22.13 21 17.25 21 12V7L12 2Z" stroke="white" stroke-width="1.8" fill="rgba(255,255,255,0.15)"/>
                        <path d="M12 8V13M12 16H12.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1>Alerte Direction</h1>
                <p class="subtitle">SGP-RCPB &mdash; Communication prioritaire</p>
            </div>

            <!-- ROLE BADGE -->
            <div class="role-section">
                <span class="role-badge">{{ $recipientRole }}</span>
            </div>

            <!-- BODY -->
            <div class="body">
                <p class="greeting">{{ $recipientRole }}, {{ $recipientName }}</p>
                <p class="intro">
                    En tant que <strong>membre de la direction</strong> de la Faîtière, vous recevez cette alerte en priorité.
                    Veuillez prendre connaissance des détails ci-dessous et agir en conséquence.
                </p>

                <!-- ALERT BOX -->
                <div class="alert-box">
                    <div class="label-row">
                        <span class="priority-dot {{ $alerte->priorite }}"></span>
                        <span class="alert-label">Priorité {{ ucfirst($alerte->priorite) }}</span>
                    </div>
                    <p class="alert-title">{{ $alerte->titre }}</p>
                    @if($alerte->message)
                        <p class="alert-message">{{ $alerte->message }}</p>
                    @endif
                </div>

                <!-- META -->
                <div class="meta-grid">
                    <div class="meta-card">
                        <p class="mc-label">Date & Heure</p>
                        <p class="mc-value">{{ $alerte->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="meta-card">
                        <p class="mc-label">Émetteur</p>
                        <p class="mc-value">{{ $alerte->createur?->name ?? 'Système' }}</p>
                    </div>
                    <div class="meta-card">
                        <p class="mc-label">Adresse IP</p>
                        <p class="mc-value">{{ $alerte->ip_address ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- CONFIDENTIALITY -->
                <div class="confidential">
                    <div class="conf-icon">!</div>
                    <p><strong>Communication réservée à la direction.</strong> Ce message contient des informations internes à la Faîtière du RCPB. Merci de ne pas le transférer en dehors du cadre professionnel.</p>
                </div>
            </div>

            <div class="gold-bar-bottom"></div>
            <div class="footer">
                <p><span class="brand">SGP-RCPB</span> — Système de Gestion de la Performance<br>Ce message a été envoyé automatiquement. Ne répondez pas à cet email.</p>
            </div>
        </div>
    </div>
</body>
</html>
