@extends('layouts.directeur')

@section('title', 'Mes subordonnés | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace Directeur / Subordonnés</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Mes subordonnés</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $direction->nom }}</p>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        {{-- Chefs de service --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h2 class="text-lg font-black text-slate-900">Chefs de service</h2>
                <a href="{{ route('directeur.evaluations.create') }}" class="ent-btn ent-btn-primary text-xs">
                    <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                </a>
            </div>

            @if ($servicesData->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-400">
                    Aucun service rattaché à votre direction.
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-base font-black text-slate-900">{{ $svc->nom }}</p>
                                    <p class="mt-0.5 text-sm text-slate-500">{{ $chef ?: '—' }}</p>
                                </div>
                                @if ($note)
                                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-black {{ $noteClass }}">
                                        {{ $note }}/10
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-600">
                                    <i class="fas fa-users text-[9px]"></i> {{ $item['agentsCount'] }} agents
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-600">
                                    <i class="fas fa-star text-[9px]"></i> {{ $item['evalCount'] }} éval.
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-bold text-slate-600">
                                    <i class="fas fa-bullseye text-[9px]"></i> {{ $item['ficheCount'] }} objectifs
                                </span>
                            </div>
                            <div class="flex gap-2 mt-auto pt-2 border-t border-slate-100">
                                <a href="{{ route('directeur.subordonnes.service', $svc) }}"
                                   class="ent-btn ent-btn-soft flex-1 justify-center text-xs">
                                    <i class="fas fa-eye mr-1"></i> Voir le dossier
                                </a>
                                <a href="{{ route('directeur.evaluations.create', ['service_id' => $svc->id]) }}"
                                   class="ent-btn ent-btn-soft text-xs" title="Nouvelle évaluation">
                                    <i class="fas fa-plus"></i>
                                </a>
                                <a href="{{ route('directeur.subordonnes.service.objectifs.create', $svc) }}"
                                   class="ent-btn ent-btn-soft text-xs" title="Assigner des objectifs">
                                    <i class="fas fa-bullseye"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Secrétaire --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h2 class="text-lg font-black text-slate-900">Secrétaire</h2>
                @if ($secretaire)
                    <div class="flex gap-2">
                        <a href="{{ route('directeur.subordonnes.secretaire.evaluations.create') }}" class="ent-btn ent-btn-soft text-xs">
                            <i class="fas fa-plus mr-1"></i> Évaluer
                        </a>
                        <a href="{{ route('directeur.subordonnes.secretaire.objectifs.create') }}" class="ent-btn ent-btn-soft text-xs">
                            <i class="fas fa-bullseye mr-1"></i> Objectifs
                        </a>
                    </div>
                @endif
            </div>

            @if ($secretaire)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 font-black text-lg">
                            {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-base font-black text-slate-900">{{ $secretaire->name }}</p>
                            <p class="text-sm text-slate-500">{{ $secretaire->email }}</p>
                            <div class="mt-1 flex gap-2 text-xs text-slate-400">
                                <span>{{ $secretaireEvalCount }} évaluation(s)</span>
                                <span>·</span>
                                <span>{{ $secretaireObjectifCount }} fiche(s) objectifs</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('directeur.subordonnes.secretaire') }}" class="ent-btn ent-btn-soft text-xs">
                        <i class="fas fa-eye mr-1"></i> Voir le dossier
                    </a>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-400">
                    Aucun(e) secrétaire enregistré(e) pour votre direction.
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
