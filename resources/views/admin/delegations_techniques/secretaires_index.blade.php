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

                <div class="mt-6 overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Delegation</th>
                                <th>Secretaire</th>
                                <th>Email</th>
                                <th>Telephone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($secretaires as $direction)
                                <tr>
                                    <td>{{ ($secretaires->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $direction->delegationTechnique?->region }} / {{ $direction->delegationTechnique?->ville }}</td>
                                    <td>{{ $direction->secretaire_prenom }} {{ $direction->secretaire_nom }}</td>
                                    <td>{{ $direction->secretaire_email }}</td>
                                    <td>{{ $direction->secretaire_telephone }}</td>
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
