@extends('layouts.app')

@section('title', 'Ajouter un conseiller | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mb-4">
            <a href="{{ route('admin.direction-generale.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Retour à la Direction Générale</span>
            </a>
        </div>

        <div class="mx-auto max-w-2xl">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Direction Générale — {{ $entite->ville ?? '' }}</span>
                </div>

                <div class="flex items-start gap-4 mt-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 text-lg font-black shrink-0">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Direction Générale</p>
                        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">Ajouter un conseiller du DG</h1>
                        <p class="mt-1 text-sm text-slate-500">Conseiller rattaché directement au Directeur Général.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.direction-generale.conseillers.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Informations personnelles</p>

                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="prenom" class="text-sm font-semibold text-slate-700">Prénom</label>
                                <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" required class="ent-input" placeholder="Prénom">
                            </div>
                            <div class="space-y-2">
                                <label for="nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="conseiller@rcpb.bf">
                        </div>

                        <div class="space-y-2">
                            <label for="specialite" class="text-sm font-semibold text-slate-700">Spécialité / Domaine <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <input id="specialite" name="specialite" type="text" value="{{ old('specialite') }}" class="ent-input" placeholder="Ex: Finances, Juridique, RH...">
                        </div>

                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                                <select id="sexe" name="sexe" required class="ent-input">
                                    <option value="">Choisir</option>
                                    <option value="Homme" @selected(old('sexe') === 'Homme')>Homme</option>
                                    <option value="Femme" @selected(old('sexe') === 'Femme')>Femme</option>
                                    <option value="Autres" @selected(old('sexe') === 'Autres')>Autres</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                <input id="date_prise_fonction" name="date_prise_fonction" type="month" value="{{ old('date_prise_fonction') }}" required class="ent-input">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm text-cyan-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Un compte de connexion sera généré automatiquement et envoyé par email au conseiller.
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="ent-btn ent-btn-primary flex-1 justify-center px-5 py-3 text-sm">
                            <i class="fas fa-user-plus mr-2"></i>
                            Ajouter le conseiller
                        </button>
                        <a href="{{ route('admin.direction-generale.index') }}" class="ent-btn flex-none justify-center px-5 py-3 text-sm border border-slate-200 text-slate-600 hover:bg-slate-50">
                            Annuler
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
