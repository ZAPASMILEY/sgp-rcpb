@extends('layouts.directeur')

@section('title', 'Mes subordonnés | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">
    <div class="w-full flex flex-col gap-6">

        {{-- Hero --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-10 left-1/3 h-40 w-40 rounded-full bg-teal-300/10 blur-2xl"></div>
            <div class="relative">
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Espace Directeur · Subordonnés</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Mes subordonnés</h1>
                <p class="mt-1 text-sm text-emerald-100/80">{{ $ctx->getNom() }} · {{ $ctx->getRoleLabel() }}</p>
            </div>
        </div>

        @include('layouts._features_notice')

        <div class="px-4 lg:px-8 flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        {{-- ══ MODULE : Directeurs de Caisse (Directeur_Technique uniquement) ══ --}}
        @if ($ctx->hasCaisses())
        <section class="admin-panel overflow-hidden">

            {{-- Module header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                        <i class="fas fa-landmark text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-[0.14em] text-slate-700">Directeurs de Caisse</h2>
                        <p class="text-xs text-slate-400">{{ $caissesData->count() }} caisse(s) sous votre délégation</p>
                    </div>
                </div>
            </div>

            {{-- Liste --}}
            @if ($caissesData->isEmpty())
                <div class="px-6 py-10 text-center lg:px-8">
                    <i class="fas fa-landmark text-2xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune caisse rattachée à votre délégation</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($caissesData as $item)
                        @php
                            $css   = $item['caisse'];
                            $eval  = $item['latestEval'];
                            $dir   = $item['directeurUser'] ? $item['directeurUser']->name : null;
                            $note  = $eval ? number_format((float) $eval->note_finale, 2, ',', ' ') : null;
                            $noteClass = $eval ? match(true) {
                                (float) $eval->note_finale >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                (float) $eval->note_finale >= 7   => 'bg-sky-100 text-sky-700',
                                (float) $eval->note_finale >= 5   => 'bg-amber-100 text-amber-700',
                                default                            => 'bg-rose-100 text-rose-700',
                            } : null;
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50/70 lg:px-8">

                            {{-- Identité --}}
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 font-black text-base">
                                    {{ strtoupper(substr($css->nom, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-900">{{ $css->nom }}</p>
                                    <p class="text-xs text-slate-500">{{ $dir ?? 'Directeur non assigné' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-users text-[9px]"></i> {{ $item['agentsCount'] }} agents
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-star text-[9px]"></i> {{ $item['evalCount'] }} éval.
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-bullseye text-[9px]"></i> {{ $item['ficheCount'] }} objectifs
                                        </span>
                                        @if ($note)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black {{ $noteClass }}">{{ $note }}/10</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                @if($evaluationsEnabled && $item['ficheAcceptee'] && !$item['evaluationEnCours'])
                                    <a href="{{ route('directeur.evaluations.create', ['caisse_id' => $css->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="{{ $item['evaluationEnCours'] ? 'Une évaluation est déjà en cours.' : (!$item['ficheAcceptee'] ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')) }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled && !$item['ficheBlocksNew'])
                                    <a href="{{ route('directeur.subordonnes.caisse.objectifs.create', $css) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="{{ $item['ficheBlocksNew'] ? 'Une fiche d\'objectifs est déjà assignée à ce directeur.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                                <a href="{{ route('directeur.subordonnes.caisse', $css) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-violet-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
        @endif

        {{-- ══ MODULE : Agences (Directeur_Caisse uniquement) ══ --}}
        @if ($ctx->hasAgences())
        <section class="admin-panel overflow-hidden">

            {{-- Module header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                        <i class="fas fa-building text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-[0.14em] text-slate-700">Agences</h2>
                        <p class="text-xs text-slate-400">{{ $agencesData->count() }} agence(s) sous votre caisse</p>
                    </div>
                </div>
            </div>

            {{-- Liste --}}
            @if ($agencesData->isEmpty())
                <div class="px-6 py-10 text-center lg:px-8">
                    <i class="fas fa-building text-2xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune agence rattachée à votre caisse</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($agencesData as $item)
                        @php
                            $agc  = $item['agence'];
                            $eval = $item['latestEval'];
                            $chef = $agc->chef ? trim($agc->chef->prenom.' '.$agc->chef->nom) : null;
                            $note = $eval ? number_format((float) $eval->note_finale, 2, ',', ' ') : null;
                            $noteClass = $eval ? match(true) {
                                (float) $eval->note_finale >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                (float) $eval->note_finale >= 7   => 'bg-sky-100 text-sky-700',
                                (float) $eval->note_finale >= 5   => 'bg-amber-100 text-amber-700',
                                default                            => 'bg-rose-100 text-rose-700',
                            } : null;
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50/70 lg:px-8">

                            {{-- Identité --}}
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 font-black text-base">
                                    {{ strtoupper(substr($agc->nom, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-900">{{ $agc->nom }}</p>
                                    <p class="text-xs text-slate-500">{{ $chef ?? 'Chef non assigné' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-users text-[9px]"></i> {{ $item['agentsCount'] }} agents
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-cash-register text-[9px]"></i> {{ $item['guichetsCount'] }} guichets
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-star text-[9px]"></i> {{ $item['evalCount'] }} éval.
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-bullseye text-[9px]"></i> {{ $item['ficheCount'] }} objectifs
                                        </span>
                                        @if ($note)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black {{ $noteClass }}">{{ $note }}/10</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                @if($evaluationsEnabled && $item['ficheAcceptee'] && !$item['evaluationEnCours'])
                                    <a href="{{ route('directeur.evaluations.create', ['agence_id' => $agc->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="{{ $item['evaluationEnCours'] ? 'Une évaluation est déjà en cours.' : (!$item['ficheAcceptee'] ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')) }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled && !$item['ficheBlocksNew'])
                                    <a href="{{ route('directeur.subordonnes.agence.objectifs.create', $agc) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-sky-300 hover:text-sky-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="{{ $item['ficheBlocksNew'] ? 'Une fiche d\'objectifs est déjà assignée à ce chef.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                                <a href="{{ route('directeur.subordonnes.agence', $agc) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-sky-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
        @endif

        {{-- ══ MODULE : Chefs de Service ══ --}}
        <section class="admin-panel overflow-hidden">

            {{-- Module header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                        <i class="fas fa-sitemap text-sm"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-[0.14em] text-slate-700">Chefs de Service</h2>
                        <p class="text-xs text-slate-400">{{ $servicesData->count() }} service(s)</p>
                    </div>
                </div>
            </div>

            {{-- Liste --}}
            @if ($servicesData->isEmpty())
                <div class="px-6 py-10 text-center lg:px-8">
                    <i class="fas fa-sitemap text-2xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun service rattaché à votre structure</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($servicesData as $item)
                        @php
                            $svc  = $item['service'];
                            $eval = $item['latestEval'];
                            $chef = trim(($svc->chef_prenom ?? '').' '.($svc->chef_nom ?? ''));
                            $note = $eval ? number_format((float) $eval->note_finale, 2, ',', ' ') : null;
                            $noteClass = $eval ? match(true) {
                                (float) $eval->note_finale >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                (float) $eval->note_finale >= 7   => 'bg-sky-100 text-sky-700',
                                (float) $eval->note_finale >= 5   => 'bg-amber-100 text-amber-700',
                                default                            => 'bg-rose-100 text-rose-700',
                            } : null;
                        @endphp
                        <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50/70 lg:px-8">

                            {{-- Identité --}}
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 font-black text-base">
                                    {{ strtoupper(substr($svc->nom, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-black text-slate-900">{{ $svc->nom }}</p>
                                    <p class="text-xs text-slate-500">{{ $chef ?: 'Chef non assigné' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-users text-[9px]"></i> {{ $item['agentsCount'] }} agents
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-star text-[9px]"></i> {{ $item['evalCount'] }} éval.
                                        </span>
                                        <span class="text-slate-200">·</span>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                            <i class="fas fa-bullseye text-[9px]"></i> {{ $item['ficheCount'] }} objectifs
                                        </span>
                                        @if ($note)
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black {{ $noteClass }}">{{ $note }}/10</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                @if($evaluationsEnabled && $item['ficheAcceptee'] && !$item['evaluationEnCours'])
                                    <a href="{{ route('directeur.evaluations.create', ['service_id' => $svc->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="{{ $item['evaluationEnCours'] ? 'Une évaluation est déjà en cours.' : (!$item['ficheAcceptee'] ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')) }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled && !$item['ficheBlocksNew'])
                                    <a href="{{ route('directeur.subordonnes.service.objectifs.create', $svc) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="{{ $item['ficheBlocksNew'] ? 'Une fiche d\'objectifs est déjà assignée à ce chef.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                                <a href="{{ route('directeur.subordonnes.service', $svc) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-indigo-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ══ MODULE : Secrétaire ══ --}}
        <section class="admin-panel overflow-hidden">

            {{-- Module header --}}
            <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-4 lg:px-8">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <i class="fas fa-user-pen text-sm"></i>
                </span>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-[0.14em] text-slate-700">Secrétaire</h2>
                    <p class="text-xs text-slate-400">Secrétaire de direction</p>
                </div>
            </div>

            @if ($secretaire)
                <div class="flex flex-wrap items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50/70 lg:px-8">

                    {{-- Identité --}}
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 font-black text-base">
                            {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-black text-slate-900">{{ $secretaire->name }}</p>
                            <p class="text-xs text-slate-500">{{ $secretaire->email }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                    <i class="fas fa-star text-[9px]"></i> {{ $secretaireEvalCount }} éval.
                                </span>
                                <span class="text-slate-200">·</span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-400">
                                    <i class="fas fa-bullseye text-[9px]"></i> {{ $secretaireObjectifCount }} objectifs
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 items-center gap-2">
                        @if($evaluationsEnabled && $secretaireFicheAcceptee)
                            <a href="{{ route('directeur.subordonnes.secretaire.evaluations.create') }}"
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-rose-300 hover:text-rose-600">
                                <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                            </a>
                        @else
                            <span title="{{ !$secretaireFicheAcceptee ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.') }}"
                                  class="ent-btn-disabled-light">
                                <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                            </span>
                        @endif
                        @if($objectifsEnabled && !$secretaireFicheBlocksNew)
                            <a href="{{ route('directeur.subordonnes.secretaire.objectifs.create') }}"
                               class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-rose-300 hover:text-rose-600">
                                <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                            </a>
                        @else
                            <span title="{{ $secretaireFicheBlocksNew ? 'Une fiche d\'objectifs est déjà assignée à la secrétaire.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                                  class="ent-btn-disabled-light">
                                <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                            </span>
                        @endif
                        <a href="{{ route('directeur.subordonnes.secretaire') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-rose-700">
                            <i class="fas fa-folder-open text-[10px]"></i> Dossier
                        </a>
                    </div>
                </div>
            @else
                <div class="px-6 py-10 text-center lg:px-8">
                    <i class="fas fa-user-pen text-2xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune secrétaire enregistrée pour votre structure</p>
                </div>
            @endif
        </section>

        </div>
    </div>
</div>
@endsection
