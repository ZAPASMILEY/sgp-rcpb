@extends('layouts.subordonne')

@section('title', 'Mon evaluation | '.config('app.name', 'SGP-RCPB'))

@php
    $ident = $evaluation->identification;
    $mentionClass = match ($mention) {
        'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
        'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
        default     => 'border-rose-200 bg-rose-50 text-rose-700',
    };
    $statusClass = match ($evaluation->statut) {
        'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
        default  => 'border-slate-200 bg-slate-100 text-slate-700',
    };
    $statusLabel = match ($evaluation->statut) {
        'valide' => 'Validee',
        'soumis' => 'Soumise',
        default  => 'Brouillon',
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
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace / Mes evaluations</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation — {{ $periodeLabel }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Evaluateur : {{ $evaluation->evaluateur?->name ?? '-' }}</p>
                </div>
                <a href="{{ route('subordonne.mon-espace') }}?tab=evaluations" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        {{-- Note summary --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Periode</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $periodeLabel }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($note, 2, ',', ' ') }}<span class="text-sm font-semibold text-slate-500">/10</span></p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mention</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">
                            {{ $mention }}
                        </span>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

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
            </section>
        @endif

        @if ($objectiveCriteria->isNotEmpty())
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900">Criteres objectifs</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($objectiveCriteria as $criterion)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                <span class="shrink-0 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-black text-emerald-700">
                                    {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}/5
                                </span>
                            </div>
                            @if ($criterion->observation)
                                <p class="mt-2 text-sm text-slate-500">{{ $criterion->observation }}</p>
                            @endif
                            @if ($criterion->sousCriteres->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    @foreach ($criterion->sousCriteres as $sub)
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                                            <span class="text-slate-700">{{ $sub->libelle }}</span>
                                            <span class="font-bold text-slate-900">{{ $sub->note }}/5</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($subjectiveCriteria->isNotEmpty())
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900">Criteres subjectifs</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($subjectiveCriteria as $criterion)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                <span class="shrink-0 rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-black text-slate-700">
                                    {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}/5
                                </span>
                            </div>
                            @if ($criterion->observation)
                                <p class="mt-2 text-sm text-slate-500">{{ $criterion->observation }}</p>
                            @endif
                            @if ($criterion->sousCriteres->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    @foreach ($criterion->sousCriteres as $sub)
                                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                                            <span class="text-slate-700">{{ $sub->libelle }}</span>
                                            <span class="font-bold text-slate-900">{{ $sub->note }}/5</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

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

    </div>
</div>
@endsection
