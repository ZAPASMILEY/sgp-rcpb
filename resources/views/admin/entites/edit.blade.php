@extends('layouts.app')

@section('title', 'Modifier la faitiere | '.config('app.name', 'SGP-RCPB'))

@section('content')
        <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
            </div>
            <div class="w-full">
                <section class="admin-panel p-6 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mise a jour du siege</p>
                            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier la faitiere</h1>
                            <p class="mt-2 text-sm text-slate-600">Mettez a jour la localisation du siege ainsi que les responsables de la faitiere.</p>
                        </div>
                        <a href="{{ route('admin.entites.show', $entite) }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>

                    @if ($errors->any())
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.entites.update', $entite) }}" enctype="multipart/form-data" class="mt-6 grid gap-5">
                        @csrf
                        @method('PUT')

                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="ville" class="text-sm font-semibold text-slate-700">Ville</label>
                                <input id="ville" name="ville" type="text" value="{{ old('ville', $entite->ville) }}" required class="ent-input">
                            </div>

                            <div class="space-y-2">
                                <label for="region" class="text-sm font-semibold text-slate-700">Region</label>
                                <input id="region" name="region" type="text" value="{{ old('region', $entite->region) }}" required class="ent-input">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur general</p>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="directrice_generale_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="directrice_generale_prenom" name="directrice_generale_prenom" type="text" value="{{ old('directrice_generale_prenom', $entite->directrice_generale_prenom) }}" required class="ent-input">
                                </div>
                                <div class="space-y-2">
                                    <label for="directrice_generale_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="directrice_generale_nom" name="directrice_generale_nom" type="text" value="{{ old('directrice_generale_nom', $entite->directrice_generale_nom) }}" required class="ent-input">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="directrice_generale_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="directrice_generale_email" name="directrice_generale_email" type="email" value="{{ old('directrice_generale_email', $entite->directrice_generale_email) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="directrice_generale_photo" class="text-sm font-semibold text-slate-700">Photo</label>
                                <input id="directrice_generale_photo" name="directrice_generale_photo" type="file" accept="image/*" class="ent-input">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur general adjoint</p>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="dga_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="dga_prenom" name="dga_prenom" type="text" value="{{ old('dga_prenom', $entite->dga_prenom) }}" required class="ent-input">
                                </div>
                                <div class="space-y-2">
                                    <label for="dga_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="dga_nom" name="dga_nom" type="text" value="{{ old('dga_nom', $entite->dga_nom) }}" required class="ent-input">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="dga_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="dga_email" name="dga_email" type="email" value="{{ old('dga_email', $entite->dga_email) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="dga_photo" class="text-sm font-semibold text-slate-700">Photo</label>
                                <input id="dga_photo" name="dga_photo" type="file" accept="image/*" class="ent-input">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Assistante du DG</p>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="assistante_dg_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="assistante_dg_prenom" name="assistante_dg_prenom" type="text" value="{{ old('assistante_dg_prenom', $entite->assistante_dg_prenom) }}" required class="ent-input">
                                </div>
                                <div class="space-y-2">
                                    <label for="assistante_dg_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="assistante_dg_nom" name="assistante_dg_nom" type="text" value="{{ old('assistante_dg_nom', $entite->assistante_dg_nom) }}" required class="ent-input">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="assistante_dg_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="assistante_dg_email" name="assistante_dg_email" type="email" value="{{ old('assistante_dg_email', $entite->assistante_dg_email) }}" required class="ent-input">
                            </div>
                        </div>

                        <div class="ent-card space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">PCA</p>
                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="pca_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="pca_prenom" name="pca_prenom" type="text" value="{{ old('pca_prenom', $entite->pca_prenom) }}" required class="ent-input">
                                </div>
                                <div class="space-y-2">
                                    <label for="pca_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="pca_nom" name="pca_nom" type="text" value="{{ old('pca_nom', $entite->pca_nom) }}" required class="ent-input">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label for="pca_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="pca_email" name="pca_email" type="email" value="{{ old('pca_email', $entite->pca_email) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="pca_photo" class="text-sm font-semibold text-slate-700">Photo</label>
                                <input id="pca_photo" name="pca_photo" type="file" accept="image/*" class="ent-input">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                            <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone', $entite->secretariat_telephone) }}" required class="ent-input">
                        </div>

                        <div class="ent-card space-y-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Comptes de connexion</p>
                                <p class="mt-1 text-xs text-slate-500">Les mots de passe ne se saisissent plus ici. Ils sont generes a la creation puis chaque responsable peut ensuite le changer dans son espace.</p>
                            </div>
                        </div>

                        <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                            Enregistrer les modifications
                        </button>
                    </form>
                </section>
            </div>
        </main>
@endsection
