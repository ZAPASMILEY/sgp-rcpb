@extends('layouts.app')

@section('title', 'Services de la Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl flex flex-col gap-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h1 class="text-3xl font-black tracking-tight text-slate-900">Services de la Faîtière</h1>
                <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">{{ $services->count() }} service(s)</div>
            </div>
            <div class="ent-table-wrap overflow-x-auto">
                <table class="ent-table text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Direction</th>
                            <th>Chef de service</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $service->nom }}</td>
                                <td>{{ $service->direction?->nom }}</td>
                                <td>{{ $service->chef_prenom }} {{ $service->chef_nom }}</td>
                                <td>{{ $service->chef_email }}</td>
                                <td>{{ $service->chef_telephone }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-slate-500">Aucun service trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
