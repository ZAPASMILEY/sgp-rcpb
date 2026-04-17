@extends('layouts.app')

@section('title', 'Configurer la Direction Generale | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>
        </div>
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Etape 2 sur 2</span>
                </div>

                {{-- Indicateur d'étapes --}}
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-500 text-xs font-bold text-white">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <span class="text-sm font-medium text-slate-400 line-through">Faitiere & PCA</span>
                    </div>
                    <div class="h-px flex-1 bg-cyan-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">2</span>
                        <span class="text-sm font-semibold text-cyan-700">Direction Generale</span>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Configuration du siege — {{ $entite->ville ?? '' }}</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Direction Generale</h1>
                    <p class="mt-2 text-sm text-slate-600">Renseignez les grands responsables de la Direction Generale. Leurs comptes de connexion seront generes automatiquement.</p>
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

                @if ($dejaConfiguree)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        La Direction Generale est deja configuree. Cette action va creer de nouveaux comptes.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.direction-generale.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5">
                    @csrf

                    {{-- Directeur General --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-white">DG</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur General</p>
                        </div>
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
                            <input id="directrice_generale_email" name="directrice_generale_email" type="email" value="{{ old('directrice_generale_email') }}" required class="ent-input" placeholder="dg@rcpb.bf">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directrice_generale_sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                                <select id="directrice_generale_sexe" name="directrice_generale_sexe" required class="ent-input">
                                    <option value="">Choisir</option>
                                    <option value="Homme" @selected(old('directrice_generale_sexe') === 'Homme')>Homme</option>
                                    <option value="Femme" @selected(old('directrice_generale_sexe') === 'Femme')>Femme</option>
                                    <option value="Autres" @selected(old('directrice_generale_sexe') === 'Autres')>Autres</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="directrice_generale_date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                <input id="directrice_generale_date_prise_fonction" name="directrice_generale_date_prise_fonction" type="month" value="{{ old('directrice_generale_date_prise_fonction') }}" required class="ent-input">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="directrice_generale_photo" class="text-sm font-semibold text-slate-700">Photo <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <input id="directrice_generale_photo" name="directrice_generale_photo" type="file" accept="image/*" class="ent-input">
                        </div>
                    </div>

                    {{-- Directeur General Adjoint --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-600 text-xs font-bold text-white">DGA</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur General Adjoint</p>
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="dga_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="dga_prenom" name="dga_prenom" type="text" value="{{ old('dga_prenom') }}" required class="ent-input" placeholder="Prenom">
                            </div>
                            <div class="space-y-2">
                                <label for="dga_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="dga_nom" name="dga_nom" type="text" value="{{ old('dga_nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="dga_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="dga_email" name="dga_email" type="email" value="{{ old('dga_email') }}" required class="ent-input" placeholder="dga@rcpb.bf">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="dga_sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                                <select id="dga_sexe" name="dga_sexe" required class="ent-input">
                                    <option value="">Choisir</option>
                                    <option value="Homme" @selected(old('dga_sexe') === 'Homme')>Homme</option>
                                    <option value="Femme" @selected(old('dga_sexe') === 'Femme')>Femme</option>
                                    <option value="Autres" @selected(old('dga_sexe') === 'Autres')>Autres</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="dga_date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                <input id="dga_date_prise_fonction" name="dga_date_prise_fonction" type="month" value="{{ old('dga_date_prise_fonction') }}" required class="ent-input">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="dga_photo" class="text-sm font-semibold text-slate-700">Photo <span class="text-slate-400 font-normal">(optionnel)</span></label>
                            <input id="dga_photo" name="dga_photo" type="file" accept="image/*" class="ent-input">
                        </div>
                    </div>

                    {{-- Assistante DG --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-400 text-xs font-bold text-white">ASS</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Assistante du Directeur General</p>
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="assistante_dg_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="assistante_dg_prenom" name="assistante_dg_prenom" type="text" value="{{ old('assistante_dg_prenom') }}" required class="ent-input" placeholder="Prenom">
                            </div>
                            <div class="space-y-2">
                                <label for="assistante_dg_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="assistante_dg_nom" name="assistante_dg_nom" type="text" value="{{ old('assistante_dg_nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="assistante_dg_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="assistante_dg_email" name="assistante_dg_email" type="email" value="{{ old('assistante_dg_email') }}" required class="ent-input" placeholder="assistante.dg@rcpb.bf">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="assistante_dg_sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                                <select id="assistante_dg_sexe" name="assistante_dg_sexe" required class="ent-input">
                                    <option value="">Choisir</option>
                                    <option value="Homme" @selected(old('assistante_dg_sexe') === 'Homme')>Homme</option>
                                    <option value="Femme" @selected(old('assistante_dg_sexe') === 'Femme')>Femme</option>
                                    <option value="Autres" @selected(old('assistante_dg_sexe') === 'Autres')>Autres</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="assistante_dg_date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                <input id="assistante_dg_date_prise_fonction" name="assistante_dg_date_prise_fonction" type="month" value="{{ old('assistante_dg_date_prise_fonction') }}" required class="ent-input">
                            </div>
                        </div>
                    </div>

                    {{-- Info comptes --}}
                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm text-cyan-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Les comptes de connexion du DG, DGA et de l'Assistante seront generes automatiquement et envoyes par email. Les Conseillers et Secretaires seront ajoutes separement.
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        <i class="fas fa-check mr-2"></i>
                        Configurer la Direction Generale
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
