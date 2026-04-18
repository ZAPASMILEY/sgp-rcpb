@extends('layouts.app')

@section('title', 'Ajouter un secrétaire | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full">
        <section class="admin-panel ent-window p-6 sm:p-8">
            <div class="ent-window__bar" aria-hidden="true">
                <span class="ent-window__dot ent-window__dot--danger"></span>
                <span class="ent-window__dot ent-window__dot--warn"></span>
                <span class="ent-window__dot ent-window__dot--ok"></span>
                <span class="ent-window__label">Ajout secrétaire</span>
            </div>
            <h1 class="text-2xl font-bold mb-4">Ajouter un secrétaire</h1>
            <form method="POST" action="{{ route('admin.secretaires.store') }}" class="space-y-5">
                @csrf
                <div class="ent-form-grid">
                    <div class="space-y-2">
                        <label for="prenom" class="text-sm font-semibold text-slate-700">Prénom</label>
                        <input id="prenom" name="prenom" type="text" value="{{ old('prenom') }}" required class="ent-input" placeholder="Prénom">
                    </div>
                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="ent-input" placeholder="Nom">
                    </div>
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="ent-input" placeholder="exemple@domaine.com">
                </div>
                <div class="space-y-2">
                    <label for="direction_id" class="text-sm font-semibold text-slate-700">Direction rattachée</label>
                    <select id="direction_id" name="direction_id" required class="ent-input">
                        <option value="">Sélectionner une direction</option>
                        @foreach($directions as $direction)
                            <option value="{{ $direction->id }}" {{ old('direction_id') == $direction->id ? 'selected' : '' }}>{{ $direction->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="date_prise_fonction" class="text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                    <input id="date_prise_fonction" name="date_prise_fonction" type="date" value="{{ old('date_prise_fonction') }}" required class="ent-input">
                </div>
                <button type="submit" class="ent-btn ent-btn-primary px-5 py-3 text-sm">Ajouter</button>
            </form>
        </section>
    </div>
</main>
@endsection
