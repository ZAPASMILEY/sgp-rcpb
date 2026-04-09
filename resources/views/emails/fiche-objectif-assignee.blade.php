<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle fiche d'objectifs</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background-color: #f1f5f9; padding: 40px 16px; }
        .wrapper { max-width: 600px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #064e3b, #059669); padding: 36px 40px 28px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; }
        .body { padding: 32px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #111827; margin-bottom: 12px; }
        .intro { font-size: 14px; color: #6b7280; line-height: 1.7; margin-bottom: 24px; }
        .fiche-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px; }
        .fiche-title { font-size: 15px; font-weight: 700; color: #059669; margin-bottom: 8px; }
        .fiche-detail { font-size: 13px; color: #374151; margin-bottom: 4px; }
        .footer { padding: 20px 40px; border-top: 1px solid #f3f4f6; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>Nouvelle fiche d'objectifs</h1>
            </div>
            <div class="body">
                <p class="greeting">Bonjour, {{ $recipientName }} 👋</p>
                <p class="intro">
                    Une nouvelle fiche d'objectifs vient de vous être assignée sur la plateforme <strong>SGP RCPB</strong>.
                </p>
                <div class="fiche-box">
                    <div class="fiche-title">Titre : {{ $fiche->titre }}</div>
                    <div class="fiche-detail">Année : {{ $fiche->annee }}</div>
                    <div class="fiche-detail">Date d'assignation : {{ $fiche->date }}</div>
                    <div class="fiche-detail">Échéance : {{ $fiche->date_echeance }}</div>
                </div>
                <p class="intro">Connectez-vous à la plateforme pour consulter et compléter vos objectifs.</p>
            </div>
            <div class="footer">
                <p>SGP RCPB &copy; {{ date('Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
