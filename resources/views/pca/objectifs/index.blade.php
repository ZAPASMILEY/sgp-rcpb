@extends('layouts.pca')

@section('title', 'Objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA / Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Objectifs</h1>
                        <p class="mt-2 text-sm text-slate-600">Objectifs de votre entite et de ses directeurs.</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="status-pill">Total {{ $objectifs->total() }}</span>
                            @if ($filters['search'])
                                <span class="status-pill">Filtres actifs</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
                        Reinitialiser
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div id="pca-objectifs-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-objectifs-status-message')?.remove(), 3000);</script>
            @endif

            <section class="admin-panel px-6 py-6 lg:px-8">
                <form method="GET" action="{{ route('pca.objectifs.index') }}" class="ent-filters mb-6 grid gap-3 lg:grid-cols-[1.2fr_auto_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input id="search" name="search" type="text" value="{{ $filters['search'] }}" placeholder="Commentaire, echeance" class="ent-input">
                    </div>
                    <button type="submit" class="ent-btn ent-btn-primary">Filtrer</button>
                    <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Ajouter un objectif" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="ent-table-wrap overflow-x-auto">
                    <table class="ent-table ent-table--stack text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Echeance</th>
                                <th>Type</th>
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
                                    $typeLabel = $assignable instanceof \App\Models\Entite ? 'Entite' : ($assignable instanceof \App\Models\Direction ? 'Direction' : '-');
                                    $cibleLabel = $assignable?->nom ?? '—';
                                    $progressTextClasses = $progressValue > 50
                                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                                        : 'text-rose-700 bg-rose-50 border-rose-200';
                                @endphp
                                <tr>
                                    <td data-label="#"><p class="ent-identity">{{ ($objectifs->firstItem() ?? 1) + $loop->index }}</p></td>
                                    <td data-label="Date"><p class="ent-identity">{{ $objectif->date }}</p></td>
                                    <td data-label="Echeance">
                                        <p class="ent-identity">{{ $objectif->date_echeance }}</p>
                                        @if ($isExpired)
                                            <p class="ent-subtext text-rose-600">Echeance depassee</p>
                                        @endif
                                    </td>
                                    <td data-label="Type"><p class="ent-subtext">{{ $typeLabel }}</p></td>
                                    <td data-label="Cible"><p class="ent-identity">{{ $cibleLabel }}</p></td>
                                    <td data-label="Avancement">
                                        <div class="min-w-[180px] space-y-2">
                                            <div class="flex items-center gap-2">
                                                @if (! $isEvaluationLocked)
                                                    <form method="POST" action="{{ route('pca.objectifs.progress', $objectif) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" @disabled($isExpired) class="ent-btn ent-btn-soft inline-flex h-7 min-w-10 items-center justify-center px-2 text-xs disabled:cursor-not-allowed disabled:opacity-50" aria-label="Diminuer de 10%">-10%</button>
                                                    </form>
                                                @endif
                                                <p class="min-w-14 rounded-full border px-2 py-1 text-center text-sm font-semibold {{ $progressTextClasses }}">{{ $objectif->avancement_percentage }}%</p>
                                                @if ($isEvaluationLocked)
                                                    <p class="inline-flex w-fit rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Verrouillee</p>
                                                @endif
                                                @if (! $isEvaluationLocked)
                                                    <form method="POST" action="{{ route('pca.objectifs.progress', $objectif) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" @disabled($isExpired) class="ent-btn ent-btn-primary inline-flex h-7 min-w-10 items-center justify-center px-2 text-xs disabled:cursor-not-allowed disabled:opacity-50" aria-label="Augmenter de 10%">+10%</button>
                                                    </form>
                                                @endif
                                            </div>
                                            @if ($isExpired && ! $isEvaluationLocked)
                                                <p class="text-xs font-medium text-rose-600">Evolution verrouillee apres echeance</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td data-label="Commentaire"><p class="ent-subtext">{{ \Illuminate\Support\Str::limit($objectif->commentaire, 90) }}</p></td>
                                    <td data-label="Actions" class="whitespace-nowrap">
                                        <div class="ent-actions flex-nowrap">
                                            <a href="{{ route('pca.objectifs.show', $objectif) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir" aria-label="Voir l'objectif">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            <form method="POST" action="{{ route('pca.objectifs.destroy', $objectif) }}" onsubmit="return confirm('Supprimer cet objectif ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ent-btn ent-btn-destructive inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer" aria-label="Supprimer l'objectif">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-sm text-slate-500">Aucun objectif trouve.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($objectifs->hasPages())
                    <div class="mt-6">{{ $objectifs->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
