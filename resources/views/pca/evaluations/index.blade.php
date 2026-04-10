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
                'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',
                'accent' => 'text-amber-700',
                'icon' => 'fas fa-paper-plane',
                'iconWrap' => 'bg-white text-amber-600',
            ],
            [
                'label' => 'Validées',
                'value' => $stats['valide'] ?? 0,
                'meta' => 'Évaluations définitivement approuvées',
                'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900',
                'accent' => 'text-emerald-700',
                'icon' => 'fas fa-circle-check',
                'iconWrap' => 'bg-white text-emerald-600',
            ],
        ];
    @endphp

    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-4">
            <header class="rounded-[28px] border border-white bg-white/90 px-5 py-5 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur lg:px-7">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-base font-black text-emerald-700">Evaluations</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 lg:text-4xl">Pilotage des évaluations PCA</h1>
                        <p class="mt-2 text-sm text-slate-500">
                            Retrouvez en un coup d'oeil les évaluations de l'entité et de la direction générale, avec une lecture plus claire des statuts et des résultats.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                                {{ $stats['total'] ?? $evaluations->total() }} évaluation(s)
                            </span>
                            @if ($filters['search'] || $filters['statut'])
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-slate-600">
                                    Filtres actifs
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('pca.evaluations.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                            <i class="fas fa-rotate-right mr-2 text-xs"></i>
                            Réinitialiser
                        </a>
                        <a href="{{ route('pca.evaluations.create') }}" data-open-create-modal data-modal-title="Ajouter une evaluation" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_14px_30px_-18px_rgba(8,145,178,0.8)] transition hover:bg-cyan-700">
                            <i class="fas fa-plus mr-2 text-xs"></i>
                            Nouvelle évaluation
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($summaryCards as $card)
                        <div class="rounded-[24px] border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] {{ $card['accent'] }}">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                                </div>
                                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl {{ $card['iconWrap'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                            </div>
                            <p class="mt-3 text-xs font-semibold text-slate-500">{{ $card['meta'] }}</p>
                        </div>
                    @endforeach
                </div>
            </header>

            @if (session('status'))
                <div id="pca-evaluations-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-evaluations-status-message')?.remove(), 3000);</script>
            @endif

            <section class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_50px_-34px_rgba(15,23,42,0.28)] lg:p-6">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.75fr)]">
                    <form method="GET" action="{{ route('pca.evaluations.index') }}" class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_260px_auto] lg:items-end">
                            <div class="min-w-0 space-y-2">
                                <label for="search" class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                        <i class="fas fa-search text-sm"></i>
                                    </span>
                                    <input
                                        id="search"
                                        name="search"
                                        type="text"
                                        value="{{ $filters['search'] }}"
                                        placeholder="Nom de l'entité ou du directeur"
                                        class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                                    >
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="statut" class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                                <select id="statut" name="statut" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                    <option value="">Tous les statuts</option>
                                    <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                                    <option value="soumis" @selected($filters['statut'] === 'soumis')>Soumis</option>
                                    <option value="valide" @selected($filters['statut'] === 'valide')>Valide</option>
                                </select>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    <i class="fas fa-filter mr-2 text-xs"></i>
                                    Filtrer
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="rounded-[24px] border border-cyan-100 bg-[linear-gradient(135deg,#ecfeff_0%,#f8fafc_100%)] p-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-700">Vue rapide</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800">
                            Utilisez cette page pour suivre l'avancement du cycle d'évaluation, distinguer les brouillons des dossiers validés et accéder rapidement aux détails.
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white/90 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Lignes visibles</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $evaluations->count() }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/90 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Page</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $evaluations->currentPage() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-[0_14px_40px_-30px_rgba(15,23,42,0.25)]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Cible</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Résultat</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $evaluation)
                                    @php
                                        $evaluable = $evaluation->evaluable;
                                        $role = $evaluation->evaluable_role ?? 'entity';

                                        if ($evaluation->evaluable_type === \App\Models\User::class && $role === 'dg') {
                                            $typeLabel = 'Direction générale';
                                            $cibleLabel = $evaluable?->name ?? 'Non renseigné';
                                        } elseif ($evaluation->evaluable_type === \App\Models\Direction::class && $role === 'manager') {
                                            $typeLabel = 'Direction';
                                            $cibleLabel = trim(($evaluable->directeur_prenom ?? '').' '.($evaluable->directeur_nom ?? '')) ?: 'Directeur non renseigné';
                                        } elseif ($evaluation->evaluable_type === \App\Models\Entite::class) {
                                            $typeLabel = 'Entité';
                                            $cibleLabel = $evaluable?->nom ?? 'Non renseignée';
                                        } else {
                                            $typeLabel = 'Autre';
                                            $cibleLabel = '—';
                                        }

                                        $mention = $evaluation->note_finale < 5 ? 'Insuffisant'
                                            : ($evaluation->note_finale < 7 ? 'Passable'
                                            : ($evaluation->note_finale < 8.5 ? 'Bien' : 'Excellent'));

                                        $mentionClass = match ($mention) {
                                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'Bien' => 'border-sky-200 bg-sky-50 text-sky-700',
                                            'Passable' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default => 'border-rose-200 bg-rose-50 text-rose-700',
                                        };

                                        $statusClass = match ($evaluation->statut) {
                                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };

                                        $statusLabel = match ($evaluation->statut) {
                                            'valide' => 'Validée',
                                            'soumis' => 'Soumise',
                                            default => 'Brouillon',
                                        };

                                        $noteValue = number_format((float) $evaluation->note_finale, 2, '.', '');
                                        $notePercent = max(0, min(100, ((float) $evaluation->note_finale / 10) * 100));
                                        $noteBarClass = $notePercent >= 85
                                            ? 'bg-emerald-500'
                                            : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="align-top">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $evaluation->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-slate-900">{{ $cibleLabel }}</p>
                                            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $typeLabel }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">Évaluateur {{ $evaluation->evaluateur?->name ?? 'Non renseigné' }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[150px]">
                                                <div class="mb-2 flex items-center justify-between gap-3">
                                                    <span class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">Note finale</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-black text-slate-700">{{ $noteValue }}/10</span>
                                                </div>
                                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">
                                                {{ $mention }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('pca.evaluations.show', $evaluation) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600" title="Voir" aria-label="Voir l'evaluation">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($evaluation->statut !== 'valide')
                                                    <form method="POST" action="{{ route('pca.evaluations.destroy', $evaluation) }}" onsubmit="return confirm('Supprimer cette evaluation ?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition hover:bg-rose-100 hover:text-rose-700" title="Supprimer" aria-label="Supprimer l'evaluation">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-md rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <p class="text-base font-black text-slate-700">Aucune évaluation trouvée</p>
                                                <p class="mt-2 text-sm text-slate-500">Essayez un autre filtre ou créez une nouvelle évaluation pour démarrer le suivi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($evaluations->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $evaluations->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
