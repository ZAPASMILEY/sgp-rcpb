@extends('layouts.dg')

@section('title', 'Nouvelle evaluation | '.config('app.name', 'SGP-RCPB'))

@php
    $resolvedSubordonneId = (int) old('subordonne_id', $selectedSubordonne['id'] ?? 0);
    $resolvedSubordonne   = $subordonnes->firstWhere('id', $resolvedSubordonneId) ?? $selectedSubordonne;
    $lockSubordonne       = $subordonnes->count() === 1 || $selectedSubordonne !== null;

    $extractYear = static function (?string $value, string $format): ?int {
        if (! filled($value)) return null;
        try {
            return match ($format) {
                'd/m/Y' => \Carbon\Carbon::createFromFormat('d/m/Y', $value)->year,
                'm/Y'   => (int) substr($value, -4),
                default => \Carbon\Carbon::parse($value)->year,
            };
        } catch (\Throwable) {
            return null;
        }
    };

    $displayYear = $extractYear(old('identification.date_evaluation'), 'd/m/Y')
        ?? $extractYear(old('date_debut'), 'm/Y')
        ?? now()->year;
@endphp

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col gap-6">

            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Collaborateurs</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle evaluation</h1>
                        <p class="mt-2 text-sm text-slate-600">Renseignez les criteres objectifs, puis les criteres subjectifs et le plan d'amelioration.</p>
                    </div>
                    <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </header>

            <section class="admin-panel px-6 py-6 lg:px-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('dg.sub-evaluations.store') }}" class="space-y-8">
                    @csrf

                    {{-- ── Section 1 : Identification ── --}}
                    <section class="space-y-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">1. Identification et periode</h2>
                            <p class="mt-1 text-sm text-slate-500">Selectionnez le collaborateur evalue, la periode concernee et verifiez les informations.</p>
                        </div>

                        {{-- Collaborateur cible --}}
                        @if ($lockSubordonne && $resolvedSubordonne)
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Collaborateur evalue</p>
                                <p class="mt-2 text-base font-black text-slate-900">{{ $resolvedSubordonne['nom'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $resolvedSubordonne['role_label'] ?? '' }}</p>
                                <input type="hidden" name="subordonne_id" value="{{ $resolvedSubordonne['id'] }}">
                            </div>
                        @else
                            <div class="space-y-2">
                                <label for="subordonne_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Collaborateur evalue</label>
                                <select id="subordonne_id" name="subordonne_id" class="ent-select" required>
                                    <option value="">Selectionner un collaborateur</option>
                                    @foreach ($subordonnes as $sub)
                                        <option value="{{ $sub['id'] }}" @selected($resolvedSubordonneId === (int) $sub['id'])>
                                            {{ $sub['nom'] }} ({{ $sub['role_label'] ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Période --}}
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="date_debut" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date debut</label>
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
                            <h3 class="border-t border-slate-200 pt-8 text-base font-black text-slate-900">Identification de l'evalue</h3>
                            <p class="mt-1 text-sm text-slate-500">Cette section est editable manuellement.</p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Annee</label>
                                <input id="annee_field" type="text" value="{{ $displayYear }}" class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_semestre" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                <select id="identification_semestre" name="identification[semestre]" class="ent-select">
                                    <option value="">Selectionner</option>
                                    <option value="1" @selected(old('identification.semestre') === '1')>Semestre 1</option>
                                    <option value="2" @selected(old('identification.semestre') === '2')>Semestre 2</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de l'evaluation</label>
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
                                       value="{{ old('identification.emploi', $resolvedSubordonne['role_label'] ?? '') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prenom</label>
                                <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text"
                                       value="{{ old('identification.nom_prenom', $resolvedSubordonne['nom'] ?? '') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entite</label>
                                <input id="identification_direction" name="identification[direction]" type="text"
                                       value="{{ old('identification.direction') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction / Service</label>
                                <input id="identification_direction_service" name="identification[direction_service]" type="text"
                                       value="{{ old('identification.direction_service') }}" class="ent-input">
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
                                                <th class="px-3 py-3 text-left">Domaines d'expertises</th>
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
                                    <h3 class="text-base font-black text-slate-900">III. Experience professionnelle</h3>
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

                    {{-- ── Section 3 : Critères objectifs ── --}}
                    <section id="objective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">3. Criteres objectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Choisissez une fiche d'objectifs, puis renseignez les sous-criteres et leurs notes. Bareme : 1 a 5.</p>
                            </div>
                            <div class="flex gap-2">
                                <select id="objective-fiche-selector" class="ent-select min-w-64">
                                    <option value="">Selectionner une fiche d'objectif</option>
                                </select>
                                <button id="add-selected-objectives" type="button" class="ent-btn ent-btn-soft">Ajouter les objectifs</button>
                            </div>
                        </div>

                        <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Selectionnez une fiche pour afficher ses objectifs.
                        </div>
                        <div id="objective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- ── Section 4 : Critères subjectifs ── --}}
                    <section id="subjective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">4. Criteres subjectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">Renseignez les sous-criteres et leurs notes. Bareme : 1 a 5.</p>
                            </div>
                            <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft">Ajouter un critere</button>
                        </div>
                        <div id="subjective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- ── Section 5 : Synthèse ── --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">5. Synthese des notes</h2>
                            <p class="mt-1 text-sm text-slate-500">Cet espace conserve les moyennes ponderees, les notes par criteres et la note totale.</p>
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

                    {{-- ── Section 6 : Plan d'amélioration ── --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">6. Plan d'amelioration</h2>
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
                            <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'evaluateur</label>
                            <textarea id="commentaire" name="commentaire" rows="5" class="ent-input">{{ old('commentaire') }}</textarea>
                        </div>
                    </section>

                    {{-- ── Section 7 : Signatures ── --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">7. Signatures</h2>
                        </div>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="signature_evalue_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evalue</label>
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text"
                                       value="{{ old('signature_evalue_nom', $resolvedSubordonne['nom'] ?? '') }}" class="ent-input">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date"
                                       value="{{ old('date_signature_evalue') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="signature_evaluateur_nom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur (DG)</label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text"
                                       value="{{ old('signature_evaluateur_nom', auth()->user()->name ?? '') }}" class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date"
                                       value="{{ old('date_signature_evaluateur') }}" class="ent-input">
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                        <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Annuler</a>
                        <button type="submit" class="ent-btn ent-btn-primary">Creer l'evaluation</button>
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
        const objectiveOptions  = JSON.parse(document.getElementById('dg-eval-objective-options').textContent || '[]');
        const subjectiveTemplates = JSON.parse(document.getElementById('dg-eval-subjective-templates').textContent || '[]');
        const oldObjectiveCriteria = JSON.parse(document.getElementById('dg-eval-objective-old').textContent || '[]');
        const oldFormations  = JSON.parse(document.getElementById('dg-eval-formations-old').textContent || '[]');
        const oldExperiences = JSON.parse(document.getElementById('dg-eval-experiences-old').textContent || '[]');

        let subjectiveIndexCounter  = 0;
        let objectiveIndexCounter   = 0;
        let formationIndexCounter   = 0;
        let experienceIndexCounter  = 0;

        const objectiveSelector       = document.getElementById('objective-fiche-selector');
        const addSelectedObjectivesBtn= document.getElementById('add-selected-objectives');
        const objectiveChoiceContainer= document.getElementById('objective-choice-container');
        const objectiveContainer      = document.getElementById('objective-criteria-container');
        const subjectiveContainer     = document.getElementById('subjective-criteria-container');
        const addSubjectiveBtn        = document.getElementById('add-subjective-criterion');
        const formationsRows          = document.getElementById('formations-rows');
        const experiencesRows         = document.getElementById('experiences-rows');
        const addFormationRowBtn      = document.getElementById('add-formation-row');
        const addExperienceRowBtn     = document.getElementById('add-experience-row');
        const summaryMoyenneObjectifs = document.getElementById('summary-moyenne-objectifs');
        const summaryNoteObjectifs    = document.getElementById('summary-note-objectifs');
        const summaryMoyenneSubjectifs= document.getElementById('summary-moyenne-subjectifs');
        const summaryNoteSubjectifs   = document.getElementById('summary-note-subjectifs');
        const summaryNoteFinale       = document.getElementById('summary-note-finale');

        // ── Score summary ────────────────────────────────────────────────────
        function formatScore(v) { return Number(v || 0).toFixed(2).replace('.', ','); }

        function updateScoreSummary() {
            const computeAvg = (selector) => {
                const vals = Array.from(document.querySelectorAll(selector))
                    .map(i => Number(i.value)).filter(v => !isNaN(v) && v > 0);
                return vals.length ? vals.reduce((s, v) => s + v, 0) / vals.length : 0;
            };
            const mObj  = computeAvg('input[name^="objective_criteres"][name$="[note]"]');
            const mSubj = computeAvg('input[name^="subjective_criteres"][name$="[note]"]');
            const nObj  = mObj * 0.75;
            const nSubj = mSubj * 0.25;
            summaryMoyenneObjectifs.textContent  = formatScore(mObj);
            summaryNoteObjectifs.textContent     = formatScore(nObj);
            summaryMoyenneSubjectifs.textContent = formatScore(mSubj);
            summaryNoteSubjectifs.textContent    = formatScore(nSubj);
            summaryNoteFinale.textContent        = formatScore((nObj + nSubj) * 2);
        }

        // ── Escape HTML ─────────────────────────────────────────────────────
        function escapeHtml(v) {
            return String(v ?? '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        // ── Subcriterion row ─────────────────────────────────────────────────
        function renderSubcriterion(path, parentIdx, sub, subIdx) {
            const w = document.createElement('div');
            w.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 md:grid-cols-[1.6fr_120px_1fr_auto]';
            w.innerHTML = `
                <input type="hidden" name="${path}[${parentIdx}][subcriteria][${subIdx}][source_fiche_objectif_objectif_id]" value="${sub.source_fiche_objectif_objectif_id ?? ''}">
                <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][libelle]" value="${escapeHtml(sub.libelle ?? '')}" class="ent-input" placeholder="Sous-critere">
                <input type="number" name="${path}[${parentIdx}][subcriteria][${subIdx}][note]" value="${sub.note ?? 1}" min="1" max="5" step="1" class="ent-input" placeholder="Note">
                <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][observation]" value="${escapeHtml(sub.observation ?? '')}" class="ent-input" placeholder="Observation">
                <button type="button" class="ent-btn ent-btn-soft" data-remove-sub>Supprimer</button>
            `;
            w.querySelector('[data-remove-sub]').addEventListener('click', () => { w.remove(); updateScoreSummary(); });
            return w;
        }

        // ── Criterion card ──────────────────────────────────────────────────
        function renderCriterion(path, criterion, idx, options = {}) {
            const article = document.createElement('article');
            article.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
            const titleReadonly   = options.titleReadonly === true;
            const allowRemove     = options.allowRemoveCriterion !== false;
            const ficheId  = criterion.source_fiche_objectif_id ?? '';
            const objectifId = criterion.source_fiche_objectif_objectif_id ?? '';
            const templateId = path === 'subjective_criteres' ? (criterion.id ?? criterion.source_template_id ?? '') : '';

            article.innerHTML = `
                <div class="grid gap-4 md:grid-cols-[1.5fr_1fr]">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Critere</label>
                        <input type="text" name="${path}[${idx}][titre]" value="${escapeHtml(criterion.titre ?? '')}" class="ent-input" placeholder="Titre du critere" ${titleReadonly ? 'readonly' : ''}>
                        <input type="hidden" name="${path}[${idx}][source_template_id]"                value="${escapeHtml(path === 'subjective_criteres' ? templateId : '')}">
                        <input type="hidden" name="${path}[${idx}][source_fiche_objectif_id]"          value="${escapeHtml(path === 'objective_criteres' ? ficheId : '')}">
                        <input type="hidden" name="${path}[${idx}][source_fiche_objectif_objectif_id]" value="${escapeHtml(path === 'objective_criteres' ? objectifId : '')}">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Observation globale</label>
                        <input type="text" name="${path}[${idx}][observation]" value="${escapeHtml(criterion.observation ?? '')}" class="ent-input" placeholder="Observation globale">
                    </div>
                </div>
                <div class="mt-4 space-y-3" data-subcriteria-container></div>
                <div class="mt-4 flex justify-between">
                    <button type="button" class="ent-btn ent-btn-soft" data-add-sub>Ajouter un sous-critere</button>
                    ${allowRemove
                        ? '<button type="button" class="ent-btn ent-btn-soft" data-remove-criterion>Supprimer le critere</button>'
                        : '<span class="text-xs font-medium text-slate-400">Critere issu de la fiche selectionnee</span>'}
                </div>
            `;

            const subContainer = article.querySelector('[data-subcriteria-container]');
            const addSubBtn    = article.querySelector('[data-add-sub]');
            const removeBtn    = article.querySelector('[data-remove-criterion]');
            let subIdx = 0;

            const initialSubs = (criterion.subcriteria && criterion.subcriteria.length > 0)
                ? criterion.subcriteria
                : [{ libelle: '', note: 1, observation: '' }];

            initialSubs.forEach(sub => {
                subContainer.appendChild(renderSubcriterion(path, idx, sub, subIdx));
                subIdx++;
            });

            addSubBtn.addEventListener('click', () => {
                subContainer.appendChild(renderSubcriterion(path, idx, { libelle: '', note: 1, observation: '' }, subIdx));
                subIdx++;
                updateScoreSummary();
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', () => { article.remove(); updateScoreSummary(); });
            }

            return article;
        }

        // ── Objective fiche selector ────────────────────────────────────────
        function populateObjectiveSelector() {
            objectiveSelector.innerHTML = '<option value="">Selectionner une fiche d\'objectif</option>';
            objectiveOptions.forEach(item => {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = `${item.titre} (écheance ${item.date_echeance})`;
                objectiveSelector.appendChild(opt);
            });
            if (objectiveOptions.length === 1) {
                objectiveSelector.value = String(objectiveOptions[0].id);
            }
            renderObjectiveChoices();
        }

        function getSelectedFiche() {
            const id = objectiveSelector.value;
            return objectiveOptions.find(o => String(o.id) === id) || null;
        }

        function renderObjectiveChoices() {
            const selected = getSelectedFiche();
            if (!selected) {
                objectiveChoiceContainer.innerHTML = 'Selectionnez une fiche pour afficher ses objectifs.';
                return;
            }

            const alreadySelected = new Set(
                Array.from(objectiveContainer.querySelectorAll('input[name^="objective_criteres"][name$="[source_fiche_objectif_objectif_id]"]'))
                    .map(i => String(i.value)).filter(Boolean)
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
            objectifs.forEach(item => {
                const row = document.createElement('label');
                row.className = 'flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3';
                row.innerHTML = `
                    <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600"
                           value="${item.source_fiche_objectif_objectif_id}"
                           data-objective-title="${escapeHtml(item.titre ?? '')}"
                           ${alreadySelected.has(String(item.source_fiche_objectif_objectif_id)) ? 'checked disabled' : ''}>
                    <span class="text-sm text-slate-700">${escapeHtml(item.titre ?? '')}</span>
                `;
                list.appendChild(row);
            });
        }

        function addSelectedObjectives() {
            const selected = getSelectedFiche();
            if (!selected) return;

            const checked = Array.from(objectiveChoiceContainer.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)'));
            if (checked.length === 0) return;

            checked.forEach(input => {
                objectiveContainer.appendChild(renderCriterion('objective_criteres', {
                    titre: input.dataset.objectiveTitle || '',
                    source_fiche_objectif_id: selected.id,
                    source_fiche_objectif_objectif_id: input.value,
                    observation: '',
                    subcriteria: [{ libelle: '', note: 1, observation: '' }],
                }, objectiveIndexCounter, { titleReadonly: true, allowRemoveCriterion: true }));
                objectiveIndexCounter++;
            });

            renderObjectiveChoices();
            updateScoreSummary();
        }

        objectiveSelector.addEventListener('change', renderObjectiveChoices);
        addSelectedObjectivesBtn.addEventListener('click', addSelectedObjectives);

        // ── Subjective criteria ──────────────────────────────────────────────
        function renderSubjectiveCriteria(criteria) {
            subjectiveContainer.innerHTML = '';
            criteria.forEach((c, i) => {
                subjectiveContainer.appendChild(renderCriterion('subjective_criteres', c, i));
            });
            subjectiveIndexCounter = criteria.length;
        }

        addSubjectiveBtn.addEventListener('click', () => {
            subjectiveContainer.appendChild(renderCriterion('subjective_criteres', {
                titre: '', observation: '',
                subcriteria: [{ libelle: '', note: 1, observation: '' }],
            }, subjectiveIndexCounter));
            subjectiveIndexCounter++;
            updateScoreSummary();
        });

        // ── Formation / Experience rows ──────────────────────────────────────
        function makeFormationRow(row, idx) {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-200';
            tr.innerHTML = `
                <td class="p-2"><input type="text" name="identification[formations][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td>
                <td class="p-2"><input type="text" name="identification[formations][${idx}][libelle]" value="${escapeHtml(row.libelle ?? '')}" class="ent-input"></td>
                <td class="p-2"><input type="text" name="identification[formations][${idx}][domaine]" value="${escapeHtml(row.domaine ?? '')}" class="ent-input"></td>
                <td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>
            `;
            tr.querySelector('[data-rm]').addEventListener('click', () => {
                tr.remove();
                if (!formationsRows.children.length) addFormationRow({});
            });
            return tr;
        }

        function addFormationRow(row) {
            formationsRows.appendChild(makeFormationRow(row || {}, formationIndexCounter));
            formationIndexCounter++;
        }

        function makeExperienceRow(row, idx) {
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-200';
            tr.innerHTML = `
                <td class="p-2"><input type="text" name="identification[experiences][${idx}][periode]" value="${escapeHtml(row.periode ?? '')}" class="ent-input"></td>
                <td class="p-2"><input type="text" name="identification[experiences][${idx}][poste]" value="${escapeHtml(row.poste ?? '')}" class="ent-input"></td>
                <td class="p-2"><input type="text" name="identification[experiences][${idx}][observations]" value="${escapeHtml(row.observations ?? '')}" class="ent-input"></td>
                <td class="p-2"><button type="button" class="ent-btn ent-btn-soft" data-rm>Supprimer</button></td>
            `;
            tr.querySelector('[data-rm]').addEventListener('click', () => {
                tr.remove();
                if (!experiencesRows.children.length) addExperienceRow({});
            });
            return tr;
        }

        function addExperienceRow(row) {
            experiencesRows.appendChild(makeExperienceRow(row || {}, experienceIndexCounter));
            experienceIndexCounter++;
        }

        addFormationRowBtn.addEventListener('click', () => addFormationRow({}));
        addExperienceRowBtn.addEventListener('click', () => addExperienceRow({}));

        // ── Note input listener ──────────────────────────────────────────────
        document.addEventListener('input', e => {
            if (e.target.matches('input[name^="objective_criteres"][name$="[note]"], input[name^="subjective_criteres"][name$="[note]"]')) {
                updateScoreSummary();
            }
        });

        // ── Initialise ───────────────────────────────────────────────────────
        populateObjectiveSelector();
        (Array.isArray(oldFormations) && oldFormations.length ? oldFormations : [{}]).forEach(r => addFormationRow(r));
        (Array.isArray(oldExperiences) && oldExperiences.length ? oldExperiences : [{}]).forEach(r => addExperienceRow(r));
        renderSubjectiveCriteria(Array.isArray(subjectiveTemplates) ? subjectiveTemplates : []);
        if (Array.isArray(oldObjectiveCriteria) && oldObjectiveCriteria.length) {
            oldObjectiveCriteria.forEach((c, i) => {
                objectiveContainer.appendChild(renderCriterion('objective_criteres', c, i, { titleReadonly: true, allowRemoveCriterion: true }));
                objectiveIndexCounter++;
            });
        }
        updateScoreSummary();
    });
    </script>

    {{-- MM/YYYY date helpers --}}
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
                    let month = parseInt(match[1], 10) + 6;
                    let year  = parseInt(match[2], 10);
                    if (month > 12) { year += Math.floor((month - 1) / 12); month = ((month - 1) % 12) + 1; }
                    fin.value = (month < 10 ? '0' : '') + month + '/' + year;
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

        const dateDebut  = document.getElementById('date_debut');
        const anneeField = document.getElementById('annee_field');
        if (dateDebut && anneeField) {
            dateDebut.addEventListener('input', function () {
                const v = dateDebut.value;
                anneeField.value = /^(0[1-9]|1[0-2])\/(\d{4})$/.test(v) ? v.split('/')[1] : '';
            });
            if (/^(0[1-9]|1[0-2])\/(\d{4})$/.test(dateDebut.value)) {
                anneeField.value = dateDebut.value.split('/')[1];
            }
        }

        // Auto-fill today for signature dates
        function todayISO() { const d = new Date(); return d.toISOString().slice(0, 10); }
        ['date_signature_evalue', 'date_signature_evaluateur'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value) el.value = todayISO();
        });

        // Sync nom_prenom → signature_evalue_nom
        const nomIdent = document.getElementById('identification_nom_prenom');
        const nomSig   = document.getElementById('signature_evalue_nom');
        if (nomIdent && nomSig) {
            const syncNom = () => { if (nomSig.value === '' || nomSig.dataset.autoFilled) { nomSig.value = nomIdent.value; nomSig.dataset.autoFilled = '1'; } };
            nomIdent.addEventListener('input', syncNom);
        }

        // Auto-fill today date for date_evaluation
        const dateEval = document.getElementById('identification_date_evaluation');
        if (dateEval && !dateEval.value) {
            const today = new Date();
            dateEval.value = String(today.getDate()).padStart(2,'0')+'/'+String(today.getMonth()+1).padStart(2,'0')+'/'+today.getFullYear();
        }
    });
    </script>
@endpush
