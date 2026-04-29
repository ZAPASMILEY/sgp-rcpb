@extends('layouts.dg')

@php
    $directeur    = $direction->directeur;
    $directeurNom = $directeur ? trim($directeur->prenom.' '.$directeur->nom) : null;
@endphp

@section('title', $direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full flex-col gap-6">

        {{-- En-tête --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        <a href="{{ route('dg.directions') }}" class="hover:text-emerald-600">Mes Directeurs</a>
                        <span class="mx-1 text-slate-300">/</span>
                        {{ $direction->nom }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $direction->nom }}</h1>
                    @if ($directeurNom)
                        <p class="mt-1 text-sm text-slate-500">
                            <i class="fas fa-user mr-1 text-slate-400"></i>{{ $directeurNom }}
                            @if ($directeur->fonction)
                                <span class="ml-1 text-slate-400">— {{ $directeur->fonction }}</span>
                            @endif
                        </p>
                    @else
                        <p class="mt-1 text-sm text-slate-400 italic">Directeur non affecté</p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('dg.directions.evaluations.create', $direction) }}" class="ent-btn ent-btn-primary">
                        <i class="fas fa-pen-to-square mr-2"></i>Nouvelle évaluation
                    </a>
                    <a href="{{ route('dg.directions.objectifs.create', $direction) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-bullseye mr-2"></i>Assigner des objectifs
                    </a>
                    <a href="{{ route('dg.directions') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </header>

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
        <div class="flex gap-1 border-b border-slate-200 bg-white px-6">
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
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-black text-slate-900">Évaluations créées</h2>
                    <a href="{{ route('dg.directions.evaluations.create', $direction) }}" class="ent-btn ent-btn-primary">
                        <i class="fas fa-plus mr-2"></i>Nouvelle évaluation
                    </a>
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
                                            'brouillon' => 'bg-slate-100 text-slate-600',
                                            'soumis'    => 'bg-amber-50 text-amber-700',
                                            'valide'    => 'bg-emerald-50 text-emerald-700',
                                            'refuse'    => 'bg-rose-50 text-rose-700',
                                        ];
                                        $statutLabels = [
                                            'brouillon' => 'Brouillon',
                                            'soumis'    => 'Soumise',
                                            'valide'    => 'Acceptée',
                                            'refuse'    => 'Refusée',
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
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-black text-slate-900">Fiches d'objectifs assignées</h2>
                    <a href="{{ route('dg.directions.objectifs.create', $direction) }}" class="ent-btn ent-btn-primary">
                        <i class="fas fa-plus mr-2"></i>Assigner des objectifs
                    </a>
                </div>

                @if ($fiches->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                        <p class="mt-3 text-sm text-slate-500">Aucune fiche d'objectifs assignée à cette direction.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($fiches as $fiche)
                            @php
                                $statutColors = [
                                    'en_attente' => 'bg-amber-50 text-amber-700',
                                    'acceptee'   => 'bg-emerald-50 text-emerald-700',
                                    'refusee'    => 'bg-rose-50 text-rose-700',
                                ];
                                $statutLabels = [
                                    'en_attente' => 'En attente',
                                    'acceptee'   => 'Acceptée',
                                    'refusee'    => 'Refusée',
                                ];
                                $av = (int) ($fiche->avancement_percentage ?? 0);
                                $avColor = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                            @endphp
                            <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-bullseye text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-slate-900">{{ $fiche->titre }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ $fiche->objectifs_count }} objectif(s) —
                                        Échéance {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}
                                    </p>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <div class="h-1.5 flex-1 rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $avColor }}" style="width: {{ $av }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-500">{{ $av }}%</span>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statutColors[$fiche->statut ?? 'en_attente'] ?? 'bg-slate-100 text-slate-500' }}">
                                    {{ $statutLabels[$fiche->statut ?? 'en_attente'] ?? ucfirst($fiche->statut ?? 'En attente') }}
                                </span>
                                <div class="flex shrink-0 items-center gap-2">
                                    <a href="{{ route('dg.directions.objectifs.show', $fiche) }}"
                                       class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                        Voir
                                    </a>
                                    <form method="POST" action="{{ route('dg.directions.objectifs.avancement', $fiche) }}"
                                          class="flex items-center gap-1">
                                        @csrf @method('PATCH')
                                        <select name="avancement_percentage"
                                                class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                                                onchange="this.form.submit()">
                                            @for ($p = 0; $p <= 100; $p += 5)
                                                <option value="{{ $p }}" @selected((int) $fiche->avancement_percentage === $p)>{{ $p }}%</option>
                                            @endfor
                                        </select>
                                    </form>
                                    <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                                          onsubmit="return confirm('Supprimer cette fiche ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-100">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

    </div>
</div>
@endsection
