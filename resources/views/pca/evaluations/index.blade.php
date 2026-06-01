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

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/80">
                                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Cible</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                                    <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $evaluation)
                                    @php
                                        $target        = $evaluation->evaluable;
                                        $cibleLabel    = trim((string) ($evaluation->identification?->nom_prenom ?? '')) ?: ($target?->name ?? '-');
                                        $hasNote       = $evaluation->note_finale !== null;
                                        $noteVal       = (float) ($evaluation->note_finale ?? 0);
                                        $mention       = $noteVal >= 8.5 ? 'Excellent' : ($noteVal >= 7 ? 'Bien' : ($noteVal >= 5 ? 'Passable' : 'Insuffisant'));
                                        $mentionTxtCls = match ($mention) {
                                            'Excellent' => 'text-emerald-600',
                                            'Bien'      => 'text-sky-600',
                                            'Passable'  => 'text-amber-600',
                                            default     => 'text-rose-600',
                                        };
                                        $notePct     = $hasNote ? max(0, min(100, ($noteVal / 10) * 100)) : 0;
                                        $noteBarCls  = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                        $notePillCls = $notePct >= 85
                                            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                            : ($notePct >= 70 ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                                            : ($notePct >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                                            : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'));
                                        $statusCls = match ($evaluation->statut) {
                                            'valide'      => 'bg-emerald-100 text-emerald-700',
                                            'soumis'      => 'bg-amber-100 text-amber-700',
                                            'refuse'      => 'bg-rose-100 text-rose-700',
                                            'reclamation' => 'bg-orange-100 text-orange-700',
                                            'a_reviser'   => 'bg-purple-100 text-purple-700',
                                            default       => 'bg-slate-100 text-slate-600',
                                        };
                                        $dotCls = match ($evaluation->statut) {
                                            'valide'      => 'bg-emerald-500',
                                            'soumis'      => 'bg-amber-400',
                                            'refuse'      => 'bg-rose-500',
                                            'reclamation' => 'bg-orange-500',
                                            'a_reviser'   => 'bg-purple-500',
                                            default       => 'bg-slate-400',
                                        };
                                        $statusLabel = match ($evaluation->statut) {
                                            'valide'      => 'Validée',
                                            'soumis'      => 'Soumise',
                                            'refuse'      => 'Refusée',
                                            'reclamation' => 'Réclamation',
                                            'a_reviser'   => 'À réviser',
                                            'brouillon'   => 'Brouillon',
                                            default       => ucfirst((string) $evaluation->statut),
                                        };
                                        $identification = $evaluation->identification;
                                        $anneeEval = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                                        $sem = trim((string) ($identification?->semestre ?? ''));
                                        if ($sem === '') { $sem = $evaluation->date_debut->month <= 6 ? '1' : '2'; }
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        {{-- Cible --}}
                                        <td class="px-5 py-3.5">
                                            <p class="font-black text-slate-800">{{ $cibleLabel }}</p>
                                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $identification?->emploi ?? '-' }}</p>
                                        </td>
                                        {{-- Période --}}
                                        <td class="px-5 py-3.5">
                                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                                <i class="fas fa-calendar-alt text-[9px] text-slate-400"></i>
                                                S{{ $sem }} / {{ $anneeEval }}
                                            </div>
                                            <p class="mt-1 text-[11px] text-slate-400">{{ $evaluation->evaluateur?->name ?? '-' }}</p>
                                        </td>
                                        {{-- Note --}}
                                        <td class="px-5 py-3.5">
                                            @if ($hasNote)
                                                <span class="inline-flex items-baseline gap-0.5 rounded-lg px-2.5 py-1 text-sm font-black {{ $notePillCls }}">
                                                    {{ number_format($noteVal, 2, ',', ' ') }}<span class="text-[10px] font-bold opacity-60">/10</span>
                                                </span>
                                                <div class="mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBarCls }}" style="width:{{ $notePct }}%"></div>
                                                </div>
                                                <p class="mt-0.5 text-[10px] font-bold {{ $mentionTxtCls }}">{{ $mention }}</p>
                                            @else
                                                <span class="text-slate-300 text-xs">—</span>
                                            @endif
                                        </td>
                                        {{-- Statut --}}
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        {{-- Actions --}}
                                        <td class="px-5 py-3.5 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('pca.evaluations.show', $evaluation) }}"
                                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-100 hover:text-blue-600"
                                                   title="Voir">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                @if ($evaluation->statut !== 'brouillon')
                                                    <a href="{{ route('pca.evaluations.pdf', $evaluation) }}"
                                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-600"
                                                       title="PDF" target="_blank">
                                                        <i class="fas fa-file-pdf text-xs"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-14 text-center">
                                            <div class="mx-auto max-w-xs">
                                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                                    <i class="fas fa-clipboard-list text-xl text-slate-300"></i>
                                                </div>
                                                <p class="text-sm font-black text-slate-700">Aucune évaluation enregistrée</p>
                                                <p class="mt-1 text-xs text-slate-400">Ajustez vos critères ou créez une nouvelle évaluation.</p>
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