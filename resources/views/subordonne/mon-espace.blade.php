@extends('layouts.subordonne')

@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
$roleLabel = match($user->role) {
    'DGA'           => 'Directeur Général Adjoint',
    'Assistante_Dg' => 'Assistante du DG',
    'Conseillers_Dg'=> 'Conseiller du DG',
    default         => $user->role,
};
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ══════════════════════════ HERO ══════════════════════════════════════ --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">{{ $roleLabel }} · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-white/60">Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        {{-- Mini KPIs dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Évaluations', 'value' => $evaluationsStats['total'],  'icon' => 'fas fa-star'],
                ['label' => 'Validées',    'value' => $evaluationsStats['valide'], 'icon' => 'fas fa-check'],
                ['label' => 'Objectifs',   'value' => $fichesStats['total'],       'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Acceptés',    'value' => $fichesStats['acceptees'],   'icon' => 'fas fa-circle-check'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="{{ $m['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>{{-- mini KPIs --}}
    </div>{{-- hero --}}

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Section secrétaire (Assistante_Dg uniquement) --}}
        @if ($user->role === 'Assistante_Dg')
            <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-5 shadow-sm lg:px-8">
                <p class="mb-4 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Mes subordonnés</p>
                <a href="{{ route('assistante.secretaire') }}"
                   class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div>
                            <p class="font-black text-slate-900">Ma secrétaire</p>
                            <p class="text-xs text-slate-500">Gérer les évaluations et objectifs</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                </a>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm">

            {{-- Tab nav --}}
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ route('subordonne.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'evaluations'
                               ? 'border border-slate-200 bg-white text-cyan-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i>
                        Mes evaluations
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'evaluations' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>
                    <a href="{{ route('subordonne.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs'
                               ? 'border border-slate-200 bg-white text-indigo-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i>
                        Mes objectifs
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'objectifs' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- ── TAB: Evaluations ── --}}
            @if ($tab === 'evaluations')

                {{-- Stats evaluations --}}
                <div class="mb-5 grid grid-cols-3 gap-3">
                    @php
                    $evalCards = [
                        ['label'=>'Total',    'value'=>$evaluationsStats['total'],  'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',            'iconWrap'=>'bg-slate-100 text-slate-600'],
                        ['label'=>'Soumises', 'value'=>$evaluationsStats['soumis'], 'icon'=>'fas fa-paper-plane',   'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',      'iconWrap'=>'bg-white text-amber-600'],
                        ['label'=>'Validees', 'value'=>$evaluationsStats['valide'], 'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
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

                {{-- Filter --}}
                <form method="GET" action="{{ route('subordonne.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            <option value="">Tous les statuts</option>
                            <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                            <option value="soumis"    @selected($filters['statut'] === 'soumis')>Soumise</option>
                            <option value="valide"    @selected($filters['statut'] === 'valide')>Validee</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('subordonne.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                        Effacer
                    </a>
                </form>

                {{-- Table evaluations --}}
                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Periode</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Note finale</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Evaluateur</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $evaluation)
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
                                            'valide'      => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis'      => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse'      => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'reclamation' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            default       => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match ($evaluation->statut) {
                                            'valide'      => 'Validée',
                                            'soumis'      => 'Soumise',
                                            'refuse'      => 'Refusée',
                                            'reclamation' => 'Réclamation',
                                            default       => 'Brouillon',
                                        };
                                        $identification = $evaluation->identification;
                                        $anneeEval  = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                                        $semestreEval = trim((string)($identification?->semestre ?? ''));
                                        if ($semestreEval === '') {
                                            $semestreEval = $evaluation->date_debut->month <= 6 ? '1' : '2';
                                        }
                                        $noteValue   = number_format($note, 2, ',', ' ');
                                        $notePercent = max(0, min(100, ($note / 10) * 100));
                                        $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="align-top hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $evaluation->id }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-slate-700">{{ $anneeEval }} - Semestre {{ $semestreEval }}</p>
                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}
                                            </p>
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
                                            {{ $evaluation->evaluateur?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <a href="{{ route('subordonne.evaluations.show', $evaluation) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-indigo-100 hover:text-indigo-600"
                                               title="Voir l'evaluation">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune evaluation</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore d'evaluation enregistree.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($evaluations->hasPages())
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        {{ $evaluations->links() }}
                    </div>
                @endif

            {{-- ── TAB: Objectifs ── --}}
            @else

                {{-- Stats objectifs --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                    $objCards = [
                        ['label'=>'Total',      'value'=>$fichesStats['total'],     'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',          'iconWrap'=>'bg-slate-100 text-slate-600'],
                        ['label'=>'Acceptees',  'value'=>$fichesStats['acceptees'], 'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
                        ['label'=>'En attente', 'value'=>$fichesStats['en_attente'],'icon'=>'fas fa-clock',         'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',    'iconWrap'=>'bg-white text-amber-600'],
                        ['label'=>'Refusees',   'value'=>$fichesStats['refusees'],  'icon'=>'fas fa-circle-xmark',  'tone'=>'border-rose-100 bg-rose-50/80 text-rose-900',       'iconWrap'=>'bg-white text-rose-500'],
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

                {{-- Filter --}}
                <form method="GET" action="{{ route('subordonne.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               placeholder="Titre ou annee..."
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-indigo-300 focus:ring-4 focus:ring-indigo-100">
                            <option value="">Tous</option>
                            <option value="en_attente" @selected($filters['statut'] === 'en_attente')>En attente</option>
                            <option value="acceptee"   @selected($filters['statut'] === 'acceptee')>Acceptee</option>
                            <option value="refusee"    @selected($filters['statut'] === 'refusee')>Refusee</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('subordonne.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
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
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Periode</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($fiches as $fiche)
                                    @php
                                        $statutClass = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            'contesté' => 'border-orange-200 bg-orange-50 text-orange-700',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $statutLabel = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Acceptée',
                                            'refusee'  => 'Refusée',
                                            'contesté' => 'Contestée',
                                            default    => 'En attente',
                                        };
                                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $fiche->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-700">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Annee {{ $fiche->annee?->annee ?? $fiche->annee_id }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600">
                                            <p>{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Echeance : {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-700">
                                                <i class="fas fa-list text-[10px]"></i>
                                                {{ $fiche->objectifs_count }}
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
                                            <a href="{{ route('subordonne.objectifs.show', $fiche) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-indigo-100 hover:text-indigo-600"
                                               title="Voir la fiche">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucun objectif</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore de fiche d'objectifs assignee.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($fiches->hasPages())
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        {{ $fiches->links() }}
                    </div>
                @endif

            @endif
        </div>

    </div>
    </div>{{-- px-4 --}}
</div>
@endsection
