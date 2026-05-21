<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Comparaison inter-période — PCA</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; padding: 20px 28px; }

    .header { border-bottom: 3px solid #7c3aed; padding-bottom: 10px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: flex-end; }
    .header-title { font-size: 18px; font-weight: 700; color: #7c3aed; }
    .header-sub   { font-size: 11px; color: #64748b; margin-top: 2px; }
    .header-date  { font-size: 10px; color: #94a3b8; }

    .period-bar { display: flex; gap: 12px; margin-bottom: 16px; }
    .period-card { flex: 1; padding: 10px 14px; border-radius: 6px; }
    .period-card.p1 { background: #eff6ff; border-left: 4px solid #3b82f6; }
    .period-card.p2 { background: #f5f3ff; border-left: 4px solid #8b5cf6; }
    .period-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
    .period-year  { font-size: 20px; font-weight: 900; color: #1e293b; margin-top: 2px; }

    .dg-banner { background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 6px; padding: 8px 14px; margin-bottom: 16px; font-size: 11px; color: #5b21b6; }

    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    thead th { background: #5b21b6; color: #fff; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 7px 10px; text-align: left; }
    thead th:not(:first-child) { text-align: center; }
    tbody tr:nth-child(even) { background: #faf5ff; }
    tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
    tbody td:not(:first-child) { text-align: center; font-weight: 600; }

    .section-title { font-size: 12px; font-weight: 800; color: #5b21b6; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; margin-top: 16px; text-transform: uppercase; letter-spacing: 0.5px; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .badge-blue   { background: #dbeafe; color: #1d4ed8; }
    .badge-purple { background: #ede9fe; color: #5b21b6; }

    .avancement-bar { height: 8px; border-radius: 4px; background: #e9d5ff; overflow: hidden; margin-top: 3px; }
    .avancement-fill { height: 100%; background: #7c3aed; border-radius: 4px; }

    .footer { border-top: 1px solid #e2e8f0; margin-top: 20px; padding-top: 8px; font-size: 9px; color: #94a3b8; display: flex; justify-content: space-between; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="header-title">Comparaison inter-période — Présidence du Conseil d'Administration</div>
        <div class="header-sub">
            Période A : <strong>{{ $annee1?->annee ?? '—' }}</strong> &nbsp;|&nbsp;
            Période B : <strong>{{ $annee2?->annee ?? '—' }}</strong>
        </div>
    </div>
    <div class="header-date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>

@if($dgUser)
<div class="dg-banner">
    Suivi du Directeur Général : <strong>{{ $dgUser->name }}</strong>
    @if($entite?->nom) — {{ $entite->nom }}@endif
</div>
@endif

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

{{-- Fiches DG --}}
<div class="section-title">Fiches objectifs — DG</div>
<table>
    <thead>
        <tr>
            <th style="width:38%">Indicateur</th>
            <th>{{ $annee1?->annee ?? 'Période A' }}</th>
            <th>{{ $annee2?->annee ?? 'Période B' }}</th>
            <th>Évolution</th>
        </tr>
    </thead>
    <tbody>
        @foreach ([
            ['Total fiches',        'fiches',           ''],
            ['Acceptées',           'fiches_acceptees', ''],
            ['En attente',          'fiches_attente',   ''],
            ['Refusées',            'fiches_refusees',  ''],
            ['Avancement moyen',    'avancement',       '%'],
        ] as $row)
        @php $v1 = $stats1[$row[1]]; $v2 = $stats2[$row[1]]; @endphp
        <tr>
            <td>
                {{ $row[0] }}
                @if($row[1] === 'avancement')
                    <div class="avancement-bar"><div class="avancement-fill" style="width:{{ min($v2, 100) }}%"></div></div>
                @endif
            </td>
            <td><span class="badge badge-blue">{{ $v1 }}{{ $row[2] }}</span></td>
            <td><span class="badge badge-purple">{{ $v2 }}{{ $row[2] }}</span></td>
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

{{-- Évaluations DG --}}
<div class="section-title">Évaluations — DG</div>
<table>
    <thead>
        <tr>
            <th style="width:38%">Indicateur</th>
            <th>{{ $annee1?->annee ?? 'Période A' }}</th>
            <th>{{ $annee2?->annee ?? 'Période B' }}</th>
            <th>Évolution</th>
        </tr>
    </thead>
    <tbody>
        @foreach ([
            ['Total évaluations',   'evals',           ''],
            ['Validées',            'evals_validees',  ''],
            ['Soumises',            'evals_soumises',  ''],
            ['En brouillon',        'evals_brouillon', ''],
            ['Refusées',            'evals_refusees',  ''],
            ['Note moyenne /10',    'moyenne',         '/10'],
            ['Meilleure note',      'meilleure',       '/10'],
        ] as $row)
        @php $v1 = $stats1[$row[1]]; $v2 = $stats2[$row[1]]; @endphp
        <tr>
            <td>{{ $row[0] }}</td>
            <td><span class="badge badge-blue">{{ $v1 }}{{ $row[2] }}</span></td>
            <td><span class="badge badge-purple">{{ $v2 }}{{ $row[2] }}</span></td>
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

<div class="footer">
    <span>RCPB — Rapport de comparaison inter-période (PCA)</span>
    <span>{{ now()->format('d/m/Y') }}</span>
</div>

</body>
</html>
