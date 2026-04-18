<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Personnel — {{ $entiteNom }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.4;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            border-bottom: 2px solid #008751;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .header-top { display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; font-size: 8px; color: #64748b; }
        .org-name { font-size: 14px; font-weight: 700; color: #008751; letter-spacing: 0.5px; }
        .org-sub { font-size: 8px; color: #64748b; margin-top: 2px; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title { font-size: 13px; font-weight: 700; color: #1e293b; margin-top: 6px; }
        .doc-entite { font-size: 10px; font-weight: 700; color: #008751; margin-top: 2px; }

        /* ── Filtres ── */
        .filter-bar {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 5px 8px;
            margin-bottom: 10px;
            font-size: 8px;
            color: #64748b;
        }
        .filter-bar span { color: #1e293b; font-weight: 700; }

        /* ── Stats ── */
        .stats-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .stats-table td {
            width: 16.66%;
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            text-align: center;
            background: #f8fafc;
        }
        .stat-value { font-size: 15px; font-weight: 700; color: #1e293b; display: block; }
        .stat-label { font-size: 7px; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; display: block; margin-top: 2px; }

        /* ── Table ── */
        table.main { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        table.main thead th {
            background: #008751;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 6px 5px;
            border: 1px solid #007040;
            font-size: 7.5px;
        }
        table.main tbody tr:nth-child(even) { background: #f8fafc; }
        table.main tbody tr:nth-child(odd)  { background: #fff; }
        table.main tbody td {
            padding: 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .rank  { color: #94a3b8; font-weight: 700; text-align: center; }
        .nom   { font-weight: 700; color: #0f172a; }
        .mat   { font-size: 7.5px; color: #94a3b8; }
        .note  { font-weight: 700; font-size: 11px; text-align: right; color: #0f172a; }

        .badge { display: inline-block; padding: 1px 5px; border-radius: 20px; font-size: 7.5px; font-weight: 700; }
        .badge-excellent   { background: #d1fae5; color: #065f46; }
        .badge-bien        { background: #e0f2fe; color: #0c4a6e; }
        .badge-passable    { background: #fef3c7; color: #92400e; }
        .badge-insuffisant { background: #fee2e2; color: #991b1b; }
        .badge-valide      { background: #d1fae5; color: #065f46; }
        .badge-soumis      { background: #fef3c7; color: #92400e; }
        .badge-refuse      { background: #fee2e2; color: #991b1b; }

        .bar-wrap { background: #e2e8f0; border-radius: 3px; height: 4px; margin-top: 2px; }
        .bar-fill  { height: 4px; border-radius: 3px; }
        .bar-e { background: #10b981; }
        .bar-b { background: #0ea5e9; }
        .bar-p { background: #f59e0b; }
        .bar-i { background: #ef4444; }

        /* ── Footer ── */
        .footer {
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="org-name">RCPB — Réseau des Caisses Populaires du Burkina</div>
                <div class="org-sub">Direction Générale · Système de Gestion de la Performance</div>
                <div class="doc-title">Liste du personnel évalué</div>
                <div class="doc-entite">{{ $entiteLabel }} : {{ $entiteNom }}</div>
            </div>
            <div class="header-right">
                Généré le {{ now()->format('d/m/Y à H:i') }}<br>
                Confidentiel — usage interne
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    @php
        $hasFilters = $filters['search'] || $filters['appreciation'] || $filters['statut'];
    @endphp
    <div class="filter-bar">
        @if ($hasFilters)
            Filtres appliqués :
            @if ($filters['search'])       Recherche : <span>{{ $filters['search'] }}</span>  @endif
            @if ($filters['appreciation']) Appréciation : <span>{{ ucfirst($filters['appreciation']) }}</span>  @endif
            @if ($filters['statut'])       Statut : <span>{{ ucfirst($filters['statut']) }}</span>@endif
        @else
            Aucun filtre appliqué — liste complète de {{ $entiteLabel }} {{ $entiteNom }}
        @endif
    </div>

    {{-- Stats --}}
    <table class="stats-table">
        <tr>
            <td>
                <span class="stat-value">{{ $stats['total'] }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td style="background:#f8fafc;">
                <span class="stat-value" style="color:#008751;">{{ number_format($stats['moyenne'], 2, ',', ' ') }}</span>
                <span class="stat-label">Moyenne /10</span>
            </td>
            <td style="background:#d1fae5;">
                <span class="stat-value" style="color:#065f46;">{{ $stats['excellent'] }}</span>
                <span class="stat-label">Excellent</span>
            </td>
            <td style="background:#e0f2fe;">
                <span class="stat-value" style="color:#0c4a6e;">{{ $stats['bien'] }}</span>
                <span class="stat-label">Bien</span>
            </td>
            <td style="background:#fef3c7;">
                <span class="stat-value" style="color:#92400e;">{{ $stats['passable'] }}</span>
                <span class="stat-label">Passable</span>
            </td>
            <td style="background:#fee2e2;">
                <span class="stat-value" style="color:#991b1b;">{{ $stats['insuffisant'] }}</span>
                <span class="stat-label">Insuffisant</span>
            </td>
        </tr>
    </table>

    {{-- Tableau --}}
    <table class="main">
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:20%">Nom complet</th>
                <th style="width:15%">Emploi / Poste</th>
                <th style="width:9%">Période</th>
                <th style="width:10%; text-align:right;">Note /10</th>
                <th style="width:9%">Appréciation</th>
                <th style="width:8%">Statut</th>
                <th style="width:16%">Évaluateur</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($evaluations as $i => $eval)
                @php
                    $ident = $eval->identification;
                    $note  = (float) $eval->note_finale;

                    if ($note >= 8.5)   { $appLabel = 'Excellent';   $appClass = 'badge-excellent';   $barClass = 'bar-e'; }
                    elseif ($note >= 7) { $appLabel = 'Bien';        $appClass = 'badge-bien';        $barClass = 'bar-b'; }
                    elseif ($note >= 5) { $appLabel = 'Passable';    $appClass = 'badge-passable';    $barClass = 'bar-p'; }
                    else                { $appLabel = 'Insuffisant'; $appClass = 'badge-insuffisant'; $barClass = 'bar-i'; }

                    $statClass = match($eval->statut) {
                        'valide' => 'badge-valide',
                        'soumis' => 'badge-soumis',
                        'refuse' => 'badge-refuse',
                        default  => '',
                    };
                    $statLabel = match($eval->statut) {
                        'valide' => 'Validée',
                        'soumis' => 'Soumise',
                        'refuse' => 'Refusée',
                        default  => ucfirst($eval->statut),
                    };

                    $semLabel   = $ident?->semestre ? 'S'.$ident->semestre.' ' : '';
                    $anneeLabel = $ident?->date_evaluation?->format('Y') ?? $eval->date_debut?->format('Y') ?? '—';
                    $periode    = $semLabel.$anneeLabel;
                    $pct        = $note > 0 ? min(100, $note * 10) : 0;
                @endphp
                <tr>
                    <td class="rank">{{ $i + 1 }}</td>
                    <td>
                        <div class="nom">{{ $ident?->nom_prenom ?? '—' }}</div>
                        @if ($ident?->matricule)
                            <div class="mat">Mat. {{ $ident->matricule }}</div>
                        @endif
                    </td>
                    <td>{{ $ident?->emploi ?? '—' }}</td>
                    <td style="white-space:nowrap;">{{ $periode }}</td>
                    <td style="text-align:right;">
                        <span class="note">{{ number_format($note, 2, ',', ' ') }}</span>
                        <div class="bar-wrap">
                            <div class="bar-fill {{ $barClass }}" style="width:{{ $pct }}%;"></div>
                        </div>
                    </td>
                    <td><span class="badge {{ $appClass }}">{{ $appLabel }}</span></td>
                    <td><span class="badge {{ $statClass }}">{{ $statLabel }}</span></td>
                    <td>{{ $eval->evaluateur?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:16px; color:#94a3b8;">
                        Aucun résultat pour ces critères.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">RCPB — {{ $entiteLabel }} : {{ $entiteNom }}</div>
        <div class="footer-right">{{ $stats['total'] }} agent(s) · Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

</body>
</html>
