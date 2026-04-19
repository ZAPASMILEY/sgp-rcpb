@extends('layouts.app')

@section('title', 'Modifier le membre | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Modifier un membre')

@section('content')
@php
    $roleLabel = match($user->role) {
        'DG'            => 'Directeur Général',
        'DGA'           => 'Directeur Général Adjoint',
        'Assistante_Dg' => 'Assistante DG',
        default         => $user->role,
    };
    // Décomposer name en prénom + nom (première partie = prénom, reste = nom)
    $parts  = explode(' ', $user->name, 2);
    $prenom = $parts[0] ?? '';
    $nom    = $parts[1] ?? '';
@endphp
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full max-w-2xl space-y-6">

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-600">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900">Modifier — {{ $roleLabel }}</h1>
                    <p class="text-sm text-slate-500">Compte actuel : {{ $user->email }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.direction-generale.membres.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Prénom --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Prénom</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $prenom) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                               required>
                    </div>

                    {{-- Nom --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', $nom) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                               required>
                    </div>

                    {{-- Email --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Adresse email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                               required>
                        <p class="mt-1 text-xs text-slate-400">Modifier l'email change également l'identifiant de connexion.</p>
                    </div>

                    {{-- Sexe --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Genre</label>
                        <select name="sexe" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100" required>
                            @foreach (['Homme', 'Femme', 'Autres'] as $genre)
                                <option value="{{ $genre }}" @selected(old('sexe', $user->sexe) === $genre)>{{ $genre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date de prise de fonction --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Date de prise de fonction</label>
                        <input type="month" name="date_prise_fonction"
                               value="{{ old('date_prise_fonction', $user->date_prise_fonction) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100"
                               required>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-cyan-700">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.direction-generale.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
