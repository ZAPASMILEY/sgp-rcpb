@extends('layouts.chef')
@section('title', trim($agent->prenom.' '.$agent->nom).' | '.config('app.name', 'SGP-RCPB'))

@php
    $agentNom  = trim($agent->prenom.' '.$agent->nom);
    $agentRole = $agent->role ?? 'Agent';
    $initiale  = strtoupper(substr($agentNom, 0, 1));
    $agentRoute = fn ($t) => route('chef.agent.show', ['agent' => $agent->id, 'tab' => $t]);
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-xl font-black text-white ring-2 ring-white/20">
                    {{ $initiale }}
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-violet-200">
                        Mon Équipe · {{ $ctx->getTypeLabel() }} · Dossier agent
                    </p>
                    <h1 class="mt-0.5 text-2xl font-black leading-tight text-white">{{ $agentNom }}</h1>
                    <p class="mt-0.5 text-sm text-violet-100/80">{{ $agentRole }}</p>
                </div>
            </div>
            <a href="{{ route('chef.equipe') }}"
               class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-white/10 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour à l'équipe
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check shrink-0"></i> {{ session('status') }}
            </div>
        @endif

        {{-- KPI globaux --}}
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Évaluations',         'value' => $stats['evaluations'], 'icon' => 'fas fa-star-half-stroke', 'color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-100'],
                ['label' => 'Éval. validées',       'value' => $stats['evalides'],    'icon' => 'fas fa-check-double',     'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
                ['label' => 'Fiches d\'objectifs',  'value' => $stats['fiches'],      'icon' => 'fas fa-bullseye',         'color' => 'bg-violet-600',  'light' => 'bg-violet-50 border-violet-100'],
                ['label' => 'Objectifs acceptés',   'value' => $stats['facceptees'],  'icon' => 'fas fa-circle-check',     'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
            ] as $kpi)
            <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                        <i class="{{ $kpi['icon'] }}"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Onglets --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm">

            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ $agentRoute('evaluations') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                              {{ $tab === 'evaluations' ? 'border border-slate-200 bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i> Évaluations
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $tab === 'evaluations' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>
                    <a href="{{ $agentRoute('objectifs') }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                              {{ $tab === 'objectifs' ? 'border border-slate-200 bg-white text-violet-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i> Fiches d'objectifs
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $tab === 'objectifs' ? 'bg-violet-100 text-violet-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                </div>
                @if ($tab === 'evaluations' && $evaluationsEnabled)
                    <a href="{{ route('chef.evaluations.create', ['agent_id' => $agent->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm transition"
                       style="background:#2563eb" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                        <i class="fas fa-plus text-xs"></i> Nouvelle évaluation
                    </a>
                @elseif ($tab === 'objectifs' && $objectifsEnabled)
                    <a href="{{ route('chef.objectifs.create', ['agent_id' => $agent->id]) }}"
                       class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm transition"
                       style="background:#7c3aed" onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">
                        <i class="fas fa-plus text-xs"></i> Assigner objectifs
                    </a>
                @endif
            </div>

            {{-- ════ TAB : ÉVALUATIONS ════ --}}
            @if ($tab === 'evaluations')

                {{-- KPI évaluations --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label'=>'Total',     'value'=>$evaluationsStats['total'],    'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',             'iw'=>'bg-slate-100 text-slate-600'],
                        ['label'=>'Brouillons','value'=>$evaluationsStats['brouillon'],'icon'=>'fas fa-file-pen',      'tone'=>'border-slate-100 bg-slate-50/80 text-slate-900',        'iw'=>'bg-white text-slate-500'],
                        ['label'=>'Soumises',  'value'=>$evaluationsStats['soumis'],   'icon'=>'fas fa-paper-plane',   'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',         'iw'=>'bg-white text-amber-600'],
                        ['label'=>'Validées',  'value'=>$evaluationsStats['valide'],   'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900',   'iw'=>'bg-white text-emerald-600'],
                    ] as $card)
                    <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                                <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                            </div>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iw'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Filtre évaluations --}}
                <form method="GET" action="{{ $agentRoute('evaluations') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                            <option value="soumis"    @selected($filters['statut'] === 'soumis')>Soumise</option>
                            <option value="valide"    @selected($filters['statut'] === 'valide')>Validée</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ $agentRoute('evaluations') }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Table évaluations --}}
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
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $eval)
                                    @php
                                        $note      = (float) $eval->note_finale;
                                        $mention   = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                                        $mentCls   = match ($mention) {
                                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
                                            'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default     => 'border-rose-200 bg-rose-50 text-rose-700',
                                        };
                                        $statCls   = match ($eval->statut) {
                                            'valide'      => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis'      => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse'      => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'reclamation' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            'a_reviser'   => 'border-purple-200 bg-purple-50 text-purple-700',
                                            default       => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statLbl   = match ($eval->statut) {
                                            'valide'      => 'Validée', 'soumis' => 'Soumise', 'refuse' => 'Refusée',
                                            'reclamation' => 'Réclamation', 'a_reviser' => 'À réviser', 'brouillon' => 'Brouillon', default => ucfirst((string) $eval->statut),
                                        };
                                        $ident     = $eval->identification;
                                        $anneeEval = $ident?->date_evaluation?->format('Y') ?? $eval->date_debut->format('Y');
                                        $sem       = trim((string)($ident?->semestre ?? ''));
                                        if ($sem === '') { $sem = $eval->date_debut->month <= 6 ? '1' : '2'; }
                                        $noteVal   = number_format($note, 2, ',', ' ');
                                        $notePct   = max(0, min(100, ($note / 10) * 100));
                                        $noteBar   = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="align-top hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $eval->id }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ $anneeEval }} — Semestre {{ $sem }}</p>
                                            <p class="mt-1 text-xs text-slate-400">{{ $eval->date_debut->format('m/Y') }} → {{ $eval->date_fin->format('m/Y') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[130px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Score</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">{{ $noteVal }}/10</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBar }}" style="width: {{ $notePct }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentCls }}">{{ $mention }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statCls }}">{{ $statLbl }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($eval->statut !== 'brouillon')
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('chef.evaluations.show', $eval) }}"
                                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                                </a>
                                                <a href="{{ route('chef.evaluations.pdf', $eval) }}"
                                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-rose-300 hover:text-rose-600"
                                                   title="PDF" target="_blank">
                                                    <i class="fas fa-file-pdf text-[10px]"></i>
                                                </a>
                                            </div>
                                            @else
                                            <a href="{{ route('chef.evaluations.show', $eval) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                                                <i class="fas fa-pen text-[10px]"></i> Modifier
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune évaluation</p>
                                                <p class="mt-1 text-xs text-slate-500">Aucune évaluation créée pour cet agent.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($evaluations->hasPages())
                    <div class="mt-5 border-t border-slate-200 pt-4">{{ $evaluations->links() }}</div>
                @endif

            {{-- ════ TAB : OBJECTIFS ════ --}}
            @else

                {{-- KPI objectifs --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label'=>'Total',      'value'=>$fichesStats['total'],     'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',             'iw'=>'bg-slate-100 text-slate-600'],
                        ['label'=>'Acceptées',  'value'=>$fichesStats['acceptees'], 'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw'=>'bg-white text-emerald-600'],
                        ['label'=>'En attente', 'value'=>$fichesStats['en_attente'],'icon'=>'fas fa-clock',         'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',       'iw'=>'bg-white text-amber-600'],
                        ['label'=>'Refusées',   'value'=>$fichesStats['refusees'],  'icon'=>'fas fa-circle-xmark',  'tone'=>'border-rose-100 bg-rose-50/80 text-rose-900',          'iw'=>'bg-white text-rose-500'],
                    ] as $card)
                    <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                                <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                            </div>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iw'] }}">
                                <i class="{{ $card['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Filtre objectifs --}}
                <form method="GET" action="{{ $agentRoute('objectifs') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Titre ou année..."
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none">
                            <option value="">Tous</option>
                            <option value="en_attente" @selected($filters['statut'] === 'en_attente')>En attente</option>
                            <option value="acceptee"   @selected($filters['statut'] === 'acceptee')>Acceptée</option>
                            <option value="refusee"    @selected($filters['statut'] === 'refusee')>Refusée</option>
                            <option value="brouillon"  @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ $agentRoute('objectifs') }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Table objectifs --}}
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
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($fiches as $fiche)
                                    @php
                                        $fsCls = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'contesté' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            'brouillon'=> 'border-slate-200 bg-slate-100 text-slate-600',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $fsLbl = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Acceptée', 'refusee' => 'Refusée', 'contesté' => 'Contestée',
                                            'brouillon'=> 'Brouillon', default => 'En attente',
                                        };
                                        $av      = (int) ($fiche->avancement_percentage ?? 0);
                                        $avColor = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $fiche->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-700">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Année {{ $fiche->annee?->annee ?? $fiche->annee_id }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                            <p>{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Échéance : {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-700">
                                                <i class="fas fa-list text-[10px]"></i> {{ $fiche->objectifs_count }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[120px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Progress</span>
                                                    <span class="text-xs font-black text-slate-700">{{ $av }}%</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $avColor }}" style="width: {{ $av }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $fsCls }}">{{ $fsLbl }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <a href="{{ route('chef.objectifs.show', $fiche) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                                                <i class="fas fa-eye text-[10px]"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune fiche d'objectifs</p>
                                                <p class="mt-1 text-xs text-slate-500">Aucune fiche assignée à cet agent.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($fiches->hasPages())
                    <div class="mt-5 border-t border-slate-200 pt-4">{{ $fiches->links() }}</div>
                @endif

            @endif
        </div>{{-- fin panel --}}
    </div>
</div>
@endsection
