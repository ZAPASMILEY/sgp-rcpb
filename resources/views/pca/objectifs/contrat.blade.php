@extends('layouts.pca')

@section('title', "Contrat d'objectifs | ".config('app.name', 'SGP-RCPB'))

@section('content')
<main class="min-h-screen bg-white px-4 py-6 sm:px-6 lg:px-10">
    <div class="mb-4 flex flex-wrap gap-3">
        <button onclick="history.back()" class="ent-btn ent-btn-soft"><i class="fas fa-arrow-left mr-2"></i>Retour</button>
        <a href="{{ route('pca.objectifs.contrat.download', $contrat) }}" class="ent-btn ent-btn-primary">Telecharger le PDF</a>
    </div>

    <div class="mx-auto max-w-3xl rounded-xl border border-slate-300 bg-white p-8 shadow print:border-0 print:p-0 print:shadow-none">
        <div class="mb-6 flex items-start gap-4 border-b pb-4">
            <img src="/img/logo-rcpb.png" alt="Logo {{ $institution_sigle }}" class="h-20 w-20 object-contain border border-slate-400 bg-white p-1">
            <div class="flex-1">
                <div class="text-xs font-semibold uppercase">Faitiere des Caisses Populaires du Burkina</div>
                <div class="mt-1 text-xs"><span class="font-bold">TITRE DU DOCUMENT :</span> CONTRAT D'OBJECTIFS DU {{ $institution_sigle }}</div>
                <div class="text-xs"><span class="font-bold">REFERENCE :</span> EN/ 2018-Fi002-PGORH-V1</div>
                <div class="text-xs"><span class="font-bold">SUJET :</span> Definition des objectifs de performances</div>
                <div class="text-xs"><span class="font-bold">LIEU DE DIFFUSION :</span> {{ $institution_sigle }}</div>
                <div class="mt-1 text-center text-xs font-bold">CONFIDENTIEL</div>
            </div>
        </div>

        <h1 class="mb-8 text-center text-2xl font-bold tracking-wider">CONTRAT D'OBJECTIFS</h1>

        <div class="mb-6 text-sm">
            <p><strong>ENTRE</strong></p>
            <p>La Faitiere des Caisses Populaires du Burkina (FCPB), representee par <strong>{{ $institution_representant }}</strong> agissant en qualite de {{ $institution_fonction }}</p>
            <p class="mt-2"><strong>ET</strong></p>
            <p><strong>{{ $salarie_nom }}</strong>, {{ $salarie_fonction }}</p>
        </div>

        <div class="mb-6 text-sm">
            <p class="font-bold underline">PREAMBULE</p>
            <p>Le present contrat d'objectifs vise a inciter les travailleurs de la Faitiere des Caisses Populaires du Burkina a rechercher la productivite et la rentabilite, a epouser l'idee d'obligation de resultats, a prendre en charge les contraintes de performances individuelles et a mesurer leur contribution dans l'atteinte des resultats globaux de l'institution.</p>
        </div>

        <div class="mb-6 text-sm">
            <p class="font-bold underline">ARTICLE 1er : Engagement du travailleur</p>
            <p>Monsieur/Madame <strong>{{ $salarie_nom }}</strong> s'engage a assumer les principales fonctions et responsabilites suivantes :</p>
            <ul class="ml-8 mt-2 list-disc">
                @foreach($objectifs as $objectif)
                    <li>{{ is_object($objectif) ? $objectif->description : $objectif }}</li>
                @endforeach
            </ul>
        </div>

        <div class="mb-6 text-sm">
            <p class="font-bold underline">ARTICLE 2 : Moyens de realisation</p>
            <p>La Faitiere des Caisses Populaires du Burkina s'engage a mettre a la disposition de Monsieur/Madame <strong>{{ $salarie_nom }}</strong> le budget necessaire a son fonctionnement, conformement a la planification de la FCPB de l'annee {{ \Carbon\Carbon::parse($date_debut)->format('Y') }}.</p>
        </div>

        <div class="mb-6 text-sm">
            <p class="font-bold underline">ARTICLE 3 : Duree du contrat</p>
            <p>Le present contrat est conclu pour la periode du <strong>{{ $date_debut }}</strong> au <strong>{{ $date_fin }}</strong>.</p>
        </div>

        <div class="mb-6 text-sm">
            <p class="font-bold underline">ARTICLE 4 : Resiliation du contrat</p>
            <p>Le present contrat peut etre revise ou resilie en cas de necessite de service, de changement majeur dans les orientations de l'institution ou de non-respect des engagements convenus par l'une des parties.</p>
        </div>

        <div class="mb-8 text-right text-sm">
            <span>Ouagadougou, le {{ \Carbon\Carbon::parse($date_fin)->translatedFormat('d F Y') }}</span>
        </div>

        <div class="mt-12 flex justify-between text-sm">
            <div>
                <p>Signature du salarie :</p>
                <div class="mt-6 h-16 w-48 border-b border-slate-400"></div>
                <p class="mt-2">{{ $salarie_nom }}</p>
            </div>
            <div>
                <p>Signature du representant :</p>
                <div class="mt-6 h-16 w-48 border-b border-slate-400"></div>
                <p class="mt-2">{{ $institution_representant }}</p>
            </div>
        </div>

        <div class="mt-8 text-right text-xs">Page 1 sur 1</div>
        <div class="mt-2 text-left text-xs text-slate-400">Ceci est un exemplaire institutionnel</div>
    </div>
</main>
@endsection
