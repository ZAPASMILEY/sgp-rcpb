@extends('layouts.app')

@section('title', 'Directions caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Caisses / Directions</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Direction de {{ $caisse->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Direction propre a cette caisse.</p>
                    </div>
                    <a href="{{ route('admin.caisses.show', $caisse) }}" class="ent-btn ent-btn-soft">Retour a la caisse</a>
                </div>
            </section>

            <section class="admin-panel p-6">
                <div class="overflow-x-auto">
                    <table class="ent-table ent-table--compact text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Direction</th>
                                <th>Responsable</th>
                                <th>Email</th>
                                <th>Numero</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($caisseDirections as $direction)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $direction->nom ?: 'Direction de caisse' }}</td>
                                    <td>{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</td>
                                    <td>{{ $direction->directeur_email }}</td>
                                    <td>{{ $direction->directeur_numero }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.directions.show', $direction) }}" class="ent-btn ent-btn-soft">Voir la direction</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-slate-500">
                                        Aucune direction propre n'est rattachee a cette caisse.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
