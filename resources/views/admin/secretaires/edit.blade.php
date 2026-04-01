@extends('layouts.app')

@section('title', 'Modifier Secrétaire')

@section('content')
<div class="min-h-screen flex items-center justify-center p-8">
    <div class="bg-white rounded-3xl shadow-lg p-8 max-w-lg w-full">
        <h1 class="text-2xl font-black mb-6 text-slate-800">Modifier le Secrétaire</h1>
        <form method="POST" action="#">
            @csrf
            {{-- Ajoutez ici l'action réelle si besoin --}}
            <div class="mb-4">
                <label class="block text-slate-600 font-bold mb-1">Prénom</label>
                <input type="text" name="secretaire_prenom" value="{{ $direction->secretaire_prenom }}" class="ent-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-slate-600 font-bold mb-1">Nom</label>
                <input type="text" name="secretaire_nom" value="{{ $direction->secretaire_nom }}" class="ent-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-slate-600 font-bold mb-1">Email</label>
                <input type="email" name="secretaire_email" value="{{ $direction->secretaire_email }}" class="ent-input w-full" required>
            </div>
            <div class="mb-4">
                <label class="block text-slate-600 font-bold mb-1">Date de prise de fonction</label>
                <input type="date" name="date_prise_fonction" value="{{ $direction->date_prise_fonction }}" class="ent-input w-full">
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
                <a href="{{ route('admin.secretaires.show', $direction->id) }}" class="ent-btn ent-btn-soft">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
