{{--
    evaluations/create.blade.php — Formulaire de création d'évaluation (unifié)

    Variables requises :
      $layout          — layout à étendre (ex: 'layouts.chef')
      $heroSubtitle    — texte de contexte au-dessus du h1
      $backUrl         — URL du bouton Retour
      $formAction      — action du formulaire (route de store)
      $evalueLabel     — libellé évalué pour la section Signatures (ex: 'Agent')
      $evaluateurLabel — libellé évaluateur pour la section Signatures (ex: 'Chef de Caisse')
      $targetType      — type de cible : 'agent' | 'user' | 'service' | 'secretaire' | 'direction'
      $openAnnee       — Annee ouverte (ou null)
      $openSemestre    — Semestre ouvert (ou null)
      $objectiveOptions    — fiches d'objectifs disponibles (array)
      $subjectiveTemplates — critères subjectifs initiaux (collection/array)
      $oldFormations       — formations old() ou []
      $oldExperiences      — expériences old() ou []

    Variables selon $targetType :
      'agent'     : $agents, $agentsJson, $selectedAgent, $lockAgent, $resolvedAgent,
                    $prefilledMatricule, $prefilledNomPrenom, $prefilledEmploi,
                    $prefilledDirectionService, $entiteNom, $prefilledAgentId
      'user'      : $subordonne (User|array), $prefilledNomPrenom
      'secretaire': $secretaire (User), $direction (Direction|null)
      'direction' : $direction (Direction)
      'service'   : $ctx (DirecteurEntity), $services, $agences, $caisses,
                    $selectedService, $selectedAgence, $selectedCaisse,
                    $resolvedServiceId, $resolvedService, $lockService,
                    $prefilledNomPrenom, $prefilledEmploi, $entiteNom
--}}
@extends($layout ?? 'layouts.app')

