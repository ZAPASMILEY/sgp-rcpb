<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche d'objectifs — {{ $fiche->titre }}</title>
    <style>
        @page { margin: 20mm 15mm 18mm 15mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #111; }
        h1 { font-size: 16px; font-weight: bold; margin: 0 0 4px 0; }
        h2 { font-size: 12px; font-weight: bold; margin: 18px 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .header { border-bottom: 2px solid #1e293b; padding-bottom: 12px; margin-bottom: 16px; }
        .subtitle { color: #6b7280; font-size: 10px; margin-top: 2px; }
        .meta-grid { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-grid td { padding: 5px 8px; vertical-align: top; width: 25%; }
        .meta-label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #6b7280; margin-bottom: 2px; display: block; }
        .meta-value { font-size: 11px; font-weight: bold; color: #111; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 9px; font-weight: bold; }
        .badge-green { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .badge-red   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-amber { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .progress-bar-wrap { background: #e5e7eb; border-radius: 4px; height: 8px; width: 120px; display: inline-block; vertical-align: middle; overflow: hidden; }
        .progress-bar-fill { height: 100%; border-radius: 4px; background: #10b981; }
        .obj-list { margin: 0; padding: 0; list-style: none; }
        .obj-item { padding: 7px 10px; border-left: 3px solid #10b981; margin-bottom: 6px; background: #f8fafc; font-size: 11px; }
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        .signature-table td { width: 50%; vertical-align: top; text-align: center; padding: 0 16px; }
        .signature-space { height: 60px; border-bottom: 1px solid #9ca3af; margin-bottom: 4px; }
        .sig-name { font-weight: bold; font-size: 10px; }
        .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fiche d'objectifs</h1>
        <div class="subtitle">{{ config('app.name', 'SGP-RCPB') }}</div>
    </div>

    <table class="meta-grid">
        <tr>
            <td>
                <span class="meta-label">Titre</span>
                <span class="meta-value">{{ $fiche->titre }}</span>
            </td>
            <td>
                <span class="meta-label">Annee</span>
                <span class="meta-value">{{ $fiche->annee }}</span>
            </td>
            <td>
                <span class="meta-label">Assignee a</span>
                <span class="meta-value">{{ $assigneNom }}</span>
            </td>
            <td>
                <span class="meta-label">Statut</span>
                @php
                    $statut = $fiche->statut ?? 'en_attente';
                    $badgeClass = match($statut) { 'acceptee' => 'badge-green', 'refusee' => 'badge-red', default => 'badge-amber' };
                    $statLabel  = match($statut) { 'acceptee' => 'Acceptee', 'refusee' => 'Refusee', default => 'En attente' };
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $statLabel }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Date d'assignation</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</span>
            </td>
            <td>
                <span class="meta-label">Echeance</span>
                <span class="meta-value">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</span>
            </td>
            <td colspan="2">
                <span class="meta-label">Avancement</span>
                @php $pct = (int)($fiche->avancement_percentage ?? 0); @endphp
                <span class="meta-value">{{ $pct }}%</span>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width:{{ $pct }}%"></div>
                </div>
            </td>
        </tr>
    </table>

    <h2>Objectifs</h2>
    <ul class="obj-list">
        @foreach($fiche->objectifs as $objectif)
            <li class="obj-item">{{ $objectif->description }}</li>
        @endforeach
    </ul>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-space"></div>
                <div class="sig-name">{{ $assigneNom }}</div>
                <div style="font-size:9px; color:#6b7280;">{{ $assigneRole }}</div>
            </td>
            <td>
                <div class="signature-space"></div>
                <div class="sig-name">{{ $assigneurNom }}</div>
                <div style="font-size:9px; color:#6b7280;">{{ $assigneurRole }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">Document genere le {{ now()->format('d/m/Y') }}</div>
</body>
</html>
