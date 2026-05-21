@extends('layouts.personnel')
@section('title', 'Mon évaluation | ' . config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $notePercent  = max(0, min(100, ((float) $note / 10) * 100));
    $noteBarClass = $notePercent >= 85 ? 'bg-emerald-500' : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
    $mentionClass = match ($mention) {
        'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'Bien'      => 'border-sky-200 bg-sky-50 text-sky-700',
        'Passable'  => 'border-amber-200 bg-amber-50 text-amber-700',
        default     => 'border-rose-200 bg-rose-50 text-rose-700',
    };
@endphp
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- En-tête --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mon Espace / Mes évaluations</p>
                    <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                        Évaluation — {{ $anneeEval }} · Semestre {{ $semestreEval }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Évaluateur : {{ $evaluation->evaluateur?->name ?? '—' }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('personnel.evaluations.pdf', $evaluation) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300">
                        <i class="fas fa-file-pdf text-xs text-rose-500"></i> Télécharger PDF
                    </a>
                    <a href="{{ route('personnel.dashboard') }}?tab=evaluations"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300">
                        <i class="fas fa-arrow-left text-xs"></i> Mon espace
                    </a>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Bandeau accepter / refuser (uniquement si soumise) --}}
        @if ($canValidate)
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Validation requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">Consultez votre fiche d'évaluation puis acceptez ou refusez-la.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button"
                        onclick="document.getElementById('modalRefus').classList.remove('hidden');document.getElementById('modalRefus').classList.add('flex')"
                        class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                    <i class="fas fa-times text-xs"></i> Refuser
                </button>
                <form action="{{ route('personnel.evaluations.statut', $evaluation) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit"
                            onclick="return confirm('Accepter cette évaluation ?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-slate-800">
                        <i class="fas fa-check text-xs"></i> Accepter
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- Bandeau refus + réclamation --}}
        @if($evaluation->statut === 'refuse')
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-rose-200 bg-rose-50 px-6 py-5">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-ban text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black text-rose-900">Évaluation refusée</p>
                    @if($evaluation->motif_refus)
                    <p class="mt-1 text-sm font-semibold text-rose-700">Motif :</p>
                    <p class="mt-0.5 text-sm text-rose-800">{{ $evaluation->motif_refus }}</p>
                    @endif
                    @if($evaluation->statut_reclamation)
                    <p class="mt-3 inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-white px-3 py-1 text-xs font-semibold text-rose-700">
                        @if($evaluation->statut_reclamation === 'en_attente') <i class="fas fa-clock"></i> Réclamation en attente
                        @elseif($evaluation->statut_reclamation === 'maintenu') <i class="fas fa-ban"></i> Refus maintenu par le RH
                        @elseif($evaluation->statut_reclamation === 'rouvert') <i class="fas fa-rotate-left"></i> Rouvert pour correction
                        @endif
                    </p>
                    @endif
                </div>
            </div>
            {{-- Formulaire de réclamation --}}
            @if(!$evaluation->reclamation && $evaluation->statut_reclamation !== 'maintenu')
            <form action="{{ route('personnel.evaluations.reclamer', $evaluation) }}" method="POST" class="mt-2 flex flex-col gap-3">
                @csrf
                <label class="text-sm font-semibold text-rose-800">Soumettre une réclamation</label>
                <textarea name="reclamation" rows="3"
                    class="w-full rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100"
                    placeholder="Expliquez les raisons de votre réclamation…" required maxlength="1000"></textarea>
                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-rose-700">
                        <i class="fas fa-paper-plane text-xs"></i> Envoyer la réclamation
                    </button>
                </div>
            </form>
            @elseif($evaluation->reclamation)
            <div class="mt-2 rounded-xl border border-rose-200 bg-white px-4 py-3">
                <p class="text-xs font-black uppercase tracking-[0.15em] text-rose-400">Votre réclamation</p>
                <p class="mt-1 text-sm text-slate-700">{{ $evaluation->reclamation }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Résumé des scores --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Période</p>
                    <p class="mt-2 text-sm text-slate-800">
                        {{ $evaluation->date_debut->format('d/m/Y') }} – {{ $evaluation->date_fin->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note subjectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_subjectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Moy. objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->moyenne_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note objectifs</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $evaluation->note_criteres_objectifs, 2, ',', ' ') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">
                        {{ number_format($note, 2, ',', ' ') }}<span class="text-sm font-semibold text-slate-500">/10</span>
                    </p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-4">
                <span class="inline-flex items-center gap-1.5 rounded-full border {{ $mentionClass }} px-3 py-1 text-xs font-black">
                    {{ $mention }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full border {{ $statusClass }} px-3 py-1 text-xs font-black">
                    {{ $statusLabel }}
                </span>
            </div>
        </section>

        {{-- Critères objectifs --}}
        @if ($objectiveCriteria->isNotEmpty())
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Critères objectifs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4 text-left">Objectif</th>
                            <th class="py-3 pr-4 text-center">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($objectiveCriteria as $critere)
                            <tr>
                                <td class="py-3 pr-4 font-medium">{{ $critere->libelle }}</td>
                                <td class="py-3 pr-4 text-center font-black text-slate-900">
                                    @foreach ($critere->sousCriteres as $sc)
                                        {{ $sc->note }}/5
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Critères subjectifs --}}
        @if ($subjectiveCriteria->isNotEmpty())
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Critères subjectifs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="border-b border-slate-100 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4 text-left">Critère</th>
                            <th class="py-3 pr-4 text-left">Sous-critère</th>
                            <th class="py-3 pr-4 text-center">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($subjectiveCriteria as $critere)
                            @foreach ($critere->sousCriteres as $i => $sc)
                                <tr>
                                    @if ($i === 0)
                                        <td class="py-3 pr-4 font-semibold" rowspan="{{ $critere->sousCriteres->count() }}">
                                            {{ $critere->libelle }}
                                        </td>
                                    @endif
                                    <td class="py-3 pr-4 text-slate-600">{{ $sc->libelle }}</td>
                                    <td class="py-3 pr-4 text-center font-black text-slate-900">{{ $sc->note }}/5</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- Commentaires --}}
        @if ($evaluation->commentaire || $evaluation->points_a_ameliorer || $evaluation->strategies_amelioration)
        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="mb-4 text-base font-black text-slate-900">Commentaires</h2>
            @if ($evaluation->commentaire)
                <div class="mb-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Commentaire général</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->commentaire }}</p>
                </div>
            @endif
            @if ($evaluation->points_a_ameliorer)
                <div class="mb-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Points à améliorer</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->points_a_ameliorer }}</p>
                </div>
            @endif
            @if ($evaluation->strategies_amelioration)
                <div>
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Stratégies d'amélioration</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $evaluation->strategies_amelioration }}</p>
                </div>
            @endif
        </section>
        @endif

    </div>
