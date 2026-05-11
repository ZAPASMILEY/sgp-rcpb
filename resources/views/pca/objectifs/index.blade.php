@extends('layouts.pca')

@section('title', 'Objectifs DG | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- ── Hero Banner ────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-teal-400/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-200">PCA · Pilotage Objectifs</p>
                <h1 class="mt-1 text-2xl font-black text-white">Fiches d'objectifs du Directeur Général</h1>
                <p class="mt-1 text-sm text-emerald-100/75">
                    Suivi opérationnel des contrats d'objectifs assignés au DG
                    @if($dgUser) — <span class="font-semibold text-white">{{ $dgUser->name }}</span>@endif
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-3">
                @if ($filters['search'] || $filters['statut'])
                    <a href="{{ route('pca.objectifs.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-emerald-200 ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-times text-xs"></i> Réinitialiser
                    </a>
                @endif
                @if($objectifsEnabled)
                <a href="{{ route('pca.objectifs.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-black text-emerald-700 shadow-md transition hover:bg-emerald-50">
                    <i class="fas fa-plus text-xs"></i> Nouvelle fiche
                </a>
                @else
                <span class="inline-flex items-center gap-2 rounded-xl border-2 border-white/30 bg-white/10 px-5 py-2.5 text-sm font-black text-white/50 cursor-not-allowed" title="Désactivé par l'administrateur">
                    <i class="fas fa-ban text-xs"></i> Objectifs désactivés
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        @if (session('status'))
            <div id="pca-status-msg" class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle"></i> {{ session('status') }}
            </div>
            <script>setTimeout(function(){ var el=document.getElementById('pca-status-msg'); if(el) el.remove(); }, 4000);</script>
        @endif

        {{-- ── Scorecard KPIs (cliquables = filtre statut) ─────────────────── --}}
        @php
            $activeStatut = $filters['statut'];
            $cards = [
                [
                    'key'   => '',
                    'label' => 'Total fiches',
                    'value' => $stats['total'],
                    'icon'  => 'fas fa-folder-open',
                    'meta'  => 'Toutes les fiches DG',
                    'color' => 'border-slate-200 bg-white',
                    'iconBg'=> 'bg-slate-100 text-slate-600',
                    'val'   => 'text-slate-900',
                    'ring'  => 'ring-slate-900',
                ],
                [
                    'key'   => 'acceptee',
                    'label' => 'Acceptées',
                    'value' => $stats['acceptees'],
                    'icon'  => 'fas fa-circle-check',
                    'meta'  => 'Validées par le DG',
                    'color' => 'border-emerald-100 bg-emerald-50',
                    'iconBg'=> 'bg-emerald-100 text-emerald-600',
                    'val'   => 'text-emerald-700',
                    'ring'  => 'ring-emerald-500',
                ],
                [
                    'key'   => 'en_attente',
                    'label' => 'En attente',
                    'value' => $stats['en_attente'],
                    'icon'  => 'fas fa-hourglass-half',
                    'meta'  => 'Validation requise',
                    'color' => 'border-amber-100 bg-amber-50',
                    'iconBg'=> 'bg-amber-100 text-amber-600',
                    'val'   => 'text-amber-700',
                    'ring'  => 'ring-amber-500',
                ],
                [
                    'key'   => 'refusee',
                    'label' => 'Refusées',
                    'value' => $stats['refusees'],
                    'icon'  => 'fas fa-ban',
                    'meta'  => 'À corriger',
                    'color' => 'border-rose-100 bg-rose-50',
                    'iconBg'=> 'bg-rose-100 text-rose-600',
                    'val'   => 'text-rose-700',
                    'ring'  => 'ring-rose-500',
                ],
            ];
        @endphp
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ($cards as $card)
                @php
                    $isActive  = $activeStatut === $card['key'];
                    $href      = $card['key'] === ''
                        ? route('pca.objectifs.index', array_filter(['search' => $filters['search']]))
                        : route('pca.objectifs.index', array_filter(['search' => $filters['search'], 'statut' => $card['key']]));
                @endphp
                <a href="{{ $href }}"
                   class="group flex flex-col rounded-[20px] border px-5 py-4 shadow-sm transition {{ $card['color'] }}
                          {{ $isActive ? 'ring-2 '.$card['ring'].' shadow-md' : 'hover:shadow-md' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $card['label'] }}</p>
                            <p class="mt-2 text-4xl font-black leading-none {{ $card['val'] }}">{{ $card['value'] }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $card['iconBg'] }} transition group-hover:scale-105">
                            <i class="{{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-400">{{ $card['meta'] }}</p>
                    @if ($isActive)
                        <span class="mt-2 inline-flex items-center gap-1 self-start rounded-full bg-white/70 px-2.5 py-0.5 text-[10px] font-black text-slate-600">
                            <i class="fas fa-filter text-[8px]"></i> Filtre actif
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- ── Filtre + compteur ─────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('pca.objectifs.index') }}"
                  class="flex flex-1 items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100">
                @if ($filters['statut'])
                    <input type="hidden" name="statut" value="{{ $filters['statut'] }}">
                @endif
                <i class="fas fa-search text-slate-300 text-sm"></i>
                <input type="text" name="search" value="{{ $filters['search'] }}"
                    placeholder="Rechercher par titre ou année…"
                    class="min-w-0 flex-1 bg-transparent text-sm font-semibold text-slate-700 placeholder-slate-300 outline-none">
                @if ($filters['search'])
                    <a href="{{ route('pca.objectifs.index', array_filter(['statut' => $filters['statut']])) }}"
                       class="text-slate-300 transition hover:text-slate-500">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
                <button type="submit"
                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                    <i class="fas fa-filter mr-1.5"></i> Filtrer
                </button>
            </form>

            {{-- Vue rapide --}}
            <div class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-100">
                <span class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">
                    {{ $fiches->count() }} fiche(s) · page {{ $fiches->currentPage() }}/{{ $fiches->lastPage() }}
                </span>
                @if ($activeStatut || $filters['search'])
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black text-emerald-700">
                        filtré
                    </span>
                @endif
            </div>
        </div>

        {{-- ── Alerte en attente ────────────────────────────────────────────── --}}
        @if ($stats['en_attente'] > 0 && !$activeStatut)
        <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3">
            <i class="fas fa-hourglass-half text-amber-500"></i>
            <p class="text-sm font-semibold text-amber-700">
                <span class="font-black">{{ $stats['en_attente'] }}</span> fiche(s) en attente de validation par le DG.
            </p>
            <a href="{{ route('pca.objectifs.index', ['statut' => 'en_attente']) }}"
               class="ml-auto shrink-0 text-xs font-black text-amber-600 underline-offset-2 hover:underline">
                Voir &rarr;
            </a>
        </div>
        @endif

        {{-- ── Tableau principal ────────────────────────────────────────────── --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">

            {{-- Table header --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Directeur Général</p>
                    <p class="mt-0.5 text-sm font-black text-slate-800">
                        Contrats d'objectifs
                        @if ($activeStatut)
                            <span class="ml-2 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-black text-emerald-700">
                                {{ match($activeStatut) { 'acceptee' => 'Acceptées', 'en_attente' => 'En attente', 'refusee' => 'Refusées', default => '' } }}
                            </span>
                        @endif
                    </p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">
                    {{ $fiches->total() }} fiche(s)
                </span>
            </div>

            @forelse ($fiches as $fiche)
                @php
                    $assignable    = $fiche->assignable;
                    $cibleNom      = $assignable?->name ?? 'DG non renseigné';
                    $cibleInitiale = strtoupper(substr($cibleNom, 0, 1));

                    $statut        = $fiche->statut ?? 'en_attente';
                    $progressValue = (int) ($fiche->avancement_percentage ?? 0);
                    $progressBar   = $progressValue >= 75 ? 'bg-emerald-500'
                        : ($progressValue >= 40 ? 'bg-sky-500'
                        : ($progressValue > 0  ? 'bg-amber-400' : 'bg-slate-200'));

                    // Deadline alert: within 30 days & not accepted
                    $echeance         = \Illuminate\Support\Carbon::parse($fiche->date_echeance);
                    $isNearDeadline   = $echeance->isPast() || $echeance->diffInDays(now()) <= 30;
                    $isPastDeadline   = $echeance->isPast();
                    $deadlineClass    = $isPastDeadline
                        ? 'text-rose-600 font-black'
                        : ($isNearDeadline && $statut !== 'acceptee' ? 'text-amber-600 font-bold' : 'text-slate-500');
                    $deadlineIcon     = $isPastDeadline ? 'fas fa-circle-exclamation text-rose-500'
                        : ($isNearDeadline && $statut !== 'acceptee' ? 'fas fa-clock text-amber-500' : '');

                    $statusCls   = match ($statut) {
                        'acceptee' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'refusee'  => 'bg-rose-100 text-rose-700 border-rose-200',
                        default    => 'bg-amber-100 text-amber-700 border-amber-200',
                    };
                    $statusLabel = match ($statut) {
                        'acceptee' => 'Acceptée',
                        'refusee'  => 'Refusée',
                        default    => 'En attente',
                    };
                    $statusDot = match ($statut) {
                        'acceptee' => 'bg-emerald-500',
                        'refusee'  => 'bg-rose-500',
                        default    => 'bg-amber-400',
                    };
                @endphp
                <div class="group border-b border-slate-50 px-6 py-5 transition hover:bg-slate-50/60 last:border-b-0">
                    <div class="flex flex-wrap items-center gap-4">

                        {{-- Avatar --}}
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 text-base font-black shadow-sm">
                            {{ $cibleInitiale }}
                        </div>

                        {{-- Identity + fiche title --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-baseline gap-2">
                                <a href="{{ route('pca.objectifs.show', $fiche->id) }}"
                                   class="text-base font-black text-slate-900 transition hover:text-emerald-700 group-hover:underline underline-offset-2">
                                    {{ $fiche->titre }}
                                </a>
                                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[10px] font-black {{ $statusCls }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                <span class="font-semibold">
                                    <i class="fas fa-user mr-1 text-[9px]"></i>{{ $cibleNom }}
                                </span>
                                <span class="text-slate-200">·</span>
                                <span class="font-semibold">
                                    <i class="fas fa-calendar mr-1 text-[9px]"></i>
                                    Du {{ \Illuminate\Support\Carbon::parse($fiche->date)->format('d/m/Y') }}
                                </span>
                                <span class="text-slate-200">·</span>
                                <span class="{{ $deadlineClass }}">
                                    @if ($deadlineIcon)<i class="{{ $deadlineIcon }} mr-1"></i>@endif
                                    Échéance {{ $echeance->format('d/m/Y') }}
                                    @if ($isPastDeadline) (dépassée)@elseif($isNearDeadline && $statut !== 'acceptee') (proche)@endif
                                </span>
                                <span class="text-slate-200">·</span>
                                <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 font-black text-slate-600">
                                    <i class="fas fa-bullseye text-[9px]"></i>
                                    {{ $fiche->objectifs_count ?? 0 }} objectif(s)
                                </span>
                            </div>
                        </div>

                        {{-- Progress --}}
                        <div class="hidden w-44 shrink-0 sm:block">
                            <div class="mb-1.5 flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Avancement</span>
                                <span class="text-xs font-black text-slate-700">{{ $progressValue }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full transition-all {{ $progressBar }}" style="width:{{ $progressValue }}%"></div>
                            </div>
                            @if ($progressValue === 100)
                                <p class="mt-1 text-[10px] font-black text-emerald-600">
                                    <i class="fas fa-check mr-0.5"></i> Complété
                                </p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex shrink-0 items-center gap-2">
                            {{-- Consultation (neutre) --}}
                            <a href="{{ route('pca.objectifs.show', $fiche->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700"
                               title="Voir la fiche">
                                <i class="fas fa-eye text-[10px]"></i>
                                <span class="hidden sm:inline">Voir</span>
                            </a>

                            {{-- Contrat PDF --}}
                            <a href="{{ route('pca.objectifs.contrat', $fiche->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:border-slate-400"
                               title="Contrat PDF">
                                <i class="fas fa-file-pdf text-[10px]"></i>
                                <span class="hidden sm:inline">Contrat</span>
                            </a>

                            {{-- Suppression (critique, uniquement si en_attente) --}}
                            @if ($statut === 'en_attente' || $fiche->statut === null)
                                <form method="POST" action="{{ route('pca.objectifs.destroy', $fiche->id) }}"
                                      onsubmit="return confirm('Supprimer définitivement cette fiche d\'objectifs ?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-black text-rose-600 shadow-sm transition hover:border-rose-400 hover:bg-rose-100 hover:text-rose-700"
                                            title="Supprimer la fiche">
                                        <i class="fas fa-trash text-[10px]"></i>
                                        <span class="hidden sm:inline">Supprimer</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Mobile progress bar --}}
                    <div class="mt-3 sm:hidden">
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Avancement</span>
                            <span class="text-xs font-black text-slate-700">{{ $progressValue }}%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $progressBar }}" style="width:{{ $progressValue }}%"></div>
                        </div>
                    </div>
                </div>

            @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto max-w-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                            <i class="fas fa-folder-open text-2xl text-slate-300"></i>
                        </div>
                        <p class="mt-4 text-base font-black text-slate-700">Aucune fiche trouvée</p>
                        <p class="mt-1.5 text-sm text-slate-400">
                            @if ($activeStatut || $filters['search'])
                                Aucun résultat pour ce filtre.
                                <a href="{{ route('pca.objectifs.index') }}" class="font-bold text-emerald-600 hover:underline">Réinitialiser</a>
                            @else
                                Créez une première fiche pour démarrer le suivi du Directeur Général.
                            @endif
                        </p>
                        @if (!$activeStatut && !$filters['search'] && $objectifsEnabled)
                            <a href="{{ route('pca.objectifs.create') }}"
                               class="mt-5 inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                                <i class="fas fa-plus"></i> Nouvelle fiche
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if ($fiches->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $fiches->withQueryString()->links() }}
                </div>
            @endif
        </div>

        </div>
    </div>
</div>
@endsection
