@extends('layouts.app')

@section('title', 'Modifier Délégation Technique | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Modifier — '.$delegationTechnique->region)

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-4xl space-y-6">

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Modifier la Délégation</h1>
                    <p class="mt-1 text-sm text-slate-400">{{ $delegationTechnique->region }} — {{ $delegationTechnique->ville }}</p>
                </div>
                <a href="{{ route('admin.delegations-techniques.show', $delegationTechnique) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
            </div>
        </div>

        {{-- Main form --}}
        <form method="POST" action="{{ route('admin.delegations-techniques.update', $delegationTechnique) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Infos de base --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-map-marker-alt text-cyan-500"></i>
                    Informations générales
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Région <span class="text-rose-500">*</span></label>
                        <input type="text" name="region" value="{{ old('region', $delegationTechnique->region) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville (siège) <span class="text-rose-500">*</span></label>
                        <input type="text" name="ville" value="{{ old('ville', $delegationTechnique->ville) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                        <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone', $delegationTechnique->secretariat_telephone) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                </div>
            </section>

            {{-- Directeur Régional --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-user-tie text-sky-500"></i>
                    Directeur Régional
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom</label>
                        <input type="text" name="directeur_prenom" value="{{ old('directeur_prenom', $delegationTechnique->directeur_prenom) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom</label>
                        <input type="text" name="directeur_nom" value="{{ old('directeur_nom', $delegationTechnique->directeur_nom) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe</label>
                        <select name="directeur_sexe" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" {{ old('directeur_sexe', $delegationTechnique->directeur_sexe) === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                            <option value="Feminin" {{ old('directeur_sexe', $delegationTechnique->directeur_sexe) === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                        <input type="email" name="directeur_email" value="{{ old('directeur_email', $delegationTechnique->directeur_email) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                        <input type="text" name="directeur_telephone" value="{{ old('directeur_telephone', $delegationTechnique->directeur_telephone) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois)</label>
                        <input type="month" name="directeur_date_debut_mois" value="{{ old('directeur_date_debut_mois', $delegationTechnique->directeur_date_debut_mois) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Photo du directeur</label>
                        @if ($delegationTechnique->directeur_photo_path)
                            <div class="mb-3 flex items-center gap-4">
                                <img src="{{ asset('storage/'.$delegationTechnique->directeur_photo_path) }}" alt="Photo directeur" class="h-16 w-16 rounded-full object-cover shadow-md ring-2 ring-white">
                                <span class="text-xs text-slate-400">Photo actuelle</span>
                            </div>
                        @endif
                        <input type="file" name="directeur_photo" accept="image/*" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-cyan-700 hover:file:bg-cyan-100">
                    </div>
                </div>
            </section>

            {{-- Secrétaire --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-user-pen text-fuchsia-500"></i>
                    Secrétaire
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom</label>
                        <input type="text" name="secretaire_prenom" value="{{ old('secretaire_prenom', $delegationTechnique->secretaire_prenom) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom</label>
                        <input type="text" name="secretaire_nom" value="{{ old('secretaire_nom', $delegationTechnique->secretaire_nom) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe</label>
                        <select name="secretaire_sexe" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                            <option value="">-- Choisir --</option>
                            <option value="Masculin" {{ old('secretaire_sexe', $delegationTechnique->secretaire_sexe) === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                            <option value="Feminin" {{ old('secretaire_sexe', $delegationTechnique->secretaire_sexe) === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                        <input type="email" name="secretaire_email" value="{{ old('secretaire_email', $delegationTechnique->secretaire_email) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                        <input type="text" name="secretaire_telephone" value="{{ old('secretaire_telephone', $delegationTechnique->secretaire_telephone) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois)</label>
                        <input type="month" name="secretaire_date_debut_mois" value="{{ old('secretaire_date_debut_mois', $delegationTechnique->secretaire_date_debut_mois) }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                </div>
            </section>

            {{-- Villes couvertes --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-city text-violet-500"></i>
                    Villes couvertes
                </h2>
                <p class="mb-4 text-xs text-slate-400">Ajoutez les villes que cette délégation couvre. Une ville ne peut appartenir qu'à une seule délégation.</p>

                <div id="villes-container" class="space-y-2">
                    @foreach ($delegationTechnique->villes as $index => $ville)
                        <div class="ville-row flex items-center gap-3">
                            <input type="hidden" name="villes[{{ $index }}][id]" value="{{ $ville->id }}">
                            <input type="text" name="villes[{{ $index }}][nom]" value="{{ old("villes.{$index}.nom", $ville->nom) }}" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400" placeholder="Nom de la ville">
                            <button type="button" onclick="this.closest('.ville-row').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-ville-btn" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-violet-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-violet-600">
                    <i class="fas fa-plus text-[10px]"></i> Ajouter une ville
                </button>
            </section>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-cyan-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-700">
                    <i class="fas fa-check"></i> Enregistrer
                </button>
                <a href="{{ route('admin.delegations-techniques.show', $delegationTechnique) }}" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('villes-container');
    var btn = document.getElementById('add-ville-btn');
    var index = container.querySelectorAll('.ville-row').length;

    btn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'ville-row flex items-center gap-3';
        row.innerHTML =
            '<input type="text" name="villes[' + index + '][nom]" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400" placeholder="Nom de la ville">' +
            '<button type="button" onclick="this.closest(\'.ville-row\').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('input').focus();
        index++;
    });
});
</script>
@endpush