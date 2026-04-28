@extends('layouts.rh')

@section('title', 'Espace RH | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Direction des Ressources Humaines</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Tableau de bord RH</h1>
                    <p class="mt-1 text-sm text-slate-500">Vue complète du réseau — accès intégral</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-purple-700 font-black text-xl shadow-sm">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Stats globales --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
            @php
            $statCards = [
                ['label' => 'Agents',          'value' => $stats['agents'],      'icon' => 'fas fa-users',            'tone' => 'border-purple-100 bg-purple-50 text-purple-900',   'iconWrap' => 'bg-white text-purple-600'],
                ['label' => 'Évaluations',     'value' => $stats['evaluations'], 'icon' => 'fas fa-clipboard-list',   'tone' => 'border-slate-100 bg-white text-slate-900',         'iconWrap' => 'bg-slate-100 text-slate-600'],
                ['label' => 'Éval. validées',  'value' => $stats['eval_valide'], 'icon' => 'fas fa-circle-check',     'tone' => 'border-emerald-100 bg-emerald-50 text-emerald-900','iconWrap' => 'bg-white text-emerald-600'],
                ['label' => 'Objectifs',        'value' => $stats['objectifs'],   'icon' => 'fas fa-bullseye',         'tone' => 'border-slate-100 bg-white text-slate-900',         'iconWrap' => 'bg-slate-100 text-slate-600'],
                ['label' => 'Obj. acceptés',   'value' => $stats['obj_accepte'], 'icon' => 'fas fa-trophy',           'tone' => 'border-amber-100 bg-amber-50 text-amber-900',      'iconWrap' => 'bg-white text-amber-600'],
            ];
            @endphp
            @foreach ($statCards as $card)
                <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                    <div class="flex items-start justify-between gap-2">
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
            <div class="mb-6 inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                @php
                $tabs = [
                    ['key' => 'agents',      'label' => 'Agents',       'icon' => 'fas fa-users'],
                    ['key' => 'evaluations', 'label' => 'Évaluations',  'icon' => 'fas fa-star-half-stroke'],
                    ['key' => 'objectifs',   'label' => 'Objectifs',    'icon' => 'fas fa-bullseye'],
                ];
                @endphp
                @foreach ($tabs as $t)
                    <a href="{{ route('rh.dashboard') }}?tab={{ $t['key'] }}"
                       class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                           {{ $filters['tab'] === $t['key']
                               ? 'border border-slate-200 bg-white text-purple-700 shadow-sm'
                               : 'text-slate-500 hover:text-slate-800' }}">
                        <i class="{{ $t['icon'] }} text-xs"></i>
                        {{ $t['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- ══ TAB AGENTS ══ --}}
            @if ($filters['tab'] === 'agents')

                <form method="GET" action="{{ route('rh.dashboard') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="agents">

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Recherche</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               placeholder="Nom, email, fonction…"
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Délégation Technique</label>
                        <select name="dt_id"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                            <option value="">Toutes les DT</option>
                            @foreach ($delegations as $dt)
                                <option value="{{ $dt->id }}" @selected(request('dt_id') == $dt->id)>
                                    {{ $dt->region }} — {{ $dt->ville }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Caisse</label>
                        <select name="caisse_id"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                            <option value="">Toutes les caisses</option>
                            @foreach ($caisses as $c)
                                <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>{{ $c->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Direction</label>
                        <select name="dir_id"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                            <option value="">Toutes les directions</option>
                            @foreach ($directions as $d)
                                <option value="{{ $d->id }}" @selected(request('dir_id') == $d->id)>{{ $d->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-purple-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('rh.dashboard') }}?tab=agents"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                        Effacer
                    </a>
                </form>

                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Agent</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fonction</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Structure</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Rôle système</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($agents as $agent)
                                    @php
                                        $structure = $agent->caisse?->nom
                                            ?? ($agent->delegationTechnique ? $agent->delegationTechnique->region.' — '.$agent->delegationTechnique->ville : null)
                                            ?? $agent->direction?->nom
                                            ?? $agent->agence?->nom
                                            ?? $agent->service?->nom
                                            ?? '—';
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-xs font-black text-purple-700">
                                                    {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600">{{ $agent->fonction ?? '—' }}</td>
                                        <td class="px-4 py-4 text-slate-600 text-xs">{{ $structure }}</td>
                                        <td class="px-4 py-4">
                                            @if ($agent->user)
                                                <span class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-black text-purple-700">
                                                    {{ $agent->user->role }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-xs">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-slate-500 text-xs">{{ $agent->email }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-users text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucun agent trouvé</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($agents->hasPages())
                    <div class="mt-5 border-t border-slate-200 pt-4">{{ $agents->links() }}</div>
                @endif

            {{-- ══ TAB ÉVALUATIONS ══ --}}
            @elseif ($filters['tab'] === 'evaluations')

                {{-- Stats évals --}}
                @if ($evalStats)
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @php
                    $ec = [
                        ['label'=>'Total',     'value'=>$evalStats['total'],     'tone'=>'border-slate-100 bg-white text-slate-900'],
                        ['label'=>'Brouillon', 'value'=>$evalStats['brouillon'], 'tone'=>'border-slate-100 bg-slate-50 text-slate-700'],
                        ['label'=>'Soumises',  'value'=>$evalStats['soumis'],    'tone'=>'border-amber-100 bg-amber-50 text-amber-900'],
                        ['label'=>'Validées',  'value'=>$evalStats['valide'],    'tone'=>'border-emerald-100 bg-emerald-50 text-emerald-900'],
                        ['label'=>'Refusées',  'value'=>$evalStats['refuse'],    'tone'=>'border-rose-100 bg-rose-50 text-rose-900'],
                    ];
                    @endphp
                    @foreach ($ec as $c)
                        <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $c['tone'] }}">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $c['label'] }}</p>
                            <p class="mt-1 text-2xl font-black">{{ $c['value'] }}</p>
                        </div>
                    @endforeach
                </div>
                @endif

                <form method="GET" action="{{ route('rh.dashboard') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="evaluations">

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Agent évalué</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               placeholder="Nom de l'agent…"
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                            <option value="">Tous</option>
                            <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
                            <option value="soumis"    @selected($filters['statut'] === 'soumis')>Soumise</option>
                            <option value="valide"    @selected($filters['statut'] === 'valide')>Validée</option>
                            <option value="refuse"    @selected($filters['statut'] === 'refuse')>Refusée</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Année</label>
                        <input type="number" name="annee" value="{{ $filters['annee'] }}" min="2020" max="2030"
                               placeholder="2026"
                               class="w-28 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                    </div>

                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-purple-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('rh.dashboard') }}?tab=evaluations"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                        Effacer
                    </a>
                </form>

                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Agent évalué</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Note finale</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Évaluateur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($evaluations as $evaluation)
                                    @php
                                        $note        = (float) $evaluation->note_finale;
                                        $mention     = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                                        $mentionCls  = match($mention) {
                                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
                                            'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                            default     => 'border-rose-200 bg-rose-50 text-rose-700',
                                        };
                                        $statutCls   = match($evaluation->statut) {
                                            'valide'  => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'soumis'  => 'border-amber-200 bg-amber-50 text-amber-700',
                                            'refuse'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default   => 'border-slate-200 bg-slate-100 text-slate-700',
                                        };
                                        $statutLabel = match($evaluation->statut) {
                                            'valide' => 'Validée', 'soumis' => 'Soumise',
                                            'refuse' => 'Refusée', default  => 'Brouillon',
                                        };
                                        $agent       = $evaluation->evaluable;
                                        $notePercent = max(0, min(100, ($note / 10) * 100));
                                        $noteBarCls  = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4">
                                            @if ($agent)
                                                <p class="font-bold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                                <p class="text-xs text-slate-400">{{ $agent->fonction }}</p>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-slate-600 text-xs">
                                            {{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[110px]">
                                                <div class="mb-1 flex items-center justify-between gap-1">
                                                    <span class="text-[10px] font-black uppercase text-slate-400">Score</span>
                                                    <span class="text-xs font-black text-slate-700">{{ number_format($note, 2, ',', ' ') }}/10</span>
                                                </div>
                                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $noteBarCls }}" style="width: {{ $notePercent }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionCls }}">{{ $mention }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutCls }}">{{ $statutLabel }}</span>
                                        </td>
                                        <td class="px-4 py-4 text-slate-500 text-xs">{{ $evaluation->evaluateur?->name ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-clipboard-list text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucune évaluation trouvée</p>
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

            {{-- ══ TAB OBJECTIFS ══ --}}
            @else

                {{-- Stats objectifs --}}
                @if ($ficheStats)
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                    $fc = [
                        ['label'=>'Total',      'value'=>$ficheStats['total'],      'tone'=>'border-slate-100 bg-white text-slate-900'],
                        ['label'=>'Acceptées',  'value'=>$ficheStats['acceptee'],   'tone'=>'border-emerald-100 bg-emerald-50 text-emerald-900'],
                        ['label'=>'En attente', 'value'=>$ficheStats['en_attente'], 'tone'=>'border-amber-100 bg-amber-50 text-amber-900'],
                        ['label'=>'Refusées',   'value'=>$ficheStats['refusee'],    'tone'=>'border-rose-100 bg-rose-50 text-rose-900'],
                    ];
                    @endphp
                    @foreach ($fc as $c)
                        <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $c['tone'] }}">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $c['label'] }}</p>
                            <p class="mt-1 text-2xl font-black">{{ $c['value'] }}</p>
                        </div>
                    @endforeach
                </div>
                @endif

                <form method="GET" action="{{ route('rh.dashboard') }}"
                      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                    <input type="hidden" name="tab" value="objectifs">

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Titre / Agent</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                               placeholder="Titre ou nom agent…"
                               class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
                        <select name="statut"
                                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                            <option value="">Tous</option>
                            <option value="en_attente" @selected($filters['statut'] === 'en_attente')>En attente</option>
                            <option value="acceptee"   @selected($filters['statut'] === 'acceptee')>Acceptée</option>
                            <option value="refusee"    @selected($filters['statut'] === 'refusee')>Refusée</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Année</label>
                        <input type="number" name="annee" value="{{ $filters['annee'] }}" min="2020" max="2030"
                               placeholder="2026"
                               class="w-28 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-purple-300 focus:ring-4 focus:ring-purple-100">
                    </div>

                    <button type="submit"
                            class="inline-flex items-center rounded-2xl bg-purple-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-purple-800">
                        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
                    </button>
                    <a href="{{ route('rh.dashboard') }}?tab=objectifs"
                       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
                        Effacer
                    </a>
                </form>

                <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="bg-slate-50/80">
                                <tr class="border-b border-slate-200 text-slate-500">
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fiche</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Assigné à</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($fiches as $fiche)
                                    @php
                                        $agent      = $fiche->assignable;
                                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                                        $avCls      = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                                        $stCls      = match($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                                        };
                                        $stLabel    = match($fiche->statut ?? 'en_attente') {
                                            'acceptee' => 'Acceptée', 'refusee' => 'Refusée', default => 'En attente',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="px-4 py-4">
                                            <p class="font-bold text-slate-900">{{ $fiche->titre }}</p>
                                            <p class="text-xs text-slate-400">Année {{ $fiche->annee_id }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if ($agent)
                                                <p class="font-semibold text-slate-700">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                                <p class="text-xs text-slate-400">{{ $agent->fonction }}</p>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-xs text-slate-500 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}
                                            <br><span class="text-slate-400">Éch. {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-700">
                                                <i class="fas fa-list text-[10px]"></i> {{ $fiche->objectifs_count }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="min-w-[100px]">
                                                <div class="mb-1 flex items-center justify-between gap-1">
                                                    <span class="text-[10px] font-black uppercase text-slate-400">Progress</span>
                                                    <span class="text-xs font-black text-slate-700">{{ $avancement }}%</span>
                                                </div>
                                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $avCls }}" style="width: {{ $avancement }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $stCls }}">{{ $stLabel }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center">
                                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                                <i class="fas fa-bullseye text-2xl text-slate-300"></i>
                                                <p class="mt-2 text-sm font-black text-slate-700">Aucun objectif trouvé</p>
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
        </div>

    </div>
</div>
@endsection
