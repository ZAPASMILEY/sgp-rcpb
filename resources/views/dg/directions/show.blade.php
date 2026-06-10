@extends('layouts.dg')

@php
    $directeur    = $direction->directeur;
    $directeurNom = $directeur ? trim($directeur->prenom.' '.$directeur->nom) : null;
@endphp

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-900 px-6 py-10 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-48 w-48 rounded-full bg-emerald-400/10 blur-2xl"></div>

        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('dg.directions') }}" class="mb-2 inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left text-[10px]"></i> Mes Directeurs
                </a>
                <h1 class="text-2xl font-black tracking-tight text-white">{{ $direction->nom }}</h1>
                @if ($directeurNom)
                    <div class="mt-2 flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/30 text-[10px] font-black text-emerald-200">
                            {{ strtoupper(substr($directeurNom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-300">{{ $directeurNom }}</p>
                            @if($directeur->role)
                                <p class="text-xs text-slate-500">{{ $directeur->role }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="mt-1 text-sm italic text-slate-500">Directeur non affecté</p>
                @endif
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2 mt-2 sm:mt-0">
                @if($evaluationsEnabled && $ficheAcceptee && !$evaluationEnCours)
                    <a href="{{ route('dg.directions.evaluations.create', $direction) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-black text-white shadow transition hover:bg-emerald-400">
                        <i class="fas fa-pen-to-square text-xs"></i>Évaluer
                    </a>
                @else
                    <span title="{{ $evaluationEnCours ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : (!$ficheAcceptee ? 'Aucune fiche d\'objectifs acceptée pour ce directeur.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées.')) }}" class="ent-btn-disabled-dark"><i class="fas fa-pen-to-square text-xs"></i>Évaluer</span>
                @endif
                @if($objectifsEnabled && !$ficheBlocksNew)
                    <a href="{{ route('dg.directions.objectifs.create', $direction) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-bullseye text-xs"></i>Objectifs
                    </a>
                @else
                    <span title="{{ $ficheBlocksNew ? 'Une fiche d\'objectifs est déjà assignée à ce directeur.' : ($objectifsDisabledMessage ?: 'Assignation désactivée.') }}" class="ent-btn-disabled-dark"><i class="fas fa-bullseye text-xs"></i>Objectifs</span>
                @endif
            </div>
        </div>
    </div>

    @include('layouts._features_notice')

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Onglets --}}
        <div class="rounded-[20px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex gap-1 border-b border-slate-200 bg-slate-50/60 px-6">
                <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations']) }}"
                   class="border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'evaluations' ? 'border-emerald-500 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <i class="fas fa-star mr-2"></i>Évaluations ({{ $evaluations->count() }})
                </a>
                <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs']) }}"
                   class="border-b-2 px-4 py-3 text-sm font-bold transition {{ $tab === 'objectifs' ? 'border-emerald-500 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    <i class="fas fa-bullseye mr-2"></i>Objectifs ({{ $fiches->count() }})
                </a>
            </div>

        {{-- Tab Évaluations --}}
        @if ($tab === 'evaluations')
            @php
                $evalTotal     = $evaluations->count();
                $evalSoumises  = $evaluations->where('statut', 'soumis')->count();
                $evalAcceptees = $evaluations->where('statut', 'valide')->count();
                $evalRefusees  = $evaluations->where('statut', 'refuse')->count();
            @endphp
            <section class="px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-black text-slate-900">Évaluations créées</h2>
                    @if($evaluationsEnabled && $ficheAcceptee && !$evaluationEnCours)
                        <a href="{{ route('dg.directions.evaluations.create', $direction) }}" class="ent-btn ent-btn-primary">
                            <i class="fas fa-plus mr-2"></i>Nouvelle évaluation
                        </a>
                    @else
                        <span title="{{ $evaluationEnCours ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : (!$ficheAcceptee ? 'Aucune fiche d\'objectifs acceptée pour ce directeur.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées.')) }}"
                              class="ent-btn ent-btn-primary cursor-not-allowed opacity-75 select-none pointer-events-none">
                            <i class="fas fa-plus mr-2"></i>Nouvelle évaluation
                        </span>
                    @endif
                </div>

                {{-- KPI Cards --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total</p>
                        <p class="mt-1 text-2xl font-black text-slate-800">{{ $evalTotal }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-amber-500">Soumises</p>
                        <p class="mt-1 text-2xl font-black text-amber-700">{{ $evalSoumises }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-500">Acceptées</p>
                        <p class="mt-1 text-2xl font-black text-emerald-700">{{ $evalAcceptees }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-rose-400">Refusées</p>
                        <p class="mt-1 text-2xl font-black text-rose-600">{{ $evalRefusees }}</p>
                    </div>
                </div>

                @if ($evaluations->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-star text-2xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-500">Aucune évaluation créée pour cette direction.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Période</th>
                                    <th class="px-4 py-3 text-left">Évalué</th>
                                    <th class="px-4 py-3 text-left">Note</th>
                                    <th class="px-4 py-3 text-left">Statut</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($evaluations as $eval)
                                    @php
                                        $statutColors = [
                                            'brouillon'   => 'bg-slate-100 text-slate-600',
                                            'soumis'      => 'bg-amber-50 text-amber-700',
                                            'valide'      => 'bg-emerald-50 text-emerald-700',
                                            'refuse'      => 'bg-rose-50 text-rose-700',
                                            'reclamation' => 'bg-orange-50 text-orange-700',
                                        ];
                                        $statutLabels = [
                                            'brouillon'   => 'Brouillon',
                                            'soumis'      => 'Soumise',
                                            'valide'      => 'Acceptée',
                                            'refuse'      => 'Refusée',
                                            'reclamation' => 'Réclamation',
                                        ];
                                    @endphp
                                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium">
                                            {{ $eval->date_debut->format('m/Y') }} – {{ $eval->date_fin->format('m/Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $eval->identification?->nom_prenom ?? $directeurNom ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-black text-emerald-700">
                                            {{ number_format((float) $eval->note_finale, 2, ',', ' ') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $statutColors[$eval->statut] ?? 'bg-slate-100 text-slate-500' }}">
                                                {{ $statutLabels[$eval->statut] ?? ucfirst($eval->statut) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('dg.directions.evaluations.show', $eval) }}"
                                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                                    Voir
                                                </a>
                                                @if ($eval->statut === 'brouillon')
                                                    <form method="POST" action="{{ route('dg.directions.evaluations.submit', $eval) }}"
                                                          onsubmit="return confirm('Soumettre cette évaluation au directeur ?')">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-200">
                                                            Soumettre
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('dg.directions.evaluations.destroy', $eval) }}"
                                                          onsubmit="return confirm('Supprimer cette évaluation ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif

        {{-- Tab Objectifs --}}
        @if ($tab === 'objectifs')
            @php
                $fichesTotal     = $fiches->count();
                $fichesAcceptees = $fiches->where('statut', 'acceptee')->count();
                $fichesEnAttente = $fiches->where('statut', 'en_attente')->count();
                $fichesRefusees  = $fiches->whereIn('statut', ['refusee', 'contesté'])->count();
            @endphp
            <section class="px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-black text-slate-900">Fiches d'objectifs assignées</h2>
                    @if($objectifsEnabled && !$ficheBlocksNew)
                        <a href="{{ route('dg.directions.objectifs.create', $direction) }}" class="ent-btn ent-btn-primary">
                            <i class="fas fa-plus mr-2"></i>Assigner des objectifs
                        </a>
                    @else
                        <span title="{{ $ficheBlocksNew ? 'Une fiche d\'objectifs est déjà assignée à ce directeur.' : ($objectifsDisabledMessage ?: 'Assignation désactivée.') }}"
                              class="ent-btn ent-btn-primary cursor-not-allowed opacity-75 select-none pointer-events-none">
                            <i class="fas fa-plus mr-2"></i>Assigner des objectifs
                        </span>
                    @endif
                </div>

                {{-- KPI Cards --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Total</p>
                        <p class="mt-1 text-2xl font-black text-slate-800">{{ $fichesTotal }}</p>
                    </div>
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-amber-500">En attente</p>
                        <p class="mt-1 text-2xl font-black text-amber-700">{{ $fichesEnAttente }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-500">Acceptées</p>
                        <p class="mt-1 text-2xl font-black text-emerald-700">{{ $fichesAcceptees }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-center">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-rose-400">Refusées</p>
                        <p class="mt-1 text-2xl font-black text-rose-600">{{ $fichesRefusees }}</p>
                    </div>
                </div>

                @if ($fiches->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-500">Aucune fiche d'objectifs assignée à cette direction.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Titre</th>
                                    <th class="px-4 py-3 text-left">Objectifs</th>
                                    <th class="px-4 py-3 text-left">Avancement</th>
                                    <th class="px-4 py-3 text-left">Échéance</th>
                                    <th class="px-4 py-3 text-left">Statut</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fiches as $fiche)
                                    @php
                                        $statutColors = [
                                            'en_attente' => 'bg-amber-50 text-amber-700',
                                            'acceptee'   => 'bg-emerald-50 text-emerald-700',
                                            'refusee'    => 'bg-rose-50 text-rose-700',
                                            'brouillon'  => 'bg-slate-100 text-slate-600',
                                            'contesté'   => 'bg-orange-50 text-orange-700',
                                        ];
                                        $statutLabels = [
                                            'en_attente' => 'En attente',
                                            'acceptee'   => 'Acceptée',
                                            'refusee'    => 'Refusée',
                                            'brouillon'  => 'Brouillon',
                                            'contesté'   => 'Contestée',
                                        ];
                                        $av      = (int) ($fiche->avancement_percentage ?? 0);
                                        $avColor = $av >= 75 ? 'bg-emerald-500' : ($av >= 40 ? 'bg-sky-500' : ($av > 0 ? 'bg-amber-400' : 'bg-slate-300'));
                                    @endphp
                                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $fiche->titre }}</td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $fiche->objectifs_count ?? $fiche->objectifs->count() }} objectif(s)
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $avColor }}" style="width: {{ $av }}%"></div>
                                                </div>
                                                <span class="text-[11px] font-black text-slate-600">{{ $av }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $statutColors[$fiche->statut ?? 'en_attente'] ?? 'bg-slate-100 text-slate-500' }}">
                                                {{ $statutLabels[$fiche->statut ?? 'en_attente'] ?? ucfirst($fiche->statut ?? 'En attente') }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('dg.directions.objectifs.show', $fiche) }}"
                                                   class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                                    Voir
                                                </a>
                                                <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                                                      onsubmit="return confirm('Supprimer cette fiche ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                                        <i class="fas fa-trash text-[10px]"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
        </div>{{-- /outer card --}}

    </div>
    </div>
</div>
@endsection
