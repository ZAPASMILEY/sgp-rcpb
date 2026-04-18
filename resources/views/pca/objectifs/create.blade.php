@extends('layouts.pca')

@section('title', 'Nouvel objectif | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvel objectif</h1>
                        <p class="mt-2 text-sm text-slate-600">Creez une fiche d'objectifs destinee au DG de votre entite.</p>
                    </div>
                    <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('pca.objectifs.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Cible unique</p>
                        <p class="mt-2 text-base font-black text-slate-900">{{ $dgUser?->name ?? 'DG non configure' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Les objectifs crees par le PCA sont assignes uniquement au Directeur General.</p>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="date" class="text-sm font-semibold text-slate-700">Date</label>
                            <input id="date" type="date" value="{{ $today }}" readonly class="ent-input bg-slate-100 text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label for="date_echeance" class="text-sm font-semibold text-slate-700">Date d'échéance</label>
                            <input id="date_echeance" name="date_echeance" type="date" value="{{ old('date_echeance', \Carbon\Carbon::parse($today)->addYear()->toDateString()) }}" required class="ent-input">
                        </div>
                    </div>

                    <!-- Les champs Type de cible et Cible sont masqués pour le PCA, l'objectif sera assigné automatiquement au DG -->

                    <div class="space-y-2">
                        <label for="titre_fiche" class="text-sm font-semibold text-slate-700">Titre de la fiche d'objectifs</label>
                        <input type="text" id="titre_fiche" name="titre_fiche" required class="ent-input" placeholder="Titre général de la fiche (ex: Contrat d'objectifs 2026)">
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Objectifs à assigner au DG</label>
                        <div id="objectifs-container" class="space-y-2">
                            <div class="objectif-row flex items-center gap-2">
                                <input type="text" name="objectifs[]" required class="ent-input flex-1" placeholder="Description de l'objectif">
                                <button type="button" class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" title="Supprimer cet objectif" style="display:none;"><i class="fas fa-trash text-xs"></i></button>
                            </div>
                        </div>
                        <button type="button" id="add-objectif-btn" class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600"><i class="fas fa-plus text-[10px]"></i> Ajouter un objectif</button>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm mt-4">
                        Créer les objectifs
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ajout dynamique d'objectifs (descriptions)
        var container = document.getElementById('objectifs-container');
        var btn = document.getElementById('add-objectif-btn');

        btn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'objectif-row flex items-center gap-2';
            row.innerHTML =
                '<input type="text" name="objectifs[]" required class="ent-input flex-1" placeholder="Description de l\'objectif">' +
                '<button type="button" class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" title="Supprimer cet objectif"><i class="fas fa-trash text-xs"></i></button>';
            container.appendChild(row);
            row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
                row.remove();
                updateRemoveButtons();
            });
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            var rows = container.querySelectorAll('.objectif-row');
            rows.forEach(function (row, idx) {
                var btn = row.querySelector('.remove-objectif-btn');
                btn.style.display = rows.length > 1 ? '' : 'none';
            });
        }

        // Initialiser le bouton de suppression sur la première ligne
        updateRemoveButtons();
        container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btn.closest('.objectif-row').remove();
                updateRemoveButtons();
            });
        });
    });
    </script>
@endpush
