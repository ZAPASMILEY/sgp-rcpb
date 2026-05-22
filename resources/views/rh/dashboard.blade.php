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
                <div class="flex items-center gap-4 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-orange-800">
                            {{ $agentsSansEval }} agent{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation
                            — {{ $openAnnee->annee }}{{ $openSemestre ? ' · Semestre '.$openSemestre->numero : '' }}
                        </p>
                        <p class="mt-0.5 text-xs text-orange-600">
                            Ces agents n'ont pas encore d'évaluation pour le semestre en cours.
                        </p>
                    </div>
                    <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-3 text-lg font-black text-white shadow-sm">
                        {{ $agentsSansEval }}
                    </span>
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
                            <div class="text-center">
                                <p class="text-2xl font-black text-emerald-600">{{ $agentsEvalues }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Évalués</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-black {{ $agentsSansEval > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ $agentsSansEval }}</p>
                                <p class="text-[10px] font-bold uppercase text-slate-400">Restants</p>
                            </div>
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
@endsection