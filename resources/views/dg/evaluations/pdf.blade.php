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
    <h1>Fiche d'evaluation des performances
        @if(isset($evaluation->identification->emploi) && $evaluation->identification->emploi)
            du {{ strtoupper($evaluation->identification->emploi) }}
        @endif
    </h1>
    <p class="muted">{{ $cibleType }} : {{ $cibleLabel }} | Année : {{ $evaluation->date_debut->format('Y') }}
    @if($evaluation->identification && $evaluation->identification->semestre)
        | Semestre : {{ $evaluation->identification->semestre }}
    @endif
    </p>

    <table class="grid">
        <tr>
            <th>Moy. subjectifs</th>
            <th>Note subjectifs /10</th>
            <th>Moy. objectifs</th>
            <th>Note objectifs /10</th>
            <th>Note finale /10</th>
            <th>Appréciation</th>
        </tr>
        <tr>
            <td>{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</td>
            <td>{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</td>
            <td>
                @php
                    $note = (float) $evaluation->note_finale;
                    if ($note >= 9) {
                        $appreciation = 'Excellent';
                    } elseif ($note >= 8) {
                        $appreciation = 'Très satisfaisant';
                    } elseif ($note >= 7) {
                        $appreciation = 'Satisfaisant';
                    } elseif ($note >= 6) {
                        $appreciation = 'Assez satisfaisant';
                    } elseif ($note >= 5) {
                        $appreciation = 'Passable';
                    } else {
                        $appreciation = 'Insuffisant';
                    }
                @endphp
                {{ $appreciation }}
            </td>
        </tr>
    </table>

    <h2>Identification</h2>
    <table class="grid">
        <tr>
            <th>Nom et prenom</th>
            <th>Emploi</th>
            <th>Matricule</th>
            <th>Entite</th>
            <th>Direction / Service</th>
            <th>Date d'evaluation</th>
        </tr>
        <tr>
            <td>{{ $evaluation->identification->nom_prenom ?? '-' }}</td>
            <td>{{ $evaluation->identification->emploi ?? '-' }}</td>
            <td>{{ $evaluation->identification->matricule ?? '-' }}</td>
            <td>{{ $evaluation->identification->direction ?? '-' }}</td>
            <td>{{ $evaluation->identification->direction_service ?? '-' }}</td>
            <td>{{ $evaluation->identification->date_evaluation ? $evaluation->identification->date_evaluation->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    <h2>Formations</h2>
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
            <div class="criterion-title">{{ $criterion->titre }} (Note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }})</div>
            <table class="grid">
                <tr>
                    <th>Sous-critere</th>
                    <th>Note</th>
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
            <div class="criterion-title">{{ $criterion->titre }} (Note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }})</div>
            <table class="grid">
                <tr>
                    <th>Sous-critere</th>
                    <th>Note</th>
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

    <h2>Plan d'amelioration</h2>
    <table class="grid">
        <tr>
            <th>Points à améliorer</th>
            <th>Stratégies d'amélioration</th>
            <th>Commentaires de l'évalué</th>
            <th>Commentaire de l'évaluateur</th>
        </tr>
        <tr>
            <td>{{ $evaluation->points_a_ameliorer ?: '-' }}</td>
            <td>{{ $evaluation->strategies_amelioration ?: '-' }}</td>
            <td>{{ $evaluation->commentaires_evalue ?: '-' }}</td>
            <td>{{ $evaluation->commentaire ?: '-' }}</td>
        </tr>
    </table>

    <br><br>
    <div style="width: 100%; margin-top: 40px;">
        <div style="display: flex; justify-content: space-between;">
            <div style="width: 45%; text-align: left;">
                <strong>Nom de l'évaluateur :</strong><br>
                {{ $evaluation->signature_evaluateur_nom ?: '...........................................' }}<br><br>
                <span style="display: inline-block; width: 100%; border-bottom: 1px solid #d1d5db; height: 40px;"></span><br>
                <span style="font-size: 10px;">Signature de l'évaluateur</span>
            </div>
            <div style="width: 45%; text-align: right;">
                <strong>Nom de l'évalué :</strong><br>
                {{ $evaluation->signature_evalue_nom ?: '...........................................' }}<br><br>
                <span style="display: inline-block; width: 100%; border-bottom: 1px solid #d1d5db; height: 40px;"></span><br>
                <span style="font-size: 10px;">Signature de l'évalué</span>
            </div>
        </div>
    </div>

    @php
        // Récupération de la ville de l'entité de l'évaluateur
        $ville = null;
        if ($evaluation->evaluateur && $evaluation->evaluateur->entite) {
            $ville = $evaluation->evaluateur->entite->ville;
        }
        // Si PCA (faitière), la ville est Ouagadougou
        if ($evaluation->evaluateur && $evaluation->evaluateur->isPca()) {
            $ville = 'Ouagadougou';
        }
    @endphp
    <div style="margin-top: 30px; text-align: right; font-size: 11px;">
        Fait à <span style="display:inline-block; min-width:180px; border-bottom:1px dotted #888;">...........................................</span>,
        le <span style="display:inline-block; min-width:120px; border-bottom:1px dotted #888;">........../........../..........</span>
    </div>
</body>
</html>
