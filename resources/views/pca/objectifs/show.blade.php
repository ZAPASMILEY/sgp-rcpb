@extends('layouts.pca')

@section('title', "Fiche d'objectifs | ".config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6 sm:p-8">
                <h1 class="mb-4 text-2xl font-bold">Fiche d'objectifs : {{ $fiche->titre }}</h1>

                <div class="mb-4 flex flex-wrap gap-4">
                    <div><strong>Annee :</strong> {{ $fiche->annee }}</div>
                    <div><strong>Date :</strong> {{ $fiche->date }}</div>
                    <div><strong>Echeance :</strong> {{ $fiche->date_echeance }}</div>
                    <div><strong>Avancement :</strong> {{ $fiche->avancement_percentage }}</div>
                    <div>
                        <strong>Statut :</strong>
                        @if ($fiche->statut === 'acceptee')
                            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Acceptee</span>
                        @elseif ($fiche->statut === 'refusee')
                            <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Refusee</span>
                        @else
                            <span class="inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">En attente</span>
                        @endif
                    </div>
                </div>

                <hr class="my-4">

                <h2 class="mb-2 text-lg font-bold">Liste des objectifs</h2>
                <ul class="ml-8 list-disc space-y-2">
                    @foreach($fiche->objectifs as $objectif)
                        <li>{{ $objectif->description }}</li>
                    @endforeach
                </ul>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('pca.objectifs.contrat', $fiche) }}" class="ent-btn ent-btn-soft">Voir le contrat</a>
                    <a href="{{ route('pca.objectifs.contrat.download', $fiche) }}" class="ent-btn ent-btn-primary">Telecharger le contrat PDF</a>
                    <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft">Retour a la liste</a>
                </div>
            </section>
        </div>
    </div>
@endsection
