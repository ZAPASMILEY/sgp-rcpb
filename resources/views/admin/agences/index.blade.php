@extends('layouts.app')

@section('title', 'Agences | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .agences-page .agences-hero {
            border-radius: 28px;
            border: 1px solid rgba(190, 201, 220, 0.55);
            background:
                radial-gradient(circle at top left, rgba(22, 163, 74, 0.12), transparent 34%),
                radial-gradient(circle at top right, rgba(234, 179, 8, 0.12), transparent 30%),
                linear-gradient(180deg, rgba(255, 253, 245, 0.98), rgba(248, 250, 236, 0.96));
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        }

        .agences-page .agences-table-wrap {
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid rgba(190, 201, 220, 0.75);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .agences-page .agences-table thead th {
            background: linear-gradient(90deg, rgba(227, 239, 208, 0.7), rgba(247, 250, 235, 0.92));
            border-bottom: 1px solid rgba(190, 201, 220, 0.85);
            font-size: 0.72rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #64748b;
        }

        .agences-page .agences-table tbody tr:hover {
            background: rgba(248, 250, 252, 0.88);
        }

        .agences-page .agence-index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #1f7a36, #4d9f38);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.18);
        }

        .agences-page .agence-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #334155;
        }

        .agences-page .agence-meta {
            margin-top: 0.2rem;
            font-size: 0.76rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .agences-page .agence-person {
            font-weight: 600;
            color: #334155;
        }

        .agences-page .agence-subtext {
            margin-top: 0.35rem;
            font-size: 0.85rem;
            color: #64748b;
        }

        .agences-page .agence-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.4rem 0.75rem;
            background: rgba(241, 245, 249, 0.95);
            color: #475569;
            font-weight: 600;
        }

        .agences-page .agence-actions {
            display: flex;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .agences-page .agences-table {
                min-width: 0;
            }

            .agences-page .agence-actions {
                justify-content: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="agences-page admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-7xl space-y-6">
            <section class="agences-hero admin-panel p-6 lg:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Referentiel / Agences</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Index des agences</h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des agences avec le chef d'agence, la secretaire, la delegation technique et le directeur de caisse superviseur.</p>
                    </div>
                    <a href="{{ route('admin.agences.create') }}" data-open-create-modal data-modal-title="Ajouter une agence" class="ent-btn ent-btn-primary">Ajouter une agence</a>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel p-6">
                <div class="agences-table-wrap">
                    <table class="agences-table ent-table ent-table--stack text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Agence</th>
                                <th>Chef d'agence</th>
                                <th>Secretaire</th>
                                <th>Delegation</th>
                                <th>Superviseur (Directeur de caisse)</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agences as $agence)
                                <tr>
                                    <td data-label="#">
                                        <span class="agence-index">{{ ($agences->firstItem() ?? 1) + $loop->index }}</span>
                                    </td>
                                    <td data-label="Agence">
                                        <p class="agence-name">{{ $agence->nom }}</p>
                                        <p class="agence-meta">Structure locale</p>
                                    </td>
                                    <td data-label="Chef d'agence">
                                        <p class="agence-person">{{ $agence->chef_nom }}</p>
                                        <p class="agence-subtext">{{ $agence->chef_telephone }}</p>
                                    </td>
                                    <td data-label="Secretaire">
                                        <p class="agence-person">{{ $agence->secretaire_nom }}</p>
                                        <p class="agence-subtext">{{ $agence->secretaire_telephone }}</p>
                                    </td>
                                    <td data-label="Delegation">
                                        @if ($agence->delegationTechnique)
                                            <span class="agence-pill">{{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Superviseur">
                                        @if ($agence->superviseurCaisse)
                                            <p class="agence-person">{{ $agence->superviseurCaisse->nom }}</p>
                                            <p class="agence-subtext">{{ $agence->superviseurCaisse->directeur_nom }}</p>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td data-label="Actions" class="text-right">
                                        <div class="agence-actions">
                                            <a href="{{ route('admin.agences.agents.index', $agence) }}" class="ent-btn ent-btn-soft">Agents</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                                        Aucune agence enregistree.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($agences->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $agences->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
