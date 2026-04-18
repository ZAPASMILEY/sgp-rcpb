@extends('layouts.app')

@section('title', 'Creer une evaluation | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex flex-col gap-6">
            <header class="admin-panel ent-window px-6 py-6 lg:px-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre d'ajout</span>
                </div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle evaluation</h1>
                <p class="mt-2 text-sm text-slate-600">Evaluez une entite, une direction, un directeur, un service, un chef de service ou un agent sur une periode donnee.</p>
            </header>

            <section class="admin-panel px-6 py-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.evaluations.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="evaluable_type" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Type de cible</label>
                            <select id="evaluable_type" name="evaluable_type" class="ent-select" required>
                                <option value="">Selectionner un type</option>
                                <option value="entite"    @selected(old('evaluable_type') === 'entite')>Entite</option>
                                <option value="direction" @selected(old('evaluable_type') === 'direction')>Direction</option>
                                <option value="directeur" @selected(old('evaluable_type') === 'directeur')>Directeur</option>
                                <option value="service"   @selected(old('evaluable_type') === 'service')>Service</option>
                                <option value="chef_service" @selected(old('evaluable_type') === 'chef_service')>Chef de service</option>
                                <option value="agent"     @selected(old('evaluable_type') === 'agent')>Agent</option>
                            </select>
                            @error('evaluable_type')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label for="evaluable_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Cible</label>
                            <select id="evaluable_id" name="evaluable_id" class="ent-select" required data-previous-target="{{ old('evaluable_id') }}">
                                <option value="">Selectionner d'abord un type</option>
                            </select>
                            @error('evaluable_id')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="space-y-2">
                            <label for="date_debut" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date debut</label>
                            <input id="date_debut" name="date_debut" type="date" value="{{ old('date_debut') }}" class="ent-input" required>
                            @error('date_debut')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="space-y-2">
                            <label for="date_fin" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date fin</label>
                            <input id="date_fin" name="date_fin" type="date" value="{{ old('date_fin') }}" class="ent-input" required>
                            @error('date_fin')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="note_manuelle" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note manuelle (0-100) — optionnel</label>
                        <input id="note_manuelle" name="note_manuelle" type="number" min="0" max="100" value="{{ old('note_manuelle') }}" class="ent-input" placeholder="La note auto sera calculee depuis les objectifs de la periode">
                        @error('note_manuelle')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="space-y-2">
                        <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" rows="5" class="ent-input" placeholder="Appreciation globale, points forts, axes d'amelioration...">{{ old('commentaire') }}</textarea>
                        @error('commentaire')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('admin.evaluations.index') }}" class="ent-btn ent-btn-soft">Annuler</a>
                        <button type="submit" class="ent-btn ent-btn-primary">Creer l'evaluation</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var optionsByType = JSON.parse(document.getElementById('eval-assignment-options').textContent || '{}');
            var typeSelect   = document.getElementById('evaluable_type');
            var targetSelect = document.getElementById('evaluable_id');
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
    <script id="eval-assignment-options" type="application/json">@json($assignmentOptions)</script>
@endpush
