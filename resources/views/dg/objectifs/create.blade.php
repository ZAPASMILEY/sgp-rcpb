@extends('layouts.dg')

@section('title', 'Assigner une fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-3xl flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <h1 class="text-2xl font-bold mb-4">Assigner une fiche d'objectifs</h1>
            </header>
            <form method="POST" action="{{ route('dg.objectifs.store') }}" class="admin-panel px-6 py-6 lg:px-8 bg-white rounded-xl shadow">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Titre de la fiche</label>
                    <input type="text" name="titre_fiche" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Date d'échéance</label>
                    <input type="date" name="date_echeance" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Subordonné</label>
                    <select name="subordonne_id" class="w-full border rounded px-3 py-2" required>
                        <option value="">Sélectionner</option>
                        @foreach($subordonnes as $sub)
                            <option value="{{ $sub['id'] }}">{{ $sub['nom'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Objectifs</label>
                    <div id="objectifs-container" class="space-y-2">
                        <input type="text" name="objectifs[]" class="w-full border rounded px-3 py-2 mb-2" required placeholder="Description de l'objectif">
                    </div>
                    <button type="button" onclick="addObjectif()" class="ent-btn ent-btn-soft mt-2">Ajouter un objectif</button>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="ent-btn ent-btn-primary">Assigner la fiche</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function addObjectif() {
            const container = document.getElementById('objectifs-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'objectifs[]';
            input.className = 'w-full border rounded px-3 py-2 mb-2';
            input.required = true;
            input.placeholder = "Description de l'objectif";
            container.appendChild(input);
        }
    </script>
@endsection
