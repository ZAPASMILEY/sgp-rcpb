@php
    $cibleLabel = $ident->nom_prenom ?? ($evaluation->evaluable?->prenom.' '.$evaluation->evaluable?->nom ?? '—');
@endphp

@extends('layouts.rh')

@section('title', 'Évaluation — '.$cibleLabel.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace RH / Évaluation</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">{{ $cibleLabel }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Période : {{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}
                    &nbsp;·&nbsp; Évaluateur : {{ $evaluation->evaluateur?->name ?? '—' }}
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('rh.dashboard') }}?tab=evaluations"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
            </div>
        </div>
    </header>

    {{-- Scores synthèse --}}
    <section class="admin-panel px-6 py-5 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @php
            $scoreCards = [
                ['label' => 'Moy. subjectifs',  'value' => number_format((float)$evaluation->moyenne_subjectifs, 2, ',', ' ')],
                ['label' => 'Note subjectifs',   'value' => number_format((float)$evaluation->note_criteres_subjectifs, 2, ',', ' ')],
                ['label' => 'Moy. objectifs',    'value' => number_format((float)$evaluation->moyenne_objectifs, 2, ',', ' ')],
                ['label' => 'Note objectifs',    'value' => number_format((float)$evaluation->note_criteres_objectifs, 2, ',', ' ')],
                ['label' => 'NOTE FINALE /10',   'value' => number_format((float)$evaluation->note_finale, 2, ',', ' '), 'highlight' => true],
            ];
            @endphp
            @foreach($scoreCards as $sc)
                <div class="rounded-2xl border {{ ($sc['highlight'] ?? false) ? 'border-emerald-200 bg-emerald-50' : 'border-slate-100 bg-white' }} px-4 py-4 shadow-sm text-center">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] {{ ($sc['highlight'] ?? false) ? 'text-emerald-600' : 'text-slate-400' }}">{{ $sc['label'] }}</p>
                    <p class="mt-1 text-2xl font-black {{ ($sc['highlight'] ?? false) ? 'text-emerald-700' : 'text-slate-900' }}">{{ $sc['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Identification --}}
    <section class="admin-panel px-6 py-6 lg:px-8">
        <h2 class="text-lg font-black text-slate-900">Identification</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div><span class="text-xs uppercase text-slate-400">Année</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Nom & prénom</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->nom_prenom ?? '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Semestre</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->semestre ? 'Semestre '.$ident->semestre : '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Date d'évaluation</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->date_evaluation?->format('d/m/Y') ?? '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Emploi</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->emploi ?? '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Matricule</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->matricule ?? '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Entité</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->direction ?? '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Direction / Service</span><p class="mt-1 text-sm font-semibold text-slate-800">{{ $ident?->direction_service ?? '—' }}</p></div>
        </div>

        {{-- Formations & Expériences --}}
        <div class="mt-6 grid gap-6 xl:grid-cols-2">
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Période</th>
                            <th class="px-3 py-3">Formation</th>
                            <th class="px-3 py-3">Domaine</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('evaluations._formations_auto')
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Période</th>
                            <th class="px-3 py-3">Poste / Fonction</th>
                            <th class="px-3 py-3">Observations</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($ident->experiences ?? []) as $row)
                            <tr class="border-t border-slate-200">
                                <td class="px-3 py-2">{{ $row['periode'] ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $row['poste'] ?? '—' }}</td>
                                <td class="px-3 py-2">{{ $row['observations'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-3 text-slate-400">Aucune expérience renseignée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Critères objectifs --}}
    <section class="admin-panel px-6 py-6 lg:px-8">
        <h2 class="text-lg font-black text-slate-900">Critères objectifs</h2>
        <div class="mt-4 space-y-4">
            @forelse($objectiveCriteria as $criterion)
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                            @if($criterion->observation)
                                <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                            Note globale {{ number_format((float)$criterion->note_globale, 2, ',', ' ') }}
                        </span>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                    <th class="py-2 pr-4">Sous-critère</th>
                                    <th class="py-2 pr-4">Note /5</th>
                                    <th class="py-2">Observation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criterion->sousCriteres as $sub)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                        <td class="py-2 pr-4 font-semibold">{{ number_format((float)$sub->note, 2, ',', ' ') }}</td>
                                        <td class="py-2">{{ $sub->observation ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-400">Aucun critère objectif enregistré.</p>
            @endforelse
            <div class="flex justify-end">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne objectifs</p>
                        <p class="text-2xl font-black text-slate-900">{{ number_format((float)$evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note objectifs</p>
                        <p class="text-2xl font-black text-slate-900">{{ number_format((float)$evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Critères subjectifs --}}
    <section class="admin-panel px-6 py-6 lg:px-8">
        <h2 class="text-lg font-black text-slate-900">Critères subjectifs</h2>
        <div class="mt-4 space-y-4">
            @forelse($subjectiveCriteria as $criterion)
                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                            @if($criterion->observation)
                                <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                            @endif
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                            Note globale {{ number_format((float)$criterion->note_globale, 2, ',', ' ') }}
                        </span>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead>
                                <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                    <th class="py-2 pr-4">Sous-critère</th>
                                    <th class="py-2 pr-4">Note /5</th>
                                    <th class="py-2">Observation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($criterion->sousCriteres as $sub)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                        <td class="py-2 pr-4 font-semibold">{{ number_format((float)$sub->note, 2, ',', ' ') }}</td>
                                        <td class="py-2">{{ $sub->observation ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <p class="text-sm text-slate-400">Aucun critère subjectif enregistré.</p>
            @endforelse
            <div class="flex justify-end">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne subjectifs</p>
                        <p class="text-2xl font-black text-slate-900">{{ number_format((float)$evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note subjectifs</p>
                        <p class="text-2xl font-black text-slate-900">{{ number_format((float)$evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note finale /10</p>
                        <p class="text-2xl font-black text-emerald-700">{{ number_format((float)$evaluation->note_finale, 2, ',', ' ') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Plan d'amélioration --}}
    <section class="admin-panel px-6 py-6 lg:px-8">
        <h2 class="text-lg font-black text-slate-900">Plan d'amélioration</h2>
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points à améliorer</p>
                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->points_a_ameliorer ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Stratégies d'amélioration</p>
                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->strategies_amelioration ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'évaluateur</p>
                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaire ?: '—' }}</p>
            </div>
            @if($evaluation->commentaires_evalue)
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaires de l'évalué</p>
                <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue }}</p>
            </div>
            @endif
        </div>
        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div><span class="text-xs uppercase text-slate-400">Évalué</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: '—' }}</p></div>
            <div><span class="text-xs uppercase text-slate-400">Évaluateur</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: '—' }}</p></div>
        </div>
    </section>

    {{-- Statut (lecture seule) --}}
    <section class="admin-panel px-6 py-5 lg:px-8">
        @php
            $statutColors = [
                'brouillon' => 'border-slate-200 bg-slate-100 text-slate-600',
                'soumis'    => 'border-amber-200 bg-amber-50 text-amber-700',
                'valide'    => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'refuse'    => 'border-rose-200 bg-rose-50 text-rose-700',
            ];
            $statutLabels = ['brouillon' => 'Brouillon', 'soumis' => 'Soumise', 'valide' => 'Validée', 'refuse' => 'Refusée'];
        @endphp
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                <span class="mt-1 inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutColors[$evaluation->statut] ?? 'bg-slate-100 text-slate-500' }}">
                    {{ $statutLabels[$evaluation->statut] ?? ucfirst($evaluation->statut) }}
                </span>
            </div>
            <a href="{{ route('rh.dashboard') }}?tab=evaluations"
               class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
            </a>
        </div>
    </section>

</div>
</div>
@endsection
