@extends('layouts.app')

@section('title', 'Index Services Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Index</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Services</h1>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <form method="GET" action="{{ route('admin.delegations-techniques.services.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            @if ($activeDelegationId > 0)
                                <input type="hidden" name="delegation_id" value="{{ $activeDelegationId }}">
                            @endif
                            <input type="search" name="search" value="{{ $search }}" class="ent-input min-w-[260px]" placeholder="Rechercher un service, chef, region...">
                            <button type="submit" class="ent-btn ent-btn-soft">Rechercher</button>
                        </form>
                        <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">{{ $services->total() }} service(s)</div>
                        <a href="{{ route('admin.services.create') }}" data-open-create-modal data-modal-title="Ajouter un service" class="ent-btn ent-btn-primary">Ajouter</a>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('admin.delegations-techniques.services.index', array_filter(['search' => $search])) }}" class="ent-btn {{ $activeDelegationId === 0 ? 'ent-btn-primary' : 'ent-btn-soft' }}">Toutes les delegations</a>
                    @foreach ($delegations as $delegation)
                        <a href="{{ route('admin.delegations-techniques.services.index', array_filter(['delegation_id' => $delegation->id, 'search' => $search])) }}" class="ent-btn {{ $activeDelegationId === $delegation->id ? 'ent-btn-primary' : 'ent-btn-soft' }}">{{ $delegation->region }} / {{ $delegation->ville }}</a>
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
