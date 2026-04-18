<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Personnel RCPB</title>
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
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .org-name {
            font-size: 15px;
            font-weight: 700;
            color: #008751;
            letter-spacing: 0.5px;
        }
        .org-sub {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-meta {
            text-align: right;
            font-size: 8px;
            color: #64748b;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 8px;
        }

        /* ── Filtres appliqués ── */
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
        .stats-row {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .stat-cell {
            display: table-cell;
            width: 16.66%;
            border: 1px solid #e2e8f0;
            border-radius: 0;
            padding: 6px 8px;
            text-align: center;
            background: #f8fafc;
        }
        .stat-cell:first-child { border-radius: 4px 0 0 4px; }
        .stat-cell:last-child  { border-radius: 0 4px 4px 0; }
        .stat-value {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            display: block;
        }
        .stat-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            display: block;
            margin-top: 2px;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        thead th {
            background: #008751;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 6px 5px;
            border: 1px solid #007040;
            font-size: 7.5px;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #fff; }
        tbody td {
            padding: 5px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .rank { color: #94a3b8; font-weight: 700; text-align: center; }
        .nom  { font-weight: 700; color: #0f172a; }
        .mat  { font-size: 7.5px; color: #94a3b8; }
        .note { font-weight: 700; font-size: 11px; text-align: right; color: #0f172a; }

        /* appréciation badges */
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 20px;
            font-size: 7.5px;
            font-weight: 700;
        }
        .badge-excellent   { background: #d1fae5; color: #065f46; }
        .badge-bien        { background: #e0f2fe; color: #0c4a6e; }
        .badge-passable    { background: #fef3c7; color: #92400e; }
        .badge-insuffisant { background: #fee2e2; color: #991b1b; }
        .badge-valide      { background: #d1fae5; color: #065f46; }
        .badge-soumis      { background: #fef3c7; color: #92400e; }
        .badge-refuse      { background: #fee2e2; color: #991b1b; }

        /* note bar */
        .bar-wrap { background: #e2e8f0; border-radius: 3px; height: 4px; margin-top: 2px; }
        .bar-fill  { height: 4px; border-radius: 3px; }
        .bar-excellent   { background: #10b981; }
        .bar-bien        { background: #0ea5e9; }
        .bar-passable    { background: #f59e0b; }
        .bar-insuffisant { background: #ef4444; }

        /* ── Footer ── */
        .footer {
            margin-top: 14px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="org-name">RCPB — Réseau des Caisses Populaires du Burkina</div>
                <div class="org-sub">Direction Générale · Système de Gestion de la Performance</div>
            </div>
            <div class="doc-meta">
                Généré le {{ now()->format('d/m/Y à H:i') }}<br>
                Confidentiel — usage interne
            </div>
        </div>
        <div class="doc-title">Liste du personnel évalué — Réseau RCPB</div>
    </div>

    {{-- Filtres appliqués --}}
    @php
        $hasFilters = $filters['search'] || $filters['anneeId'] || $filters['semestre']
                   || $filters['appreciation'] || $filters['statut']
                   || $filters['emploi'] || $filters['structure'];
    @endphp
    <div class="filter-bar">
        @if ($hasFilters)
            Filtres appliqués :
            @if ($filters['search'])       Recherche : <span>{{ $filters['search'] }}</span> · @endif
            @if ($filters['emploi'])       Emploi : <span>{{ $filters['emploi'] }}</span> · @endif
            @if ($filters['structure'])    Structure : <span>{{ $filters['structure'] }}</span> · @endif
            @if ($filters['semestre'])     Semestre : <span>{{ $filters['semestre'] }}</span> · @endif
            @if ($filters['appreciation']) Appréciation : <span>{{ ucfirst($filters['appreciation']) }}</span> · @endif
            @if ($filters['statut'])       Statut : <span>{{ ucfirst($filters['statut']) }}</span> · @endif
            @if ($filters['anneeId'])      Année ID : <span>{{ $filters['anneeId'] }}</span>@endif
        @else
            Aucun filtre appliqué — liste complète du réseau
        @endif
    </div>

    {{-- Stats ── --}}
    <table class="stats-row" style="margin-bottom:12px;">
        <tr>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#f8fafc;">
                <span class="stat-value">{{ $stats['total'] }}</span>
                <span class="stat-label">Total</span>
            </td>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#f8fafc;">
                <span class="stat-value" style="color:#008751;">{{ number_format($stats['moyenne'], 2, ',', ' ') }}</span>
                <span class="stat-label">Moyenne /10</span>
            </td>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#d1fae5;">
                <span class="stat-value" style="color:#065f46;">{{ $stats['excellent'] }}</span>
                <span class="stat-label">Excellent</span>
            </td>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#e0f2fe;">
                <span class="stat-value" style="color:#0c4a6e;">{{ $stats['bien'] }}</span>
                <span class="stat-label">Bien</span>
            </td>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#fef3c7;">
                <span class="stat-value" style="color:#92400e;">{{ $stats['passable'] }}</span>
                <span class="stat-label">Passable</span>
            </td>
            <td style="width:16.66%; border:1px solid #e2e8f0; padding:6px 8px; text-align:center; background:#fee2e2;">
                <span class="stat-value" style="color:#991b1b;">{{ $stats['insuffisant'] }}</span>
                <span class="stat-label">Insuffisant</span>
            </td>
        </tr>
    </table>

    {{-- Tableau ── --}}
    <table>
        <thead>
            <tr>
                <th style="width:3%">#</th>
                <th style="width:18%">Nom complet</th>
                <th style="width:14%">Emploi / Poste</th>
                <th style="width:14%">Direction / Structure</th>
                <th style="width:8%">Période</th>
                <th style="width:9%; text-align:right;">Note /10</th>
                <th style="width:9%">Appréciation</th>
                <th style="width:7%">Statut</th>
                <th style="width:14%">Évaluateur</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($evaluations as $i => $eval)
                @php
                    $ident = $eval->identification;
                    $note  = (float) $eval->note_finale;

                    if ($note >= 8.5)      { $appLabel = 'Excellent';   $appClass = 'badge-excellent';   $barClass = 'bar-excellent'; }
                    elseif ($note >= 7)    { $appLabel = 'Bien';        $appClass = 'badge-bien';        $barClass = 'bar-bien'; }
                    elseif ($note >= 5)    { $appLabel = 'Passable';    $appClass = 'badge-passable';    $barClass = 'bar-passable'; }
                    else                   { $appLabel = 'Insuffisant'; $appClass = 'badge-insuffisant'; $barClass = 'bar-insuffisant'; }

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
                    <td>{{ $ident?->emploi ?? $eval->evaluable_role ?? '—' }}</td>
                    <td>
                        {{ $ident?->direction ?? '—' }}
                        @if ($ident?->direction_service && $ident->direction_service !== $ident->direction)
                            <br><span style="color:#94a3b8; font-size:7.5px;">{{ $ident->direction_service }}</span>
                        @endif
                    </td>
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
                    <td colspan="9" style="text-align:center; padding:16px; color:#94a3b8;">
                        Aucun résultat pour ces critères de filtrage.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer ── --}}
    <div class="footer">
        <span>RCPB — Système de Gestion de la Performance</span>
        <span>{{ $stats['total'] }} agent(s) · Généré le {{ now()->format('d/m/Y à H:i') }}</span>
    </div>

</body>
</html>
