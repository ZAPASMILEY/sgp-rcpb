<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Evaluation #{{ $evaluation->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; line-height: 1.4; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        h2 { margin: 18px 0 8px; font-size: 13px; }
        .muted { color: #6b7280; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .grid td, .grid th { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        .criterion { margin-top: 12px; margin-bottom: 12px; }
        .criterion-title { font-weight: 700; margin-bottom: 4px; }
    </style>
</head>
<body>
    <h1>Fiche d'evaluation des performances du DIRECTEUR GENERAL</h1>
    <p class="muted">Directeur Général : {{ $cibleLabel }} | Année : {{ $evaluation->identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}
    @if($evaluation->identification->semestre)
        &nbsp; Semestre : {{ $evaluation->identification->semestre }}
    @endif
    </p>

    <table class="grid">
        <tr>
            <th>Moy. subjectifs</th>
            <th>Note subjectifs</th>
            <th>Moy. objectifs</th>
            <th>Note objectifs</th>
            <th>Note finale</th>
            <th>Mention</th>
        </tr>
        <tr>
            <td>{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</td>
            <td>{{ $mention }}</td>
        </tr>
    </table>

    <h2>Identification</h2>
    <table class="grid">
        <tr>
            <td><strong>Annee</strong><br>{{ $evaluation->identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</td>
            <td><strong>Nom et prenom</strong><br>{{ $evaluation->identification->nom_prenom ?? '-' }}</td>
            <td><strong>Semestre</strong><br>{{ $evaluation->identification->semestre ? 'Semestre '.$evaluation->identification->semestre : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Date d'evaluation</strong><br>{{ $evaluation->identification?->date_evaluation?->format('d/m/Y') ?? '-' }}</td>
            <td><strong>Emploi</strong><br>{{ $evaluation->identification->emploi ?? '-' }}</td>
            <td><strong>Matricule</strong><br>{{ $evaluation->identification->matricule ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Entite</strong><br>{{ $evaluation->identification->direction ?? '-' }}</td>
            <td colspan="2"><strong>Direction / Service</strong><br>{{ $evaluation->identification->direction_service ?? '-' }}</td>
        </tr>
    </table>

    <h2>Formation, stage et seminaires</h2>
    <table class="grid">
        <tr>
            <th>Periode</th>
            <th>Formation</th>
            <th>Domaine</th>
        </tr>
        @forelse (($evaluation->identification->formations ?? []) as $row)
            <tr>
                <td>{{ $row['periode'] ?? '-' }}</td>
                <td>{{ $row['libelle'] ?? '-' }}</td>
                <td>{{ $row['domaine'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune formation renseignee.</td></tr>
        @endforelse
    </table>

    <h2>Experience professionnelle</h2>
    <table class="grid">
        <tr>
            <th>Periode</th>
            <th>Poste ou fonction</th>
            <th>Observations</th>
        </tr>
        @forelse (($evaluation->identification->experiences ?? []) as $row)
            <tr>
                <td>{{ $row['periode'] ?? '-' }}</td>
                <td>{{ $row['poste'] ?? '-' }}</td>
                <td>{{ $row['observations'] ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune experience renseignee.</td></tr>
        @endforelse
    </table>

    <h2>Criteres objectifs</h2>
    @foreach ($objectiveCriteria as $criterion)
        <div class="criterion">
            <div class="criterion-title">{{ $criterion->titre }} (note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }})</div>
            <table class="grid">
                <tr>
                    <th>Sous-critere</th>
                    <th>Note /5</th>
                    <th>Observation</th>
                </tr>
                @foreach ($criterion->sousCriteres as $subcriterion)
                    <tr>
                        <td>{{ $subcriterion->libelle }}</td>
                        <td>{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                        <td>{{ $subcriterion->observation ?: '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    <h2>Criteres subjectifs</h2>
    @foreach ($subjectiveCriteria as $criterion)
        <div class="criterion">
            <div class="criterion-title">{{ $criterion->titre }} (note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }})</div>
            <table class="grid">
                <tr>
                    <th>Sous-critere</th>
                    <th>Note /5</th>
                    <th>Observation</th>
                </tr>
                @foreach ($criterion->sousCriteres as $subcriterion)
                    <tr>
                        <td>{{ $subcriterion->libelle }}</td>
                        <td>{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                        <td>{{ $subcriterion->observation ?: '-' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    <h2>Note totale d'évaluation</h2>
    <table class="grid">
        <tr>
            <td><strong>Note finale</strong></td>
            <td>{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</td>
        </tr>
    </table>

    <h2>Plan d'amelioration</h2>
    <table class="grid">
        <tr>
            <td><strong>Points a ameliorer</strong><br>{{ $evaluation->points_a_ameliorer ?: '-' }}</td>
            <td><strong>Strategies d'amelioration</strong><br>{{ $evaluation->strategies_amelioration ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Commentaires de l'evalue</strong><br>{{ $evaluation->commentaires_evalue ?: '-' }}</td>
            <td><strong>Commentaire de l'evaluateur</strong><br>{{ $evaluation->commentaire ?: '-' }}</td>
        </tr>
    </table>
</body>
</html>
