<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Evaluation #{{ $evaluation->id }}</title>
    <style>
        { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; line-height: 1.4; margin: 0; padding: 10px 14px; }
        h1 { margin: 0 0 4px; font-size: 15px; font-weight: 700; }
        h2 { margin: 14px 0 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; }
        .muted { color: #6b7280; font-size: 10px; margin-bottom: 4px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        .grid th { background: #f1f5f9; font-weight: 700; font-size: 9.5px; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; }
        .grid td { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; font-size: 9.5px; word-wrap: break-word; }
        .group-row td { background: #e2e8f0; font-weight: 700; font-size: 9.5px; padding: 4px 6px; }
        .note-col  { width: 10%; text-align: center; }
        .obs-col   { width: 22%; }
        .label-col { width: 30%; }
        .critere-col { width: 35%; }
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
        @include('evaluations._formations_auto_pdf')
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
    <table class="grid">
        <colgroup>
            <col style="width:35%">
            <col style="width:35%">
            <col style="width:10%">
            <col style="width:20%">
        </colgroup>
        <tr>
            <th>Critere</th>
            <th>Sous-critere</th>
            <th class="note-col">Note</th>
            <th>Observation</th>
        </tr>
        @foreach ($objectiveCriteria as $criterion)
            <tr class="group-row">
                <td colspan="4">
                    {{ $criterion->titre }}
                    — Note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                </td>
            </tr>
            @foreach ($criterion->sousCriteres as $subcriterion)
                <tr>
                    <td></td>
                    <td>{{ $subcriterion->libelle }}</td>
                    <td class="note-col" style="text-align:center">{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                    <td>{{ $subcriterion->observation ?: '-' }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

    <h2>Criteres subjectifs</h2>
    <table class="grid">
        <colgroup>
            <col style="width:35%">
            <col style="width:35%">
            <col style="width:10%">
            <col style="width:20%">
        </colgroup>
        <tr>
            <th>Critere</th>
            <th>Sous-critere</th>
            <th class="note-col">Note</th>
            <th>Observation</th>
        </tr>
        @foreach ($subjectiveCriteria as $criterion)
            <tr class="group-row">
                <td colspan="4">
                    {{ $criterion->titre }}
                    — Note globale : {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                </td>
            </tr>
            @foreach ($criterion->sousCriteres as $subcriterion)
                <tr>
                    <td></td>
                    <td>{{ $subcriterion->libelle }}</td>
                    <td class="note-col" style="text-align:center">{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                    <td>{{ $subcriterion->observation ?: '-' }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>

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
