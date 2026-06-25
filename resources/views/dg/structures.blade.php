@extends('layouts.dg')

@section('title', 'Structures | Espace DG · ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero émeraude ───────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-teal-400/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            {{-- Titre --}}
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow-lg ring-1 ring-white/20">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-200">{{ auth()->user()?->agent?->role_genree ?? 'Directeur Général' }} · Réseau RCPB</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">Structures du réseau</h1>
                    <p class="mt-0.5 text-sm text-emerald-100/80">Vue agrégée par structure avec statistiques d'évaluation</p>
                </div>
            </div>

            {{-- Contrôles : filtre type + tri + PDF --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Filtre Type --}}
                <form method="GET" action="{{ route('dg.structures') }}" id="filterForm" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="sort" value="{{ $sortBy }}">
                    <select name="type" onchange="document.getElementById('filterForm').submit()"
                        class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm ring-1 ring-white/20 outline-none cursor-pointer">
                        <option value="" class="text-slate-900" {{ !$typeFilter ? 'selected' : '' }}>Tous les types</option>
                        <option value="delegation" class="text-slate-900" {{ $typeFilter === 'delegation' ? 'selected' : '' }}>Délégations Techniques</option>
                        <option value="direction"  class="text-slate-900" {{ $typeFilter === 'direction'  ? 'selected' : '' }}>Directions</option>
                        <option value="caisse"     class="text-slate-900" {{ $typeFilter === 'caisse'     ? 'selected' : '' }}>Caisses</option>
                        <option value="agence"     class="text-slate-900" {{ $typeFilter === 'agence'     ? 'selected' : '' }}>Agences</option>
                        <option value="guichet"    class="text-slate-900" {{ $typeFilter === 'guichet'    ? 'selected' : '' }}>Guichets</option>
                        <option value="service"    class="text-slate-900" {{ $typeFilter === 'service'    ? 'selected' : '' }}>Services</option>
                    </select>
                </form>

                {{-- Tri --}}
                <form method="GET" action="{{ route('dg.structures') }}" id="sortForm" class="flex items-center gap-2">
                    @if($typeFilter)<input type="hidden" name="type" value="{{ $typeFilter }}">@endif
                    <select name="sort" onchange="document.getElementById('sortForm').submit()"
                        class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white backdrop-blur-sm ring-1 ring-white/20 outline-none cursor-pointer">
                        <option value="note"   class="text-slate-900" {{ $sortBy === 'note'   ? 'selected' : '' }}>Trier : Note ↓</option>
                        <option value="nom"    class="text-slate-900" {{ $sortBy === 'nom'    ? 'selected' : '' }}>Trier : Nom A→Z</option>
                        <option value="type"   class="text-slate-900" {{ $sortBy === 'type'   ? 'selected' : '' }}>Trier : Type</option>
                        <option value="agents" class="text-slate-900" {{ $sortBy === 'agents' ? 'selected' : '' }}>Trier : Nb agents ↓</option>
                        <option value="evals"  class="text-slate-900" {{ $sortBy === 'evals'  ? 'selected' : '' }}>Trier : Nb évaluations ↓</option>
                    </select>
                </form>

                {{-- Réinitialiser --}}
                @if($typeFilter || $sortBy !== 'note')
                    <a href="{{ route('dg.structures') }}"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2.5 text-xs font-bold text-emerald-200 ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                @endif

                {{-- Bouton PDF --}}
                <a href="{{ route('dg.structures.pdf', array_filter(['type' => $typeFilter, 'sort' => $sortBy])) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-black text-emerald-700 shadow-lg transition hover:bg-emerald-50">
                    <i class="fas fa-file-pdf text-rose-500"></i>
                    Télécharger PDF
                </a>
            </div>
        </div>
    </div>

    <div class="px-6 lg:px-10">

        {{-- ── Bannière année ouverte ──────────────────────────────────────── --}}
        @if (! $notesVisibles)
            <div class="mt-6 flex items-start gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-800">Notes indisponibles — Année {{ $anneeOuverte->annee }} en cours d'exercice</p>
                    <p class="mt-0.5 text-xs text-amber-600">
                        Les notes des structures seront affichées une fois l'année <strong>{{ $anneeOuverte->annee }}</strong>
                        clôturée par l'administrateur. Les évaluations continuent d'être enregistrées normalement.
                    </p>
                </div>
            </div>
        @elseif ($derniereAnnee)
            <div class="mt-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3">
                <i class="fas fa-circle-check text-emerald-500"></i>
                <p class="text-sm font-semibold text-emerald-700">
                    Affichage des notes pour l'année <strong>{{ $derniereAnnee->annee }}</strong> (clôturée).
                </p>
            </div>
        @endif

        {{-- ── KPIs ─────────────────────────────────────────────────────────── --}}
        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-5">

            {{-- Structures --}}
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Structures</p>
                    <p class="text-xl font-black text-slate-800">{{ $globalStats['nb_structures'] }}</p>
                </div>
            </div>

            {{-- Agents --}}
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Agents</p>
                    <p class="text-xl font-black text-slate-800">{{ number_format($globalStats['nb_agents']) }}</p>
                </div>
            </div>

            {{-- Note globale --}}
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <i class="fas fa-globe"></i>
                </div>
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Note globale</p>
                    <p class="text-xl font-black text-slate-800">
                        @if ($notesVisibles)
                            {{ $perimetreStats['globale'] !== null ? number_format($perimetreStats['globale'], 2).' /10' : '—' }}
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </p>
                    <p class="text-[9px] text-slate-400 mt-0.5">Faîtière + Terrain</p>
                </div>
            </div>

        </div>

        {{-- ── Tableau des structures ───────────────────────────────────────── --}}
        <div class="mt-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-black text-slate-700">
                    {{ $structures->count() }} structure(s)
                    @if($typeFilter)
                        <span class="ml-1 text-sm font-semibold text-slate-400">· filtre: {{ $typeFilter }}</span>
                    @endif
                    <span class="ml-1 text-sm font-semibold text-slate-400">· tri: {{ ['note'=>'note ↓','nom'=>'nom A→Z','type'=>'type','agents'=>'agents ↓','evals'=>'évaluations ↓'][$sortBy] }}</span>
                </h2>
            </div>

            @include('structures._table', ['structures' => $structures])
        </div>

    </div>
</div>
@endsection
