@extends('layouts.app')

@section('title', 'Index Secretaires Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Index</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Secretaires</h1>
            </section>

            <section class="admin-panel p-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.delegations-techniques.secretaires.index') }}" class="ent-btn {{ $activeDelegationId === 0 ? 'ent-btn-primary' : 'ent-btn-soft' }}">Toutes les delegations</a>
                    @foreach ($delegations as $delegation)
                        <a href="{{ route('admin.delegations-techniques.secretaires.index', ['delegation_id' => $delegation->id]) }}" class="ent-btn {{ $activeDelegationId === $delegation->id ? 'ent-btn-primary' : 'ent-btn-soft' }}">{{ $delegation->region }} / {{ $delegation->ville }}</a>
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
                                <th>Secretaire</th>
                                <th>Email</th>
                                <th class="w-24 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($secretaires as $direction)
                                <tr>
                                    <td>{{ ($secretaires->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $direction->delegationTechnique?->region }} / {{ $direction->delegationTechnique?->ville }}</td>
                                    <td>{{ $direction->secretaire_prenom }} {{ $direction->secretaire_nom }}</td>
                                    <td>{{ $direction->secretaire_email }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="text-cyan-500 hover:text-cyan-700 mr-2" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.secretaires.edit', $direction->id) }}" class="text-amber-500 hover:text-amber-700" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-500">Aucun secretaire trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($secretaires->hasPages())
                    <div class="mt-4">{{ $secretaires->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
