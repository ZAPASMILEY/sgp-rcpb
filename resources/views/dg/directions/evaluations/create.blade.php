@extends('layouts.dg')

@section('title', 'Nouvelle évaluation | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex-col gap-6">

            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Directions / {{ $direction->nom }}</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle évaluation</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les critères objectifs, puis les critères subjectifs et le plan d'amélioration.</p>
                    </div>
                    <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations']) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </header>

            <section class="admin-panel px-6 py-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('dg.directions.evaluations.store') }}" class="space-y-8">
                    @csrf
                    <input type="hidden" name="direction_id" value="{{ $direction->id }}">

                    {{-- Section 1 : Identification --}}
                    <section class="space-y-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">1. Identification et période</h2>
                        </div>

                        {{-- Direction cible --}}
                        <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Direction évaluée</p>
                            <p class="mt-2 text-base font-black text-slate-900">{{ $direction->nom }}</p>
                            @php $directeurNom = $direction->directeur ? trim($direction->directeur->prenom.' '.$direction->directeur->nom) : null; @endphp
                            @if ($directeurNom)
                                <p class="mt-1 text-sm text-slate-500">Directeur : {{ $directeurNom }}</p>
                            @endif
                        </div>

                        {{-- Période --}}
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="date_debut" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date début</label>
                                <input id="date_debut" name="date_debut" type="text" value="{{ old('date_debut') }}"
                                       class="ent-input" placeholder="MM/YYYY" required autocomplete="off" maxlength="7">
                                <div id="date_debut_error" class="text-rose-600 text-xs mt-1" style="display:none"></div>
                            </div>
                            <div class="space-y-2">
                                <label for="date_fin" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date fin</label>
                                <input id="date_fin" name="date_fin" type="text" value="{{ old('date_fin') }}"
                                       class="ent-input" placeholder="MM/YYYY" required readonly>
                            </div>
                        </div>

                        <div>
                            <h3 class="border-t border-slate-200 pt-8 text-base font-black text-slate-900">Identification de l'évalué</h3>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Année</label>
                                <input id="annee_field" type="text" value="{{ $displayYear }}" class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_semestre" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                <select id="identification_semestre" name="identification[semestre]" required class="ent-select">
                                    <option value="">Sélectionner</option>
                                    <option value="1" @selected(old('identification.semestre') === '1')>Semestre 1</option>
                                    <option value="2" @selected(old('identification.semestre') === '2')>Semestre 2</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de l'évaluation</label>
                                <input id="identification_date_evaluation" name="identification[date_evaluation]" type="text"
                                       value="{{ old('identification.date_evaluation') }}" class="ent-input" placeholder="JJ/MM/YYYY" autocomplete="off">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_matricule" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Matricule</label>
                                <input id="identification_matricule" name="identification[matricule]" type="text"
                                       value="{{ old('identification.matricule') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_emploi" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Emploi</label>
                                <input id="identification_emploi" name="identification[emploi]" type="text"
                                       value="{{ old('identification.emploi', 'Directeur de Direction') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prénom</label>
                                <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text"
                                       value="{{ old('identification.nom_prenom', $directeurNom) }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entité</label>
                                <input id="identification_direction" name="identification[direction]" type="text"
                                       value="{{ old('identification.direction') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction</label>
                                <input id="identification_direction_service" name="identification[direction_service]" type="text"
                                       value="{{ old('identification.direction_service', $direction->nom) }}" class="ent-input">
                            </div>
                        </div>

                        <div class="grid gap-6 xl:grid-cols-2">
                            <div class="space-y-3">
                                <h3 class="text-base font-black text-slate-900">II. Formation, stage et séminaires</h3>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="min-w-full text-sm text-slate-700">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3 text-left">Période</th>
                                                <th class="px-3 py-3 text-left">Formation / diplôme</th>
                                                <th class="px-3 py-3 text-left">Domaine</th>
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
                                <h3 class="text-base font-black text-slate-900">III. Expérience professionnelle</h3>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="min-w-full text-sm text-slate-700">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3 text-left">Période</th>
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

                    {{-- Section 3 : Critères objectifs --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">3. Critères objectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Barème : 1 à 5.</p>
                            </div>
                            <div class="flex gap-2">
                                <select id="objective-fiche-selector" class="ent-select min-w-64">
                                    <option value="">Sélectionner une fiche d'objectif</option>
                                </select>
                                <button id="add-selected-objectives" type="button" class="ent-btn ent-btn-soft">Ajouter</button>
                            </div>
                        </div>
                        <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Sélectionnez une fiche pour afficher ses objectifs.
                        </div>
                        <div id="objective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- Section 4 : Critères subjectifs --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">4. Critères subjectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Barème : 1 à 5.</p>
                            </div>
                            <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft">Ajouter un critère</button>
                        </div>
                        <div id="subjective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- Section 5 : Synthèse --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <h2 class="text-lg font-black text-slate-900">5. Synthèse des notes</h2>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. objectifs</p>
                                <p id="summary-moyenne-objectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note objectifs</p>
                                <p id="summary-note-objectifs" class="mt-3 text-2xl font-black text-emerald-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. subjectifs</p>
                                <p id="summary-moyenne-subjectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note subjectifs</p>
                                <p id="summary-note-subjectifs" class="mt-3 text-2xl font-black text-sky-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700">Note totale</p>
                                <p id="summary-note-finale" class="mt-3 text-3xl font-black text-emerald-700">0,00</p>
                            </div>
                        </div>
                    </section>

                    {{-- Section 6 : Plan d'amélioration --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <h2 class="text-lg font-black text-slate-900">6. Plan d'amélioration</h2>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="points_a_ameliorer" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points à améliorer</label>
                                <textarea id="points_a_ameliorer" name="points_a_ameliorer" rows="8" class="ent-input">{{ old('points_a_ameliorer') }}</textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="strategies_amelioration" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Stratégies d'amélioration</label>
                                <textarea id="strategies_amelioration" name="strategies_amelioration" rows="8" class="ent-input">{{ old('strategies_amelioration') }}</textarea>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'évaluateur</label>
                            <textarea id="commentaire" name="commentaire" rows="5" class="ent-input">{{ old('commentaire') }}</textarea>
                        </div>
                    </section>

                    {{-- Section 7 : Signatures --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <h2 class="text-lg font-black text-slate-900">7. Signatures</h2>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="signature_evalue_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Évalué (Directeur)</label>
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text"
                                       value="{{ old('signature_evalue_nom', $directeurNom) }}" class="ent-input">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date"
                                       value="{{ old('date_signature_evalue') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="signature_evaluateur_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Évaluateur (DG)</label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text"
                                       value="{{ old('signature_evaluateur_nom', auth()->user()->name ?? '') }}" class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date"
                                       value="{{ old('date_signature_evaluateur') }}" class="ent-input">
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                        <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'evaluations']) }}" class="ent-btn ent-btn-soft">Annuler</a>
                        <button type="submit" class="ent-btn ent-btn-primary">Créer l'évaluation</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script id="dg-eval-objective-options" type="application/json">@json($objectiveOptions ?? [])</script>
    <script id="dg-eval-subjective-templates" type="application/json">@json(old('subjective_criteres', $subjectiveTemplates ?? []))</script>
    <script id="dg-eval-objective-old" type="application/json">@json(old('objective_criteres', []))</script>
    <script id="dg-eval-formations-old" type="application/json">@json($oldFormations ?? [['periode'=>'','libelle'=>'','domaine'=>'']])</script>
    <script id="dg-eval-experiences-old" type="application/json">@json($oldExperiences ?? [['periode'=>'','poste'=>'','observations'=>'']])</script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const objectiveOptions    = JSON.parse(document.getElementById('dg-eval-objective-options').textContent || '[]');
        const subjectiveTemplates = JSON.parse(document.getElementById('dg-eval-subjective-templates').textContent || '[]');
        const oldObjectiveCriteria = JSON.parse(document.getElementById('dg-eval-objective-old').textContent || '[]');
        const oldFormations  = JSON.parse(document.getElementById('dg-eval-formations-old').textContent || '[]');
        const oldExperiences = JSON.parse(document.getElementById('dg-eval-experiences-old').textContent || '[]');

        let subjectiveIndexCounter = 0, objectiveIndexCounter = 0, formationIndexCounter = 0, experienceIndexCounter = 0;

        const objectiveSelector        = document.getElementById('objective-fiche-selector');
        const addSelectedObjectivesBtn = document.getElementById('add-selected-objectives');
        const objectiveChoiceContainer = document.getElementById('objective-choice-container');
        const objectiveContainer       = document.getElementById('objective-criteria-container');
        const subjectiveContainer      = document.getElementById('subjective-criteria-container');
        const addSubjectiveBtn         = document.getElementById('add-subjective-criterion');
        const formationsRows           = document.getElementById('formations-rows');
        const experiencesRows          = document.getElementById('experiences-rows');
        const summaryMoyenneObjectifs  = document.getElementById('summary-moyenne-objectifs');
        const summaryNoteObjectifs     = document.getElementById('summary-note-objectifs');
        const summaryMoyenneSubjectifs = document.getElementById('summary-moyenne-subjectifs');
        const summaryNoteSubjectifs    = document.getElementById('summary-note-subjectifs');
        const summaryNoteFinale        = document.getElementById('summary-note-finale');

        function formatScore(v) { return Number(v || 0).toFixed(2).replace('.', ','); }
        function updateScoreSummary() {
            const avg = (sel) => { const v = Array.from(document.querySelectorAll(sel)).map(i => Number(i.value)).filter(v => !isNaN(v) && v > 0); return v.length ? v.reduce((s,x) => s+x, 0)/v.length : 0; };
            const mObj = avg('input[name^="objective_criteres"][name$="[note]"]');
            const mSubj = avg('input[name^="subjective_criteres"][name$="[note]"]');
            const nObj = mObj * 0.75; const nSubj = mSubj * 0.25;
            summaryMoyenneObjectifs.textContent  = formatScore(mObj);
            summaryNoteObjectifs.textContent     = formatScore(nObj);
            summaryMoyenneSubjectifs.textContent = formatScore(mSubj);
            summaryNoteSubjectifs.textContent    = formatScore(nSubj);
            summaryNoteFinale.textContent        = formatScore((nObj + nSubj) * 2);
        }
        function escapeHtml(v) { return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

        function renderSubcriterion(path, parentIdx, sub, subIdx) {
            const w = document.createElement('div');
            w.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 grid-cols-[1.6fr_100px_1fr_auto]';
            w.innerHTML = `<input type="hidden" name="${path}[${parentIdx}][subcriteria][${subIdx}][source_fiche_objectif_objectif_id]" value="${sub.source_fiche_objectif_objectif_id ?? ''}"><input type="text" name="${path}[${parentIdx}][subcriteria][${subIdx}][libelle]" value="${escapeHtml(sub.libelle ?? '')}" class="ent-input" placeholder="Sous-critère"><input type="number" name="${path}[${parentIdx}][subcriteria][${subIdx}][note]" value="${sub.note ?? 1}" min="1" max="5" step="1" class="ent-input" placeholder="Note" oninput="this.value=Math.min(Math.max(parseInt(this.value)||1,1),5)"><input type="text" name="${path}[${parentIdx}][subcriteria][${subIdx}][observation]" value="${escapeHtml(sub.observation ?? '')}" class="ent-input" placeholder="Observation"><button type="button" class="ent-btn ent-btn-soft" data-remove-sub>Supprimer</button>`;
            w.querySelector('[data-remove-sub]').addEventListener('click', () => { w.remove(); updateScoreSummary(); });
            return w;
        }

        function renderCriterion(path, criterion, idx, options = {}) {
            const article = document.createElement('article');
            article.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
            const titleReadonly = options.titleReadonly === true;
            const allowRemove   = options.allowRemoveCriterion !== false;
            const ficheId = criterion.source_fiche_objectif_id ?? '';
            const objectifId = criterion.source_fiche_objectif_objectif_id ?? '';
            const templateId = path === 'subjective_criteres' ? (criterion.id ?? criterion.source_template_id ?? '') : '';
            article.innerHTML = `<div class="grid gap-4 md:grid-cols-[1.5fr_1fr]"><div class="space-y-2"><label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Critère</label><input type="text" name="${path}[${idx}][titre]" value="${escapeHtml(criterion.titre ?? '')}" class="ent-input" placeholder="Titre du critère" ${titleReadonly ? 'readonly' : ''}><input type="hidden" name="${path}[${idx}][source_template_id]" value="${escapeHtml(path === 'subjective_criteres' ? templateId : '')}"><input type="hidden" name="${path}[${idx}][source_fiche_objectif_id]" value="${escapeHtml(path === 'objective_criteres' ? ficheId : '')}"><input type="hidden" name="${path}[${idx}][source_fiche_objectif_objectif_id]" value="${escapeHtml(path === 'objective_criteres' ? objectifId : '')}"></div><div class="space-y-2"><label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Observation globale</label><input type="text" name="${path}[${idx}][observation]" value="${escapeHtml(criterion.observation ?? '')}" class="ent-input" placeholder="Observation globale"></div></div><div class="mt-4 space-y-3" data-subcriteria-container></div><div class="mt-4 flex justify-between"><button type="button" class="ent-btn ent-btn-soft" data-add-sub>Ajouter un sous-critère</button>${allowRemove ? '<button type="button" class="ent-btn ent-btn-soft" data-remove-criterion>Supprimer le critère</button>' : '<span class="text-xs font-medium text-slate-400">Critère issu de la fiche sélectionnée</span>'}</div>`;
            const subContainer = article.querySelector('[data-subcriteria-container]');
            const addSubBtn    = article.querySelector('[data-add-sub]');
            const removeBtn    = article.querySelector('[data-remove-criterion]');
            let subIdx = 0;
            const initialSubs = (criterion.subcriteria && criterion.subcriteria.length > 0) ? criterion.subcriteria : [{ libelle: '', note: 1, observation: '' }];
            initialSubs.forEach(sub => { subContainer.appendChild(renderSubcriterion(path, idx, sub, subIdx)); subIdx++; });
            addSubBtn.addEventListener('click', () => { subContainer.appendChild(renderSubcriterion(path, idx, { libelle: '', note: 1, observation: '' }, subIdx)); subIdx++; updateScoreSummary(); });
            if (removeBtn) removeBtn.addEventListener('click', () => { article.remove(); updateScoreSummary(); });
            return article;
        }

        function populateObjectiveSelector() {
            objectiveSelector.innerHTML = '<option value="">Sélectionner une fiche d\'objectif</option>';
            objectiveOptions.forEach(item => { const opt = document.createElement('option'); opt.value = String(item.id); opt.textContent = `${item.titre} (échéance ${item.date_echeance})`; objectiveSelector.appendChild(opt); });
            if (objectiveOptions.length === 1) objectiveSelector.value = String(objectiveOptions[0].id);
            renderObjectiveChoices();
        }

        function getSelectedFiche() { return objectiveOptions.find(o => String(o.id) === objectiveSelector.value) || null; }

        function renderObjectiveChoices() {
            const selected = getSelectedFiche();
            if (!selected) { objectiveChoiceContainer.innerHTML = 'Sélectionnez une fiche pour afficher ses objectifs.'; return; }
            const alreadySelected = new Set(Array.from(objectiveContainer.querySelectorAll('input[name^="objective_criteres"][name$="[source_fiche_objectif_objectif_id]"]')).map(i => String(i.value)).filter(Boolean));
            const objectifs = selected.objectifs || [];
            if (objectifs.length === 0) { objectiveChoiceContainer.innerHTML = 'Cette fiche ne contient aucun objectif disponible.'; return; }
            objectiveChoiceContainer.innerHTML = `<div class="space-y-3"><p class="text-sm font-semibold text-slate-800">Objectifs disponibles</p><div class="space-y-2" data-objective-choice-list></div></div>`;
            const list = objectiveChoiceContainer.querySelector('[data-objective-choice-list]');
            objectifs.forEach(item => { const row = document.createElement('label'); row.className = 'flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3'; row.innerHTML = `<input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600" value="${item.source_fiche_objectif_objectif_id}" data-objective-title="${escapeHtml(item.titre ?? '')}" ${alreadySelected.has(String(item.source_fiche_objectif_objectif_id)) ? 'checked disabled' : ''}><span class="text-sm text-slate-700">${escapeHtml(item.titre ?? '')}</span>`; list.appendChild(row); });
        }

        objectiveSelector.addEventListener('change', renderObjectiveChoices);
        addSelectedObjectivesBtn.addEventListener('click', () => {
            const selected = getSelectedFiche(); if (!selected) return;
            const checked = Array.from(objectiveChoiceContainer.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)'));
            if (checked.length === 0) return;
            checked.forEach(input => { objectiveContainer.appendChild(renderCriterion('objective_criteres', { titre: input.dataset.objectiveTitle || '', source_fiche_objectif_id: selected.id, source_fiche_objectif_objectif_id: input.value, observation: '', subcriteria: [{ libelle: '', note: 1, observation: '' }] }, objectiveIndexCounter, { titleReadonly: true, allowRemoveCriterion: true })); objectiveIndexCounter++; });
            renderObjectiveChoices(); updateScoreSummary();
        });

        function renderSubjectiveCriteria(criteria) { subjectiveContainer.innerHTML = ''; criteria.forEach((c, i) => { subjectiveContainer.appendChild(renderCriterion('subjective_criteres', c, i)); }); subjectiveIndexCounter = criteria.length; }
        addSubjectiveBtn.addEventListener('click', () => { subjectiveContainer.appendChild(renderCriterion('subjective_criteres', { titre: '', observation: '', subcriteria: [{ libelle: '', note: 1, observation: '' }] }, subjectiveIndexCounter)); subjectiveIndexCounter++; updateScoreSummary(); });

        function makeFormationRow(row, idx) {
            const tr = document.createElement('tr'); tr.className = 'border-t border-slate-200';
            tr.innerHTML = `<td class="p-2"><input type="text" name="identification[formations][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td><td class="p-2"><input type="text" name="identification[formations][${idx}][libelle]" value="${escapeHtml(row.libelle ?? '')}" class="ent-input"></td><td class="p-2"><input type="text" name="identification[formations][${idx}][domaine]" value="${escapeHtml(row.domaine ?? '')}" class="ent-input"></td><td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>`;
            tr.querySelector('[data-rm]').addEventListener('click', () => { tr.remove(); if (!formationsRows.children.length) addFormationRow({}); });
            return tr;
        }
        function addFormationRow(row) { formationsRows.appendChild(makeFormationRow(row || {}, formationIndexCounter)); formationIndexCounter++; }

        function makeExperienceRow(row, idx) {
            const tr = document.createElement('tr'); tr.className = 'border-t border-slate-200';
            tr.innerHTML = `<td class="p-2"><input type="text" name="identification[experiences][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td><td class="p-2"><input type="text" name="identification[experiences][${idx}][poste]" value="${escapeHtml(row.poste ?? '')}" class="ent-input"></td><td class="p-2"><input type="text" name="identification[experiences][${idx}][observations]" value="${escapeHtml(row.observations ?? '')}" class="ent-input"></td><td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>`;
            tr.querySelector('[data-rm]').addEventListener('click', () => { tr.remove(); if (!experiencesRows.children.length) addExperienceRow({}); });
            return tr;
        }
        function addExperienceRow(row) { experiencesRows.appendChild(makeExperienceRow(row || {}, experienceIndexCounter)); experienceIndexCounter++; }

        document.getElementById('add-formation-row').addEventListener('click', () => addFormationRow({}));
        document.getElementById('add-experience-row').addEventListener('click', () => addExperienceRow({}));
        document.addEventListener('input', e => { if (e.target.matches('input[name^="objective_criteres"][name$="[note]"], input[name^="subjective_criteres"][name$="[note]"]')) updateScoreSummary(); });

        populateObjectiveSelector();
        (Array.isArray(oldFormations) && oldFormations.length ? oldFormations : [{}]).forEach(r => addFormationRow(r));
        (Array.isArray(oldExperiences) && oldExperiences.length ? oldExperiences : [{}]).forEach(r => addExperienceRow(r));
        renderSubjectiveCriteria(Array.isArray(subjectiveTemplates) ? subjectiveTemplates : []);
        if (Array.isArray(oldObjectiveCriteria) && oldObjectiveCriteria.length) { oldObjectiveCriteria.forEach((c, i) => { objectiveContainer.appendChild(renderCriterion('objective_criteres', c, i, { titleReadonly: true, allowRemoveCriterion: true })); objectiveIndexCounter++; }); }
        updateScoreSummary();
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const debut = document.getElementById('date_debut');
        const fin   = document.getElementById('date_fin');
        const error = document.getElementById('date_debut_error');
        if (debut && fin) {
            debut.addEventListener('input', function () {
                let val = debut.value.replace(/[^0-9]/g, '');
                if (val.length > 6) val = val.slice(0, 6);
                if (val.length > 2) val = val.slice(0, 2) + '/' + val.slice(2);
                debut.value = val;
                const match = val.match(/^(0[1-9]|1[0-2])\/(\d{4})$/);
                if (match) {
                    let month = parseInt(match[1], 10) + 6, year = parseInt(match[2], 10);
                    if (month > 12) { year += Math.floor((month - 1) / 12); month = ((month - 1) % 12) + 1; }
                    fin.value = (month < 10 ? '0' : '') + month + '/' + year;
                    error.style.display = 'none';
                } else { fin.value = ''; if (val.length === 7) { error.textContent = 'Format invalide. Utilisez MM/YYYY.'; error.style.display = 'block'; } else error.style.display = 'none'; }
            });
        }
        const anneeField = document.getElementById('annee_field');
        if (debut && anneeField) {
            debut.addEventListener('input', function () { anneeField.value = /^(0[1-9]|1[0-2])\/(\d{4})$/.test(debut.value) ? debut.value.split('/')[1] : ''; });
        }
        function todayISO() { return new Date().toISOString().slice(0, 10); }
        ['date_signature_evalue', 'date_signature_evaluateur'].forEach(id => { const el = document.getElementById(id); if (el && !el.value) el.value = todayISO(); });
        const dateEval = document.getElementById('identification_date_evaluation');
        if (dateEval && !dateEval.value) { const t = new Date(); dateEval.value = String(t.getDate()).padStart(2,'0')+'/'+String(t.getMonth()+1).padStart(2,'0')+'/'+t.getFullYear(); }
    });
    </script>
@endpush
