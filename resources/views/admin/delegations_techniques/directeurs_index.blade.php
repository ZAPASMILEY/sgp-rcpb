@extends('layouts.app')

@section('title', 'Index Directeurs Techniques | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Index</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Directeurs Techniques</h1>
                        <p class="mt-2 text-sm text-slate-600">Chaque delegation dispose de son index via le filtre ci-dessous.</p>
                    </div>
                    <a href="{{ route('admin.directions.create') }}" data-open-create-modal data-modal-title="Ajouter un Directeur Technique" class="ent-btn ent-btn-primary">Ajouter</a>
                </div>
            </section>

            <section class="admin-panel p-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.delegations-techniques.directeurs.index') }}" class="ent-btn {{ $activeDelegationId === 0 ? 'ent-btn-primary' : 'ent-btn-soft' }}">Toutes les delegations</a>
                    @foreach ($delegations as $delegation)
                        <a href="{{ route('admin.delegations-techniques.directeurs.index', ['delegation_id' => $delegation->id]) }}" class="ent-btn {{ $activeDelegationId === $delegation->id ? 'ent-btn-primary' : 'ent-btn-soft' }}">
                            {{ $delegation->region }} / {{ $delegation->ville }}
                        </a>
                    @endforeach
                </div>

                @if ($selectedDelegation)
                    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Services de la delegation</p>
                        <h2 class="mt-2 text-lg font-semibold text-slate-950">{{ $selectedDelegation->region }} / {{ $selectedDelegation->ville }}</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @forelse ($delegationServices as $service)
                                <span class="ent-btn ent-btn-soft">{{ $service->nom }}</span>
                            @empty
                                <p class="text-sm text-slate-500">Aucun service rattache a cette delegation.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                <div class="mt-6 overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Delegation</th>
                                <th>Directeur</th>
                                <th>Note</th>
                                <th>Email</th>
                                <th>Numero</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($directeurs as $direction)
                                <tr>
                                    <td>{{ ($directeurs->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $direction->delegationTechnique?->region }} / {{ $direction->delegationTechnique?->ville }}</td>
                                    <td>{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</td>
                                    <td>
                                        @php($note = $directeurNotes[$direction->id] ?? null)
                                        <span class="font-semibold text-emerald-700">{{ $note !== null ? $note.'/100' : '-' }}</span>
                                    </td>
                                    <td>{{ $direction->directeur_email }}</td>
                                    <td>{{ $direction->directeur_numero }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.directions.show', $direction) }}" class="ent-btn ent-btn-soft">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-500">Aucun directeur technique trouve.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($directeurs->hasPages())
                    <div class="mt-4">{{ $directeurs->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
