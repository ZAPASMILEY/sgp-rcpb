@extends('layouts.app')

@section('title', 'Caisses | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Referentiel / Caisses</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Les Caisses de la RCPB </h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des caisses avec les coordonnees du directeur, le numero du secretariat et le superviseur technique.</p>
                    </div>
                    <a href="{{ route('admin.caisses.create') }}" data-open-create-modal data-modal-title="Ajouter une caisse" class="ent-btn ent-btn-primary">Ajouter une caisse</a>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel p-6">
                <div class="overflow-x-auto">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>N</th>
                                <th>Caisse</th>
                                <th>Directeur de caisse</th>
                                <th>Contact directeur</th>
                                <th>Secretariat</th>
                                <th>Superviseur (D.T.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($caisses as $caisse)
                                <tr>
                                    <td>{{ ($caisses->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $caisse->nom }}</td>
                                    <td>{{ $caisse->directeur_nom }}</td>
                                    <td>
                                        <p>{{ $caisse->directeur_email }}</p>
                                    </td>
                                    <td>{{ $caisse->secretariat_telephone }}</td>
                                    <td>
                                        @if ($caisse->superviseur)
                                            {{ $caisse->superviseur->directeur_prenom }} {{ $caisse->superviseur->directeur_nom }}
                                            @if ($caisse->superviseur->delegationTechnique)
                                                <p class="text-xs text-slate-500">{{ $caisse->superviseur->delegationTechnique->region }} / {{ $caisse->superviseur->delegationTechnique->ville }}</p>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                                        Aucune caisse enregistree.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($caisses->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $caisses->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
