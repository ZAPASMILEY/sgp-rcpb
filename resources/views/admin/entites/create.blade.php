@extends('layouts.app')

@section('title', 'Configurer la faitiere | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>
        </div>
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Etape 1 sur 2</span>
                </div>

                {{-- Indicateur d'étapes --}}
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">1</span>
                        <span class="text-sm font-semibold text-cyan-700">Faitiere & PCA</span>
                    </div>
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-500">2</span>
                        <span class="text-sm font-medium text-slate-400">Direction Generale</span>
                    </div>
                </div>

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Configuration du siege</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Configurer la faitiere</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez la localisation du siege et les informations du PCA.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.entites.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5">
                    @csrf

                    {{-- Localisation --}}
                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Localisation du siege</p>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="ville" class="text-sm font-semibold text-slate-700">Ville</label>
                                <input id="ville" name="ville" type="text" value="{{ old('ville') }}" required class="ent-input" placeholder="Ex: Ouagadougou">
                            </div>
                            <div class="space-y-2">
                                <label for="region" class="text-sm font-semibold text-slate-700">Region</label>
                                <input id="region" name="region" type="text" value="{{ old('region') }}" required class="ent-input" placeholder="Ex: Centre">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                            <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="ent-input" placeholder="Ex: +226 25 00 00 00">
                        </div>
                    </div>

                    {{-- PCA --}}
                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">President du Conseil d'Administration (PCA)</p>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="pca_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="pca_prenom" name="pca_prenom" type="text" value="{{ old('pca_prenom') }}" required class="ent-input" placeholder="Prenom">
                            </div>
                            <div class="space-y-2">
                                <label for="pca_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="pca_nom" name="pca_nom" type="text" value="{{ old('pca_nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="pca_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="pca_email" name="pca_email" type="email" value="{{ old('pca_email') }}" required class="ent-input" placeholder="pca@rcpb.bf">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="pca_sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                                <select id="pca_sexe" name="pca_sexe" required class="ent-input">
                                    <option value="">Choisir</option>
                                    <option value="Homme" @selected(old('pca_sexe') === 'Homme')>Homme</option>
                                    <option value="Femme" @selected(old('pca_sexe') === 'Femme')>Femme</option>
                                    <option value="Autres" @selected(old('pca_sexe') === 'Autres')>Autres</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="pca_date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                <input id="pca_date_prise_fonction" name="pca_date_prise_fonction" type="month" value="{{ old('pca_date_prise_fonction') }}" required class="ent-input">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="pca_photo" class="text-sm font-semibold text-slate-700">Photo <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <input id="pca_photo" name="pca_photo" type="file" accept="image/*" class="ent-input">
                        </div>
                    </div>

                    {{-- Info comptes --}}
                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm text-cyan-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Un compte de connexion sera genere automatiquement et envoye par email au PCA. Le DG, DGA et Assistante seront configures a l'etape suivante.
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Continuer vers la Direction Generale
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
