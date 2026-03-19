@extends('layouts.pca')

@section('title', 'Evaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA / Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluations</h1>
                        <p class="mt-2 text-sm text-slate-600">Evaluations de votre entite et de ses directeurs.</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="status-pill">Total {{ $evaluations->total() }}</span>
                            @if ($filters['search'] || $filters['statut'])
                                <span class="status-pill">Filtres actifs</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
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
                <form method="GET" action="{{ route('pca.evaluations.index') }}" class="ent-filters mb-6 grid gap-3 lg:grid-cols-[1fr_auto_auto_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input id="search" name="search" type="text" value="{{ $filters['search'] }}" placeholder="Nom de l'entite ou du directeur" class="ent-input">
                    </div>
                    <div class="space-y-2">
                        <label for="statut" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</label>
                        <select id="statut" name="statut" class="ent-select">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                            <option value="soumis" @selected($filters['statut'] === 'soumis')>Soumis</option>
                            <option value="valide" @selected($filters['statut'] === 'valide')>Valide</option>
                        </select>
                    </div>
                    <button type="submit" class="ent-btn ent-btn-primary">Filtrer</button>
                    <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Ajouter une evaluation" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="ent-table-wrap overflow-x-auto">
                    <table class="ent-table ent-table--stack text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Cible</th>
                                <th>Periode</th>
                                <th>Note finale</th>
                                <th>Mention</th>
                                <th>Statut</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($evaluations as $evaluation)
                                @php
                                    $evaluable = $evaluation->evaluable;
                                    $role = $evaluation->evaluable_role ?? 'entity';
                                    $typeLabel = $evaluation->evaluable_type === \App\Models\Direction::class && $role === 'manager'
                                        ? 'Directeur'
                                        : ($evaluation->evaluable_type === \App\Models\Entite::class ? 'Entite' : '-');
                                    $cibleLabel = $evaluable instanceof \App\Models\Direction && $role === 'manager'
                                        ? ($evaluable->directeur_nom ?: 'Directeur non renseigne')
                                        : ($evaluable?->nom ?? '—');
                                    $mention = $evaluation->note_finale < 50 ? 'Insuffisant'
                                        : ($evaluation->note_finale < 70 ? 'Passable'
                                        : ($evaluation->note_finale < 85 ? 'Bien' : 'Excellent'));
                                    $mentionClass = match ($mention) {
                                        'Excellent' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                        'Bien'      => 'text-sky-700 bg-sky-50 border-sky-200',
                                        'Passable'  => 'text-amber-700 bg-amber-50 border-amber-200',
                                        default     => 'text-rose-700 bg-rose-50 border-rose-200',
                                    };
                                    $statusClass = $evaluation->statut === 'valide'
                                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                                        : ($evaluation->statut === 'soumis'
                                            ? 'text-amber-700 bg-amber-50 border-amber-200'
                                            : 'text-slate-700 bg-slate-100 border-slate-200');
                                @endphp
                                <tr>
                                    <td data-label="#">{{ $evaluation->id }}</td>
                                    <td data-label="Type">{{ $typeLabel }}</td>
                                    <td data-label="Cible">{{ $cibleLabel }}</td>
                                    <td data-label="Periode" class="whitespace-nowrap">{{ $evaluation->date_debut->format('d/m/Y') }} – {{ $evaluation->date_fin->format('d/m/Y') }}</td>
                                    <td data-label="Note finale">{{ $evaluation->note_finale }}%</td>
                                    <td data-label="Mention">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $mentionClass }}">
                                            {{ $mention }}
                                        </span>
                                    </td>
                                    <td data-label="Statut">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                            {{ ucfirst($evaluation->statut) }}
                                        </span>
                                    </td>
                                    <td data-label="Actions">
                                        <div class="ent-actions justify-end gap-2">
                                            <a href="{{ route('pca.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir" aria-label="Voir l'evaluation">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            @if ($evaluation->statut !== 'valide')
                                                <form method="POST" action="{{ route('pca.evaluations.destroy', $evaluation) }}" onsubmit="return confirm('Supprimer cette evaluation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ent-btn ent-btn-destructive inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer" aria-label="Supprimer">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-sm text-slate-500">Aucune evaluation trouvee.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($evaluations->hasPages())
                    <div class="mt-6">{{ $evaluations->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
