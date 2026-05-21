@extends('layouts.app')

@section('title', 'Affecter un agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.agences.agents.index', $agence) }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour aux agents de l'agence</span>
        </a>
    </div>

    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Affectation d'agent</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Agence / {{ $agence->nom }}</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Affecter un agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Sélectionnez un agent (Fonction: Agent) à rattacher à cette agence.</p>
                    </div>
                    <a href="{{ route('admin.agences.agents.index', $agence) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($agents->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Aucun personnel avec la fonction <strong>"Agent"</strong> n'est disponible pour cette affectation.
                        <a href="{{ route('admin.agents.create') }}" class="ml-2 font-bold underline">Créer un nouvel agent</a>
                    </div>
                @endif

<form method="POST" action="{{ route('admin.agences.agents.store', $agence) }}" class="mt-6">
    @csrf

    <div class="space-y-4">
        <label for="agent_id" class="text-sm font-semibold text-slate-700 flex items-center justify-between">
            <span>Agent à affecter <span class="text-red-500">*</span></span>
            <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded-full text-slate-500">{{ $agents->count() }} agents éligibles</span>
        </label>

        <div class="relative overflow-visible">
            <select id="agent_id" name="agent_id" required
                    class="w-full rounded-xl border-slate-200 bg-slate-50 p-4 text-sm font-bold text-slate-700 focus:ring-4 focus:ring-cyan-500/10 transition-all appearance-none outline-none"
                    style="min-height: 55px;"
                    @disabled($agents->isEmpty())>
                <option value="" data-poste="">— Sélectionner un agent libre —</option>
                @foreach ($agents as $a)
                    <option value="{{ $a->id }}" data-poste="{{ $a->poste ?? '' }}" @selected(old('agent_id') == $a->id)>
                        {{ strtoupper($a->nom) }} {{ $a->prenom }}
                        @if ($a->matricule) · [{{ $a->matricule }}] @endif
                    </option>
                @endforeach
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                <i class="fas fa-chevron-down text-xs"></i>
            </div>
        </div>

        <p class="text-[11px] text-slate-500 italic flex items-center gap-2">
            <i class="fas fa-info-circle text-cyan-500"></i>
            Seuls les profils avec la fonction exacte "Agent" et sans affectation sont listés.
        </p>
    </div>

    <div class="mt-4 space-y-2">
        <label for="poste" class="text-sm font-semibold text-slate-700">Fonction <span class="text-red-500">*</span></label>
        <input id="poste" name="poste" type="text"
               value="{{ old('poste') }}"
               list="postes-list"
               required
               class="ent-input w-full"
               placeholder="Ex : Caissier, Chargé de crédit, Développeur…">
        <datalist id="postes-list">
            @foreach ($postes as $libelle)
                <option value="{{ $libelle }}">
            @endforeach
        </datalist>
        <p id="poste-hint" class="text-xs text-slate-500">Saisissez ou choisissez parmi les fonctions existantes.</p>
    </div>

    <div class="mt-10">
        <button type="submit" @disabled($agents->isEmpty())
                class="w-full flex items-center justify-center gap-2 rounded-2xl bg-slate-950 py-4 text-sm font-black text-white shadow-xl shadow-slate-200 transition-all hover:bg-cyan-700 active:scale-[0.98] disabled:opacity-50">
            <i class="fas fa-link"></i>
            Confirmer l'affectation à l'agence
        </button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var sel  = document.getElementById('agent_id');
    var inp  = document.getElementById('poste');
    var hint = document.getElementById('poste-hint');

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
            </section>
        </div>
    </main>
@endsection