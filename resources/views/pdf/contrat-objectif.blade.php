<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat d'objectifs</title>
    <style>
        @page {
            margin: 22mm 16mm 18mm 16mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.35;
            color: #111;
        }

        .page {
            position: relative;
            min-height: 100%;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .header-table td {
            border: 1px solid #444;
            vertical-align: top;
            padding: 6px 8px;
        }

        .logo-cell {
            width: 84px;
            text-align: center;
        }

        .logo-box {
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #444;
            padding: 4px 2px;
            margin-bottom: 6px;
        }

        .logo-image {
            width: 38px;
            height: 38px;
            object-fit: contain;
            display: block;
            margin: 0 auto 4px auto;
        }

        .tiny {
            font-size: 8px;
            line-height: 1.2;
        }

        .header-main {
            text-align: center;
            font-size: 9px;
            line-height: 1.3;
        }

        .header-main strong {
            font-size: 10px;
        }

        .header-meta {
            margin-top: 6px;
            font-size: 8.5px;
            text-align: left;
        }

        .exemplaire {
            text-align: center;
            font-size: 8px;
            font-style: italic;
            margin-top: 4px;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 16px 0 18px 0;
            letter-spacing: 0.5px;
        }

        .label {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .paragraph {
            text-align: justify;
            margin: 0 0 10px 0;
        }

        .section-title {
            font-size: 9.5px;
            font-weight: bold;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
        }

        ul {
            margin: 4px 0 10px 0;
            padding: 0;
            list-style: none;
        }

        li {
            margin-bottom: 5px;
            text-align: justify;
        }

        .arrow-bullet {
            display: inline-block;
            width: 14px;
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            margin-top: 34px;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }

        .signature-space {
            height: 70px;
        }

        .footer {
            width: 100%;
            margin-top: 18px;
            font-size: 8px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="page">
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <div class="logo-box">{{ $institution_sigle === 'FCPB' ? 'F.C.P.B' : 'R.C.P.B' }}</div>
                    @php
                        $logoPath = public_path('images/rcpb-logo.jpeg');
                    @endphp
                    @if (file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo RCPB" class="logo-image">
                    @endif
                    <div class="tiny">Reseaux des Caisses Populaires du Burkina</div>
                </td>
                <td>
                    <div class="header-main">
                        <strong>Faitiere des Caisses Populaires du Burkina</strong><br>
                        1712, Avenue Kwame N'Krumah / 01 BP 4077 Ouagadougou 01
                    </div>
                    <div class="header-meta">
                        <div><strong>TITRE DU DOCUMENT :</strong> CONTRAT D'OBJECTIFS DU {{ $institution_sigle }}</div>
                        <div><strong>REFERENCE :</strong> EN/ 2018-Fi002-PGORH-V1</div>
                        <div><strong>SUJET :</strong> Definition des objectifs de performances</div>
                        <div><strong>LIEU DE DIFFUSION :</strong> {{ $institution_sigle }}</div>
                    </div>
                    <div class="exemplaire">Exemplaire unique</div>
                </td>
            </tr>
        </table>

        <div class="title">CONTRAT D'OBJECTIFS</div>

        <div class="label">ENTRE</div>
        <p class="paragraph">
            La Faitiere des Caisses Populaires du Burkina (FCPB), representee par
            <strong>{{ $partieFaitiereNomComplet !== '' ? $partieFaitiereNomComplet : 'President du Conseil d Administration' }}</strong>,
            agissant en qualite de {{ $partieFaitiereRole ?? 'President du Conseil d Administration' }}.
        </p>

        <div class="label">ET</div>
        <p class="paragraph">
            <strong>{{ $partieCollaborateur->name ?? 'Collaborateur' }}</strong>
            @if (!empty($partieCollaborateur->role))
                , {{ $partieCollaborateur->role }}
            @endif
        </p>

        <div class="section-title">PREAMBULE</div>
        <p class="paragraph">
            Le present contrat vise a inciter le travailleur a rechercher la productivite et la rentabilite,
            a epouser l'idee d'obligation de resultats, a prendre en charge les contraintes de performance
            individuelle et a mesurer sa contribution dans l'atteinte des resultats globaux de l'institution.
        </p>

        <div class="section-title">ARTICLE 1er : Engagement du travailleur</div>
        <p class="paragraph">
            Monsieur ou Madame <strong>{{ $partieCollaborateur->name ?? 'Collaborateur' }}</strong>
            s'engage a assumer les principales fonctions et responsabilites suivantes :
        </p>
        <ul>
            @foreach($objectifs as $objectif)
                <li><span class="arrow-bullet">></span>{{ $objectif->description }}</li>
            @endforeach
        </ul>

        <div class="section-title">ARTICLE 2 : MOYENS DE REALISATION</div>
        <p class="paragraph">
            La Faitiere des Caisses Populaires du Burkina s'engage a mettre a la disposition de
            Monsieur ou Madame <strong>{{ $partieCollaborateur->name ?? 'le travailleur' }}</strong>
            le budget necessaire a son fonctionnement, conformement a la planification de la FCPB
            de l'annee {{ \Carbon\Carbon::parse($dateDebut)->format('Y') }}.
        </p>

        <div class="section-title">ARTICLE 3 : DUREE DU CONTRAT</div>
        <p class="paragraph">
            Le present contrat couvre la periode du <strong>{{ $dateDebut }}</strong>
            au <strong>{{ $dateFin }}</strong>.
        </p>

        <div class="section-title">ARTICLE 4 : RESILIATION DU CONTRAT</div>
        <p class="paragraph">
            Le present contrat peut etre revise ou resilie en cas de necessite de service,
            de changement majeur dans les orientations de l'institution ou de non-respect
            des engagements convenus par l'une des parties.
        </p>

        <table class="signature-table">
            <tr>
                <td>
                    <div>Signature du travailleur</div>
                    <div class="signature-space"></div>
                    <div><strong>{{ $partieCollaborateur->name ?? 'Collaborateur' }}</strong></div>
                </td>
                <td>
                    <div>Signature du representant</div>
                    <div class="signature-space"></div>
                    <div><strong>{{ $partieFaitiereNomComplet !== '' ? $partieFaitiereNomComplet : 'PCA' }}</strong></div>
                </td>
            </tr>
        </table>

        <div class="footer">Page 1 sur 1</div>
    </div>
</body>
</html>
