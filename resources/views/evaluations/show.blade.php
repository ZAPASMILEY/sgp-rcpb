{{--
    ──────────────────────────────────────────────────────────────────────────
    evaluations/show.blade.php — Vue unifiée pour toutes les évaluations
    ──────────────────────────────────────────────────────────────────────────

    Variables universelles :
      $evaluation         — Evaluation (avec relations chargées)
      $objectiveCriteria  — Collection<EvaluationCritere> (type objectif)
      $subjectiveCriteria — Collection<EvaluationCritere> (type subjectif)
      $note               — float (note finale)
      $mention            — string (Excellent|Bien|Passable|Insuffisant)
      $cibleLabel         — string (nom de l'évalué)
      $cibleType          — string (rôle/type de l'évalué)
      $statusLabel        — string
      $statusClass        — classes Tailwind du badge
      $ident              — EvaluationIdentification|null
      $layout             — string (ex: 'layouts.chef')
      $backRoute          — string (URL complète, pas un nom de route)
      $breadcrumb         — string (ex: 'Espace Chef · Mon équipe')

    Variables optionnelles — actions assignateur :
      $editRoute          — nom de route (Modifier, conditionnel EDITABLE_STATUTS)
      $soumettreRoute     — nom de route (Soumettre à l'évalué, conditionnel EDITABLE_STATUTS)
      $destroyRoute       — nom de route (Supprimer, conditionnel statut ≠ valide)

    Variables optionnelles — mode assignataire :
      $isAssignee         — bool (true = vue de l'évalué)
      $statutRoute        — nom de route pour PATCH accepter/refuser
      $reclamerRoute      — nom de route pour POST réclamation
      $commentaireRoute   — nom de route pour POST commentaire

    Variables spéciales :
      $pdfRoute            — nom de route PDF (optionnel)
      $subjectiveTemplates — Collection<SubjectiveCriteriaTemplate> (fallback quand
                             subjectiveCriteria vide ; passé par le contrôleur pour les
                             assignateurs — collect() pour les assignataires)
    ──────────────────────────────────────────────────────────────────────────
--}}
@extends($layout)

@section('title', 'Évaluation | ' . config('app.name', 'SGP-RCPB'))

@php
    $_noteVal    = (float) $evaluation->note_finale;
    $_notePct    = max(0, min(100, ($_noteVal / 10) * 100));
    $_noteBar    = $_notePct >= 85 ? 'bg-emerald-500' : ($_notePct >= 70 ? 'bg-sky-500' : ($_notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
    $_mentionCls = match ($mention ?? '') {
        'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
        'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
        default     => 'border-rose-200 bg-rose-50 text-rose-700',
    };
    $_isAssignee = $isAssignee ?? false;
    $_templates  = $subjectiveTemplates ?? collect();
@endphp

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- ── Hero ──────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">{{ $breadcrumb ?? 'Évaluation' }}</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">
                    Évaluation — {{ $cibleLabel }}
                </h1>
                <p class="mt-0.5 text-sm text-violet-100/80">
                    {{ $cibleType }}
                    · {{ $evaluation->date_debut->format('m/Y') }} – {{ $evaluation->date_fin->format('m/Y') }}
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20">
                    {{ $statusLabel }}
                </span>
                @if (isset($pdfRoute) && $evaluation->statut !== 'brouillon')
                    <a href="{{ route($pdfRoute, $evaluation) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                        <i class="fas fa-file-pdf text-[10px]"></i> PDF
                    </a>
                @endif
                @if (!$_isAssignee && isset($editRoute) && in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS))
                    <a href="{{ route($editRoute, $evaluation) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-amber-500/80 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-amber-500">
                        <i class="fas fa-edit text-[10px]"></i> Modifier
                    </a>
                @endif
                <a href="{{ $backRoute }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="w-full flex flex-col gap-5">

        {{-- ── Messages flash ──────────────────────────────────────────── --}}
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- ── Bandeau RH : Maintenir / Rouvrir ──────────────────────────── --}}
        @if (!empty($isRh) && in_array($evaluation->statut, ['refuse', 'reclamation']) && in_array($evaluation->statut_reclamation, ['en_attente', null]))
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-gavel text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Décision RH requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">
                        @if($evaluation->reclamation)
                            Réclamation déposée par l'évalué. Examinez-la puis choisissez la suite.
                        @else
                            Évaluation refusée sans réclamation. Vous pouvez maintenir le refus ou rouvrir pour correction.
                        @endif
                    </p>
                    @if($evaluation->reclamation)
                    <div class="mt-3 rounded-xl border border-amber-200 bg-white px-4 py-3">
                        <p class="text-[10px] font-black uppercase tracking-[0.15em] text-amber-500">Réclamation</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $evaluation->reclamation }}</p>
                    </div>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                <form method="POST" action="{{ route($repondreRoute, $evaluation) }}">
                    @csrf
                    <input type="hidden" name="reponse" value="maintenu">
                    <button type="submit"
                            onclick="return confirm('Confirmer : maintenir le refus ?')"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-bold text-red-700 shadow-sm transition hover:bg-red-100">
                        <i class="fas fa-ban text-xs"></i> Maintenir le refus
                    </button>
                </form>
                <form method="POST" action="{{ route($repondreRoute, $evaluation) }}">
                    @csrf
                    <input type="hidden" name="reponse" value="rouvert">
                    <button type="submit"
                            onclick="return confirm('Confirmer : rouvrir l\'évaluation pour correction ?')"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-2.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                        <i class="fas fa-rotate-left text-xs"></i> Rouvrir l'évaluation
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- ── Bandeau validation requise (assignataire, statut = soumis) ── --}}
        @if ($_isAssignee && $evaluation->statut === 'soumis' && isset($statutRoute))
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Validation requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">Consultez votre fiche d'évaluation puis acceptez ou refusez-la.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button"
                        onclick="document.getElementById('modalRefus').classList.remove('hidden');document.getElementById('modalRefus').classList.add('flex')"
                        class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                    <i class="fas fa-times text-xs"></i> Refuser
                </button>
                <form action="{{ route($statutRoute, $evaluation) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit"
                            onclick="return confirm('Accepter cette évaluation ?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-slate-800">
                        <i class="fas fa-check text-xs"></i> Accepter
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- ── Bandeau refus / réclamation (assignataire) ──────────────── --}}
        @if ($_isAssignee && in_array($evaluation->statut, ['refuse', 'reclamation']))
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-rose-200 bg-rose-50 px-6 py-5">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-ban text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black text-rose-900">Évaluation refusée</p>
                    @if ($evaluation->motif_refus)
                        <p class="mt-1 text-sm font-semibold text-rose-700">Motif :</p>
                        <p class="mt-0.5 text-sm text-rose-800">{{ $evaluation->motif_refus }}</p>
                    @endif
                    @if ($evaluation->statut_reclamation)
                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700">
                            @if ($evaluation->statut_reclamation === 'en_attente') <i class="fas fa-clock"></i> Réclamation en attente
                            @elseif ($evaluation->statut_reclamation === 'maintenu') <i class="fas fa-ban"></i> Refus maintenu par le RH
                            @elseif ($evaluation->statut_reclamation === 'rouvert') <i class="fas fa-rotate-left"></i> Rouvert pour correction
                            @endif
                        </p>
                    @endif
                </div>
            </div>
            @if (isset($reclamerRoute) && !$evaluation->reclamation && $evaluation->statut_reclamation !== 'maintenu')
            <form action="{{ route($reclamerRoute, $evaluation) }}" method="POST" class="mt-2 flex flex-col gap-3">
                @csrf
                <label class="text-sm font-semibold text-rose-800">Soumettre une réclamation</label>
                <textarea name="reclamation" rows="3"
                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100"
                    placeholder="Expliquez les raisons de votre réclamation…" required maxlength="1000"></textarea>
                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-rose-700">
                        <i class="fas fa-paper-plane text-xs"></i> Envoyer la réclamation
                    </button>
                </div>
            </form>
            @elseif ($evaluation->reclamation)
            <div class="mt-2 rounded-xl border border-rose-200 bg-white px-4 py-3">
                <p class="text-xs font-black uppercase tracking-[0.15em] text-rose-400">Votre réclamation</p>
                <p class="mt-1 text-sm text-slate-700">{{ $evaluation->reclamation }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- ── Motif refus (assignateur, statut reclamation / a_reviser) ── --}}
        @if (!$_isAssignee && in_array($evaluation->statut, ['reclamation', 'a_reviser']) && $evaluation->motif_refus)
        <div class="rounded-xl border border-orange-200 bg-orange-50 px-5 py-4">
            <p class="text-xs font-black uppercase tracking-[0.15em] text-orange-600">
                <i class="fas fa-triangle-exclamation mr-1"></i> Motif du refus par l'évalué
            </p>
            <p class="mt-2 text-sm text-orange-800">{{ $evaluation->motif_refus }}</p>
        </div>
        @endif

        {{-- ── Réclamation de l'évalué (assignateur, statut reclamation) ── --}}
        @if (!$_isAssignee && $evaluation->statut === 'reclamation' && $evaluation->reclamation)
        <div class="rounded-xl border border-rose-300 bg-rose-50 px-5 py-4">
            <p class="text-xs font-black uppercase tracking-[0.15em] text-rose-600">
                <i class="fas fa-flag mr-1"></i> Réclamation soumise par {{ $cibleLabel }}
            </p>
            <p class="mt-2 text-sm text-rose-800 whitespace-pre-line">{{ $evaluation->reclamation }}</p>
        </div>
        @endif

        {{-- ── KPI cards ────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-7">
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</p>
                <p class="mt-2 text-sm font-black text-slate-900">{{ $evaluation->date_debut->format('d/m/Y') }}</p>
                <p class="text-[11px] text-slate-400">→ {{ $evaluation->date_fin->format('d/m/Y') }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Moy. objectifs</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">Note : {{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Moy. subjectifs</p>
                <p class="mt-2 text-lg font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">Note : {{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note finale</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($_noteVal, 2, ',', ' ') }}<span class="text-sm font-bold text-slate-400">/10</span></p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $_noteBar }}" style="width: {{ $_notePct }}%"></div>
                </div>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Mention</p>
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-full border {{ $_mentionCls }} px-3 py-1 text-xs font-black">{{ $mention ?? '—' }}</span>
                </div>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</p>
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-full border {{ $statusClass }} px-3 py-1 text-xs font-black">{{ $statusLabel }}</span>
                </div>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                    {{ $_isAssignee ? 'Évaluateur' : 'Évalué' }}
                </p>
                <p class="mt-2 text-sm font-black text-slate-900">
                    {{ $_isAssignee ? ($evaluation->evaluateur?->name ?? '—') : $cibleLabel }}
                </p>
            </div>
        </div>

        {{-- ── Identification ────────────────────────────────────────────── --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Identification</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div><span class="text-xs uppercase text-slate-500">Année</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y') }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Nom et prénom</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->nom_prenom ?? '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Semestre</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->semestre ? 'Semestre ' . $ident->semestre : '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Date d'évaluation</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->date_evaluation?->format('d/m/Y') ?? '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Emploi</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->emploi ?? '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Matricule</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->matricule ?? '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Entité</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->direction ?? '—' }}</p></div>
                <div><span class="text-xs uppercase text-slate-500">Direction / Service</span><p class="mt-1 text-sm text-slate-800">{{ $ident?->direction_service ?? '—' }}</p></div>
            </div>
            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Période</th>
                                <th class="px-3 py-3">Formation</th>
                                <th class="px-3 py-3">Domaine</th>
                            </tr>
                        </thead>
                        <tbody>
                            @include('evaluations._formations_auto')
                        </tbody>
                    </table>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Période</th>
                                <th class="px-3 py-3">Poste ou fonction</th>
                                <th class="px-3 py-3">Observations</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse (($ident?->experiences ?? []) as $row)
                                <tr class="border-t border-slate-200">
                                    <td class="px-3 py-2">{{ $row['periode'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $row['poste'] ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $row['observations'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-3 text-slate-400">Aucune expérience renseignée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- ── Critères objectifs ────────────────────────────────────────── --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Critères objectifs</h2>
            <div class="mt-4 space-y-4">
                @forelse ($objectiveCriteria as $criterion)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                @if ($criterion->observation)
                                    <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                            </span>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <th class="py-2 pr-4">Sous-critère</th>
                                        <th class="py-2 pr-4">Note /5</th>
                                        <th class="py-2">Observation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($criterion->sousCriteres as $sub)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                            <td class="py-2 pr-4 font-semibold">{{ number_format((float) $sub->note, 2, ',', ' ') }}</td>
                                            <td class="py-2">{{ $sub->observation ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-400">Aucun critère objectif enregistré.</p>
                @endforelse
                <div class="mt-2 flex justify-end">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne objectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note objectifs (×0,75)</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Critères subjectifs ──────────────────────────────────────── --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Critères subjectifs</h2>
            <div class="mt-4 space-y-4">
                @forelse ($subjectiveCriteria as $criterion)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $criterion->titre }}</h3>
                                @if ($criterion->observation)
                                    <p class="mt-1 text-sm text-slate-500">{{ $criterion->observation }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                Note globale {{ number_format((float) $criterion->note_globale, 2, ',', ' ') }}
                            </span>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-500">
                                        <th class="py-2 pr-4">Sous-critère</th>
                                        <th class="py-2 pr-4">Note /5</th>
                                        <th class="py-2">Observation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($criterion->sousCriteres as $sub)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                            <td class="py-2 pr-4 font-semibold">{{ number_format((float) $sub->note, 2, ',', ' ') }}</td>
                                            <td class="py-2">{{ $sub->observation ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @empty
                    @if ($_templates->isNotEmpty())
                        @foreach ($_templates as $template)
                            <article class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-700">{{ $template->titre }}</h3>
                                        @if ($template->description)
                                            <p class="mt-1 text-sm text-slate-400">{{ $template->description }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-400">Non noté</span>
                                </div>
                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full text-left text-sm text-slate-500">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-xs uppercase tracking-[0.12em] text-slate-400">
                                                <th class="py-2 pr-4">Sous-critère</th>
                                                <th class="py-2 pr-4">Note /5</th>
                                                <th class="py-2">Observation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($template->subcriteria as $sub)
                                                <tr class="border-b border-slate-100">
                                                    <td class="py-2 pr-4">{{ $sub->libelle }}</td>
                                                    <td class="py-2 pr-4 font-semibold text-slate-300">—</td>
                                                    <td class="py-2 text-slate-300">—</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        @endforeach
                    @else
                        <p class="text-sm text-slate-400">Aucun critère subjectif renseigné.</p>
                    @endif
                @endforelse
                <div class="mt-2 flex justify-end">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 flex flex-row items-center gap-12">
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Moyenne subjectifs</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-1">Note subjectifs (×0,25)</p>
                            <p class="text-2xl font-black text-slate-900">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs font-semibold uppercase tracking-[0.15em] text-emerald-700 mb-1">Note totale</p>
                            <p class="text-2xl font-black text-emerald-700">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Plan d'amélioration ──────────────────────────────────────── --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Plan d'amélioration</h2>
            <div class="mt-4 grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Points à améliorer</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->points_a_ameliorer ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Stratégies d'amélioration</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->strategies_amelioration ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire de l'évaluateur</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaire ?: '—' }}</p>
                </div>
            </div>
            <div class="mt-6 grid gap-5 border-t border-slate-100 pt-5 md:grid-cols-2">
                <div>
                    <span class="text-xs uppercase text-slate-500">Évalué</span>
                    <p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evalue_nom ?: $cibleLabel }}</p>
                    @if ($evaluation->date_signature_evalue)
                        <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($evaluation->date_signature_evalue)->format('d/m/Y') }}</p>
                    @endif
                </div>
                <div>
                    <span class="text-xs uppercase text-slate-500">Évaluateur</span>
                    <p class="mt-1 text-sm text-slate-800">{{ $evaluation->signature_evaluateur_nom ?: ($evaluation->evaluateur?->name ?? '—') }}</p>
                    @if ($evaluation->date_signature_evaluateur)
                        <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($evaluation->date_signature_evaluateur)->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>
        </section>

        {{-- ── Commentaire de l'évalué ──────────────────────────────────── --}}
        @if ($_isAssignee && isset($commentaireRoute))
        {{-- Assignataire : formulaire éditable (lecture seule si validé) --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="mb-1 text-base font-black text-slate-900">Mes commentaires</h2>
            <p class="text-sm text-slate-500">Vous pouvez saisir vos commentaires sur cette évaluation tant qu'elle n'est pas validée.</p>
            @if ($evaluation->statut === 'valide')
                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Commentaire enregistré (évaluation validée)</p>
                    <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: 'Aucun commentaire saisi.' }}</p>
                </div>
            @else
                <form method="POST" action="{{ route($commentaireRoute, $evaluation) }}" class="mt-4 space-y-3">
                    @csrf
                    <textarea name="commentaires_evalue" rows="6"
                        class="ent-input w-full"
                        placeholder="Saisissez vos commentaires sur cette évaluation...">{{ old('commentaires_evalue', $evaluation->commentaires_evalue) }}</textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="ent-btn ent-btn-primary">Enregistrer mon commentaire</button>
                    </div>
                </form>
            @endif
        </section>
        @elseif (!$_isAssignee)
        {{-- Assignateur / superviseur : lecture seule du commentaire de l'évalué --}}
        <section class="rounded-[20px] border border-slate-100 bg-white px-6 py-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900">Commentaire de l'évalué</h2>
            <p class="mt-1 text-sm text-slate-500">Commentaire saisi par {{ $cibleLabel }} sur cette évaluation.</p>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $evaluation->commentaires_evalue ?: 'Aucun commentaire saisi par l\'évalué.' }}</p>
            </div>
        </section>
        @endif

        {{-- ── Footer actions ──────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
            <a href="{{ $backRoute }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Retour
            </a>
            <div class="flex flex-wrap items-center gap-3">

                {{-- Assignateur : Modifier / Soumettre / Supprimer --}}
                @if (!$_isAssignee)
                    @if (isset($editRoute) && in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS))
                        <a href="{{ route($editRoute, $evaluation) }}"
                           class="inline-flex items-center gap-2 rounded-xl border-2 border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-100">
                            <i class="fas fa-pen text-xs"></i> Modifier
                        </a>
                    @endif
                    @if (isset($soumettreRoute) && in_array($evaluation->statut, \App\Models\Evaluation::EDITABLE_STATUTS))
                        <form method="POST" action="{{ route($soumettreRoute, $evaluation) }}"
                              onsubmit="return confirm('Soumettre cette évaluation à l\'évalué ?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                                <i class="fas fa-paper-plane text-xs"></i> Soumettre
                            </button>
                        </form>
                    @endif
                    @if (isset($destroyRoute) && in_array($evaluation->statut, ['brouillon', 'a_reviser']))
                        <form method="POST" action="{{ route($destroyRoute, $evaluation) }}"
                              onsubmit="return confirm('Supprimer définitivement cette évaluation ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                                <i class="fas fa-trash text-xs"></i> Supprimer
                            </button>
                        </form>
                    @endif
                @endif

                {{-- Assignataire : Accepter / Refuser dans le footer (doublon du bandeau, visible directement) --}}
                @if ($_isAssignee && $evaluation->statut === 'soumis' && isset($statutRoute))
                    <form method="POST" action="{{ route($statutRoute, $evaluation) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="accepter">
                        <button type="submit" onclick="return confirm('Accepter cette évaluation ?')"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-check text-xs"></i> Accepter l'évaluation
                        </button>
                    </form>
                    <button type="button"
                            onclick="document.getElementById('modalRefus').classList.remove('hidden');document.getElementById('modalRefus').classList.add('flex')"
                            class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                        <i class="fas fa-times text-xs"></i> Refuser l'évaluation
                    </button>
                @endif

            </div>
        </div>

        </div>
    </div>
</div>

{{-- ── Modal Refus (assignataire uniquement) ───────────────────────────── --}}
@if ($_isAssignee && isset($statutRoute))
<div id="modalRefus" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50"
         onclick="this.parentElement.classList.add('hidden');this.parentElement.classList.remove('flex')"></div>
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-4">
        <h3 class="text-lg font-black text-slate-900">Refuser l'évaluation</h3>
        <p class="mt-1 text-sm text-slate-500">Indiquez le motif de votre refus (obligatoire).</p>
        <form action="{{ route($statutRoute, $evaluation) }}" method="POST" class="mt-5 flex flex-col gap-4">
            @csrf @method('PATCH')
            <input type="hidden" name="action" value="refuser">
            <div class="flex flex-col gap-1.5">
                <label for="motif_refus" class="text-sm font-semibold text-slate-700">Motif du refus</label>
                <textarea id="motif_refus" name="motif_refus" rows="4" required maxlength="1000"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100"
                    placeholder="Expliquez pourquoi vous refusez cette évaluation…"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalRefus').classList.add('hidden');document.getElementById('modalRefus').classList.remove('flex')"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                    <i class="fas fa-times mr-1.5 text-xs"></i> Confirmer le refus
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modalRefus').classList.add('hidden');
        document.getElementById('modalRefus').classList.remove('flex');
    }
});
</script>
@endif

@endsection
