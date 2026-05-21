<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Comparaison inter-période — DG</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; padding: 20px 28px; }

    .header { border-bottom: 3px solid #0369a1; padding-bottom: 10px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-title { font-size: 18px; font-weight: 700; color: #0369a1; }
    .header-sub   { font-size: 11px; color: #64748b; margin-top: 2px; }
    .header-date  { font-size: 10px; color: #94a3b8; }

    .period-bar { display: flex; gap: 12px; margin-bottom: 16px; }
    .period-card { flex: 1; padding: 10px 14px; border-radius: 6px; }
    .period-card.p1 { background: #eff6ff; border-left: 4px solid #3b82f6; }
    .period-card.p2 { background: #f0fdf4; border-left: 4px solid #22c55e; }
    .period-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .period-year  { font-size: 20px; font-weight: 900; color: #1e293b; margin-top: 2px; }

    .kpi-row { display: flex; gap: 10px; margin-bottom: 18px; }
    .kpi-card { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; text-align: center; }
    .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; font-weight: 700; }
    .kpi-val   { font-size: 20px; font-weight: 900; color: #1e293b; margin-top: 2px; }
    .kpi-sub   { font-size: 9px; color: #94a3b8; margin-top: 1px; }
    .kpi-delta { font-size: 10px; font-weight: 700; margin-top: 4px; }
    .delta-up   { color: #16a34a; }
    .delta-down { color: #dc2626; }
    .delta-eq   { color: #94a3b8; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    thead th { background: #1e3a5f; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 10px; text-align: left; }
    thead th:not(:first-child) { text-align: center; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
    tbody td:not(:first-child) { text-align: center; font-weight: 600; }

    .section-title { font-size: 12px; font-weight: 800; color: #1e3a5f; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; margin-top: 16px; text-transform: uppercase; letter-spacing: 0.5px; }

    .apprec-bar { display: flex; gap: 10px; margin-bottom: 18px; }
    .apprec-item { flex: 1; border-radius: 6px; padding: 8px 10px; text-align: center; }
    .apprec-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .apprec-val   { font-size: 15px; font-weight: 900; margin-top: 2px; }
    .apprec-item.exc { background: #ecfdf5; color: #065f46; }
    .apprec-item.bien { background: #eff6ff; color: #1d4ed8; }
    .apprec-item.pass { background: #fffbeb; color: #92400e; }
    .apprec-item.insuf { background: #fef2f2; color: #991b1b; }

    .footer { border-top: 1px solid #e2e8f0; margin-top: 20px; padding-top: 8px; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .badge-blue  { background: #dbeafe; color: #1d4ed8; }
    .badge-green { background: #dcfce7; color: #15803d; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-title">Comparaison inter-période — Direction Générale</div>
        <div class="header-sub">
            Période A : <strong>{{ $annee1?->annee ?? '—' }}</strong> &nbsp;|&nbsp;
            Période B : <strong>{{ $annee2?->annee ?? '—' }}</strong>
        </div>
    </div>
    <div class="header-date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>

<div class="period-bar">
    <div class="period-card p1">
        <div class="period-label">Période A</div>
        <div class="period-year">{{ $annee1?->annee ?? '—' }}</div>
    </div>
    <div class="period-card p2">
        <div class="period-label">Période B</div>
        <div class="period-year">{{ $annee2?->annee ?? '—' }}</div>
    </div>
</div>

@php
    $delta = fn($a, $b, $fmt='') => $b != $a
        ? '<span class="kpi-delta '.($b > $a ? 'delta-up' : 'delta-down').'">'.($b > $a ? '▲' : '▼').' '.abs(round($b - $a, 2)).$fmt.'</span>'
        : '<span class="kpi-delta delta-eq">= stable</span>';
@endphp

{{-- KPI Cards --}}
<div class="kpi-row">
    <div class="kpi-card">
        <div class="kpi-label">Note moy. A</div>
        <div class="kpi-val">{{ number_format($stats1['moyenne'], 2) }}</div>
        <div class="kpi-sub">/10</div>
        {!! $delta($stats1['moyenne'], $stats2['moyenne']) !!}
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Note moy. B</div>
        <div class="kpi-val">{{ number_format($stats2['moyenne'], 2) }}</div>
        <div class="kpi-sub">/10</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Évals validées A</div>
        <div class="kpi-val">{{ $stats1['validees'] }}</div>
        {!! $delta($stats1['validees'], $stats2['validees']) !!}
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Évals validées B</div>
        <div class="kpi-val">{{ $stats2['validees'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Taux complétion A</div>
        <div class="kpi-val">{{ $stats1['taux_completion'] }}%</div>
        {!! $delta($stats1['taux_completion'], $stats2['taux_completion'], '%') !!}
    </div>
    <div class="kpi-card">
        <div class="kpi-label">Taux complétion B</div>
        <div class="kpi-val">{{ $stats2['taux_completion'] }}%</div>
    </div>
</div>

{{-- Évaluations table --}}
<div class="section-title">Évaluations</div>
<table>
    <thead>
        <tr>
            <th style="width:40%">Indicateur</th>
            <th>{{ $annee1?->annee ?? 'Période A' }}</th>
            <th>{{ $annee2?->annee ?? 'Période B' }}</th>
            <th>Évolution</th>
        </tr>
    </thead>
    <tbody>
        @foreach ([
            ['Total soumises',      'total',           ''],
            ['Validées',            'validees',        ''],
            ['En cours (soumises)', 'soumises',        ''],
            ['Refusées',            'refusees',        ''],
            ['Note moyenne /10',    'moyenne',         '/10'],
            ['Meilleure note',      'meilleure',       '/10'],
            ['Note la plus basse',  'pire',            '/10'],
            ['Agents évalués',      'agents_evalues',  ''],
            ['Taux de complétion',  'taux_completion', '%'],
        ] as $row)
        @php $v1 = $stats1[$row[1]]; $v2 = $stats2[$row[1]]; @endphp
        <tr>
            <td>{{ $row[0] }}</td>
            <td><span class="badge badge-blue">{{ $v1 }}{{ $row[2] }}</span></td>
            <td><span class="badge badge-green">{{ $v2 }}{{ $row[2] }}</span></td>
            <td>
                @if($v2 > $v1) <span style="color:#16a34a;font-weight:700">▲ +{{ round($v2 - $v1, 2) }}{{ $row[2] }}</span>
                @elseif($v2 < $v1) <span style="color:#dc2626;font-weight:700">▼ {{ round($v2 - $v1, 2) }}{{ $row[2] }}</span>
                @else <span style="color:#94a3b8">= stable</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Appréciations --}}
<div class="section-title">Répartition des appréciations</div>
<table>
    <thead>
        <tr>
            <th style="width:30%">Appréciation</th>
            <th>{{ $annee1?->annee ?? 'Période A' }}</th>
            <th>{{ $annee2?->annee ?? 'Période B' }}</th>
            <th>Évolution</th>
        </tr>
    </thead>
    <tbody>
        @foreach ([
            ['Excellent (≥ 8.5/10)', 'excellent',  '#065f46'],
            ['Bien (7 – 8.5/10)',    'bien',        '#1d4ed8'],
            ['Passable (5 – 7/10)',  'passable',    '#92400e'],
            ['Insuffisant (< 5/10)', 'insuffisant', '#991b1b'],
        ] as $row)
        @php $v1 = $stats1[$row[1]]; $v2 = $stats2[$row[1]]; @endphp
        <tr>
            <td style="font-weight:700;color:{{ $row[2] }}">{{ $row[0] }}</td>
            <td>{{ $v1 }}</td>
            <td>{{ $v2 }}</td>
            <td>
                @if($v2 > $v1) <span style="color:#16a34a;font-weight:700">▲ +{{ $v2 - $v1 }}</span>
                @elseif($v2 < $v1) <span style="color:#dc2626;font-weight:700">▼ {{ $v2 - $v1 }}</span>
                @else <span style="color:#94a3b8">stable</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <span>RCPB — Rapport de comparaison inter-période (Direction Générale)</span>
    <span>{{ now()->format('d/m/Y') }}</span>
</div>

</body>
</html>
