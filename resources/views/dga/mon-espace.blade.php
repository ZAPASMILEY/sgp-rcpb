@extends('layouts.dga')

@section('title', 'Mon Espace DGA | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace / Directeur General Adjoint</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Directeur General Adjoint</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 font-black text-xl shadow-sm">
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

        {{-- Stats rapides --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
            $quickCards = [
                ['label'=>'Evaluations',  'value'=>$evaluationsStats['total'],  'icon'=>'fas fa-star',        'tone'=>'border-violet-100 bg-violet-50/80 text-violet-900',   'iconWrap'=>'bg-white text-violet-600'],
                ['label'=>'Acceptees',    'value'=>$evaluationsStats['valide'], 'icon'=>'fas fa-check',       'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
                ['label'=>'Objectifs',    'value'=>$fichesStats['total'],       'icon'=>'fas fa-bullseye',    'tone'=>'border-violet-100 bg-violet-50/80 text-violet-900',   'iconWrap'=>'bg-white text-violet-500'],
                ['label'=>'Acceptes',     'value'=>$fichesStats['acceptees'],   'icon'=>'fas fa-circle-check','tone'=>'border-slate-100 bg-white text-slate-900',            'iconWrap'=>'bg-slate-100 text-slate-600'],
            ];
            @endphp
            @foreach ($quickCards as $card)
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

        {{-- Tabs --}}
        <div class="admin-panel px-6 py-6">

            {{-- Tab nav --}}
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ route('dga.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'evaluations'
                               ? 'border border-slate-200 bg-white text-violet-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i>
                        Mes evaluations
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'evaluations' ? 'bg-violet-100 text-violet-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>
                    <a href="{{ route('dga.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs'
                               ? 'border border-slate-200 bg-white text-violet-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i>
                        Mes objectifs
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                            {{ $tab === 'objectifs' ? 'bg-violet-100 text-violet-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- ── TAB: Evaluations ── --}}
            @if ($tab === 'evaluations')

                {{-- Stats evaluations --}}
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                    $evalCards = [
                        ['label'=>'Total',    'value'=>$evaluationsStats['total'],    'icon'=>'fas fa-clipboard-list','tone'=>'border-slate-100 bg-white text-slate-900',          'iconWrap'=>'bg-slate-100 text-slate-600'],
                        ['label'=>'Soumises', 'value'=>$evaluationsStats['soumis'],   'icon'=>'fas fa-paper-plane',   'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',    'iconWrap'=>'bg-white text-amber-600'],
                        ['label'=>'Acceptees','value'=>$evaluationsStats['valide'],   'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
                        ['label'=>'Refusees', 'value'=>$evaluationsStats['refuse'],   'icon'=>'fas fa-circle-xmark',  'tone'=>'border-rose-100 bg-rose-50/80 text-rose-900',       'iconWrap'=>'bg-white text-rose-500'],
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
                <form method="GET" action="{{ route('dga.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100">
                            <option value="">Tous les statuts</option>
                            <option value="soumis"  @selected($filters['statut'] === 'soumis')>Soumise</option>
                            <option value="valide"  @selected($filters['statut'] === 'valide')>Acceptee</option>
                            <option value="refuse"  @selected($filters['statut'] === 'refuse')>Refusee</option>
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('dga.mon-espace') }}?tab=evaluations"
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
                                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse' => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default  => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match ($evaluation->statut) {
                                            'valide' => 'Acceptee',
                                            'soumis' => 'Soumise',
                                            'refuse' => 'Refusee',
                                            default  => 'Brouillon',
                                        };
                                        $identification = $evaluation->identification;
                                        $anneeEval    = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
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
                                            <a href="{{ route('dga.evaluations.show', $evaluation) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-violet-100 hover:text-violet-600"
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
                        ['label'=>'En attente', 'value'=>$fichesStats['en_attente'],'icon'=>'fas fa-clock',         'tone'=>'border-amber-100 bg-amber-50/80 text-amber-900',    'iconWrap'=>'bg-white text-amber-600'],
                        ['label'=>'Acceptees',  'value'=>$fichesStats['acceptees'], 'icon'=>'fas fa-circle-check',  'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
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
                <form method="GET" action="{{ route('dga.mon-espace') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               placeholder="Titre ou annee..."
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-violet-300 focus:ring-4 focus:ring-violet-100">
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
                    <a href="{{ route('dga.mon-espace') }}?tab=objectifs"
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
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $statutLabel = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Acceptee',
                                            'refusee'  => 'Refusee',
                                            default    => 'En attente',
                                        };
                                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4 font-black text-slate-900">{{ $fiche->id }}</td>
                                        <td class="px-4 py-4">
                                            <p class="font-semibold text-slate-700">{{ $fiche->titre }}</p>
                                            <p class="mt-1 text-xs text-slate-400">Annee {{ $fiche->annee }}</p>
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
                                            <a href="{{ route('dga.objectifs.show', $fiche) }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-violet-100 hover:text-violet-600"
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
</div>
@endsection
