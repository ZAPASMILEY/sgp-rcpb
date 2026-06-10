@extends('layouts.dga')
@section('title', 'Mes Subordonnés | '.config('app.name'))

@section('content')
@php
    $tab     = $filters['tab'];
    $dtId    = $filters['dtId'];
    $statut  = $filters['statut'];
    $annee   = $filters['annee'];
    $search  = $filters['search'];
    $sort    = $filters['sort'];
    $sortDir = $filters['sortDir'];

    $hasFilter = $dtId || $statut || $annee || $search;
    $indexUrl  = route('dga.subordonnes.index');

    // Tri par colonne — inverse la direction si déjà actif
    $sortLink = function (string $col) use ($tab, $dtId, $statut, $annee, $search, $sort, $sortDir, $indexUrl) {
        $dir = ($sort === $col && $sortDir === 'asc') ? 'desc' : 'asc';
        return $indexUrl . '?' . http_build_query(array_filter([
            'tab'      => $tab,
            'dt_id'    => $dtId,
            'statut'   => $statut,
            'annee'    => $annee,
            'search'   => $search,
            'sort'     => $col,
            'sort_dir' => $dir,
        ]));
    };
    $sortIcon = fn (string $col) => $sort === $col
        ? ($sortDir === 'asc' ? ' <i class="fas fa-sort-up ml-0.5 text-violet-400"></i>' : ' <i class="fas fa-sort-down ml-0.5 text-violet-400"></i>')
        : ' <i class="fas fa-sort ml-0.5 text-slate-300"></i>';
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">Espace DGA</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Mes Subordonnés</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">
                    {{ $directeursTechniques->count() }} Directeur(s) Technique(s)
                    @if($secretaire) · 1 Secrétaire @endif
                </p>
            </div>
            {{-- Secrétaire compact --}}
            @if($secretaire)
            <a href="{{ route('dga.subordonnes.show', $secretaire) }}"
               class="inline-flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/20 transition hover:bg-white/20">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/20 text-white font-black text-sm">
                    {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                </div>
                <div class="text-left">
                    <p class="text-xs font-black text-white">{{ $secretaire->name }}</p>
                    <p class="text-[10px] text-violet-200">Secrétaire</p>
                </div>
                <i class="fas fa-folder-open text-violet-300 text-xs ml-1"></i>
            </a>
            @endif
        </div>
    </div>

    @include('layouts._features_notice')

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- ── Cartes DT ────────────────────────────────────────────────── --}}
        <div class="grid gap-3" style="grid-template-columns: repeat({{ min($directeursTechniques->count(), 4) }}, minmax(0,1fr))">
            @foreach($directeursTechniques as $dt)
            @php
                $isActive   = $dtId == $dt->id;
                $delegation = $dt->agent?->directedDelegation;
                $dtFilterUrl = $indexUrl . '?' . http_build_query(array_filter([
                    'tab'      => $tab,
                    'dt_id'    => $isActive ? null : $dt->id,
                    'statut'   => $statut,
                    'annee'    => $annee,
                    'search'   => $search,
                    'sort'     => $sort,
                    'sort_dir' => $sortDir,
                ]));
            @endphp
            <a href="{{ $dtFilterUrl }}"
               class="flex items-center gap-3 rounded-2xl border px-4 py-3.5 transition
                      {{ $isActive
                          ? 'border-violet-300 bg-violet-50 shadow-md shadow-violet-100'
                          : 'border-slate-200 bg-white hover:border-violet-200 hover:bg-violet-50/40 shadow-sm' }}">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl font-black text-sm text-white shadow
                            {{ $isActive ? 'bg-violet-600' : 'bg-gradient-to-br from-emerald-500 to-teal-600' }}">
                    {{ strtoupper(substr($dt->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-black {{ $isActive ? 'text-violet-800' : 'text-slate-800' }}">
                        {{ $dt->name }}
                    </p>
                    @if($delegation)
                    <p class="truncate text-[10px] {{ $isActive ? 'text-violet-500' : 'text-slate-400' }}">
                        <i class="fas fa-map-marker-alt mr-0.5"></i>{{ $delegation->region }}
                    </p>
                    @endif
                </div>
                @if($isActive)
                <span class="shrink-0 rounded-full bg-violet-600 px-2 py-0.5 text-[10px] font-black text-white">
                    <i class="fas fa-times"></i>
                </span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- ── Panneau principal ─────────────────────────────────────────── --}}
        <div class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">

            {{-- Header : onglets + bouton nouveau --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ $indexUrl }}?tab=objectifs{{ $dtId ? '&dt_id='.$dtId : '' }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs'
                               ? 'border border-slate-200 bg-white text-emerald-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i> Objectifs
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'objectifs' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                    <a href="{{ $indexUrl }}?tab=evaluations{{ $dtId ? '&dt_id='.$dtId : '' }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'evaluations'
                               ? 'border border-slate-200 bg-white text-cyan-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i> Évaluations
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'evaluations' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>
                </div>

                @if($tab === 'objectifs')
                    @if($objectifsEnabled && !($dtId && $ficheBlocksNewForDt))
                        <a href="{{ route('dga.sub-objectifs.create', $dtId ? ['subordonne_id' => $dtId] : []) }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-emerald-600">
                            <i class="fas fa-plus text-xs"></i> Nouvelle fiche
                        </a>
                    @else
                        <span title="{{ ($dtId && $ficheBlocksNewForDt) ? 'Une fiche d\'objectifs est déjà assignée à ce DT.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                              class="ent-btn-disabled-light">
                            <i class="fas fa-plus text-xs"></i> Nouvelle fiche
                        </span>
                    @endif
                @elseif($tab === 'evaluations')
                    @if($evaluationsEnabled && !($dtId && !$ficheAccepteeForDt) && !($dtId && $evaluationEnCoursForDt))
                        <a href="{{ route('dga.sub-evaluations.create', $dtId ? ['subordonne_id' => $dtId] : []) }}"
                           class="inline-flex items-center gap-2 rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-cyan-700">
                            <i class="fas fa-plus text-xs"></i> Nouvelle évaluation
                        </a>
                    @else
                        <span title="{{ ($dtId && $evaluationEnCoursForDt) ? 'Une évaluation est déjà en cours (brouillon ou soumise) pour ce DT.' : (($dtId && !$ficheAccepteeForDt) ? 'Aucune fiche d\'objectifs acceptée pour ce DT.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')) }}"
                              class="ent-btn-disabled-light">
                            <i class="fas fa-plus text-xs"></i> Nouvelle évaluation
                        </span>
                    @endif
                @endif
            </div>

            {{-- ── Cartes stats ──────────────────────────────────────────── --}}
            @if($tab === 'objectifs')
            @php $cards = [
                ['label'=>'Total',      'value'=>$fichesStats['total'],      'accent'=>'bg-slate-300',   'tone'=>'border-slate-100 bg-white'],
                ['label'=>'Acceptées',  'value'=>$fichesStats['acceptees'],  'accent'=>'bg-emerald-500', 'tone'=>'border-emerald-100 bg-emerald-50/60'],
                ['label'=>'En attente', 'value'=>$fichesStats['en_attente'], 'accent'=>'bg-amber-400',   'tone'=>'border-amber-100 bg-amber-50/60'],
                ['label'=>'Refusées',   'value'=>$fichesStats['refusees'],   'accent'=>'bg-rose-500',    'tone'=>'border-rose-100 bg-rose-50/60'],
            ]; @endphp
            @else
            @php $cards = [
                ['label'=>'Total',     'value'=>$evaluationsStats['total'],    'accent'=>'bg-slate-300',   'tone'=>'border-slate-100 bg-white'],
                ['label'=>'Brouillons','value'=>$evaluationsStats['brouillon'],'accent'=>'bg-slate-400',   'tone'=>'border-slate-100 bg-slate-50/80'],
                ['label'=>'Soumises',  'value'=>$evaluationsStats['soumis'],   'accent'=>'bg-amber-400',   'tone'=>'border-amber-100 bg-amber-50/60'],
                ['label'=>'Validées',  'value'=>$evaluationsStats['valide'],   'accent'=>'bg-emerald-500', 'tone'=>'border-emerald-100 bg-emerald-50/60'],
            ]; @endphp
            @endif
            <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach($cards as $card)
                <div class="relative overflow-hidden rounded-2xl border shadow-sm {{ $card['tone'] }}">
                    <div class="absolute inset-y-0 left-0 w-1 {{ $card['accent'] }}"></div>
                    <div class="px-5 py-4 pl-6">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $card['label'] }}</p>
                        <p class="mt-2 text-4xl font-black leading-none text-slate-900">{{ $card['value'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Barre de filtres ──────────────────────────────────────── --}}
            <form method="GET" action="{{ $indexUrl }}"
                  class="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
                @if($dtId)<input type="hidden" name="dt_id" value="{{ $dtId }}">@endif

                {{-- Statut --}}
                <select name="statut"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                    <option value="">Tous statuts</option>
                    @if($tab === 'objectifs')
                        <option value="en_attente" @selected($statut === 'en_attente')>En attente</option>
                        <option value="acceptee"   @selected($statut === 'acceptee')>Acceptée</option>
                        <option value="refusee"    @selected($statut === 'refusee')>Refusée</option>
                    @else
                        <option value="brouillon" @selected($statut === 'brouillon')>Brouillon</option>
                        <option value="soumis"    @selected($statut === 'soumis')>Soumise</option>
                        <option value="valide"    @selected($statut === 'valide')>Validée</option>
                        <option value="refuse"    @selected($statut === 'refuse')>Refusée</option>
                    @endif
                </select>

                {{-- Année --}}
                <input type="number" name="annee" value="{{ $annee }}" placeholder="Année"
                       min="2020" max="2099"
                       class="w-24 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">

                {{-- Recherche (objectifs seulement) --}}
                @if($tab === 'objectifs')
                <div class="relative flex-1 min-w-36">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="fas fa-search text-[10px]"></i>
                    </span>
                    <input name="search" type="text" value="{{ $search }}" placeholder="Titre..."
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                </div>
                @endif

                {{-- Tri --}}
                <select name="sort"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                    <option value="date"   @selected($sort === 'date')>Date</option>
                    <option value="statut" @selected($sort === 'statut')>Statut</option>
                </select>
                <select name="sort_dir"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
                    <option value="desc" @selected($sortDir === 'desc')>Récent</option>
                    <option value="asc"  @selected($sortDir === 'asc')>Ancien</option>
                </select>

                <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
                    Filtrer
                </button>
                @if($hasFilter)
                <a href="{{ $indexUrl }}?tab={{ $tab }}"
                   class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500 transition hover:text-slate-800"
                   title="Réinitialiser les filtres">
                    <i class="fas fa-times text-[10px]"></i>
                </a>
                @endif
            </form>

            {{-- ── Table objectifs ──────────────────────────────────────── --}}
            @if($tab === 'objectifs')
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80">
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    <a href="{{ $sortLink('name') }}">Directeur Technique{!! $sortIcon('name') !!}</a>
                                </th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Fiche</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    <a href="{{ $sortLink('statut') }}">Statut{!! $sortIcon('statut') !!}</a>
                                </th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    <a href="{{ $sortLink('date') }}">Date{!! $sortIcon('date') !!}</a>
                                </th>
                                <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($fiches as $fiche)
                            @php
                                $dt = $fiche->assignable;
                                $statut = $fiche->statut ?? 'en_attente';
                                $statusCls = match($statut) {
                                    'acceptee'  => 'bg-emerald-100 text-emerald-700',
                                    'refusee'   => 'bg-rose-100 text-rose-700',
                                    'contesté'  => 'bg-orange-100 text-orange-700',
                                    'brouillon' => 'bg-slate-100 text-slate-600',
                                    default     => 'bg-amber-100 text-amber-700',
                                };
                                $dotCls = match($statut) {
                                    'acceptee'  => 'bg-emerald-500',
                                    'refusee'   => 'bg-rose-500',
                                    'contesté'  => 'bg-orange-400',
                                    'brouillon' => 'bg-slate-400',
                                    default     => 'bg-amber-400',
                                };
                                $statusLabel = match($statut) {
                                    'acceptee'  => 'Acceptée',
                                    'refusee'   => 'Refusée',
                                    'contesté'  => 'Contestée',
                                    'brouillon' => 'Brouillon',
                                    default     => 'En attente',
                                };
                                $progress = (int) ($fiche->avancement_percentage ?? 0);
                                $avBarCls = $progress >= 80 ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-sky-500' : ($progress >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                $avTxtCls = $progress >= 80 ? 'text-emerald-700' : ($progress >= 50 ? 'text-sky-700' : ($progress >= 25 ? 'text-amber-600' : 'text-slate-500'));
                                $objCount = $fiche->objectifs_count ?? 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                {{-- DT --}}
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('dga.subordonnes.show', $dt) }}"
                                       class="font-black text-violet-700 hover:underline">
                                        {{ $dt?->name ?? '—' }}
                                    </a>
                                    @if($dt?->agent?->directedDelegation)
                                    <p class="mt-0.5 text-[10px] text-slate-400">
                                        <i class="fas fa-map-marker-alt mr-0.5"></i>
                                        {{ $dt->agent->directedDelegation->region }} / {{ $dt->agent->directedDelegation->ville }}
                                    </p>
                                    @endif
                                </td>
                                {{-- Fiche --}}
                                <td class="px-5 py-3.5">
                                    <p class="font-black text-slate-800">{{ $fiche->titre }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                                            <i class="fas fa-calendar-alt text-[9px]"></i>
                                            {{ $fiche->annee_value ?? \Carbon\Carbon::parse($fiche->date)->year }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                                            <i class="fas fa-bullseye text-[9px]"></i>
                                            {{ $objCount }} objectif{{ $objCount > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                </td>
                                {{-- Avancement --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $avBarCls }}" style="width:{{ $progress }}%"></div>
                                        </div>
                                        <span class="text-sm font-black {{ $avTxtCls }}">{{ $progress }}%</span>
                                    </div>
                                </td>
                                {{-- Statut --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                {{-- Date --}}
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}
                                </td>
                                {{-- Actions --}}
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('dga.sub-objectifs.show', $fiche->id) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-100 hover:text-blue-600"
                                           title="Voir la fiche">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        @if($statut !== 'acceptee')
                                        <form method="POST" action="{{ route('dga.sub-objectifs.destroy', $fiche->id) }}"
                                              onsubmit="return confirm('Supprimer cette fiche ?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-600"
                                                    title="Supprimer">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                            <i class="fas fa-bullseye text-xl text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-black text-slate-700">Aucune fiche d'objectifs</p>
                                        <p class="mt-1 text-xs text-slate-400">Aucune fiche ne correspond aux filtres sélectionnés.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($fiches->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">{{ $fiches->links() }}</div>
            @endif

            {{-- ── Table évaluations ──────────────────────────────────────── --}}
            @else
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80">
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Directeur Technique</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    <a href="{{ $sortLink('statut') }}">Statut{!! $sortIcon('statut') !!}</a>
                                </th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                                    <a href="{{ $sortLink('date') }}">Date{!! $sortIcon('date') !!}</a>
                                </th>
                                <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($evaluations as $evaluation)
                            @php
                                $dt   = $evaluation->evaluable;
                                $note = (float) $evaluation->note_finale;
                                $notePct    = max(0, min(100, ($note / 10) * 100));
                                $noteBarCls = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                $notePillCls = $notePct >= 85
                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                    : ($notePct >= 70 ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                                    : ($notePct >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                                    : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'));
                                $statusCls = match($evaluation->statut) {
                                    'valide'      => 'bg-emerald-100 text-emerald-700',
                                    'soumis'      => 'bg-amber-100 text-amber-700',
                                    'refuse'      => 'bg-rose-100 text-rose-700',
                                    'reclamation' => 'bg-orange-100 text-orange-700',
                                    'a_reviser'   => 'bg-purple-100 text-purple-700',
                                    default       => 'bg-slate-100 text-slate-600',
                                };
                                $dotCls = match($evaluation->statut) {
                                    'valide'      => 'bg-emerald-500',
                                    'soumis'      => 'bg-amber-400',
                                    'refuse'      => 'bg-rose-500',
                                    'reclamation' => 'bg-orange-500',
                                    'a_reviser'   => 'bg-purple-500',
                                    default       => 'bg-slate-400',
                                };
                                $statusLabel = match($evaluation->statut) {
                                    'valide'      => 'Validée',
                                    'soumis'      => 'Soumise',
                                    'refuse'      => 'Refusée',
                                    'reclamation' => 'Réclamation',
                                    'a_reviser'   => 'À réviser',
                                    'brouillon'   => 'Brouillon',
                                    default       => ucfirst((string) $evaluation->statut),
                                };
                                $identification = $evaluation->identification;
                                $sem = trim((string) ($identification?->semestre ?? ''));
                                if ($sem === '') { $sem = $evaluation->date_debut->month <= 6 ? '1' : '2'; }
                                $anneeEval = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                {{-- DT --}}
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('dga.subordonnes.show', $dt) }}"
                                       class="font-black text-violet-700 hover:underline">
                                        {{ $dt?->name ?? '—' }}
                                    </a>
                                    @if($dt?->agent?->directedDelegation)
                                    <p class="mt-0.5 text-[10px] text-slate-400">
                                        <i class="fas fa-map-marker-alt mr-0.5"></i>
                                        {{ $dt->agent->directedDelegation->region }} / {{ $dt->agent->directedDelegation->ville }}
                                    </p>
                                    @endif
                                </td>
                                {{-- Période --}}
                                <td class="px-5 py-3.5">
                                    <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                        <i class="fas fa-calendar-alt text-[9px] text-slate-400"></i>
                                        S{{ $sem }} / {{ $anneeEval }}
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-400">
                                        {{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}
                                    </p>
                                </td>
                                {{-- Note --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-baseline gap-0.5 rounded-lg px-2.5 py-1 text-sm font-black {{ $notePillCls }}">
                                        {{ number_format($note, 2, ',', ' ') }}<span class="text-[10px] font-bold opacity-60">/10</span>
                                    </span>
                                    <div class="mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $noteBarCls }}" style="width:{{ $notePct }}%"></div>
                                    </div>
                                </td>
                                {{-- Statut --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                {{-- Date --}}
                                <td class="px-5 py-3.5 text-xs text-slate-400">
                                    {{ $evaluation->date_debut->format('d/m/Y') }}
                                </td>
                                {{-- Actions --}}
                                <td class="px-5 py-3.5 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('dga.sub-evaluations.show', $evaluation) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-100 hover:text-blue-600"
                                           title="{{ $evaluation->statut === 'brouillon' ? 'Modifier' : 'Voir' }}">
                                            <i class="fas fa-{{ $evaluation->statut === 'brouillon' ? 'pen' : 'eye' }} text-xs"></i>
                                        </a>
                                        @if($evaluation->statut !== 'brouillon')
                                        <a href="{{ route('dga.sub-evaluations.pdf', $evaluation) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-600"
                                           title="PDF" target="_blank">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>
                                        @endif
                                        @if (in_array($evaluation->statut, ['brouillon', 'a_reviser']))
                                            <form method="POST" action="{{ route('dga.sub-evaluations.destroy', $evaluation) }}"
                                                  onsubmit="return confirm('Supprimer définitivement cette évaluation ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-400 shadow-sm transition hover:bg-rose-50 hover:text-rose-600"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                            <i class="fas fa-clipboard-list text-xl text-slate-300"></i>
                                        </div>
                                        <p class="text-sm font-black text-slate-700">Aucune évaluation</p>
                                        <p class="mt-1 text-xs text-slate-400">Aucune évaluation ne correspond aux filtres sélectionnés.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($evaluations->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">{{ $evaluations->links() }}</div>
            @endif
            @endif

        </div>{{-- fin panneau principal --}}

    </div>
    </div>
</div>
@endsection
