@extends('layouts.app')

@section('title', 'Modifier '.$direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mise a jour</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier le Directeur Technique</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour les informations demandees.</p>
                    </div>
                    <a href="{{ route('admin.directions.show', $direction) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.directions.update', $direction) }}" class="mt-6 grid gap-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation Technique (Region / Ville)</label>
                        <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                            <option value="">Selectionner une delegation</option>
                            @foreach ($delegations as $delegation)
                                <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id', $direction->delegation_technique_id) === (string) $delegation->id)>
                                    {{ $delegation->region }} / {{ $delegation->ville }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur Technique</p>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directeur_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="directeur_prenom" name="directeur_prenom" type="text" value="{{ old('directeur_prenom', $direction->directeur_prenom) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="directeur_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom', $direction->directeur_nom) }}" required class="ent-input">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="directeur_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email', $direction->directeur_email) }}" required class="ent-input">
                        </div>
                        <div class="space-y-2">
                            <label for="directeur_numero" class="text-sm font-semibold text-slate-700">Numero de telephone</label>
                            <input id="directeur_numero" name="directeur_numero" type="text" value="{{ old('directeur_numero', $direction->directeur_numero) }}" required class="ent-input">
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Secretaire de direction</p>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="secretaire_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="secretaire_prenom" name="secretaire_prenom" type="text" value="{{ old('secretaire_prenom', $direction->secretaire_prenom) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="secretaire_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="secretaire_nom" name="secretaire_nom" type="text" value="{{ old('secretaire_nom', $direction->secretaire_nom) }}" required class="ent-input">
                            </div>
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="secretaire_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="secretaire_email" name="secretaire_email" type="email" value="{{ old('secretaire_email', $direction->secretaire_email) }}" required class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="secretaire_telephone" class="text-sm font-semibold text-slate-700">Telephone</label>
                                <input id="secretaire_telephone" name="secretaire_telephone" type="text" value="{{ old('secretaire_telephone', $direction->secretaire_telephone) }}" required class="ent-input">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                        Les mots de passe ne se modifient plus ici. Si vous changez les emails, les comptes sont synchronises automatiquement.
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
