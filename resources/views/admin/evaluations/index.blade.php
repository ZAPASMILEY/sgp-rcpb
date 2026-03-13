@extends('layouts.app')

@section('title', 'Evaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluations</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des evaluations des entites, directions, services et agents.</p>
                    </div>
                    <a href="{{ route('admin.evaluations.index') }}" class="ent-btn ent-btn-soft inline-flex items-center justify-center whitespace-nowrap">
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
                    <a href="{{ route('admin.evaluations.create') }}" class="ent-btn ent-btn-primary text-center">Ajouter</a>
                </form>

                <div class="ent-table-wrap overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
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
                                    $typeMap = [
                                        \App\Models\Entite::class    => 'Entite',
                                        \App\Models\Direction::class => 'Direction',
                                        \App\Models\Service::class   => 'Service',
                                        \App\Models\Agent::class     => 'Agent',
                                    ];
                                    $typeLabel = $typeMap[$evaluation->evaluable_type] ?? '-';
                                    if ($evaluable instanceof \App\Models\Agent) {
                                        $cibleLabel = trim($evaluable->prenom.' '.$evaluable->nom);
                                    } elseif ($evaluable instanceof \App\Models\Direction) {
                                        $cibleLabel = $evaluable->nom.($evaluable->directeur_nom ? ' ('.$evaluable->directeur_nom.')' : '');
                                    } elseif ($evaluable instanceof \App\Models\Service) {
                                        $chef = trim(($evaluable->chef_prenom ?? '').' '.($evaluable->chef_nom ?? ''));
                                        $cibleLabel = $evaluable->nom.($chef !== '' ? ' ('.$chef.')' : '');
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
                                    <td>{{ $evaluation->id }}</td>
                                    <td>{{ $typeLabel }}</td>
                                    <td>{{ $cibleLabel }}</td>
                                    <td class="whitespace-nowrap">{{ $evaluation->date_debut->format('d/m/Y') }} - {{ $evaluation->date_fin->format('d/m/Y') }}</td>
                                    <td>{{ $evaluation->note_finale }}%</td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $mentionClass }}">
                                            {{ $mention }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                            {{ ucfirst($evaluation->statut) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="ent-actions justify-end gap-2">
                                            @if ($evaluation->statut === 'brouillon')
                                                <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier l'evaluation" aria-label="Modifier l'evaluation">
                                                    <span aria-hidden="true">~</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir l'evaluation" aria-label="Voir l'evaluation">
                                                <span aria-hidden="true">+</span>
                                            </a>
                                            <a href="{{ route('admin.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Exporter en PDF" aria-label="Exporter en PDF">
                                                <span aria-hidden="true">PDF</span>
                                            </a>
                                            @if ($evaluation->statut !== 'valide')
                                                <form method="POST" action="{{ route('admin.evaluations.destroy', $evaluation) }}" class="inline" onsubmit="return confirm('Supprimer cette evaluation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer l'evaluation" aria-label="Supprimer l'evaluation">
                                                        <span aria-hidden="true">x</span>
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
