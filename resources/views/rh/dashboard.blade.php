@extends('layouts.rh')

@section('title', 'Tableau de bord RH | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#2e1065 0%,#4c1d95 50%,#6d28d9 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-purple-300/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            {{-- Identité RH --}}
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow-lg ring-1 ring-white/20">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-purple-200">Ressources Humaines · RCPB</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">Vue d'ensemble du réseau</h1>
                    <p class="mt-0.5 text-sm text-purple-100/80">Toutes les données de performance — agents et cadres</p>
                </div>
            </div>
        </div>

        {{-- Mini KPIs globaux du bandeau --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="fas fa-clipboard-list"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-purple-200">Total évaluations</p>
                    <p class="text-xl font-black text-white">{{ $stats['total'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="fas fa-circle-check"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-purple-200">Validées</p>
                    <p class="text-xl font-black text-white">{{ $stats['valide'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="fas fa-hourglass-half"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-purple-200">En attente</p>
                    <p class="text-xl font-black text-white">{{ $stats['soumis'] }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="fas fa-users"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-purple-200">Agents réseau</p>
                    <p class="text-xl font-black text-white">{{ $stats['agents'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Contenu principal ────────────────────────────────────────────────── --}}
    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-6">

            {{-- Alerte agents sans évaluation --}}
            @if ($openAnnee && $agentsSansEval > 0)
                @php $rhSansEvalUrl = request()->fullUrlWithQuery(['sans_eval' => $filters['sansEval'] ? null : 1]); @endphp
                <a href="{{ $rhSansEvalUrl }}"
                   class="flex items-center gap-4 rounded-2xl border px-5 py-4 transition hover:shadow-md
                          {{ $filters['sansEval'] ? 'border-orange-400 bg-orange-100' : 'border-orange-200 bg-orange-50' }}">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-orange-800">
                            {{ $agentsSansEval }} agent{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation
                            — {{ $openAnnee->annee }}{{ $openSemestre ? ' · Semestre '.$openSemestre->numero : '' }}
                        </p>
                        <p class="mt-0.5 text-xs text-orange-600">
                            {{ $filters['sansEval'] ? 'Cliquez pour masquer la liste.' : 'Cliquez pour voir la liste avec contacts.' }}
                        </p>
                    </div>
                    <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-3 text-lg font-black text-white shadow-sm">
                        {{ $agentsSansEval }}
                    </span>
                </a>
            @endif

            {{-- Liste agents sans évaluation --}}
            @if ($filters['sansEval'] && $openAnnee)
                <div class="rounded-2xl border border-orange-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-orange-100 bg-orange-50 px-5 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm font-black text-orange-800 shrink-0">
                            <i class="fas fa-user-xmark mr-2 text-orange-500"></i>
                            Agents sans évaluation — {{ $openAnnee->annee }}{{ $openSemestre ? ' · S'.$openSemestre->numero : '' }}
                        </p>
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="relative flex-1 sm:w-64">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                                <input id="rh-se-search" type="text" placeholder="Rechercher…"
                                       class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-7 pr-3 text-xs font-semibold text-slate-700 outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-100">
                            </div>
                            <span id="rh-se-count" class="shrink-0 rounded-full bg-orange-200 px-2.5 py-0.5 text-xs font-black text-orange-800">{{ $listeSansEval->count() }}</span>
                            <button type="button" onclick="document.getElementById('modal-relance').classList.remove('hidden')"
                                    class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-orange-500 px-3 py-1.5 text-xs font-black text-white shadow-sm hover:bg-orange-600 transition">
                                <i class="fas fa-bell text-[10px]"></i> Envoyer une alerte
                            </button>
                            <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="shrink-0 text-xs font-bold text-orange-600 hover:underline">Fermer</a>
                        </div>
                    </div>
                    @if ($listeSansEval->isEmpty())
                        <div class="px-6 py-8 text-center text-sm text-slate-400">Tous les agents ont une évaluation validée.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table id="rh-se-table" class="w-full text-sm">
                                <thead><tr class="border-b border-slate-100 bg-slate-50/70">
                                    <th data-col="0" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Nom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                    <th data-col="1" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Prénom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                    <th data-col="2" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Matricule <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                    <th data-col="3" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Fonction <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                    <th data-col="4" class="se-th cursor-pointer select-none px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Structure <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                    <th class="px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Téléphone</th>
                                </tr></thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach ($listeSansEval as $a)
                                    <tr class="se-row hover:bg-orange-50/40 transition-colors">
                                        <td class="px-5 py-2.5 font-semibold text-slate-800">{{ $a->nom }}</td>
                                        <td class="px-5 py-2.5 text-slate-700">{{ $a->prenom }}</td>
                                        <td class="px-5 py-2.5 text-xs font-mono text-slate-500">{{ $a->matricule ?? '—' }}</td>
                                        <td class="px-5 py-2.5 text-xs text-slate-500">{{ $a->role ?? '—' }}</td>
                                        <td class="px-5 py-2.5 text-xs text-slate-500">
                                            @php
                                                $struct = $a->agence?->nom
                                                    ?? $a->guichet?->nom
                                                    ?? $a->service?->nom
                                                    ?? $a->caisse?->nom
                                                    ?? $a->direction?->nom
                                                    ?? $a->delegationTechnique?->region
                                                    ?? $a->entite?->nom
                                                    ?? '—';
                                            @endphp
                                            {{ $struct }}
                                        </td>
                                        <td class="px-5 py-2.5">
                                            @if ($a->numero_telephone)
                                                <a href="tel:{{ $a->numero_telephone }}"
                                                   class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition">
                                                    <i class="fas fa-phone text-[10px]"></i>{{ $a->numero_telephone }}
                                                </a>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Couverture Évaluation --}}
            @if($openAnnee && $totalAgents > 0)
                @php $tauxCouv = round(($agentsEvalues / $totalAgents) * 100); @endphp
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $agentsSansEval === 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                                <i class="fas fa-users-viewfinder"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    Couverture réseau · {{ $openAnnee->annee }}{{ $openSemestre ? ' — S'.$openSemestre->numero : '' }}
                                </p>
                                <p class="text-sm font-black text-slate-900">Évaluation des agents</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="text-center hover:opacity-75 transition">
                                <p class="text-2xl font-black text-emerald-600">{{ $agentsEvalues }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Évalués</p>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['sans_eval' => 1]) }}" class="text-center hover:opacity-75 transition">
                                <p class="text-2xl font-black {{ $agentsSansEval > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ $agentsSansEval }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Restants</p>
                            </a>
                            <div class="text-center">
                                <p class="text-2xl font-black text-slate-700">{{ $totalAgents }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Total</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-4">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-1.5">
                            <span>Progression réseau</span>
                            <span class="{{ $tauxCouv === 100 ? 'text-emerald-600' : ($tauxCouv >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $tauxCouv }}%</span>
                        </div>
                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full transition-all {{ $tauxCouv === 100 ? 'bg-emerald-500' : ($tauxCouv >= 50 ? 'bg-amber-400' : 'bg-rose-500') }}"
                                 style="width:{{ $tauxCouv }}%"></div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Navigation des Onglets --}}
            <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1 self-start">
                @foreach([
                    ['key' => 'evaluations', 'label' => 'Évaluations',  'icon' => 'fas fa-star-half-stroke'],
                    ['key' => 'objectifs',   'label' => 'Objectifs',     'icon' => 'fas fa-bullseye'],
                ] as $t)
                    <a href="{{ route('rh.dashboard') }}?tab={{ $t['key'] }}&search={{ urlencode($filters['search']) }}&statut={{ $filters['statut'] }}&annee={{ $filters['annee'] }}&sexe={{ $filters['sexe'] }}&fonction={{ $filters['fonction'] }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $filters['tab'] === $t['key']
                               ? 'border border-slate-200 bg-white text-violet-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="{{ $t['icon'] }} text-xs"></i>{{ $t['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- ── ONGLET : ÉVALUATIONS ─────────────────────────────────────── --}}
            @if($filters['tab'] === 'evaluations')
                {{-- Barre de recherche & Filtres dédiés aux évaluations --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <form method="GET" action="{{ route('rh.dashboard') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="tab" value="evaluations">
                        
                        <div class="flex-1 min-w-[240px]">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Recherche</label>
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, emploi, évaluateur..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:border-violet-500 focus:bg-white transition">
                        </div>

                        <div class="w-48">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Statut</label>
                            <select name="statut" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none cursor-pointer">
                                <option value="">Tous statuts</option>
                                <option value="soumis" {{ $filters['statut'] === 'soumis' ? 'selected' : '' }}>Soumises</option>
                                <option value="valide" {{ $filters['statut'] === 'valide' ? 'selected' : '' }}>Validées</option>
                                <option value="reclamation" {{ $filters['statut'] === 'reclamation' ? 'selected' : '' }}>Réclamations</option>
                                <option value="refusee" {{ $filters['statut'] === 'refusee' ? 'selected' : '' }}>Refusées</option>
                                <option value="brouillon" {{ $filters['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                            </select>
                        </div>

                        <div class="w-32">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Année</label>
                            <input type="number" name="annee" value="{{ $filters['annee'] }}" min="2020" max="2035" placeholder="Ex: 2026"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-violet-700 transition">
                                <i class="fas fa-filter mr-2"></i>Filtrer
                            </button>
                            @if($filters['search'] || $filters['statut'] || $filters['annee'])
                                <a href="{{ route('rh.dashboard') }}?tab=evaluations" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                                    Effacer
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-4 px-6 py-4 border-b border-slate-100">
                        <h3 class="font-black text-slate-800">Suivi des Évaluations</h3>
                        @if($evaluations)
                        <p class="text-xs text-slate-400">{{ $evaluations->total() }} résultat(s)</p>
                        @endif
                    </div>

                    @if($evaluations && $evaluations->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/60">
                                    <th class="px-6 py-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Agent</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Emploi</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Évaluateur</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Note</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Statut</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Période</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($evaluations as $eval)
                                @php
                                    $ident = $eval->identification;
                                    $nom   = $ident?->nom_prenom
                                        ?? (($eval->evaluable?->prenom ?? '') . ' ' . ($eval->evaluable?->nom ?? '') ?: '—');
                                    $emploi = $ident?->emploi ?? $eval->evaluable?->role ?? '—';
                                    $note   = $eval->note_finale !== null
                                        ? number_format((float)$eval->note_finale, 2, ',', ' ')
                                        : '—';
                                    $statutColors = [
                                        'brouillon'   => 'border-slate-200 bg-slate-100 text-slate-600',
                                        'soumis'      => 'border-amber-200 bg-amber-50 text-amber-700',
                                        'valide'      => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                        'refuse'      => 'border-rose-200 bg-rose-50 text-rose-700',
                                        'reclamation' => 'border-orange-200 bg-orange-50 text-orange-700',
                                    ];
                                    $statutLabels = [
                                        'brouillon'   => 'Brouillon',
                                        'soumis'      => 'Soumise',
                                        'valide'      => 'Validée',
                                        'refuse'      => 'Refusée',
                                        'reclamation' => 'Réclamation',
                                    ];
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-slate-800">{{ trim($nom) ?: '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $emploi }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $eval->evaluateur?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="font-black text-slate-800">{{ $note }}</span>
                                        @if($eval->note_finale !== null)<span class="text-xs text-slate-400">/10</span>@endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-black {{ $statutColors[$eval->statut] ?? 'bg-slate-100 text-slate-500' }}">
                                            {{ $statutLabels[$eval->statut] ?? ucfirst($eval->statut) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-slate-400">
                                        {{ $eval->date_debut?->format('m/Y') }} → {{ $eval->date_fin?->format('m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('rh.evaluations.show', $eval) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                                            <i class="fas fa-eye text-[10px]"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($evaluations->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $evaluations->withQueryString()->links() }}
                    </div>
                    @endif

                    @else
                    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <i class="fas fa-clipboard-list text-2xl"></i>
                        </div>
                        <p class="font-semibold text-slate-500">Aucune évaluation trouvée</p>
                        <p class="text-xs text-slate-400">Modifiez les filtres ou attendez la soumission d'évaluations.</p>
                    </div>
                    @endif
                </div>
            @endif

            {{-- ── ONGLET : OBJECTIFS ───────────────────────────────────────── --}}
            @if($filters['tab'] === 'objectifs')
                {{-- 1. Cartes de Statistiques d'Objectifs --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-2xl border border-slate-150 bg-white p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total</p>
                        <p class="mt-2 text-3xl font-black text-slate-800">{{ $ficheStats['total'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-emerald-600/80">Acceptées</p>
                        <p class="mt-2 text-3xl font-black text-emerald-600">{{ $ficheStats['acceptee'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-amber-600/80">En attente</p>
                        <p class="mt-2 text-3xl font-black text-amber-500">{{ $ficheStats['en_attente'] ?? 0 }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm">
                        <p class="text-[10px] font-black uppercase tracking-wider text-rose-600/80">Refusées</p>
                        <p class="mt-2 text-3xl font-black text-rose-500">{{ $ficheStats['refusee'] ?? 0 }}</p>
                    </div>
                </div>

                {{-- 2. Zone de filtrage dédiée --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <form method="GET" action="{{ route('rh.dashboard') }}" class="flex flex-wrap items-end gap-4">
                        <input type="hidden" name="tab" value="objectifs">
                        
                        <div class="flex-1 min-w-[240px]">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Recherche</label>
                            <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, matricule, titre..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:border-violet-500 focus:bg-white transition">
                        </div>

                        <div class="w-48">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Statut</label>
                            <select name="statut" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-bold text-slate-700 outline-none cursor-pointer">
                                <option value="">Tous les statuts</option>
                                <option value="acceptee" {{ $filters['statut'] === 'acceptee' ? 'selected' : '' }}>Acceptées</option>
                                <option value="en_attente" {{ $filters['statut'] === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                <option value="refusee" {{ $filters['statut'] === 'refusee' ? 'selected' : '' }}>Refusées</option>
                            </select>
                        </div>

                        <div class="w-32">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5 block">Année</label>
                            <input type="number" name="annee" value="{{ $filters['annee'] }}" min="2020" max="2035" placeholder="Année"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none">
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-violet-700 transition">
                                <i class="fas fa-filter mr-2"></i>Filtrer
                            </button>
                            @if($filters['search'] || $filters['statut'] || $filters['annee'])
                                <a href="{{ route('rh.dashboard') }}?tab=objectifs" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                                    Effacer
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- 3. Tableau des fiches d'objectifs --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-slate-800 uppercase text-xs tracking-wider">Fiches d'objectifs</h3>
                            <span class="rounded-full bg-slate-200 px-2.5 py-0.5 text-xs font-bold text-slate-600">{{ $fiches->total() }}</span>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/30">
                                    <th class="px-6 py-4">Fiche</th>
                                    <th class="px-6 py-4">Assigné à</th>
                                    <th class="px-6 py-4">Période</th>
                                    <th class="px-6 py-4 text-center">Objectifs</th>
                                    <th class="px-6 py-4 text-right">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($fiches as $fiche)
                                    @php
                                        // Détermination adaptative du nom de l'agent selon la structure de ta relation assignable
                                        $nomComplet = 'Non assigné';
                                        $initiale = '—';
                                        
                                        if ($fiche->assignable) {
                                            // Cas 1 : Si assignable est directement l'Agent ou possède name
                                            if (isset($fiche->assignable->nom_prenom)) {
                                                $nomComplet = $fiche->assignable->nom_prenom;
                                            } elseif (isset($fiche->assignable->name)) {
                                                $nomComplet = $fiche->assignable->name;
                                            } elseif (isset($fiche->assignable->nom) || isset($fiche->assignable->prenom)) {
                                                $nomComplet = trim(($fiche->assignable->prenom ?? '') . ' ' . ($fiche->assignable->nom ?? ''));
                                            } 
                                            // Cas 2 : Si assignable est un User qui possède une relation 'agent'
                                            elseif (isset($fiche->assignable->agent)) {
                                                $nomComplet = $fiche->assignable->agent->nom_prenom ?? trim(($fiche->assignable->agent->prenom ?? '') . ' ' . ($fiche->assignable->agent->nom ?? ''));
                                            }
                                            
                                            $initiale = mb_substr(trim($nomComplet), 0, 1) ?: '—';
                                        }

                                        // Formater proprement la période si tes champs date_debut / date_fin existent
                                        $debut = isset($fiche->date_debut) ? \Carbon\Carbon::parse($fiche->date_debut)->format('d/m/Y') : null;
                                        $fin = isset($fiche->date_fin) ? \Carbon\Carbon::parse($fiche->date_fin)->format('d/m/Y') : null;
                                        $anneeFiche = \Carbon\Carbon::parse($fiche->date)->year;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-slate-800">{{ $fiche->titre ?? 'Contrat d\'objectifs ' . $anneeFiche }}</p>
                                            <p class="text-[11px] font-semibold text-slate-400">ID: #{{ str_pad($fiche->id, 5, '0', STR_PAD_LEFT) }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700 uppercase">
                                                    {{ $initiale }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-700">{{ $nomComplet }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-slate-500 font-semibold text-xs">
                                            @if($debut && $fin)
                                                {{ $debut }} <i class="fas fa-arrow-right text-[10px] text-slate-400 mx-1"></i> {{ $fin }}
                                            @else
                                                Année {{ $anneeFiche }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-900 text-xs font-black text-white">
                                                {{ $fiche->objectifs_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            @if(($fiche->statut === 'acceptee') || ($fiche->statut === 'accepte'))
                                                <span class="inline-flex rounded-lg bg-emerald-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-emerald-700">Acceptée</span>
                                            @elseif(($fiche->statut === 'en_attente') || ($fiche->statut === 'brouillon'))
                                                <span class="inline-flex rounded-lg bg-amber-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-700">En attente</span>
                                            @else
                                                <span class="inline-flex rounded-lg bg-rose-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-rose-700">Refusée</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-sm font-medium text-slate-400">
                                            Aucune fiche d'objectifs trouvée pour ces critères.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($fiches->hasPages())
                        <div class="border-t border-slate-100 px-6 py-4">
                            {{ $fiches->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</div>

{{-- Modal : Alerte de relance évaluation --}}
<div id="modal-relance" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden">
        <div class="border-b border-slate-100 bg-orange-50 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <i class="fas fa-bell"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-wider text-orange-500">Relance évaluation</p>
                    <h3 class="text-sm font-black text-slate-900">Alerte aux agents sans évaluation</h3>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-relance').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('rh.alertes.relancer-sans-eval') }}" class="px-6 py-5 space-y-4">
            @csrf
            <p class="text-xs text-slate-500">
                Cette alerte sera envoyée à <strong class="text-orange-600">{{ $agentsSansEval }} agent(s)</strong>
                sans évaluation validée pour l'année {{ $openAnnee?->annee }}.
            </p>
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Titre <span class="text-rose-500">*</span></label>
                <input type="text" name="titre"
                       value="Rappel : évaluation de performance en attente — {{ $openAnnee?->annee }}"
                       required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 outline-none focus:border-orange-400 focus:bg-white transition">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Message</label>
                <textarea name="message" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-700 outline-none focus:border-orange-400 focus:bg-white transition resize-none"
                          placeholder="Votre évaluation de performance pour l'exercice en cours n'a pas encore été validée. Veuillez contacter votre responsable hiérarchique.">Votre évaluation de performance pour l'exercice {{ $openAnnee?->annee }} n'a pas encore été validée. Veuillez contacter votre responsable hiérarchique afin de régulariser votre situation.</textarea>
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1.5">Priorité</label>
                <select name="priorite" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-orange-400 focus:bg-white transition">
                    <option value="moyenne">Moyenne</option>
                    <option value="haute" selected>Haute</option>
                    <option value="critique">Critique</option>
                    <option value="basse">Basse</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-relance').classList.add('hidden')"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2 text-xs font-black text-white shadow-sm hover:bg-orange-600 transition">
                    <i class="fas fa-paper-plane text-[10px]"></i> Envoyer l'alerte
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var table  = document.getElementById('rh-se-table');
    var search = document.getElementById('rh-se-search');
    var count  = document.getElementById('rh-se-count');
    if (!table || !search) return;
    var tbody = table.querySelector('tbody');
    var sortCol = -1, sortAsc = true;

    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        var rows = tbody.querySelectorAll('tr.se-row');
        var n = 0;
        rows.forEach(function (tr) {
            var show = q === '' || tr.textContent.toLowerCase().includes(q);
            tr.classList.toggle('hidden', !show);
            if (show) n++;
        });
        if (count) count.textContent = n;
    });

    table.querySelectorAll('th.se-th').forEach(function (th) {
        th.addEventListener('click', function () {
            var col = parseInt(th.dataset.col);
            if (sortCol === col) { sortAsc = !sortAsc; } else { sortCol = col; sortAsc = true; }
            table.querySelectorAll('th.se-th').forEach(function (h) {
                var icon = h.querySelector('i');
                if (!icon) return;
                icon.className = h === th
                    ? (sortAsc ? 'fas fa-sort-up ml-1 text-orange-500' : 'fas fa-sort-down ml-1 text-orange-500')
                    : 'fas fa-sort ml-1 opacity-40';
            });
            var rows = Array.from(tbody.querySelectorAll('tr.se-row'));
            rows.sort(function (a, b) {
                var va = a.cells[col] ? a.cells[col].textContent.trim().toLowerCase() : '';
                var vb = b.cells[col] ? b.cells[col].textContent.trim().toLowerCase() : '';
                return sortAsc ? va.localeCompare(vb, 'fr') : vb.localeCompare(va, 'fr');
            });
            rows.forEach(function (tr) { tbody.appendChild(tr); });
        });
    });
})();
</script>
@endpush