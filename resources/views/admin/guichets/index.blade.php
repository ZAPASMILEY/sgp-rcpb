@extends('layouts.app')

@section('title', 'Guichets | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Referentiel / Guichet</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Index des guichets</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des guichets avec le chef de guichet et l'agence associee.</p>
                    </div>
                    <a href="{{ route('admin.guichets.create') }}" data-open-create-modal data-modal-title="Ajouter un guichet" class="ent-btn ent-btn-primary">Ajouter un guichet</a>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel p-6">
                <div class="mt-2 overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Guichet</th>
                                <th>Chef de guichet</th>
                                <th>Coordonnees</th>
                                <th>Agence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($guichets as $guichet)
                                <tr>
                                    <td>{{ ($guichets->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $guichet->nom }}</td>
                                    <td>{{ $guichet->chef_nom }}</td>
                                    <td>
                                        <p>{{ $guichet->chef_email }}</p>
                                        <p class="text-xs text-slate-500">{{ $guichet->chef_telephone }}</p>
                                    </td>
                                    <td>
                                        <p>{{ $guichet->agence?->nom ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $guichet->agence?->delegationTechnique?->region }} / {{ $guichet->agence?->delegationTechnique?->ville }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">Aucun guichet enregistre.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($guichets->hasPages())
                    <div class="mt-4">{{ $guichets->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection