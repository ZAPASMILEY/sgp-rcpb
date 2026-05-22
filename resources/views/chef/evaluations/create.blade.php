{{--
    ──────────────────────────────────────────────────────────────────────────
    chef/evaluations/create.blade.php — Formulaire de création d'évaluation
    ──────────────────────────────────────────────────────────────────────────

    Évalue un Agent (subordonné du chef), non une structure.
    Structure identique à directeur/evaluations/create.blade.php
    mais le sélecteur cible des Agents et non des Services/Agences/Caisses.

    Variables reçues de ChefEvaluationController::create() :
      $ctx                      — ChefEntity
      $agents                   — Collection des agents subordonnés
      $selectedAgent            — Agent pré-sélectionné (ou null)
      $objectiveOptions         — Fiches d'objectifs de l'agent sélectionné
      $subjectiveTemplates      — Templates de critères subjectifs actifs
      $oldFormations            — Anciennes valeurs formations (après erreur)
      $oldExperiences           — Anciennes valeurs expériences (après erreur)
      $agentsJson               — Données JSON des agents pour auto-remplissage
      $prefilledNomPrenom       — Nom/prénom pré-rempli
      $prefilledEmploi          — Emploi pré-rempli
      $prefilledDirectionService — Structure pré-remplie
      $displayYear              — Année d'affichage initiale
      $entiteNom                — Nom de la structure du chef
    ──────────────────────────────────────────────────────────────────────────
--}}
@extends('layouts.chef')

@section('title', 'Nouvelle évaluation | ' . config('app.name', 'SGP-RCPB'))

@php
    {{-- Récupère le service/agent pré-sélectionné selon la valeur old() --}}
    $resolvedAgentId = (int) old('agent_id', $selectedAgent?->id ?? 0);
    $resolvedAgent   = $agents->firstWhere('id', $resolvedAgentId) ?? $selectedAgent;
    $lockAgent       = $agents->count() === 1 || $selectedAgent !== null;
