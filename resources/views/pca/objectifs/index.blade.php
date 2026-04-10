@extends('layouts.pca')

@section('title', 'Objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'Fiches total',
                'value' => $stats['total'] ?? $fiches->total(),
                'meta' => 'Objectifs de la faîtière et des directions',
                'tone' => 'border-slate-100 bg-white text-slate-900',
                'accent' => 'text-slate-500',
                'icon' => 'fas fa-folder-open',
                'iconWrap' => 'bg-slate-100 text-slate-700',
            ],
            [
                'label' => 'Acceptées',
                'value' => $stats['acceptees'] ?? 0,
                'meta' => 'Fiches validées par la direction générale',
                'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900',
                'accent' => 'text-emerald-700',
                'icon' => 'fas fa-circle-check',
                'iconWrap' => 'bg-white text-emerald-600',
            ],
            [
                'label' => 'En attente',
                'value' => $stats['en_attente'] ?? 0,
                'meta' => 'Fiches encore en cours de validation',
                'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',
                'accent' => 'text-amber-700',
                'icon' => 'fas fa-hourglass-half',
                'iconWrap' => 'bg-white text-amber-600',
            ],
            [
                'label' => 'Refusées',
                'value' => $stats['refusees'] ?? 0,
                'meta' => 'Fiches à reprendre ou à corriger',
                'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',
                'accent' => 'text-rose-700',
                'icon' => 'fas fa-ban',
                'iconWrap' => 'bg-white text-rose-600',
            ],
        ];
    @endphp

    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-4">
            <header class="rounded-[28px] border border-white bg-white/90 px-5 py-5 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur lg:px-7">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-base font-black text-emerald-700">Objectifs</p>
                        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900 lg:text-4xl">Pilotage des fiches d'objectifs PCA</h1>
                        <p class="mt-2 text-sm text-slate-500">
                            Suivez les fiches de l'entité faîtière et de ses directions dans un espace plus clair, plus rapide à parcourir et plus simple à filtrer.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-emerald-700">
                                {{ $stats['total'] ?? $fiches->total() }} fiche(s)
                            </span>
                            @if ($filters['search'])
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-[0.18em] text-slate-600">
                                    Recherche active
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('pca.objectifs.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                            <i class="fas fa-rotate-right mr-2 text-xs"></i>
                            Réinitialiser
                        </a>
                        <a href="{{ route('pca.objectifs.create') }}" data-open-create-modal data-modal-title="Ajouter un objectif" class="inline-flex items-center justify-center rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-white shadow-[0_14px_30px_-18px_rgba(16,185,129,0.9)] transition hover:bg-emerald-600">
                            <i class="fas fa-plus mr-2 text-xs"></i>
                            Nouvelle fiche
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
                <div id="pca-objectifs-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('pca-objectifs-status-message')?.remove(), 3000);</script>
            @endif

            <section class="rounded-[28px] border border-slate-100 bg-white p-5 shadow-[0_18px_50px_-34px_rgba(15,23,42,0.28)] lg:p-6">
                <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.7fr)]">
                    <form method="GET" action="{{ route('pca.objectifs.index') }}" class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                            <div class="min-w-0 flex-1 space-y-2">
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
                                        placeholder="Titre, année"
                                        class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                                    >
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                    <i class="fas fa-filter mr-2 text-xs"></i>
                                    Filtrer
                                </button>
                                <a href="{{ route('pca.objectifs.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                                    Effacer
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="rounded-[24px] border border-cyan-100 bg-[linear-gradient(135deg,#ecfeff_0%,#f8fafc_100%)] p-4">
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-cyan-700">Vue rapide</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800">
                            Cette page centralise les fiches de la faîtière et des directions pour faciliter la validation, la consultation et la mise à jour.
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-white/90 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Lignes visibles</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $fiches->count() }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/90 px-4 py-3 shadow-sm">
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Page</p>
                                <p class="mt-2 text-2xl font-black text-slate-900">{{ $fiches->currentPage() }}</p>
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
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fiche</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($fiches as $fiche)
                                    @php
                                        $assignable = $fiche->assignable;
                                        $isEntite = $fiche->assignable_type === \App\Models\Entite::class;
                                        $cibleType = $isEntite ? 'Faîtière' : 'Direction';
                                        $cibleNom = $isEntite
                                            ? ($assignable?->nom ?? 'Entité non renseignée')
                                            : ($assignable?->nom ?? 'Direction non renseignée');
                                        $statut = $fiche->statut ?? 'en_attente';
                                        $statusClasses = match ($statut) {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee' => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match ($statut) {
                                            'acceptee' => 'Acceptée',
                                            'refusee' => 'Refusée',
                                            default => 'En attente',
                                        };
                                        $progressValue = (int) ($fiche->avancement_percentage ?? 0);
                                        $progressClasses = $progressValue >= 50
                                            ? 'bg-emerald-500'
                                            : ($progressValue > 0 ? 'bg-amber-400' : 'bg-rose-400');
                                    @endphp
                                    <tr class="align-top">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ ($fiches->firstItem() ?? 1) + $loop->index }}</td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-slate-900">{{ $cibleNom }}</p>
                                            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $cibleType }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-slate-900">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">Année {{ $fiche->annee }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ \Illuminate\Support\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">Échéance {{ \Illuminate\Support\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-black text-cyan-700">
                                                {{ $fiche->objectifs_count ?? $fiche->objectifs()->count() }} objectif(s)
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[150px]">
                                                <div class="mb-2 flex items-center justify-between gap-3">
                                                    <span class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">Progression</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-black text-slate-700">{{ $progressValue }}%</span>
                                                </div>
                                                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $progressClasses }}" style="width: {{ $progressValue }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <a href="{{ route('pca.objectifs.show', $fiche->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600" title="Voir" aria-label="Voir la fiche">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($fiche->statut === 'en_attente' || $fiche->statut === null)
                                                    <a href="{{ route('pca.objectifs.edit', $fiche->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-500 transition hover:bg-amber-100 hover:text-amber-700" title="Modifier" aria-label="Modifier la fiche">
                                                        <i class="fas fa-pen-to-square"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('pca.objectifs.destroy', $fiche->id) }}" onsubmit="return confirm('Supprimer cette fiche ?');" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500 transition hover:bg-rose-100 hover:text-rose-700" title="Supprimer" aria-label="Supprimer la fiche">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-md rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <p class="text-base font-black text-slate-700">Aucune fiche d'objectifs trouvée</p>
                                                <p class="mt-2 text-sm text-slate-500">Essayez une autre recherche ou créez une nouvelle fiche pour démarrer le suivi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($fiches->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $fiches->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
