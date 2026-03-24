@extends('layouts.app')

@section('title', 'Nouveau Directeur Technique | '.config('app.name', 'SGP-RCPB'))

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
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouveau Directeur Technique</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations demandees.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('admin.delegations-techniques.directeurs.index') }}" target="_top" class="ent-btn ent-btn-soft">Index Directeurs</a>
                        <a href="{{ route('admin.directions.index') }}" target="_top" class="ent-btn ent-btn-soft">Index Delegations</a>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($delegations->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        Configurez d'abord au moins une delegation technique (region, ville, secretariat) depuis le tableau de bord.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.directions.store') }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation Technique (Region / Ville)</label>
                        <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                            <option value="">Selectionner une delegation</option>
                            @foreach ($delegations as $delegation)
                                <option value="{{ $delegation->id }}" @selected((string) old('delegation_technique_id') === (string) $delegation->id)>
                                    {{ $delegation->region }} / {{ $delegation->ville }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Le numero du secretariat est recupere automatiquement depuis la delegation configuree.</p>
                    </div>

                    {{-- Directeur Technique --}}
                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur Technique</p>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directeur_prenom" class="text-sm font-semibold text-slate-700">Prenom</label>
                                <input id="directeur_prenom" name="directeur_prenom" type="text" value="{{ old('directeur_prenom') }}" required class="ent-input" placeholder="Prenom">
                            </div>
                            <div class="space-y-2">
                                <label for="directeur_nom" class="text-sm font-semibold text-slate-700">Nom</label>
                                <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom') }}" required class="ent-input" placeholder="Nom">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="directeur_email" class="text-sm font-semibold text-slate-700">Email</label>
                            <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email') }}" required class="ent-input" placeholder="directeur@rcpb.org">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directeur_numero" class="text-sm font-semibold text-slate-700">Numero de telephone</label>
                                <input id="directeur_numero" name="directeur_numero" type="text" value="{{ old('directeur_numero') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-slate-700">Region</label>
                                <div class="ent-input bg-slate-50 text-slate-500">Choisie depuis la delegation</div>
                            </div>
                        </div>
                    </div>

                    {{-- Secretaire --}}
                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Secretaire de direction</p>
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
                                <label for="secretaire_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="secretaire_email" name="secretaire_email" type="email" value="{{ old('secretaire_email') }}" required class="ent-input" placeholder="secretaire@rcpb.org">
                            </div>
                            <div class="space-y-2">
                                <label for="secretaire_telephone" class="text-sm font-semibold text-slate-700">Telephone</label>
                                <input id="secretaire_telephone" name="secretaire_telephone" type="text" value="{{ old('secretaire_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">
                        Les mots de passe du directeur technique et de sa secretaire seront generes automatiquement et envoyes par e-mail.
                    </div>

                    <button type="submit" id="submit-btn" @disabled($delegations->isEmpty()) class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        Enregistrer
                    </button>
                </form>

                @push('scripts')
                <script>
                    document.querySelector('form').addEventListener('submit', function () {
                        var btn = document.getElementById('submit-btn');
                        btn.disabled = true;
                        btn.textContent = 'Enregistrement...';
                    });
                </script>
                @endpush
            </section>
        </div>
    </main>
@endsection
