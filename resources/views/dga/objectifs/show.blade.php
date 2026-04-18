@extends('layouts.dga')

@section('title', 'Ma fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto flex max-w-5xl flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace DGA / Mes objectifs</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $fiche->titre }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Annee {{ $fiche->annee }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dga.objectifs.pdf', $fiche) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Telecharger PDF
                    </a>
                    <a href="{{ route('dga.mon-espace') }}?tab=objectifs" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </div>
        </header>

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

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'assignation</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Echeance</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Avancement</p>
                    @php
                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                    @endphp
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-semibold text-slate-500">%</span></p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $avancementColor }}" style="width: {{ $avancement }}%"></div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutClass }}">
                            {{ $statutLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Objectifs assignes</h2>
            <div class="mt-4 space-y-3">
                @forelse ($fiche->objectifs as $objectif)
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4">
                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700">
                            <i class="fas fa-check text-[10px]"></i>
                        </div>
                        <p class="text-sm text-slate-700">{{ $objectif->description }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center">
                        <p class="text-sm text-slate-500">Aucun objectif dans cette fiche.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Avancement --}}
        @if (($fiche->statut ?? 'en_attente') !== 'refusee')
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Mettre a jour l'avancement</h2>
            @php $pct = (int)($fiche->avancement_percentage ?? 0); @endphp
            <div class="mt-4 flex items-center gap-5">
                <form method="POST" action="{{ route('dga.objectifs.avancement', $fiche) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="avancement_percentage" value="{{ max(0, $pct - 5) }}">
                    <button type="submit" @disabled($pct <= 0) class="ent-btn ent-btn-soft disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-minus mr-1"></i> 5%
                    </button>
                </form>
                <div class="flex flex-col items-center gap-1 min-w-[100px]">
                    <span class="text-3xl font-black text-slate-900">{{ $pct }}%</span>
                    <div class="w-full h-2 overflow-hidden rounded-full bg-slate-200">
                        @php $barColor = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-sky-500' : ($pct >= 25 ? 'bg-amber-400' : 'bg-slate-300')); @endphp
                        <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                <form method="POST" action="{{ route('dga.objectifs.avancement', $fiche) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="avancement_percentage" value="{{ min(100, $pct + 5) }}">
                    <button type="submit" @disabled($pct >= 100) class="ent-btn ent-btn-primary disabled:opacity-40 disabled:cursor-not-allowed">
                        <i class="fas fa-plus mr-1"></i> 5%
                    </button>
                </form>
            </div>
        </section>
        @endif

        {{-- Actions : Accepter / Refuser (uniquement si statut = en_attente) --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut de la fiche</p>
                    <span class="mt-1 inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statutClass }}">
                        {{ $statutLabel }}
                    </span>
                </div>
                @if (($fiche->statut ?? 'en_attente') === 'en_attente')
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('dga.objectifs.statut', $fiche) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="accepter">
                            <button type="submit" class="ent-btn ent-btn-primary">
                                <i class="fas fa-check mr-2"></i>Accepter la fiche
                            </button>
                        </form>
                        <form method="POST" action="{{ route('dga.objectifs.statut', $fiche) }}"
                              onsubmit="return confirm('Confirmer le refus de cette fiche d\'objectifs ?')">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="action" value="refuser">
                            <button type="submit" class="ent-btn bg-rose-600 text-white hover:bg-rose-700">
                                <i class="fas fa-times mr-2"></i>Refuser la fiche
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </section>

    </div>
</div>
@endsection
