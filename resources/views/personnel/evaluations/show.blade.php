@extends('layouts.personnel')
@section('title', 'Mon évaluation | ' . config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $notePercent  = max(0, min(100, ((float) $note / 10) * 100));
    $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
    $mentionClass = match ($mention) {
        'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
        'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
        default     => 'border-rose-200 bg-rose-50 text-rose-700',
    };
@endphp
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- En-tête --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace / Mes évaluations</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Évaluation — {{ $anneeEval }} · Semestre {{ $semestreEval }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Évaluateur : {{ $evaluation->evaluateur?->name ?? '—' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('personnel.evaluations.pdf', $evaluation) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300">
                        <i class="fas fa-file-pdf text-xs text-rose-500"></i> Télécharger PDF
                    </a>
                    <a href="{{ route('personnel.dashboard') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300">
                        <i class="fas fa-arrow-left text-xs"></i> Mon espace
                    </a>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Bandeau accepter / refuser (uniquement si soumise) --}}
        @if ($canValidate)
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Validation requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">Consultez votre fiche d'évaluation puis acceptez ou refusez-la.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('personnel.evaluations.statut', $evaluation) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="refuser">
                    <button type="submit"
                            onclick="return confirm('Refuser cette évaluation ?')"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                        <i class="fas fa-times text-xs"></i> Refuser
                    </button>
                </form>
                <form action="{{ route('personnel.evaluations.statut', $evaluation) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit"
                            onclick="return confirm('Accepter cette évaluation ?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-slate-800">
                        <i class="fas fa-check text-xs"></i> Accepter
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Résumé des scores --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Période</p>
                    <p class="mt-2 text-sm text-slate-800">
                        {{ $evaluation->date_debut->format('d/m/Y') }} – {{ $evaluation->date_fin->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ number_format($note, 2, ',', ' ') }}<span class="text-sm font-semibold text-slate-500">/10</span>
                    </p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                <span class="inline-flex items-center gap-1.5 rounded-full border {{ $mentionClass }} px-3 py-1 text-xs font-black">
                    {{ $mention }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border {{ $statusClass }} px-3 py-1 text-xs font-black">
                    {{ $statusLabel }}
                </span>
            </div>
        </section>

        {{-- Critères objectifs --}}
        @if ($objectiveCriteria->isNotEmpty())
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Critères objectifs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4 text-left">Objectif</th>
                            <th class="py-3 pr-4 text-center">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($objectiveCriteria as $critere)
                            <tr>
                                <td class="py-3 pr-4 font-medium">{{ $critere->libelle }}</td>
                                <td class="py-3 pr-4 text-center font-black text-slate-900">
                                    @foreach ($critere->sousCriteres as $sc)
                                        {{ $sc->note }}/5
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Critères subjectifs --}}
        @if ($subjectiveCriteria->isNotEmpty())
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Critères subjectifs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4 text-left">Critère</th>
                            <th class="py-3 pr-4 text-left">Sous-critère</th>
                            <th class="py-3 pr-4 text-center">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($subjectiveCriteria as $critere)
                            @foreach ($critere->sousCriteres as $i => $sc)
                                <tr>
                                    @if ($i === 0)
                                        <td class="py-3 pr-4 font-semibold" rowspan="{{ $critere->sousCriteres->count() }}">
                                            {{ $critere->libelle }}
                                        </td>
                                    @endif
                                    <td class="py-3 pr-4 text-slate-600">{{ $sc->libelle }}</td>
                                    <td class="py-3 pr-4 text-center font-black text-slate-900">{{ $sc->note }}/5</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Commentaires --}}
        @if ($evaluation->commentaire || $evaluation->points_a_ameliorer || $evaluation->strategies_amelioration)
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Commentaires</h2>
            @if ($evaluation->commentaire)
                <div class="mb-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Commentaire général</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->commentaire }}</p>
                </div>
            @endif
            @if ($evaluation->points_a_ameliorer)
                <div class="mb-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Points à améliorer</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->points_a_ameliorer }}</p>
                </div>
            @endif
            @if ($evaluation->strategies_amelioration)
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Stratégies d'amélioration</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->strategies_amelioration }}</p>
                </div>
            @endif
        </section>
        @endif

    </div>
</div>
@endsection
