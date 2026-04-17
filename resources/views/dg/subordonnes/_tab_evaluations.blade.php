{{-- Tab : Evaluations d'un subordonne du DG --}}
{{-- Variables : $evaluations (paginated), $evaluationsStats, $filters --}}

@php
$evalCards = [
    [
        'label'    => 'Total',
        'value'    => $evaluationsStats['total'],
        'icon'     => 'fas fa-clipboard-list',
        'tone'     => 'border-slate-100 bg-white text-slate-900',
        'iconWrap' => 'bg-slate-100 text-slate-600',
    ],
    [
        'label'    => 'Brouillons',
        'value'    => $evaluationsStats['brouillon'],
        'icon'     => 'fas fa-file-pen',
        'tone'     => 'border-slate-100 bg-slate-50/80 text-slate-900',
        'iconWrap' => 'bg-white text-slate-500',
    ],
    [
        'label'    => 'Soumises',
        'value'    => $evaluationsStats['soumis'],
        'icon'     => 'fas fa-paper-plane',
        'tone'     => 'border-amber-100 bg-amber-50/80 text-amber-900',
        'iconWrap' => 'bg-white text-amber-600',
    ],
    [
        'label'    => 'Validees',
        'value'    => $evaluationsStats['valide'],
        'icon'     => 'fas fa-circle-check',
        'tone'     => 'border-emerald-100 bg-emerald-50/80 text-emerald-900',
        'iconWrap' => 'bg-white text-emerald-600',
    ],
];
@endphp

<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($evalCards as $card)
        <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                    <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iconWrap'] }}">
                    <i class="{{ $card['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

<form method="GET" action="{{ request()->url() }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
    <input type="hidden" name="tab" value="evaluations">
    <div class="space-y-1.5">
        <label class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Statut</label>
        <select name="statut"
                class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
            <option value="">Tous les statuts</option>
            <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
            <option value="soumis" @selected($filters['statut'] === 'soumis')>Soumise</option>
            <option value="valide" @selected($filters['statut'] === 'valide')>Validee</option>
        </select>
    </div>
    <button type="submit"
            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
    </button>
    <a href="{{ request()->url() }}?tab=evaluations"
       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
        Effacer
    </a>
</form>

<div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm text-slate-700">
            <thead class="bg-slate-50/80">
                <tr class="border-b border-slate-200 text-slate-500">
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Cible</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Periode</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Note finale</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Mention</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($evaluations as $evaluation)
                    @php
                        $note = (float) $evaluation->note_finale;
                        $mention = $note < 5 ? 'Insuffisant'
                            : ($note < 7 ? 'Passable'
                            : ($note < 8.5 ? 'Bien' : 'Excellent'));

                        $mentionClass = match ($mention) {
                            'Excellent' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'Bien' => 'border-sky-200 bg-sky-50 text-sky-700',
                            'Passable' => 'border-amber-200 bg-amber-50 text-amber-700',
                            default => 'border-rose-200 bg-rose-50 text-rose-700',
                        };

                        $statusClass = match ($evaluation->statut) {
                            'valide' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'soumis' => 'border-amber-200 bg-amber-50 text-amber-700',
                            default => 'border-slate-200 bg-slate-100 text-slate-700',
                        };

                        $statusLabel = match ($evaluation->statut) {
                            'valide' => 'Validee',
                            'soumis' => 'Soumise',
                            default => 'Brouillon',
                        };

                        $identification = $evaluation->identification;
                        $cibleLabel = trim((string) ($identification?->nom_prenom ?? '')) ?: ($evaluation->evaluable?->name ?? '-');
                        $anneeEvaluation = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                        $semestreEvaluation = trim((string) ($identification?->semestre ?? ''));
                        if ($semestreEvaluation === '') {
                            $semestreEvaluation = $evaluation->date_debut->month <= 6 ? '1' : '2';
                        }
                        $periodeLabel = $anneeEvaluation.' - Semestre '.$semestreEvaluation;

                        $noteValue = number_format($note, 2, ',', ' ');
                        $notePercent = max(0, min(100, ($note / 10) * 100));
                        $noteBarClass = $notePercent >= 85
                            ? 'bg-emerald-500'
                            : ($notePercent >= 70 ? 'bg-sky-500' : ($notePercent >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                    @endphp
                    <tr class="align-top hover:bg-slate-50/60">
                        <td class="px-4 py-4 font-black text-slate-900">{{ $evaluation->id }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-slate-700">{{ $cibleLabel }}</p>
                            <p class="mt-1 text-xs text-slate-400">Evalue</p>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <p class="font-semibold text-slate-700">{{ $periodeLabel }}</p>
                            <p class="mt-1 text-xs text-slate-400">Evaluateur : {{ $evaluation->evaluateur?->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <div class="min-w-[130px]">
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Score</span>
                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">
                                        {{ $noteValue }}/10
                                    </span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $noteBarClass }}" style="width: {{ $notePercent }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $mentionClass }}">
                                {{ $mention }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <a href="{{ route('dg.sub-evaluations.show', $evaluation) }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                               title="Voir l'evaluation">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                <i class="fas fa-clipboard text-2xl text-slate-300"></i>
                                <p class="mt-2 text-sm font-black text-slate-700">Aucune evaluation</p>
                                <p class="mt-1 text-xs text-slate-500">Aucune evaluation enregistree pour ce collaborateur.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($evaluations->hasPages())
    <div class="mt-5 border-t border-slate-200 pt-4">
        {{ $evaluations->links() }}
    </div>
@endif
