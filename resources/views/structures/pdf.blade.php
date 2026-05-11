<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; background: #fff; }

    /* ── En-tête ── */
    .header { background: #008751; color: #fff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; }
    .header-left h1 { font-size: 14pt; font-weight: 900; letter-spacing: 0.5px; }
    .header-left p  { font-size: 8pt; opacity: 0.8; margin-top: 2px; }
    .header-right   { text-align: right; font-size: 8pt; opacity: 0.85; }
    .logo-box { width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px;
                display: flex; align-items: center; justify-content: center; margin-left: 12px; }

    /* ── KPIs ── */
    .kpi-row { display: flex; gap: 10px; margin: 14px 20px 10px; }
    .kpi { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
    .kpi-label { font-size: 7pt; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; font-weight: 700; }
    .kpi-value { font-size: 18pt; font-weight: 900; color: #008751; margin-top: 2px; }

    /* ── Meta ── */
    .meta { margin: 0 20px 10px; font-size: 7.5pt; color: #64748b; }
    .meta span { font-weight: 700; color: #334155; }

    /* ── Table ── */
    table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
    thead tr { background: #008751; color: #fff; }
    thead th { padding: 7px 8px; text-align: left; font-size: 7.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; }
    thead th.center { text-align: center; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr { border-bottom: 1px solid #f1f5f9; }
    tbody td { padding: 6px 8px; font-size: 8pt; vertical-align: middle; }
    tbody td.center { text-align: center; }

    /* ── Note bar ── */
    .bar-wrap { display: flex; align-items: center; gap: 5px; }
    .bar-bg { flex: 1; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 3px; }
    .bar-emerald { background: #10b981; }
    .bar-sky     { background: #0ea5e9; }
    .bar-amber   { background: #f59e0b; }
    .bar-rose    { background: #f43f5e; }
    .bar-slate   { background: #cbd5e1; }

    /* ── Mention badge ── */
    .badge { padding: 2px 7px; border-radius: 10px; font-size: 7pt; font-weight: 800; display: inline-block; }
    .badge-emerald { background: #d1fae5; color: #065f46; }
    .badge-sky     { background: #e0f2fe; color: #0369a1; }
    .badge-amber   { background: #fef3c7; color: #92400e; }
    .badge-rose    { background: #ffe4e6; color: #9f1239; }
    .badge-slate   { background: #f1f5f9; color: #64748b; }

    /* ── Type badge ── */
    .type-badge { background: #eef2ff; color: #4338ca; padding: 2px 6px; border-radius: 6px; font-size: 7pt; font-weight: 700; }

    /* ── Distribution ── */
    .dist { display: flex; gap: 3px; flex-wrap: wrap; }
    .dist span { padding: 1px 5px; border-radius: 8px; font-size: 6.5pt; font-weight: 800; }
    .d-em { background: #d1fae5; color: #065f46; }
    .d-bi { background: #e0f2fe; color: #0369a1; }
    .d-pa { background: #fef3c7; color: #92400e; }
    .d-in { background: #ffe4e6; color: #9f1239; }

    /* ── Pied de page ── */
    .footer { position: fixed; bottom: 10px; left: 20px; right: 20px;
              display: flex; justify-content: space-between;
              font-size: 7pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 5px; }
</style>
</head>
<body>

{{-- En-tête --}}
<div class="header">
    <div class="header-left">
        <h1>Structures du Réseau RCPB</h1>
        <p>Rapport de synthèse — statistiques d'évaluation par structure</p>
    </div>
    <div class="header-right">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        Réseau des Caisses Populaires du Burkina
    </div>
</div>

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi">
        <div class="kpi-label">Structures</div>
        <div class="kpi-value">{{ $globalStats['nb_structures'] }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Agents</div>
        <div class="kpi-value">{{ number_format($globalStats['nb_agents']) }}</div>
    </div>
    <div class="kpi">
        <div class="kpi-label">Note moyenne réseau</div>
        <div class="kpi-value">{{ $globalStats['note_moy_reseau'] !== null ? number_format($globalStats['note_moy_reseau'], 2).' /10' : '—' }}</div>
    </div>
</div>

{{-- Meta --}}
<div class="meta">
    @if($typeFilter)
        Filtre : <span>{{ ucfirst($typeFilter) }}</span> ·
    @endif
    Tri : <span>{{ ['note'=>'Note décroissante','nom'=>'Nom A→Z','type'=>'Type','agents'=>'Nb agents','evals'=>'Nb évaluations'][$sortBy] ?? $sortBy }}</span> ·
    <span>{{ $structures->count() }}</span> structure(s) affichée(s)
</div>

{{-- Tableau --}}
<table>
    <thead>
        <tr>
            <th>Structure</th>
            <th>Type</th>
            <th class="center">Agents</th>
            <th class="center">Évals</th>
            <th style="min-width:110px">Note moy. /10</th>
            <th class="center">Mention</th>
            <th>Distribution</th>
        </tr>
    </thead>
    <tbody>
        @forelse($structures as $s)
            @php
                $note = $s->note_moyenne;
                $pct  = $note !== null ? min(100, ($note / 10) * 100) : 0;
                if ($note === null)      { $barCls = 'bar-slate';   $bdg = 'badge-slate';   $lbl = '—'; }
                elseif ($note >= 8.5)   { $barCls = 'bar-emerald'; $bdg = 'badge-emerald'; $lbl = 'Excellent'; }
                elseif ($note >= 7)     { $barCls = 'bar-sky';     $bdg = 'badge-sky';     $lbl = 'Bien'; }
                elseif ($note >= 5)     { $barCls = 'bar-amber';   $bdg = 'badge-amber';   $lbl = 'Passable'; }
                else                    { $barCls = 'bar-rose';    $bdg = 'badge-rose';    $lbl = 'Insuffisant'; }
            @endphp
            <tr>
                <td><strong>{{ $s->nom }}</strong></td>
                <td><span class="type-badge">{{ $s->type }}</span></td>
                <td class="center">{{ $s->nb_agents }}</td>
                <td class="center">{{ $s->nb_evaluations }}</td>
                <td>
                    @if($note !== null)
                        <div class="bar-wrap">
                            <div class="bar-bg"><div class="bar-fill {{ $barCls }}" style="width:{{ $pct }}%"></div></div>
                            <span style="font-weight:800;min-width:28px">{{ number_format($note, 2) }}</span>
                        </div>
                    @else
                        <span style="color:#94a3b8">—</span>
                    @endif
                </td>
                <td class="center"><span class="badge {{ $bdg }}">{{ $lbl }}</span></td>
                <td>
                    <div class="dist">
                        <span class="d-em">★ {{ $s->nb_excellent }}</span>
                        <span class="d-bi">▲ {{ $s->nb_bien }}</span>
                        <span class="d-pa">– {{ $s->nb_passable }}</span>
                        <span class="d-in">▼ {{ $s->nb_insuffisant }}</span>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:20px;color:#94a3b8">Aucune structure</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>RCPB — Rapport Structures Réseau</span>
    <span>{{ now()->format('d/m/Y') }}</span>
</div>

</body>
</html>