@section('title', 'Nouvelle évaluation | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">{{ $heroSubtitle ?? '' }}</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Nouvelle évaluation</h1>
                <p class="mt-0.5 text-sm text-violet-100/80">Renseignez les critères objectifs, subjectifs et le plan d'amélioration.</p>
            </div>
            <a href="{{ $backUrl ?? url()->previous() }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="w-full">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('periode_fermee'))
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <i class="fas fa-lock mt-0.5 shrink-0 text-amber-500"></i>
                    <span>{{ session('periode_fermee') }}</span>
                </div>
            @endif

            @if (!empty($alreadyEvaluatedWarning))
                <div class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <i class="fas fa-triangle-exclamation mt-0.5 shrink-0 text-amber-500"></i>
                    <span>{{ $alreadyEvaluatedWarning }}</span>
                </div>
            @endif

            @php $targetType ??= 'user'; @endphp

            {{-- Aucun agent éligible --}}
            @if ($targetType === 'agent' && isset($agents) && $agents->isEmpty())
                <div class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-10 text-center shadow-sm">
                    <i class="fas fa-users-slash text-2xl text-slate-300"></i>
                    <p class="font-black text-slate-700">Tous les agents ont déjà été évalués</p>
                    <p class="text-sm text-slate-500">Tous les agents de votre équipe ont déjà une évaluation pour le semestre en cours.</p>
                    <a href="{{ $backUrl ?? url()->previous() }}" class="mt-2 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                        <i class="fas fa-arrow-left text-xs"></i> Retour
                    </a>
                </div>
            @else

            <form method="POST" action="{{ $formAction }}"
                  class="flex flex-col gap-5 lg:grid lg:grid-cols-[1fr_300px] lg:items-start lg:gap-6">
                @csrf
                <input type="hidden" name="annee_id"  value="{{ $openAnnee?->id }}">
                <input type="hidden" name="semestre"   value="{{ $openSemestre ? 'S'.$openSemestre->numero : '' }}">
                <input type="hidden" name="_subjective_criteres_submitted" value="1">

                {{-- ══════════════════════ COLONNE PRINCIPALE ══════════════════════ --}}
                <div class="flex flex-col gap-5">

                    {{-- ── CARTE 1 : Identification et période ──────────────────────── --}}
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">1</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Identification et période</p>
                                <p class="text-xs text-slate-500">
                                    @if($targetType === 'agent') Sélectionnez l'agent à évaluer, la période et vérifiez les informations.
                                    @elseif($targetType === 'service') Sélectionnez le collaborateur évalué et vérifiez les informations.
                                    @else Vérifiez les informations et renseignez la période concernée.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="space-y-6 px-6 py-6">

                            {{-- Sélecteur de cible --}}
                            @if ($targetType === 'guichet')
                                @php
                                    $guichetChefNom = isset($guichet) ? ($guichet->chef ? trim(($guichet->chef->prenom ?? '') . ' ' . ($guichet->chef->nom ?? '')) : '—') : '—';
                                @endphp
                                <input type="hidden" name="guichet_id" value="{{ $guichet->id ?? '' }}">
                                <div class="rounded-2xl border border-violet-100 bg-violet-50/70 px-4 py-4">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Chef de Guichet évalué</p>
                                    <p class="mt-2 text-base font-black text-slate-900">{{ $guichetChefNom }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Guichet — {{ $guichet?->nom ?? '—' }}</p>
                                </div>

                            @elseif ($targetType === 'agent')
                                @if (isset($lockAgent) && $lockAgent && isset($resolvedAgent))
                                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Agent évalué</p>
                                        <p class="mt-2 text-base font-black text-slate-900">{{ trim(($resolvedAgent->prenom ?? '') . ' ' . ($resolvedAgent->nom ?? '')) ?: ($resolvedAgent->name ?? '-') }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $resolvedAgent->role ?? 'Agent' }}</p>
                                        <input type="hidden" name="agent_id" value="{{ $resolvedAgent->id }}">
                                    </div>
                                @else
                                    <div class="space-y-2">
                                        <label for="agent_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Agent évalué</label>
                                        <select id="agent_id" name="agent_id" class="ent-select" required>
                                            <option value="">— Sélectionner un agent —</option>
                                            @foreach ($agents ?? [] as $ag)
                                                <option value="{{ $ag->id }}" @selected(isset($prefilledAgentId) && (int) $prefilledAgentId === $ag->id)>
                                                    {{ trim(($ag->prenom ?? '') . ' ' . ($ag->nom ?? '')) }} — {{ $ag->role ?? 'Agent' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('agent_id')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif

                            @elseif ($targetType === 'user')
                                @php
                                    $sub = $subordonne ?? null;
                                    $subNom = is_array($sub) ? ($sub['nom'] ?? '-') : ($sub?->name ?? '-');
                                    $subRole = is_array($sub) ? ($sub['role_label'] ?? '') : ($sub?->role ?? '');
                                    $subId = is_array($sub) ? ($sub['id'] ?? null) : ($sub?->id ?? null);
                                @endphp
                                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Collaborateur évalué</p>
                                    <p class="mt-2 text-base font-black text-slate-900">{{ $subNom }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $subRole }}</p>
                                    @if($subId)<input type="hidden" name="user_id" value="{{ $subId }}">@endif
                                </div>

                            @elseif ($targetType === 'secretaire')
                                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Évalué(e)</p>
                                    <p class="mt-2 text-base font-black text-slate-900">{{ $secretaire->name ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Secrétaire — {{ $direction?->nom ?? '' }}</p>
                                </div>

                            @elseif ($targetType === 'direction')
                                @php
                                    $dirChef = $direction?->directeurAgent ?? null;
                                    $dirChefNom = $dirChef ? trim(($dirChef->prenom ?? '') . ' ' . ($dirChef->nom ?? '')) : '—';
                                @endphp
                                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Direction évaluée</p>
                                    <p class="mt-2 text-base font-black text-slate-900">{{ $direction?->nom ?? '-' }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Directeur : {{ $dirChefNom }}</p>
                                    <input type="hidden" name="direction_id" value="{{ $direction?->id }}">
                                </div>

                            @elseif ($targetType === 'service')
                                @php
                                    $resolvedServiceId = (int) old('service_id', $selectedService?->id ?? 0);
                                    $resolvedService   = ($services ?? collect())->firstWhere('id', $resolvedServiceId) ?? ($selectedService ?? null);
                                    $lockService       = isset($lockService) ? $lockService : (($services ?? collect())->count() === 1 || isset($selectedService));
                                @endphp

                                @if (isset($selectedCaisse))
                                    @php
                                        $dirCaisseAgent = $selectedCaisse->directeurAgent ?? null;
                                        $dirCaisseNom   = $dirCaisseAgent ? trim(($dirCaisseAgent->prenom ?? '') . ' ' . ($dirCaisseAgent->nom ?? '')) : '—';
                                    @endphp
                                    <div class="rounded-2xl border border-violet-100 bg-violet-50/70 px-4 py-4">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-700">Directeur de caisse évalué</p>
                                        <p class="mt-2 text-base font-black text-slate-900">{{ $dirCaisseNom }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $selectedCaisse->nom }}</p>
                                        <input type="hidden" name="caisse_id" value="{{ $selectedCaisse->id }}">
                                    </div>

                                @elseif (isset($selectedAgence))
                                    @php
                                        $chefAgenceAgent = $selectedAgence->chef ?? null;
                                        $chefAgenceNom   = $chefAgenceAgent ? trim(($chefAgenceAgent->prenom ?? '') . ' ' . ($chefAgenceAgent->nom ?? '')) : '—';
                                    @endphp
                                    <div class="rounded-2xl border border-sky-100 bg-sky-50/70 px-4 py-4">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-sky-700">Chef d'agence évalué</p>
                                        <p class="mt-2 text-base font-black text-slate-900">{{ $chefAgenceNom }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $selectedAgence->nom }}</p>
                                        <input type="hidden" name="agence_id" value="{{ $selectedAgence->id }}">
                                    </div>

                                @elseif ($lockService && $resolvedService)
                                    @php
                                        $chefSvcAgent = $resolvedService->chef ?? null;
                                        $chefSvcNom   = $chefSvcAgent ? trim(($chefSvcAgent->prenom ?? '') . ' ' . ($chefSvcAgent->nom ?? '')) : '—';
                                    @endphp
                                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Chef de service évalué</p>
                                        <p class="mt-2 text-base font-black text-slate-900">{{ $chefSvcNom }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $resolvedService->nom }}</p>
                                        <input type="hidden" name="service_id" value="{{ $resolvedService->id }}">
                                    </div>

                                @elseif (isset($ctx) && method_exists($ctx, 'hasCaisses') && $ctx->hasCaisses() && ($caisses ?? collect())->isNotEmpty())
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur de caisse évalué</label>
                                            <select name="caisse_id" class="ent-select">
                                                <option value="">— Sélectionner —</option>
                                                @foreach ($caisses as $cai)
                                                    @php $dNom = $cai->directeurAgent ? trim(($cai->directeurAgent->prenom ?? '') . ' ' . ($cai->directeurAgent->nom ?? '')) : $cai->nom; @endphp
                                                    <option value="{{ $cai->id }}" @selected(old('caisse_id') == $cai->id)>{{ $dNom }} ({{ $cai->nom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if (($services ?? collect())->isNotEmpty())
                                            <div class="space-y-2">
                                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">— ou — Chef de service évalué</label>
                                                <select name="service_id" class="ent-select">
                                                    <option value="">— Sélectionner —</option>
                                                    @foreach ($services as $svc)
                                                        @php $cNom = $svc->chef ? trim(($svc->chef->prenom ?? '') . ' ' . ($svc->chef->nom ?? '')) : $svc->nom; @endphp
                                                        <option value="{{ $svc->id }}" @selected(old('service_id') == $svc->id)>{{ $cNom }} ({{ $svc->nom }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p class="text-xs text-slate-400">Sélectionnez soit un directeur de caisse, soit un chef de service — pas les deux.</p>
                                        @endif
                                    </div>

                                @elseif (isset($ctx) && method_exists($ctx, 'hasAgences') && $ctx->hasAgences() && ($agences ?? collect())->isNotEmpty())
                                    <div class="space-y-4">
                                        <div class="space-y-2">
                                            <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Chef d'agence évalué</label>
                                            <select name="agence_id" class="ent-select">
                                                <option value="">— Sélectionner —</option>
                                                @foreach ($agences as $agc)
                                                    @php $cNom = $agc->chef ? trim(($agc->chef->prenom ?? '') . ' ' . ($agc->chef->nom ?? '')) : $agc->nom; @endphp
                                                    <option value="{{ $agc->id }}" @selected(old('agence_id') == $agc->id)>{{ $cNom }} ({{ $agc->nom }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if (($services ?? collect())->isNotEmpty())
                                            <div class="space-y-2">
                                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">— ou — Chef de service évalué</label>
                                                <select name="service_id" class="ent-select">
                                                    <option value="">— Sélectionner —</option>
                                                    @foreach ($services as $svc)
                                                        @php $cNom = $svc->chef ? trim(($svc->chef->prenom ?? '') . ' ' . ($svc->chef->nom ?? '')) : $svc->nom; @endphp
                                                        <option value="{{ $svc->id }}" @selected(old('service_id') == $svc->id)>{{ $cNom }} ({{ $svc->nom }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <p class="text-xs text-slate-400">Sélectionnez soit un chef d'agence, soit un chef de service — pas les deux.</p>
                                        @endif
                                    </div>

                                @else
                                    <div class="space-y-2">
                                        <label for="service_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Chef de service évalué</label>
                                        <select id="service_id" name="service_id" class="ent-select" required>
                                            <option value="">Sélectionner un chef de service</option>
                                            @foreach ($services ?? [] as $svc)
                                                @php $cNom = $svc->chef ? trim(($svc->chef->prenom ?? '') . ' ' . ($svc->chef->nom ?? '')) : $svc->nom; @endphp
                                                <option value="{{ $svc->id }}" @selected($resolvedServiceId === (int) $svc->id)>{{ $cNom }} ({{ $svc->nom }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endif

                            {{-- I. Identification de l'évalué --}}
                            <div>
                                <h3 class="border-t border-slate-200 pt-6 text-base font-black text-slate-900">
                                    @if($targetType === 'secretaire') Identification de l'évalué
                                    @else I. Identification de l'évalué
                                    @endif
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    @if($targetType === 'agent') Les champs grisés sont remplis automatiquement lors de la sélection de l'agent.
                                    @elseif($targetType === 'guichet') Les champs grisés sont pré-remplis automatiquement.
                                    @elseif($targetType === 'service') Les champs sont remplis automatiquement lors de la sélection du collaborateur.
                                    @else Cette section est éditable manuellement.
                                    @endif
                                </p>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Année</label>
                                    <input type="text" value="{{ $openAnnee?->annee ?? now()->year }}" class="ent-input bg-slate-50 text-slate-600" readonly>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Semestre</label>
                                    <input type="text" value="{{ isset($openSemestre) && $openSemestre ? 'Semestre '.$openSemestre->numero : '—' }}"
                                           name="identification[semestre]" class="ent-input bg-slate-50 text-slate-600" readonly>
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_date_evaluation" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date de l'évaluation</label>
                                    @php $dateReadonly = in_array($targetType ?? '', ['agent', 'guichet']); @endphp
                                    <input id="identification_date_evaluation" name="identification[date_evaluation]" type="text"
                                           value="{{ old('identification.date_evaluation') }}"
                                           class="ent-input {{ $dateReadonly ? 'bg-slate-50 text-slate-600' : '' }}" placeholder="JJ/MM/YYYY"
                                           {{ $dateReadonly ? 'readonly' : 'autocomplete=off' }}>
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_matricule" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Matricule</label>
                                    @php
                                        $matriculeDefault = $prefilledMatricule ?? ($targetType === 'guichet' ? ($guichet?->chef?->matricule ?? '') : old('identification.matricule', ''));
                                    @endphp
                                    <input id="identification_matricule" name="identification[matricule]" type="text"
                                           value="{{ $matriculeDefault }}"
                                           class="ent-input bg-slate-50 text-slate-600" readonly placeholder="Renseigné automatiquement">
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_grade" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Grade</label>
                                    <input id="identification_grade" name="identification[grade]" type="text"
                                           value="{{ old('identification.grade') }}" class="ent-input" placeholder="Grade de l'évalué">
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_emploi" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Emploi / Fonction</label>
                                    @php
                                        $prefilledEmploiDefault = $targetType === 'secretaire' ? 'Secrétaire' : ($targetType === 'guichet' ? 'Chef de Guichet' : '');
                                        $emploiReadonly = in_array($targetType, ['agent', 'guichet']);
                                    @endphp
                                    <input id="identification_emploi" name="identification[emploi]" type="text"
                                           value="{{ old('identification.emploi', $prefilledEmploi ?? $prefilledEmploiDefault) }}"
                                           class="ent-input{{ $emploiReadonly ? ' bg-slate-50 text-slate-600' : '' }}"
                                           {{ $emploiReadonly ? 'readonly' : '' }}>
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_nom_prenom" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom et prénom</label>
                                    @php
                                        $guichetChefNomDefault = isset($guichet) ? ($guichet->chef ? trim(($guichet->chef->prenom ?? '') . ' ' . ($guichet->chef->nom ?? '')) : '') : '';
                                        $nomDefault = $targetType === 'secretaire' ? ($prefilledNomPrenom ?? $secretaire?->name ?? '')
                                            : ($targetType === 'guichet' ? $guichetChefNomDefault
                                            : ($prefilledNomPrenom ?? ''));
                                        $nomReadonly = in_array($targetType, ['agent', 'guichet']);
                                    @endphp
                                    <input id="identification_nom_prenom" name="identification[nom_prenom]" type="text"
                                           value="{{ old('identification.nom_prenom', $nomDefault) }}"
                                           class="ent-input{{ $nomReadonly ? ' bg-slate-50 text-slate-600' : '' }}"
                                           {{ $nomReadonly ? 'readonly' : '' }}>
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_direction" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Entité</label>
                                    @php
                                        $entiteDefault = $targetType === 'secretaire' ? ($entiteNom ?? $direction?->nom ?? '')
                                            : ($targetType === 'guichet' ? ($entiteNom ?? '')
                                            : ($entiteNom ?? ''));
                                        $entiteReadonly = in_array($targetType, ['agent', 'guichet']);
                                    @endphp
                                    <input id="identification_direction" name="identification[direction]" type="text"
                                           value="{{ old('identification.direction', $entiteDefault) }}"
                                           class="ent-input{{ $entiteReadonly ? ' bg-slate-50 text-slate-600' : '' }}"
                                           {{ $entiteReadonly ? 'readonly' : '' }}>
                                </div>
                                <div class="space-y-2">
                                    <label for="identification_direction_service" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction / Service</label>
                                    @php
                                        $dsDefault = $targetType === 'secretaire' ? 'Secrétariat'
                                            : ($targetType === 'guichet' ? ($guichet?->nom ?? '')
                                            : ($prefilledDirectionService ?? ''));
                                        $dsReadonly = in_array($targetType, ['agent', 'guichet']);
                                    @endphp
                                    <input id="identification_direction_service" name="identification[direction_service]" type="text"
                                           value="{{ old('identification.direction_service', $dsDefault) }}"
                                           class="ent-input{{ $dsReadonly ? ' bg-slate-50 text-slate-600' : '' }}"
                                           {{ $dsReadonly ? 'readonly' : '' }}>
                                </div>
                            </div>

                            {{-- II. Formations & III. Expériences --}}
                            <div class="grid gap-6 xl:grid-cols-2">
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="border-t border-slate-200 pt-6 text-base font-black text-slate-900">II. Formation, stage et séminaires</h3>
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
                                        <button id="add-formation-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="border-t border-slate-200 pt-6 text-base font-black text-slate-900">III. Expérience professionnelle</h3>
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
                                        <button id="add-experience-row" type="button" class="ent-btn ent-btn-soft">Ajouter une ligne</button>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- end card 1 body --}}
                    </div>{{-- end card 1 --}}

                    {{-- ── CARTE 2 : Critères objectifs ─────────────────────────────── --}}
                    <div id="objective-section" class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">2</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Critères objectifs</p>
                                <p class="text-xs text-slate-500">Choisissez une fiche d'objectifs, puis notez les sous-critères. Barème : 1 à 5.</p>
                            </div>
                        </div>
                        <div class="space-y-5 px-6 py-6">
                            {{-- Sélecteur de fiche --}}
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Sélectionner une fiche d'objectifs</label>
                                <div class="flex gap-2">
                                    <select id="objective-fiche-selector" class="ent-select flex-1">
                                        <option value="">— Choisir une fiche —</option>
                                    </select>
                                    <button id="add-selected-objectives" type="button"
                                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-xs font-black text-white transition hover:bg-violet-700">
                                        <i class="fas fa-plus text-[10px]"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                            {{-- Objectifs de la fiche --}}
                            <div id="objective-choice-container" class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 italic">
                                Sélectionnez une fiche pour afficher ses objectifs disponibles.
                            </div>
                            {{-- Critères ajoutés --}}
                            <div id="objective-criteria-container" class="space-y-5"></div>
                        </div>
                    </div>

                    {{-- ── CARTE 3 : Critères subjectifs ────────────────────────────── --}}
                    <div id="subjective-section" class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">3</span>
                                <div>
                                    <p class="text-sm font-black text-slate-900">Critères subjectifs</p>
                                    <p class="text-xs text-slate-500">Renseignez les sous-critères comportementaux. Barème : 1 à 5.</p>
                                </div>
                            </div>
                            <button id="add-subjective-criterion" type="button" class="ent-btn ent-btn-soft">Ajouter un critère</button>
                        </div>
                        <div class="px-6 py-6">
                            <div id="subjective-criteria-container" class="space-y-5"></div>
                        </div>
                    </div>

                    {{-- ── CARTE 4 : Synthèse des notes ─────────────────────────────── --}}
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">4</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Synthèse des notes</p>
                                <p class="text-xs text-slate-500">Calcul automatique : objectifs ×0,75 + subjectifs ×0,25, puis ×2 = note /10.</p>
                            </div>
                        </div>
                        <div class="grid gap-3 px-6 py-6 sm:grid-cols-2 xl:grid-cols-5">
                            <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-slate-300"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500">Moy. objectifs</p>
                                    <p id="summary-moyenne-objectifs" class="mt-2 text-2xl font-black leading-none text-slate-900">0,00</p>
                                </div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50/60 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-700">Note objectifs</p>
                                    <p id="summary-note-objectifs" class="mt-2 text-2xl font-black leading-none text-emerald-700">0,00</p>
                                </div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-slate-300"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500">Moy. subjectifs</p>
                                    <p id="summary-moyenne-subjectifs" class="mt-2 text-2xl font-black leading-none text-slate-900">0,00</p>
                                </div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl border border-sky-100 bg-sky-50/60 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-sky-500"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-sky-700">Note subjectifs</p>
                                    <p id="summary-note-subjectifs" class="mt-2 text-2xl font-black leading-none text-sky-700">0,00</p>
                                </div>
                            </div>
                            <div class="relative overflow-hidden rounded-2xl border border-violet-200 bg-violet-50 shadow-sm">
                                <div class="absolute inset-y-0 left-0 w-1 bg-violet-600"></div>
                                <div class="px-5 py-4 pl-6">
                                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-violet-700">Note totale</p>
                                    <p id="summary-note-finale" class="mt-2 text-3xl font-black leading-none text-violet-700">0,00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── CARTE 5 : Plan d'amélioration ───────────────────────────── --}}
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">5</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Plan d'amélioration</p>
                            </div>
                        </div>
                        <div class="space-y-5 px-6 py-6">
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
                        </div>
                    </div>

                    {{-- ── CARTE 6 : Signatures ─────────────────────────────────────── --}}
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-xs font-black text-white">6</span>
                            <div>
                                <p class="text-sm font-black text-slate-900">Signatures</p>
                            </div>
                        </div>
                        <div class="grid gap-5 px-6 py-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évalué(e) — {{ $evalueLabel ?? 'Évalué' }}
                                </label>
                                @php
                                    $sigEvalueDefault = $targetType === 'secretaire' ? ($secretaire?->name ?? '') : '';
                                @endphp
                                <input id="signature_evalue_nom" name="signature_evalue_nom" type="text"
                                       value="{{ old('signature_evalue_nom', $sigEvalueDefault) }}" class="ent-input"
                                       placeholder="Nom de l'évalué(e)">
                                <input id="date_signature_evalue" name="date_signature_evalue" type="date"
                                       value="{{ old('date_signature_evalue') }}" class="ent-input">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                    Évaluateur — {{ $evaluateurLabel ?? 'Évaluateur' }}
                                </label>
                                <input id="signature_evaluateur_nom" name="signature_evaluateur_nom" type="text"
                                       value="{{ old('signature_evaluateur_nom', auth()->user()->name ?? '') }}" class="ent-input">
                                <input id="date_signature_evaluateur" name="date_signature_evaluateur" type="date"
                                       value="{{ old('date_signature_evaluateur') }}" class="ent-input">
                            </div>
                        </div>
                    </div>

                </div>{{-- fin colonne principale --}}

                {{-- ══════════════════════ SIDEBAR ══════════════════════════════════ --}}
                <div class="sticky top-4 flex flex-col gap-4">

                    {{-- Info période + actions --}}
                    <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="bg-gradient-to-br from-violet-600 to-purple-600 px-5 py-5 text-white">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-violet-200">{{ $heroSubtitle ?? 'Évaluation' }}</p>
                            <p class="mt-1 text-lg font-black leading-tight">Nouvelle évaluation</p>
                        </div>
                        <div class="space-y-2 px-5 py-4">
                            <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                                <i class="fas fa-calendar-alt text-xs text-slate-400"></i>
                                <span class="text-sm font-semibold text-slate-700">{{ $openAnnee?->annee ?? now()->year }}</span>
                            </div>
                            @if($openSemestre)
                            <div class="flex items-center gap-2 rounded-xl bg-violet-50 px-3 py-2">
                                <i class="fas fa-layer-group text-xs text-violet-500"></i>
                                <span class="text-sm font-semibold text-violet-700">Semestre {{ $openSemestre->numero }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="border-t border-slate-100 px-5 py-4">
                            <p class="mb-3 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Note calculée</p>
                            <div class="flex items-end gap-1">
                                <span id="sidebar-note" class="text-3xl font-black leading-none text-violet-700">0,00</span>
                                <span class="mb-0.5 text-sm font-semibold text-slate-400">/ 10</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-lg bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[10px] text-slate-400">Objectifs</p>
                                    <p id="sidebar-obj" class="font-black text-slate-700">0,00</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-2 py-1.5 text-center">
                                    <p class="text-[10px] text-slate-400">Subjectifs</p>
                                    <p id="sidebar-subj" class="font-black text-slate-700">0,00</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-slate-100 px-5 py-4">
                            <p class="mb-3 text-[10px] font-black uppercase tracking-[0.15em] text-slate-400">Sections</p>
                            <ol class="space-y-1.5 text-xs text-slate-500">
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">1</span> Identification</li>
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">2</span> Critères objectifs</li>
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">3</span> Critères subjectifs</li>
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">4</span> Synthèse</li>
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">5</span> Plan d'amélioration</li>
                                <li class="flex items-center gap-2"><span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-violet-100 text-[9px] font-black text-violet-700">6</span> Signatures</li>
                            </ol>
                        </div>
                    </div>

                    {{-- Boutons d'action --}}
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                        <i class="fas fa-floppy-disk text-xs"></i> Enregistrer (brouillon)
                    </button>
                    <a href="{{ $backUrl ?? url()->previous() }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-600 shadow-sm transition hover:bg-slate-50">
                        <i class="fas fa-times text-xs"></i> Annuler
                    </a>

                </div>{{-- fin sidebar --}}

            </form>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script id="eval-objective-options"    type="application/json">@json($objectiveOptions ?? [])</script>
<script id="eval-subjective-templates" type="application/json">@json(old('subjective_criteres', $subjectiveTemplates ?? []))</script>
<script id="eval-objective-old"        type="application/json">@json(old('objective_criteres', []))</script>
<script id="eval-formations-old"       type="application/json">@json($oldFormations ?? null)</script>
<script id="eval-experiences-old"      type="application/json">@json($oldExperiences ?? [['periode'=>'','poste'=>'','observations'=>'']])</script>
<script id="eval-agents-data"          type="application/json">@json($agentsJson ?? [])</script>
<script id="eval-prefilled-agent"      type="application/json">@json($prefilledAgentId ?? null)</script>
<script id="eval-services-data"        type="application/json">@json($servicesJson ?? [])</script>
<script id="eval-agences-data"         type="application/json">@json($agencesJson  ?? [])</script>
<script id="eval-caisses-data"         type="application/json">@json($caissesJson  ?? [])</script>
<script id="eval-prefilled-caisse"     type="application/json">@json($selectedCaisse?->id  ?? null)</script>
<script id="eval-prefilled-service-dt" type="application/json">@json($selectedService?->id ?? null)</script>
<script id="eval-prefilled-agence"     type="application/json">@json($selectedAgence?->id  ?? null)</script>
@if(Route::has('directeur.evaluations.objectives-for-entity'))
<script id="eval-entity-objectives-url" type="application/json">@json(route('directeur.evaluations.objectives-for-entity'))</script>
@endif

@include('evaluations.partials._eval-js')
@endpush
