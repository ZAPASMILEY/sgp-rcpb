@extends('layouts.app')

@section('title', 'Modifier objectif | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modification</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier l'objectif</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour la cible, l'echeance ou le commentaire.</p>
                    </div>
                    <a href="{{ route('admin.objectifs.show', $objectif) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.objectifs.update', $objectif) }}" class="mt-6 grid gap-5">
                    @csrf
                    @method('PUT')

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="date" class="text-sm font-semibold text-slate-700">Date</label>
                            <input id="date" type="date" value="{{ $objectif->date }}" readonly class="ent-input bg-slate-100 text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label for="date_echeance" class="text-sm font-semibold text-slate-700">Date d'echeance</label>
                            <input id="date_echeance" name="date_echeance" type="date" value="{{ old('date_echeance', $objectif->date_echeance) }}" required class="ent-input">
                        </div>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="assignable_type" class="text-sm font-semibold text-slate-700">Type de cible</label>
                            <select id="assignable_type" name="assignable_type" required class="ent-select">
                                <option value="">Selectionner un type</option>
                                <option value="entite" @selected(old('assignable_type', $selectedAssignableType) === 'entite')>Entite</option>
                                <option value="direction" @selected(old('assignable_type', $selectedAssignableType) === 'direction')>Direction</option>
                                <option value="service" @selected(old('assignable_type', $selectedAssignableType) === 'service')>Service</option>
                                <option value="agent" @selected(old('assignable_type', $selectedAssignableType) === 'agent')>Agent</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="assignable_id" class="text-sm font-semibold text-slate-700">Cible</label>
                            <select id="assignable_id" name="assignable_id" required class="ent-select" data-previous-target="{{ old('assignable_id', $objectif->assignable_id) }}">
                                <option value="">Selectionner d'abord un type</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="commentaire" class="text-sm font-semibold text-slate-700">Commentaire / objectif</label>
                        <textarea id="commentaire" name="commentaire" rows="6" required class="ent-input">{{ old('commentaire', $objectif->commentaire) }}</textarea>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var optionsByType = JSON.parse(document.getElementById('objectif-assignment-options').textContent || '{}');
            var typeSelect = document.getElementById('assignable_type');
            var targetSelect = document.getElementById('assignable_id');
            var previousTarget = targetSelect.dataset.previousTarget || '';

            function populateTargets() {
                var selectedType = typeSelect.value;
                var options = optionsByType[selectedType] || [];

                targetSelect.innerHTML = '';

                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = options.length > 0 ? 'Selectionner une cible' : 'Aucune cible disponible';
                targetSelect.appendChild(defaultOption);

                options.forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.label;

                    if (previousTarget !== '' && String(item.id) === previousTarget) {
                        option.selected = true;
                    }

                    targetSelect.appendChild(option);
                });
            }

            typeSelect.addEventListener('change', function () {
                previousTarget = '';
                populateTargets();
            });

            populateTargets();
        });
    </script>
    <script id="objectif-assignment-options" type="application/json">@json($assignmentOptions)</script>
@endpush