@endphp

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex-col gap-6">

            {{-- ── En-tête de page ─────────────────────────────────────────── --}}
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                            Espace Chef / {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                        </p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouvelle évaluation</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            Renseignez les critères objectifs, puis les critères subjectifs et le plan d'amélioration.
                        </p>
                    </div>
                    <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </header>

            {{-- ── Formulaire ──────────────────────────────────────────────── --}}
            <section class="admin-panel px-6 py-6 lg:px-8">

                {{-- Message d'erreur global --}}
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('chef.evaluations.store') }}" class="space-y-8">
                    @csrf

                    {{-- ══════════════════════════════════════════════════════════
                         Section 1 : Identification et période
                    ══════════════════════════════════════════════════════════ --}}
                    <section class="space-y-6">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">1. Identification et période</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Sélectionnez l'agent à évaluer, la période concernée et vérifiez les informations.
                            </p>
                        </div>

                        {{-- Sélection de l'agent évalué --}}
                        @if ($lockAgent && $resolvedAgent)
                            {{-- Agent unique ou pré-sélectionné : affichage en lecture seule --}}
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Agent évalué</p>
                                <p class="mt-2 text-base font-black text-slate-900">
                                    {{ trim($resolvedAgent->prenom . ' ' . $resolvedAgent->nom) }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">{{ $resolvedAgent->role ?? 'Agent' }}</p>
                                <input type="hidden" name="agent_id" value="{{ $resolvedAgent->id }}">
                            </div>
                        @else
                            {{-- Plusieurs agents : liste déroulante avec auto-remplissage --}}
                            <div class="space-y-2">
                                <label for="agent_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Agent évalué
                                </label>
                                <select id="agent_id" name="agent_id" class="ent-select" required>
                                    <option value="">— Sélectionner un agent —</option>
                                    @foreach ($agents as $ag)
                                        <option value="{{ $ag->id }}" @selected($resolvedAgentId === (int) $ag->id)>
                                            {{ trim($ag->prenom . ' ' . $ag->nom) }} — {{ $ag->role ?? 'Agent' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <p class="text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif



                        {{-- Identification de l'évalué --}}
                        <div>
                            <h3 class="border-t border-slate-200 pt-8 text-base font-black text-slate-900">
                                I. Identification de l'évalué
                            </h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Les champs grisés sont remplis automatiquement lors de la sélection de l'agent.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Année</label>
                                <input type="text" value="{{ $openAnnee?->annee ?? now()->year }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                <input type="text" value="{{ $openSemestre ? 'Semestre '.$openSemestre->numero : '—' }}"
                                       name="identification[semestre]"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Date de l'évaluation
                                </label>
                                <input id="identification_date_evaluation" name="identification[date_evaluation]" type="text"
                                       value="{{ old('identification.date_evaluation') }}"
                                       class="ent-input bg-slate-50 text-slate-600" placeholder="JJ/MM/YYYY" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_matricule" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Matricule
                                </label>
                                <input id="identification_matricule" name="identification[matricule]" type="text"
                                       value="{{ $prefilledMatricule ?? old('identification.matricule', '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly placeholder="Renseigné automatiquement">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_grade" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Grade <span class="text-red-500">*</span>
                                </label>
                                <input id="identification_grade" name="identification[grade]" type="text"
                                       value="{{ old('identification.grade') }}"
                                       class="ent-input" placeholder="Grade de l'évalué" required>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_emploi" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Emploi / Fonction
                                </label>
                                <input id="identification_emploi" name="identification[emploi]" type="text"
                                       value="{{ old('identification.emploi', $prefilledEmploi ?? '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Nom et prénom
                                </label>
                                <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text"
                                       value="{{ old('identification.nom_prenom', $prefilledNomPrenom ?? '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Entité
                                </label>
                                <input id="identification_direction" name="identification[direction]" type="text"
                                       value="{{ old('identification.direction', $entiteNom ?? '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Direction / Service
                                </label>
                                <input id="identification_direction_service" name="identification[direction_service]" type="text"
                                       value="{{ old('identification.direction_service', $prefilledDirectionService ?? '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                        </div>

                        {{-- Formations et Expériences (tableaux dynamiques) --}}
                        <div class="grid gap-6 xl:grid-cols-2">
                            <div class="space-y-3">
                                <div>
                                    <h3 class="text-base font-black text-slate-900">II. Formation, stage et séminaires</h3>
                                    <p class="mt-1 text-sm text-slate-500">Renseignez les formations de l'année en cours.</p>
                                </div>
                                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                                    <table class="min-w-full text-sm text-slate-700">
                                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                            <tr>
                                                <th class="px-3 py-3 text-left">Période</th>
                                                <th class="px-3 py-3 text-left">Formation / diplômes</th>
                                                <th class="px-3 py-3 text-left">Domaines</th>
                                                <th class="px-3 py-3 text-left">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="formations-rows"></tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end">
                                    <button id="add-formation-row" type="button" class="ent-btn ent-btn-soft">
                                        Ajouter une ligne
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <h3 class="text-base font-black text-slate-900">III. Expérience professionnelle</h3>
                                    <p class="mt-1 text-sm text-slate-500">Renseignez les principales expériences.</p>
                                </div>
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
                                    <button id="add-experience-row" type="button" class="ent-btn ent-btn-soft">
                                        Ajouter une ligne
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- ══════════════════════════════════════════════════════════
                         Section 3 : Critères objectifs
                         Tirés des fiches d'objectifs acceptées de l'agent
                    ══════════════════════════════════════════════════════════ --}}
                    <section id="objective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">3. Critères objectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Choisissez une fiche d'objectifs, puis renseignez les sous-critères. Barème : 1 à 5.
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <select id="objective-fiche-selector" class="ent-select min-w-64">
                                    <option value="">Sélectionner une fiche d'objectif</option>
                                </select>
                                <button id="add-selected-objectives" type="button" class="ent-btn ent-btn-soft">
                                    Ajouter les objectifs
                                </button>
                            </div>
                        </div>

                        <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Sélectionnez une fiche pour afficher ses objectifs.
                        </div>
                        <div id="objective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- ══════════════════════════════════════════════════════════
                         Section 4 : Critères subjectifs
                         Comportement, attitudes, compétences transversales
                    ══════════════════════════════════════════════════════════ --}}
                    <section id="subjective-section" class="space-y-5 border-t border-slate-200 pt-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">4. Critères subjectifs</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Renseignez les sous-critères comportementaux. Barème : 1 à 5.
                                </p>
                            </div>
                            <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft">
                                Ajouter un critère
                            </button>
                        </div>
                        <div id="subjective-criteria-container" class="space-y-5"></div>
                    </section>

                    {{-- ══════════════════════════════════════════════════════════
                         Section 5 : Synthèse des notes (calculée en JS)
                    ══════════════════════════════════════════════════════════ --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">5. Synthèse des notes</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Calcul automatique : objectifs ×0,75 + subjectifs ×0,25, puis ×2 = note /10.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne pondérée objectifs</p>
                                <p id="summary-moyenne-objectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note critères objectifs</p>
                                <p id="summary-note-objectifs" class="mt-3 text-2xl font-black text-emerald-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moyenne pondérée subjectifs</p>
                                <p id="summary-moyenne-subjectifs" class="mt-3 text-2xl font-black text-slate-900">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note critères subjectifs</p>
                                <p id="summary-note-subjectifs" class="mt-3 text-2xl font-black text-sky-700">0,00</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700">Note totale d'évaluation</p>
                                <p id="summary-note-finale" class="mt-3 text-3xl font-black text-emerald-700">0,00</p>
                            </div>
                        </div>
                    </section>

                    {{-- ══════════════════════════════════════════════════════════
                         Section 6 : Plan d'amélioration
                    ══════════════════════════════════════════════════════════ --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <h2 class="text-lg font-black text-slate-900">6. Plan d'amélioration</h2>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="points_a_ameliorer" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Points à améliorer
                                </label>
                                <textarea id="points_a_ameliorer" name="points_a_ameliorer" rows="8" class="ent-input">{{ old('points_a_ameliorer') }}</textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="strategies_amelioration" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Stratégies d'amélioration
                                </label>
                                <textarea id="strategies_amelioration" name="strategies_amelioration" rows="8" class="ent-input">{{ old('strategies_amelioration') }}</textarea>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                Commentaire de l'évaluateur
                            </label>
                            <textarea id="commentaire" name="commentaire" rows="5" class="ent-input">{{ old('commentaire') }}</textarea>
                        </div>
                    </section>

                    {{-- ══════════════════════════════════════════════════════════
                         Section 7 : Signatures
                    ══════════════════════════════════════════════════════════ --}}
                    <section class="space-y-5 border-t border-slate-200 pt-8">
                        <h2 class="text-lg font-black text-slate-900">7. Signatures</h2>
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évalué (Agent)
                                </label>
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text"
                                       value="{{ old('signature_evalue_nom') }}" class="ent-input"
                                       placeholder="Nom de l'agent évalué">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date"
                                       value="{{ old('date_signature_evalue') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évaluateur ({{ $ctx->getRoleLabel() }})
                                </label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text"
                                       value="{{ old('signature_evaluateur_nom', auth()->user()->name ?? '') }}" class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date"
                                       value="{{ old('date_signature_evaluateur') }}" class="ent-input">
                            </div>
                        </div>
                    </section>

                    {{-- Boutons de soumission --}}
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                        <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Annuler</a>
                        <button type="submit" class="ent-btn ent-btn-primary">Créer l'évaluation (brouillon)</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Données JSON pour les scripts JS --}}
    <script id="chef-eval-objective-options" type="application/json">@json($objectiveOptions ?? [])</script>
    <script id="chef-eval-subjective-templates" type="application/json">@json(old('subjective_criteres', $subjectiveTemplates ?? []))</script>
    <script id="chef-eval-objective-old" type="application/json">@json(old('objective_criteres', []))</script>
    <script id="chef-eval-formations-old" type="application/json">@json($oldFormations)</script>
    <script id="chef-eval-experiences-old" type="application/json">@json($oldExperiences ?? [['periode'=>'','poste'=>'','observations'=>'']])</script>
    <script id="chef-eval-agents-data" type="application/json">@json($agentsJson ?? [])</script>
    <script id="chef-eval-prefilled-agent" type="application/json">@json($prefilledAgentId ?? null)</script>

    {{-- ══════════════════════════════════════════════════════════════════════
         Script principal — identique à directeur/evaluations/create.blade.php
         Gestion des critères, formations, expériences et calcul des scores
    ══════════════════════════════════════════════════════════════════════════ --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const objectiveOptions     = JSON.parse(document.getElementById('chef-eval-objective-options').textContent || '[]');
        const subjectiveTemplates  = JSON.parse(document.getElementById('chef-eval-subjective-templates').textContent || '[]');
        const oldObjectiveCriteria = JSON.parse(document.getElementById('chef-eval-objective-old').textContent || '[]');
        const oldFormations        = JSON.parse(document.getElementById('chef-eval-formations-old').textContent || 'null');
        const oldExperiences       = JSON.parse(document.getElementById('chef-eval-experiences-old').textContent || '[]');
        const prefilledAgentId     = JSON.parse(document.getElementById('chef-eval-prefilled-agent').textContent || 'null');

        let subjectiveIndexCounter = 0;
        let objectiveIndexCounter  = 0;
        let formationIndexCounter  = 0;
        let experienceIndexCounter = 0;

        const objectiveSelector        = document.getElementById('objective-fiche-selector');
        const addSelectedObjectivesBtn = document.getElementById('add-selected-objectives');
        const objectiveChoiceContainer = document.getElementById('objective-choice-container');
        const objectiveContainer       = document.getElementById('objective-criteria-container');
        const subjectiveContainer      = document.getElementById('subjective-criteria-container');
        const addSubjectiveBtn         = document.getElementById('add-subjective-criterion');
        const formationsRows           = document.getElementById('formations-rows');
        const experiencesRows          = document.getElementById('experiences-rows');
        const addFormationRowBtn       = document.getElementById('add-formation-row');
        const addExperienceRowBtn      = document.getElementById('add-experience-row');
        const summaryMoyenneObjectifs  = document.getElementById('summary-moyenne-objectifs');
        const summaryNoteObjectifs     = document.getElementById('summary-note-objectifs');
        const summaryMoyenneSubjectifs = document.getElementById('summary-moyenne-subjectifs');
        const summaryNoteSubjectifs    = document.getElementById('summary-note-subjectifs');
        const summaryNoteFinale        = document.getElementById('summary-note-finale');

        // Formate un nombre avec une virgule comme séparateur décimal
        function formatScore(v) { return Number(v || 0).toFixed(2).replace('.', ','); }

        // Recalcule les scores à chaque modification de note
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

        // Échappe les caractères HTML pour prévenir l'injection
        function escapeHtml(v) {
            return String(v ?? '')
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        // Crée un sous-critère (ligne de note avec libellé et observation)
        function renderSubcriterion(path, parentIdx, sub, subIdx) {
            const w = document.createElement('div');
            w.className = 'grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 grid-cols-[1.6fr_100px_1fr_auto]';
            w.innerHTML = `
                <input type="hidden" name="${path}[${parentIdx}][subcriteria][${subIdx}][source_fiche_objectif_objectif_id]" value="${sub.source_fiche_objectif_objectif_id ?? ''}">
                <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][libelle]" value="${escapeHtml(sub.libelle ?? '')}" class="ent-input" placeholder="Sous-critère">
                <input type="number" name="${path}[${parentIdx}][subcriteria][${subIdx}][note]" value="${sub.note ?? 1}" min="1" max="5" step="1" class="ent-input" placeholder="Note" oninput="this.value=Math.min(Math.max(parseInt(this.value)||1,1),5)">
                <input type="text"   name="${path}[${parentIdx}][subcriteria][${subIdx}][observation]" value="${escapeHtml(sub.observation ?? '')}" class="ent-input" placeholder="Observation">
                <button type="button" class="ent-btn ent-btn-soft" data-remove-sub>Supprimer</button>
            `;
            w.querySelector('[data-remove-sub]').addEventListener('click', () => { w.remove(); updateScoreSummary(); });
            return w;
        }

        // Crée un critère (groupe : titre + sous-critères)
        function renderCriterion(path, criterion, idx, options = {}) {
            const article = document.createElement('article');
            article.className = 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm';
            const titleReadonly = options.titleReadonly === true;
            const allowRemove   = options.allowRemoveCriterion !== false;
            const ficheId    = criterion.source_fiche_objectif_id ?? '';
            const objectifId = criterion.source_fiche_objectif_objectif_id ?? '';
            const templateId = path === 'subjective_criteres' ? (criterion.id ?? criterion.source_template_id ?? '') : '';

            article.innerHTML = `
                <div class="grid gap-4 md:grid-cols-[1.5fr_1fr]">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Critère</label>
                        <input type="text" name="${path}[${idx}][titre]" value="${escapeHtml(criterion.titre ?? '')}" class="ent-input" placeholder="Titre du critère" ${titleReadonly ? 'readonly' : ''}>
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
                    <button type="button" class="ent-btn ent-btn-soft" data-add-sub>Ajouter un sous-critère</button>
                    ${allowRemove
                        ? '<button type="button" class="ent-btn ent-btn-soft" data-remove-criterion>Supprimer le critère</button>'
                        : '<span class="text-xs font-medium text-slate-400">Critère issu de la fiche sélectionnée</span>'}
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

        // Peuple le sélecteur de fiches d'objectifs
        function populateObjectiveSelector() {
            objectiveSelector.innerHTML = '<option value="">Sélectionner une fiche d\'objectif</option>';
            objectiveOptions.forEach(item => {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = `${item.titre} (échéance ${item.date_echeance})`;
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
                objectiveChoiceContainer.innerHTML = 'Sélectionnez une fiche pour afficher ses objectifs.';
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
                    <p class="text-sm font-semibold text-slate-800">Objectifs disponibles dans « ${escapeHtml(selected.titre)} »</p>
                    <div class="space-y-2" data-objective-choice-list></div>
                </div>
            `;

            const list = objectiveChoiceContainer.querySelector('[data-objective-choice-list]');
            objectifs.forEach(item => {
                const row = document.createElement('label');
                row.className = 'flex items-start gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3';
                row.innerHTML = `
                    <input type="checkbox" class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600"
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

        // Ligne de formation dynamique
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

        // ── Auto-remplissage formations depuis la base ────────────────────────
        window.sgpFillFormations = function (agentId) {
            if (!agentId) return;
            fetch('/formations/agent/' + agentId, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (formations) {
                formationsRows.innerHTML = '';
                formationIndexCounter = 0;
                (formations.length ? formations : [{}]).forEach(function (f) { addFormationRow(f); });
            })
            .catch(function () {});
        };

        // Ligne d'expérience dynamique
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

        // Recalcul live à chaque saisie de note
        document.addEventListener('input', e => {
            if (e.target.matches('input[name^="objective_criteres"][name$="[note]"], input[name^="subjective_criteres"][name$="[note]"]')) {
                updateScoreSummary();
            }
        });

        // Initialisation
        populateObjectiveSelector();
        (function () {
            var hasOld = Array.isArray(oldFormations) && oldFormations.some(function (f) { return f && f.libelle && String(f.libelle).trim(); });
            if (hasOld) { oldFormations.forEach(function (r) { addFormationRow(r || {}); }); }
            else if (prefilledAgentId) { window.sgpFillFormations(prefilledAgentId); }
            else { addFormationRow({}); }
        })();
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

    {{-- Auto-remplissage des champs d'identification lors du changement d'agent --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const agentsData = JSON.parse(document.getElementById('chef-eval-agents-data')?.textContent || '[]');

        const fieldNomPrenom       = document.getElementById('identification_nom_prenom');
        const fieldEmploi          = document.getElementById('identification_emploi');
        const fieldDirection       = document.getElementById('identification_direction');
        const fieldDirectionSvc    = document.getElementById('identification_direction_service');
        const fieldSignatureEvalue = document.getElementById('signature_evalue_nom');

        function fillIdent(data) {
            if (!data) return;
            if (fieldNomPrenom)    { fieldNomPrenom.value    = data.nom_prenom        ?? ''; }
            if (fieldEmploi)       { fieldEmploi.value       = data.emploi            ?? ''; }
            if (fieldDirection)    { fieldDirection.value    = data.entite_nom        ?? ''; }
            if (fieldDirectionSvc) { fieldDirectionSvc.value = data.direction_service ?? ''; }
            const fieldMatricule = document.getElementById('identification_matricule');
            if (fieldMatricule) fieldMatricule.value = data.matricule ?? '';
            // Sync vers le champ de signature de l'évalué
            if (fieldSignatureEvalue && (!fieldSignatureEvalue.value || fieldSignatureEvalue.dataset.autoFilled)) {
                fieldSignatureEvalue.value = data.nom_prenom ?? '';
                fieldSignatureEvalue.dataset.autoFilled = '1';
            }
        }

        const selAgent = document.querySelector('select[name="agent_id"]');
        if (selAgent) {
            selAgent.addEventListener('change', function () {
                const id   = parseInt(this.value, 10);
                const data = agentsData.find(a => a.id === id) || null;
                fillIdent(data);
                if (id && window.sgpFillFormations) window.sgpFillFormations(id);
            });
        }
    });
    </script>

    {{-- Helpers pour les champs date et synchronisations --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Pré-remplissage des dates de signature avec la date du jour
        function todayISO() { const d = new Date(); return d.toISOString().slice(0, 10); }
        ['date_signature_evalue', 'date_signature_evaluateur'].forEach(id => {
            const el = document.getElementById(id);
            if (el && !el.value) el.value = todayISO();
        });

        // Synchronisation initiale du nom de l'évalué vers la signature
        const nomIdent = document.getElementById('identification_nom_prenom');
        const nomSig   = document.getElementById('signature_evalue_nom');
        if (nomIdent && nomSig && nomIdent.value && !nomSig.value) {
            nomSig.value = nomIdent.value;
            nomSig.dataset.autoFilled = '1';
        }

        // Pré-remplissage de la date d'évaluation (JJ/MM/YYYY)
        const dateEval = document.getElementById('identification_date_evaluation');
        if (dateEval && !dateEval.value) {
            const today = new Date();
            dateEval.value = String(today.getDate()).padStart(2,'0') + '/'
                + String(today.getMonth() + 1).padStart(2,'0') + '/'
                + today.getFullYear();
        }
    });
    </script>
@endpush
