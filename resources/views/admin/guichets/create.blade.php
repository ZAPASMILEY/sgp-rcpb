@extends('layouts.app')

@section('title', 'Nouveau guichet | '.config('app.name', 'SGP-RCPB'))

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
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouveau guichet</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez le nom du guichet, les coordonnees du chef et l'agence d'appartenance.</p>
                    </div>
                    <a href="{{ route('admin.guichets.index') }}" target="_top" class="ent-btn ent-btn-soft">Index guichets</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.guichets.store') }}" target="_top" class="mt-6 grid gap-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom du guichet</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Guichet principal">
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Chef de guichet</p>
                        <div class="space-y-2">
                            <label for="chef_nom" class="text-sm font-semibold text-slate-700">Nom complet</label>
                            <input id="chef_nom" name="chef_nom" type="text" value="{{ old('chef_nom') }}" required class="ent-input" placeholder="Nom et prenom du chef de guichet">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="chef_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="chef_email" name="chef_email" type="email" value="{{ old('chef_email') }}" required class="ent-input" placeholder="chef.guichet@rcpb.org">
                            </div>
                            <div class="space-y-2">
                                <label for="chef_telephone" class="text-sm font-semibold text-slate-700">Telephone</label>
                                <input id="chef_telephone" name="chef_telephone" type="text" value="{{ old('chef_telephone') }}" required class="ent-input" placeholder="+226 70 00 00 00">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="agence_id" class="text-sm font-semibold text-slate-700">Agence d'appartenance</label>
                        <select id="agence_id" name="agence_id" required class="ent-select">
                            <option value="">Selectionner une agence</option>
                            @foreach ($agences as $agence)
                                <option value="{{ $agence->id }}" @selected((string) old('agence_id') === (string) $agence->id)>
                                    {{ $agence->nom }}
                                    @if ($agence->delegationTechnique)
                                        - {{ $agence->delegationTechnique->region }} / {{ $agence->delegationTechnique->ville }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer le guichet
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
