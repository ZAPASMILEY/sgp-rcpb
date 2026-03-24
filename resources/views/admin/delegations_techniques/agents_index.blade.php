@extends('layouts.app')

@section('title', 'Index Agents Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique / Index</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Agents</h1>
                    </div>
                    <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter</a>
                </div>
            </section>

            <section class="admin-panel p-6">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.delegations-techniques.agents.index') }}" class="ent-btn {{ $activeDelegationId === 0 ? 'ent-btn-primary' : 'ent-btn-soft' }}">Toutes les delegations</a>
                    @foreach ($delegations as $delegation)
                        <a href="{{ route('admin.delegations-techniques.agents.index', ['delegation_id' => $delegation->id]) }}" class="ent-btn {{ $activeDelegationId === $delegation->id ? 'ent-btn-primary' : 'ent-btn-soft' }}">{{ $delegation->region }} / {{ $delegation->ville }}</a>
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
                                <th>Agent</th>
                                <th>Fonction</th>
                                <th>Service</th>
                                <th>Delegation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agents as $agent)
                                <tr>
                                    <td>{{ ($agents->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $agent->prenom }} {{ $agent->nom }}</td>
                                    <td>{{ $agent->fonction }}</td>
                                    <td>{{ $agent->service?->nom ?? '-' }}</td>
                                    <td>{{ $agent->service?->direction?->delegationTechnique?->region }} / {{ $agent->service?->direction?->delegationTechnique?->ville }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-slate-500">Aucun agent trouve.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($agents->hasPages())
                    <div class="mt-4">{{ $agents->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
