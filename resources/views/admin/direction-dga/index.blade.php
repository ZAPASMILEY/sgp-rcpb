@extends('layouts.app')
@section('title', 'Direction DGA | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction DGA')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full space-y-6">

    @if (session('status'))
        <div id="status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600"><i class="fas fa-check"></i></div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('status-msg')?.remove(), 3000);</script>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-500"><i class="fas fa-exclamation-triangle text-sm"></i></div>
            <p class="text-sm font-semibold text-rose-700">{{ session('error') }}</p>
        </div>
    @endif

    @if (! $direction)
        <div class="rounded-2xl bg-white p-10 shadow-sm text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-100">
                <i class="fas fa-sitemap text-2xl text-purple-400"></i>
            </div>
            <p class="font-bold text-slate-600">Direction DGA introuvable.</p>
            <p class="mt-1 text-xs text-slate-400">La direction « Direction Générale Adjointe » n'existe pas encore ou aucun DGA n'y est affecté.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a href="{{ route('admin.directions.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-plus text-xs text-slate-400"></i> Créer la direction
                </a>
                <a href="{{ route('admin.direction-dga.configurer') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-purple-200 transition hover:bg-purple-700">
                    <i class="fas fa-user-tie text-xs"></i> Configurer le DGA
                </a>
            </div>
        </div>
    @else

    {{-- ── En-tête direction ─────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Faîtière / Direction</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $direction->nom }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.direction-dga.configurer') }}" class="ent-btn ent-btn-soft text-xs py-1.5 px-4">
                    <i class="fas fa-user-tie mr-1.5"></i>Configurer DGA
                </a>
                <a href="{{ route('admin.directions.edit', $direction) }}" class="ent-btn ent-btn-primary text-xs py-1.5 px-4">
                    <i class="fas fa-pen mr-1.5"></i>Modifier
                </a>
            </div>
        </div>

        {{-- DGA + Secrétaire --}}
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            {{-- DGA --}}
            <div class="flex items-center gap-4 rounded-2xl border border-purple-100 bg-purple-50/60 p-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-700 font-black text-lg">
                    {{ $direction->directeur ? strtoupper(substr($direction->directeur->prenom, 0, 1)) : '?' }}
                </div>
                <div class="min-w-0">
                    @if($direction->directeur)
                        <p class="font-black text-slate-900 truncate">{{ $direction->directeur->prenom }} {{ $direction->directeur->nom }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $direction->directeur->email }}</p>
                    @else
                        <p class="text-sm italic text-slate-400">DGA non affecté</p>
                    @endif
                    <span class="mt-1 inline-flex rounded-full bg-purple-500 px-2 py-0.5 text-[10px] font-bold text-white">DGA</span>
                </div>
            </div>

            {{-- Secrétaire --}}
            <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-slate-600 font-black text-lg">
                    {{ $direction->secretaire ? strtoupper(substr($direction->secretaire->prenom, 0, 1)) : '?' }}
                </div>
                <div class="min-w-0 flex-1">
                    @if($direction->secretaire)
                        <p class="font-black text-slate-900 truncate">{{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $direction->secretaire->email }}</p>
                        <span class="mt-1 inline-flex rounded-full bg-slate-400 px-2 py-0.5 text-[10px] font-bold text-white">Secrétaire</span>
                    @else
                        <p class="text-sm italic text-slate-400">Secrétaire non affecté(e)</p>
                    @endif
                </div>
                <a href="{{ route('admin.direction-dga.configurer') }}"
                   class="shrink-0 flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition">
                    <i class="fas fa-pen text-xs"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ── Services ──────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-black text-slate-900">Services</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ count($services) }} service{{ count($services) > 1 ? 's' : '' }} rattaché{{ count($services) > 1 ? 's' : '' }} à la Direction DGA</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create-service').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-purple-700 transition">
                <i class="fas fa-plus text-[10px]"></i> Créer un service
            </button>
        </div>

        @if(count($services) === 0)
            <div class="px-5 py-10 text-center text-sm text-slate-400">Aucun service configuré.</div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($services as $s)
                @php
                    $service  = $s['service'];
                    $chef     = $s['chef'];
                    $chefUser = $s['chefUser'];
                    $membres  = $service->agents->where('id', '!=', $chef?->id)->values();
                    $dispoPourService = $agentsDisponibles->filter(fn($a) => (int)$a->id !== $chef?->id);
                    $chefsModal = $chefsDisponibles->when($chef, fn($c) => $c->push($chef)->sortBy('nom')->values());
                @endphp
                <div class="px-5 py-4">
                    {{-- Ligne service + actions --}}
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-purple-600 text-white font-black text-lg shadow">
                            {{ strtoupper(substr($service->nom, 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-900">{{ $service->nom }}</p>
                            @if($chef)
                                <p class="mt-0.5 text-sm text-slate-500">
                                    <i class="fas fa-user-tie mr-1 text-purple-400"></i>
                                    <span class="font-semibold text-slate-700">{{ $chef->prenom }} {{ $chef->nom }}</span>
                                    <span class="text-xs text-slate-400 ml-1">— {{ $chef->role }}</span>
                                </p>
                            @else
                                <p class="mt-0.5 text-sm italic text-slate-400">Chef non affecté</p>
                            @endif
                            <div class="mt-1.5 flex flex-wrap gap-1.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-500">
                                    <i class="fas fa-users text-[9px]"></i> {{ $s['nbAgents'] }} agent{{ $s['nbAgents'] > 1 ? 's' : '' }}
                                </span>
                                @if($chefUser)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold
                                        {{ $chefUser->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        Compte {{ $chefUser->is_active ? 'actif' : 'inactif' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-700 px-2.5 py-0.5 text-[11px] font-semibold">
                                        <i class="fas fa-exclamation-triangle text-[9px]"></i> Sans compte
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            {{-- Voir le service --}}
                            <a href="{{ route('admin.services.show', $service) }}"
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-cyan-600 transition">
                                <i class="fas fa-eye text-[10px]"></i> Voir
                            </a>

                            {{-- Affecter / changer le chef --}}
                            <button type="button"
                                    onclick="document.getElementById('modal-chef-dga-{{ $service->id }}').classList.remove('hidden')"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-purple-50 hover:border-purple-200 hover:text-purple-700 transition">
                                <i class="fas fa-user-tie text-[10px]"></i>
                                {{ $chef ? 'Changer le chef' : 'Affecter un chef' }}
                            </button>

                            {{-- Ajouter un agent (désactivé si pas de chef) --}}
                            @if($chef)
                            <button type="button"
                                    onclick="document.getElementById('modal-add-agent-{{ $service->id }}').classList.remove('hidden')"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition">
                                <i class="fas fa-user-plus text-[10px]"></i> Ajouter un agent
                            </button>
                            @else
                            <span title="Affectez d'abord un chef de service"
                                  class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-300 cursor-not-allowed">
                                <i class="fas fa-user-plus text-[10px]"></i> Ajouter un agent
                            </span>
                            @endif

                            {{-- Supprimer (seulement si vide et sans chef) --}}
                            @if($s['nbAgents'] === 0 && ! $chef)
                            <form method="POST" action="{{ route('admin.direction-dga.services.destroy', $service) }}"
                                  onsubmit="return confirm('Supprimer le service « {{ e($service->nom) }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-rose-200 bg-rose-50 text-rose-500 hover:bg-rose-100 transition">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    {{-- Agents du service --}}
                    @if($membres->isNotEmpty())
                        <div class="mt-3 border-l-2 border-purple-100 space-y-1 pl-1" style="margin-left:3.75rem">
                            @foreach($membres as $agent)
                            @php $agentUser = \App\Models\User::where('agent_id', $agent->id)->first(); @endphp
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 text-xs font-black">
                                    {{ strtoupper(substr($agent->prenom, 0, 1)) }}{{ strtoupper(substr($agent->nom, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate-800 truncate">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $agent->role }}</p>
                                </div>
                                @if($agentUser)
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                        {{ $agentUser->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                        <i class="fas fa-circle text-[6px]"></i>
                                        {{ $agentUser->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                @else
                                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-slate-100 text-slate-400 px-2 py-0.5 text-[10px] font-semibold">
                                        Sans compte
                                    </span>
                                @endif
                                <form method="POST"
                                      action="{{ route('admin.direction-dga.services.agents.destroy', [$service, $agent]) }}"
                                      onsubmit="return confirm('Retirer {{ e($agent->prenom) }} {{ e($agent->nom) }} du service ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-rose-200 bg-white text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition"
                                            title="Retirer du service">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-xs italic text-slate-300" style="margin-left:3.75rem">Aucun autre agent dans ce service.</p>
                    @endif

                    {{-- Modale : affecter / changer le chef --}}
                    <div id="modal-chef-dga-{{ $service->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl mx-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900">
                                    {{ $chef ? 'Changer le chef' : 'Affecter un chef' }} — <span class="text-purple-700">{{ $service->nom }}</span>
                                </h3>
                                <button type="button" onclick="document.getElementById('modal-chef-dga-{{ $service->id }}').classList.add('hidden')"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            @if($chefsModal->isEmpty())
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center">
                                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fas fa-user-tie text-slate-400 text-sm"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">Aucun Chef de Service disponible</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Créez d'abord un agent avec le rôle « Chef de Service ».</p>
                                    <a href="{{ route('admin.agents.create', ['redirect_to' => route('admin.direction-dga.index')]) }}"
                                       class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-purple-700 transition">
                                        <i class="fas fa-user-plus text-[10px]"></i> Créer un agent
                                    </a>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="button" onclick="document.getElementById('modal-chef-dga-{{ $service->id }}').classList.add('hidden')"
                                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                        Fermer
                                    </button>
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.direction-dga.services.chef.update', $service) }}" class="space-y-4">
                                    @csrf @method('PUT')
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Chef de service <span class="text-rose-500">*</span></label>
                                        <select name="chef_agent_id" required
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100" data-no-ts>
                                            <option value="">— Choisir —</option>
                                            @foreach($chefsModal as $c)
                                                <option value="{{ $c->id }}" {{ $chef && $chef->id === $c->id ? 'selected' : '' }}>
                                                    {{ $c->prenom }} {{ $c->nom }}{{ isset($c->matricule) && $c->matricule ? ' ('.$c->matricule.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('modal-chef-dga-{{ $service->id }}').classList.add('hidden')"
                                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                                class="rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white hover:bg-purple-700">
                                            <i class="fas fa-check mr-1"></i>Confirmer
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>

                    {{-- Modale : ajouter un agent --}}
                    <div id="modal-add-agent-{{ $service->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                        <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl mx-4">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-black text-slate-900">Ajouter un agent — <span class="text-purple-700">{{ $service->nom }}</span></h3>
                                <button type="button" onclick="document.getElementById('modal-add-agent-{{ $service->id }}').classList.add('hidden')"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                            @if($dispoPourService->isEmpty())
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center">
                                    <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100">
                                        <i class="fas fa-user-slash text-slate-400 text-sm"></i>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-600">Aucun agent disponible</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Tous les agents de la direction sont déjà affectés.</p>
                                    <a href="{{ route('admin.agents.create', ['redirect_to' => route('admin.direction-dga.index')]) }}"
                                       class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-purple-700 transition">
                                        <i class="fas fa-user-plus text-[10px]"></i> Créer un nouvel agent
                                    </a>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <button type="button" onclick="document.getElementById('modal-add-agent-{{ $service->id }}').classList.add('hidden')"
                                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                        Fermer
                                    </button>
                                </div>
                            @else
                                <form method="POST" action="{{ route('admin.direction-dga.services.agents.store', $service) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold text-slate-600">Agent à affecter</label>
                                        <select name="agent_id" required
                                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100" data-no-ts>
                                            <option value="">— Choisir un agent —</option>
                                            @foreach($dispoPourService as $ag)
                                                <option value="{{ $ag->id }}">{{ $ag->prenom }} {{ $ag->nom }} — {{ $ag->role }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="document.getElementById('modal-add-agent-{{ $service->id }}').classList.add('hidden')"
                                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                                class="rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white hover:bg-purple-700">
                                            <i class="fas fa-check mr-1"></i>Affecter
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Modale : créer un service ─────────────────────────────────────── --}}
    <div id="modal-create-service" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl mx-4">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900">Nouveau service</h3>
                <button type="button" onclick="document.getElementById('modal-create-service').classList.add('hidden')"
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.direction-dga.services.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Nom du service <span class="text-rose-500">*</span></label>
                    <input type="text" name="nom" required autofocus
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100"
                           placeholder="Ex : Service Informatique">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Chef de service</label>
                    @if($chefsDisponibles->isNotEmpty())
                        <select name="chef_agent_id"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800 focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-100" data-no-ts>
                            <option value="">— Affecter plus tard —</option>
                            @foreach($chefsDisponibles as $chef)
                                <option value="{{ $chef->id }}">{{ $chef->prenom }} {{ $chef->nom }}{{ $chef->matricule ? ' ('.$chef->matricule.')' : '' }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-center">
                            <p class="text-xs text-slate-400 italic">Aucun Chef de Service disponible.</p>
                            <a href="{{ route('admin.agents.create', ['redirect_to' => route('admin.direction-dga.index')]) }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-purple-600 hover:text-purple-800 transition">
                                <i class="fas fa-user-plus text-[10px]"></i> Créer un agent Chef de Service
                            </a>
                        </div>
                    @endif
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button"
                            onclick="document.getElementById('modal-create-service').classList.add('hidden')"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white hover:bg-purple-700">
                        <i class="fas fa-plus mr-1"></i>Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Collaborateurs directs ─────────────────────────────────────────── --}}
    @if($agentsDirects->isNotEmpty())
    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-black text-slate-900">Collaborateurs directs</h2>
            <p class="text-xs text-slate-400 mt-0.5">Agents rattachés directement à la direction (hors services)</p>
        </div>
        <ul class="divide-y divide-slate-50">
            @foreach($agentsDirects as $agent)
            <li class="flex items-center gap-4 px-5 py-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 font-black text-sm">
                    {{ strtoupper(substr($agent->prenom, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-slate-800 text-sm truncate">{{ $agent->prenom }} {{ $agent->nom }}</p>
                    <p class="text-xs text-slate-400">{{ $agent->role }}</p>
                </div>
                @if($agent->user)
                    <span class="inline-flex items-center gap-1 rounded-full {{ $agent->user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }} px-2.5 py-0.5 text-[11px] font-semibold">
                        <i class="fas fa-circle text-[6px]"></i> Compte {{ $agent->user->is_active ? 'actif' : 'inactif' }}
                    </span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @endif {{-- end if $direction --}}

</div>
</div>
@endsection
