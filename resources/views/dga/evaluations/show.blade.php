@extends('layouts.dga')

@section('title', 'Mon evaluation | '.config('app.name', 'SGP-RCPB'))

@php
    $ident = $evaluation->identification;
    $mentionClass = match ($mention) {
        'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
        'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
        default     => 'border-rose-200 bg-rose-50 text-rose-700',
    };
    $note = (float) $evaluation->note_finale;
    $notePercent = max(0, min(100, ($note / 10) * 100));
    $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto flex max-w-5xl flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace DGA / Mes evaluations</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation — {{ $periodeLabel }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Evaluateur : {{ $evaluation->evaluateur?->name ?? '-' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dga.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Telecharger PDF
                    </a>
                    <a href="{{ route('dga.mon-espace') }}?tab=evaluations" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Score summary --}}
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
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}<span class="text-sm font-semibold text-slate-500">/10</span></p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mention</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">{{ $mention }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $evaluation->evaluateur?->name ?? '-' }}</p>
                </div>
            </div>
        </section>

        {{-- Identification --}}
        @if ($ident)
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900">Identification</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Annee</p><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prenom</p><p class="mt-1 text-sm text-slate-800">{{ $ident->nom_prenom ?? '-' }}</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</p><p class="mt-1 text-sm text-slate-800">{{ $ident->semestre ? 'Semestre '.$ident->semestre : '-' }}</p></div>
                    <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Emploi</p><p class="mt-1 text-sm text-slate-800">{{ $ident->emploi ?? '-' }}</p></div>
                    @if ($ident->matricule)
                        <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Matricule</p><p class="mt-1 text-sm text-slate-800">{{ $ident->matricule }}</p></div>
                    @endif
                    @if ($ident->direction)
                        <div><p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entite</p><p class="mt-1 text-sm text-slate-800">{{ $ident->direction }}</p></div>
                    @endif
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
        @endif

        {{-- Criteres objectifs --}}
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
                                <span class="shrink-0 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-black text-emerald-700">
                                    Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                                </span>
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
                </div>
            </section>
        @endif

        {{-- Criteres subjectifs --}}
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
                            <span class="shrink-0 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-black text-slate-700">
                                Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                            </span>
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
                    @php
                        $templates = \App\Models\SubjectiveCriteriaTemplate::query()
                            ->with('subcriteria')
                            ->where('is_active', true)
                            ->orderBy('ordre')
                            ->get();
                    @endphp
                    @foreach ($templates as $template)
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
            </div>
        </section>

        {{-- Synthese et commentaires --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Synthese et commentaires</h2>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                @if ($evaluation->points_a_ameliorer)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points a ameliorer</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->points_a_ameliorer }}</p>
                    </div>
                @endif
                @if ($evaluation->strategies_amelioration)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Strategies d'amelioration</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->strategies_amelioration }}</p>
                    </div>
                @endif
                @if ($evaluation->commentaires_evalue)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mes commentaires</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue }}</p>
                    </div>
                @endif
                @if ($evaluation->commentaire)
                    <div class="space-y-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'evaluateur</p>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaire }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-6 border-t border-slate-200 pt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evalue</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: $user->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: ($evaluation->evaluateur?->name ?? '-') }}</p>
                </div>
            </div>
        </section>

        {{-- Commentaire de l'évalué --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Mes commentaires</h2>
            <p class="mt-1 text-sm text-slate-500">En tant qu'évalué, vous pouvez saisir vos commentaires sur cette évaluation tant qu'elle n'est pas validée.</p>

            @if ($evaluation->statut === 'valide')
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Commentaire enregistré (évaluation validée)</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: 'Aucun commentaire saisi.' }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('dga.evaluations.commentaire', $evaluation) }}" class="mt-4 space-y-3">
                    @csrf
                    <textarea
                        name="commentaires_evalue"
                        rows="6"
                        class="ent-input w-full"
                        placeholder="Saisissez vos commentaires sur cette évaluation..."
                    >{{ old('commentaires_evalue', $evaluation->commentaires_evalue) }}</textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="ent-btn ent-btn-primary">Enregistrer mon commentaire</button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Actions : Accepter / Refuser (uniquement si statut = soumis) --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut de l'evaluation</p>
                    <span class="mt-1 inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                @if ($evaluation->statut === 'soumis')
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('dga.evaluations.statut', $evaluation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="accepter">
                            <button type="submit" class="ent-btn ent-btn-primary">
                                <i class="fas fa-check mr-2"></i>Accepter l'evaluation
                            </button>
                        </form>
                        <form method="POST" action="{{ route('dga.evaluations.statut', $evaluation) }}"
                              onsubmit="return confirm('Confirmer le refus de cette evaluation ?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="refuser">
                            <button type="submit" class="ent-btn bg-rose-600 text-white hover:bg-rose-700">
                                <i class="fas fa-times mr-2"></i>Refuser l'evaluation
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>
@endsection
