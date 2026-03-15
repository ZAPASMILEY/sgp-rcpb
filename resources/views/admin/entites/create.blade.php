@extends('layouts.app')

@section('title', 'Nouvelle entite | '.config('app.name', 'SGP-RCPB'))

@section('content')
        <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
            <div class="mx-auto max-w-3xl">
                <section class="admin-panel p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle entite</h1>
                            <p class="mt-2 text-sm text-slate-600">Renseignez les informations demandees.</p>
                        </div>
                        <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.entites.store') }}" class="mt-6 grid gap-5">
                        @csrf

                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="nom" class="text-sm font-semibold text-slate-700">Nom de l'entite</label>
                                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: RCPB Nord">
                            </div>

                            <div class="space-y-2">
                                <label for="ville" class="text-sm font-semibold text-slate-700">Ville</label>
                                <input id="ville" name="ville" type="text" value="{{ old('ville') }}" required class="ent-input" placeholder="Ex: Bobo-Dioulasso">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directrice generale</p>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="directrice_generale_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="directrice_generale_prenom" name="directrice_generale_prenom" type="text" value="{{ old('directrice_generale_prenom') }}" required class="ent-input" placeholder="Prenom">
                                </div>
                                <div class="space-y-2">
                                    <label for="directrice_generale_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="directrice_generale_nom" name="directrice_generale_nom" type="text" value="{{ old('directrice_generale_nom') }}" required class="ent-input" placeholder="Nom">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="directrice_generale_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="directrice_generale_email" name="directrice_generale_email" type="email" value="{{ old('directrice_generale_email') }}" required class="ent-input" placeholder="dg@entreprise.com">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">PCA</p>
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
                                <input id="pca_email" name="pca_email" type="email" value="{{ old('pca_email') }}" required class="ent-input" placeholder="pca@entreprise.com">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                            <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="ent-input" placeholder="Ex: +226 70 00 00 00">
                        </div>

                        <div class="ent-card space-y-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Compte de connexion du PCA</p>
                                <p class="mt-1 text-xs text-slate-500">L'email du PCA servira d'identifiant de connexion.</p>
                            </div>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="pca_password" class="text-sm font-semibold text-slate-700">Mot de passe</label>
                                    <input id="pca_password" name="pca_password" type="password" required class="ent-input" placeholder="Min. 8 caracteres" autocomplete="new-password">
                                </div>
                                <div class="space-y-2">
                                    <label for="pca_password_confirmation" class="text-sm font-semibold text-slate-700">Confirmer le mot de passe</label>
                                    <input id="pca_password_confirmation" name="pca_password_confirmation" type="password" required class="ent-input" placeholder="Retaper le mot de passe" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                            Creer l'entite
                        </button>
                    </form>
                </section>
            </div>
        </main>
@endsection
