@extends('layouts.app')

@section('title', 'Caisses | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .caisses-table thead th,
        .caisses-table tbody td {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .caisses-table thead th {
            letter-spacing: 0.12em;
        }

        .caisses-nowrap {
            white-space: nowrap;
        }

        .caisses-subtext {
            margin-top: 0.1rem;
            font-size: 0.74rem;
            line-height: 1.2;
        }

        .caisses-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .caisses-actions .ent-btn {
            width: 2.6rem;
            height: 2.6rem;
        }
    </style>
@endpush

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
                <form method="GET" action="{{ route('admin.caisses.index') }}" class="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Rechercher une caisse</label>
                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            class="ent-input"
                            placeholder="Rechercher par caisse, directeur, contact, secretariat ou delegation"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="ent-btn ent-btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Rechercher</span>
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.caisses.index') }}" class="ent-btn ent-btn-soft">Reinitialiser</a>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="caisses-table ent-table ent-table--compact text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>N</th>
                                <th>Caisse</th>
                                <th>Directeur de caisse</th>
                                <th>Contact directeur</th>
                                <th>Secretariat</th>
                                <th>Superviseur (D.T.)</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($caisses as $caisse)
                                <tr>
                                    <td class="caisses-nowrap">{{ ($caisses->firstItem() ?? 1) + $loop->index }}</td>
                                    <td class="caisses-nowrap">{{ $caisse->nom }}</td>
                                    <td class="caisses-nowrap">{{ $caisse->directeur_nom }}</td>
                                    <td class="caisses-nowrap">
                                        <p>{{ $caisse->directeur_email }}</p>
                                    </td>
                                    <td class="caisses-nowrap">{{ $caisse->secretariat_telephone }}</td>
                                    <td>
                                        @if ($caisse->superviseur)
                                            <p class="caisses-nowrap">{{ $caisse->superviseur->directeur_prenom }} {{ $caisse->superviseur->directeur_nom }}</p>
                                            @if ($caisse->superviseur->delegationTechnique)
                                                <p class="caisses-nowrap caisses-subtext text-slate-500">{{ $caisse->superviseur->delegationTechnique->region }} / {{ $caisse->superviseur->delegationTechnique->ville }}</p>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="caisses-actions">
                                            <a
                                                href="{{ route('admin.caisses.show', $caisse) }}"
                                                class="ent-btn ent-btn-soft inline-flex h-10 w-10 items-center justify-center p-0"
                                                title="Voir"
                                                aria-label="Voir"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a
                                                href="{{ route('admin.caisses.edit', $caisse) }}"
                                                class="ent-btn ent-btn-soft inline-flex h-10 w-10 items-center justify-center p-0"
                                                title="Modifier"
                                                aria-label="Modifier"
                                            >
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.caisses.destroy', $caisse) }}" onsubmit="return confirm('Supprimer cette caisse ?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="ent-btn ent-btn-danger inline-flex h-10 w-10 items-center justify-center p-0"
                                                    title="Supprimer"
                                                    aria-label="Supprimer"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                                        {{ $search !== '' ? 'Aucune caisse ne correspond a votre recherche.' : 'Aucune caisse enregistree.' }}
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
