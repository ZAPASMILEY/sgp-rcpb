@extends('layouts.personnel')

@section('title', 'Mon Dossier | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Mon dossier · Personnel</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $agent?->fonction ?? $user->role }}
                    @if ($agent?->service)
                        · {{ $agent->service->nom }}
                    @elseif ($agent?->agence)
                        · {{ $agent->agence->nom }}
                    @endif
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 font-black text-xl shadow-sm">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    @if (! $agent)
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-12 text-center shadow-sm">
            <i class="fas fa-user-slash text-3xl text-slate-300"></i>
            <p class="mt-3 text-sm font-semibold text-slate-700">Aucun dossier agent associé à votre compte.</p>
            <p class="mt-1 text-xs text-slate-500">Contactez l'administrateur pour lier votre compte à un dossier agent.</p>
        </div>
    @else

        {{-- Informations personnelles --}}
        <div class="admin-panel px-6 py-5">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 mb-3">Informations personnelles</p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm text-slate-700">
                @if ($agent->prenom || $agent->nom)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Nom complet</p>
                        <p class="mt-1 font-semibold">{{ trim($agent->prenom.' '.$agent->nom) }}</p>
                    </div>
                @endif
                @if ($agent->fonction)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Fonction</p>
                        <p class="mt-1 font-semibold">{{ $agent->fonction }}</p>
                    </div>
                @endif
                @if ($agent->service)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Service</p>
                        <p class="mt-1 font-semibold">{{ $agent->service->nom }}</p>
                    </div>
                @elseif ($agent->agence)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Agence</p>
                        <p class="mt-1 font-semibold">{{ $agent->agence->nom }}</p>
                    </div>
                @endif
                @if ($agent->email)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Email</p>
                        <p class="mt-1 font-semibold">{{ $agent->email }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-6 shadow-sm">

            {{-- Tab nav --}}
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    <a href="{{ route('personnel.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'evaluations' ? 'border border-slate-200 bg-white text-cyan-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-star-half-stroke text-xs"></i>
                        Mes évaluations
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $tab === 'evaluations' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $evaluationsStats['total'] }}
                        </span>
                    </a>
                    <a href="{{ route('personnel.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $tab === 'objectifs' ? 'border border-slate-200 bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="fas fa-bullseye text-xs"></i>
                        Mes objectifs
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-black {{ $tab === 'objectifs' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                            {{ $fichesStats['total'] }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- ── TAB: Évaluations ── --}}
            @if ($tab === 'evaluations')

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

                <form method="GET" action="{{ route('personnel.mon-espace') }}"
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
                    <a href="{{ route('personnel.mon-espace') }}?tab=evaluations"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

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
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Action</th>
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
                                            default  => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statusLabel = match ($evaluation->statut) {
                                            'valide' => 'Validée', 'soumis' => 'Soumise', default => 'Brouillon',
                                        };
                                        $identification = $evaluation->identification;
                                        $anneeEval = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
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
                                            <p class="mt-1 text-xs text-slate-400">{{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}</p>
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
                                        <td class="px-4 py-4 text-slate-600">{{ $evaluation->evaluateur?->name ?? '-' }}</td>
                                        <td class="px-4 py-4">
                                            @if ($evaluation->statut !== 'brouillon')
                                            <a href="{{ route('personnel.evaluations.show', $evaluation) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700">
                                                <i class="fas fa-eye text-[10px]"></i> Voir
                                            </a>
                                            @else
                                            <span class="text-[11px] text-slate-300">En cours</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune évaluation</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore d'évaluation enregistrée.</p>
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

            {{-- ── TAB: Objectifs ── --}}
            @else

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

                <form method="GET" action="{{ route('personnel.mon-espace') }}"
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
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('personnel.mon-espace') }}?tab=objectifs"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        Effacer
                    </a>
                </form>

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
                                        $statutClass = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $statutLabel = match ($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Acceptée', 'refusee' => 'Refusée', default => 'En attente',
                                        };
                                        $av = (int) ($fiche->avancement_percentage ?? 0);
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
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutClass }}">{{ $statutLabel }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <a href="{{ route('personnel.fiches.show', $fiche) }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                                                <i class="fas fa-eye text-[10px]"></i> Voir
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucun objectif</p>
                                                <p class="mt-1 text-xs text-slate-500">Vous n'avez pas encore de fiche d'objectifs assignée.</p>
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
        </div>{{-- fin panel tabs --}}
    @endif

</div>
</div>
@endsection
