@extends('layouts.app')

@section('title', $service->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-msg')?.remove(), 3000);</script>
        @endif

        {{-- En-tête --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ $service->nom }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Service
                        @if($service->direction)
                            · {{ $service->direction->nom }}
                        @elseif($service->delegationTechnique ?? null)
                            · {{ $service->delegationTechnique->region }} / {{ $service->delegationTechnique->ville }}
                        @elseif($service->caisse ?? null)
                            · {{ $service->caisse->nom }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.services.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
                <a href="{{ route('admin.services.edit', $service) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-pen text-xs"></i> Modifier
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Chef de service --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-black uppercase tracking-wider text-slate-400">Chef de service</h2>
                @if($service->chef)
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-lg font-black text-cyan-700">
                            {{ strtoupper(substr($service->chef->prenom, 0, 1)) }}{{ strtoupper(substr($service->chef->nom, 0, 1)) }}
                        </div>
                        <div class="space-y-1">
                            <p class="font-black text-slate-900">{{ $service->chef->prenom }} {{ $service->chef->nom }}</p>
                            <p class="text-xs text-slate-500">{{ $service->chef->role }}</p>
                            @if($service->chef->email)
                                <p class="text-xs text-slate-400"><i class="fas fa-envelope w-3.5 mr-1"></i>{{ $service->chef->email }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                        <p class="text-xs text-slate-400">Aucun chef de service désigné.</p>
                    </div>
                @endif
            </div>

            {{-- Rattachement --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-black uppercase tracking-wider text-slate-400">Rattachement</h2>
                <dl class="space-y-3 text-sm">
                    @if($service->direction)
                        <div class="flex items-start gap-2">
                            <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Direction</dt>
                            <dd class="font-semibold text-slate-700">{{ $service->direction->nom }}</dd>
                        </div>
                    @elseif($service->caisse ?? null)
                        <div class="flex items-start gap-2">
                            <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Caisse</dt>
                            <dd class="font-semibold text-slate-700">{{ $service->caisse->nom }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- NOUVELLE SECTION : LISTE DES AGENTS --}}
      
  
    
</div>
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5 flex items-center justify-between bg-white">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">Agents affectés ({{ $service->agents->count() }})</h2>
                    <button onclick="document.getElementById('modal-affecter').classList.remove('hidden')" 
            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-cyan-700">
        <i class="fas fa-user-plus text-[10px]"></i>Affecter un agent
    </button>
                </div>
            </div>
            
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Agent</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Matricule</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Rôle</th>
                            <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($service->agents as $agent)
                            <tr class="group hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 border border-white shadow-sm">
                                            {{ strtoupper(substr($agent->prenom, 0, 1)) }}{{ strtoupper(substr($agent->nom, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $agent->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-500">
                                    <span class="px-2 py-1 bg-slate-100 rounded-md ring-1 ring-slate-200/50">{{ $agent->matricule ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 font-medium">
                                    {{ $agent->role }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.agents.show', $agent) }}" 
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 transition hover:text-cyan-600 hover:border-cyan-100 hover:bg-cyan-50">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <i class="fas fa-user-slash text-slate-200 text-3xl"></i>
                                        <p class="text-xs font-medium text-slate-400">Aucun agent trouvé dans ce service.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
{{-- Modal d'affectation - FILTRE STRICT FONCTION AGENT --}}
<div id="modal-affecter" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" onclick="this.parentElement.parentElement.classList.add('hidden')"></div>
        
        <div class="relative w-full max-w-2xl transform overflow-hidden rounded-[32px] bg-white shadow-2xl transition-all">
            
            <div class="border-b border-slate-100 bg-white px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                            <i class="fas fa-user-check text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Affectation d'Agent</h3>
                            <p class="text-xs font-bold text-slate-400">Uniquement les personnels avec la fonction "Agent"</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('modal-affecter').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.services.attach-agent', $service) }}" method="POST" class="p-8">
                @csrf
                
                <div class="space-y-6">
                    @php
                        $agentsLibres = \App\Models\Agent::query()
                            ->where('role', 'Agent')
                            ->whereNull('service_id')
                            ->whereNull('direction_id')
                            ->whereNull('caisse_id')
                            ->whereNull('agence_id')
                            ->whereNull('delegation_technique_id')
                            ->whereNull('guichet_id')
                            ->whereNull('entite_id')
                            ->orderBy('nom')
                            ->get(['id', 'nom', 'prenom', 'matricule', 'poste']);
                    @endphp

                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-3">
                            Choisir l'un des {{ $agentsLibres->count() }} agents non affectés
                        </label>
                        
                        <div class="relative">
                            <i class="fas fa-user-shield absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select id="modal-agent-id" name="agent_id" required
                                    class="w-full rounded-2xl border-slate-200 bg-slate-50 py-4 pl-12 pr-10 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-cyan-500/10 appearance-none transition-all">
                                <option value="" data-poste="">-- Sélectionner l'agent --</option>
                                @foreach($agentsLibres as $a)
                                    <option value="{{ $a->id }}" data-poste="{{ $a->poste ?? '' }}">
                                        {{ strtoupper($a->nom) }} {{ $a->prenom }}
                                        @if($a->matricule) · [{{ $a->matricule }}] @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                        @if($agentsLibres->isEmpty())
                            <div class="mt-4 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center">
                                <p class="text-[11px] font-bold text-slate-500 italic">
                                    Aucun personnel avec la fonction "Agent" n'est libre actuellement.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="modal-poste" class="block text-[11px] font-black uppercase tracking-widest text-slate-400 mb-3">Fonction <span class="text-red-500">*</span></label>
                        <input id="modal-poste" name="poste" type="text"
                               list="postes-service-list"
                               required
                               class="w-full rounded-2xl border-slate-200 bg-slate-50 py-4 px-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-cyan-500/10 transition-all"
                               placeholder="Ex : Caissier, Chargé de crédit, Développeur…">
                        <datalist id="postes-service-list">
                            @foreach ($postes as $libelle)
                                <option value="{{ $libelle }}">
                            @endforeach
                        </datalist>
                        <p id="modal-poste-hint" class="mt-1 text-[10px] text-slate-400">Saisissez ou choisissez parmi les fonctions existantes.</p>
                    </div>
                </div>

                <div class="mt-10 flex flex-col gap-3 sm:flex-row-reverse">
                    <button type="submit" class="flex-1 rounded-2xl bg-slate-900 py-4 text-sm font-black text-white hover:bg-cyan-700 shadow-xl shadow-slate-200 transition-all active:scale-95">
                        Valider l'affectation
                    </button>
                    <button type="button" onclick="document.getElementById('modal-affecter').classList.add('hidden')" class="flex-1 rounded-2xl border border-slate-200 bg-white py-4 text-sm font-black text-slate-500 hover:bg-slate-50">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
            </form>
        </div>
    </div>
</div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var sel  = document.getElementById('modal-agent-id');
    var inp  = document.getElementById('modal-poste');
    var hint = document.getElementById('modal-poste-hint');

    function syncPoste() {
        if (!sel || !inp) return;
        var opt = sel.options[sel.selectedIndex];
        var agentPoste = opt ? (opt.getAttribute('data-poste') || '') : '';
        if (agentPoste) {
            inp.value    = agentPoste;
            inp.readOnly = true;
            inp.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            if (hint) hint.textContent = 'Fonction issue du profil de l\'agent (non modifiable ici).';
        } else {
            if (inp.readOnly) inp.value = '';
            inp.readOnly = false;
            inp.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            if (hint) hint.textContent = 'Saisissez ou choisissez parmi les fonctions existantes.';
        }
    }

    if (sel) sel.addEventListener('change', syncPoste);
    syncPoste();
})();
</script>
@endpush

@endsection