@extends('layouts.pca')

@section('title', $fiche->titre." | ".config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $statutConfig = [
        'acceptee'  => ['label' => 'Acceptée',  'bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500', 'border' => 'border-emerald-200'],
        'refusee'   => ['label' => 'Refusée',   'bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'dot' => 'bg-rose-500',    'border' => 'border-rose-200'],
        'en_attente'=> ['label' => 'En attente','bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'dot' => 'bg-amber-400',   'border' => 'border-amber-200'],
    ];
    $statut  = $fiche->statut ?? 'en_attente';
    $sc      = $statutConfig[$statut] ?? $statutConfig['en_attente'];
    $avancement = (int) ($fiche->avancement_percentage ?? 0);
    $progressColor = $avancement >= 100 ? 'bg-emerald-500' : ($avancement >= 60 ? 'bg-sky-500' : ($avancement >= 30 ? 'bg-amber-400' : 'bg-rose-400'));
    $assignable = $fiche->assignable;
    $dgName = $assignable?->name ?? 'Directeur Général';
@endphp

<div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-10 pt-0 lg:px-8">
    <div class="w-full space-y-5">

        {{-- En-tête --}}
        <section class="rounded-[26px] border border-white bg-white/90 px-6 py-5 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('pca.objectifs.index') }}" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400 hover:text-emerald-600 transition">
                            Objectifs
                        </a>
                        <i class="fas fa-chevron-right text-[9px] text-slate-300"></i>
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Fiche #{{ $fiche->id }}</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900 leading-tight">{{ $fiche->titre }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Assignée à <span class="font-semibold text-slate-700">{{ $dgName }}</span></p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border {{ $sc['border'] }} {{ $sc['bg'] }} px-3 py-1 text-xs font-bold {{ $sc['text'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                        {{ $sc['label'] }}
                    </span>
                    <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-arrow-left mr-1.5"></i> Retour
                    </a>
                </div>
            </div>
        </section>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="fas fa-circle-check mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Métriques --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Année</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $fiche->annee }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date d'émission</p>
                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '-' }}
                </p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Échéance</p>
                @php
                    $echeance = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
                    $expired  = $echeance && $echeance->isPast();
                @endphp
                <p class="mt-2 text-lg font-black {{ $expired ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $echeance ? $echeance->format('d/m/Y') : '-' }}
                </p>
                @if ($expired)
                    <p class="mt-0.5 text-[10px] font-bold text-rose-400">Échéance dépassée</p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Objectifs</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $fiche->objectifs->count() }}</p>
            </div>
        </div>

        {{-- Avancement global --}}
        <section class="rounded-[26px] border border-slate-100 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Avancement global</p>
                    <p class="mt-1 text-3xl font-black text-slate-900">{{ $avancement }}<span class="text-lg font-bold text-slate-400">%</span></p>
                </div>
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl
                    {{ $avancement >= 100 ? 'bg-emerald-100 text-emerald-600' : ($avancement >= 60 ? 'bg-sky-100 text-sky-600' : 'bg-amber-100 text-amber-600') }}">
                    <i class="fas {{ $avancement >= 100 ? 'fa-circle-check' : 'fa-chart-line' }} text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 h-3 w-full rounded-full bg-slate-100">
                <div class="h-3 rounded-full transition-all {{ $progressColor }}" style="width: {{ min($avancement, 100) }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-[10px] font-bold text-slate-400">
                <span>0%</span>
                <span>50%</span>
                <span>100%</span>
            </div>
        </section>

        {{-- Liste des objectifs --}}
        <section class="rounded-[26px] border border-slate-100 bg-white px-6 py-5 shadow-sm">
            <div class="mb-5 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Objectifs de la fiche</h2>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $fiche->objectifs->count() }} objectif(s) défini(s)</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-bullseye"></i>
                </span>
            </div>

            @forelse ($fiche->objectifs as $index => $objectif)
                <div class="group flex items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50/60 px-5 py-4 mb-3 last:mb-0 transition hover:border-emerald-200 hover:bg-emerald-50/30">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white font-black text-sm text-emerald-600 shadow-sm border border-slate-100">
                        {{ $index + 1 }}
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-slate-800 leading-relaxed">{{ $objectif->description }}</p>
                        @if ($objectif->avancement_percentage !== null)
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-1.5 flex-1 rounded-full bg-slate-200">
                                    <div class="h-1.5 rounded-full {{ (int)$objectif->avancement_percentage >= 100 ? 'bg-emerald-500' : 'bg-sky-500' }}"
                                         style="width: {{ min((int)$objectif->avancement_percentage, 100) }}%"></div>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500">{{ (int)$objectif->avancement_percentage }}%</span>
                            </div>
                        @endif
                    </div>
                    @if ((int)($objectif->avancement_percentage ?? 0) >= 100)
                        <i class="fas fa-circle-check mt-0.5 shrink-0 text-emerald-500"></i>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-10 text-center">
                    <i class="fas fa-inbox text-2xl text-slate-300"></i>
                    <p class="mt-2 text-sm font-semibold text-slate-400">Aucun objectif défini dans cette fiche.</p>
                </div>
            @endforelse
        </section>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-white px-6 py-4 shadow-sm">
            <a href="{{ route('pca.objectifs.index') }}" class="ent-btn ent-btn-soft">
                <i class="fas fa-arrow-left mr-1.5"></i> Retour à la liste
            </a>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('pca.objectifs.contrat', $fiche) }}" class="ent-btn ent-btn-soft">
                    <i class="fas fa-eye mr-1.5"></i> Aperçu du contrat
                </a>
                <a href="{{ route('pca.objectifs.contrat.download', $fiche) }}" class="ent-btn ent-btn-primary">
                    <i class="fas fa-file-pdf mr-1.5"></i> Télécharger PDF
                </a>
                @if ($fiche->statut === 'en_attente' || $fiche->statut === null)
                    <form method="POST" action="{{ route('pca.objectifs.destroy', $fiche) }}"
                          onsubmit="return confirm('Supprimer définitivement cette fiche ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ent-btn ent-btn-destructive">
                            <i class="fas fa-trash mr-1.5"></i> Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
