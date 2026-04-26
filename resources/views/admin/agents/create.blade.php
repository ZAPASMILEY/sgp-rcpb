@extends('layouts.app')

@section('title', 'Nouvel agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.agents.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvel agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations personnelles et professionnelles de l'agent. Le rattachement se gère depuis la fiche de la structure concernée.</p>
                    </div>
                    <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agents.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-5">
                    @csrf

                    {{-- ── Identité ── --}}
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="nom" class="text-sm font-semibold text-slate-700">Nom <span class="text-red-500">*</span></label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Nom de famille">
                        </div>
                        <div class="space-y-2">
                            <label for="prenom" class="text-sm font-semibold text-slate-700">Prénom <span class="text-red-500">*</span></label>
                            <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" required class="ent-input" placeholder="Prénom">
                        </div>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                            <select id="sexe" name="sexe" class="ent-select">
                                <option value="">Sélectionner</option>
                                <option value="homme" @selected(old('sexe') === 'homme')>Homme</option>
                                <option value="femme" @selected(old('sexe') === 'femme')>Femme</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="date_debut_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                            <input id="date_debut_fonction" name="date_debut_fonction" type="date" value="{{ old('date_debut_fonction') }}" class="ent-input">
                        </div>
                    </div>

                    {{-- ── Profession ── --}}
                    <div class="space-y-2">
                        <label for="fonction" class="text-sm font-semibold text-slate-700">Fonction <span class="text-red-500">*</span></label>
                        <select id="fonction" name="fonction" required class="ent-select">
                            <option value="">Sélectionner une fonction</option>
                            @foreach (\App\Models\Agent::FONCTIONS as $val => $label)
                                <option value="{{ $val }}" @selected(old('fonction') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">La fonction détermine dans quels selects cet agent apparaîtra lors de la gestion des structures.</p>
                    </div>

                    {{-- ── Contact ── --}}
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="numero_telephone" class="text-sm font-semibold text-slate-700">Numéro de téléphone</label>
                            <input id="numero_telephone" name="numero_telephone" type="text" value="{{ old('numero_telephone') }}" class="ent-input" placeholder="+226 70 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Email professionnel <span class="text-red-500">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="agent@rcpb.bf">
                            <p class="text-xs text-slate-500">Sert d'identifiant unique dans les listes de sélection.</p>
                        </div>
                    </div>

                    {{-- ── Photo ── --}}
                    <fieldset class="rounded-2xl border border-slate-200 bg-slate-50 p-5 space-y-4">
                        <legend class="px-2 text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Photo</legend>
                        <div class="grid gap-5 lg:grid-cols-2">
                            <div class="space-y-2">
                                <label for="photo_import" class="text-sm font-semibold text-slate-700">Importer depuis l'appareil</label>
                                <input id="photo_import" name="photo_import" type="file" accept="image/*"
                                       class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                                <p class="text-xs text-slate-500">Formats image uniquement. Max 3 Mo.</p>
                            </div>
                            <div class="space-y-2">
                                <label for="photo_camera" class="text-sm font-semibold text-slate-700">Prendre avec la caméra</label>
                                <input id="photo_camera" name="photo_camera" type="file" accept="image/*" capture="environment"
                                       class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-amber-400 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-900">
                                <p class="text-xs text-slate-500">Compatible mobile et tablette.</p>
                            </div>
                        </div>
                    </fieldset>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer l'agent
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
