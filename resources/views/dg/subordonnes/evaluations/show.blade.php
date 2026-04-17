@extends('layouts.dg')

@section('title', 'Evaluation du subordonne | '.config('app.name', 'SGP-RCPB'))

@php
    $ident = $evaluation->identification;
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto flex max-w-6xl flex-col gap-6">
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Evaluation subordonnee</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation de {{ $cibleLabel }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $periodeLabel }}</p>
                </div>
                <a href="{{ $backUrl }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Cible</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $cibleLabel }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Periode</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $periodeLabel }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}/10</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mention</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $mention }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($evaluation->statut) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $evaluation->evaluateur?->name ?? '-' }}</p>
                </div>
            </div>
        </section>

        @if ($ident)
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900">Identification</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div><span class="text-xs uppercase text-slate-500">Annee</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Nom et prenom</span><p class="mt-1 text-sm text-slate-800">{{ $ident->nom_prenom ?? '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Semestre</span><p class="mt-1 text-sm text-slate-800">{{ $ident->semestre ? 'Semestre '.$ident->semestre : '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Date d'evaluation</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('d/m/Y') ?? '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Emploi</span><p class="mt-1 text-sm text-slate-800">{{ $ident->emploi ?? '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Matricule</span><p class="mt-1 text-sm text-slate-800">{{ $ident->matricule ?? '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Entite</span><p class="mt-1 text-sm text-slate-800">{{ $ident->direction ?? '-' }}</p></div>
                    <div><span class="text-xs uppercase text-slate-500">Direction / Service</span><p class="mt-1 text-sm text-slate-800">{{ $ident->direction_service ?? '-' }}</p></div>
                </div>
            </section>
        @endif

        @if ($objectiveCriteria->isNotEmpty())
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
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                    Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($subjectiveCriteria->isNotEmpty())
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
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Synthese et commentaires</h2>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'evaluateur</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaire ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaires de l'evalue</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: '-' }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div><span class="text-xs uppercase text-slate-500">Evalue</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: $cibleLabel }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Evaluateur</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: ($evaluation->evaluateur?->name ?? '-') }}</p></div>
            </div>
        </section>
    </div>
</div>
@endsection
