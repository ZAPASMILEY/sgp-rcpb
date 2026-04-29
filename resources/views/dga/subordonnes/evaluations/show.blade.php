@extends('layouts.dga')

@section('title', 'Evaluation subordonne #'.$evaluation->id.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DGA / Évaluation subordonné</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation de {{ $cibleLabel }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $periodeLabel }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dga.sub-evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>PDF
                    </a>
                    <a href="{{ $backUrl }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Notes --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Periode</p>
                    <p class="mt-2 text-sm text-slate-800">{{ $evaluation->date_debut->format('d/m/Y') }} - {{ $evaluation->date_fin->format('d/m/Y') }}</p>
                </div>
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
            <div class="mt-6 grid gap-4 md:grid-cols-3">
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
                    <p class="mt-2 text-sm text-slate-700">{{ $evaluation->evaluateur?->name ?? '-' }}</p>
                </div>
            </div>
        </section>

        {{-- Identification --}}
        @php $ident = $evaluation->identification; @endphp
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Identification</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div><span class="text-xs uppercase text-slate-500">Annee</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Nom et prenom</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->nom_prenom ?? '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Semestre</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->semestre ? 'Semestre '.$ident->semestre : '-' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Date evaluation</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('d/m/Y') ?? '-' }}</p></div>
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
                            @forelse (($ident?->formations ?? []) as $row)
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
                            @forelse (($ident?->experiences ?? []) as $row)
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

        {{-- Criteres objectifs --}}
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

        {{-- Criteres subjectifs --}}
        @php
            $subjectiveTemplates = $subjectiveCriteria->isEmpty()
                ? \App\Models\SubjectiveCriteriaTemplate::query()->with('subcriteria')->where('is_active', true)->orderBy('ordre')->get()
                : collect();
        @endphp
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Criteres subjectifs</h2>
            <div class="mt-4 space-y-4">
                @forelse ($subjectiveCriteria as $criterion)
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
                @empty
                    @foreach ($subjectiveTemplates as $template)
                        <article class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-700">{{ $template->titre }}</h3>
                                    @if ($template->description)
                                        <p class="mt-1 text-sm text-slate-400">{{ $template->description }}</p>
                                    @endif
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-400">Non note</span>
                            </div>
                            <div class="mt-4 overflow-x-auto">
                                <table class="min-w-full text-left text-sm text-slate-500">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-400">
                                            <th class="py-2 pr-4">Sous-critere</th>
                                            <th class="py-2 pr-4">Note /5</th>
                                            <th class="py-2">Observation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($template->subcriteria as $sub)
                                            <tr class="border-b border-slate-100">
                                                <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                                <td class="py-2 pr-4 font-semibold text-slate-300">—</td>
                                                <td class="py-2 text-slate-300">—</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </article>
                    @endforeach
                @endforelse
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
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note finale</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Plan d'amelioration --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Plan d'amelioration</h2>
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
            </div>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div><span class="text-xs uppercase text-slate-500">Evalue</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: $cibleLabel }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Evaluateur</span><p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: ($evaluation->evaluateur?->name ?? '-') }}</p></div>
            </div>
            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                @if ($evaluation->statut === 'brouillon')
                    <form method="POST" action="{{ route('dga.sub-evaluations.submit', $evaluation) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="ent-btn ent-btn-primary">Soumettre</button>
                    </form>
                @endif
                @if ($evaluation->statut !== 'valide')
                    <form method="POST" action="{{ route('dga.sub-evaluations.destroy', $evaluation) }}" onsubmit="return confirm('Supprimer cette evaluation ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ent-btn ent-btn-destructive">Supprimer</button>
                    </form>
                @endif
            </div>
        </section>

        {{-- Commentaire de l'evalue (lecture seule pour le DG) --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Commentaire de l'evalue</h2>
            <p class="mt-1 text-sm text-slate-500">Commentaire saisi par {{ $cibleLabel }} sur cette evaluation.</p>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: 'Aucun commentaire saisi par l\'evalue.' }}</p>
            </div>
        </section>

    </div>
</div>
@endsection
