@extends('layouts.directeur')

@section('title', 'Mon Espace Directeur | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Header --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace / Directeur</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Direction : <span class="font-semibold text-blue-700">{{ $direction->nom }}</span></p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 font-black text-xl shadow-sm">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <a href="{{ route('directeur.evaluations.create') }}" class="ent-btn ent-btn-primary text-xs">
                        <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
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

        {{-- KPI rapides --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @php
            $quickCards = [
                ['label'=>'Services',       'value'=> $servicesOverview->count(),    'icon'=>'fas fa-building-user',  'tone'=>'border-blue-100 bg-blue-50/80 text-blue-900',    'iconWrap'=>'bg-white text-blue-600'],
                ['label'=>'Note moyenne',   'value'=> $noteMoyenne !== null ? number_format($noteMoyenne,2,',',' ').' /10' : '—', 'icon'=>'fas fa-chart-line', 'tone'=>'border-emerald-100 bg-emerald-50/80 text-emerald-900','iconWrap'=>'bg-white text-emerald-600'],
                ['label'=>'Évaluations',    'value'=> $evaluationsStats['total'],    'icon'=>'fas fa-star',           'tone'=>'border-blue-100 bg-blue-50/80 text-blue-900',    'iconWrap'=>'bg-white text-blue-500'],
                ['label'=>'Objectifs',      'value'=> $fichesStats['total'],         'icon'=>'fas fa-bullseye',       'tone'=>'border-slate-100 bg-white text-slate-900',       'iconWrap'=>'bg-slate-100 text-slate-600'],
            ];
            @endphp
            @foreach ($quickCards as $card)
                <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                            <p class="mt-1 text-2xl font-black leading-none">{{ $card['value'] }}</p>
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
            <div class="mb-6 flex flex-wrap items-center gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    @foreach ([
                        ['key'=>'dashboard',   'icon'=>'fas fa-chart-bar',        'label'=>'Tableau de bord'],
                        ['key'=>'evaluations', 'icon'=>'fas fa-star-half-stroke',  'label'=>'Mes évaluations'],
                        ['key'=>'objectifs',   'icon'=>'fas fa-bullseye',          'label'=>'Mes objectifs'],
                    ] as $t)
                        <a href="{{ route('directeur.mon-espace') }}?tab={{ $t['key'] }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                               {{ $tab === $t['key'] ? 'border border-slate-200 bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="{{ $t['icon'] }} text-xs"></i> {{ $t['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ── TAB DASHBOARD ── --}}
            @if ($tab === 'dashboard')

                {{-- Services / Chefs de service --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-black text-slate-900">Vue d'ensemble des services</h2>
                        <a href="{{ route('directeur.evaluations.create') }}" class="ent-btn ent-btn-primary text-xs">
                            <i class="fas fa-plus mr-1"></i> Évaluer un chef
                        </a>
                    </div>

                    @if ($servicesOverview->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-400">
                            Aucun service rattaché à votre direction.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50">
                                        <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Service</th>
                                        <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Chef de service</th>
                                        <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Agents</th>
                                        <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Dernière note chef</th>
                                        <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Statut éval.</th>
                                        <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($servicesOverview as $item)
                                        @php
                                            $svc  = $item['service'];
                                            $eval = $item['eval'];
                                            $chef = trim(($svc->chef_prenom ?? '').' '.($svc->chef_nom ?? ''));
                                            $note = $eval ? number_format((float) $eval->note_finale, 2, ',', ' ') : null;
                                            $noteClass = $eval ? match(true) {
                                                (float) $eval->note_finale >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                                (float) $eval->note_finale >= 7   => 'bg-sky-100 text-sky-700',
                                                (float) $eval->note_finale >= 5   => 'bg-amber-100 text-amber-700',
                                                default                            => 'bg-rose-100 text-rose-700',
                                            } : null;
                                        @endphp
                                        <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $svc->nom }}</td>
                                            <td class="px-4 py-3">{{ $chef ?: '—' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                                    <i class="fas fa-users text-[9px]"></i> {{ $item['agents_count'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($note)
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $noteClass }}">
                                                        {{ $note }} /10
                                                    </span>
                                                @else
                                                    <span class="text-slate-300 text-xs">Non évalué</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($eval)
                                                    @php
                                                        $sc = match($eval->statut) {
                                                            'valide' => 'bg-emerald-100 text-emerald-700',
                                                            'soumis' => 'bg-amber-100 text-amber-700',
                                                            'refuse' => 'bg-rose-100 text-rose-700',
                                                            default  => 'bg-slate-100 text-slate-600',
                                                        };
                                                        $sl = match($eval->statut) {
                                                            'valide' => 'Validée', 'soumis' => 'Soumise',
                                                            'refuse' => 'Refusée', default => 'Brouillon',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $sc }}">{{ $sl }}</span>
                                                @else
                                                    <span class="text-slate-300 text-xs">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <div class="flex justify-end gap-2">
                                                    @if ($eval)
                                                        <a href="{{ route('directeur.evaluations.show', $eval) }}"
                                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition" title="Voir">
                                                            <i class="fas fa-eye text-xs"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('directeur.evaluations.create', ['service_id' => $svc->id]) }}"
                                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition" title="Nouvelle évaluation">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Évaluations créées par le directeur --}}
                    @if ($evaluationsCreees->isNotEmpty())
                        <div class="mt-6">
                            <h2 class="mb-3 text-base font-black text-slate-900">Toutes les évaluations créées</h2>
                            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                <table class="w-full text-left text-sm text-slate-700">
                                    <thead>
                                        <tr class="border-b border-slate-100 bg-slate-50">
                                            <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Service</th>
                                            <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Période</th>
                                            <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Note</th>
                                            <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Statut</th>
                                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wider text-slate-400">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($evaluationsCreees as $eval)
                                            @php
                                                $sc = match($eval->statut) {
                                                    'valide' => 'bg-emerald-100 text-emerald-700',
                                                    'soumis' => 'bg-amber-100 text-amber-700',
                                                    'refuse' => 'bg-rose-100 text-rose-700',
                                                    default  => 'bg-slate-100 text-slate-600',
                                                };
                                                $sl = match($eval->statut) {
                                                    'valide' => 'Validée', 'soumis' => 'Soumise',
                                                    'refuse' => 'Refusée', default => 'Brouillon',
                                                };
                                            @endphp
                                            <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $eval->evaluable?->nom ?? '—' }}</td>
                                                <td class="px-4 py-3 text-slate-500">{{ $eval->date_debut->format('m/Y') }} — {{ $eval->date_fin->format('m/Y') }}</td>
                                                <td class="px-4 py-3 font-bold">{{ number_format((float) $eval->note_finale, 2, ',', ' ') }} /10</td>
                                                <td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $sc }}">{{ $sl }}</span></td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex justify-end gap-1">
                                                        <a href="{{ route('directeur.evaluations.show', $eval) }}"
                                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-blue-50 hover:text-blue-600 transition">
                                                            <i class="fas fa-eye text-xs"></i>
                                                        </a>
                                                        @if ($eval->statut === 'brouillon')
                                                            <form method="POST" action="{{ route('directeur.evaluations.submit', $eval) }}">
                                                                @csrf @method('PATCH')
                                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition" title="Soumettre">
                                                                    <i class="fas fa-paper-plane text-xs"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('directeur.evaluations.destroy', $eval) }}" onsubmit="return confirm('Supprimer cette évaluation ?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-500 transition" title="Supprimer">
                                                                    <i class="fas fa-trash text-xs"></i>
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
                        </div>
                    @endif
                </div>

            {{-- ── TAB ÉVALUATIONS REÇUES ── --}}
            @elseif ($tab === 'evaluations')

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ([
                        ['label'=>'Total',    'value'=>$evaluationsStats['total'],    'color'=>'bg-slate-100 text-slate-700'],
                        ['label'=>'Soumises', 'value'=>$evaluationsStats['soumis'],   'color'=>'bg-amber-100 text-amber-700'],
                        ['label'=>'Acceptées','value'=>$evaluationsStats['valide'],   'color'=>'bg-emerald-100 text-emerald-700'],
                        ['label'=>'Refusées', 'value'=>$evaluationsStats['refuse'],   'color'=>'bg-rose-100 text-rose-700'],
                    ] as $c)
                        <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm text-center">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $c['label'] }}</p>
                            <p class="mt-1 text-2xl font-black"><span class="inline-flex rounded-full px-3 py-0.5 {{ $c['color'] }}">{{ $c['value'] }}</span></p>
                        </div>
                    @endforeach
                </div>

                @forelse ($evaluationsRecues as $eval)
                    @php
                        $ident = $eval->identification;
                        $periode = $eval->date_debut->format('m/Y').' — '.$eval->date_fin->format('m/Y');
                        $sc = match($eval->statut) {
                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                            'refuse' => 'border-rose-200 bg-rose-50 text-rose-700',
                            default  => 'border-slate-200 bg-slate-100 text-slate-600',
                        };
                        $sl = match($eval->statut) {
                            'valide' => 'Acceptée', 'soumis' => 'Soumise', 'refuse' => 'Refusée', default => 'Brouillon',
                        };
                        $mention = match(true) {
                            (float)$eval->note_finale >= 8.5 => 'Excellent',
                            (float)$eval->note_finale >= 7   => 'Bien',
                            (float)$eval->note_finale >= 5   => 'Passable',
                            default                          => 'Insuffisant',
                        };
                        $mentionClass = match($mention) {
                            'Excellent' => 'bg-emerald-100 text-emerald-700',
                            'Bien'      => 'bg-sky-100 text-sky-700',
                            'Passable'  => 'bg-amber-100 text-amber-700',
                            default     => 'bg-rose-100 text-rose-700',
                        };
                    @endphp
                    <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Évaluation reçue</p>
                                <p class="mt-1 text-base font-black text-slate-900">{{ $periode }}</p>
                                <p class="mt-0.5 text-sm text-slate-500">Évaluateur : {{ $eval->evaluateur?->name ?? '—' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $sc }}">{{ $sl }}</span>
                                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{ $mentionClass }}">{{ $mention }}</span>
                                <span class="text-lg font-black text-slate-900">{{ number_format((float) $eval->note_finale, 2, ',', ' ') }}/10</span>
                                <a href="{{ route('directeur.evaluations.show', $eval) }}" class="ent-btn ent-btn-soft text-xs">Voir</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm text-slate-400">
                        Aucune évaluation reçue pour le moment.
                    </div>
                @endforelse

            {{-- ── TAB OBJECTIFS REÇUS ── --}}
            @elseif ($tab === 'objectifs')

                <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ([
                        ['label'=>'Total',      'value'=>$fichesStats['total'],      'color'=>'bg-slate-100 text-slate-700'],
                        ['label'=>'Acceptées',  'value'=>$fichesStats['acceptees'],  'color'=>'bg-emerald-100 text-emerald-700'],
                        ['label'=>'En attente', 'value'=>$fichesStats['en_attente'], 'color'=>'bg-amber-100 text-amber-700'],
                    ] as $c)
                        <div class="rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm text-center">
                            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $c['label'] }}</p>
                            <p class="mt-1 text-2xl font-black"><span class="inline-flex rounded-full px-3 py-0.5 {{ $c['color'] }}">{{ $c['value'] }}</span></p>
                        </div>
                    @endforeach
                </div>

                @forelse ($fichesObjectifs as $fiche)
                    @php
                        $sc = match($fiche->statut) {
                            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
                            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
                            default      => 'border-slate-200 bg-slate-100 text-slate-600',
                        };
                        $sl = match($fiche->statut) {
                            'acceptee' => 'Acceptée', 'en_attente' => 'En attente', 'refusee' => 'Refusée', default => ucfirst($fiche->statut),
                        };
                    @endphp
                    <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-black text-slate-900">{{ $fiche->titre }}</p>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    Année : {{ $fiche->annee }}
                                    @if ($fiche->date_echeance)
                                        · Échéance : {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}
                                    @endif
                                </p>
                                <p class="mt-1 text-sm text-slate-400">{{ $fiche->objectifs->count() }} objectif(s)</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $sc }}">{{ $sl }}</span>
                                <a href="{{ route('directeur.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft text-xs">Voir</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center text-sm text-slate-400">
                        Aucune fiche d'objectifs reçue pour le moment.
                    </div>
                @endforelse

            @endif

        </div>
    </div>
</div>
@endsection
