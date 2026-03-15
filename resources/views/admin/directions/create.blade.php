@extends('layouts.app')

@section('title', 'Nouvelle direction | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle direction</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations demandees.</p>
                    </div>
                    <a href="{{ route('admin.directions.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.directions.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom de la direction</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Direction Financiere">
                    </div>

                    <div class="space-y-2">
                        <label for="entite_id" class="text-sm font-semibold text-slate-700">Entite</label>
                        <select id="entite_id" name="entite_id" required class="ent-select">
                            <option value="">Selectionner une entite</option>
                            @foreach ($entites as $entite)
                                <option value="{{ $entite->id }}" @selected((string) old('entite_id') === (string) $entite->id)>{{ $entite->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="directeur_nom" class="text-sm font-semibold text-slate-700">Nom du directeur</label>
                        <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom') }}" required class="ent-input" placeholder="Nom complet">
                    </div>

                    <div class="space-y-2">
                        <label for="directeur_email" class="text-sm font-semibold text-slate-700">Email du directeur</label>
                        <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email') }}" required class="ent-input" placeholder="directeur@entreprise.com">
                    </div>

                    <div class="space-y-2">
                        <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                        <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" required class="ent-input" placeholder="Ex: +226 70 00 00 00">
                    </div>

                    <div class="ent-card space-y-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Compte de connexion du directeur</p>
                            <p class="mt-1 text-xs text-slate-500">L'email du directeur servira d'identifiant de connexion.</p>
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="password" class="text-sm font-semibold text-slate-700">Mot de passe</label>
                                <input id="password" name="password" type="password" required class="ent-input" placeholder="Min. 8 caracteres" autocomplete="new-password">
                            </div>
                            <div class="space-y-2">
                                <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirmer le mot de passe</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required class="ent-input" placeholder="Retaper le mot de passe" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Creer la direction
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
