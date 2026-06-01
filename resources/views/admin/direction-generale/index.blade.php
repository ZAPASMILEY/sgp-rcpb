@extends('layouts.app')

@section('title', 'Direction Générale | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction Générale')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        @if($direction)

            {{-- ── Cadres principaux : DG / DGA / Assistante ── --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-5 text-lg font-black text-slate-900">Cadres de la Direction Générale</h2>

                @if($membres->isEmpty())
                    <p class="text-sm text-slate-400">Aucun cadre enregistré.</p>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($membres as $membre)
                            @php
                                $role = $membre->role; // original case
                                $roleUp = strtoupper($role);
                                $color = match($roleUp) {
                                    'PCA'           => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'badge' => 'bg-orange-500'],
                                    'DG'            => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'badge' => 'bg-cyan-500'],
                                    'DGA'           => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'badge' => 'bg-purple-500'],
                                    'ASSISTANTE_DG' => ['bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'badge' => 'bg-pink-500'],
                                    default         => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'badge' => 'bg-slate-400'],
                                };
                                $fonction = match($roleUp) {
                                    'PCA'           => 'Président du Conseil d\'Administration',
                                    'DG'            => 'Directeur Général',
                                    'DGA'           => 'Directeur Général Adjoint',
                                    'ASSISTANTE_DG' => 'Assistante du Directeur Général',
                                    default         => ucfirst(strtolower(str_replace('_', ' ', $role))),
                                };
                                $initiales = collect(explode(' ', $membre->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                            @endphp
                            <div class="flex flex-col rounded-2xl border border-slate-100 bg-slate-50/60 p-4 gap-3">
                                {{-- Avatar + nom + badge rôle --}}
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $color['bg'] }} {{ $color['text'] }} text-sm font-black">
                                        {{ $initiales }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-900">{{ $membre->name }}</p>
                                        <span class="inline-block rounded-full {{ $color['badge'] }} px-2 py-0.5 text-[10px] font-bold text-white leading-tight mt-0.5">
                                            {{ $roleUp }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Détails --}}
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex items-center gap-2 text-slate-500">
                                        <i class="fas fa-briefcase w-3.5 text-slate-300"></i>
                                        <span>{{ $fonction }}</span>
                                    </div>
                                    @if($membre->email)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-envelope w-3.5 text-slate-300"></i>
                                            <span class="truncate">{{ $membre->email }}</span>
                                        </div>
                                    @endif
                                    @if($membre->sexe)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-venus-mars w-3.5 text-slate-300"></i>
                                            <span>{{ $membre->sexe }}</span>
                                        </div>
                                    @endif
                                    @if($membre->date_prise_fonction)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-calendar-check w-3.5 text-slate-300"></i>
                                            <span>En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $membre->date_prise_fonction)->translatedFormat('M Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action modifier --}}
                                @if(in_array($role, ['DG', 'DGA', 'Assistante_Dg']))
                                    <div class="border-t border-slate-100 pt-2">
                                        <a href="{{ route('admin.direction-generale.membres.edit', $membre) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                            <i class="fas fa-pen text-[10px]"></i> Modifier
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Secrétaires & Conseillers (2 colonnes) ── --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Secrétaires --}}
                <div class="rounded-2xl bg-white shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900">Secrétaires</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Secrétariat de la Direction Générale</p>
                        </div>
                        <a href="{{ route('admin.direction-generale.secretaires.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Ajouter
                        </a>
                    </div>

                    <ul class="space-y-2">
                        @forelse($secretaires as $secretaire)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-black text-slate-600">
                                    {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-slate-800 text-sm">{{ $secretaire->name }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $secretaire->sexe ?? '—' }}
                                        @if($secretaire->date_prise_fonction)
                                            · En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $secretaire->date_prise_fonction)->translatedFormat('M Y') }}
                                        @endif
                                    </p>
                                    @if($secretaire->email)
                                        <p class="text-xs text-slate-400 truncate">{{ $secretaire->email }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.direction-generale.secretaires.destroy', $secretaire) }}"
                                      onsubmit="return confirm('Supprimer ce secrétaire ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                <p class="text-xs text-slate-400">Aucun secrétaire enregistré.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- Conseillers --}}
                <div class="rounded-2xl bg-white shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900">Conseillers du DG</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Conseil et expertise auprès du Directeur Général</p>
                        </div>
                        <a href="{{ route('admin.direction-generale.conseillers.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Ajouter
                        </a>
                    </div>

                    <ul class="space-y-2">
                        @forelse($conseillers as $conseiller)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-xs font-black text-indigo-600">
                                    {{ strtoupper(substr($conseiller->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-slate-800 text-sm">{{ $conseiller->name }}</p>
                                    @if($conseiller->agent?->role)
                                        <p class="text-xs font-medium text-indigo-600">{{ $conseiller->agent->role }}</p>
                                    @endif
                                    @if($conseiller->agent?->date_debut_fonction)
                                        <p class="text-xs text-slate-400">
                                            En poste depuis {{ \Carbon\Carbon::parse($conseiller->agent->date_debut_fonction)->translatedFormat('M Y') }}
                                        </p>
                                    @endif
                                    @if($conseiller->email)
                                        <p class="text-xs text-slate-400 truncate">{{ $conseiller->email }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.direction-generale.conseillers.destroy', $conseiller) }}"
                                      onsubmit="return confirm('Supprimer ce conseiller ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                <p class="text-xs text-slate-400">Aucun conseiller enregistré.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>

            </div>{{-- /grid --}}

            {{-- ── Services de la Direction Générale ── --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-black text-slate-900">Services</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Services rattachés à la Direction Générale</p>
                    </div>
                    <button onclick="document.getElementById('modal-create-service-dg').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-plus"></i> Créer un service
                    </button>
                </div>

                @if($services->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                        <p class="text-xs text-slate-400">Aucun service créé pour cette direction.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($services as $item)
                            @php $svc = $item['service']; @endphp
                            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                                {{-- En-tête service --}}
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-slate-800 text-sm">{{ $svc->nom }}</span>
                                        <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">
                                            {{ $item['nbAgents'] }} agent(s)
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="document.getElementById('modal-add-agent-dg-{{ $svc->id }}').classList.remove('hidden')"
                                                class="inline-flex items-center gap-1 rounded-lg bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700 hover:bg-cyan-100 transition">
                                            <i class="fas fa-user-plus text-[10px]"></i> Ajouter un agent
                                        </button>
                                        @if($item['nbAgents'] === 0)
                                            <form method="POST" action="{{ route('admin.direction-generale.services.destroy', $svc) }}"
                                                  onsubmit="return confirm('Supprimer ce service ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>

                                {{-- Chef de service --}}
                                @if($item['chef'])
                                    <div class="mb-2 flex items-center gap-2 text-xs text-slate-500">
                                        <i class="fas fa-user-tie w-3.5 text-slate-300"></i>
                                        <span class="font-semibold">Chef :</span>
                                        <span>{{ $item['chef']->prenom }} {{ $item['chef']->nom }}</span>
                                    </div>
                                @endif

                                {{-- Agents du service --}}
                                @if($svc->agents->count() > 0)
                                    <ul class="mt-2 space-y-1.5">
                                        @foreach($svc->agents as $agent)
                                            <li class="flex items-center justify-between rounded-lg bg-white border border-slate-100 px-3 py-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-black text-slate-500">
                                                        {{ strtoupper(substr($agent->prenom, 0, 1)) }}{{ strtoupper(substr($agent->nom, 0, 1)) }}
                                                    </div>
                                                    <span class="text-xs font-semibold text-slate-700">{{ $agent->prenom }} {{ $agent->nom }}</span>
                                                    <span class="text-[10px] text-slate-400">{{ $agent->role }}</span>
                                                </div>
                                                <form method="POST" action="{{ route('admin.direction-generale.services.agents.destroy', [$svc, $agent]) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="flex h-6 w-6 items-center justify-center rounded-md bg-red-50 text-red-400 hover:bg-red-100 transition" title="Retirer du service">
                                                        <i class="fas fa-times text-[9px]"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        @else
            <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <i class="fas fa-user-tie text-2xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-600">La Direction Générale n'est pas encore configurée.</p>
                <p class="mt-1 text-xs text-slate-400">Définissez le DG, le DGA et les membres fondateurs.</p>
                <a href="{{ route('admin.direction-generale.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus"></i> Configurer la Direction Générale
                </a>
            </div>
        @endif

    </div>
</div>

{{-- ── Modal : Créer un service ── --}}
<div id="modal-create-service-dg" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl mx-4">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-black text-slate-900">Créer un service</h3>
            <button onclick="document.getElementById('modal-create-service-dg').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.direction-generale.services.store') }}">
            @csrf
            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-bold text-slate-700">Nom du service</label>
                <input type="text" name="nom" required placeholder="ex: Service Informatique"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('modal-create-service-dg').classList.add('hidden')"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modals : Ajouter un agent par service ── --}}
@if($direction)
    @foreach($direction->services as $svc)
        <div id="modal-add-agent-dg-{{ $svc->id }}" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl mx-4">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-black text-slate-900">Ajouter un agent — <span class="text-emerald-700">{{ $svc->nom }}</span></h3>
                    <button onclick="document.getElementById('modal-add-agent-dg-{{ $svc->id }}').classList.add('hidden')"
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.direction-generale.services.agents.store', $svc) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Agent à affecter</label>
                        <select name="agent_id" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                            <option value="">-- Sélectionner un agent --</option>
                            @foreach($agentsDisponibles as $a)
                                <option value="{{ $a->id }}">{{ $a->prenom }} {{ $a->nom }} ({{ $a->role }})</option>
                            @endforeach
                        </select>
                        @if($agentsDisponibles->isEmpty())
                            <p class="mt-1 text-xs text-slate-400">Aucun agent disponible dans cette direction.</p>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button"
                                onclick="document.getElementById('modal-add-agent-dg-{{ $svc->id }}').classList.add('hidden')"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                            Annuler
                        </button>
                        <button type="submit"
                                class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                            Affecter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif

@endsection
