@extends('layouts.dg')
@section('title', $fiche->titre.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $statut  = $fiche->statut ?? 'en_attente';
    $sc = match($statut) {
        'acceptee'  => ['label'=>'Acceptée',   'bg'=>'bg-emerald-100','text'=>'text-emerald-700','dot'=>'bg-emerald-500','border'=>'border-emerald-200'],
        'refusee'   => ['label'=>'Refusée',    'bg'=>'bg-rose-100',   'text'=>'text-rose-700',   'dot'=>'bg-rose-500',   'border'=>'border-rose-200'],
        'brouillon' => ['label'=>'Brouillon',  'bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'dot'=>'bg-amber-400',  'border'=>'border-amber-200'],
        'contesté'  => ['label'=>'Contestée',  'bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'dot'=>'bg-orange-500', 'border'=>'border-orange-200'],
        default     => ['label'=>'En attente', 'bg'=>'bg-sky-100',    'text'=>'text-sky-700',    'dot'=>'bg-sky-400',    'border'=>'border-sky-200'],
    };
    $avancement    = (int) ($fiche->avancement_percentage ?? 0);
    $progressColor = $avancement >= 75 ? 'bg-emerald-500' : ($avancement >= 40 ? 'bg-sky-500' : ($avancement > 0 ? 'bg-amber-400' : 'bg-slate-200'));
    $echeance      = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
    $expired       = $echeance && $echeance->isPast();
    $directeurNom  = $direction->directeur ? trim($direction->directeur->prenom.' '.$direction->directeur->nom) : $direction->nom;
    $backUrl       = route('dg.directions.show', ['direction' => $direction->id, 'tab' => 'objectifs']);
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-violet-300">
                    <a href="{{ $backUrl }}" class="hover:text-white transition">{{ $direction->nom }}</a>
                    <i class="fas fa-chevron-right text-[8px]"></i>
                    <span class="text-white">Fiche #{{ $fiche->id }}</span>
                </div>
                <h1 class="mt-2 text-2xl font-black text-white leading-tight">{{ $fiche->titre }}</h1>
                <p class="mt-1 text-sm text-violet-100/80">Assignée à <span class="font-semibold text-white">{{ $directeurNom }}</span></p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full border {{ $sc['border'] }} bg-white/10 px-3 py-1 text-xs font-bold text-white backdrop-blur-sm">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
                <a href="{{ $backUrl }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Bannière contestation --}}
        @if ($statut === 'contesté')
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-orange-200 bg-orange-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                    <i class="fas fa-flag text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-orange-900">Objectif(s) contesté(s) par le directeur</p>
                    <p class="mt-0.5 text-sm text-orange-700">Le directeur a contesté un ou plusieurs objectifs. Modifiez la fiche et renvoyez-la.</p>
                </div>
            </div>
            <a href="{{ route('dg.directions.objectifs.edit', $fiche) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-black text-white shadow-md shadow-orange-200 transition hover:bg-orange-700 shrink-0">
                <i class="fas fa-pencil text-xs"></i> Modifier et renvoyer
            </a>
        </div>
        @endif

        {{-- KPI cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Direction</p>
                <p class="mt-2 text-sm font-black text-slate-900 leading-snug">{{ $direction->nom }}</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Assignée le</p>
                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '—' }}
                </p>
            </div>
            <div class="rounded-[20px] border {{ $expired ? 'border-rose-200 bg-rose-50' : 'border-slate-100 bg-white' }} px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Échéance</p>
                <p class="mt-2 text-lg font-black {{ $expired ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $echeance ? $echeance->format('d/m/Y') : '—' }}
                </p>
                @if ($expired)
                    <p class="mt-0.5 text-[10px] font-bold text-rose-500"><i class="fas fa-circle-exclamation mr-1"></i>Dépassée</p>
                @endif
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement global</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-bold text-slate-400">%</span></p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $progressColor }}" style="width: {{ min($avancement, 100) }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">Calculé automatiquement</p>
            </div>
            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date de validation</p>
                @if ($fiche->date_validation)
                    <p class="mt-2 text-lg font-black text-emerald-700">
                        {{ \Carbon\Carbon::parse($fiche->date_validation)->format('d/m/Y') }}
                    </p>
                    <p class="mt-0.5 text-[10px] font-bold text-emerald-500"><i class="fas fa-circle-check mr-1"></i>Validée</p>
                @else
                    <p class="mt-2 text-lg font-black text-slate-400">—</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">Non encore validée</p>
                @endif
            </div>
        </div>

        {{-- Objectifs list --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Contenu de la fiche</p>
                    <p class="mt-0.5 text-sm font-black text-slate-800">Objectifs assignés <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $fiche->objectifs->count() }}</span></p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <i class="fas fa-bullseye"></i>
                </span>
            </div>
            <div class="divide-y divide-slate-50 px-6 py-2">
                @forelse ($fiche->objectifs as $index => $objectif)
                    @php
                        $contested  = ($objectif->statut ?? '') === 'contesté';
                        $ligneAv    = (int) ($objectif->avancement_percentage ?? 0);
                        $ligneColor = $ligneAv >= 75 ? 'bg-emerald-500' : ($ligneAv >= 40 ? 'bg-sky-500' : ($ligneAv > 0 ? 'bg-amber-400' : 'bg-slate-200'));
                    @endphp
                    <div class="flex items-start gap-4 py-4 rounded-xl {{ $contested ? 'bg-rose-50 -mx-2 px-2' : '' }}">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-black ring-1
                            {{ $contested ? 'bg-rose-100 text-rose-600 ring-rose-200' : 'bg-violet-50 text-violet-600 ring-violet-100' }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0 pt-1">
                            <p class="text-sm font-semibold leading-relaxed {{ $contested ? 'text-rose-700' : 'text-slate-700' }}">{{ $objectif->description }}</p>
                            @if ($contested)
                                <p class="mt-1 text-[10px] font-bold text-rose-500"><i class="fas fa-flag mr-1"></i>Contesté par le directeur</p>
                            @else
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="flex-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full transition-all {{ $ligneColor }}" style="width: {{ $ligneAv }}%"></div>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-black text-slate-500 w-8 text-right">{{ $ligneAv }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <i class="fas fa-inbox text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucun objectif défini dans cette fiche.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Bannière motif de refus --}}
        @if ($statut === 'refusee' && $fiche->motif_refus)
        <div class="flex items-start gap-4 rounded-[24px] border-2 border-rose-200 bg-rose-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                <i class="fas fa-comment-slash text-lg"></i>
            </div>
            <div>
                <p class="font-black text-rose-900">Motif du refus</p>
                <p class="mt-0.5 text-sm text-rose-700 italic">« {{ $fiche->motif_refus }} »</p>
            </div>
        </div>
        @endif

        {{-- Actions footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
            </a>
            <div class="flex flex-wrap gap-2">

                @if ($statut === 'brouillon')
                    <a href="{{ route('dg.directions.objectifs.edit', $fiche) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-black text-amber-700 shadow-sm transition hover:bg-amber-100">
                        <i class="fas fa-pencil text-xs"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('dg.directions.objectifs.soumettre', $fiche) }}"
                          onsubmit="return confirm('Soumettre cette fiche au directeur ?')">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                            <i class="fas fa-paper-plane text-xs"></i> Envoyer au directeur
                        </button>
                    </form>
                    <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                          onsubmit="return confirm('Supprimer ce brouillon ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                            <i class="fas fa-trash text-xs"></i> Supprimer
                        </button>
                    </form>

                @elseif ($statut === 'contesté')
                    <a href="{{ route('dg.directions.objectifs.edit', $fiche) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-100">
                        <i class="fas fa-pencil text-xs"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                          onsubmit="return confirm('Supprimer définitivement cette fiche ?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                            <i class="fas fa-trash text-xs"></i> Supprimer
                        </button>
                    </form>

                @elseif ($statut === 'refusee')
                    <a href="{{ route('dg.directions.objectifs.edit', $fiche) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-700 shadow-sm transition hover:bg-rose-100">
                        <i class="fas fa-pencil text-xs"></i> Corriger et renvoyer
                    </a>

                @else
                    @if ($statut === 'en_attente' || $fiche->statut === null)
                        <form method="POST" action="{{ route('dg.directions.objectifs.destroy', $fiche) }}"
                              onsubmit="return confirm('Supprimer définitivement cette fiche ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                                <i class="fas fa-trash text-xs"></i> Supprimer
                            </button>
                        </form>
                    @endif
                @endif

            </div>
        </div>

        </div>
    </div>
</div>
@endsection
