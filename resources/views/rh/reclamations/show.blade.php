@extends('layouts.rh')

@section('title', 'Réclamation — Détail | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

    {{-- En-tête --}}
    @php
        $nom = $evaluation->identification?->nom_prenom
            ?? (($evaluation->evaluable?->prenom ?? '') . ' ' . ($evaluation->evaluable?->nom ?? ''))
            ?: ($evaluation->evaluable?->nom ?? '—');
        $nom = trim($nom) ?: '—';

        $statutReclam = $evaluation->statut_reclamation;
        $badgeClass = match($statutReclam) {
            'en_attente' => 'bg-amber-100 text-amber-700 border-amber-200',
            'maintenu'   => 'bg-red-100 text-red-700 border-red-200',
            'rouvert'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            default      => 'bg-slate-100 text-slate-500 border-slate-200',
        };
        $statutLabel = match($statutReclam) {
            'en_attente' => 'En attente',
            'maintenu'   => 'Maintenu',
            'rouvert'    => 'Réglé',
            default      => '—',
        };
    @endphp

    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace RH / Réclamations / Détail</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                    <i class="fas fa-triangle-exclamation mr-2 text-amber-500"></i> {{ $nom }}
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Évaluation #{{ $evaluation->id }}
                    @if($evaluation->identification?->grade)
                        — {{ $evaluation->identification->grade }}
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('rh.reclamations.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if(session('status'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Colonne principale --}}
        <div class="flex flex-col gap-6 lg:col-span-2">

            {{-- Informations générales --}}
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">Informations générales</h2>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Évalué</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $nom }}</p>
                        @if($evaluation->identification?->grade)
                            <p class="text-xs text-slate-400">{{ $evaluation->identification->grade }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Évaluateur</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $evaluation->evaluateur?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Statut évaluation</p>
                        <p class="mt-1">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold
                                {{ match($evaluation->statut) {
                                    'valide'      => 'bg-emerald-100 text-emerald-700',
                                    'refuse'      => 'bg-red-100 text-red-700',
                                    'soumis'      => 'bg-blue-100 text-blue-700',
                                    'brouillon'   => 'bg-slate-100 text-slate-600',
                                    'a_reviser'   => 'bg-orange-100 text-orange-700',
                                    'reclamation' => 'bg-amber-100 text-amber-700',
                                    default       => 'bg-slate-100 text-slate-500',
                                } }}">
                                {{ \App\Models\Evaluation::STATUT_LABELS[$evaluation->statut] ?? $evaluation->statut }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Statut réclamation</p>
                        <p class="mt-1">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                                @if($statutReclam === 'en_attente')
                                    <i class="fas fa-clock text-[10px]"></i>
                                @elseif($statutReclam === 'maintenu')
                                    <i class="fas fa-ban text-[10px]"></i>
                                @elseif($statutReclam === 'rouvert')
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                @endif
                                {{ $statutLabel }}
                            </span>
                        </p>
                    </div>
                    @if($evaluation->note_finale !== null)
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Note finale</p>
                        <p class="mt-1 text-2xl font-black text-slate-800">{{ number_format($evaluation->note_finale, 2) }}</p>
                    </div>
                    @endif
                    @if($evaluation->date_debut || $evaluation->date_fin)
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400">Période</p>
                        <p class="mt-1 text-slate-700">
                            {{ $evaluation->date_debut?->format('d/m/Y') ?? '—' }}
                            @if($evaluation->date_fin) → {{ $evaluation->date_fin->format('d/m/Y') }} @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Motif du refus --}}
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">
                        <i class="fas fa-ban mr-2 text-red-400"></i>Motif du refus
                    </h2>
                </div>
                <div class="px-6 py-5">
                    @if($evaluation->motif_refus)
                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $evaluation->motif_refus }}</p>
                    @else
                        <p class="text-slate-400 italic text-sm">Aucun motif de refus renseigné.</p>
                    @endif
                </div>
            </div>

            {{-- Réclamation de l'évalué --}}
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">
                        <i class="fas fa-comment-dots mr-2 text-amber-400"></i>Réclamation de l'évalué
                    </h2>
                </div>
                <div class="px-6 py-5">
                    @if($evaluation->reclamation)
                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $evaluation->reclamation }}</p>
                    @else
                        <p class="text-slate-400 italic text-sm">Aucune réclamation déposée.</p>
                    @endif
                </div>
            </div>

            {{-- Commentaires --}}
            @if($evaluation->commentaire || $evaluation->commentaires_evalue)
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">Commentaires</h2>
                </div>
                <div class="px-6 py-5 flex flex-col gap-4">
                    @if($evaluation->commentaire)
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400 mb-1">Commentaire évaluateur</p>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $evaluation->commentaire }}</p>
                    </div>
                    @endif
                    @if($evaluation->commentaires_evalue)
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-400 mb-1">Commentaire évalué</p>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">{{ $evaluation->commentaires_evalue }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </div>

        {{-- Colonne latérale : actions --}}
        <div class="flex flex-col gap-6">

            {{-- Actions RH --}}
            @if(in_array($statutReclam, ['en_attente', null]))
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">
                        <i class="fas fa-gavel mr-2 text-slate-400"></i>Décision RH
                    </h2>
                </div>
                <div class="px-6 py-5 flex flex-col gap-3">
                    <p class="text-xs text-slate-500">Choisissez la suite à donner à cette réclamation.</p>

                    {{-- Maintenir le refus --}}
                    <form method="POST" action="{{ route('rh.reclamations.repondre', $evaluation) }}">
                        @csrf
                        <input type="hidden" name="reponse" value="maintenu">
                        <button type="submit"
                                onclick="return confirm('Confirmer : maintenir le refus ?')"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 shadow-sm transition hover:bg-red-100">
                            <i class="fas fa-ban"></i> Maintenir le refus
                        </button>
                    </form>

                    {{-- Rouvrir l'évaluation --}}
                    <form method="POST" action="{{ route('rh.reclamations.repondre', $evaluation) }}">
                        @csrf
                        <input type="hidden" name="reponse" value="rouvert">
                        <button type="submit"
                                onclick="return confirm('Confirmer : rouvrir l\'évaluation pour correction ?')"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                            <i class="fas fa-rotate-left"></i> Rouvrir l'évaluation
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="admin-panel overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">Décision RH</h2>
                </div>
                <div class="px-6 py-5">
                    <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold {{ $badgeClass }}">
                        @if($statutReclam === 'maintenu')
                            <i class="fas fa-ban"></i> Refus maintenu
                        @elseif($statutReclam === 'rouvert')
                            <i class="fas fa-check-circle"></i> Évaluation réouverte
                        @endif
                    </span>
                    <p class="mt-2 text-xs text-slate-400">Cette réclamation a déjà été traitée.</p>
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
</div>
@endsection
