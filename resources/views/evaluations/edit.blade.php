{{--
    evaluations/edit.blade.php — Formulaire de modification d'évaluation (unifié)

    Variables requises :
      $layout          — layout à étendre (ex: 'layouts.chef')
      $heroSubtitle    — texte de contexte au-dessus du h1
      $backUrl         — URL du bouton Retour
      $formAction      — action du formulaire PUT (route de update)
      $evalueLabel     — libellé évalué pour la section Signatures (ex: 'Agent')
      $evaluateurLabel — libellé évaluateur pour la section Signatures (ex: 'Chef de Caisse')
      $evaluation      — Evaluation (brouillon)
      $ident           — EvaluationIdentification|null
      $openAnnee       — Annee ouverte (ou null)
      $openSemestre    — Semestre ouvert (ou null)
      $objectiveOptions         — fiches d'objectifs disponibles (array)
      $existingObjectiveCriteria  — critères objectifs existants (array sérialisé)
      $existingSubjectiveCriteria — critères subjectifs existants (array sérialisé)
      $oldFormations            — formations old() ou $ident->formations
      $oldExperiences           — expériences old() ou $ident->experiences
      $cibleNom        — nom affiché de la cible évaluée
      $cibleType       — type/rôle affiché de la cible évaluée
--}}
@extends($layout ?? 'layouts.app')

