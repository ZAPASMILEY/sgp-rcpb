@extends('layouts.app')

@php
    $isFaitiereDirection = (bool) ($faitiere ?? false);
    $pageTitle = $isFaitiereDirection ? 'Nouvelle Direction Faitiere' : 'Nouveau Directeur Technique';
    $pageIntro = $isFaitiereDirection
        ? 'Cette creation est dediee aux directions de la Faitiere uniquement.'
        : 'Creez une direction rattachee a une delegation technique avec son directeur et son secretaire.';
    $submitRoute = $isFaitiereDirection ? route('admin.entites.directions.store') : route('admin.directions.store');
    $backRoute = $isFaitiereDirection ? route('admin.entites.index') : route('admin.directions.index');
@endphp

@section('title', $pageTitle.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window h-full w-full p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                            {{ $isFaitiereDirection ? 'Faitiere / Direction' : 'Delegation technique / Direction' }}
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $pageTitle }}</h1>
                        <p class="mt-2 text-sm text-slate-600">{{ $pageIntro }}</p>
                    </div>
                    <a href="{{ $backRoute }}" target="_top" class="ent-btn ent-btn-soft">
                        {{ $isFaitiereDirection ? 'Retour Faitiere' : 'Retour Delegations' }}
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ $submitRoute }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    @unless ($isFaitiereDirection)
                        <div class="space-y-2">
                            <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation technique</label>
                            <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                                <option value="">Selectionner une delegation</option>
                                @foreach ($delegations as $delegation)
                                    <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id') === (string) $delegation->id)>
                                        {{ $delegation->region }} / {{ $delegation->ville }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Direction</p>

                        @if ($isFaitiereDirection)
                            <div class="space-y-2">
                                <label for="nom" class="text-sm font-semibold text-slate-700">Nom de la direction</label>
                                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Direction des Operations">
                            </div>
                        @endif

                        <div class="ent-form-grid">
                            @if ($isFaitiereDirection)
                                <div class="space-y-2">
                                    <label for="date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                                    <input id="date_prise_fonction" name="date_prise_fonction" type="date" value="{{ old('date_prise_fonction') }}" required class="ent-input">
                                </div>
                            @endif

                            <div class="space-y-2">
                                <label for="directeur_prenom" class="text-sm font-semibold text-slate-700">Prenom du directeur</label>
                                <input id="directeur_prenom" name="directeur_prenom" type="text" value="{{ old('directeur_prenom') }}" required class="ent-input" placeholder="Prenom">
                            </div>
                            <div class="space-y-2">
                                <label for="directeur_nom" class="text-sm font-semibold text-slate-700">Nom du directeur</label>
                                <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>

                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directeur_email" class="text-sm font-semibold text-slate-700">Email professionnel</label>
                                <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email') }}" required class="ent-input" placeholder="directeur@rcpb.org">
                            </div>
                            <div class="space-y-2">
                                <label for="directeur_numero" class="text-sm font-semibold text-slate-700">Telephone</label>
                                <input id="directeur_numero" name="directeur_numero" type="text" value="{{ old('directeur_numero') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                            </div>
                        </div>
                    </div>

                    @unless ($isFaitiereDirection)
                        <div class="ent-card space-y-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Secretaire</p>

                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="secretaire_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                    <input id="secretaire_prenom" name="secretaire_prenom" type="text" value="{{ old('secretaire_prenom') }}" required class="ent-input" placeholder="Prenom">
                                </div>
                                <div class="space-y-2">
                                    <label for="secretaire_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                    <input id="secretaire_nom" name="secretaire_nom" type="text" value="{{ old('secretaire_nom') }}" required class="ent-input" placeholder="Nom">
                                </div>
                            </div>

                            <div class="ent-form-grid">
                                <div class="space-y-2">
                                    <label for="secretaire_email" class="text-sm font-semibold text-slate-700">Email professionnel</label>
                                    <input id="secretaire_email" name="secretaire_email" type="email" value="{{ old('secretaire_email') }}" required class="ent-input" placeholder="secretaire@rcpb.org">
                                </div>
                                <div class="space-y-2">
                                    <label for="secretaire_telephone" class="text-sm font-semibold text-slate-700">Telephone</label>
                                    <input id="secretaire_telephone" name="secretaire_telephone" type="text" value="{{ old('secretaire_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="space-y-2">
                            <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Telephone du secretariat (optionnel)</label>
                            <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone') }}" class="ent-input" placeholder="+226 70 00 00 00">
                        </div>
                    @endunless

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                        {{ $isFaitiereDirection
                            ? "Un compte directeur sera cree automatiquement avec envoi du mot de passe par e-mail."
                            : "Les comptes du directeur technique et du secretaire seront crees automatiquement avec envoi des mots de passe par e-mail." }}
                    </div>

                    <button type="submit" id="submit-btn" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer la direction
                    </button>
                </form>

                @push('scripts')
                <script>
                    document.querySelector('form')?.addEventListener('submit', function () {
                        var btn = document.getElementById('submit-btn');
                        if (!btn) {
                            return;
                        }

                        btn.disabled = true;
                        btn.textContent = 'Enregistrement...';
                    });
                </script>
                @endpush
            </section>
        </div>
    </main>
@endsection
