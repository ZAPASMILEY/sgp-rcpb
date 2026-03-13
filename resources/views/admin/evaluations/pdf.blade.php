<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Evaluation #{{ $evaluation->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.45;
        }
        h1 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        .muted {
            color: #6b7280;
        }
        .grid {
            margin-top: 18px;
            margin-bottom: 18px;
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            vertical-align: top;
        }
        .section-title {
            margin-top: 24px;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }
        .objectif {
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <h1>Evaluation #{{ $evaluation->id }}</h1>
    <p class="muted">
        Type : {{ $cibleType }} | Cible : {{ $cibleLabel }}
    </p>

    <table class="grid">
        <tr>
            <td><strong>Periode</strong><br>{{ $evaluation->date_debut->format('d/m/Y') }} - {{ $evaluation->date_fin->format('d/m/Y') }}</td>
            <td><strong>Note objectifs</strong><br>{{ $evaluation->note_objectifs }}%</td>
            <td><strong>Note manuelle</strong><br>{{ $evaluation->note_manuelle !== null ? $evaluation->note_manuelle.'%' : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Note finale</strong><br>{{ $evaluation->note_finale }}%</td>
            <td><strong>Mention</strong><br>{{ $mention }}</td>
            <td><strong>Statut</strong><br>{{ ucfirst($evaluation->statut) }}</td>
        </tr>
    </table>

    <p class="section-title">Commentaire</p>
    <p>{{ $evaluation->commentaire ?: 'Aucun commentaire.' }}</p>

    <p class="section-title">Objectifs de la periode</p>
    @forelse ($objectifs as $objectif)
        <div class="objectif">
            <p style="margin:0 0 6px;">{{ $objectif->commentaire }}</p>
            <p class="muted" style="margin:0;">Date: {{ \Carbon\Carbon::parse($objectif->date)->format('d/m/Y') }} | Avancement: {{ $objectif->avancement_percentage }}%</p>
        </div>
    @empty
        <p class="muted">Aucun objectif dans la periode selectionnee.</p>
    @endforelse
</body>
</html>
