@extends('layouts.app')

@section('title', 'Objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Objectifs</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des objectifs assignes.</p>
                    </div>
                    <a href="{{ route('admin.objectifs.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
                        Reinitialiser
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel px-6 py-6 lg:px-8">
                <form method="GET" action="{{ route('admin.objectifs.index') }}" class="ent-filters mb-6 grid gap-3 lg:grid-cols-[1.2fr_auto_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $filters['search'] }}"
                            placeholder="Cible, commentaire, echeance"
                            class="ent-input"
                        >
                    </div>
                    <button type="submit" class="ent-btn ent-btn-primary">Filtrer</button>
                    <a href="{{ route('admin.objectifs.create') }}" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="ent-table-wrap overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Echeance</th>
                                <th>Type cible</th>
                                <th>Cible</th>
                                <th>Avancement</th>
                                <th>Commentaire</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($objectifs as $objectif)
                                @php
                                    $assignable = $objectif->assignable;
                                    $progressValue = (int) $objectif->avancement_percentage;
                                    $isExpired = \Carbon\Carbon::parse($objectif->date_echeance)->isBefore(today());
                                    $isEvaluationLocked = (bool) ($objectif->is_evaluation_locked ?? false);
                                    $typeLabel = $assignable instanceof \App\Models\Entite ? 'Entite' : (
                                        $assignable instanceof \App\Models\Direction ? 'Direction' : (
                                            $assignable instanceof \App\Models\Service ? 'Service' : (
                                                $assignable instanceof \App\Models\Agent ? 'Agent' : '-'
                                            )
                                        )
                                    );
                                    $cibleLabel = $assignable instanceof \App\Models\Agent
                                        ? trim($assignable->prenom.' '.$assignable->nom)
                                        : ($assignable?->nom ?? '-');
                                    $progressBarClasses = $progressValue > 50
                                        ? '[&::-webkit-progress-value]:bg-emerald-600 [&::-moz-progress-bar]:bg-emerald-600'
                                        : '[&::-webkit-progress-value]:bg-rose-500 [&::-moz-progress-bar]:bg-rose-500';
                                    $progressTextClasses = $progressValue > 50
                                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                                        : 'text-rose-700 bg-rose-50 border-rose-200';
                                @endphp
                                <tr>
                                    <td><p class="ent-identity">{{ ($objectifs->firstItem() ?? 1) + $loop->index }}</p></td>
                                    <td><p class="ent-identity">{{ $objectif->date }}</p></td>
                                    <td>
                                        <p class="ent-identity">{{ $objectif->date_echeance }}</p>
                                        @if ($isExpired)
                                            <p class="ent-subtext text-rose-600">Echeance depassee</p>
                                        @endif
                                    </td>
                                    <td><p class="ent-subtext">{{ $typeLabel }}</p></td>
                                    <td><p class="ent-identity">{{ $cibleLabel }}</p></td>
                                    <td>
                                        <div class="min-w-[180px] space-y-2">
                                            <div class="flex items-center gap-2">
                                                @if (! $isEvaluationLocked)
                                                    <form method="POST" action="{{ route('admin.objectifs.progress', $objectif) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" @disabled($isExpired) class="ent-btn ent-btn-soft inline-flex h-7 min-w-10 items-center justify-center px-2 text-xs disabled:cursor-not-allowed disabled:opacity-50" aria-label="Diminuer l'avancement de 10%">-10%</button>
                                                    </form>
                                                @endif
                                                <p class="min-w-14 rounded-full border px-2 py-1 text-center text-sm font-semibold {{ $progressTextClasses }}">{{ $objectif->avancement_percentage }}%</p>
                                                @if ($isEvaluationLocked)
                                                    <p class="inline-flex w-fit rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Verrouillee</p>
                                                @endif
                                                @if (! $isEvaluationLocked)
                                                    <form method="POST" action="{{ route('admin.objectifs.progress', $objectif) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" @disabled($isExpired) class="ent-btn ent-btn-primary inline-flex h-7 min-w-10 items-center justify-center px-2 text-xs disabled:cursor-not-allowed disabled:opacity-50" aria-label="Augmenter l'avancement de 10%">+10%</button>
                                                    </form>
                                                @endif
                                            </div>
                                            @if ($isExpired && ! $isEvaluationLocked)
                                                <p class="text-xs font-medium text-rose-600">Evolution verrouillee apres echeance</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td><p class="ent-subtext">{{ \Illuminate\Support\Str::limit($objectif->commentaire, 90) }}</p></td>
                                    <td class="whitespace-nowrap">
                                        <div class="ent-actions flex-nowrap">
                                            <a href="{{ route('admin.objectifs.show', $objectif) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir l'objectif" aria-label="Voir l'objectif">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.objectifs.edit', $objectif) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier l'objectif" aria-label="Modifier l'objectif">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.objectifs.destroy', $objectif) }}" onsubmit="return confirm('Supprimer cet objectif ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer l'objectif" aria-label="Supprimer l'objectif">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18M9.75 6.75V5.625A1.875 1.875 0 0 1 11.625 3.75h.75A1.875 1.875 0 0 1 14.25 5.625V6.75m3.75 0V18A2.25 2.25 0 0 1 15.75 20.25h-7.5A2.25 2.25 0 0 1 6 18V6.75h12Zm-8.25 4.5v5.25m4.5-5.25v5.25" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-sm text-slate-500">
                                        Aucun objectif enregistre.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($objectifs->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $objectifs->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection