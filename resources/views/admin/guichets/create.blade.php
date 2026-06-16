@extends('layouts.app')

@section('title', 'Nouveau guichet | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window h-full w-full p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouveau guichet</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez le nom du guichet, les coordonnees du chef et l'agence d'appartenance.</p>
                    </div>
                    <a href="{{ route('admin.guichets.index') }}" target="_top" class="ent-btn ent-btn-soft">Index guichets</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.guichets.store') }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="nom" class="text-sm font-semibold text-slate-700">Nom du guichet <span class="text-rose-500">*</span></label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Guichet principal">
                        </div>
                        <div class="space-y-2">
                            <label for="telephone_accueil" class="text-sm font-semibold text-slate-700">Téléphone d'accueil <span class="text-rose-500">*</span></label>
                            <input id="telephone_accueil" name="telephone_accueil" type="text" value="{{ old('telephone_accueil') }}" required class="ent-input" placeholder="+226 25 00 00 00">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="agence_id" class="text-sm font-semibold text-slate-700">Agence d'appartenance <span class="text-rose-500">*</span></label>
                        <select id="agence_id" name="agence_id" required class="ent-select">
                            <option value="">Selectionner une agence</option>
                            @foreach ($agences as $agence)
                                <option value="{{ $agence->id }}" @selected((string) old('agence_id') === (string) $agence->id)>
                                    {{ $agence->nom }}
                                    @if ($agence->delegationTechnique)
                                        - {{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="chef_agent_id" class="text-sm font-semibold text-slate-700">Chef de guichet</label>
                        <select id="chef_agent_id" name="chef_agent_id" class="ent-select">
                            <option value="">— Choisir d'abord une agence —</option>
                        </select>
                        <p class="text-xs text-slate-500">Agents disponibles : Chef de Guichet et agents simples de l'agence sélectionnée.</p>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer le guichet
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
<script>
(function () {
    var agents     = @json($chefs->map(fn($a) => ['id' => $a->id, 'nom' => $a->nom, 'prenom' => $a->prenom, 'role' => $a->role, 'agence_id' => $a->agence_id]));
    var selAgence  = document.getElementById('agence_id');
    var selChef    = document.getElementById('chef_agent_id');
    var oldChefId  = '{{ old('chef_agent_id') }}';

    function filtrerChefs(agenceId) {
        selChef.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = agenceId ? '— Aucun chef pour l\'instant —' : '— Choisir d\'abord une agence —';
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
            if (String(a.id) === String(oldChefId)) opt.selected = true;
            selChef.appendChild(opt);
        });
    }

    selAgence.addEventListener('change', function () { filtrerChefs(this.value); });
    filtrerChefs(selAgence.value);
})();
</script>
@endpush
