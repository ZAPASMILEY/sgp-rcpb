{{--
    ──────────────────────────────────────────────────────────────────────────
    chef/subordonnes/objectifs/show.blade.php
    Détail d'une fiche d'objectifs assignée par le chef
    ──────────────────────────────────────────────────────────────────────────

    Variables reçues de ChefObjectifController::show() :
      $ctx         — ChefEntity
      $fiche       — FicheObjectif (avec objectifs et assignable chargés)
      $statusClass — Classes Tailwind du badge de statut
      $statusLabel — Libellé du statut
    ──────────────────────────────────────────────────────────────────────────
--}}
@extends('layouts.chef')

@section('title', 'Fiche d\'objectifs | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Espace Chef / {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                        Fiche d'objectifs
                    </h1>
                    @php
                        /** @var \App\Models\Agent|null $agent */
                        $agent = $fiche->assignable;
                        $agentNom = $agent
                            ? trim($agent->prenom . ' ' . $agent->nom)
                            : '—';
                    @endphp
                    <p class="mt-2 text-sm text-slate-600">
                        Agent : <span class="font-semibold">{{ $agentNom }}</span>
                        @if ($agent?->fonction)
                            — {{ $agent->fonction }}
                        @endif
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('chef.mon-espace', ['tab' => 'agents']) }}" class="ent-btn ent-btn-soft">
                        Retour
                    </a>
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

        {{-- ── Résumé de la fiche ────────────────────────────────────────────── --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">

                {{-- Titre --}}
                <div class="lg:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Titre de la fiche</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $fiche->titre }}</p>
                </div>

                {{-- Dates --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'émission</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'échéance</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800">
                        {{ $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') : '—' }}
                    </p>
                </div>

                {{-- Statut --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <span class="mt-2 inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Avancement --}}
                <div class="lg:col-span-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                        Avancement global
                    </p>
                    <div class="mt-3 flex items-center gap-4">
                        <div class="flex-1 h-3 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-emerald-500 transition-all"
                                 style="width: {{ $fiche->avancement_percentage ?? 0 }}%"></div>
                        </div>
                        <span class="w-12 text-right text-sm font-black text-slate-700">
                            {{ $fiche->avancement_percentage ?? 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Liste des objectifs ───────────────────────────────────────────── --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">
                Objectifs
                <span class="ml-2 rounded-full bg-slate-100 px-2.5 py-0.5 text-sm font-bold text-slate-600">
                    {{ $fiche->objectifs->count() }}
                </span>
            </h2>

            @if ($fiche->objectifs->isEmpty())
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-6 py-8 text-center">
                    <p class="text-sm text-slate-400">Aucun objectif dans cette fiche.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left w-8">#</th>
                                <th class="px-4 py-3 text-left">Description de l'objectif</th>
                                @if ($fiche->objectifs->whereNotNull('note_obtenue')->isNotEmpty())
                                    <th class="px-4 py-3 text-left">Note obtenue</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($fiche->objectifs as $i => $objectif)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-4 py-3 text-slate-400 font-bold">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">{{ $objectif->description }}</td>
                                    @if ($fiche->objectifs->whereNotNull('note_obtenue')->isNotEmpty())
                                        <td class="px-4 py-3">
                                            @if ($objectif->note_obtenue !== null)
                                                <span class="font-bold text-emerald-700">
                                                    {{ number_format((float) $objectif->note_obtenue, 2, ',', ' ') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Actions disponibles</p>
                    @if ($fiche->statut === 'acceptee')
                        <p class="text-sm text-slate-600">
                            La fiche a été acceptée par l'agent. Elle ne peut plus être supprimée.
                        </p>
                    @elseif ($fiche->statut === 'en_attente')
                        <p class="text-sm text-slate-600">
                            En attente d'acceptation par l'agent.
                        </p>
                    @elseif ($fiche->statut === 'refusee')
                        <p class="text-sm text-slate-600 text-rose-700">
                            L'agent a refusé cette fiche. Vous pouvez la supprimer et en créer une nouvelle.
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Nouvelle évaluation pour cet agent --}}
                    @if ($fiche->assignable && $evaluationsEnabled)
                        <a href="{{ route('chef.evaluations.create', ['agent_id' => $fiche->assignable->id]) }}"
                           class="ent-btn ent-btn-soft">
                            <i class="fas fa-star-half-stroke mr-2"></i>Évaluer cet agent
                        </a>
                    @endif

                    {{-- Suppression (sauf si acceptée) --}}
                    @if ($fiche->statut !== 'acceptee')
                        <form method="POST"
                              action="{{ route('chef.objectifs.destroy', $fiche) }}"
                              onsubmit="return confirm('Supprimer définitivement cette fiche d\'objectifs ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ent-btn bg-rose-600 text-white hover:bg-rose-700">
                                <i class="fas fa-trash mr-2"></i>Supprimer la fiche
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('chef.mon-espace', ['tab' => 'agents']) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-2"></i>Retour
                    </a>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
