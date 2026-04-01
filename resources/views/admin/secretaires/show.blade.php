@extends('layouts.app')

@section('title', 'Détail Secrétaire')

@section('content')
<div class="min-h-screen flex items-center justify-center p-8">
    <div class="bg-white rounded-3xl shadow-lg p-8 max-w-lg w-full">
        <h1 class="text-2xl font-black mb-6 text-slate-800">Détail du Secrétaire</h1>
        <ul class="space-y-3 text-slate-700">
            <li><strong>Nom :</strong> {{ $direction->secretaire_prenom }} {{ $direction->secretaire_nom }}</li>
            <li><strong>Email :</strong> {{ $direction->secretaire_email }}</li>
            <li><strong>Direction :</strong> {{ $direction->nom }}</li>
            <li><strong>Date de prise de fonction :</strong> {{ $direction->date_prise_fonction }}</li>
        </ul>
        <div class="mt-8 flex gap-3">
            <a href="{{ route('admin.secretaires.edit', $direction->id) }}" class="ent-btn ent-btn-primary">Modifier</a>
            <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Retour</a>
        </div>
    </div>
</div>
@endsection
