<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte SGP-RCPB</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', Arial, sans-serif; background-color: #f1f5f9; padding: 40px 16px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header-critique { background: linear-gradient(135deg, #991b1b, #dc2626); }
        .header-haute { background: linear-gradient(135deg, #9a3412, #ea580c); }
        .header-moyenne { background: linear-gradient(135deg, #854d0e, #d97706); }
        .header-basse { background: linear-gradient(135deg, #334155, #64748b); }
        .header { padding: 36px 40px 28px; text-align: center; }
        .header .icon-box { width: 52px; height: 52px; background: rgba(255,255,255,0.18); border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
        .header .badge { display: inline-block; background: rgba(255,255,255,0.2); color: #fff; font-size: 11px; font-weight: 600; padding: 3px 14px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 10px; }
        .body { padding: 32px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #111827; margin-bottom: 12px; }
        .intro { font-size: 14px; color: #6b7280; line-height: 1.7; margin-bottom: 24px; }
        .alert-box { border-radius: 12px; padding: 24px 28px; margin-bottom: 24px; }
        .alert-box-critique { background: #fef2f2; border: 1px solid #fecaca; }
        .alert-box-haute { background: #fff7ed; border: 1px solid #fed7aa; }
        .alert-box-moyenne { background: #fffbeb; border: 1px solid #fde68a; }
        .alert-box-basse { background: #f8fafc; border: 1px solid #e2e8f0; }
        .alert-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 10px; }
        .alert-label-critique { color: #dc2626; }
        .alert-label-haute { color: #ea580c; }
        .alert-label-moyenne { color: #d97706; }
        .alert-label-basse { color: #64748b; }
        .alert-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 8px; }
        .alert-message { font-size: 14px; color: #4b5563; line-height: 1.7; }
        .meta-row { display: flex; gap: 24px; margin-bottom: 24px; }
        .meta-item { flex: 1; }
        .meta-label { font-size: 11px; color: #9ca3af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .meta-value { font-size: 13px; font-weight: 600; color: #374151; }
        .footer { padding: 20px 40px; border-top: 1px solid #f3f4f6; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header header-{{ $alerte->priorite }}">
                <div class="icon-box">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L3 7V17L12 22L21 17V7L12 2Z" stroke="white" stroke-width="2" stroke-linejoin="round" fill="rgba(255,255,255,0.2)"/>
                        <path d="M12 8V13M12 16H12.01" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1>Alerte SGP-RCPB</h1>
                <span class="badge">Priorité {{ ucfirst($alerte->priorite) }}</span>
            </div>

            <div class="body">
                <p class="greeting">Bonjour, {{ $recipientName }}</p>
                <p class="intro">
                    Une nouvelle alerte a été diffusée sur la plateforme <strong>SGP-RCPB</strong>.
                    Veuillez prendre connaissance des détails ci-dessous.
                </p>

                <div class="alert-box alert-box-{{ $alerte->priorite }}">
                    <p class="alert-label alert-label-{{ $alerte->priorite }}">Détails de l'alerte</p>
                    <p class="alert-title">{{ $alerte->titre }}</p>
                    @if($alerte->message)
                        <p class="alert-message">{{ $alerte->message }}</p>
                    @endif
                </div>

                <div class="meta-row">
                    <div class="meta-item">
                        <p class="meta-label">Priorité</p>
                        <p class="meta-value">{{ ucfirst($alerte->priorite) }}</p>
                    </div>
                    <div class="meta-item">
                        <p class="meta-label">Date</p>
                        <p class="meta-value">{{ $alerte->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div class="meta-item">
                        <p class="meta-label">Émetteur</p>
                        <p class="meta-value">{{ $alerte->createur?->name ?? 'Système' }}</p>
                    </div>
                </div>
            </div>

            <div class="footer">
                <p>Ce message a été envoyé automatiquement par <strong>SGP-RCPB</strong>.<br>Ne répondez pas à cet email.</p>
            </div>
        </div>
    </div>
</body>
</html>
