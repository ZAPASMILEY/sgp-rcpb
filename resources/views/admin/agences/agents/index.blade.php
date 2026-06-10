@extends('layouts.app')

@section('title', 'Agents de l\'agence | '.config('app.name', 'SGP-RCPB'))

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
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Agences / Agents</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $agence->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            @if ($agence->delegationTechnique)
                                Delegation: {{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}
                            @endif
                            @if ($agence->caisse)
                                - Superviseur caisse: {{ $agence->caisse->directeur_nom }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.agences.index') }}" class="ent-btn ent-btn-soft">Index agences</a>
                        <a href="{{ route('admin.agences.agents.create', $agence) }}" data-open-create-modal data-modal-title="Ajouter un agent d'agence" class="ent-btn ent-btn-primary">Ajouter un agent</a>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="inline-flex px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">
                        {{ $agents->count() }} agent(s)
                    </div>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel p-6">
                <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                    <table class="ent-table text-left text-sm text-slate-700">
                        <thead class="sticky top-0 z-10">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Prenom</th>
                                <th>Sexe</th>
                                <th>Rôle</th>
                                <th>Debut fonction</th>
                                <th>Numero</th>
                                <th>Email</th>
                                <th class="text-right px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agents as $agent)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="font-medium text-slate-900">{{ $agent->nom }}</td>
                                    <td>{{ $agent->prenom }}</td>
                                    <td>{{ ucfirst($agent->sexe ?? '-') }}</td>
                                    <td>{{ $agent->role }}</td>
                                    <td>{{ optional($agent->date_debut_fonction)->format('d/m/Y') ?: '-' }}</td>
                                    <td>{{ $agent->numero_telephone }}</td>
                                    <td>{{ $agent->email }}</td>
                                    <td class="whitespace-nowrap text-right px-4 py-3">
                                        <div class="inline-flex items-center gap-1.5">
                                            
                                            <a href="{{ route('admin.agents.show', $agent) }}" 
                                               class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition shadow-sm" 
                                               title="Voir l'agent" aria-label="Voir l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5S21.75 12 21.75 12s-3.75 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('admin.agents.edit', $agent) }}" 
                                               class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 hover:bg-cyan-100 hover:text-cyan-800 transition shadow-sm" 
                                               title="Modifier l'agent" aria-label="Modifier l'agent">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 3.487 3.651 3.651M4.5 19.5l3.981-.884a2.25 2.25 0 0 0 1.068-.574L20.513 7.078a1.875 1.875 0 0 0 0-2.652l-.939-.939a1.875 1.875 0 0 0-2.652 0L5.958 14.451a2.25 2.25 0 0 0-.574 1.068L4.5 19.5Z" />
                                                </svg>
                                            </a>

                                            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" onsubmit="return confirm('Supprimer cet agent ?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ route('admin.agences.agents.index', $agence) }}">
                                                <button type="submit" 
                                                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-800 transition shadow-sm" 
                                                        title="Supprimer l'agent" aria-label="Supprimer l'agent">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75h18M9.75 6.75V5.625A1.875 1.875 0 0 1 11.625 3.75h.75A1.875 1.875 0 0 1 14.25 5.625V6.75m3.75 0V18A2.25 2.25 0 0 1 15.75 20.25h-7.5A2.25 2.25 0 0 1 6 18V6.75h12Zm-8.25 4.5v5.25m4.5-5.25v5.25" />
                                                    </svg>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-10 text-center text-sm text-slate-500">
                                        Aucun agent enregistré pour cette agence.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $agents->count() }} résultat(s)</div>
            </section>
        </div>
    </div>
@endsection