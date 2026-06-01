<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Evaluation #{{ $evaluation->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; line-height: 1.4; margin: 0; padding: 10px 14px; }
        h1 { margin: 0 0 4px; font-size: 15px; font-weight: 700; }
        h2 { margin: 14px 0 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 2px; }
        .muted { color: #6b7280; font-size: 10px; margin-bottom: 4px; }
        .grid { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        .grid th { background: #f1f5f9; font-weight: 700; font-size: 9.5px; text-align: left; padding: 5px 6px; border: 1px solid #cbd5e1; }
        .grid td { border: 1px solid #cbd5e1; padding: 5px 6px; vertical-align: top; font-size: 9.5px; word-wrap: break-word; }
        .group-row td { background: #e2e8f0; font-weight: 700; font-size: 9.5px; padding: 4px 6px; }
        .note-col { width: 10%; text-align: center; }
        .calc-header td { background: #1e3a5f; color: #ffffff; font-weight: 700; font-size: 9.5px; padding: 4px 6px; }
        .calc-total td  { background: #dbeafe; font-weight: 700; font-size: 9.5px; }
        .text-center { text-align: center; }
        .formula-box { margin-top: 4px; padding: 5px 8px; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 9px; color: #374151; }
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

    <h2>Criteres de calcul</h2>
    <table class="grid">
        <colgroup>
            <col style="width:32%">
            <col style="width:14%">
            <col style="width:14%">
            <col style="width:14%">
            <col style="width:26%">
        </colgroup>
        <tr class="calc-header">
            <td>Composante</td>
            <td class="text-center">Moyenne /5</td>
            <td class="text-center">Ponderation</td>
            <td class="text-center">Contribution /10</td>
            <td>Formule</td>
        </tr>
        <tr>
            <td><strong>Criteres objectifs</strong></td>
            <td class="text-center">{{ number_format((float) $evaluation->moyenne_objectifs,  2, ',', ' ') }}</td>
            <td class="text-center">75 %</td>
            <td class="text-center">{{ number_format((float) $evaluation->note_criteres_objectifs,  2, ',', ' ') }}</td>
            <td style="font-size:8.5px;">Moy. objectifs &times; 0,75</td>
        </tr>
        <tr>
            <td><strong>Criteres subjectifs</strong></td>
            <td class="text-center">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</td>
            <td class="text-center">25 %</td>
            <td class="text-center">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</td>
            <td style="font-size:8.5px;">Moy. subjectifs &times; 0,25</td>
        </tr>
        <tr class="calc-total">
            <td><strong>Note finale /10</strong></td>
            <td class="text-center">—</td>
            <td class="text-center">100 %</td>
            <td class="text-center"><strong>{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</strong></td>
            <td style="font-size:8.5px;">(Contrib. obj. + Contrib. subj.) &times; 2</td>
        </tr>
    </table>
    <div class="formula-box">
        <strong>Grille d'appreciation :</strong>
        Excellent (&ge; 8,5) &nbsp;|&nbsp; Bien (&ge; 7) &nbsp;|&nbsp; Passable (&ge; 5) &nbsp;|&nbsp; Insuffisant (&lt; 5)
    </div>

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
            <th class="note-col">Note /5</th>
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
            <th class="note-col">Note /5</th>
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
