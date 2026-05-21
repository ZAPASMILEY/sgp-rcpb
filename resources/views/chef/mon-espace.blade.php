{{--
    ──────────────────────────────────────────────────────────────────────────
    chef/mon-espace.blade.php — Dossier personnel du chef
    ──────────────────────────────────────────────────────────────────────────

    Variables reçues du contrôleur ChefMonEspaceController :
      $ctx               — ChefEntity (type, entity, agent…)
      $user              — User connecté
      $agent             — Agent du chef connecté (peut être null)
      $tab               — Onglet actif ('evaluations'|'objectifs')
      $evaluationsRecues — Collection d'évaluations reçues par le chef
      $evaluationsStats  — ['total','soumis','valide','refuse','brouillon']
      $fichesObjectifs   — Collection de fiches d'objectifs reçues
      $fichesStats       — ['total','acceptees','en_attente','refusees']

    Note : La gestion de l'équipe (agents) est sur chef.equipe via ChefEquipeController.
    ──────────────────────────────────────────────────────────────────────────
--}}
@extends('layouts.chef')

@section('title', 'Mon Espace Chef | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">
                        Mon Espace / {{ $ctx->getRoleLabel() }}
                    </p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $ctx->getTypeLabel() }} :
                        <span class="font-semibold text-blue-700">{{ $ctx->getNom() }}</span>
                        @if ($ctx->getParentNom())
                            <span class="text-slate-400"> — {{ $ctx->getParentNom() }}</span>
                        @endif
                    </p>
                </div>
                {{-- Initiale du nom dans un badge carré --}}
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 font-black text-xl shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
        </header>

        {{-- ── Messages flash ──────────────────────────────────────────────── --}}
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

        {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
        @php
        $kpis = [
            ['label' => 'Évaluations reçues', 'value' => $evaluationsStats['total'],   'icon' => 'fas fa-star-half-stroke', 'color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-100'],
            ['label' => 'Acceptées',           'value' => $evaluationsStats['valide'],  'icon' => 'fas fa-circle-check',     'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'Objectifs reçus',     'value' => $fichesStats['total'],        'icon' => 'fas fa-bullseye',         'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
            ['label' => 'En attente',          'value' => $fichesStats['en_attente'],   'icon' => 'fas fa-clock',            'color' => 'bg-amber-500',   'light' => 'bg-amber-50 border-amber-100'],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                            <i class="{{ $kpi['icon'] }}"></i>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ── Tabs ────────────────────────────────────────────────────────── --}}
        <div class="admin-panel px-6 py-6 lg:px-8">

            {{-- Navigation entre onglets --}}
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">

                    {{-- Onglet Évaluations reçues --}}
                    <a href="{{ route('chef.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'evaluations'
                               ? 'border border-slate-200 bg-white text-blue-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i>
                        Mes évaluations reçues
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'evaluations' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>

                    {{-- Onglet Objectifs reçus --}}
                    <a href="{{ route('chef.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs'
                               ? 'border border-slate-200 bg-white text-teal-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i>
                        Mes objectifs reçus
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'objectifs' ? 'bg-teal-100 text-teal-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>

                </div>
                {{-- Lien vers Mon équipe (page séparée) --}}
                <a href="{{ route('chef.equipe') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                    <i class="fas fa-users text-xs"></i>
                    Voir Mon équipe
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            {{-- ════════════════════════════════════════════════════════════════
                 Onglet : Mes évaluations reçues
                 Évaluations créées par un supérieur hiérarchique pour ce chef
            ════════════════════════════════════════════════════════════════════ --}}
            @if ($tab === 'evaluations')
                @if ($evaluationsRecues->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-inbox text-3xl text-slate-300 mb-3"></i>
                        <p class="text-sm text-slate-500">Aucune évaluation reçue pour l'instant.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($evaluationsRecues as $eval)
                            @php
                                $statClass = match ($eval->statut) {
                                    'valide'    => 'bg-emerald-100 text-emerald-700',
                                    'soumis'    => 'bg-amber-100 text-amber-700',
                                    'refuse'    => 'bg-rose-100 text-rose-700',
                                    default     => 'bg-slate-100 text-slate-600',
                                };
                                $statLabel = match ($eval->statut) {
                                    'valide'    => 'Acceptée',
                                    'soumis'    => 'Soumise',
                                    'refuse'    => 'Refusée',
                                    'brouillon' => 'Brouillon',
                                    default     => ucfirst($eval->statut),
                                };
                            @endphp
                            <a href="{{ route('chef.evaluations.show', $eval) }}"
                               class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 transition hover:shadow-md sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        Période {{ $eval->date_debut->format('m/Y') }} – {{ $eval->date_fin->format('m/Y') }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Évalué par {{ $eval->evaluateur?->name ?? '—' }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if ($eval->note_finale !== null)
                                        <span class="text-lg font-black text-emerald-700">
                                            {{ number_format((float) $eval->note_finale, 2, ',', ' ') }}/10
                                        </span>
                                    @endif
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">
                                        {{ $statLabel }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            {{-- ════════════════════════════════════════════════════════════════
                 Onglet : Mes objectifs reçus
                 Fiches assignées au chef par un supérieur
            ════════════════════════════════════════════════════════════════════ --}}
            @elseif ($tab === 'objectifs')
                @if ($fichesObjectifs->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-bullseye text-3xl text-slate-300 mb-3"></i>
                        <p class="text-sm text-slate-500">Aucune fiche d'objectifs reçue pour l'instant.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($fichesObjectifs as $fiche)
                            @php
                                $statClass = match ($fiche->statut) {
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',
                                    default      => 'bg-slate-100 text-slate-600',
                                };
                                $statLabel = match ($fiche->statut) {
                                    'acceptee'   => 'Acceptée',
                                    'en_attente' => 'En attente',
                                    'refusee'    => 'Refusée',
                                    default      => ucfirst($fiche->statut),
                                };
                            @endphp
                            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $fiche->titre }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Échéance : {{ $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') : '—' }}
                                        · {{ $fiche->objectifs->count() }} objectif(s)
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    {{-- Barre d'avancement --}}
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-200">
                                            <div class="h-full rounded-full bg-emerald-500"
                                                 style="width: {{ $fiche->avancement_percentage ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-600">{{ $fiche->avancement_percentage ?? 0 }}%</span>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">
                                        {{ $statLabel }}
                                    </span>
                                    {{-- Bouton Voir : accepter / refuser / consulter --}}
                                    <a href="{{ route('chef.mes-fiches.show', $fiche) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                                        <i class="fas fa-eye text-[10px]"></i> Voir
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            {{-- ════════════════════════════════════════════════════════════════
                 Onglet : Mon équipe (agents)
                 Liste des agents subordonnés avec leur statut d'évaluation
            ════════════════════════════════════════════════════════════════════ --}}
            @elseif ($tab === 'agents')
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-black text-slate-900">
                        Agents de {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                    </h2>
                    <div class="flex gap-2">
                        @if($evaluationsEnabled)
                            <a href="{{ route('chef.evaluations.create') }}" class="ent-btn ent-btn-soft">
                                <i class="fas fa-star-half-stroke mr-2"></i>Évaluer un agent
                            </a>
                        @else
                            <span title="Fonctionnalité désactivée par l'administrateur"
                                  class="ent-btn ent-btn-soft cursor-not-allowed opacity-75 select-none pointer-events-none">
                                <i class="fas fa-star-half-stroke mr-2"></i>Évaluer un agent
                            </span>
                        @endif
                        @if($objectifsEnabled)
                            <a href="{{ route('chef.objectifs.create') }}" class="ent-btn ent-btn-primary">
                                <i class="fas fa-list-check mr-2"></i>Assigner des objectifs
                            </a>
                        @else
                            <span title="Fonctionnalité désactivée par l'administrateur"
                                  class="ent-btn ent-btn-primary cursor-not-allowed opacity-75 select-none pointer-events-none">
                                <i class="fas fa-list-check mr-2"></i>Assigner des objectifs
                            </span>
                        @endif
                    </div>
                </div>

                @if ($agentsOverview->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-10 text-center">
                        <i class="fas fa-users text-3xl text-slate-300 mb-3"></i>
                        <p class="text-sm text-slate-500">Aucun agent dans votre {{ $ctx->getTypeLabel() }} pour l'instant.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">Agent</th>
                                    <th class="px-4 py-3 text-left">Rôle</th>
                                    <th class="px-4 py-3 text-left">Dernière évaluation</th>
                                    <th class="px-4 py-3 text-left">Statut</th>
                                    <th class="px-4 py-3 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($agentsOverview as $row)
                                    @php
                                        /** @var \App\Models\Agent $ag */
                                        $ag       = $row['agent'];
                                        $eval     = $row['latest_eval'];
                                        $statut   = $row['eval_statut'];

                                        $evalClass = match ($statut) {
                                            'valide'    => 'bg-emerald-100 text-emerald-700',
                                            'soumis'    => 'bg-amber-100 text-amber-700',
                                            'refuse'    => 'bg-rose-100 text-rose-700',
                                            'brouillon' => 'bg-slate-100 text-slate-600',
                                            default     => null,
                                        };
                                        $evalLabel = match ($statut) {
                                            'valide'    => 'Acceptée',
                                            'soumis'    => 'Soumise',
                                            'refuse'    => 'Refusée',
                                            'brouillon' => 'Brouillon',
                                            default     => null,
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-4 py-3 font-semibold text-slate-900">
                                            {{ trim($ag->prenom . ' ' . $ag->nom) }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $ag->role ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($eval)
                                                {{ $eval->date_debut->format('m/Y') }}–{{ $eval->date_fin->format('m/Y') }}
                                                @if ($eval->note_finale !== null)
                                                    <span class="ml-1 font-bold text-emerald-700">
                                                        {{ number_format((float) $eval->note_finale, 2, ',', ' ') }}/10
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-400">Aucune</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($evalLabel)
                                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $evalClass }}">
                                                    {{ $evalLabel }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">Non évalué</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                @if($evaluationsEnabled)
                                                    <a href="{{ route('chef.evaluations.create', ['agent_id' => $ag->id]) }}"
                                                       class="ent-btn ent-btn-soft py-1 text-xs">
                                                        Évaluer
                                                    </a>
                                                @else
                                                    <span title="Fonctionnalité désactivée"
                                                          class="ent-btn ent-btn-soft py-1 text-xs cursor-not-allowed opacity-75 select-none pointer-events-none">
                                                        Évaluer
                                                    </span>
                                                @endif
                                                @if($objectifsEnabled)
                                                    <a href="{{ route('chef.objectifs.create', ['agent_id' => $ag->id]) }}"
                                                       class="ent-btn ent-btn-soft py-1 text-xs">
                                                        Objectifs
                                                    </a>
                                                @else
                                                    <span title="Fonctionnalité désactivée"
                                                          class="ent-btn ent-btn-soft py-1 text-xs cursor-not-allowed opacity-75 select-none pointer-events-none">
                                                        Objectifs
                                                    </span>
                                                @endif
                                                @if ($eval)
                                                    <a href="{{ route('chef.evaluations.show', $eval) }}"
                                                       class="ent-btn ent-btn-soft py-1 text-xs">
                                                        Voir éval.
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

        </div>{{-- fin .admin-panel --}}
    </div>{{-- fin flex flex-col gap-6 --}}
</div>{{-- fin min-h-screen --}}
@endsection
