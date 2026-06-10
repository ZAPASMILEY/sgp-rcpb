@extends('layouts.chef')

@section('title', 'Mon Équipe | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">
    <div class="w-full flex flex-col gap-6">

        {{-- Hero --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-600 px-6 py-8 lg:px-10">
            <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-10 left-1/3 h-40 w-40 rounded-full bg-indigo-300/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-blue-200">Mon Équipe · {{ $ctx->getTypeLabel() }}</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-white">{{ $ctx->getNom() }}</h1>
                    @if ($ctx->getParentNom())
                        <p class="mt-1 text-sm text-blue-100/80">{{ $ctx->getParentNom() }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="px-4 lg:px-8 flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="grid grid-cols-3 gap-3">
            @foreach ([
                ['label' => 'Agents dans l\'équipe', 'value' => $stats['total_agents'],       'icon' => 'fas fa-users',           'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
                ['label' => 'Déjà évalués',          'value' => $stats['agents_evalues'],      'icon' => 'fas fa-clipboard-check', 'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
                ['label' => 'Évaluations créées',    'value' => $stats['evaluations_creees'],  'icon' => 'fas fa-star-half-stroke','color' => 'bg-blue-600',    'light' => 'bg-blue-50 border-blue-100'],
            ] as $kpi)
            <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs">
                        <i class="{{ $kpi['icon'] }}"></i>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        @include('layouts._features_notice')

        {{-- Filtres --}}
        <form method="GET" action="{{ route('chef.equipe') }}" id="filtres-form"
              class="rounded-2xl bg-white px-5 py-4 shadow-sm border border-slate-100 flex flex-col gap-4">

            {{-- Ligne 1 : Recherche --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px] space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Rechercher</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-300 text-xs">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Nom ou prénom…"
                               class="w-full rounded-xl border border-slate-200 pl-8 pr-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300"
                               autocomplete="off">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Sexe</label>
                    <select name="sexe" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300">
                        <option value="">Tous</option>
                        <option value="homme" @selected($sexe === 'homme')>Homme</option>
                        <option value="femme" @selected($sexe === 'femme')>Femme</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Fonction</label>
                    <select name="fonction" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300 max-w-xs">
                        <option value="">Toutes</option>
                        @foreach ($fonctions as $key => $label)
                            <option value="{{ $key }}" @selected($fonction === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 ml-auto">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white shadow-sm hover:bg-blue-700">
                        <i class="fas fa-search text-[10px]"></i> Rechercher
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('chef.equipe') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    @endif
                </div>
            </div>

            {{-- Ligne 2 : Filtres statut + note --}}
            <div class="flex flex-wrap items-end gap-3 border-t border-slate-100 pt-3">
                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Statut évaluation</label>
                    <select name="statut_eval" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300">
                        <option value="">Tous</option>
                        <option value="non_evalue"  @selected($statutEval === 'non_evalue')>Non évalué</option>
                        <option value="brouillon"   @selected($statutEval === 'brouillon')>Brouillon</option>
                        <option value="soumis"      @selected($statutEval === 'soumis')>Soumise</option>
                        <option value="valide"      @selected($statutEval === 'valide')>Acceptée</option>
                        <option value="refuse"      @selected($statutEval === 'refuse')>Refusée</option>
                        <option value="reclamation" @selected($statutEval === 'reclamation')>Réclamation</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Fiche objectifs</label>
                    <select name="statut_fiche" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300">
                        <option value="">Toutes</option>
                        <option value="aucune"     @selected($statutFiche === 'aucune')>Aucune fiche</option>
                        <option value="en_attente" @selected($statutFiche === 'en_attente')>En attente</option>
                        <option value="acceptee"   @selected($statutFiche === 'acceptee')>Acceptée</option>
                        <option value="refusee"    @selected($statutFiche === 'refusee')>Refusée</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-black uppercase tracking-wider text-slate-400">Tranche de note</label>
                    <select name="note" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 outline-none focus:border-blue-300">
                        <option value="">Toutes</option>
                        <option value="non_note"   @selected($noteRange === 'non_note')>Non noté</option>
                        <option value="excellent"  @selected($noteRange === 'excellent')>Excellent (≥ 8.5)</option>
                        <option value="bien"       @selected($noteRange === 'bien')>Bien (7 – 8.4)</option>
                        <option value="moyen"      @selected($noteRange === 'moyen')>Moyen (5 – 6.9)</option>
                        <option value="insuffisant"@selected($noteRange === 'insuffisant')>Insuffisant (< 5)</option>
                    </select>
                </div>

                @if ($hasFilters)
                    <p class="text-[11px] text-slate-400 ml-auto self-end pb-2">
                        <span class="font-black text-blue-600">{{ $agentsOverview->count() }}</span>
                        / {{ $stats['total_agents'] }} agent{{ $stats['total_agents'] > 1 ? 's' : '' }}
                    </p>
                @endif
            </div>
        </form>

        {{-- Liste des agents --}}
        <div class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 lg:px-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                    Agents de {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                    @if ($sexe || $fonction)
                        <span class="ml-2 text-blue-500">· filtrés</span>
                    @endif
                </p>
            </div>

            @if ($agentsOverview->isEmpty())
                <div class="px-6 py-16 text-center lg:px-8">
                    <i class="fas fa-users text-3xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">
                        Aucun agent dans votre {{ $ctx->getTypeLabel() }} pour l'instant.
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Contactez l'administrateur pour affecter des agents à votre structure.
                    </p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($agentsOverview as $row)
                        @php
                            $ag           = $row['agent'];
                            $eval         = $row['latest_eval'];
                            $statut       = $row['eval_statut'];
                            $note         = $row['eval_note'];
                            $attention    = $row['attention'] ?? false;
                            $attReason    = $row['attention_reason'] ?? null;
                            $hasUnread    = $row['has_unread_notif'] ?? false;

                            $evalClass = match ($statut) {
                                'valide'      => 'bg-emerald-100 text-emerald-700',
                                'soumis'      => 'bg-amber-100 text-amber-700',
                                'refuse'      => 'bg-rose-100 text-rose-700',
                                'reclamation' => 'bg-orange-100 text-orange-700',
                                'brouillon'   => 'bg-slate-100 text-slate-600',
                                default       => null,
                            };
                            $evalLabel = match ($statut) {
                                'valide'      => 'Acceptée',
                                'soumis'      => 'Soumise',
                                'refuse'      => 'Refusée',
                                'reclamation' => 'Réclamation',
                                'brouillon'   => 'Brouillon',
                                default       => null,
                            };
                            $noteClass = $note !== null ? match(true) {
                                (float)$note >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                (float)$note >= 7   => 'bg-sky-100 text-sky-700',
                                (float)$note >= 5   => 'bg-amber-100 text-amber-700',
                                default             => 'bg-rose-100 text-rose-700',
                            } : null;
                            $noteBar = $note !== null ? max(0, min(100, (float)$note * 10)) : 0;
                            $barClass = $note !== null ? match(true) {
                                (float)$note >= 8.5 => 'bg-emerald-500',
                                (float)$note >= 7   => 'bg-sky-500',
                                (float)$note >= 5   => 'bg-amber-400',
                                default             => 'bg-rose-400',
                            } : 'bg-slate-200';
                        @endphp
                        <div class="flex flex-wrap items-center gap-5 px-6 py-5 transition lg:px-8
                            {{ $attention ? 'bg-rose-50/60 hover:bg-rose-50' : ($hasUnread ? 'bg-blue-50/40 hover:bg-blue-50/60' : 'hover:bg-slate-50/70') }}">

                            {{-- Avatar avec indicateurs --}}
                            <div class="relative shrink-0">
                                <div @class([
                                        'flex h-12 w-12 items-center justify-center rounded-2xl text-base font-black shadow-sm',
                                        'bg-rose-100 text-rose-700' => $attention,
                                        'bg-blue-100 text-blue-700' => ! $attention,
                                    ])>
                                    {{ strtoupper(substr(trim($ag->prenom . ' ' . $ag->nom), 0, 1)) }}
                                </div>
                                @if ($attention)
                                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex h-3 w-3 rounded-full bg-rose-500"></span>
                                    </span>
                                @elseif ($hasUnread)
                                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center">
                                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-60"></span>
                                        <span class="relative inline-flex h-3 w-3 rounded-full bg-blue-500"></span>
                                    </span>
                                @endif
                            </div>

                            {{-- Identité --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black text-slate-900 text-base">
                                        {{ trim($ag->prenom . ' ' . $ag->nom) }}
                                    </p>
                                    @if ($ag->poste ?: $ag->role)
                                        <span class="text-xs text-slate-400">· {{ $ag->poste ?: $ag->role }}</span>
                                    @endif
                                    @if ($attention)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-black text-rose-700"
                                              title="{{ $attReason }}">
                                            <i class="fas fa-circle-exclamation text-[9px]"></i>
                                            {{ $attReason }}
                                        </span>
                                    @elseif ($hasUnread)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-black text-blue-700">
                                            <i class="fas fa-bell text-[9px]"></i>
                                            Nouvelle notification
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    @if ($ag->numero_telephone)
                                        <span class="text-[11px] text-slate-400">
                                            <i class="fas fa-phone mr-1 text-[9px]"></i>{{ $ag->numero_telephone }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Dernière note --}}
                            <div class="hidden w-36 shrink-0 sm:block">
                                @if ($note !== null)
                                    <div class="mb-1 flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Dernière note</span>
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-black {{ $noteClass }}">
                                            {{ number_format((float)$note, 2, ',', ' ') }}/10
                                        </span>
                                    </div>
                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $barClass }}" style="width:{{ $noteBar }}%"></div>
                                    </div>
                                    @if ($evalLabel)
                                        <span class="mt-1.5 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $evalClass }}">
                                            {{ $evalLabel }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-300">Non évalué</span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('chef.agent.show', $ag->id) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-3 py-2 text-xs font-black text-white shadow-sm transition hover:bg-violet-700">
                                    <i class="fas fa-folder-open text-[10px]"></i> Dossier
                                </a>
                                @if($evaluationsEnabled && $row['ficheAcceptee'] && !$row['evaluationEnCours'])
                                    <a href="{{ route('chef.evaluations.create', ['agent_id' => $ag->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </a>
                                @else
                                    <span title="{{ $row['evaluationEnCours'] ? 'Une évaluation est déjà en cours (brouillon ou soumise).' : (!$row['ficheAcceptee'] ? 'Aucune fiche d\'objectifs acceptée.' : ($evaluationsDisabledMessage ?: 'Évaluations désactivées par l\'administrateur.')) }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-star-half-stroke text-[10px]"></i> Évaluer
                                    </span>
                                @endif
                                @if($objectifsEnabled && !$row['ficheBlocksNew'])
                                    <a href="{{ route('chef.objectifs.create', ['agent_id' => $ag->id]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </a>
                                @else
                                    <span title="{{ $row['ficheBlocksNew'] ? 'Une fiche d\'objectifs est déjà assignée à cet agent.' : ($objectifsDisabledMessage ?: 'Assignation d\'objectifs désactivée par l\'administrateur.') }}"
                                          class="ent-btn-disabled-light">
                                        <i class="fas fa-bullseye text-[10px]"></i> Objectifs
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
    </div>
</div>
@endsection