</div>

{{-- Modal Refus --}}
<div id="modalRefus" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="this.parentElement.classList.add('hidden');this.parentElement.classList.remove('flex')"></div>
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-4">
        <h3 class="text-lg font-black text-slate-900">Refuser l'évaluation</h3>
        <p class="mt-1 text-sm text-slate-500">Indiquez le motif de votre refus (obligatoire).</p>
        <form action="{{ route('personnel.evaluations.statut', $evaluation) }}" method="POST" class="mt-5 flex flex-col gap-4">
            @csrf @method('PATCH')
            <input type="hidden" name="action" value="refuser">
            <div class="flex flex-col gap-1.5">
                <label for="motif_refus" class="text-sm font-semibold text-slate-700">Motif du refus</label>
                <textarea id="motif_refus" name="motif_refus" rows="4" required maxlength="1000"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 shadow-sm focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100"
                    placeholder="Expliquez pourquoi vous refusez cette évaluation…"></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalRefus').classList.add('hidden');document.getElementById('modalRefus').classList.remove('flex')"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-2xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700">
                    <i class="fas fa-times mr-1.5 text-xs"></i> Confirmer le refus
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('modalRefus').classList.add('hidden');
        document.getElementById('modalRefus').classList.remove('flex');
    }
});
</script>
@endsection
