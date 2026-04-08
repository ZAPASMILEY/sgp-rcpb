@extends('layouts.pca')

@section('title', 'Objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-4">
            <header class="rounded-[26px] border border-white bg-white/90 px-5 py-4 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-base font-black text-emerald-700">Objectifs</p>
                        <div class="mt-1 flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-black tracking-tight text-slate-900">Gestion des objectifs</h1>
                        </div>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Suivi des objectifs de l'entité et des directeurs</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="status-pill">Total {{ $fiches->total() }}</span>
                            @if ($filters['search'])
                                <span class="status-pill">Filtres actifs</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('pca.objectifs.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                        <i class="fas fa-rotate-right"></i>
                        <span>Réinitialiser</span>
                    </a>
                </div>
            </header>

            @if (session('status'))
                <div id="pca-objectifs-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-objectifs-status-message')?.remove(), 3000);</script>
            @endif

            <section class="rounded-[26px] border border-white bg-white/90 px-5 py-6 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur mt-4">
                <form method="GET" action="{{ route('pca.objectifs.index') }}" class="mb-6 grid gap-3 lg:grid-cols-[1.2fr_auto_auto] lg:items-end">
                    <div class="space-y-2">
                        <label for="search" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                        <input id="search" name="search" type="text" value="{{ $filters['search'] }}" placeholder="Commentaire, echeance" class="ent-input">
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-700"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                    <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Ajouter un objectif" class="inline-flex items-center rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600"><i class="fas fa-plus mr-2 text-xs"></i>Ajouter</a>
                </form>

                <div class="rounded-2xl border border-slate-100 bg-white/90 overflow-x-auto shadow-[0_12px_30px_-24px_rgba(15,23,42,0.3)]">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Année</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Echéance</th>
                                <th>Avancement</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fiches as $fiche)
                                <tr>
                                    <td class="font-bold text-slate-900">{{ ($fiches->firstItem() ?? 1) + $loop->index }}</td>
                                    <td class="font-bold text-sky-600">{{ $fiche->annee }}</td>
                                    <td class="font-semibold text-slate-800">{{ $fiche->titre }}</td>
                                    <td class="text-slate-600">{{ $fiche->date }}</td>
                                    <td class="text-slate-600">{{ $fiche->date_echeance }}</td>
                                    <td>
                                        <span class="inline-block min-w-14 rounded-full border px-2 py-1 text-center text-sm font-bold tracking-tight {{ $fiche->avancement_percentage > 50 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-rose-700 bg-rose-50 border-rose-200' }}">
                                            {{ $fiche->avancement_percentage }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if ($fiche->statut === 'acceptee')
                                            <span class="inline-block rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">Acceptée</span>
                                        @elseif ($fiche->statut === 'refusee')
                                            <span class="inline-block rounded-full bg-rose-100 px-3 py-1 text-xs font-black text-rose-700">Refusée</span>
                                        @else
                                            <span class="inline-block rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">En attente</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-center align-middle">
                                        <div class="inline-flex gap-1">
                                            <a href="{{ route('pca.objectifs.show', $fiche->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-blue-100 hover:text-blue-600 transition" title="Voir" aria-label="Voir la fiche">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($fiche->statut === 'en_attente' || $fiche->statut === null)
                                                <a href="{{ route('pca.objectifs.edit', $fiche->id) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-50 text-amber-500 hover:bg-amber-100 hover:text-amber-700 transition" title="Modifier" aria-label="Modifier la fiche">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('pca.objectifs.destroy', $fiche->id) }}" onsubmit="return confirm('Supprimer cette fiche ?');" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-100 hover:text-rose-700 transition" title="Supprimer" aria-label="Supprimer la fiche">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-slate-400 py-8">Aucune fiche d'objectifs trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($fiches->hasPages())
                    <div class="mt-6">{{ $fiches->links() }}</div>
                @endif
            </section>
        </div>
    </div>
@endsection
