@extends('layouts.app')

@section('title', 'Evaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluations</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des evaluations des entites, directions, services et agents.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">{{ $evaluations->total() }} évaluation(s)</div>
                        <a href="{{ route('admin.evaluations.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
                            Reinitialiser
                        </a>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel px-6 py-6 lg:px-8">
                <form method="GET" action="{{ route('admin.evaluations.index') }}" class="ent-filters mb-6 grid gap-3 lg:grid-cols-[1fr_auto_auto_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input id="search" name="search" type="text" value="{{ $filters['search'] }}" placeholder="Nom de l'entite, direction, service ou agent" class="ent-input">
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
                    <a href="{{ route('admin.evaluations.create') }}" data-open-create-modal data-modal-title="Ajouter une evaluation" class="ent-btn ent-btn-primary text-center">Ajouter</a>
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
                                    $role = strtolower($evaluation->evaluable_role ?? 'entity');
                                    if ($evaluation->evaluable_type === \App\Models\Direction::class && $role === 'manager') {
                                        $typeLabel = 'Directeur';
                                    } elseif ($evaluation->evaluable_type === \App\Models\Service::class && $role === 'manager') {
                                        $typeLabel = 'Chef de service';
                                    } else {
                                        $typeMap = [
                                            \App\Models\Entite::class    => 'Entite',
                                            \App\Models\Direction::class => 'Direction',
                                            \App\Models\Service::class   => 'Service',
                                            \App\Models\Agent::class     => 'Agent',
                                        ];
                                        $typeLabel = $typeMap[$evaluation->evaluable_type] ?? '-';
                                    }
                                    if ($evaluable instanceof \App\Models\Agent) {
                                        $cibleLabel = trim($evaluable->prenom.' '.$evaluable->nom);
                                    } elseif ($evaluable instanceof \App\Models\Direction) {
                                        $cibleLabel = $role === 'manager'
                                            ? ($evaluable->directeur_nom ?: 'Directeur non renseigne')
                                            : $evaluable->nom;
                                    } elseif ($evaluable instanceof \App\Models\Service) {
                                        $chef = trim(($evaluable->chef_prenom ?? '').' '.($evaluable->chef_nom ?? ''));
                                        $cibleLabel = $role === 'manager'
                                            ? ($chef !== '' ? $chef : 'Chef non renseigne')
                                            : $evaluable->nom;
                                    } else {
                                        $cibleLabel = $evaluable?->nom ?? '-';
                                    }
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
                                    <td data-label="Periode" class="whitespace-nowrap">{{ $evaluation->date_debut->format('d/m/Y') }} - {{ $evaluation->date_fin->format('d/m/Y') }}</td>
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
                                            @if ($evaluation->statut === 'brouillon')
                                                <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier l'evaluation" aria-label="Modifier l'evaluation">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                    </svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir l'evaluation" aria-label="Voir l'evaluation">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Exporter en PDF" aria-label="Exporter en PDF">
                                                <span aria-hidden="true">PDF</span>
                                            </a>
                                            @if ($evaluation->statut !== 'valide')
                                                <form method="POST" action="{{ route('admin.evaluations.destroy', $evaluation) }}" class="inline" onsubmit="return confirm('Supprimer cette evaluation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer l'evaluation" aria-label="Supprimer l'evaluation">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18M9.75 6.75V5.625A1.875 1.875 0 0 1 11.625 3.75h.75A1.875 1.875 0 0 1 14.25 5.625V6.75m3.75 0V18A2.25 2.25 0 0 1 15.75 20.25h-7.5A2.25 2.25 0 0 1 6 18V6.75h12Zm-8.25 4.5v5.25m4.5-5.25v5.25" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-500">Aucune evaluation disponible.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $evaluations->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