@section('title', 'Modifier l\'évaluation | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">{{ $heroSubtitle ?? '' }}</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Modifier le brouillon</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">{{ $cibleNom ?? '' }}{{ ($cibleType ?? '') ? ' — ' . ($cibleType ?? '') : '' }}</p>
            </div>
            <a href="{{ $backUrl ?? url()->previous() }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <i class="fas fa-triangle-exclamation mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ $formAction }}"
              class="flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_300px] lg:items-start lg:gap-6">
            @csrf
            @method('PUT')

            {{-- Sentinelles --}}
            <input type="hidden" name="_renvoyer" id="_renvoyer_flag" value="0">
            <input type="hidden" name="_subjective_criteres_submitted" value="1">

            {{-- ════════════════════════ LEFT COLUMN ════════════════════════ --}}
            <div class="flex flex-col gap-5">

                {{-- ── Card 1 : Identification et période ────────────────── --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">1</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Identification et période</p>
                            <p class="text-[11px] text-slate-400">La cible est fixée. Vous pouvez ajuster les informations d'identification.</p>
                        </div>
                    </div>
                    <div class="space-y-6 px-6 py-6">

                        {{-- Cible fixe --}}
                        <div class="relative overflow-hidden rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                            <div class="absolute inset-y-0 left-0 w-1 bg-cyan-400"></div>
                            <div class="pl-2">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-cyan-700">{{ $cibleType ?? 'Évalué' }}</p>
                                <p class="mt-1.5 text-base font-black text-slate-900">{{ $cibleNom ?? '—' }}</p>
                            </div>
                        </div>

                        {{-- I. Identification --}}
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">I. Identification de l'évalué</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">Vérifiez et complétez les informations.</p>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Année</label>
                                <input type="text" value="{{ $openAnnee?->annee ?? ($ident?->annee ?? now()->year) }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                <input type="text"
                                       value="{{ isset($openSemestre) && $openSemestre ? 'Semestre '.$openSemestre->numero : ($ident?->semestre ?? '—') }}"
                                       name="identification[semestre]" class="ent-input bg-slate-50 text-slate-600" readonly>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de l'évaluation</label>
                                <input id="identification_date_evaluation" name="identification[date_evaluation]" type="text"
                                       value="{{ old('identification.date_evaluation', $ident?->date_evaluation?->format('d/m/Y') ?? '') }}"
                                       class="ent-input" placeholder="JJ/MM/YYYY">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_matricule" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Matricule</label>
                                <input id="identification_matricule" name="identification[matricule]" type="text"
                                       value="{{ old('identification.matricule', $ident?->matricule ?? '') }}"
                                       class="ent-input bg-slate-50 text-slate-600" readonly placeholder="Renseigné automatiquement">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_grade" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Grade <span class="text-red-500">*</span></label>
                                <input id="identification_grade" name="identification[grade]" type="text"
                                       value="{{ old('identification.grade', $ident?->grade ?? '') }}"
                                       class="ent-input" placeholder="Grade de l'évalué" required>
                            </div>
                            <div class="space-y-2">
                                <label for="identification_emploi" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Emploi / Fonction</label>
                                <input id="identification_emploi" name="identification[emploi]" type="text"
                                       value="{{ old('identification.emploi', $ident?->emploi ?? '') }}"
                                       class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prénom</label>
                                <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text"
                                       value="{{ old('identification.nom_prenom', $ident?->nom_prenom ?? '') }}"
                                       class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entité</label>
                                <input id="identification_direction" name="identification[direction]" type="text"
                                       value="{{ old('identification.direction', $ident?->direction ?? '') }}"
                                       class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction / Service</label>
                                <input id="identification_direction_service" name="identification[direction_service]" type="text"
                                       value="{{ old('identification.direction_service', $ident?->direction_service ?? '') }}"
                                       class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label for="identification_date_prise_fonction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de prise de fonction <span class="text-red-500">*</span></label>
                                <input id="identification_date_prise_fonction" name="identification[date_prise_fonction]" type="text"
                                       value="{{ old('identification.date_prise_fonction', $ident?->date_prise_fonction?->format('d/m/Y') ?? '') }}"
                                       class="ent-input" placeholder="JJ/MM/YYYY" required>
                            </div>
                        </div>

                        {{-- II. Formations & III. Expériences --}}
                        <div class="grid gap-6 xl:grid-cols-2">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">II. Formation, stage et séminaires</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">Renseignez les formations de l'année en cours.</p>
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
                                    <button id="add-formation-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">III. Expérience professionnelle</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">Renseignez les principales expériences.</p>
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
                                    <button id="add-experience-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Card 2 : Critères objectifs ────────────────────────── --}}
                <div id="objective-section" class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">2</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Critères objectifs</p>
                            <p class="text-[11px] text-slate-400">Choisissez une fiche d'objectifs pour ajouter des critères, ou modifiez les existants. Barème : 1 à 5.</p>
                        </div>
                    </div>
                    <div class="space-y-5 px-6 py-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-500">Sélectionner une fiche d'objectifs existante pour pré-remplir les critères.</p>
                            <div class="flex gap-2">
                                <select id="objective-fiche-selector" class="ent-select min-w-52">
                                    <option value="">Sélectionner une fiche d'objectif</option>
                                </select>
                                <button id="add-selected-objectives" type="button" class="ent-btn ent-btn-soft">Ajouter les objectifs</button>
                            </div>
                        </div>
                        <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                            Sélectionnez une fiche pour afficher ses objectifs.
                        </div>
                        <div id="objective-criteria-container" class="space-y-5"></div>
                    </div>
                </div>

                {{-- ── Card 3 : Critères subjectifs ───────────────────────── --}}
                <div id="subjective-section" class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">3</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Critères subjectifs</p>
                                <p class="text-[11px] text-slate-400">Renseignez les sous-critères comportementaux. Barème : 1 à 5.</p>
                            </div>
                        </div>
                        <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft shrink-0">Ajouter un critère</button>
                    </div>
                    <div class="px-6 py-6">
                        <div id="subjective-criteria-container" class="space-y-5"></div>
                    </div>
                </div>

                {{-- ── Card 4 : Synthèse des notes ────────────────────────── --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">4</span>
                        <div>
                            <p class="text-sm font-black text-slate-900">Synthèse des notes</p>
                            <p class="text-[11px] text-slate-400">Calcul automatique : objectifs ×0,75 + subjectifs ×0,25, puis ×2 = note /10.</p>
                        </div>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                            {{-- Moyenne objectifs --}}
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-slate-300"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500">Moy. pondérée objectifs</p>
                                    <p id="summary-moyenne-objectifs" class="mt-2 text-2xl font-black leading-none text-slate-900">0,00</p>
                                </div>
                            </div>
                            {{-- Note objectifs --}}
                            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-700">Note critères objectifs</p>
                                    <p id="summary-note-objectifs" class="mt-2 text-2xl font-black leading-none text-emerald-700">0,00</p>
                                </div>
                            </div>
                            {{-- Moyenne subjectifs --}}
                            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-slate-300"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500">Moy. pondérée subjectifs</p>
                                    <p id="summary-moyenne-subjectifs" class="mt-2 text-2xl font-black leading-none text-slate-900">0,00</p>
                                </div>
                            </div>
                            {{-- Note subjectifs --}}
                            <div class="relative overflow-hidden rounded-2xl border border-sky-100 bg-sky-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-sky-700">Note critères subjectifs</p>
                                    <p id="summary-note-subjectifs" class="mt-2 text-2xl font-black leading-none text-sky-700">0,00</p>
                                </div>
                            </div>
                            {{-- Note totale --}}
                            <div class="relative overflow-hidden rounded-2xl border border-violet-200 bg-violet-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-violet-600"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-violet-700">Note totale</p>
                                    <p id="summary-note-finale" class="mt-2 text-3xl font-black leading-none text-violet-700">0,00</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Card 5 : Plan d'amélioration ───────────────────────── --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">5</span>
                        <p class="text-sm font-black text-slate-900">Plan d'amélioration</p>
                    </div>
                    <div class="space-y-5 px-6 py-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="points_a_ameliorer" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points à améliorer</label>
                                <textarea id="points_a_ameliorer" name="points_a_ameliorer" rows="8" class="ent-input">{{ old('points_a_ameliorer', $evaluation->points_a_ameliorer ?? '') }}</textarea>
                            </div>
                            <div class="space-y-2">
                                <label for="strategies_amelioration" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Stratégies d'amélioration</label>
                                <textarea id="strategies_amelioration" name="strategies_amelioration" rows="8" class="ent-input">{{ old('strategies_amelioration', $evaluation->strategies_amelioration ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="commentaire" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'évaluateur</label>
                            <textarea id="commentaire" name="commentaire" rows="5" class="ent-input">{{ old('commentaire', $evaluation->commentaire ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ── Card 6 : Signatures ────────────────────────────────── --}}
                <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">6</span>
                        <p class="text-sm font-black text-slate-900">Signatures</p>
                    </div>
                    <div class="px-6 py-6">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-3">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évalué(e) — {{ $evalueLabel ?? 'Évalué' }}
                                </label>
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text"
                                       value="{{ old('signature_evalue_nom', $evaluation->signature_evalue_nom ?? '') }}"
                                       class="ent-input" placeholder="Nom de l'évalué(e)">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date"
                                       value="{{ old('date_signature_evalue', $evaluation->date_signature_evalue ? \Carbon\Carbon::parse($evaluation->date_signature_evalue)->format('Y-m-d') : '') }}"
                                       class="ent-input">
                            </div>
                            <div class="space-y-3">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évaluateur — {{ $evaluateurLabel ?? 'Évaluateur' }}
                                </label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text"
                                       value="{{ old('signature_evaluateur_nom', $evaluation->signature_evaluateur_nom ?? auth()->user()?->name ?? '') }}"
                                       class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date"
                                       value="{{ old('date_signature_evaluateur', $evaluation->date_signature_evaluateur ? \Carbon\Carbon::parse($evaluation->date_signature_evaluateur)->format('Y-m-d') : '') }}"
                                       class="ent-input">
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- /LEFT --}}

            {{-- ════════════════════════ RIGHT SIDEBAR ══════════════════════ --}}
            <div class="sticky top-4 flex flex-col gap-4">

                {{-- Info card --}}
                <div class="overflow-hidden rounded-[20px] bg-gradient-to-b from-violet-700 to-purple-700 shadow-lg">
                    <div class="px-5 py-5">
                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-violet-300">{{ $heroSubtitle ?? 'Évaluation' }}</p>
                        <p class="mt-1 text-sm font-black leading-snug text-white">{{ $cibleNom ?? '—' }}</p>
                        @if($cibleType ?? null)
                            <p class="mt-0.5 text-[11px] text-violet-200">{{ $cibleType }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($openAnnee ?? null)
                                <span class="inline-flex items-center gap-1 rounded-lg bg-white/15 px-2.5 py-1 text-[11px] font-bold text-violet-100">
                                    <i class="fas fa-calendar-alt text-[9px]"></i> {{ $openAnnee->annee }}
                                </span>
                            @endif
                            @if($openSemestre ?? null)
                                <span class="inline-flex items-center gap-1 rounded-lg bg-white/15 px-2.5 py-1 text-[11px] font-bold text-violet-100">
                                    <i class="fas fa-layer-group text-[9px]"></i> S{{ $openSemestre->numero }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-3 rounded-[20px] bg-white p-4 shadow-sm ring-1 ring-slate-100">
                    <button type="submit"
                            class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2">
                        <i class="fas fa-save mr-1.5 text-[11px]"></i> Enregistrer les modifications
                    </button>
                    <button type="button"
                            onclick="document.getElementById('_renvoyer_flag').value='1'; this.closest('form').submit()"
                            class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <i class="fas fa-paper-plane mr-1.5 text-[11px]"></i> Enregistrer &amp; Renvoyer
                    </button>
                    <a href="{{ $backUrl ?? url()->previous() }}"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-100">
                        Annuler
                    </a>
                    @if (isset($destroyRoute) && $evaluation->statut === 'brouillon')
                    <button type="button"
                            onclick="if(confirm('Supprimer définitivement ce brouillon ?')) document.getElementById('eval-destroy-form').submit()"
                            class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-600 transition hover:bg-rose-100">
                        <i class="fas fa-trash mr-1.5 text-[11px]"></i> Supprimer le brouillon
                    </button>
                    @endif
                </div>

            </div>{{-- /RIGHT --}}

        </form>

        {{-- Form suppression séparée — hors de la form principale pour éviter le conflit _method --}}
        @if (isset($destroyRoute) && $evaluation->statut === 'brouillon')
        <form id="eval-destroy-form" method="POST" action="{{ route($destroyRoute, $evaluation) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script id="eval-objective-options"    type="application/json">@json($objectiveOptions ?? [])</script>
<script id="eval-subjective-templates" type="application/json">@json(old('subjective_criteres') ?? $existingSubjectiveCriteria ?? [])</script>
<script id="eval-objective-old"        type="application/json">@json(old('objective_criteres') ?? $existingObjectiveCriteria ?? [])</script>
<script id="eval-formations-old"       type="application/json">@json($oldFormations ?? null)</script>
<script id="eval-experiences-old"      type="application/json">@json($oldExperiences ?? [])</script>
<script id="eval-agents-data"          type="application/json">[]</script>
<script id="eval-prefilled-agent"      type="application/json">null</script>

@include('evaluations.partials._eval-js')
@endpush
