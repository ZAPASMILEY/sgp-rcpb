@extends('layouts.app')

@section('title', 'Nouveau service | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouveau service</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les informations demandees.</p>
                    </div>
                    <a href="{{ route('admin.services.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.services.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom du service</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Ex: Service Tresorerie">
                    </div>

                    <div class="space-y-2">
                        <label for="direction_id" class="text-sm font-semibold text-slate-700">Direction</label>
                        <select id="direction_id" name="direction_id" required class="ent-select">
                            <option value="">Selectionner une direction</option>
                            @foreach ($directions as $direction)
                                <option value="{{ $direction->id }}" @selected((string) old('direction_id') === (string) $direction->id)>
                                    {{ $direction->nom }} @if ($direction->entite) - {{ $direction->entite->nom }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="chef_prenom" class="text-sm font-semibold text-slate-700">Prenom du chef de service</label>
                            <input id="chef_prenom" name="chef_prenom" type="text" value="{{ old('chef_prenom') }}" required class="ent-input" placeholder="Prenom">
                        </div>
                        <div class="space-y-2">
                            <label for="chef_nom" class="text-sm font-semibold text-slate-700">Nom du chef de service</label>
                            <input id="chef_nom" name="chef_nom" type="text" value="{{ old('chef_nom') }}" required class="ent-input" placeholder="Nom">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="chef_email" class="text-sm font-semibold text-slate-700">Email du chef de service</label>
                        <input id="chef_email" name="chef_email" type="email" value="{{ old('chef_email') }}" required class="ent-input" placeholder="chef.service@entreprise.com">
                    </div>

                    <div class="space-y-2">
                        <label for="chef_telephone" class="text-sm font-semibold text-slate-700">Numero de telephone</label>
                        <input id="chef_telephone" name="chef_telephone" type="text" value="{{ old('chef_telephone') }}" required class="ent-input" placeholder="Ex: +226 70 00 00 00">
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Creer le service
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
