@extends('layouts.app')

@section('title', 'Affecter un agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.services.show', $service) }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i> Retour au service
    </a>
</div>

<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-lg bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-y-auto max-h-[95vh] animate-in zoom-in duration-300">

        <a href="{{ route('admin.services.show', $service) }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>

        {{-- Gradient header — mêmes couleurs que caisses/services.blade.php --}}
        <div style="background: linear-gradient(to right, #22d3ee, #3b82f6)" class="p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center font-black shadow-sm" style="color:#22d3ee">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Affecter un agent</h1>
                <p class="text-white/80 text-xs font-bold uppercase mt-1 tracking-wider">{{ $service->nom }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.services.attach-agent', $service) }}" class="p-8 lg:p-12 space-y-8">
            @csrf

            @if(session('error'))
                <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700">
                    <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Agent --}}
            <div class="space-y-4">
                <label for="agent_id" class="text-xs font-bold text-slate-400 ml-1">
                    Agent à affecter
                    @if($agentsLibres->isNotEmpty())
                        <span class="font-normal">({{ $agentsLibres->count() }} disponible(s))</span>
                    @endif
                </label>

                @if($agentsLibres->isEmpty())
                    <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent libre disponible actuellement.</span>
                    </div>
                @endif

                <select id="agent_id" name="agent_id" required
                    class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">— Sélectionner un agent —</option>
                    @foreach($agentsLibres as $a)
                        <option value="{{ $a->id }}" @selected(old('agent_id') == $a->id)>
                            {{ $a->nom }} {{ $a->prenom }}{{ $a->matricule ? ' — '.$a->matricule : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Poste auto-rempli --}}
            <div class="space-y-4">
                <label for="poste" class="text-xs font-bold text-slate-400 ml-1">Fonction occupée <span class="text-rose-500">*</span></label>
                <input id="poste" name="poste" type="text" value="{{ old('poste') }}" required
                    class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 placeholder-slate-300"
                    placeholder="Ex: Agent de crédit">
                @error('poste')<p class="text-xs text-rose-500 font-bold ml-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" @disabled($agentsLibres->isEmpty())
                    class="w-full sm:flex-[2] py-5 text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100"
                    style="background: linear-gradient(to right, #22d3ee, #3b82f6)">
                    Valider l'affectation
                </button>
                <a href="{{ route('admin.services.show', $service) }}"
                    class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-in { animation: zoomIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>
@endsection

@push('head')
<style>
    .ts-wrapper .ts-control { background: rgb(241 245 249); border: none; border-radius: 20px; padding: 1rem; font-weight: 700; color: #334155; box-shadow: none; }
    .ts-wrapper.focus .ts-control { box-shadow: 0 0 0 2px #22d3ee; }
    .ts-dropdown { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .ts-dropdown .option { padding: 10px 16px; font-weight: 600; font-size: 14px; }
    .ts-dropdown .option:hover, .ts-dropdown .option.active { background: #ecfeff; color: #0e7490; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Map agent id → poste (généré côté serveur, 100% fiable)
    var posteMap = {
        @foreach($agentsLibres as $a)
            "{{ $a->id }}": "{{ addslashes($a->poste ?? '') }}",
        @endforeach
    };

    var posteInput = document.getElementById('poste');

    new TomSelect('#agent_id', {
        placeholder: 'Rechercher un agent...',
        allowEmptyOption: true,
        dropdownParent: 'body',
        onChange: function (val) {
            var agentPoste = posteMap[val] || '';
            if (agentPoste) {
                posteInput.value    = agentPoste;
                posteInput.readOnly = true;
                posteInput.style.background = 'rgb(226 232 240)';
                posteInput.style.cursor     = 'not-allowed';
                posteInput.style.color      = '#64748b';
            } else {
                if (posteInput.readOnly) posteInput.value = '';
                posteInput.readOnly         = false;
                posteInput.style.background = '';
                posteInput.style.cursor     = '';
                posteInput.style.color      = '';
            }
        },
    });
});
</script>
@endpush
