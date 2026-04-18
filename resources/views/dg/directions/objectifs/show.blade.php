@extends('layouts.dg')

@section('title', $fiche->titre.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Directions / {{ $direction->nom }}</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $fiche->titre }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Année {{ $fiche->annee }}</p>
                </div>
                <a href="{{ route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs']) }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction cible</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ $direction->nom }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'assignation</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Échéance</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h2 class="text-lg font-black text-slate-900">Objectifs assignés</h2>
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
            <div class="space-y-3">
                @forelse ($fiche->objectifs as $objectif)
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4">
                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
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

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    <span class="mt-1 inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                      onsubmit="return confirm('Supprimer définitivement cette fiche d\'objectifs ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="ent-btn bg-rose-600 text-white hover:bg-rose-700">
                        <i class="fas fa-trash mr-2"></i>Supprimer la fiche
                    </button>
                </form>
            </div>
        </section>

    </div>
</div>
@endsection
