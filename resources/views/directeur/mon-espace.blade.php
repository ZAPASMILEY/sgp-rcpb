@extends('layouts.directeur')

@section('title', 'Mon Espace | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Mon Espace / {{ $ctx->getRoleLabel() }}</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $ctx->getTypeLabel() }} :
                        <span class="font-semibold text-blue-700">{{ $ctx->getNom() }}</span>
                    </p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 font-black text-xl shadow-sm">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
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

        {{-- KPI rapides --}}
        @php
        $kpis = [
            ['label' => 'Évaluations reçues', 'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star-half-stroke', 'color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-100'],
            ['label' => 'Acceptées',           'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-circle-check',     'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'Objectifs reçus',     'value' => $fichesStats['total'],       'icon' => 'fas fa-bullseye',          'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
            ['label' => 'Objectifs acceptés',  'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check',     'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
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

        {{-- Tabs --}}
        <div class="admin-panel px-6 py-6 lg:px-8">

            {{-- Tab nav --}}
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ route('directeur.mon-espace') }}?tab=evaluations"
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
                    <a href="{{ route('directeur.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs'
                               ? 'border border-slate-200 bg-white text-blue-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i>
                        Mes objectifs reçus
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'objectifs' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- ══ TAB : ÉVALUATIONS REÇUES ══ --}}
            @if ($tab === 'evaluations')

                {{-- Stats évaluations --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                    $evalCards = [
                        ['label' => 'Total',    'value' => $evaluationsStats['total'],    'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iconWrap' => 'bg-slate-100 text-slate-600'],
                        ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'],   'icon' => 'fas fa-paper-plane',    'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iconWrap' => 'bg-white text-amber-600'],
                        ['label' => 'Acceptées','value' => $evaluationsStats['valide'],   'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap' => 'bg-white text-emerald-600'],
                        ['label' => 'Refusées', 'value' => $evaluationsStats['refuse'],   'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iconWrap' => 'bg-white text-rose-500'],
                    ];
                    @endphp
                    @foreach ($evalCards as $card)
                        <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                                    <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iconWrap'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Filtre --}}
                <form method="GET" action="{{ route('directeur.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-100">
                            <option value="">Tous les statuts</option>
                            <option value="soumis"  @selected(request('statut') === 'soumis')>Soumise</option>
                            <option value="valide"  @selected(request('statut') === 'valide')>Acceptée</option>
                            <option value="refuse"  @selected(request('statut') === 'refuse')>Refusée</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Tableau évaluations --}}
                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Note finale</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Évaluateur</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $filtreStatut = request('statut');
                                    $evalsFiltrees = $filtreStatut
                                        ? $evaluationsRecues->where('statut', $filtreStatut)
                                        : $evaluationsRecues;
                                @endphp
                                @forelse ($evalsFiltrees as $evaluation)
                                    @php
                                        $note = (float) $evaluation->note_finale;
                                        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                                        $mentionClass = match ($mention) {
                                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
                                            'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default     => 'border-rose-200 bg-rose-50 text-rose-700',
                                        };
                                        $statusClass = match ($evaluation->statut) {
                                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse' => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default  => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match ($evaluation->statut) {
                                            'valide' => 'Acceptée', 'soumis' => 'Soumise',
                                            'refuse' => 'Refusée', default => 'Brouillon',
                                        };
                                        $identification = $evaluation->identification;
                                        $anneeEval    = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                                        $semestreEval = trim((string)($identification?->semestre ?? ''));
                                        if ($semestreEval === '') {
                                            $semestreEval = $evaluation->date_debut->month <= 6 ? '1' : '2';
                                        }
                                        $noteValue    = number_format($note, 2, ',', ' ');
                                        $notePercent  = max(0, min(100, ($note / 10) * 100));
                                        $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="align-top hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $evaluation->id }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ $anneeEval }} — Sem. {{ $semestreEval }}</p>
                                            <p class="mt-1 text-xs text-slate-400">{{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[130px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Score</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">
                                                        {{ $noteValue }}/10
                                                    </span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">
                                                {{ $mention }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600 text-sm">
                                            {{ $evaluation->evaluateur?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('directeur.evaluations.show', $evaluation) }}"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (in_array($evaluation->statut, ['soumis']))
                                                    <form method="POST" action="{{ route('directeur.evaluations.statut', $evaluation) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="statut" value="valide">
                                                        <button type="submit"
                                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-emerald-100 hover:text-emerald-600"
                                                                title="Accepter" onclick="return confirm('Accepter cette évaluation ?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('directeur.evaluations.statut', $evaluation) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="statut" value="refuse">
                                                        <button type="submit"
                                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-rose-100 hover:text-rose-500"
                                                                title="Refuser" onclick="return confirm('Refuser cette évaluation ?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-star-half-stroke text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune évaluation reçue</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore d'évaluation reçue du DG.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ══ TAB : OBJECTIFS REÇUS ══ --}}
            @else

                {{-- Stats objectifs --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                    $objCards = [
                        ['label' => 'Total',      'value' => $fichesStats['total'],       'icon' => 'fas fa-clipboard-list', 'tone' => 'border-slate-100 bg-white text-slate-900',            'iconWrap' => 'bg-slate-100 text-slate-600'],
                        ['label' => 'En attente', 'value' => $fichesStats['en_attente'],  'icon' => 'fas fa-clock',          'tone' => 'border-amber-100 bg-amber-50/80 text-amber-900',      'iconWrap' => 'bg-white text-amber-600'],
                        ['label' => 'Acceptés',   'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check',   'tone' => 'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap' => 'bg-white text-emerald-600'],
                        ['label' => 'Refusés',    'value' => $fichesStats['refusees'],    'icon' => 'fas fa-circle-xmark',   'tone' => 'border-rose-100 bg-rose-50/80 text-rose-900',         'iconWrap' => 'bg-white text-rose-500'],
                    ];
                    @endphp
                    @foreach ($objCards as $card)
                        <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                                    <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                                </div>
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iconWrap'] }}">
                                    <i class="{{ $card['icon'] }}"></i>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Filtre --}}
                <form method="GET" action="{{ route('directeur.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut_obj"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-4 focus:ring-blue-100">
                            <option value="">Tous</option>
                            <option value="en_attente" @selected(request('statut_obj') === 'en_attente')>En attente</option>
                            <option value="acceptee"   @selected(request('statut_obj') === 'acceptee')>Accepté</option>
                            <option value="refusee"    @selected(request('statut_obj') === 'refusee')>Refusé</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Tableau objectifs --}}
                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fiche</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $filtreObj = request('statut_obj');
                                    $fichesFiltrees = $filtreObj
                                        ? $fichesObjectifs->where('statut', $filtreObj)
                                        : $fichesObjectifs;
                                @endphp
                                @forelse ($fichesFiltrees as $fiche)
                                    @php
                                        $statutClass = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $statutLabel = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Accepté', 'refusee' => 'Refusé', default => 'En attente',
                                        };
                                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $fiche->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-700">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Année {{ $fiche->annee?->annee ?? $fiche->annee_id }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                            <p>{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs text-slate-400">
                                                Échéance : {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-700">
                                                <i class="fas fa-list text-[10px]"></i>
                                                {{ $fiche->objectifs->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[120px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Progress</span>
                                                    <span class="text-xs font-black text-slate-700">{{ $avancement }}%</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $avancementColor }}" style="width: {{ $avancement }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutClass }}">
                                                {{ $statutLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('directeur.objectifs.show', $fiche) }}"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (in_array($fiche->statut, ['en_attente', null]))
                                                    <form method="POST" action="{{ route('directeur.objectifs.statut', $fiche) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="statut" value="acceptee">
                                                        <button type="submit"
                                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-emerald-100 hover:text-emerald-600"
                                                                title="Accepter" onclick="return confirm('Accepter cette fiche ?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('directeur.objectifs.statut', $fiche) }}">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="statut" value="refusee">
                                                        <button type="submit"
                                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-rose-100 hover:text-rose-500"
                                                                title="Refuser" onclick="return confirm('Refuser cette fiche ?')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucun objectif reçu</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore de fiche d'objectifs assignée.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @endif
        </div>

    </div>
</div>
@endsection
