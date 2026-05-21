@extends('layouts.pca')

@section('title', 'Evaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'Evaluations total',
                'value' => $stats['total'] ?? $evaluations->total(),
                'meta' => 'Suivi global de l\'entité et de la direction générale',
                'tone' => 'border-slate-100 bg-white text-slate-900',
                'accent' => 'text-slate-500',
                'icon' => 'fas fa-clipboard-list',
                'iconWrap' => 'bg-slate-100 text-slate-700',
            ],
            [
                'label' => 'Brouillons',
                'value' => $stats['brouillon'] ?? 0,
                'meta' => 'Évaluations encore en préparation',
                'tone' => 'border-slate-100 bg-slate-50/80 text-slate-900',
                'accent' => 'text-slate-600',
                'icon' => 'fas fa-file-pen',
                'iconWrap' => 'bg-white text-slate-600',
            ],
            [
                'label' => 'Soumises',
                'value' => $stats['soumis'] ?? 0,
                'meta' => 'En attente de validation finale',
                'tone' => 'border-amber-100 bg-amber-50/60 text-amber-900',
                'accent' => 'text-amber-700',
                'icon' => 'fas fa-paper-plane',
                'iconWrap' => 'bg-white text-amber-600',
            ],
            [
                'label' => 'Validées',
                'value' => $stats['valide'] ?? 0,
                'meta' => 'Évaluations définitivement approuvées',
                'tone' => 'border-emerald-100 bg-emerald-50/60 text-emerald-900',
                'accent' => 'text-emerald-700',
                'icon' => 'fas fa-circle-check',
                'iconWrap' => 'bg-white text-emerald-600',
            ],
            [
                'label' => 'Refusées',
                'value' => $stats['refuse'] ?? 0,
                'meta' => 'Refusées par le DG, réclamation en cours',
                'tone' => 'border-rose-100 bg-rose-50/60 text-rose-900',
                'accent' => 'text-rose-700',
                'icon' => 'fas fa-ban',
                'iconWrap' => 'bg-white text-rose-600',
            ],
        ];
    @endphp

    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-8 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-6">
            
            <header class="rounded-[24px] border border-white bg-white/90 p-6 shadow-[0_14px_40px_-20px_rgba(148,163,184,0.4)] backdrop-blur lg:p-8">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-xs font-bold uppercase tracking-widest text-emerald-600">Evaluations</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 lg:text-3xl">Pilotage des évaluations PCA</h1>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Retrouvez en un coup d'œil les évaluations de l'entité et de la direction générale, avec une lecture plus claire des statuts et des résultats.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-700">
                                {{ $stats['total'] ?? $evaluations->total() }} évaluation(s)
                            </span>
                            @if ($filters['search'] || $filters['statut'])
                                <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-cyan-700">
                                    Filtres actifs
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <a href="{{ route('pca.evaluations.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-950">
                            <i class="fas fa-rotate-right mr-2 text-xs opacity-80"></i>Réinitialiser
                        </a>
                        @if($evaluationsEnabled)
                        <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Ajouter une evaluation" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-cyan-700">
                            <i class="fas fa-plus mr-2 text-xs"></i>Nouvelle évaluation
                        </a>
                        @else
                        <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed" title="Désactivé par l'administrateur">
                            <i class="fas fa-ban text-xs"></i> Évaluations désactivées
                        </span>
                        @endif
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($summaryCards as $card)
                        <div class="flex flex-col justify-between rounded-2xl border p-5 shadow-sm transition-all hover:shadow-md {{ $card['tone'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-bold uppercase tracking-wider {{ $card['accent'] }}">{{ $card['label'] }}</p>
                                    <p class="text-3xl font-extrabold tracking-tight leading-none text-slate-900">{{ $card['value'] }}</p>
                                </div>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-sm {{ $card['iconWrap'] }}">
                                    <i class="{{ $card['icon'] }} text-sm"></i>
                                </span>
                            </div>
                            <p class="mt-4 text-[11px] font-medium leading-snug text-slate-400 line-clamp-2">{{ $card['meta'] }}</p>
                        </div>
                    @endforeach
                </div>
            </header>

            @if (session('status'))
                <div id="pca-evaluations-status-message" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm animate-fade-in">
                    <i class="fas fa-check-circle mr-2 text-emerald-500"></i>{{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-evaluations-status-message')?.remove(), 3000);</script>
            @endif

            <section class="rounded-[24px] border border-slate-100 bg-white p-6 shadow-[0_14px_40px_-25px_rgba(15,23,42,0.15)]">
                
                <div class="mb-6 rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                    <form method="GET" action="{{ route('pca.evaluations.index') }}">
                        <div class="grid gap-4 md:grid-cols-12 md:items-end">
                            
                            <div class="md:col-span-6 space-y-1.5">
                                <label for="search" class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Recherche rapide</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <i class="fas fa-search text-xs"></i>
                                    </span>
                                    <input id="search" name="search" type="text" value="{{ $filters['search'] }}" placeholder="Nom de l'entité, direction, directeur..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 shadow-sm">
                                </div>
                            </div>
                            
                            <div class="md:col-span-4 space-y-1.5">
                                <label for="statut" class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Filtrer par statut</label>
                                <select id="statut" name="statut" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 shadow-sm">
                                    <option value="">Tous les statuts</option>
                                    <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                                    <option value="soumis" @selected($filters['statut'] === 'soumis')>Soumis</option>
                                    <option value="valide" @selected($filters['statut'] === 'valide')>Validée</option>
                                    <option value="refuse" @selected($filters['statut'] === 'refuse')>Refusée</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="w-full inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 shadow-sm">
                                    <i class="fas fa-filter mr-2 text-xs opacity-80"></i>Filtrer
                                </button>
                                @if($filters['search'] || $filters['statut'])
                                    <a href="{{ route('pca.evaluations.index') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-300">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap">
                            <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500">
                                <tr>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider w-16 border-r border-slate-100">#</th>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider border-r border-slate-100">Cible de l'évaluation</th>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider border-r border-slate-100">Période & Evaluateur</th>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider border-r border-slate-100">Résultat / Note</th>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider border-r border-slate-100">Mention</th>
                                    <th class="px-5 py-3.5 text-[11px] font-bold uppercase tracking-wider">Statut</th>
                                    <th class="px-5 py-3.5 text-center text-[11px] font-bold uppercase tracking-wider w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $evaluation)
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-16 bg-gradient-to-b from-white to-slate-50/50">
                                            <div class="mx-auto max-w-sm rounded-2xl border border-dashed border-slate-200 bg-white p-8 shadow-sm">
                                                <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 mb-4 shadow-sm border border-cyan-100">
                                                    <i class="fas fa-folder-open text-lg"></i>
                                                </div>
                                                <p class="text-base font-bold text-slate-800">Aucune évaluation enregistrée</p>
                                                <p class="mt-1.5 text-xs leading-relaxed text-slate-400">
                                                    Il n'y a actuellement aucune fiche d'évaluation PCA à afficher. Vous pouvez ajuster vos critères de recherche ou cliquer sur le bouton ci-dessus pour initialiser un nouveau parcours de suivi.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($evaluations->hasPages())
                    <div class="mt-5 border-t border-slate-150 pt-4">
                        {{ $evaluations->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection