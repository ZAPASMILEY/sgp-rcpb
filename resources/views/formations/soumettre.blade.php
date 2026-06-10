@extends($layout ?? 'layouts.personnel')
@section('title', 'Soumettre une formation | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-20 -top-20 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-emerald-300">Mes formations</p>
                <h1 class="mt-1 text-2xl font-black leading-tight text-white">Soumettre une formation</h1>
                <p class="mt-0.5 text-sm text-emerald-100/80">
                    Pour <span class="font-bold text-white">{{ $agent ? trim($agent->prenom.' '.$agent->nom) : auth()->user()->name }}</span>
                    · Année <span class="font-bold text-white">{{ $anneeEnCours?->annee ?? now()->year }}</span>
                </p>
            </div>
            <a href="{{ url()->previous() }}"
               class="inline-flex shrink-0 items-center gap-2 self-start rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="mx-auto max-w-2xl px-4 pt-8 lg:px-0">

        @if ($errors->any())
            <div class="mb-5 flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation shrink-0"></i> {{ $errors->first() }}
            </div>
        @endif

        {{-- Info workflow --}}
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <i class="fas fa-circle-info mt-0.5 shrink-0 text-amber-500"></i>
            <p>Votre formation sera soumise au <strong>RH pour validation</strong>. Elle n'apparaîtra dans votre dossier et vos évaluations qu'après approbation.</p>
        </div>

        <form method="POST" action="{{ route('formation.store') }}" enctype="multipart/form-data"
              class="flex flex-col gap-5">
            @csrf

            {{-- Thème --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <p class="text-sm font-black text-slate-900">Intitulé de la formation</p>
                </div>
                <div class="px-6 py-5">
                    <input type="text" name="theme" value="{{ old('theme') }}"
                           placeholder="Ex : Gestion des risques opérationnels…"
                           list="themes-suggestions"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white @error('theme') border-rose-300 bg-rose-50 @enderror">
                    <datalist id="themes-suggestions">
                        @foreach($themesExistants as $t)
                            <option value="{{ $t }}">
                        @endforeach
                    </datalist>
                    @error('theme')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Type + Domaine --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <p class="text-sm font-black text-slate-900">Catégorisation</p>
                </div>
                <div class="grid gap-5 px-6 py-5 sm:grid-cols-2">
                    {{-- Type --}}
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Type</label>
                        <div class="flex gap-3">
                            @foreach($types as $key => $label)
                                <label class="flex flex-1 cursor-pointer items-center gap-2.5 rounded-xl border-2 px-4 py-3 transition
                                    {{ old('type', 'interne') === $key ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                                    <input type="radio" name="type" value="{{ $key }}"
                                           {{ old('type', 'interne') === $key ? 'checked' : '' }}
                                           class="accent-emerald-600">
                                    <span class="text-sm font-bold text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Domaine --}}
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Domaine</label>
                        <select name="domaine"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white @error('domaine') border-rose-300 @enderror">
                            <option value="">Choisir un domaine…</option>
                            @foreach($domaines as $key => $label)
                                <option value="{{ $key }}" @selected(old('domaine') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('domaine')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Dates + Durée --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <p class="text-sm font-black text-slate-900">Période &amp; durée</p>
                </div>
                <div class="grid gap-5 px-6 py-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Date de début</label>
                        <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                               min="{{ ($anneeEnCours?->annee ?? now()->year).'-01-01' }}"
                               max="{{ ($anneeEnCours?->annee ?? now()->year).'-12-31' }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white @error('date_debut') border-rose-300 @enderror">
                        @error('date_debut')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Date de fin</label>
                        <input type="date" name="date_fin" value="{{ old('date_fin') }}"
                               min="{{ ($anneeEnCours?->annee ?? now()->year).'-01-01' }}"
                               max="{{ ($anneeEnCours?->annee ?? now()->year).'-12-31' }}"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white @error('date_fin') border-rose-300 @enderror">
                        @error('date_fin')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">Durée (heures)</label>
                        <input type="number" name="duree_heures" value="{{ old('duree_heures') }}"
                               min="1" max="9999" placeholder="Ex : 8"
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-400 focus:bg-white @error('duree_heures') border-rose-300 @enderror">
                        @error('duree_heures')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Attestation --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <p class="text-sm font-black text-slate-900">Attestation <span class="text-rose-500">*</span></p>
                    <p class="text-xs text-slate-500">PDF ou image (JPG, PNG, WEBP) — 5 Mo max</p>
                </div>
                <div class="px-6 py-5">
                    <label id="drop-zone"
                           class="flex cursor-pointer flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center transition hover:border-emerald-400 hover:bg-emerald-50 @error('attestation') border-rose-300 bg-rose-50 @enderror">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm">
                            <i class="fas fa-cloud-arrow-up text-2xl text-emerald-500"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700">Cliquer pour choisir un fichier</p>
                            <p class="mt-0.5 text-xs text-slate-400">ou glisser-déposer ici</p>
                        </div>
                        <input id="attestation-input" type="file" name="attestation"
                               accept=".pdf,.jpg,.jpeg,.png,.webp"
                               class="hidden">
                    </label>
                    <p id="file-name" class="mt-2 hidden text-center text-xs font-semibold text-emerald-700"></p>
                    @error('attestation')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3 pb-4">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-paper-plane text-xs"></i> Soumettre pour validation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const input    = document.getElementById('attestation-input');
    const label    = document.getElementById('drop-zone');
    const nameEl   = document.getElementById('file-name');

    input.addEventListener('change', () => {
        if (input.files.length) {
            nameEl.textContent = '📎 ' + input.files[0].name;
            nameEl.classList.remove('hidden');
            label.classList.add('border-emerald-500', 'bg-emerald-50');
            label.classList.remove('border-dashed', 'border-slate-300');
        }
    });

    // Highlight on drag
    ['dragenter','dragover'].forEach(e => label.addEventListener(e, ev => {
        ev.preventDefault();
        label.classList.add('border-emerald-500', 'bg-emerald-50');
    }));
    ['dragleave','drop'].forEach(e => label.addEventListener(e, () => {
        if (!input.files.length) label.classList.remove('border-emerald-500', 'bg-emerald-50');
    }));
</script>
@endsection
