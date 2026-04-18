@extends('layouts.directeur')

@section('title', $secretaire->name.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace Directeur / Subordonnés / Secrétaire</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $secretaire->name }}</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $secretaire->email }}</p>
                </div>
                <a href="{{ route('directeur.subordonnes') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
        @endif

        {{-- Tabs --}}
        <div class="admin-panel px-6 py-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                    @foreach ([
                        ['key' => 'evaluations', 'icon' => 'fas fa-star-half-stroke', 'label' => 'Évaluations'],
                        ['key' => 'objectifs',   'icon' => 'fas fa-bullseye',          'label' => 'Objectifs'],
                    ] as $t)
                        <a href="{{ route('directeur.subordonnes.secretaire', ['tab' => $t['key']]) }}"
                           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                               {{ $tab === $t['key'] ? 'border border-slate-200 bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            <i class="{{ $t['icon'] }} text-xs"></i> {{ $t['label'] }}
                        </a>
                    @endforeach
                </div>
                @if ($tab === 'evaluations')
                    <a href="{{ route('directeur.subordonnes.secretaire.evaluations.create') }}" class="ent-btn ent-btn-primary text-xs">
                        <i class="fas fa-plus mr-1"></i> Nouvelle évaluation
                    </a>
                @else
                    <a href="{{ route('directeur.subordonnes.secretaire.objectifs.create') }}" class="ent-btn ent-btn-primary text-xs">
                        <i class="fas fa-plus mr-1"></i> Assigner des objectifs
                    </a>
                @endif
            </div>

            {{-- ── Évaluations ── --}}
            @if ($tab === 'evaluations')
                @forelse ($evaluations as $eval)
                    @php
                        $sc = match($eval->statut) {
                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                            'refuse' => 'border-rose-200 bg-rose-50 text-rose-700',
                            default  => 'border-slate-200 bg-slate-100 text-slate-600',
                        };
                        $sl = match($eval->statut) {
                            'valide' => 'Acceptée', 'soumis' => 'Soumise', 'refuse' => 'Refusée', default => 'Brouillon',
                        };
                        $note = number_format((float) $eval->note_finale, 2, ',', ' ');
                        $noteClass = match(true) {
                            (float)$eval->note_finale >= 8.5 => 'bg-emerald-100 text-emerald-700',
                            (float)$eval->note_finale >= 7   => 'bg-sky-100 text-sky-700',
                            (float)$eval->note_finale >= 5   => 'bg-amber-100 text-amber-700',
                            default                           => 'bg-rose-100 text-rose-700',
                        };
                    @endphp
                    <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Période d'évaluation</p>
                                <p class="mt-1 text-base font-black text-slate-900">
                                    {{ $eval->date_debut->format('m/Y') }} — {{ $eval->date_fin->format('m/Y') }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $sc }}">{{ $sl }}</span>
                                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{ $noteClass }}">{{ $note }}/10</span>
                                <a href="{{ route('directeur.subordonnes.secretaire.evaluations.show', $eval) }}" class="ent-btn ent-btn-soft text-xs">Voir</a>
                                @if ($eval->statut === 'brouillon')
                                    <form method="POST" action="{{ route('directeur.subordonnes.secretaire.evaluations.submit', $eval) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="ent-btn ent-btn-primary text-xs">
                                            <i class="fas fa-paper-plane mr-1"></i> Soumettre
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('directeur.subordonnes.secretaire.evaluations.destroy', $eval) }}"
                                          onsubmit="return confirm('Supprimer cette évaluation ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ent-btn text-xs bg-rose-50 text-rose-600 hover:bg-rose-100">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center text-sm text-slate-400">
                        Aucune évaluation créée pour la secrétaire.
                    </div>
                @endforelse

            {{-- ── Objectifs ── --}}
            @else
                @forelse ($fiches as $fiche)
                    @php
                        $sc = match($fiche->statut) {
                            'acceptee'   => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'en_attente' => 'border-amber-200 bg-amber-50 text-amber-700',
                            'refusee'    => 'border-rose-200 bg-rose-50 text-rose-700',
                            default      => 'border-slate-200 bg-slate-100 text-slate-600',
                        };
                        $sl = match($fiche->statut) {
                            'acceptee' => 'Acceptée', 'en_attente' => 'En attente', 'refusee' => 'Refusée', default => ucfirst($fiche->statut),
                        };
                    @endphp
                    <div class="mb-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-black text-slate-900">{{ $fiche->titre }}</p>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    Année : {{ $fiche->annee }}
                                    @if ($fiche->date_echeance)
                                        · Échéance : {{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}
                                    @endif
                                    · {{ $fiche->objectifs_count }} objectif(s)
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-black {{ $sc }}">{{ $sl }}</span>
                                <a href="{{ route('directeur.subordonnes.secretaire.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft text-xs">Voir</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center text-sm text-slate-400">
                        Aucune fiche d'objectifs assignée à la secrétaire.
                    </div>
                @endforelse
            @endif
        </div>

    </div>
</div>
@endsection
