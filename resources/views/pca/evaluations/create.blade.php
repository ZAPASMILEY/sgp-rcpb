@extends('layouts.pca')

@section('title', 'Nouvelle evaluation | '.config('app.name', 'SGP-RCPB'))

@php
    $extractYear = static function (?string $value, string $format): ?int {
        if (! filled($value)) {
            return null;
        }

        try {
            return match ($format) {
                'd/m/Y' => \Carbon\Carbon::createFromFormat('d/m/Y', $value)->year,
                'm/Y' => (int) substr($value, -4),
                default => \Carbon\Carbon::parse($value)->year,
            };
        } catch (\Throwable $e) {
            return null;
        }
    };

    $displayYear = $extractYear(old('identification.date_evaluation'), 'd/m/Y')
        ?? $extractYear(old('date_debut'), 'm/Y')
        ?? now()->year;
@endphp

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA / Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle evaluation</h1>
                        <p class="mt-2 text-sm text-slate-600">Saisissez d'abord les criteres objectifs, puis les criteres subjectifs et le plan d'amelioration dans une meme fiche.</p>
                    </div>
                    <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </header>

            <section class="admin-panel px-6 py-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('pca.evaluations.store') }}" class="space-y-8">
                    @csrf

                    <section class="space-y-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">1. Identification et periode</h2>
                            <p class="mt-1 text-sm text-slate-500">Selectionnez l'evalue, la periode concernee et verifiez les informations d'identification pre-remplies.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="evaluable_type" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Type de cible</label>
                                <select id="evaluable_type" name="evaluable_type" class="ent-select" required>
                                    <option value="user" selected>Directeur General</option>
                                    <option value="user" @selected(old('evaluable_type') === 'user')>Directeur Général</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Cible</label>
                                <input type="hidden" id="evaluable_id" name="evaluable_id" value="{{ $dg?->id }}" data-previous-target="">
                                <input type="text" class="ent-input bg-slate-50 text-slate-600" value="{{ $dg?->name ?? 'Aucun DG trouvé' }}" readonly>
                            </div>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="date_debut" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date début</label>
                                <input id="date_debut" name="date_debut" type="text" value="{{ old('date_debut') }}" class="ent-input" placeholder="MM/YYYY" required autocomplete="off" maxlength="7">
                                <div id="date_debut_error" class="text-rose-600 text-xs mt-1" style="display:none"></div>
                            </div>
                            <div class="space-y-2">
                                <label for="date_fin" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date fin</label>
                                <input id="date_fin" name="date_fin" type="text" value="{{ old('date_fin') }}" class="ent-input" placeholder="MM/YYYY" required readonly>
                            </div>
                        </div>
                        <div>
                            <h3 class="border-t border-slate-200 pt-8 text-base font-black text-slate-900">Identification de l'evalue</h3>
                            <p class="mt-1 text-sm text-slate-500">Cette section est pre-remplie autant que possible, mais reste editable manuellement.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Annee</label>
                                <input id="annee_field" type="text" value="{{ $displayYear }}" class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_semestre" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                <select id="identification_semestre" name="identification[semestre]" required class="ent-select">
                                    <option value="">Selectionner</option>
                                    <option value="1" @selected(old('identification.semestre') === '1')>Semestre 1</option>
                                    <option value="2" @selected(old('identification.semestre') === '2')>Semestre 2</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de l'evaluation</label>
                                <input id="identification_date_evaluation" name="identification[date_evaluation]" type="text" value="{{ old('identification.date_evaluation') }}" class="ent-input" placeholder="JJ/MM/YYYY" autocomplete="off">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_matricule" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Matricule</label>
                                <input id="identification_matricule" name="identification[matricule]" type="text" value="{{ old('identification.matricule') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_emploi" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Emploi</label>
                                <input id="identification_emploi" name="identification[emploi]" type="text" value="{{ old('identification.emploi') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prenom</label>
                                <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text" value="{{ old('identification.nom_prenom') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entite</label>
                                <input id="identification_direction" name="identification[direction]" type="text" value="{{ old('identification.direction') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction / Service</label>
                                <input id="identification_direction_service" name="identification[direction_service]" type="text" value="{{ old('identification.direction_service') }}" class="ent-input">
                            </div>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-2">
                            <div class="space-y-3">
                                <div>
                                    <h3 class="text-base font-black text-slate-900">II. Formation, stage et seminaires</h3>
                                    <p class="mt-1 text-sm text-slate-500">Renseignez les formations de l'annee en cours.</p>
                                </div>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="min-w-full text-sm text-slate-700">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3 text-left">Periode</th>
                                                <th class="px-3 py-3 text-left">Formation, diplomes ou autres titres</th>
                                                <th class="px-3 py-3 text-left">Domaines d'expertises ou connaissances</th>
                                                <th class="px-3 py-3 text-left">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="formations-rows"></tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end">
                                    <button id="add-formation-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <h3 class="text-base font-black text-slate-900">III. Experience professionnelle et resume du parcours dans le reseau</h3>
                                    <p class="mt-1 text-sm text-slate-500">Renseignez les principales experiences du parcours.</p>
                                </div>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="min-w-full text-sm text-slate-700">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3 text-left">Periode</th>
                                                <th class="px-3 py-3 text-left">Poste ou fonction</th>
                                                <th class="px-3 py-3 text-left">Observations</th>
                                                <th class="px-3 py-3 text-left">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="experiences-rows"></tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end">
                                    <button id="add-experience-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="objective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">3. Criteres objectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Choisissez des fiches d'objectifs non echues, puis renseignez les sous-criteres et leurs notes. Bareme : 1 a 5.</p>
                            </div>
                            <div class="flex gap-2">
                                <select id="objective-fiche-selector" class="ent-select min-w-72">
                                    <option value="">Selectionner une fiche d'objectif</option>
                                </select>
                                <button id="add-selected-objectives" type="button" class="ent-btn ent-btn-soft">Ajouter les objectifs choisis</button>
                            </div>
                        </div>

                        <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Selectionnez une fiche pour afficher ses objectifs.
                        </div>
                        <div id="objective-criteria-container" class="space-y-5"></div>
                    </section>

                    <section id="subjective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">4. Criteres subjectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Renseignez les sous-criteres et leurs notes. Barème : 1 a 5.</p>
                            </div>
                            <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft">Ajouter un critere</button>
                        </div>

                        <div id="subjective-criteria-container" class="space-y-5"></div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">5. Synthese des notes</h2>
                            <p class="mt-1 text-sm text-slate-500">Cet espace conserve les moyennes ponderees, les notes par criteres et la note totale d'evaluation.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne ponderee objectifs</p>
                                <p id="summary-moyenne-objectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note criteres objectifs</p>
                                <p id="summary-note-objectifs" class="mt-3 text-2xl font-black text-emerald-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne ponderee subjectifs</p>
                                <p id="summary-moyenne-subjectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note criteres subjectifs</p>
                                <p id="summary-note-subjectifs" class="mt-3 text-2xl font-black text-sky-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700">Note totale d'evaluation</p>
                                <p id="summary-note-finale" class="mt-3 text-3xl font-black text-emerald-700">0,00</p>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">6. Plan d'amelioration</h2>
                            <p class="mt-1 text-sm text-slate-500">Renseignez manuellement les derniers tableaux de la fiche.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="points_a_ameliorer" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points a ameliorer</label>
                                <textarea id="points_a_ameliorer" name="points_a_ameliorer" rows="8" class="ent-input">{{ old('points_a_ameliorer') }}</textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="strategies_amelioration" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Strategies d'amelioration</label>
                                <textarea id="strategies_amelioration" name="strategies_amelioration" rows="8" class="ent-input">{{ old('strategies_amelioration') }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="commentaires_evalue" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaires de l'evalue</label>
                            <textarea id="commentaires_evalue" name="commentaires_evalue" rows="5" class="ent-input" readonly disabled>{{ old('commentaires_evalue') }}</textarea>
                        </div>

                        <div class="space-y-2">
                            <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'evaluateur</label>
                            <textarea id="commentaire" name="commentaire" rows="5" class="ent-input">{{ old('commentaire') }}</textarea>
                        </div>
                    </section>

                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">7. Signatures</h2>
                            <p class="mt-1 text-sm text-slate-500">Le nom de l'evalue suit automatiquement la cible selectionnee.</p>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="signature_evalue_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evalue</label>
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text" value="{{ old('signature_evalue_nom') }}" class="ent-input">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date" value="{{ old('date_signature_evalue') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="signature_evaluateur_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur</label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text" value="{{ old('signature_evaluateur_nom', auth()->user()->name ?? '') }}" class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date" value="{{ old('date_signature_evaluateur') }}" class="ent-input">
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    // Synchronisation du nom de l'évalué
                                    var nomIdentification = document.getElementById('identification_nom_prenom');
                                    var nomSignature = document.getElementById('signature_evalue_nom');
                                    if (nomIdentification && nomSignature) {
                                        function syncNom() {
                                            nomSignature.value = nomIdentification.value;
                                        }
                                        nomIdentification.addEventListener('input', syncNom);
                                        nomIdentification.addEventListener('change', syncNom);
                                        // Initial sync au chargement si vide
                                        if (!nomSignature.value) {
                                            nomSignature.value = nomIdentification.value;
                                        }
                                    }
                                });
                                </script>
                            <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                function todayISO() {
                                    const d = new Date();
                                    return d.toISOString().slice(0, 10);
                                }
                                var dateEvalue = document.getElementById('date_signature_evalue');
                                var dateEvaluateur = document.getElementById('date_signature_evaluateur');
                                if (dateEvalue && !dateEvalue.value) dateEvalue.value = todayISO();
                                if (dateEvaluateur && !dateEvaluateur.value) dateEvaluateur.value = todayISO();

                                // --- Année field sync with Date début ---
                                var dateDebut = document.getElementById('date_debut');
                                var anneeField = document.getElementById('annee_field');
                                if (dateDebut && anneeField) {
                                    dateDebut.addEventListener('input', function() {
                                        // Expect MM/YYYY
                                        var val = dateDebut.value;
                                        var year = '';
                                        if (/^(0[1-9]|1[0-2])\/(\d{4})$/.test(val)) {
                                            year = val.split('/')[1];
                                        }
                                        anneeField.value = year;
                                    });
                                    // Trigger once on load if value exists
                                    if (dateDebut.value && /^(0[1-9]|1[0-2])\/(\d{4})$/.test(dateDebut.value)) {
                                        anneeField.value = dateDebut.value.split('/')[1];
                                    }
                                }
                            });
                            </script>
                        <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-soft">Annuler</a>
                        <button type="submit" class="ent-btn ent-btn-primary">Creer l'evaluation</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@php
    $oldFormationsJson = old('identification.formations');
    if (!is_array($oldFormationsJson) || $oldFormationsJson === []) {
        $oldFormationsJson = [['periode' => '', 'libelle' => '', 'domaine' => '']];
    }

    $oldExperiencesJson = old('identification.experiences');
    if (!is_array($oldExperiencesJson) || $oldExperiencesJson === []) {
        $oldExperiencesJson = [['periode' => '', 'poste' => '', 'observations' => '']];
    }
