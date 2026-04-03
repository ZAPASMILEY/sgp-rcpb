@extends('layouts.app')

@section('title', 'Modifier agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modification</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier l'agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour les informations de l'agent.</p>
                    </div>
                    <a href="{{ route('admin.agents.show', $agent) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agents.update', $agent) }}" enctype="multipart/form-data" class="mt-6 grid gap-5">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="service_id" class="text-sm font-semibold text-slate-700">Service</label>
                        <select id="service_id" name="service_id" required class="ent-select">
                            <option value="">Selectionner un service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) old('service_id', $agent->service_id) === (string) $service->id)>
                                    {{ $service->nom }}
                                    @if ($service->direction)
                                        - {{ $service->direction->nom }}
                                    @endif
                                    @if ($service->direction?->entite)
                                        ({{ $service->direction->entite->nom }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="nom" class="text-sm font-semibold text-slate-700">Nom</label>
                            <input id="nom" name="nom" type="text" value="{{ old('nom', $agent->nom) }}" required class="ent-input" placeholder="Nom">
                        </div>
                        <div class="space-y-2">
                            <label for="prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                            <input id="prenom" name="prenom" type="text" value="{{ old('prenom', $agent->prenom) }}" required class="ent-input" placeholder="Prenom">
                        </div>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                            <select id="sexe" name="sexe" required class="ent-select">
                                <option value="">Selectionner</option>
                                <option value="homme" @selected(old('sexe', $agent->sexe) === 'homme')>Homme</option>
                                <option value="femme" @selected(old('sexe', $agent->sexe) === 'femme')>Femme</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="date_debut_fonction" class="text-sm font-semibold text-slate-700">Date debut de fonction</label>
                            <input id="date_debut_fonction" name="date_debut_fonction" type="date" value="{{ old('date_debut_fonction', optional($agent->date_debut_fonction)->toDateString()) }}" required class="ent-input">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="fonction" class="text-sm font-semibold text-slate-700">Fonction</label>
                        <input id="fonction" name="fonction" type="text" value="{{ old('fonction', $agent->fonction) }}" required class="ent-input" placeholder="Ex: Charge de suivi">
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="numero_telephone" class="text-sm font-semibold text-slate-700">Numero</label>
                            <input id="numero_telephone" name="numero_telephone" type="text" value="{{ old('numero_telephone', $agent->numero_telephone) }}" required class="ent-input" placeholder="Ex: +226 70 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Mail</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $agent->email) }}" required class="ent-input" placeholder="agent@rcpb.bf">
                        </div>
                    </div>

                    @if ($agent->photo_path)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-700">Photo actuelle</p>
                            <div class="mt-3 flex items-center gap-4">
                                <img src="{{ Storage::url($agent->photo_path) }}" alt="Photo de {{ $agent->prenom }} {{ $agent->nom }}" class="h-20 w-20 rounded-2xl object-cover ring-1 ring-slate-200">
                                <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                    <input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    Supprimer la photo actuelle
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="space-y-2">
                            <label for="photo_import" class="text-sm font-semibold text-slate-700">Importer une nouvelle photo</label>
                            <input id="photo_import" name="photo_import" type="file" accept="image/*" class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                        </div>
                        <div class="space-y-2">
                            <label for="photo_camera" class="text-sm font-semibold text-slate-700">Prendre une nouvelle photo avec la camera</label>
                            <input id="photo_camera" name="photo_camera" type="file" accept="image/*" capture="environment" class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-amber-400 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-900">
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Compte de connexion</p>
                            <p class="mt-1 text-xs text-slate-500">Laissez vide pour ne pas modifier le mot de passe. L'email sert d'identifiant de connexion.</p>
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="password" class="text-sm font-semibold text-slate-700">Nouveau mot de passe</label>
                                <input id="password" name="password" type="password" class="ent-input" placeholder="Laisser vide pour ne pas changer" autocomplete="new-password">
                            </div>
                            <div class="space-y-2">
                                <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirmer le mot de passe</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="ent-input" placeholder="Retaper le nouveau mot de passe" autocomplete="new-password">
                            </div>
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
