<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attestation de Formation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

        .page { padding: 40px 50px; }

        /* En-tête institution */
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #008751; padding-bottom: 16px; margin-bottom: 24px; }
        .header-logo { font-size: 22px; font-weight: 900; color: #008751; letter-spacing: -0.5px; }
        .header-sub  { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
        .header-date { font-size: 10px; color: #94a3b8; text-align: right; }

        /* Titre document */
        .doc-title { text-align: center; margin: 24px 0; }
        .doc-title h1 { font-size: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #008751; }
        .doc-title p  { font-size: 10px; color: #94a3b8; margin-top: 4px; }

        /* Section */
        .section { margin-bottom: 20px; }
        .section-title { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }

        /* Cards */
        .info-grid { display: table; width: 100%; border-collapse: collapse; }
        .info-row  { display: table-row; }
        .info-label { display: table-cell; width: 38%; padding: 7px 10px; font-size: 10px; font-weight: 700; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; }
        .info-value { display: table-cell; padding: 7px 10px; font-size: 11px; color: #1e293b; border: 1px solid #e2e8f0; }

        /* Domaine badge */
        .badge { display: inline-block; background: #dbeafe; color: #1d4ed8; font-size: 10px; font-weight: 800; padding: 3px 10px; border-radius: 20px; }

        /* Durée highlight */
        .duree-box { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 8px; padding: 14px 20px; text-align: center; margin: 20px 0; }
        .duree-box .nb  { font-size: 36px; font-weight: 900; color: #15803d; line-height: 1; }
        .duree-box .lbl { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #16a34a; margin-top: 4px; }

        /* Signature */
        .signature-area { margin-top: 40px; display: flex; justify-content: space-between; }
        .sig-block { width: 44%; text-align: center; }
        .sig-block .sig-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; }
        .sig-block .sig-line  { margin-top: 40px; border-top: 1px solid #cbd5e1; padding-top: 6px; font-size: 10px; color: #94a3b8; }

        /* Pied de page */
        .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 10px; text-align: center; font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div>
            <div class="header-logo">SGP — RCPB</div>
            <div class="header-sub">Réseau des Caisses Populaires du Burkina</div>
        </div>
        <div class="header-date">
            Édité le {{ now()->translatedFormat('d F Y') }}<br>
            Réf. : FORM-{{ str_pad($formation->id, 5, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    {{-- Titre --}}
    <div class="doc-title">
        <h1>Attestation de Formation</h1>
        <p>Document généré automatiquement par le Système de Gestion du Personnel</p>
    </div>

    {{-- Infos agent --}}
    <div class="section">
        <div class="section-title">Informations de l'agent</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom & Prénom</div>
                <div class="info-value"><strong>{{ trim(($formation->agent->prenom ?? '') . ' ' . ($formation->agent->nom ?? '')) }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Rôle</div>
                <div class="info-value">{{ $formation->agent->role ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Infos formation --}}
    <div class="section">
        <div class="section-title">Détails de la formation</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Thème</div>
                <div class="info-value"><strong>{{ $formation->theme }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Domaine</div>
                <div class="info-value"><span class="badge">{{ $formation->domaine_label }}</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de début</div>
                <div class="info-value">{{ $formation->date_debut->translatedFormat('d F Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de fin</div>
                <div class="info-value">{{ $formation->date_fin->translatedFormat('d F Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Durée --}}
    <div class="duree-box">
        <div class="nb">{{ $formation->duree_heures }}</div>
        <div class="lbl">Heures de formation</div>
    </div>

    {{-- Signature --}}
    <div class="signature-area">
        <div class="sig-block">
            <div class="sig-label">Le Responsable RH</div>
            <div class="sig-line">{{ $formation->createdBy?->name ?? 'RH RCPB' }}</div>
        </div>
        <div class="sig-block">
            <div class="sig-label">L'Agent</div>
            <div class="sig-line">{{ trim(($formation->agent->prenom ?? '') . ' ' . ($formation->agent->nom ?? '')) }}</div>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        Ce document est généré automatiquement et certifié par le Système de Gestion du Personnel du RCPB.
    </div>

</div>
</body>
</html>