@endphp

@push('scripts')
    <script id="eval-assignment-options" type="application/json">@json($assignmentOptions)</script>
    <script id="eval-target-profiles" type="application/json">@json($targetProfiles)</script>
    <script id="eval-objective-options" type="application/json">@json($objectiveOptions)</script>
    <script id="eval-subjective-templates" type="application/json">@json(old('subjective_criteres', $subjectiveTemplates))</script>
    <script id="eval-objective-old" type="application/json">@json(old('objective_criteres', []))</script>
    <script id="eval-formations-old" type="application/json">@json($oldFormationsJson)</script>
    <script id="eval-experiences-old" type="application/json">@json($oldExperiencesJson)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const assignmentOptions = JSON.parse(document.getElementById('eval-assignment-options').textContent || '{}');
            const targetProfiles = JSON.parse(document.getElementById('eval-target-profiles').textContent || '{}');
            const objectiveOptions = JSON.parse(document.getElementById('eval-objective-options').textContent || '{}');
            const subjectiveTemplates = JSON.parse(document.getElementById('eval-subjective-templates').textContent || '[]');
            const oldObjectiveCriteria = JSON.parse(document.getElementById('eval-objective-old').textContent || '[]');
            const oldFormations = JSON.parse(document.getElementById('eval-formations-old').textContent || '[]');
            const oldExperiences = JSON.parse(document.getElementById('eval-experiences-old').textContent || '[]');
            let subjectiveIndexCounter = 0;
            let objectiveIndexCounter = 0;
            let formationIndexCounter = 0;
            let experienceIndexCounter = 0;

            const typeSelect = document.getElementById('evaluable_type');
            const targetSelect = document.getElementById('evaluable_id');
            const objectiveSelector = document.getElementById('objective-fiche-selector');
            const addSelectedObjectivesButton = document.getElementById('add-selected-objectives');
            const objectiveChoiceContainer = document.getElementById('objective-choice-container');
            const subjectiveContainer = document.getElementById('subjective-criteria-container');
            const objectiveContainer = document.getElementById('objective-criteria-container');
            const subjectiveSection = document.getElementById('subjective-section');
            const objectiveSection = document.getElementById('objective-section');
            const formationsRows = document.getElementById('formations-rows');
            const experiencesRows = document.getElementById('experiences-rows');
            const addFormationRowButton = document.getElementById('add-formation-row');
            const addExperienceRowButton = document.getElementById('add-experience-row');
            const summaryMoyenneObjectifs = document.getElementById('summary-moyenne-objectifs');
            const summaryNoteObjectifs = document.getElementById('summary-note-objectifs');
            const summaryMoyenneSubjectifs = document.getElementById('summary-moyenne-subjectifs');
            const summaryNoteSubjectifs = document.getElementById('summary-note-subjectifs');
            const summaryNoteFinale = document.getElementById('summary-note-finale');
            const previousTarget = targetSelect.dataset.previousTarget || '';
            const signatureEvalueField = document.getElementById('signature_evalue_nom');
            const availableTypes = Object.entries(assignmentOptions)
                .filter(([, items]) => Array.isArray(items) && items.length > 0)
                .map(([key]) => key);
            const objectiveCatalog = (Array.isArray(objectiveOptions.user) ? objectiveOptions.user : [])
                .map((item) => ({ ...item, type: 'user' }));

            const identificationFields = {
                nom_prenom: document.getElementById('identification_nom_prenom'),
                semestre: document.getElementById('identification_semestre'),
                poste: document.getElementById('identification_poste'),
                emploi: document.getElementById('identification_emploi'),
                direction: document.getElementById('identification_direction'),
                direction_service: document.getElementById('identification_direction_service'),
                niveau: document.getElementById('identification_niveau'),
                categorie: document.getElementById('identification_categorie'),
                anciennete: document.getElementById('identification_anciennete'),
                sexe: document.getElementById('identification_sexe'),
                date_naissance: document.getElementById('identification_date_naissance'),
                date_recrutement: document.getElementById('identification_date_recrutement'),
                date_evaluation: document.getElementById('identification_date_evaluation'),
                date_titularisation: document.getElementById('identification_date_titularisation'),
                matricule: document.getElementById('identification_matricule'),
                date_confirmation: document.getElementById('identification_date_confirmation'),
                date_affectation: document.getElementById('identification_date_affectation'),
            };

            function fieldName(path, index, property) {
                return `${path}[${index}][${property}]`;
            }

            function subFieldName(path, parentIndex, subIndex, property) {
                return `${path}[${parentIndex}][subcriteria][${subIndex}][${property}]`;
            }

            function formatScore(value) {
                return Number(value || 0).toFixed(2).replace('.', ',');
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderFormationRow(row = {}, index = 0) {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-200';
                tr.innerHTML = `
                    <td class="p-2"><input type="text" name="identification[formations][${index}][periode]" value="${escapeHtml(row.periode)}" class="ent-input"></td>
                    <td class="p-2"><input type="text" name="identification[formations][${index}][libelle]" value="${escapeHtml(row.libelle)}" class="ent-input"></td>
                    <td class="p-2"><input type="text" name="identification[formations][${index}][domaine]" value="${escapeHtml(row.domaine)}" class="ent-input"></td>
                    <td class="p-2 align-top"><button type="button" class="ent-btn ent-btn-soft" data-remove-formation-row>Supprimer</button></td>
                `;
                tr.querySelector('[data-remove-formation-row]').addEventListener('click', () => {
                    tr.remove();
                    if (!formationsRows.children.length) {
                        addFormationRow();
                    }
                });
                return tr;
            }

            function addFormationRow(row = {}) {
                formationsRows.appendChild(renderFormationRow(row, formationIndexCounter));
                formationIndexCounter += 1;
            }

            function renderExperienceRow(row = {}, index = 0) {
                const tr = document.createElement('tr');
                tr.className = 'border-t border-slate-200';
                tr.innerHTML = `
                    <td class="p-2"><input type="text" name="identification[experiences][${index}][periode]" value="${escapeHtml(row.periode)}" class="ent-input"></td>
                    <td class="p-2"><input type="text" name="identification[experiences][${index}][poste]" value="${escapeHtml(row.poste)}" class="ent-input"></td>
                    <td class="p-2"><input type="text" name="identification[experiences][${index}][observations]" value="${escapeHtml(row.observations)}" class="ent-input"></td>
                    <td class="p-2 align-top"><button type="button" class="ent-btn ent-btn-soft" data-remove-experience-row>Supprimer</button></td>
                `;
                tr.querySelector('[data-remove-experience-row]').addEventListener('click', () => {
                    tr.remove();
                    if (!experiencesRows.children.length) {
                        addExperienceRow();
                    }
                });
                return tr;
            }

            function addExperienceRow(row = {}) {
                experiencesRows.appendChild(renderExperienceRow(row, experienceIndexCounter));
                experienceIndexCounter += 1;
            }

            function updateScoreSummary() {
                const computeAverage = (selector) => {
                    const values = Array.from(document.querySelectorAll(selector))
                        .map((input) => Number(input.value))
                        .filter((value) => !Number.isNaN(value) && value > 0);

                    if (values.length === 0) {
                        return 0;
                    }

                    return values.reduce((sum, value) => sum + value, 0) / values.length;
                };

                const moyenneObjectifs = computeAverage('input[name^="objective_criteres"][name$="[note]"]');
                const moyenneSubjectifs = computeAverage('input[name^="subjective_criteres"][name$="[note]"]');
                const noteObjectifs = moyenneObjectifs * 0.75;
                const noteSubjectifs = moyenneSubjectifs * 0.25;
                const noteFinale = (noteObjectifs + noteSubjectifs) * 2;

                summaryMoyenneObjectifs.textContent = formatScore(moyenneObjectifs);
                summaryNoteObjectifs.textContent = formatScore(noteObjectifs);
                summaryMoyenneSubjectifs.textContent = formatScore(moyenneSubjectifs);
                summaryNoteSubjectifs.textContent = formatScore(noteSubjectifs);
                summaryNoteFinale.textContent = formatScore(noteFinale);
            }

            function renderSubcriterion(path, parentIndex, subcriterion, subIndex, min, max) {
                const wrapper = document.createElement('div');
                wrapper.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 md:grid-cols-[1.6fr_120px_1fr_auto]';
                wrapper.innerHTML = `
                    <input type="hidden" name="${subFieldName(path, parentIndex, subIndex, 'source_fiche_objectif_objectif_id')}" value="${subcriterion.source_fiche_objectif_objectif_id ?? ''}">
                    <input type="text" name="${subFieldName(path, parentIndex, subIndex, 'libelle')}" value="${subcriterion.libelle ?? ''}" class="ent-input" placeholder="Sous-critere">
                    <input type="number" step="1" min="${min}" max="${max}" name="${subFieldName(path, parentIndex, subIndex, 'note')}" value="${subcriterion.note ?? min}" class="ent-input" placeholder="Note">
                    <input type="text" name="${subFieldName(path, parentIndex, subIndex, 'observation')}" value="${subcriterion.observation ?? ''}" class="ent-input" placeholder="Observation">
                    <button type="button" class="ent-btn ent-btn-soft" data-remove-subcriterion>Supprimer</button>
                `;
                wrapper.querySelector('[data-remove-subcriterion]').addEventListener('click', () => {
                    wrapper.remove();
                    updateScoreSummary();
                });
                return wrapper;
            }

            function renderCriterion(path, criterion, index, min, max, options = {}) {
                const article = document.createElement('article');
                article.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
                const pathPrefix = path;
                const sourceTemplateId = criterion.id ?? criterion.source_template_id ?? '';
                const ficheId = criterion.id ?? criterion.source_fiche_objectif_id ?? '';
                const objectifId = criterion.source_fiche_objectif_objectif_id ?? '';
                const titleReadonly = options.titleReadonly === true;
                const allowRemoveCriterion = options.allowRemoveCriterion !== false;
                article.innerHTML = `
                    <div class="grid gap-4 md:grid-cols-[1.5fr_1fr]">
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Critere</label>
                            <input type="text" name="${fieldName(pathPrefix, index, 'titre')}" value="${criterion.titre ?? ''}" class="ent-input" placeholder="Titre du critere" ${titleReadonly ? 'readonly' : ''}>
                            <input type="hidden" name="${fieldName(pathPrefix, index, 'source_template_id')}" value="${path === 'subjective_criteres' ? sourceTemplateId : ''}">
                            <input type="hidden" name="${fieldName(pathPrefix, index, 'source_fiche_objectif_id')}" value="${path === 'objective_criteres' ? ficheId : ''}">
                            <input type="hidden" name="${fieldName(pathPrefix, index, 'source_fiche_objectif_objectif_id')}" value="${path === 'objective_criteres' ? objectifId : ''}">
                        </div>
                        <div class="space-y-2">
                            <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Observation globale</label>
                            <input type="text" name="${fieldName(pathPrefix, index, 'observation')}" value="${criterion.observation ?? ''}" class="ent-input" placeholder="Observation globale">
                        </div>
                    </div>
                    <div class="mt-4 space-y-3" data-subcriteria-container></div>
                    <div class="mt-4 flex justify-between">
                        <button type="button" class="ent-btn ent-btn-soft" data-add-subcriterion>Ajouter un sous-critere</button>
                        ${allowRemoveCriterion ? '<button type="button" class="ent-btn ent-btn-soft" data-remove-criterion>Supprimer le critere</button>' : '<span class="text-xs font-medium text-slate-400">Critere issu de la fiche selectionnee</span>'}
                    </div>
                `;

                const subcriteriaContainer = article.querySelector('[data-subcriteria-container]');
                const addButton = article.querySelector('[data-add-subcriterion]');
                const removeButton = article.querySelector('[data-remove-criterion]');
                let subcriterionIndexCounter = 0;

                const renderAllSubcriteria = (items) => {
                    subcriteriaContainer.innerHTML = '';
                    items.forEach((subcriterion, subIndex) => {
                        subcriteriaContainer.appendChild(renderSubcriterion(pathPrefix, index, subcriterion, subIndex, min, max));
                    });
                    subcriterionIndexCounter = items.length;
                };

                const initialSubcriteria = (criterion.subcriteria && criterion.subcriteria.length > 0)
                    ? criterion.subcriteria
                    : [{ libelle: '', note: min, observation: '' }];

                renderAllSubcriteria(initialSubcriteria);

                addButton.addEventListener('click', () => {
                    subcriteriaContainer.appendChild(renderSubcriterion(pathPrefix, index, { libelle: '', note: min, observation: '' }, subcriterionIndexCounter, min, max));
                    subcriterionIndexCounter += 1;
                    updateScoreSummary();
                });

                if (removeButton) {
                    removeButton.addEventListener('click', () => {
                        article.remove();
                        updateScoreSummary();
                    });
                }

                return article;
            }

            function renderSubjectiveCriteria(criteria) {
                subjectiveContainer.innerHTML = '';
                criteria.forEach((criterion, index) => {
                    subjectiveContainer.appendChild(renderCriterion('subjective_criteres', criterion, index, 1, 5));
                });
                subjectiveIndexCounter = criteria.length;
            }

            function renderObjectiveCriteria(criteria) {
                objectiveContainer.innerHTML = '';
                criteria.forEach((criterion, index) => {
                    objectiveContainer.appendChild(renderCriterion('objective_criteres', criterion, index, 1, 5, {
                        titleReadonly: true,
                        allowRemoveCriterion: true,
                    }));
                });
                objectiveIndexCounter = criteria.length;
            }

            function getObjectiveOptionsForCurrentSelection() {
                return objectiveCatalog;
            }

            function getSelectedFiche() {
                const selectedId = objectiveSelector.value;
                return objectiveCatalog.find((item) => String(item.id) === selectedId) || null;
            }

            function syncTargetFromSelectedFiche() {
                const selected = getSelectedFiche();
                if (!selected) {
                    return;
                }

                if (typeSelect.value !== selected.type) {
                    typeSelect.value = selected.type;
                    populateTargets();
                }

                targetSelect.value = String(selected.target_id);
                hydrateIdentification();
            }

            function renderObjectiveChoices() {
                const selected = getSelectedFiche();

                if (!selected) {
                    objectiveChoiceContainer.innerHTML = 'Selectionnez une fiche pour afficher ses objectifs.';
                    return;
                }

                const selectedIds = new Set(
                    Array.from(objectiveContainer.querySelectorAll('input[name^="objective_criteres"][name$="[source_fiche_objectif_objectif_id]"]'))
                        .map((input) => String(input.value))
                        .filter(Boolean)
                );

                const objectifs = selected.objectifs || [];
                if (objectifs.length === 0) {
                    objectiveChoiceContainer.innerHTML = 'Cette fiche ne contient aucun objectif disponible.';
                    return;
                }

                objectiveChoiceContainer.innerHTML = `
                    <div class="space-y-3">
                        <p class="text-sm font-semibold text-slate-800">Objectifs disponibles dans la fiche selectionnee</p>
                        <div class="space-y-2" data-objective-choice-list></div>
                    </div>
                `;

                const list = objectiveChoiceContainer.querySelector('[data-objective-choice-list]');
                objectifs.forEach((item) => {
                    const row = document.createElement('label');
                    row.className = 'flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3';
                    row.innerHTML = `
                        <input
                            type="checkbox"
                            class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            value="${item.source_fiche_objectif_objectif_id}"
                            data-objective-title="${(item.titre ?? '').replace(/"/g, '&quot;')}"
                            ${selectedIds.has(String(item.source_fiche_objectif_objectif_id)) ? 'checked disabled' : ''}
                        >
                        <span class="text-sm text-slate-700">${item.titre ?? ''}</span>
                    `;
                    list.appendChild(row);
                });
            }

            function addSelectedObjectives() {
                const selected = getSelectedFiche();
                if (!selected) {
                    return;
                }

                const checkedInputs = Array.from(objectiveChoiceContainer.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)'));
                if (checkedInputs.length === 0) {
                    return;
                }

                checkedInputs.forEach((input) => {
                    objectiveContainer.appendChild(renderCriterion('objective_criteres', {
                        titre: input.dataset.objectiveTitle || '',
                        source_fiche_objectif_id: selected.id,
                        source_fiche_objectif_objectif_id: input.value,
                        observation: '',
                        subcriteria: [{
                            libelle: '',
                            note: 1,
                            observation: '',
                        }],
                    }, objectiveIndexCounter, 1, 5, {
                        titleReadonly: true,
                        allowRemoveCriterion: true,
                    }));

                    objectiveIndexCounter += 1;
                });

                renderObjectiveChoices();
                updateScoreSummary();
            }

            function loadObjectivesFromSelectedFiche() {
                renderObjectiveChoices();
            }

            function populateTargets() {
                const selectedType = typeSelect.value;
                const options = assignmentOptions[selectedType] || [];
                const currentValue = targetSelect.value;
                targetSelect.innerHTML = '';
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = options.length > 0 ? 'Selectionner une cible' : 'Aucune cible disponible';
                targetSelect.appendChild(defaultOption);

                options.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = item.label;
                    if (String(item.id) === currentValue || String(item.id) === previousTarget) {
                        option.selected = true;
                    }
                    targetSelect.appendChild(option);
                });

                if (!targetSelect.value && options.length === 1) {
                    targetSelect.value = String(options[0].id);
                }
            }

            function hydrateIdentification() {
                const key = `${typeSelect.value}:${targetSelect.value}`;
                const profile = targetProfiles[key];
                if (!profile) {
                    return;
                }

                Object.entries(identificationFields).forEach(([field, element]) => {
                    if (!element || element.value) {
                        return;
                    }

                    element.value = profile[field] ?? '';
                });

                if (signatureEvalueField && !signatureEvalueField.value) {
                    signatureEvalueField.value = profile.nom_prenom ?? '';
                }
            }

            function populateObjectiveSelector() {
                const currentValue = objectiveSelector.value;
                const options = getObjectiveOptionsForCurrentSelection();

                objectiveSelector.innerHTML = '<option value="">Selectionner une fiche d\\\'objectif</option>';
                options.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = String(item.id);
                    option.textContent = `${item.titre} (echeance ${item.date_echeance})`;
                    if (String(item.id) === currentValue) {
                        option.selected = true;
                    }
                    objectiveSelector.appendChild(option);
                });

                if (!objectiveSelector.value && options.length === 1) {
                    objectiveSelector.value = String(options[0].id);
                }

                loadObjectivesFromSelectedFiche();
            }

            typeSelect.addEventListener('change', () => {
                targetSelect.dataset.previousTarget = '';
                populateTargets();
                populateObjectiveSelector();
            });

            targetSelect.addEventListener('change', () => {
                hydrateIdentification();
                populateObjectiveSelector();
            });

            objectiveSelector.addEventListener('change', () => {
                syncTargetFromSelectedFiche();
                loadObjectivesFromSelectedFiche();
            });

            addSelectedObjectivesButton.addEventListener('click', () => {
                addSelectedObjectives();
            });

            addFormationRowButton.addEventListener('click', () => {
                addFormationRow();
            });

            addExperienceRowButton.addEventListener('click', () => {
                addExperienceRow();
            });

            document.addEventListener('input', (event) => {
                if (event.target.matches('input[name^="objective_criteres"][name$="[note]"], input[name^="subjective_criteres"][name$="[note]"]')) {
                    updateScoreSummary();
                }
            });

            document.getElementById('add-subjective-criterion').addEventListener('click', () => {
                subjectiveContainer.appendChild(renderCriterion('subjective_criteres', {
                    titre: '',
                    observation: '',
                    subcriteria: [{ libelle: '', note: 1, observation: '' }],
                }, subjectiveIndexCounter, 1, 5));
                subjectiveIndexCounter += 1;
                updateScoreSummary();
            });

            if (!typeSelect.value && availableTypes.length === 1) {
                typeSelect.value = availableTypes[0];
            }

            populateTargets();
            hydrateIdentification();
            populateObjectiveSelector();
            (Array.isArray(oldFormations) && oldFormations.length ? oldFormations : [{}]).forEach((row) => addFormationRow(row));
            (Array.isArray(oldExperiences) && oldExperiences.length ? oldExperiences : [{}]).forEach((row) => addExperienceRow(row));
            if (subjectiveSection && objectiveSection && subjectiveSection.previousElementSibling !== objectiveSection) {
                subjectiveSection.parentNode.insertBefore(objectiveSection, subjectiveSection);
            }
            renderSubjectiveCriteria(subjectiveTemplates);
            if (oldObjectiveCriteria.length > 0) {
                renderObjectiveCriteria(oldObjectiveCriteria);
            }
            loadObjectivesFromSelectedFiche();
            updateScoreSummary();
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const debut = document.getElementById('date_debut');
        const fin = document.getElementById('date_fin');
        const error = document.getElementById('date_debut_error');
        if (debut && fin) {
            debut.addEventListener('input', function(e) {
                let val = debut.value.replace(/[^0-9]/g, '');
                if (val.length > 6) val = val.slice(0, 6);
                if (val.length > 2) {
                    val = val.slice(0,2) + '/' + val.slice(2);
                }
                debut.value = val;
                // Validation stricte MM/YYYY
                const match = val.match(/^(0[1-9]|1[0-2])\/(\d{4})$/);
                if (match) {
                    let month = parseInt(match[1], 10);
                    let year = parseInt(match[2], 10);
                    month += 6;
                    if (month > 12) {
                        year += Math.floor((month-1) / 12);
                        month = ((month-1) % 12) + 1;
                    }
                    const mm = month < 10 ? '0'+month : ''+month;
                    fin.value = mm + '/' + year;
                    error.style.display = 'none';
                } else {
                    fin.value = '';
                    if (val.length === 7) {
                        error.textContent = 'Format invalide. Utilisez MM/YYYY.';
                        error.style.display = 'block';
                    } else {
                        error.style.display = 'none';
                    }
                }
            });
        }
    });
    </script>
@endpush
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateEval = document.getElementById('identification_date_evaluation');
    if (dateEval && !dateEval.value) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        dateEval.value = dd + '/' + mm + '/' + yyyy;
    }
});
</script>
