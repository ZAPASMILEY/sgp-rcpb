@extends('layouts.app')

@section('title', 'Index Services Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Index</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Services</h1>
                    </div>
                    <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary">Ajouter</a>
                </div>
            </section>

            <section class="admin-panel p-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.delegations-techniques.services.index') }}" class="ent-btn {{ $activeDelegationId === 0 ? 'ent-btn-primary' : 'ent-btn-soft' }}">Toutes les delegations</a>
                    @foreach ($delegations as $delegation)
                        <a href="{{ route('admin.delegations-techniques.services.index', ['delegation_id' => $delegation->id]) }}" class="ent-btn {{ $activeDelegationId === $delegation->id ? 'ent-btn-primary' : 'ent-btn-soft' }}">{{ $delegation->region }} / {{ $delegation->ville }}</a>
                    @endforeach
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Service</th>
                                <th>Delegation</th>
                                <th>Chef</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($services as $service)
                                <tr>
                                    <td>{{ ($services->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $service->nom }}</td>
                                    <td>{{ $service->direction?->delegationTechnique?->region }} / {{ $service->direction?->delegationTechnique?->ville }}</td>
                                    <td>{{ $service->chef_prenom }} {{ $service->chef_nom }}</td>
                                    <td class="text-right"><a href="{{ route('admin.services.show', $service) }}" class="ent-btn ent-btn-soft">Voir</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-500">Aucun service trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($services->hasPages())
                    <div class="mt-4">{{ $services->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
