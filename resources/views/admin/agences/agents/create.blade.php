@extends('layouts.app')

@section('title', 'Nouvel agent d\'agence | '.config('app.name', 'SGP-RCPB'))

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

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation / Agence</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvel agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Agence: {{ $agence->nom }}</p>
                    </div>
                    <a href="{{ route('admin.agences.agents.index', $agence) }}" target="_top" class="ent-btn ent-btn-soft">Index agents agence</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agences.agents.store', $agence) }}" target="_top" class="mt-6 grid gap-5">
                    @csrf

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

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="sexe" class="text-sm font-semibold text-slate-700">Sexe</label>
                            <select id="sexe" name="sexe" required class="ent-select">
                                <option value="">Selectionner</option>
                                <option value="homme" @selected(old('sexe') === 'homme')>Homme</option>
                                <option value="femme" @selected(old('sexe') === 'femme')>Femme</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="date_debut_fonction" class="text-sm font-semibold text-slate-700">Date debut de fonction</label>
                            <input id="date_debut_fonction" name="date_debut_fonction" type="date" value="{{ old('date_debut_fonction') }}" required class="ent-input">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="fonction" class="text-sm font-semibold text-slate-700">Fonction</label>
                        <input id="fonction" name="fonction" type="text" value="{{ old('fonction') }}" required class="ent-input" placeholder="Ex: Agent de caisse">
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="numero_telephone" class="text-sm font-semibold text-slate-700">Numero</label>
                            <input id="numero_telephone" name="numero_telephone" type="text" value="{{ old('numero_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="agent.agence@rcpb.org">
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Compte de connexion</p>
                            <p class="mt-1 text-xs text-slate-500">Le mot de passe sera genere automatiquement et envoye par e-mail.</p>
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
