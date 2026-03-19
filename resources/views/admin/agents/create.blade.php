@extends('layouts.app')

@section('title', 'Nouvel agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
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
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations de l'agent. La photo est facultative.</p>
                    </div>
                    <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agents.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="service_id" class="text-sm font-semibold text-slate-700">Service</label>
                        <select id="service_id" name="service_id" required class="ent-select">
                            <option value="">Selectionner un service</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>
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
                            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Nom">
                        </div>
                        <div class="space-y-2">
                            <label for="prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                            <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" required class="ent-input" placeholder="Prenom">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="fonction" class="text-sm font-semibold text-slate-700">Fonction</label>
                        <input id="fonction" name="fonction" type="text" value="{{ old('fonction') }}" required class="ent-input" placeholder="Ex: Charge de suivi">
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="numero_telephone" class="text-sm font-semibold text-slate-700">Numero</label>
                            <input id="numero_telephone" name="numero_telephone" type="text" value="{{ old('numero_telephone') }}" required class="ent-input" placeholder="Ex: +226 70 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Mail</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="agent@rcpb.bf">
                        </div>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="space-y-2">
                            <label for="photo_import" class="text-sm font-semibold text-slate-700">Importer une photo</label>
                            <input id="photo_import" name="photo_import" type="file" accept="image/*" class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <p class="text-xs text-slate-500">Formats image uniquement. Taille maximale 3 Mo.</p>
                        </div>
                        <div class="space-y-2">
                            <label for="photo_camera" class="text-sm font-semibold text-slate-700">Prendre une photo avec la camera</label>
                            <input id="photo_camera" name="photo_camera" type="file" accept="image/*" capture="environment" class="ent-input block w-full cursor-pointer file:mr-3 file:rounded-xl file:border-0 file:bg-amber-400 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-900">
                            <p class="text-xs text-slate-500">Compatible surtout sur mobile ou tablette.</p>
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Compte de connexion</p>
                            <p class="mt-1 text-xs text-slate-500">L'adresse email ci-dessus servira d'identifiant. Le mot de passe sera genere automatiquement et envoye par e-mail.</p>
                        </div>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Creer l'agent
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection