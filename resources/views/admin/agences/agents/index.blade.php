@extends('layouts.app')

@section('title', 'Agents de l\'agence | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Agences / Agents</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $agence->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            @if ($agence->delegationTechnique)
                                Delegation: {{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}
                            @endif
                            @if ($agence->superviseurCaisse)
                                - Superviseur caisse: {{ $agence->superviseurCaisse->directeur_nom }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.agences.index') }}" class="ent-btn ent-btn-soft">Index agences</a>
                        <a href="{{ route('admin.agences.agents.create', $agence) }}" data-open-create-modal data-modal-title="Ajouter un agent d'agence" class="ent-btn ent-btn-primary">Ajouter un agent</a>
                    </div>
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
                                <th>#</th>
                                <th>Nom</th>
                                <th>Prenom</th>
                                <th>Fonction</th>
                                <th>Numero</th>
                                <th>Email</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agents as $agent)
                                <tr>
                                    <td>{{ ($agents->firstItem() ?? 1) + $loop->index }}</td>
                                    <td>{{ $agent->nom }}</td>
                                    <td>{{ $agent->prenom }}</td>
                                    <td>{{ $agent->fonction }}</td>
                                    <td>{{ $agent->numero_telephone }}</td>
                                    <td>{{ $agent->email }}</td>
                                    <td class="whitespace-nowrap text-right">
                                        <div class="ent-actions inline-flex items-center gap-2">
                                            <a href="{{ route('admin.agents.show', $agent) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Voir l'agent" aria-label="Voir l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.agents.edit', $agent) }}" class="ent-btn ent-btn-soft inline-flex h-7 w-7 items-center justify-center p-0" title="Modifier l'agent" aria-label="Modifier l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Supprimer cet agent ?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ route('admin.agences.agents.index', $agence) }}">
                                                <button type="submit" class="ent-btn ent-btn-danger inline-flex h-7 w-7 items-center justify-center p-0" title="Supprimer l'agent" aria-label="Supprimer l'agent">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-3.5 w-3.5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18M9.75 6.75V5.625A1.875 1.875 0 0 1 11.625 3.75h.75A1.875 1.875 0 0 1 14.25 5.625V6.75m3.75 0V18A2.25 2.25 0 0 1 15.75 20.25h-7.5A2.25 2.25 0 0 1 6 18V6.75h12Zm-8.25 4.5v5.25m4.5-5.25v5.25" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                                        Aucun agent enregistre pour cette agence.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($agents->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $agents->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
