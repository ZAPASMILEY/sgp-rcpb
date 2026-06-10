@extends('layouts.directeur')

@section('title', $caisse->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-blue-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-300">Espace Directeur · Subordonnés</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $caisse->nom }}</h1>
                @if ($directeurUser)
                    <p class="mt-0.5 text-sm text-indigo-100/80">Directeur de caisse : {{ $directeurUser->name }}</p>
                @endif
                @if ($caisse->ville)
                    <p class="text-xs text-indigo-200/70">{{ $caisse->ville->nom ?? '' }}</p>
                @endif
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('directeur.subordonnes') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>
    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-5">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        {{-- Tabs --}}
        <div class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    @foreach ([
                        ['key' => 'evaluations', 'icon' => 'fas fa-star-half-stroke', 'label' => 'Évaluations'],
                        ['key' => 'objectifs',   'icon' => 'fas fa-bullseye',          'label' => 'Objectifs'],
                    ] as $t)
                        <a href="{{ route('directeur.subordonnes.caisse', ['caisse' => $caisse->id, 'tab' => $t['key']]) }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                               {{ $tab === $t['key'] ? 'border border-slate-200 bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="{{ $t['icon'] }} text-xs"></i> {{ $t['label'] }}
                        </a>
                    @endforeach
                </div>
                @if ($tab === 'evaluations')
                    @if ($evaluationsEnabled && $ficheAcceptee && !$evaluationEnCours)
                        <a href="{{ route('directeur.evaluations.create', ['caisse_id' => $caisse->id]) }}" class="ent-btn ent-btn-primary text-xs">
                            <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                        </a>
                    @else
                        <span title="{{ $evaluationEnCours ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : (!$ficheAcceptee ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées.')) }}"
                              class="ent-btn-disabled-light text-xs">
                            <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                        </span>
                    @endif
                @elseif ($tab === 'objectifs')
                    @if ($objectifsEnabled && !$ficheBlocksNew)
                        <a href="{{ route('directeur.subordonnes.caisse.objectifs.create', $caisse) }}" class="ent-btn ent-btn-primary text-xs">
                            <i class="fas fa-plus mr-1"></i> Assigner des objectifs
                        </a>
                    @else
                        <span title="{{ $ficheBlocksNew ? 'Une fiche d\'objectifs est déjà assignée à ce directeur.' : ($objectifsDisabledMessage ?: 'Objectifs désactivés.') }}"
                              class="ent-btn-disabled-light text-xs">
                            <i class="fas fa-plus mr-1"></i> Assigner des objectifs
                        </span>
                    @endif
                @endif
            </div>

            {{-- ── Évaluations ── --}}
            @if ($tab === 'evaluations')
                {{-- KPI cards --}}
                @php
                $evalCards = [
                    ['label'=>'Total',     'value'=>$evaluationsStats['total'],    'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',            'iw'=>'bg-slate-100 text-slate-600'],
                    ['label'=>'Brouillons','value'=>$evaluationsStats['brouillon'],'icon'=>'fas fa-file-pen',      'tone'=>'border-slate-100 bg-slate-50/80 text-slate-900',       'iw'=>'bg-white text-slate-500'],
                    ['label'=>'Soumises',  'value'=>$evaluationsStats['soumis'],   'icon'=>'fas fa-paper-plane',   'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',       'iw'=>'bg-white text-amber-600'],
                    ['label'=>'Validées',  'value'=>$evaluationsStats['valide'],   'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw'=>'bg-white text-emerald-600'],
                ];
                @endphp
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($evalCards as $card)
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

                {{-- Filtre --}}
                <form method="GET" action="{{ route('directeur.subordonnes.caisse', $caisse) }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" @selected($filters['statut']==='brouillon')>Brouillon</option>
                            <option value="soumis"    @selected($filters['statut']==='soumis')>Soumise</option>
                            <option value="valide"    @selected($filters['statut']==='valide')>Validée</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.subordonnes.caisse', ['caisse'=>$caisse->id,'tab'=>'evaluations']) }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Table --}}
                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Cible</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Periode</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Note finale</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $eval)
                                    @php
                                        $note = (float) $eval->note_finale;
                                        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                                        $mentionClass = match($mention) {
                                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
                                            'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default     => 'border-rose-200 bg-rose-50 text-rose-700',
                                        };
                                        $statusClass = match($eval->statut) {
                                            'valide'      => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis'      => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse'      => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'reclamation' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            'a_reviser'   => 'border-purple-200 bg-purple-50 text-purple-700',
                                            default       => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match($eval->statut) {
                                            'valide' => 'Validée', 'soumis' => 'Soumise', 'refuse' => 'Refusée', 'reclamation' => 'Réclamation', 'a_reviser' => 'À réviser', 'brouillon' => 'Brouillon', default => ucfirst((string) $eval->statut),
                                        };
                                        $ident = $eval->identification;
                                        $cibleLabel = trim((string)($ident?->nom_prenom ?? '')) ?: ($directeurUser?->name ?? $caisse->nom);
                                        $anneeEval = $ident?->date_evaluation?->format('Y') ?? $eval->date_debut->format('Y');
                                        $semEval = trim((string)($ident?->semestre ?? ''));
                                        if ($semEval === '') { $semEval = $eval->date_debut->month <= 6 ? '1' : '2'; }
                                        $noteValue   = number_format($note, 2, ',', ' ');
                                        $notePercent = max(0, min(100, ($note / 10) * 100));
                                        $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="align-top hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $eval->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-700">{{ $cibleLabel }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Evalué</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ $anneeEval }} - Semestre {{ $semEval }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Evaluateur : {{ $eval->evaluateur?->name ?? auth()->user()->name }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[130px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Score</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">{{ $noteValue }}/10</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">{{ $mention }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex items-center gap-1">
                                                <a href="{{ route('directeur.evaluations.show', $eval) }}"
                                                   class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                                                   title="{{ $eval->statut === 'brouillon' ? 'Modifier' : 'Voir' }}"><i class="fas fa-{{ $eval->statut === 'brouillon' ? 'pen' : 'eye' }}"></i></a>
                                                @if ($eval->statut !== 'brouillon')
                                                    <a href="{{ route('directeur.evaluations.pdf', $eval) }}"
                                                       class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-rose-100 hover:text-rose-600"
                                                       title="PDF" target="_blank"><i class="fas fa-file-pdf text-xs"></i></a>
                                                @endif
                                                @if ($eval->statut === 'brouillon')
                                                    <form method="POST" action="{{ route('directeur.evaluations.submit', $eval) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600" title="Soumettre">
                                                            <i class="fas fa-paper-plane text-xs"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if (in_array($eval->statut, ['brouillon', 'a_reviser']))
                                                    <form method="POST" action="{{ route('directeur.evaluations.destroy', $eval) }}"
                                                          onsubmit="return confirm('Supprimer définitivement cette évaluation ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-400 shadow-sm transition hover:bg-rose-50 hover:text-rose-600" title="Supprimer">
                                                            <i class="fas fa-trash text-xs"></i>
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
                                                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune évaluation</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            {{-- ── Objectifs ── --}}
            @else
                {{-- KPI cards --}}
                @php
                $ficheCards = [
                    ['label'=>'Total',      'value'=>$fichesStats['total'],      'icon'=>'fas fa-folder-open',    'tone'=>'border-slate-100 bg-white text-slate-900',            'iw'=>'bg-slate-100 text-slate-600'],
                    ['label'=>'Acceptées',  'value'=>$fichesStats['acceptees'],  'icon'=>'fas fa-circle-check',   'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900', 'iw'=>'bg-white text-emerald-600'],
                    ['label'=>'En attente', 'value'=>$fichesStats['en_attente'], 'icon'=>'fas fa-hourglass-half', 'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',       'iw'=>'bg-white text-amber-600'],
                    ['label'=>'Refusées',   'value'=>$fichesStats['refusees'],   'icon'=>'fas fa-ban',            'tone'=>'border-rose-100 bg-rose-50/80 text-rose-900',          'iw'=>'bg-white text-rose-600'],
                ];
                @endphp
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($ficheCards as $card)
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

                {{-- Filtre --}}
                <form method="GET" action="{{ route('directeur.subordonnes.caisse', $caisse) }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">
                    <div class="flex-1 min-w-48">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <i class="fas fa-search text-sm"></i>
                            </span>
                            <input name="search" type="text" value="{{ $filters['search'] ?? '' }}" placeholder="Titre, année..."
                                   class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400">
                        </div>
                    </div>
                    <select name="statut" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" @selected(($filters['statut'] ?? '') === 'en_attente')>En attente</option>
                        <option value="acceptee"   @selected(($filters['statut'] ?? '') === 'acceptee')>Acceptée</option>
                        <option value="refusee"    @selected(($filters['statut'] ?? '') === 'refusee')>Refusée</option>
                    </select>
                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.subordonnes.caisse', ['caisse' => $caisse->id, 'tab' => 'objectifs']) }}"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

                {{-- Table --}}
                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fiche</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Année</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($fiches as $fiche)
                                    @php
                                        $ficheStatut = $fiche->statut ?? 'en_attente';
                                        $ficheStatusClass = match ($ficheStatut) {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'contesté' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            'brouillon'=> 'border-slate-200 bg-slate-100 text-slate-500',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $ficheStatusLabel = match ($ficheStatut) {
                                            'acceptee' => 'Acceptée', 'refusee' => 'Refusée',
                                            'contesté' => 'Contestée', 'brouillon' => 'Brouillon',
                                            default    => 'En attente',
                                        };
                                        $progress = (int) ($fiche->avancement_percentage ?? 0);
                                        $progressColor = $progress >= 75 ? 'bg-emerald-500' : ($progress >= 40 ? 'bg-sky-500' : ($progress > 0 ? 'bg-amber-400' : 'bg-slate-200'));
                                        $anneeVal = $fiche->annee?->annee ?? $fiche->annee_id ?? '—';
                                    @endphp
                                    <tr class="align-top hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-slate-900">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">Année {{ $anneeVal }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600">
                                                {{ $anneeVal }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-black text-cyan-700">
                                                {{ $fiche->objectifs_count ?? 0 }} objectif(s)
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[130px]">
                                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Progression</span>
                                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">{{ $progress }}%</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $progressColor }}" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $ficheStatusClass }}">{{ $ficheStatusLabel }}</span>
                                            @if ($ficheStatut === 'refusee' && $fiche->motif_refus)
                                                <p class="mt-1 max-w-[200px] truncate text-[10px] italic text-rose-600" title="{{ $fiche->motif_refus }}">
                                                    « {{ $fiche->motif_refus }} »
                                                </p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <a href="{{ route('directeur.subordonnes.caisse.objectifs.show', $fiche) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                                               title="Voir"><i class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-inbox text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune fiche d'objectifs</p>
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
</div>
@endsection
