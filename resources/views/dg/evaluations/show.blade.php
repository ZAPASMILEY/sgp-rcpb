@php
    $cibleLabel = $evaluation->identification->nom_prenom ?? 'DG';
    $ident = $evaluation->identification ?? null;
    $objectiveCriteria = $evaluation->criteres->where('type', 'objectif') ?? collect();
        $subjectiveCriteria = $evaluation->criteres->where('type', 'subjectif') ?? collect();
@endphp


@section('actions')
    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
        @if($evaluation->statut === null || $evaluation->statut === 'en_attente' || $evaluation->statut === '')
            <form method="POST" action="{{ route('dg.evaluations.statut', $evaluation) }}" class="flex gap-4">
                @csrf
                @method('PATCH')
                <button name="statut" value="acceptee" class="ent-btn ent-btn-primary">Accepter</button>
                <button name="statut" value="refusee" class="ent-btn ent-btn-destructive">Refuser</button>
            </form>
        @elseif($evaluation->statut === 'acceptee')
            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Acceptée</span>
        @elseif($evaluation->statut === 'refusee')
            <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Refusée</span>
        @endif
    </div>
@endsection



@extends('layouts.dg')

@section('title', 'Evaluation DG | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-6xl flex flex-col gap-6">
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Evaluation</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $cibleLabel }} | Période {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</p>
                </div>
                <a href="{{ route('dg.mon-espace') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</p>
                </div>
            </div>
        </section>



        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Identification</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div><span class="text-xs uppercase text-slate-500">Annee</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Nom et prenom</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->nom_prenom ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Semestre</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->semestre ? 'Semestre '.$ident->semestre : '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Date d'evaluation</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('d/m/Y') ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Emploi</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->emploi ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Matricule</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->matricule ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Entite</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->direction ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Direction / Service</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->direction_service ?? '-' }}</p></div>
            </div>
            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Periode</th>
                                <th class="px-3 py-3">Formation</th>
                                <th class="px-3 py-3">Domaine</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($ident->formations ?? []) as $row)
                                <tr class="border-t border-slate-200">
                                    <td class="px-3 py-2">{{ $row['periode'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $row['libelle'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $row['domaine'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-3 text-slate-400">Aucune formation renseignee.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Periode</th>
                                <th class="px-3 py-3">Poste ou fonction</th>
                                <th class="px-3 py-3">Observations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($ident->experiences ?? []) as $row)
                                <tr class="border-t border-slate-200">
                                    <td class="px-3 py-2">{{ $row['periode'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $row['poste'] ?? '-' }}</td>
                                    <td class="px-3 py-2">{{ $row['observations'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-3 text-slate-400">Aucune experience renseignee.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Criteres objectifs</h2>
            <div class="mt-4 space-y-4">
                @foreach ($objectiveCriteria as $criterion)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                @if ($criterion->observation)
                                    <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                                @endif
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}</span>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <th class="py-2 pr-4">Sous-critere</th>
                                        <th class="py-2 pr-4">Note /5</th>
                                        <th class="py-2">Observation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($criterion->sousCriteres as $subcriterion)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 pr-4">{{ $subcriterion->libelle }}</td>
                                            <td class="py-2 pr-4 font-semibold">{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                                            <td class="py-2">{{ $subcriterion->observation ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endforeach
                <div class="mt-6 flex flex-wrap gap-6 justify-end">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne objectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note objectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Criteres subjectifs</h2>
            <div class="mt-4 space-y-4">
                @foreach ($subjectiveCriteria as $criterion)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                @if ($criterion->observation)
                                    <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                                @endif
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}</span>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <th class="py-2 pr-4">Sous-critere</th>
                                        <th class="py-2 pr-4">Note /5</th>
                                        <th class="py-2">Observation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($criterion->sousCriteres as $subcriterion)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 pr-4">{{ $subcriterion->libelle }}</td>
                                            <td class="py-2 pr-4 font-semibold">{{ number_format((float) $subcriterion->note, 2, ',', ' ') }}</td>
                                            <td class="py-2">{{ $subcriterion->observation ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endforeach
                <div class="mt-6 flex flex-wrap gap-6 justify-end">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne subjectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note subjectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note totale d'évaluation</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Plan d'amelioration et signatures</h2>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points a ameliorer</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->points_a_ameliorer ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Strategies d'amelioration</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->strategies_amelioration ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaires de l'evalué</p>
                    @if($evaluation->statut === 'acceptee')
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: '-' }}</p>
                    @else
                        <form method="POST" action="{{ route('dg.evaluations.statut', $evaluation) }}" class="mt-2 flex flex-col gap-2">
                            @csrf
                            @method('PATCH')
                            <textarea name="commentaires_evalue" class="ent-input" rows="3" placeholder="Saisir vos commentaires ici...">{{ old('commentaires_evalue', $evaluation->commentaires_evalue) }}</textarea>
                            <div class="flex gap-4 mt-2">
                                <button name="statut" value="acceptee" class="ent-btn ent-btn-primary">Accepter</button>
                                <button name="statut" value="refusee" class="ent-btn ent-btn-destructive">Refuser</button>
                            </div>
                        </form>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'évaluateur</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaire ?? '-' }}</p>
                </div>
                </div>
            </div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div><span class="text-xs uppercase text-slate-500">Evalue</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Evaluateur</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: '-' }}</p></div>
            </div>
        </section>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6 mt-10">
        @if($evaluation->statut === null || $evaluation->statut === 'soumis' || $evaluation->statut === '')
            <form method="POST" action="{{ route('dg.evaluations.statut', $evaluation) }}" class="flex gap-4">
                @csrf
                @method('PATCH')
                <button name="statut" value="acceptee" class="ent-btn ent-btn-primary">Accepter</button>
                <button name="statut" value="refusee" class="ent-btn ent-btn-destructive">Refuser</button>
            </form>
        @elseif($evaluation->statut === 'acceptee')
            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Acceptée</span>
        @elseif($evaluation->statut === 'refusee')
            <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Refusée</span>
        @endif
        <a href="{{ route('dg.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft">Exporter PDF</a>
    </div>

@endsection
