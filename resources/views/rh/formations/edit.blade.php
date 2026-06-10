@extends($layout ?? 'layouts.rh')

@section('title', 'Modifier formation | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="mx-auto max-w-2xl flex flex-col gap-6">

    <header class="admin-panel px-6 py-5">
        <div class="flex items-center gap-3">
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ressources Humaines</p>
                <h1 class="text-xl font-black tracking-tight text-slate-950">Modifier la formation</h1>
                <p class="mt-0.5 text-sm text-slate-500 truncate">{{ $formation->theme }}</p>
            </div>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Veuillez corriger les erreurs :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route(($routePrefix ?? 'rh').'.formations.update', $formation) }}"
          class="admin-panel px-6 py-6 flex flex-col gap-5">
        @csrf @method('PUT')

        {{-- Agent --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Agent *</label>
            <select name="agent_id" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}" @selected(old('agent_id', $formation->agent_id) == $ag->id)>
                        {{ trim($ag->prenom . ' ' . $ag->nom) }} — {{ $ag->poste ?: '—' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Formateur --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">
                Formateur
                <span class="ml-1 font-normal normal-case tracking-normal text-slate-400">(optionnel — agent de la Faitière)</span>
            </label>
            <select name="formateur_id"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <option value="">— Aucun formateur —</option>
                @foreach($formateurs as $fm)
                    <option value="{{ $fm->id }}" @selected(old('formateur_id', $formation->formateur_id) == $fm->id)>
                        {{ trim($fm->prenom . ' ' . $fm->nom) }}{{ $fm->poste ? ' — ' . $fm->poste : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Titre --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Thème *</label>
            <input type="text" name="theme" value="{{ old('theme', $formation->theme) }}" required maxlength="255"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
        </div>

        {{-- Type + Domaine --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- Type --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Type *</label>
                <div class="flex gap-3">
                    @foreach($types as $key => $label)
                        <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border-2 px-4 py-3 transition
                            {{ old('type', $formation->type ?? 'interne') === $key ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                            <input type="radio" name="type" value="{{ $key }}"
                                   {{ old('type', $formation->type ?? 'interne') === $key ? 'checked' : '' }}
                                   class="accent-emerald-600">
                            <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('type')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>
            {{-- Domaine --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Domaine *</label>
                <select name="domaine" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                    @foreach($domaines as $key => $label)
                        <option value="{{ $key }}" @selected(old('domaine', $formation->domaine) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Dates : année fixée, seuls le jour et le mois sont modifiables --}}
        @php
            $annee = $anneeEnCours?->annee ?? $formation->date_debut->year;
            $moisLabels = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
                           '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
            $existingDebut = old('date_debut', $formation->date_debut->format('Y-m-d'));
            $existingFin   = old('date_fin',   $formation->date_fin->format('Y-m-d'));
            $debutJour = (int) substr($existingDebut, 8, 2);
            $debutMois = substr($existingDebut, 5, 2);
            $finJour   = (int) substr($existingFin,   8, 2);
            $finMois   = substr($existingFin,   5, 2);
        @endphp
        <div class="grid grid-cols-2 gap-4">
            {{-- Date de début --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de début *</label>
                <input type="hidden" name="date_debut" id="date_debut_hidden"
                       value="{{ $annee }}-{{ $debutMois }}-{{ str_pad($debutJour, 2, '0', STR_PAD_LEFT) }}">
                <div class="flex items-stretch gap-1.5">
                    <input type="number" id="date_debut_day" min="1" max="31"
                           value="{{ $debutJour }}"
                           placeholder="Jour" required
                           class="w-20 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-center outline-none focus:border-emerald-400 focus:bg-white">
                    <select id="date_debut_month"
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                        <option value="">Mois</option>
                        @foreach($moisLabels as $num => $nom)
                            <option value="{{ $num }}" @selected($debutMois === $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                    <span class="flex items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-500 select-none">
                        {{ $annee }}
                    </span>
                </div>
            </div>
            {{-- Date de fin --}}
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de fin *</label>
                <input type="hidden" name="date_fin" id="date_fin_hidden"
                       value="{{ $annee }}-{{ $finMois }}-{{ str_pad($finJour, 2, '0', STR_PAD_LEFT) }}">
                <div class="flex items-stretch gap-1.5">
                    <input type="number" id="date_fin_day" min="1" max="31"
                           value="{{ $finJour }}"
                           placeholder="Jour" required
                           class="w-20 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-center outline-none focus:border-emerald-400 focus:bg-white">
                    <select id="date_fin_month"
                            class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                        <option value="">Mois</option>
                        @foreach($moisLabels as $num => $nom)
                            <option value="{{ $num }}" @selected($finMois === $num)>{{ $nom }}</option>
                        @endforeach
                    </select>
                    <span class="flex items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm font-black text-slate-500 select-none">
                        {{ $annee }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Durée --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Durée (heures) *</label>
            <div class="relative">
                <input type="number" name="duree_heures"
                       value="{{ old('duree_heures', $formation->duree_heures) }}" required min="1" max="9999"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-14 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">heures</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-save text-xs"></i> Enregistrer
            </button>
        </div>
    </form>

</div>
</div>
@endsection

@push('scripts')
<script>
    // ── Combinaison jour + mois + année (fixe) → champs hidden ──────────────
    const ANNEE = {{ $annee }};

    function buildDate(dayInput, monthSelect) {
        const day   = String(dayInput.value).padStart(2, '0');
        const month = monthSelect.value;
        if (!dayInput.value || !month) return '';
        return `${ANNEE}-${month}-${day}`;
    }

    function syncDebut() {
        document.getElementById('date_debut_hidden').value =
            buildDate(document.getElementById('date_debut_day'),
                      document.getElementById('date_debut_month'));
    }

    function syncFin() {
        document.getElementById('date_fin_hidden').value =
            buildDate(document.getElementById('date_fin_day'),
                      document.getElementById('date_fin_month'));
    }

    function clampDay(input) {
        const v = parseInt(input.value, 10);
        if (!isNaN(v)) input.value = Math.min(31, Math.max(1, v));
    }

    document.getElementById('date_debut_day').addEventListener('input', function() { clampDay(this); syncDebut(); });
    document.getElementById('date_debut_month').addEventListener('change', syncDebut);
    document.getElementById('date_fin_day').addEventListener('input', function() { clampDay(this); syncFin(); });
    document.getElementById('date_fin_month').addEventListener('change', syncFin);

    // Initialisation au chargement (pré-remplissage des valeurs existantes)
    syncDebut();
    syncFin();

    // Sync de sécurité juste avant la soumission
    document.querySelector('form').addEventListener('submit', function () {
        syncDebut();
        syncFin();
    });
</script>
@endpush
