@extends('layouts.app')

@section('title', 'Modifier guichet | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">
        <a href="{{ route('admin.guichets.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
            <i class="fas fa-arrow-left text-xs"></i> Retour aux guichets
        </a>

        <div class="rounded-2xl bg-white p-6 shadow-sm lg:p-8">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Modification</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $guichet->nom }}</h1>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.guichets.update', $guichet) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom du guichet <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" value="{{ old('nom', $guichet->nom) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone d'accueil <span class="text-rose-500">*</span></label>
                        <input type="text" name="telephone_accueil" value="{{ old('telephone_accueil', $guichet->telephone_accueil) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="+226 25 00 00 00">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Agence <span class="text-rose-500">*</span></label>
                    <select id="agence_id" name="agence_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">-- Choisir --</option>
                        @foreach ($agences as $agence)
                            <option value="{{ $agence->id }}" @selected(old('agence_id', $guichet->agence_id) == $agence->id)>
                                {{ $agence->nom }}
                                @if ($agence->delegationTechnique)
                                    — {{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Chef de guichet</label>
                    <select id="chef_agent_id" name="chef_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">— Aucun chef pour l'instant —</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Agents disponibles : Chef de Guichet et agents simples de l'agence sélectionnée.</p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-check text-xs text-emerald-300"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.guichets.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var agents    = @json($chefs->map(fn($a) => ['id' => $a->id, 'nom' => $a->nom, 'prenom' => $a->prenom, 'role' => $a->role, 'agence_id' => $a->agence_id]));
    var selAgence = document.getElementById('agence_id');
    var selChef   = document.getElementById('chef_agent_id');
    var currentChefId = '{{ old('chef_agent_id', $guichet->chef_agent_id) }}';

    function filtrerChefs(agenceId) {
        selChef.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = '— Aucun chef pour l\'instant —';
        selChef.appendChild(opt0);

        if (!agenceId) return;

        var eligibles = agents.filter(function(a) {
            if (a.role === 'Chef de Guichet') return String(a.agence_id) === String(agenceId) || !a.agence_id;
            if (a.role === 'Agent')           return String(a.agence_id) === String(agenceId);
            return false;
        });

        eligibles.forEach(function(a) {
            var opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.nom + ' ' + a.prenom + (a.role !== 'Chef de Guichet' ? ' (Agent)' : ' (Chef de Guichet)');
            if (String(a.id) === String(currentChefId)) opt.selected = true;
            selChef.appendChild(opt);
        });
    }

    selAgence.addEventListener('change', function () { filtrerChefs(this.value); });
    filtrerChefs(selAgence.value);
})();
</script>
@endpush
