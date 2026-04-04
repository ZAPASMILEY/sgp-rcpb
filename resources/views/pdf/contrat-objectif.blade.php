@php
    // Variables attendues : $contrat (Objectif|Evaluation), $partieCollaborateur (User|Direction|Entite), $partieFaîtière (Entite), $objectifs (Collection), $dateDebut, $dateFin
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat d'objectifs - {{ $partieCollaborateur->name ?? ($partieCollaborateur->nom ?? '') }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 13px; color: #222; margin: 0; padding: 0; }
        .header { display: flex; align-items: center; border-bottom: 2px solid #222; padding: 10px 0 8px 0; }
        .header-logo { width: 80px; margin-right: 20px; }
        .header-info { flex: 1; }
        .header-info h2 { margin: 0 0 2px 0; font-size: 18px; font-weight: bold; }
        .header-info .meta { font-size: 12px; color: #555; }
        .box { border: 1px solid #aaa; border-radius: 8px; padding: 18px 22px; margin: 18px 0; background: #f9f9f9; }
        h1 { text-align: center; font-size: 22px; margin: 18px 0 10px 0; text-transform: uppercase; letter-spacing: 2px; }
        h2 { font-size: 16px; margin: 18px 0 8px 0; }
        .section { margin-bottom: 18px; }
        .objectifs-list { margin: 0 0 0 18px; }
        .objectifs-list li { margin-bottom: 6px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-block { width: 45%; text-align: center; }
        .signature-label { font-size: 12px; color: #555; margin-bottom: 40px; }
        .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: right; font-size: 11px; color: #888; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/logo-rcpb.png') }}" class="header-logo" alt="Logo RCPB">
        <div class="header-info">
            <h2>Faîtière des Caisses Populaires du Burkina (RCPB)</h2>
            <div class="meta">
                <div><b>Titre du document :</b> Contrat d'objectifs du RCPB</div>
                <div><b>Sujet :</b> Définition des objectifs de performance</div>
                <div><b>Niveau de diffusion :</b> RCPB</div>
            </div>
        </div>
    </div>

    <h1>CONTRAT D'OBJECTIFS</h1>

    <div class="box section">
        <b>ENTRE</b><br>
        La Faîtière des Caisses Populaires du Burkina (RCPB), représentée par Monsieur/Madame <b>{{ $partieFaîtière->pca_nom ?? 'PCA' }}</b>, Président(e) du Conseil d’Administration, ci-après dénommée « la Faîtière »<br>
        <br>
        ET<br>
        @if(isset($partieCollaborateur->name))
            Monsieur/Madame <b>{{ $partieCollaborateur->name }}</b>, {{ $partieCollaborateur->role ?? '' }}
        @elseif(isset($partieCollaborateur->nom))
            Monsieur/Madame <b>{{ $partieCollaborateur->nom }}</b>
        @endif
        <br>
        ci-après dénommé(e) « le Collaborateur »
    </div>

    <div class="section">
        <h2>Article 1 : Engagement du collaborateur</h2>
        <ul class="objectifs-list">
            @foreach($objectifs as $objectif)
                <li>{{ $objectif->description ?? $objectif->libelle ?? $objectif->titre ?? '' }}</li>
            @endforeach
        </ul>
    </div>

    <div class="section">
        <h2>Article 2 : Moyens et réalisation</h2>
        <p>Le collaborateur s’engage à mettre en œuvre tous les moyens nécessaires à la réalisation des objectifs ci-dessus, dans le respect des valeurs et procédures de la RCPB.</p>
    </div>

    <div class="section">
        <h2>Article 3 : Durée du contrat</h2>
        <p>Le présent contrat couvre la période du <b>{{ $dateDebut }}</b> au <b>{{ $dateFin }}</b>.</p>
    </div>

    <div class="signatures">
        <div class="signature-block">
            <div class="signature-label">Signature du collaborateur<br>(Précédée de la mention « lu et approuvé »)</div>
            <br><br><br>
            <span>__________________________</span>
        </div>
        <div class="signature-block">
            <div class="signature-label">Signature du supérieur hiérarchique</div>
            <br><br><br>
            <span>__________________________</span>
        </div>
    </div>

    <div class="footer">
        Page 1
    </div>
</body>
</html>
