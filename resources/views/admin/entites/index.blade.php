@extends('layouts.app')

@section('title', 'Faitiere | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Faitiere')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        @if ($entite)
            @php
                $kpis = [
                    ['label' => 'Directions', 'count' => $stats['directions'], 'icon' => 'fas fa-sitemap', 'gradient' => 'from-violet-500 to-purple-600', 'list' => route('admin.entites.directions.index'), 'create' => route('admin.entites.directions.create'), 'modal' => null],
                    ['label' => 'Services', 'count' => $stats['services'], 'icon' => 'fas fa-briefcase', 'gradient' => 'from-emerald-400 to-teal-500', 'list' => route('admin.services.faitiere'), 'create' => route('admin.services.create'), 'modal' => null],
                    ['label' => 'Secrétaires', 'count' => $stats['secretaires'], 'icon' => 'fas fa-user-check', 'gradient' => 'from-green-400 to-emerald-500', 'list' => route('admin.entites.secretaires.index'), 'create' => null, 'modal' => 'modal-secretaire'],
                    ['label' => 'Agents', 'count' => $stats['agents'], 'icon' => 'fas fa-users', 'gradient' => 'from-blue-500 to-indigo-600', 'list' => route('admin.entites.agents.index'), 'create' => null, 'modal' => 'faitiere-agent-form'],
                ];

                $tabs = [
                    'directions'  => ['label' => 'Directions',  'icon' => 'fas fa-sitemap'],
                    'services'    => ['label' => 'Services',    'icon' => 'fas fa-briefcase'],
                    'secretaires' => ['label' => 'Secrétaires', 'icon' => 'fas fa-user-check'],
                    'agents'      => ['label' => 'Agents',      'icon' => 'fas fa-users'],
                ];

                $dirigeants = [
                    [
                        'label' => 'PCA',
                        'titre' => "Président du Conseil d'Administration",
                        'nom'   => trim(($entite->pca_prenom ?? '').' '.($entite->pca_nom ?? '')) ?: 'Non renseigné',
                        'sexe'  => $entite->pca_sexe ?? null,
                        'photo' => $entite->pca_photo_path,
                        'bg'    => 'bg-orange-400',
                        'badge' => 'text-orange-300',
                    ],
                    [
                        'label' => 'DG',
                        'titre' => 'Directeur Général',
                        'nom'   => trim(($entite->directrice_generale_prenom ?? '').' '.($entite->directrice_generale_nom ?? '')) ?: 'Non renseigné',
                        'sexe'  => $entite->directrice_generale_sexe ?? null,
                        'photo' => $entite->directrice_generale_photo_path,
                        'bg'    => 'bg-cyan-500',
                        'badge' => 'text-cyan-300',
                    ],
                    [
                        'label' => 'DGA',
                        'titre' => 'Directeur Général Adjoint',
                        'nom'   => trim(($entite->dga_prenom ?? '').' '.($entite->dga_nom ?? '')) ?: 'Non renseigné',
                        'sexe'  => $entite->dga_sexe ?? null,
                        'photo' => $entite->dga_photo_path,
                        'bg'    => 'bg-violet-500',
                        'badge' => 'text-violet-300',
                    ],
                ];
            @endphp

            {{-- Header --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">Gestion de la Faitière</h1>
                        <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-400">
                            <i class="fas fa-location-dot text-xs"></i>
                            {{ $entite->ville ?: 'Ouagadougou' }}, {{ $entite->region ?: 'Centre' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <form method="POST" action="{{ route('admin.entites.reset') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                                <i class="fas fa-rotate-right text-xs"></i> Actualiser
                            </button>
                        </form>
                        <a href="{{ route('admin.entites.edit', $entite) }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                            <i class="fas fa-pen text-xs text-cyan-300"></i> Modifier la Faitière
                        </a>
                    </div>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($kpis as $kpi)
                    <div class="rounded-2xl bg-gradient-to-br {{ $kpi['gradient'] }} p-5 text-white shadow-sm">
                        <div class="flex items-start justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <i class="{{ $kpi['icon'] }} text-sm"></i>
                            </span>
                            <span class="text-3xl font-black">{{ $kpi['count'] }}</span>
                        </div>
                        <p class="mt-3 text-sm font-bold">{{ $kpi['label'] }}</p>
                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ $kpi['list'] }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white/20 px-3 py-1.5 text-xs font-bold transition hover:bg-white/30">
                                <i class="fas fa-eye text-[10px]"></i> Consulter
                            </a>
                            @if ($kpi['modal'])
                                <button type="button" onclick="document.getElementById('{{ $kpi['modal'] }}').classList.remove('hidden')" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/90 text-slate-700 transition hover:scale-105 cursor-pointer">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            @elseif ($kpi['create'])
                                <a href="{{ $kpi['create'] }}" class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-white/90 text-slate-700 transition hover:scale-105">
                                    <i class="fas fa-plus text-xs"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Two-column: Tabs + Structure --}}
            <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                {{-- Left: Tabs --}}
                <div class="rounded-2xl bg-white shadow-sm">
                    <div class="flex items-center gap-1 border-b border-slate-100 px-5 pt-4 pb-0">
                        @foreach ($tabs as $key => $tab)
                            <button type="button" data-entite-tab-trigger="{{ $key }}" class="entite-tab-trigger inline-flex items-center gap-1.5 rounded-t-xl px-4 py-2.5 text-sm font-bold text-slate-400 transition hover:text-slate-600">
                                <i class="{{ $tab['icon'] }} text-xs"></i>
                                {{ $tab['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <div class="p-5">
                        {{-- Directions panel --}}
                        <div data-entite-tab-panel="directions">
                            @forelse ($directions as $direction)
                                <a href="{{ route('admin.entites.directions.index') }}" class="flex items-center justify-between border-b border-slate-50 py-3 transition hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                        <span class="text-sm font-semibold text-slate-700">{{ $direction->nom }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </a>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-400">Aucune direction enregistrée.</p>
                            @endforelse

                            <div class="mt-4 flex items-center gap-3">
                                <a href="{{ route('admin.entites.directions.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                                    <i class="fas fa-arrow-right text-xs"></i> Voir liste
                                </a>
                                <a href="{{ route('admin.entites.directions.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-blue-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-600">
                                    <i class="fas fa-plus text-[10px]"></i> Ajouter
                                </a>
                            </div>
                        </div>

                        {{-- Services panel --}}
                        <div data-entite-tab-panel="services" class="hidden">
                            @forelse ($services as $service)
                                <a href="{{ route('admin.services.show', $service) }}" class="flex items-center justify-between border-b border-slate-50 py-3 transition hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-sm font-semibold text-slate-700">{{ $service->nom }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </a>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-400">Aucun service enregistré.</p>
                            @endforelse

                            <div class="mt-4 flex items-center gap-3">
                                <a href="{{ route('admin.services.faitiere') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                                    <i class="fas fa-arrow-right text-xs"></i> Voir liste
                                </a>
                                <a href="{{ route('admin.services.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                                    <i class="fas fa-plus text-[10px]"></i> Ajouter
                                </a>
                            </div>
                        </div>

                        {{-- Secrétaires panel --}}
                        <div data-entite-tab-panel="secretaires" class="hidden">
                            @forelse ($secretaires as $direction)
                                <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="flex items-center justify-between border-b border-slate-50 py-3 transition hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="h-2 w-2 rounded-full bg-pink-500"></span>
                                        <span class="text-sm font-semibold text-slate-700">
                                            {{ trim(($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '')) ?: 'Non renseigné' }}
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $direction->nom }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </a>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-400">Aucune secrétaire enregistrée.</p>
                            @endforelse

                            <div class="mt-4 flex items-center gap-3">
                                <a href="{{ route('admin.entites.secretaires.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                                    <i class="fas fa-arrow-right text-xs"></i> Voir liste
                                </a>
                                <button type="button" onclick="document.getElementById('modal-secretaire').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-xl bg-pink-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-pink-600">
                                    <i class="fas fa-plus text-[10px]"></i> Ajouter
                                </button>
                            </div>
                        </div>

                        {{-- Agents panel --}}
                        <div data-entite-tab-panel="agents" class="hidden">
                            @forelse ($agents as $agent)
                                <a href="{{ route('admin.agents.show', $agent) }}" class="flex items-center justify-between border-b border-slate-50 py-3 transition hover:bg-slate-50 -mx-2 px-2 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                        <span class="text-sm font-semibold text-slate-700">{{ trim(($agent->prenom ?? '').' '.($agent->nom ?? '')) ?: 'Agent' }}</span>
                                        <span class="text-xs text-slate-400">{{ $agent->fonction ?? '' }}</span>
                                    </div>
                                    <i class="fas fa-arrow-right text-xs text-slate-300"></i>
                                </a>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-400">Aucun agent enregistré.</p>
                            @endforelse

                            <div class="mt-4 flex items-center gap-3">
                                <a href="{{ route('admin.entites.agents.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                                    <i class="fas fa-arrow-right text-xs"></i> Voir liste
                                </a>
                                <button type="button" onclick="document.getElementById('faitiere-agent-form').classList.remove('hidden')" class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600">
                                    <i class="fas fa-plus text-[10px]"></i> Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Structure --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-800">Structure de la Faitière</h2>
                    <div class="mt-6 flex flex-wrap justify-center gap-6">
                        @foreach ($dirigeants as $d)
                            <div class="flex flex-col items-center text-center" style="max-width: 110px;">
                                @if (!empty($d['photo']))
                                    <img src="{{ asset('storage/'.$d['photo']) }}" alt="{{ $d['label'] }}" class="h-16 w-16 rounded-full object-cover shadow-md ring-2 ring-white">
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full {{ $d['bg'] }} text-sm font-black text-white shadow-md ring-2 ring-white">
                                        {{ $d['label'] }}
                                    </div>
                                @endif
                                <p class="mt-3 text-sm font-black text-slate-800 leading-tight">
                                    {{ $d['sexe'] === 'Feminin' ? 'Mme.' : 'M.' }}
                                    {{ Str::afterLast($d['nom'], ' ') ?: $d['nom'] }}
                                </p>
                                <p class="mt-1 text-[11px] leading-tight text-slate-400">{{ $d['titre'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bottom info bar --}}
            <div class="rounded-2xl bg-white shadow-sm">
                <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-500">
                            <i class="fas fa-phone text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Contact</p>
                            <div id="phone-display" class="mt-0.5 flex items-center gap-2">
                                <p class="text-sm font-bold text-slate-800">{{ $entite->secretariat_telephone ?? 'Non renseigné' }}</p>
                                <button type="button" onclick="document.getElementById('phone-display').classList.add('hidden'); document.getElementById('phone-edit').classList.remove('hidden'); document.getElementById('phone-input').focus();" class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-50 text-slate-400 transition hover:text-cyan-500" title="Modifier">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </button>
                            </div>
                            <form id="phone-edit" method="POST" action="{{ route('admin.entites.update-phone', $entite) }}" class="mt-0.5 hidden flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input id="phone-input" type="text" name="secretariat_telephone" value="{{ $entite->secretariat_telephone }}" class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-sm font-bold text-slate-800 shadow-sm focus:border-cyan-400 focus:ring-cyan-400" placeholder="+226 XX XX XX XX">
                                <button type="submit" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-cyan-500 text-white shadow-sm transition hover:bg-cyan-600">
                                    <i class="fas fa-check text-[10px]"></i>
                                </button>
                                <button type="button" onclick="document.getElementById('phone-edit').classList.add('hidden'); document.getElementById('phone-display').classList.remove('hidden');" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-slate-400 border border-slate-200 shadow-sm transition hover:text-rose-500">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                            <i class="fas fa-clock text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dernière mise à jour</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $entite->updated_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-500">
                            <i class="fas fa-bullseye text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Mission</p>
                            <p class="mt-0.5 text-sm font-semibold italic text-slate-600">"Coordonner et fédérer pour le développement"</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-[36px] border border-dashed border-slate-200 bg-white/90 px-8 py-20 text-center shadow-[0_30px_80px_-35px_rgba(148,163,184,0.55)]">
                <div class="mx-auto mb-8 flex h-28 w-28 items-center justify-center rounded-full bg-slate-100 text-5xl text-slate-300 shadow-inner">
                    <i class="fas fa-building"></i>
                </div>
                <h2 class="text-4xl font-black tracking-tight text-slate-800">Faitiere non configuree</h2>
                <p class="mx-auto mt-4 max-w-2xl text-slate-500">L'entite principale du reseau n'est pas encore configuree. Creez-la pour afficher ce tableau de bord.</p>
                <a href="{{ route('admin.entites.create') }}" class="mt-8 inline-flex items-center rounded-2xl bg-cyan-600 px-8 py-4 text-sm font-black uppercase tracking-[0.18em] text-white shadow-xl shadow-cyan-200 transition hover:bg-slate-900">
                    Initialiser la structure
                </a>
            </div>
        @endif
    </div>
</div>

{{-- Secrétaire creation modal --}}
@if ($entite)
<div id="modal-secretaire" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modal-secretaire').classList.add('hidden')"></div>
    <div class="relative w-full max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('modal-secretaire').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-pink-500">Nouvelle secrétaire</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter une Secrétaire</h2>
        </div>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                    <input name="prenom" type="text" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-pink-400 focus:ring-pink-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                    <input name="nom" type="text" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-pink-400 focus:ring-pink-400">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                <input name="email" type="email" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-pink-400 focus:ring-pink-400">
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Direction rattachée <span class="text-rose-500">*</span></label>
                    <select name="direction_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-pink-400 focus:ring-pink-400">
                        <option value="">-- Choisir --</option>
                        @foreach($allDirections as $dir)
                            <option value="{{ $dir->id }}">{{ $dir->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Date prise de fonction <span class="text-rose-500">*</span></label>
                    <input name="date_prise_fonction" type="date" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-pink-400 focus:ring-pink-400">
                </div>
            </div>
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-pink-500 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-pink-200 transition hover:-translate-y-0.5 hover:bg-pink-600">
                    <i class="fas fa-check"></i> Enregistrer
                </button>
                <button type="button" onclick="document.getElementById('modal-secretaire').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Agent faitière creation modal --}}
@if ($entite)
<div id="faitiere-agent-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('faitiere-agent-form').classList.add('hidden')"></div>
    <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
        <button type="button" onclick="document.getElementById('faitiere-agent-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
            <i class="fas fa-times"></i>
        </button>

        <div class="mb-6">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-orange-500">Nouvel agent</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter un Agent Faitière</h2>
        </div>

        <form method="POST" action="{{ route('admin.entites.agents.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Service <span class="text-rose-500">*</span></label>
                    <select name="service_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                        <option value="">-- Choisir --</option>
                        @foreach ($allServices as $svc)
                            <option value="{{ $svc->id }}" {{ (int) old('service_id') === $svc->id ? 'selected' : '' }}>{{ $svc->nom }} {{ $svc->direction ? '('.$svc->direction->nom.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Fonction <span class="text-rose-500">*</span></label>
                    <input type="text" name="fonction" value="{{ old('fonction') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400" placeholder="Ex: Caissier, Comptable...">
                </div>
            </div>

            <div>
                <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                    <i class="fas fa-user text-orange-500"></i>
                    Identité de l'agent
                </h3>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                        <select name="sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" {{ old('sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                            <option value="Feminin" {{ old('sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                        <input type="text" name="numero_telephone" value="{{ old('numero_telephone') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Date début fonction</label>
                        <input type="date" name="date_debut_fonction" value="{{ old('date_debut_fonction') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-orange-400 focus:ring-orange-400">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-orange-500 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-orange-600">
                    <i class="fas fa-check"></i>
                    Enregistrer
                </button>
                <button type="button" onclick="document.getElementById('faitiere-agent-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('[data-entite-tab-trigger]');
    const panels = document.querySelectorAll('[data-entite-tab-panel]');

    function activateTab(tabName) {
        triggers.forEach((trigger) => {
            const isActive = trigger.getAttribute('data-entite-tab-trigger') === tabName;

            trigger.classList.toggle('bg-blue-50', isActive);
            trigger.classList.toggle('text-blue-600', isActive);
            trigger.classList.toggle('border-b-2', isActive);
            trigger.classList.toggle('border-blue-500', isActive);
            trigger.classList.toggle('text-slate-400', !isActive);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.getAttribute('data-entite-tab-panel') !== tabName);
        });
    }

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            activateTab(this.getAttribute('data-entite-tab-trigger'));
        });
    });

    activateTab('directions');
});
</script>
@endpush
